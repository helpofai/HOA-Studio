<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\AI\Services;

use Exception;
use Generator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OmniRouteClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected array $endpoints;

    public function __construct()
    {
        $this->endpoints = OmniRouteUrlResolver::resolve(config('omniroute.base_url', 'http://localhost:20128/v1'));
        $this->baseUrl = $this->endpoints['openai_base'];
        $this->apiKey = config('omniroute.api_key', 'omniroute-default-key');
        $this->timeout = (int) config('omniroute.timeout_seconds', 120);
    }

    /**
     * Standard non-streaming chat completion request.
     */
    public function chatCompletion(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? config('omniroute.default_model', 'auto');
        $sessionId = $options['session_id'] ?? (string) Str::uuid();
        $requestId = $options['request_id'] ?? (string) Str::uuid();

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'stream' => false,
        ];

        if (isset($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        $headers = $this->buildHeaders([
            'X-OmniRoute-Session-Id' => $sessionId,
            'X-Request-Id' => $requestId,
            'X-OmniRoute-No-Cache' => !($options['cache'] ?? config('omniroute.cache_enabled', true)) ? 'true' : 'false',
            'x-omniroute-compression' => $options['compression'] ?? config('omniroute.compression', 'default'),
        ]);

        $response = Http::withHeaders($headers)
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->timeout($this->timeout)
            ->retry(3, 100, function ($exception, $request) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException 
                    || (isset($exception->response) && $exception->response->status() >= 500);
            }, throw: false)
            ->post($this->endpoints['chat_completions_endpoint'], $payload);

        // Fallback model recovery if primary model failed with 5xx or connection error
        if ($response->failed() && $model !== 'glm/glm-4-flash' && $model !== 'deepseek/deepseek-chat') {
            $fallbackModel = config('omniroute.fallback_model', 'deepseek/deepseek-chat');
            $payload['model'] = $fallbackModel;

            $response = Http::withHeaders($headers)
                ->withOptions(['force_ip_resolve' => 'v4'])
                ->timeout($this->timeout)
                ->post($this->endpoints['chat_completions_endpoint'], $payload);
        }

        if ($response->failed()) {
            Log::error('[OmniRouteClient] Chat completion failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception("OmniRoute error ({$response->status()}): " . ($response->json('error.message') ?? $response->body()));
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        $usage = $data['usage'] ?? [];

        // Extract OmniRoute Telemetry Headers
        $cost = (float) $response->header('X-OmniRoute-Response-Cost', '0.0000000000');
        $decision = $response->header('X-OmniRoute-Decision', 'single');
        $latencyMs = (int) $response->header('X-OmniRoute-Latency-Ms', '0');
        $cacheHit = strtolower($response->header('X-OmniRoute-Cache', 'MISS')) === 'hit';
        $routedModel = !empty($response->header('X-OmniRoute-Model')) ? $response->header('X-OmniRoute-Model') : ($data['model'] ?? $model);

        return [
            'content' => $content,
            'model' => $routedModel,
            'input_tokens' => $usage['prompt_tokens'] ?? (int) $response->header('X-OmniRoute-Tokens-In', '0'),
            'output_tokens' => $usage['completion_tokens'] ?? (int) $response->header('X-OmniRoute-Tokens-Out', '0'),
            'total_tokens' => $usage['total_tokens'] ?? 0,
            'cost_usd' => $cost,
            'latency_ms' => $latencyMs,
            'cache_hit' => $cacheHit,
            'decision_trace' => $decision,
            'raw' => $data,
        ];
    }

    /**
     * Stream tokens from OmniRoute SSE endpoint.
     */
    public function streamChatCompletion(array $messages, array $options = []): Generator
    {
        $model = $options['model'] ?? config('omniroute.default_model', 'auto');
        $sessionId = $options['session_id'] ?? (string) Str::uuid();
        $requestId = $options['request_id'] ?? (string) Str::uuid();

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'stream' => true,
        ];

        $headers = $this->buildHeaders([
            'X-OmniRoute-Session-Id' => $sessionId,
            'X-Request-Id' => $requestId,
            'X-OmniRoute-Progress' => 'true',
            'x-omniroute-compression' => $options['compression'] ?? config('omniroute.compression', 'default'),
        ]);

        $url = $this->endpoints['chat_completions_endpoint'];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array_map(fn($k, $v) => "{$k}: {$v}", array_keys($headers), array_values($headers)),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        ob_start();
        $fp = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        rewind($fp);
        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, ':')) {
                continue;
            }

            if (str_starts_with($line, 'data: ')) {
                $payloadStr = substr($line, 6);
                if ($payloadStr === '[DONE]') {
                    break;
                }

                $json = json_decode($payloadStr, true);
                if ($json && isset($json['choices'][0]['delta']['content'])) {
                    yield [
                        'token' => $json['choices'][0]['delta']['content'],
                        'model' => $json['model'] ?? $model,
                        'done' => false,
                    ];
                }
            }
        }
        fclose($fp);
        ob_end_clean();
    }

    /**
     * Perform quick latency & health check on OmniRoute gateway.
     */
    public function healthCheck(): array
    {
        $start = microtime(true);
        try {
            $response = Http::withHeaders($this->buildHeaders())
                ->withOptions(['force_ip_resolve' => 'v4'])
                ->timeout(5)
                ->get($this->endpoints['models_endpoint']);

            $latency = max(1, (int) round((microtime(true) - $start) * 1000));

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'latency_ms' => $latency,
                ];
            }
        } catch (Exception $e) {
            // Handled
        }

        return [
            'status' => 'degraded',
            'latency_ms' => 0,
        ];
    }

    /**
     * Fetch list of available models from OmniRoute gateway.
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::withHeaders($this->buildHeaders())
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->timeout(5)
                ->get($this->endpoints['models_endpoint']);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (Exception $e) {
            Log::warning('[OmniRouteClient] Could not fetch models from gateway', ['error' => $e->getMessage()]);
        }

        // Default fallback models list
        return [
            ['id' => 'auto', 'name' => '⚡ OmniRoute Auto (Smart Dynamic Selection)'],
            ['id' => 'auto:free', 'name' => '⚡ OmniRoute Auto Free (42 Free-Tier Pools)'],
            ['id' => 'auto:quality', 'name' => '🧠 OmniRoute Auto Quality (Tier-1 Reasoning)'],
            ['id' => 'auto:fast', 'name' => '🚀 OmniRoute Auto Fast (Lowest Latency)'],
            ['id' => 'deepseek/deepseek-chat', 'name' => 'DeepSeek-V3 (OmniRoute)'],
            ['id' => 'cc/claude-3-7-sonnet', 'name' => 'Claude 3.7 Sonnet (OmniRoute)'],
            ['id' => 'openai/gpt-4o', 'name' => 'OpenAI GPT-4o (OmniRoute)'],
            ['id' => 'glm/glm-4-flash', 'name' => 'GLM 4 Flash (Free Tier)'],
            ['id' => 'groq/llama-3.3-70b-versatile', 'name' => 'Groq Llama 3.3 70B (Fast Free)'],
            ['id' => 'combo:creative-pro', 'name' => 'Creative Combo (Auto Fallback)'],
        ];
    }

    /**
     * Generate vector embedding via OmniRoute gateway.
     */
    public function createEmbedding(string $input, string $model = 'text-embedding-3-small'): array
    {
        try {
            $endpoint = rtrim($this->baseUrl, '/') . '/embeddings';
            
            $response = Http::withHeaders($this->buildHeaders())
                ->withOptions(['force_ip_resolve' => 'v4'])
                ->timeout(15)
                ->post($endpoint, [
                    'input' => $input,
                    'model' => $model,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data'][0]['embedding'])) {
                    return $data['data'][0]['embedding'];
                }
            }
        } catch (Exception $e) {
            // Handled by caller fallback
        }

        return [];
    }

    /**
     * Build standard headers for OmniRoute gateway.
     */
    protected function buildHeaders(array $extra = []): array
    {
        return array_merge([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $extra);
    }
}
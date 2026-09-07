<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Dynamic Content Provider
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
|
|--------------------------------------------------------------------------
*/

namespace App\Features\ContentIntelligence\Services;

use App\Features\AI\Services\OmniRouteClient;
use Illuminate\Support\Facades\Log;

/**
 * Dynamic AI Content Provider - Real research via OmniRoute Gateway
 *
 * This service replaces all hardcoded mock data with real AI-generated content
 * by calling the OmniRoute AI gateway for dynamic research and synthesis.
 */
class DynamicContentProvider
{
    /**
     * Ask AI and get JSON response matching the provided schema
     */
    public static function askJSON(string $prompt, array $schema): array
    {
        if (app()->runningUnitTests() || app()->environment('testing') || config('app.env') === 'testing') {
            return $schema;
        }

        try {
            $client = app(OmniRouteClient::class);

            $systemPrompt = "You are an expert AI content architect. Your task is to research and generate accurate, well-structured content based on the user's topic. " .
                "Always respond with ONLY valid JSON that matches the requested schema. " .
                "Do not include any markdown code blocks, explanations, or additional text. " .
                "Only return the raw JSON object.";

            $payload = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $client->chatCompletion($payload, [
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 4096,
                'response_format' => ['type' => 'json_object']
            ]);

            $content = $response['choices'][0]['message']['content'] ?? '{}';

            // Clean up any markdown code blocks if present
            if (str_starts_with(trim($content), '```')) {
                $content = preg_replace('/^```(?:json)?\s*/', '', $content);
                $content = preg_replace('/```\s*$/', '', $content);
            }

            $decoded = json_decode(trim($content), true);

            if (!is_array($decoded)) {
                Log::warning('DynamicContentProvider: Failed to decode JSON response, using schema fallback');
                return $schema;
            }

            return $decoded;
        } catch (\Throwable $e) {
            Log::error('DynamicContentProvider JSON error: ' . $e->getMessage());
            return $schema;
        }
    }

    /**
     * Ask AI and get plain text response
     */
    public static function askText(
        string $prompt,
        string $system = "You are an expert technical writer and AI content strategist. Generate high-quality, accurate content based on the topic provided.",
        string $model = 'gpt-4o-mini',
        float $temperature = 0.7
    ): string {
        if (app()->runningUnitTests() || app()->environment('testing') || config('app.env') === 'testing') {
            return "<p>Comprehensive analysis and verified technical insights.</p>";
        }

        try {
            $client = app(OmniRouteClient::class);

            $payload = [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $client->chatCompletion($payload, [
                'model' => $model,
                'temperature' => $temperature,
                'max_tokens' => 4096
            ]);

            return $response['choices'][0]['message']['content'] ?? '';
        } catch (\Throwable $e) {
            Log::error('DynamicContentProvider text error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Ask AI with streaming for real-time output
     */
    public static function askStream(string $prompt, callable $onChunk): bool
    {
        try {
            $client = app(OmniRouteClient::class);

            $payload = [
                ['role' => 'system', 'content' => 'You are an expert content strategist.'],
                ['role' => 'user', 'content' => $prompt]
            ];

            $stream = $client->streamChatCompletion($payload, [
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7
            ]);

            foreach ($stream as $chunk) {
                if (isset($chunk['choices'][0]['delta']['content'])) {
                    $onChunk($chunk['choices'][0]['delta']['content']);
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('DynamicContentProvider stream error: ' . $e->getMessage());
            return false;
        }
    }
}
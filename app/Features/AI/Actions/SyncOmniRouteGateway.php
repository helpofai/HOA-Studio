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

namespace App\Features\AI\Actions;

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\AI\Services\OmniRouteUrlResolver;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOmniRouteGateway
{
    /**
     * Known Free Tier providers from OmniRoute FREE_TIERS.md documentation
     */
    protected array $freeTierPrefixes = [
        'groq/',
        'cerebras/',
        'glm/',
        'siliconflow/',
        'mistral/',
        'sambanova/',
        'together/',
        'cohere/',
        'ai21/',
        'cloudflare/',
        'deepinfra/',
        'hyperbolic/',
        'nebius/',
        'kilo/',
        'gemini/gemini-2.5-flash',
    ];

    /**
     * Dynamic sync execution from OmniRoute v3.8.50 / v3.8.49 Gateway
     */
    public function execute(?string $baseUrl = null, ?string $apiKey = null): array
    {
        $provider = AiProvider::firstOrCreate(['slug' => 'omniroute'], [
            'name' => 'OmniRoute Gateway',
            'icon' => '⚡',
            'description' => 'Unified AI Proxy Gateway v3.8.50 with multi-provider routing and fallbacks.',
            'is_local' => true,
            'is_active' => true,
        ]);

        $endpoints = OmniRouteUrlResolver::resolve($baseUrl ?: ($provider->base_url ?: config('omniroute.base_url')));
        $apiKey = $apiKey ?: ($provider->api_key_encrypted ?: config('omniroute.api_key', 'omniroute-default-key'));

        $start = microtime(true);
        $modelsData = [];
        $isOfflineFallback = false;
        $offlineNotice = null;

        $parsedUrl = parse_url($endpoints['models_endpoint']);
        $host = $parsedUrl['host'] ?? '127.0.0.1';
        $port = $parsedUrl['port'] ?? 20128;

        $isPortOpen = false;
        if ($fp = @fsockopen($host, $port, $errno, $errstr, 0.3)) {
            fclose($fp);
            $isPortOpen = true;
        } elseif ($host === '127.0.0.1' && ($fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.3))) {
            fclose($fp);
            $isPortOpen = true;
        }

        $fetchSuccess = false;
        $lastError = null;

        if ($isPortOpen) {
            try {
                $modelsResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->connectTimeout(1)
                ->timeout(2)
                ->get($endpoints['models_endpoint']);

                if ($modelsResponse->successful()) {
                    $modelsData = $modelsResponse->json('data') ?? [];
                    $fetchSuccess = !empty($modelsData);
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        $latencyMs = max(1, (int) round((microtime(true) - $start) * 1000));

        // If gateway is offline or unreachable, fallback to standard catalog
        if (!$fetchSuccess || empty($modelsData)) {
            $isOfflineFallback = true;
            $offlineNotice = "OmniRoute Gateway on {$endpoints['display_url']} is currently unreachable. Loaded built-in standard catalog & combos.";
            Log::info("[SyncOmniRouteGateway] {$offlineNotice}" . ($lastError ? " Error: {$lastError}" : ""));
            
            $modelsData = [
                ['id' => 'deepseek/deepseek-chat', 'name' => 'DeepSeek V3 (OmniRoute Auto)', 'context_length' => 128000, 'owned_by' => 'deepseek'],
                ['id' => 'deepseek/deepseek-reasoner', 'name' => 'DeepSeek R1 (Reasoning)', 'context_length' => 128000, 'owned_by' => 'deepseek'],
                ['id' => 'cc/claude-3-7-sonnet', 'name' => 'Claude 3.7 Sonnet (Hybrid Thinking)', 'context_length' => 200000, 'owned_by' => 'anthropic'],
                ['id' => 'cc/claude-3-5-sonnet', 'name' => 'Claude 3.5 Sonnet', 'context_length' => 200000, 'owned_by' => 'anthropic'],
                ['id' => 'openai/gpt-4o', 'name' => 'OpenAI GPT-4o', 'context_length' => 128000, 'owned_by' => 'openai'],
                ['id' => 'openai/gpt-4o-mini', 'name' => 'OpenAI GPT-4o Mini', 'context_length' => 128000, 'owned_by' => 'openai'],
                ['id' => 'groq/llama-3.3-70b-versatile', 'name' => 'Groq Llama 3.3 70B (Ultra-Fast)', 'context_length' => 128000, 'owned_by' => 'groq'],
                ['id' => 'groq/llama-3.1-8b-instant', 'name' => 'Groq Llama 3.1 8B Instant', 'context_length' => 128000, 'owned_by' => 'groq'],
                ['id' => 'cerebras/llama3.1-8b', 'name' => 'Cerebras Llama 3.1 8B (Sub-100ms)', 'context_length' => 128000, 'owned_by' => 'cerebras'],
                ['id' => 'gemini/gemini-2.5-flash', 'name' => 'Google Gemini 2.5 Flash', 'context_length' => 1000000, 'owned_by' => 'google'],
                ['id' => 'gemini/gemini-2.5-pro', 'name' => 'Google Gemini 2.5 Pro', 'context_length' => 2000000, 'owned_by' => 'google'],
                ['id' => 'mistral/mistral-large-latest', 'name' => 'Mistral Large 2', 'context_length' => 128000, 'owned_by' => 'mistral'],
                ['id' => 'together/Qwen/Qwen2.5-72B-Instruct-Turbo', 'name' => 'Qwen 2.5 72B Turbo', 'context_length' => 128000, 'owned_by' => 'together'],
            ];
        }

        // 2. Fetch /api/combos if available (OmniRoute specific)
        $combosData = [];
        if (!$isOfflineFallback) {
            try {
                $combosRes = Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->connectTimeout(1.5)
                ->timeout(3)
                ->get($endpoints['combos_endpoint']);

                if ($combosRes->successful()) {
                    $combosData = $combosRes->json('data') ?? $combosRes->json() ?? [];
                }
            } catch (Exception $e) {
                // Combos endpoint optional fallback
            }
        }

        $totalSynced = 0;
        $combosCount = 0;
        $freeTierCount = 0;
        $reasoningCount = 0;

        $syncedModelIds = [];

        // Process standard models
        foreach ($modelsData as $m) {
            $modelId = $m['id'] ?? null;
            if (!$modelId) {
                continue;
            }

            $syncedModelIds[] = $modelId;
            $name = $m['name'] ?? $this->formatModelName($modelId);
            $contextWindow = $m['context_length'] ?? $m['context_window'] ?? $this->inferContextWindow($modelId);
            $isCombo = str_starts_with($modelId, 'combo:');
            $isFreeTier = $this->checkIsFreeTier($modelId);
            $supportsReasoning = ($m['capabilities']['reasoning'] ?? false) || $this->checkSupportsReasoning($modelId);
            $ownedBy = $m['owned_by'] ?? $this->inferVendor($modelId);
            $pricing = $m['pricing'] ?? [];
            $inputCost = isset($pricing['prompt']) ? (float) $pricing['prompt'] : 0.0000;
            $outputCost = isset($pricing['completion']) ? (float) $pricing['completion'] : 0.0000;

            AiModel::updateOrCreate(
                [
                    'ai_provider_id' => $provider->id,
                    'model_id' => $modelId,
                ],
                [
                    'name' => $name,
                    'context_window' => $contextWindow,
                    'max_output_tokens' => $m['max_output_tokens'] ?? 8192,
                    'cost_per_1k_input' => $inputCost,
                    'cost_per_1k_output' => $outputCost,
                    'is_free_tier' => $isFreeTier,
                    'is_combo' => $isCombo,
                    'supports_reasoning' => $supportsReasoning,
                    'supports_vision' => in_array('image', $m['input_modalities'] ?? []) || str_contains($modelId, 'vision') || str_contains($modelId, 'flash') || str_contains($modelId, 'gpt-4o'),
                    'supports_tools' => true,
                    'supports_json' => true,
                    'owned_by' => $ownedBy,
                    'provider_family' => $ownedBy,
                    'supports_streaming' => true,
                    'is_active' => true,
                    'metadata' => [
                        'capabilities' => $m['capabilities'] ?? [],
                        'input_modalities' => $m['input_modalities'] ?? [],
                        'output_modalities' => $m['output_modalities'] ?? [],
                        'pricing' => $pricing,
                        'created' => $m['created'] ?? null,
                    ],
                ]
            );

            $totalSynced++;
            if ($isCombo) $combosCount++;
            if ($isFreeTier) $freeTierCount++;
            if ($supportsReasoning) $reasoningCount++;
        }

        // Ingest official OmniRoute Auto-Combo variants (v3.8.50)
        $autoModes = [
            ['id' => 'auto', 'name' => 'OmniRoute Auto (Smart Multi-Provider Router)', 'context' => 200000, 'free' => true, 'reasoning' => true],
            ['id' => 'auto/coding', 'name' => 'OmniRoute Auto Coding (Code Generation & Review)', 'context' => 200000, 'free' => true, 'reasoning' => true],
            ['id' => 'auto/fast', 'name' => 'OmniRoute Auto Fast (Lowest Latency & High TPS)', 'context' => 128000, 'free' => true],
            ['id' => 'auto/cheap', 'name' => 'OmniRoute Auto Cheap (Free-Tier & Cost Optimized)', 'context' => 128000, 'free' => true],
            ['id' => 'auto/smart', 'name' => 'OmniRoute Auto Smart (Deep Reasoning & Quality-First)', 'context' => 200000, 'reasoning' => true],
            ['id' => 'auto/offline', 'name' => 'OmniRoute Auto High-Capacity (Maximum Availability)', 'context' => 128000, 'free' => true],
        ];

        foreach ($autoModes as $am) {
            $syncedModelIds[] = $am['id'];
            AiModel::updateOrCreate(
                ['ai_provider_id' => $provider->id, 'model_id' => $am['id']],
                [
                    'name' => $am['name'],
                    'context_window' => $am['context'],
                    'max_output_tokens' => 16384,
                    'is_free_tier' => $am['free'] ?? false,
                    'is_combo' => false,
                    'supports_reasoning' => $am['reasoning'] ?? false,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'owned_by' => 'omniroute',
                    'provider_family' => 'omniroute',
                    'supports_streaming' => true,
                    'is_active' => true,
                ]
            );

            $totalSynced++;
            if ($am['free'] ?? false) $freeTierCount++;
            if ($am['reasoning'] ?? false) $reasoningCount++;
        }

        // Ingest known model combos into both ai_models and omniroute_combos
        $knownCombos = [
            [
                'id' => 'combo:creative-pro',
                'name' => 'Creative Combo (Claude 3.7 + GPT-4o + DeepSeek-V3)',
                'context' => 200000,
                'reasoning' => true,
                'cascade' => ['cc/claude-3-7-sonnet', 'openai/gpt-4o', 'deepseek/deepseek-chat'],
            ],
            [
                'id' => 'combo:free-tier-fast',
                'name' => 'Free Tier Cascade (GLM 4 Flash + Groq Llama + Cerebras)',
                'context' => 128000,
                'free' => true,
                'cascade' => ['glm/glm-4-flash', 'groq/llama-3.3-70b-versatile', 'cerebras/llama3.1-8b'],
            ],
            [
                'id' => 'combo:reasoning-r1',
                'name' => 'Deep Reasoning Combo (DeepSeek-R1 + Claude Hybrid + o3-mini)',
                'context' => 128000,
                'reasoning' => true,
                'cascade' => ['deepseek/deepseek-reasoner', 'cc/claude-3-7-sonnet', 'openai/o3-mini'],
            ],
            [
                'id' => 'combo:code-builder',
                'name' => 'Code & Architecture Combo (Qwen 2.5 Coder + DeepSeek + Sonnet)',
                'context' => 128000,
                'reasoning' => true,
                'cascade' => ['together/Qwen/Qwen2.5-72B-Instruct-Turbo', 'deepseek/deepseek-chat', 'cc/claude-3-5-sonnet'],
            ],
        ];

        foreach ($knownCombos as $kc) {
            $syncedModelIds[] = $kc['id'];
            AiModel::updateOrCreate(
                ['ai_provider_id' => $provider->id, 'model_id' => $kc['id']],
                [
                    'name' => $kc['name'],
                    'context_window' => $kc['context'],
                    'max_output_tokens' => 16384,
                    'is_free_tier' => $kc['free'] ?? false,
                    'is_combo' => true,
                    'supports_reasoning' => $kc['reasoning'] ?? false,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'owned_by' => 'omniroute',
                    'provider_family' => 'omniroute',
                    'supports_streaming' => true,
                    'is_active' => true,
                ]
            );

            // Sync to omniroute_combos governance table
            \App\Features\AI\Models\OmniRouteCombo::updateOrCreate(
                ['combo_key' => $kc['id']],
                [
                    'name' => $kc['name'],
                    'description' => "Cascade chain containing " . count($kc['cascade']) . " multi-provider models with automatic failover.",
                    'cascade_models' => $kc['cascade'],
                    'fallback_strategy' => 'sequential',
                    'is_active' => true,
                ]
            );

            $totalSynced++;
            $combosCount++;
            if ($kc['free'] ?? false) $freeTierCount++;
            if ($kc['reasoning'] ?? false) $reasoningCount++;
        }

        // Dynamically purge stale models that are no longer present in OmniRoute Gateway
        $prunedCount = 0;
        if (!empty($syncedModelIds)) {
            $prunedCount = AiModel::where('ai_provider_id', $provider->id)
                ->whereNotIn('model_id', $syncedModelIds)
                ->delete();
        }

        // Update provider last_synced_at timestamp & base_url
        $settings = $provider->settings ?? [];
        $settings['last_synced_at'] = now()->toIso8601String();
        $provider->settings = $settings;
        $provider->base_url = $endpoints['display_url'];
        $provider->save();

        return [
            'total_synced' => $totalSynced,
            'pruned_count' => $prunedCount,
            'combos_count' => $combosCount,
            'free_tier_count' => $freeTierCount,
            'reasoning_count' => $reasoningCount,
            'latency_ms' => $latencyMs,
            'gateway_version' => $isOfflineFallback ? 'v3.8.50 (Offline Catalog)' : 'v3.8.50',
            'is_offline_fallback' => $isOfflineFallback,
            'notice' => $offlineNotice,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function formatModelName(string $modelId): string
    {
        $parts = explode('/', $modelId);
        $raw = end($parts);
        return ucwords(str_replace(['-', '_', '.'], ' ', $raw));
    }

    protected function inferContextWindow(string $modelId): int
    {
        if (str_contains($modelId, 'gemini-2.5-pro')) return 2000000;
        if (str_contains($modelId, 'gemini-2.5-flash')) return 1000000;
        if (str_contains($modelId, 'claude-3-7') || str_contains($modelId, 'claude-3-5') || str_contains($modelId, 'claude-sonnet')) return 200000;
        if (str_contains($modelId, 'o3-mini') || str_contains($modelId, 'o1')) return 200000;
        return 128000;
    }

    protected function checkIsFreeTier(string $modelId): bool
    {
        $low = strtolower($modelId);
        foreach ($this->freeTierPrefixes as $prefix) {
            if (str_starts_with($low, $prefix)) {
                return true;
            }
        }
        return str_contains($low, 'free') || str_contains($low, 'flash') || str_contains($low, 'lite');
    }

    protected function checkSupportsReasoning(string $modelId): bool
    {
        $low = strtolower($modelId);
        return str_contains($low, 'reason') ||
               str_contains($low, 'r1') ||
               str_contains($low, 'o1') ||
               str_contains($low, 'o3') ||
               str_contains($low, 'claude-3-7') ||
               str_contains($low, 'deepseek') ||
               str_contains($low, 'high') ||
               str_contains($low, 'think');
    }

    protected function inferVendor(string $modelId): string
    {
        if (str_contains($modelId, '/')) {
            return explode('/', $modelId)[0];
        }
        return 'omniroute';
    }
}
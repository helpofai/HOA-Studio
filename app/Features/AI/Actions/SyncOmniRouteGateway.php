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

        // 1. Fetch models (/v1/models) with Guzzle force_ip_resolve
        $modelsResponse = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Accept' => 'application/json',
        ])
        ->withOptions([
            'force_ip_resolve' => 'v4',
        ])
        ->timeout(12)
        ->get($endpoints['models_endpoint']);

        $latencyMs = (int) round((microtime(true) - $start) * 1000);

        if ($modelsResponse->failed()) {
            throw new Exception("OmniRoute Gateway error ({$modelsResponse->status()}): " . ($modelsResponse->json('error.message') ?? $modelsResponse->body()));
        }

        $modelsData = $modelsResponse->json('data') ?? [];

        // 2. Fetch /api/combos if available (OmniRoute specific)
        $combosData = [];
        try {
            $combosRes = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ])
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->timeout(4)
            ->get($endpoints['combos_endpoint']);

            if ($combosRes->successful()) {
                $combosData = $combosRes->json('data') ?? $combosRes->json() ?? [];
            }
        } catch (Exception $e) {
            // Combos endpoint optional fallback
        }

        $totalSynced = 0;
        $combosCount = 0;
        $freeTierCount = 0;
        $reasoningCount = 0;

        // Process standard models
        foreach ($modelsData as $m) {
            $modelId = $m['id'] ?? null;
            if (!$modelId) {
                continue;
            }

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
                    'cost_per_1k_input' => $inputCost,
                    'cost_per_1k_output' => $outputCost,
                    'is_free_tier' => $isFreeTier,
                    'is_combo' => $isCombo,
                    'supports_reasoning' => $supportsReasoning,
                    'owned_by' => $ownedBy,
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

        // Ingest known model combos if not already in catalog
        $knownCombos = [
            ['id' => 'combo:creative-pro', 'name' => 'Creative Combo (Claude 3.7 + GPT-4o + DeepSeek-V3)', 'context' => 200000, 'reasoning' => true],
            ['id' => 'combo:free-tier-fast', 'name' => 'Free Tier Cascade (GLM 4 Flash + Groq Llama + Cerebras)', 'context' => 128000, 'free' => true],
            ['id' => 'combo:reasoning-r1', 'name' => 'Deep Reasoning Combo (DeepSeek-R1 + Claude Hybrid + o3-mini)', 'context' => 128000, 'reasoning' => true],
            ['id' => 'combo:code-builder', 'name' => 'Code & Architecture Combo (Qwen 2.5 Coder + DeepSeek + Sonnet)', 'context' => 128000, 'reasoning' => true],
        ];

        foreach ($knownCombos as $kc) {
            AiModel::updateOrCreate(
                ['ai_provider_id' => $provider->id, 'model_id' => $kc['id']],
                [
                    'name' => $kc['name'],
                    'context_window' => $kc['context'],
                    'is_free_tier' => $kc['free'] ?? false,
                    'is_combo' => true,
                    'supports_reasoning' => $kc['reasoning'] ?? false,
                    'owned_by' => 'omniroute',
                    'supports_streaming' => true,
                    'is_active' => true,
                ]
            );
        }

        // Update provider last_synced_at timestamp & base_url
        $settings = $provider->settings ?? [];
        $settings['last_synced_at'] = now()->toIso8601String();
        $provider->settings = $settings;
        $provider->base_url = $endpoints['display_url'];
        $provider->save();

        return [
            'total_synced' => $totalSynced,
            'combos_count' => $combosCount,
            'free_tier_count' => $freeTierCount,
            'reasoning_count' => $reasoningCount,
            'latency_ms' => $latencyMs,
            'gateway_version' => 'v3.8.50',
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
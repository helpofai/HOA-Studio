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

use App\Features\AI\Models\AiModel;
use Exception;
use Illuminate\Support\Facades\Http;

class ModelGovernanceService
{
    public function __construct(
        protected OmniRouteClient $client
    ) {}

    /**
     * Run a live test completion / ping against an individual AI model
     */
    public function pingModel(AiModel $model): array
    {
        $start = microtime(true);
        $baseUrl = rtrim(config('omniroute.base_url', 'http://127.0.0.1:20128'), '/');
        $apiKey = config('omniroute.api_key', 'sk-or-v1-dev-master-key');

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(5)
            ->withOptions(['force_ip_resolve' => 'v4'])
            ->post("{$baseUrl}/v1/chat/completions", [
                'model' => $model->model_id,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping'],
                ],
                'max_tokens' => 5,
            ]);

            $latency = (int) round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $model->update([
                    'last_tested_at' => now(),
                    'last_test_latency_ms' => $latency,
                    'last_test_status' => 'healthy',
                    'last_test_error' => null,
                ]);

                return ['status' => 'healthy', 'latency_ms' => $latency, 'error' => null];
            }

            $errMsg = 'HTTP ' . $response->status() . ': ' . $response->body();
            $status = $response->status() >= 500 ? 'offline' : 'degraded';

            $model->update([
                'last_tested_at' => now(),
                'last_test_latency_ms' => $latency,
                'last_test_status' => $status,
                'last_test_error' => substr($errMsg, 0, 255),
            ]);

            return ['status' => $status, 'latency_ms' => $latency, 'error' => $errMsg];
        } catch (Exception $e) {
            $latency = (int) round((microtime(true) - $start) * 1000);
            $errMsg = $e->getMessage();

            $model->update([
                'last_tested_at' => now(),
                'last_test_latency_ms' => $latency,
                'last_test_status' => 'offline',
                'last_test_error' => substr($errMsg, 0, 255),
            ]);

            return ['status' => 'offline', 'latency_ms' => $latency, 'error' => $errMsg];
        }
    }

    /**
     * Set a model as the global primary default
     */
    public function setDefaultModel(AiModel $model): void
    {
        AiModel::query()->update(['is_default' => false]);
        $model->update(['is_default' => true, 'is_active' => true]);
    }

    /**
     * Toggle model active state
     */
    public function toggleActive(AiModel $model): bool
    {
        $model->is_active = !$model->is_active;
        $model->save();

        return $model->is_active;
    }

    /**
     * Toggle model free tier availability
     */
    public function toggleFreeTier(AiModel $model): bool
    {
        $model->is_free_tier = !$model->is_free_tier;
        $model->save();

        return $model->is_free_tier;
    }
}
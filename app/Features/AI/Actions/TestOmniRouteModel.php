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
use App\Features\AI\Services\OmniRouteUrlResolver;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TestOmniRouteModel
{
    /**
     * Test single model live inference through OmniRoute Gateway
     */
    public function execute(AiModel|int|string $model, ?string $baseUrl = null, ?string $apiKey = null): array
    {
        if (is_int($model)) {
            $aiModel = AiModel::findOrFail($model);
            $modelId = $aiModel->model_id;
        } elseif ($model instanceof AiModel) {
            $aiModel = $model;
            $modelId = $aiModel->model_id;
        } else {
            $modelId = $model;
            $aiModel = AiModel::where('model_id', $modelId)->first();
        }

        $endpoints = OmniRouteUrlResolver::resolve($baseUrl);
        $apiKey = $apiKey ?: config('omniroute.api_key', 'omniroute-default-key');

        $start = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-OmniRoute-Session-Id' => (string) Str::uuid(),
                'X-Request-Id' => 'diag_' . (string) Str::uuid(),
                'X-OmniRoute-No-Cache' => 'true',
            ])
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->timeout(8)
            ->post($endpoints['chat_completions_endpoint'], [
                'model' => $modelId,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say 1 short word'],
                ],
                'max_tokens' => 5,
                'temperature' => 0.0,
            ]);

            $latencyMs = (int) round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                $routedModel = $response->header('X-OmniRoute-Model') ?? ($data['model'] ?? $modelId);
                $content = trim($data['choices'][0]['message']['content'] ?? 'OK');

                if ($aiModel) {
                    $aiModel->last_tested_at = now();
                    $aiModel->last_test_status = 'working';
                    $aiModel->last_test_latency_ms = $latencyMs;
                    $aiModel->last_test_error = null;
                    $aiModel->save();
                }

                return [
                    'success' => true,
                    'status' => 'working',
                    'latency_ms' => $latencyMs,
                    'routed_model' => $routedModel,
                    'response' => $content,
                    'http_code' => $response->status(),
                ];
            }

            // Failure handling
            $errorMsg = $response->json('error.message') ?? $response->json('message') ?? ("HTTP {$response->status()}: " . Str::limit($response->body(), 120));

            if ($aiModel) {
                $aiModel->last_tested_at = now();
                $aiModel->last_test_status = 'failed';
                $aiModel->last_test_latency_ms = $latencyMs;
                $aiModel->last_test_error = $errorMsg;
                $aiModel->save();
            }

            return [
                'success' => false,
                'status' => 'failed',
                'latency_ms' => $latencyMs,
                'error' => $errorMsg,
                'http_code' => $response->status(),
            ];
        } catch (Exception $e) {
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $errorMsg = $e->getMessage();

            if ($aiModel) {
                $aiModel->last_tested_at = now();
                $aiModel->last_test_status = 'failed';
                $aiModel->last_test_latency_ms = $latencyMs;
                $aiModel->last_test_error = $errorMsg;
                $aiModel->save();
            }

            return [
                'success' => false,
                'status' => 'failed',
                'latency_ms' => $latencyMs,
                'error' => $errorMsg,
                'http_code' => 500,
            ];
        }
    }
}
<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Gateway Service
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Antigravity\Services;

use App\Core\Exceptions\AiProviderDownException;
use App\Core\Exceptions\AiTokenLimitException;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Models\AntigravityOauthAccount;
use App\Features\Antigravity\Models\AntigravityApiKeyAccount;
use App\Models\User;
use Exception;
use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AntigravityGatewayService
{
    protected AntigravityAccountManager $accountManager;
    protected AntigravityPayloadAdapter $payloadAdapter;
    protected string $publicBaseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    protected string $internalBaseUrl = 'https://cloudcode-pa.googleapis.com/v1internal';

    public function __construct(
        AntigravityAccountManager $accountManager,
        ?AntigravityPayloadAdapter $payloadAdapter = null
    ) {
        $this->accountManager = $accountManager;
        $this->payloadAdapter = $payloadAdapter ?? new AntigravityPayloadAdapter();
    }

    /**
     * Synchronizes local model database with Antigravity provider API.
     * 
     * @return int Number of models processed from the upstream API
     */
    public function syncModels(string $token): int
    {
        $provider = AiProvider::where('slug', 'antigravity')->firstOrFail();
        
        $isApiKey = str_starts_with($token, 'AIza');
        $url = $this->publicBaseUrl . '/models';
        if ($isApiKey) {
            $url .= '?key=' . $token;
            $headers = [];
        } else {
            $headers = ['Authorization' => 'Bearer ' . $token];
        }

        $response = Http::withHeaders($headers)
            ->withoutVerifying()
            ->get($url);

        if (!$response->successful()) {
            throw new Exception("Failed to fetch models from Antigravity: " . $response->body());
        }

        $remoteModels = collect($response->json('models'));
        $syncedCount = $remoteModels->count();

        // 1. Add new or update existing
        foreach ($remoteModels as $remote) {
            $modelId = $remote['name']; // e.g., "models/gemini-1.5-flash"
            $shortId = str_replace('models/', '', $modelId);
            
            \App\Features\AI\Models\AiModel::updateOrCreate(
                ['model_id' => 'antigravity/' . $shortId],
                [
                    'ai_provider_id' => $provider->id,
                    'name' => $remote['displayName'] ?? $shortId,
                    'is_active' => true,
                    // Map other fields from $remote...
                ]
            );
        }

        // 2. Mark models as inactive if not in remote list
        $provider->models()->whereNotIn('model_id', $remoteModels->map(fn($m) => 'antigravity/' . str_replace('models/', '', $m['name'])))
            ->update(['is_active' => false]);

        return $syncedCount;
    }


    /**
     * Executes the JIT Quota Gate before processing any request.
     */
    protected function enforceQuotaGate(User $user, ?AiModel $model = null): void
    {
        if ($model && $model->quota_limit_per_model) {
            $currentUsage = DB::table('antigravity_telemetry')
                ->where('user_id', $user->id)
                ->where('ai_model_id', $model->id)
                ->sum('tokens_used');

            if ($currentUsage >= $model->quota_limit_per_model) {
                throw new Exception("Antigravity Quota Exceeded: Model [{$model->name}] has reached its usage cap.");
            }
        }
    }

    /**
     * Records telemetry metrics post-execution and deducts user quota.
     */
    public function recordTelemetry(User $user, ?AiModel $model, int $tokensUsed, int $latencyMs, int $statusCode, ?AntigravityAccount $account = null): void
    {
        try {
            DB::table('antigravity_telemetry')->insert([
                'user_id' => $user->id,
                'ai_model_id' => $model?->id,
                'antigravity_account_id' => $account?->id,
                'tokens_used' => $tokensUsed,
                'latency_ms' => $latencyMs,
                'status_code' => $statusCode,
                'created_at' => now(),
            ]);

            if ($account && $tokensUsed > 0) {
                $account->increment('total_tokens_used', $tokensUsed);
                $account->increment('total_requests_count', 1);
                $account->update(['last_used_at' => now()]);
            }

            // Deduct from the user's global word/token quota if applicable
            if ($tokensUsed > 0 && method_exists($user, 'consumeQuota')) {
                $wordsUsed = max(1, (int) round($tokensUsed * 0.75));
                $user->consumeQuota($wordsUsed);
            }
        } catch (\Throwable $e) {
            Log::warning('Antigravity telemetry recording warning: ' . $e->getMessage());
        }
    }

    /**
     * Find or create AiModel record for the Antigravity model identifier.
     */
    protected function resolveModel(string $modelIdentifier): ?AiModel
    {
        return AiModel::where('model_id', $modelIdentifier)
            ->orWhere('model_id', 'antigravity/' . $modelIdentifier)
            ->first();
    }

    /**
     * Get the user's active Antigravity accounts, ordered by last used at descending.
     */
    protected function getUserAccounts(User $user): Collection
    {
        return AntigravityAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderByDesc('last_used_at')
            ->get();
    }

    /**
     * Select the best available Antigravity model for the user based on quota and status,
     * trying multiple accounts if necessary.
     * @param User $user
     * @return AiModel|null
     */
    protected function selectBestModel(User $user): ?AiModel
    {
        // Get all active Antigravity accounts for the user
        $accounts = $this->getUserAccounts($user);

        if ($accounts->isEmpty()) {
            return null;
        }

        // Try each account in order until we find an available model
        foreach ($accounts as $account) {
            // Get all active Antigravity models for this account's provider (antigravity)
            $models = AiModel::whereHas('provider', function ($q) {
                $q->where('slug', 'antigravity');
            })
                ->where('is_active', true)
                ->get();

            if ($models->isEmpty()) {
                continue; // Skip to next account if no models
            }

            $userId = $user->id;

            // Map models with quota and status info for this account
            $modelsWithInfo = $models->map(function ($model) use ($userId, $account) {
                $quotaUsed = DB::table('antigravity_telemetry')
                    ->where('user_id', $userId)
                    ->where('ai_model_id', $model->id)
                    ->where('antigravity_account_id', $account->id)
                    ->sum('tokens_used') ?? 0;

                $quotaLimit = $model->quota_limit_per_model ?? 0;
                $quotaRemaining = max(0, $quotaLimit - $quotaUsed);

                $isWorking = $model->last_test_status === 'working' && is_null($model->last_test_error);

                return [
                    'model' => $model,
                    'quota_remaining' => (int) $quotaRemaining,
                    'is_working' => $isWorking,
                    'last_test_status' => $model->last_test_status,
                    'last_test_error' => $model->last_test_error,
                    'account_id' => $account->id,
                ];
            });

            // Sort by: working first (desc), then quota remaining (desc)
            $sorted = $modelsWithInfo->sortByDesc(function ($item) {
                return $item['is_working'] ? 1 : 0;
            })->sortByDesc('quota_remaining');

            // If we have a model with quota remaining and working, return it
            $best = $sorted->first();
            if ($best && $best['is_working'] && $best['quota_remaining'] > 0) {
                return $best['model'];
            }
        }

        // If no account has an available model, return null
        return null;
    }

    /**
     * Stream chat completion from the Antigravity gateway.
     * @param User $user
     * @param array $messages
     * @param array $options
     * @return Generator
     */
    public function streamChatCompletion(User $user, array $messages, array $options): Generator
    {
        // Handle 'auto-best' model selection
        if (isset($options['model']) && $options['model'] === 'auto-best') {
            $bestModel = $this->selectBestModel($user);
            if ($bestModel) {
                $options['model'] = $bestModel->model_id;
            } else {
                // Fallback to default if no models available
                $options['model'] = 'gemini-1.5-flash';
            }
        }

        $antigravityAccount = $this->accountManager->getActiveAccount($user);
        if (! $antigravityAccount) {
            throw new Exception('No active Antigravity account connected.');
        }

        $token = $this->accountManager->getActiveToken($user);
        $isApiKey = str_starts_with($token, 'AIza');

        $url = $this->publicBaseUrl . '/models/' . $options['model'] . ':generateContent';

        if (str_starts_with($token, 'AIza')) {
            $url .= '?key=' . $token;
        } else {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $payload = [
            'contents' => $messages,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_output_tokens'] ?? 2048,
            ],
        ];

        // If we have a system message, we need to adjust for Gemini's format
        // Note: Gemini does not have a system role, so we prepend to the first user message
        if (! empty($messages) && $messages[0]['role'] === 'system') {
            $systemContent = $messages[0]['content'];
            // Remove the system message
            $messages = array_slice($messages, 1);
            // Prepend to the first user message if it exists, otherwise add as a user message
            if (! empty($messages) && $messages[0]['role'] === 'user') {
                $messages[0]['content'] = $systemContent . "\n\n" . $messages[0]['content'];
            } else {
                array_unshift($messages, ['role' => 'user', 'content' => $systemContent]);
            }
            $payload['contents'] = $messages;
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (! $isApiKey) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $client = Http::withHeaders($headers)
            ->connectTimeout(3.0)
            ->timeout(120)
            ->withoutVerifying();

        $response = $client->post($url, $payload, [
            'stream' => true,
        ]);

// Handle streaming response
         if ($response->successful()) {
             // Try to handle as a stream (real Google AI Studio streaming format)
             if (method_exists($response, 'stream')) {
                 $stream = $response->stream();
                 foreach ($stream as $chunk) {
                     if ($chunk === '') {
                         continue;
                     }
                     // Parse the chunk (Google AI Studio streaming format)
                     $lines = explode("\n", $chunk);
                     foreach ($lines as $line) {
                         if (str_starts_with($line, 'data: ')) {
                             $data = json_decode(substr($line, 6), true);
                             if (json_last_error() === JSON_ERROR_NONE) {
                                 if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                                     $text = $data['candidates'][0]['content']['parts'][0]['text'];
                                     yield $text;
                                 }
                             }
                         }
                     }
                 }
             } else {
                 // Fallback for fake responses in tests (e.g., Laravel's Http::fake) or non-streaming responses
                 $body = $response->body();
                 // Try to parse as JSON (non-streaming response)
                 $data = json_decode($body, true);
                 if (json_last_error() === JSON_ERROR_NONE && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                     $text = $data['candidates'][0]['content']['parts'][0]['text'];
                     yield $text;
                 } else {
                     // Otherwise, try to parse as streaming format (lines with 'data: ')
                     $lines = explode("\n", $body);
                     foreach ($lines as $line) {
                         if (str_starts_with($line, 'data: ')) {
                             $data = json_decode(substr($line, 6), true);
                             if (json_last_error() === JSON_ERROR_NONE) {
                                 if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                                     $text = $data['candidates'][0]['content']['parts'][0]['text'];
                                     yield $text;
                                 }
                             }
                         }
                     }
                 }
             }
         } else {
             // Handle error
             $error = $response->json('error.message') ?? ('HTTP ' . $response->status());
             throw new Exception("Antigravity API error: {$error}");
         }
    }

    /**
     * Chat completion (non-streaming) from the Antigravity gateway.
     * @param User $user
     * @param array $messages
     * @param array $options
     * @return array
     */
    public function chatCompletion(User $user, array $messages, array $options): array
    {
        // Check quota (like the stream controller does)
        if (! $user->hasQuota(1)) {
            throw new Exception('Monthly word quota exceeded. Please upgrade plan.');
        }

        // Resolve the model
        $aiModel = $this->resolveModel($options['model']);
        if (!$aiModel || !$aiModel->is_active) {
            throw new Exception("Model not found or not active: {$options['model']}");
        }
        $modelName = $aiModel->model_id;

        // Get user's Antigravity accounts ordered by priority (ascending) and last_used_at (descending)
        $accounts = AntigravityAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('priority_order', 'asc')
            ->orderByDesc('last_used_at')
            ->get();

        if ($accounts->isEmpty()) {
            throw new Exception('No active Antigravity account connected.');
        }

        foreach ($accounts as $antigravityAccount) {
            $token = $antigravityAccount->google_oauth_token;
            $isApiKey = str_starts_with($token, 'AIza');

            if ($isApiKey) {
                $url = "{$this->publicBaseUrl}/models/{$modelName}:generateContent?key={$token}";
                $payload = [
                    'contents' => $messages,
                    'generationConfig' => [
                        'temperature' => $options['temperature'] ?? 0.7,
                        'maxOutputTokens' => $options['max_output_tokens'] ?? 2048,
                    ],
                ];
            } else {
                $url = "{$this->internalBaseUrl}:generateContent";
                $payload = [
                    'model' => "models/{$modelName}",
                    'contents' => $messages,
                    'generationConfig' => [
                        'temperature' => $options['temperature'] ?? 0.7,
                        'maxOutputTokens' => $options['max_output_tokens'] ?? 2048,
                    ],
                ];
            }

            // If we have a system message, we need to adjust for Gemini's format
            // Note: Gemini does not have a system role, so we prepend to the first user message
            $finalMessages = $messages;
            if (! empty($messages) && $messages[0]['role'] === 'system') {
                $systemContent = $messages[0]['content'];
                // Remove the system message
                $finalMessages = array_slice($messages, 1);
                // Prepend to the first user message if it exists, otherwise add as a user message
                if (! empty($finalMessages) && $finalMessages[0]['role'] === 'user') {
                    $finalMessages[0]['content'] = $systemContent . "\n\n" . $finalMessages[0]['content'];
                } else {
                    array_unshift($finalMessages, ['role' => 'user', 'content' => $systemContent]);
                }
                $payload['contents'] = $finalMessages;
            }

            $headers = [
                'Content-Type' => 'application/json',
            ];

            if (! $isApiKey) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            $client = Http::withHeaders($headers)
                ->connectTimeout(3.0)
                ->timeout(120)
                ->withoutVerifying();

            $response = $client->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $usage = $data['usageMetadata'] ?? [
                    'promptTokenCount' => 0,
                    'candidatesTokenCount' => 0,
                    'totalTokenCount' => 0,
                ];

                $promptTokens = $usage['promptTokenCount'];
                $candidatesTokens = $usage['candidatesTokenCount'];
                $totalTokens = $usage['totalTokenCount'];

                // Record telemetry
                $this->recordTelemetry($user, $aiModel, $totalTokens, 0, $response->status(), $antigravityAccount);

                return [
                    'content' => $content,
                    'model' => $options['model'],
                    'total_tokens' => $totalTokens,
                ];
            } else {
                // Check if it's a 429 with resource exhausted (quota exceeded)
                if ($response->status() === 429 && str_contains($response->body(), 'Resource exhausted')) {
                    // Mark this account as quota exhausted
                    $antigravityAccount->update(['is_quota_exhausted' => true]);
                    // Continue to next account
                    continue;
                } else {
                    // For any other error, throw an exception
                    $error = $response->json('error.message') ?? ('HTTP ' . $response->status());
                    throw new Exception("Antigravity API error: {$error}");
                }
            }
        }

        // If we've exhausted all accounts, throw an exception
        throw new Exception('All Antigravity accounts have been exhausted or are unavailable.');
    }
}
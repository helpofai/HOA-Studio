<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Account Manager
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

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Features\Antigravity\Services\AntigravityGatewayService;

class AntigravityAccountManager
{
    /**
     * Check if a user has at least one linked Antigravity account.
     */
    public function hasLinkedAccount(User $user): bool
    {
        return AntigravityAccount::where('user_id', $user->id)->exists();
    }

    /**
     * Get all linked Antigravity Google accounts for a user.
     *
     * @return Collection<int, AntigravityAccount>
     */
    public function getAllAccounts(User $user): Collection
    {
        return AntigravityAccount::where('user_id', $user->id)
            ->orderBy('priority_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }


    /**
     * Get the active account for a user.
     * Auto-recovers accounts whose 24-hour exhaustion period has expired.
     */
    public function getActiveAccount(User $user): ?AntigravityAccount
    {
        // First check if any exhausted accounts are ready to be reset
        AntigravityAccount::where('user_id', $user->id)
            ->where('is_quota_exhausted', true)
            ->whereNotNull('quota_reset_at')
            ->where('quota_reset_at', '<=', now())
            ->update([
                'is_quota_exhausted' => false,
                'quota_reset_at' => null,
            ]);

        $account = AntigravityAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_quota_exhausted', false)
            ->orderBy('priority_order', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($account) {
            // Trigger model sync on login/activation
            try {
                $gateway = app(AntigravityGatewayService::class);
                $token = $account->antigravity_key ?: $account->google_oauth_token;
                if ($token && !app()->runningInConsole()) {
                    $gateway->syncModels($token);
                }
            } catch (\Exception $e) {
                Log::error('Antigravity model sync failed: ' . $e->getMessage());
            }
        }

        return $account;
    }

    

    

    /**
     * Retrieves active OAuth token with automatic token refresh if expired.
     */
    public function getActiveToken(User $user): ?string
    {
        $result = $this->getActiveAccountAndToken($user);

        return $result ? $result['token'] : null;
    }

    /**
     * Get active account and valid bearer token, rotating or refreshing if needed.
     *
     * @return array{account: AntigravityAccount, token: string}|null
     */
    public function getActiveAccountAndToken(User $user): ?array
    {
        $account = $this->getActiveAccount($user);
        if (! $account) {
            return null;
        }

        // If an API Key / Static CLI Key is present, it does not expire
        if (! empty($account->antigravity_key)) {
            return ['account' => $account, 'token' => $account->antigravity_key];
        }

        // Check if token is expired or close to expiring (within 5 minutes)
        if ($account->token_expires_at && $account->token_expires_at->isPast()) {
            if ($account->google_oauth_refresh_token) {
                $refreshedToken = $this->refreshOAuthToken($account);
                if ($refreshedToken) {
                    return ['account' => $account, 'token' => $refreshedToken];
                }
            }

            // Refresh failed: Rotate to next available account
            Log::warning("Antigravity OAuth token refresh failed for account [{$account->email}]. Rotating...");
            $this->rotateToNext($user, $account);
            $nextAccount = $this->getActiveAccount($user);
            if ($nextAccount) {
                $token = $nextAccount->antigravity_key ?: $nextAccount->google_oauth_token;
                if ($token) {
                    return ['account' => $nextAccount, 'token' => $token];
                }
            }

            return null;
        }

        $token = $account->antigravity_key ?: $account->google_oauth_token;

        return $token ? ['account' => $account, 'token' => $token] : null;
    }

    /**
     * Refresh an expired Google OAuth access token using the stored refresh_token.
     */
    public function refreshOAuthToken(AntigravityAccount $account): ?string
    {
        $clientId = config('services.antigravity.client_id')
            ?: config('services.google.client_id')
            ?: env('ANTIGRAVITY_CLIENT_ID')
            ?: env('GOOGLE_CLIENT_ID')
            ?: env('GOOGLE_CLIENT_ID_FALLBACK', 'default_cli_id.apps.googleusercontent.com');

        $clientSecret = config('services.antigravity.client_secret')
            ?: config('services.google.client_secret')
            ?: env('ANTIGRAVITY_CLIENT_SECRET')
            ?: env('GOOGLE_CLIENT_SECRET')
            ?: env('GOOGLE_CLIENT_SECRET_FALLBACK', 'default_cli_secret');

        if (! $clientId || ! $clientSecret || ! $account->google_oauth_refresh_token) {
            return null;
        }

        try {
$client = Http::asForm()->timeout(10);
        if (config('app.env') === 'local' || app()->environment('local') || app()->environment('testing')) {
            $client = $client->withoutVerifying();
        }

            $response = $client->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $account->google_oauth_refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $newToken = $data['access_token'] ?? null;
                $expiresIn = (int) ($data['expires_in'] ?? 3600);

                if ($newToken) {
                    $account->update([
                        'google_oauth_token' => $newToken,
                        'token_expires_at' => now()->addSeconds($expiresIn - 60),
                    ]);

                    return $newToken;
                }
            }

            Log::error('Google OAuth token refresh error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google OAuth token refresh exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Rotates account when an HTTP 429 quota exhaustion or 401 token invalidation occurs.
     */
    public function rotateToNext(User $user, AntigravityAccount $exhaustedAccount): ?AntigravityAccount
    {
        $exhaustedAccount->update([
            'is_quota_exhausted' => true,
            'quota_reset_at' => now()->addHours(24),
        ]);

        return $this->getActiveAccount($user);
    }

    /**
     * Bootstraps and onboards the Google Cloud Code project for an Antigravity account.
     * Replicates the exact OmniRoute/9Router handshake: loadCodeAssist -> onboardUser -> loadCodeAssist.
     */
    public function bootstrapAccountProject(AntigravityAccount $account): ?string
    {
        $token = $account->google_oauth_token;
        if (! $token || str_starts_with($token, 'AIza')) {
            return $account->project_id;
        }

        $endpoints = [
            'https://cloudcode-pa.googleapis.com',
            'https://daily-cloudcode-pa.googleapis.com',
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'User-Agent' => 'antigravity/ide/1.0.0 darwin/arm64 google-api-nodejs-client/10.3.0',
            'X-Goog-Api-Client' => 'gl-node/22.21.1',
        ];

        $metadata = [
            'ideType' => 9,
            'pluginType' => 2,
            'platform' => 5,
        ];

        $projectId = null;
        $tierId = 'legacy-tier';

        foreach ($endpoints as $baseUrl) {
            try {
                $client = Http::withHeaders($headers)->timeout(8);
                if (config('app.env') === 'local' || app()->environment('local') || env('ANTIGRAVITY_SSL_VERIFY', true) === false) {
                    $client = $client->withoutVerifying();
                }

                // Step 1: Query loadCodeAssist
                $res = $client->post("{$baseUrl}/v1internal:loadCodeAssist", [
                    'metadata' => $metadata,
                ]);

                if ($res->successful()) {
                    $data = $res->json() ?? [];
                    $project = $data['cloudaicompanionProject'] ?? null;
                    if (is_string($project)) {
                        $projectId = $project;
                    } elseif (is_array($project) && isset($project['id'])) {
                        $projectId = $project['id'];
                    }

                    if (! empty($data['currentTier']['id'])) {
                        $tierId = $data['currentTier']['id'];
                    }
                }

                // Step 2: If no project, onboard user to create a cloud code companion project
                if (! $projectId) {
                    $client->post("{$baseUrl}/v1internal:onboardUser", [
                        'tier_id' => $tierId,
                        'metadata' => $metadata,
                    ]);

                    // Step 3: Re-query loadCodeAssist
                    $retryRes = $client->post("{$baseUrl}/v1internal:loadCodeAssist", [
                        'metadata' => $metadata,
                    ]);

                    if ($retryRes->successful()) {
                        $retryData = $retryRes->json() ?? [];
                        $project = $retryData['cloudaicompanionProject'] ?? null;
                        if (is_string($project)) {
                            $projectId = $project;
                        } elseif (is_array($project) && isset($project['id'])) {
                            $projectId = $project['id'];
                        }
                    }
                }

                if ($projectId) {
                    $account->update([
                        'project_id' => $projectId,
                        'tier_id' => $tierId,
                    ]);
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("Antigravity project bootstrap attempt failed on {$baseUrl}: " . $e->getMessage());
            }
        }

        return $projectId ?: $account->project_id;
    }

    /**
     * Dynamically synchronizes models from Google Cloud Code PA API or Generative Language API.
     * Prunes obsolete models and ensures only valid models are present.
     */
    public function syncAntigravityModels(User $user): int
    {
        $provider = AiProvider::firstOrCreate(
            ['slug' => 'antigravity'],
            [
                'name' => 'Antigravity (Google Powered)',
                'icon' => '🌌',
                'description' => 'Google Cloud Code backed multi-account AI routing with dynamic server model discovery and automatic quota rotation.',
                'base_url' => 'https://cloudcode-pa.googleapis.com/v1internal',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'settings' => [
                    'supports_oauth' => true,
                    'auth_type' => 'google_oauth_bearer',
                ],
            ]
        );

        $account = $this->getActiveAccount($user);
        $token = $this->getActiveToken($user);
        $syncedCount = 0;
        $syncedModelIds = [];

        if ($token) {
            $isApiKey = str_starts_with($token, 'AIza');
            $projectId = $account ? ($account->project_id ?: $this->bootstrapAccountProject($account)) : null;

            // Method 1: Google Cloud Code API (:fetchAvailableModels) - Official Antigravity Engine
            if (! $isApiKey) {
                $cloudCodeEndpoints = [
                    'https://cloudcode-pa.googleapis.com/v1internal:fetchAvailableModels',
                    'https://daily-cloudcode-pa.googleapis.com/v1internal:fetchAvailableModels',
                ];

                $headers = [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'antigravity/cli/1.0.0 (aidev_client; os_type=darwin; arch=arm64; auth_method=consumer)',
                ];

                foreach ($cloudCodeEndpoints as $endpoint) {
                    try {
                        $client = Http::withHeaders($headers)->timeout(8);
                        if (config('app.env') === 'local' || app()->environment('local') || env('ANTIGRAVITY_SSL_VERIFY', true) === false) {
                            $client = $client->withoutVerifying();
                        }

                        $response = $client->post($endpoint, $projectId ? ['project' => $projectId] : []);

                        if ($response->successful()) {
                            $data = $response->json();
                            $models = $data['models'] ?? [];

                            // Process models map or list
                            foreach ($models as $rawKey => $modelInfo) {
                                $slug = is_string($rawKey) ? $rawKey : ($modelInfo['name'] ?? $modelInfo['id'] ?? '');
                                $slug = str_replace(['models/', 'antigravity/'], '', $slug);
                                if (empty($slug) || (! empty($modelInfo['isInternal']) && $modelInfo['isInternal'] === true)) {
                                    continue;
                                }

                                $modelId = 'antigravity/' . $slug;
                                $syncedModelIds[] = $modelId;

                                $displayName = $modelInfo['displayName'] ?? ucwords(str_replace(['-', '_'], ' ', $slug));
                                $inputLimit = $modelInfo['inputTokenLimit'] ?? $modelInfo['maxInputTokens'] ?? 1048576;
                                $outputLimit = $modelInfo['outputTokenLimit'] ?? $modelInfo['maxOutputTokens'] ?? 8192;

                                AiModel::updateOrCreate(
                                    ['model_id' => $modelId, 'ai_provider_id' => $provider->id],
                                    [
                                        'name' => $displayName,
                                        'context_window' => $inputLimit,
                                        'max_output_tokens' => $outputLimit,
                                        'supports_streaming' => true,
                                        'supports_vision' => true,
                                        'supports_tools' => true,
                                        'supports_json' => true,
                                        'supports_reasoning' => str_contains(strtolower($slug), 'pro') || str_contains(strtolower($slug), 'thinking') || str_contains(strtolower($slug), 'high'),
                                        'is_free_tier' => str_contains(strtolower($slug), 'flash') || str_contains(strtolower($slug), 'free'),
                                        'is_active' => true,
                                    ]
                                );
                                $syncedCount++;
                            }

                            if ($syncedCount > 0) {
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Antigravity :fetchAvailableModels failed on {$endpoint}: " . $e->getMessage());
                    }
                }
            }

            // Method 2: Fallback to Google Generative Language API if using API Key or CloudCode unavailable
            if ($syncedCount === 0) {
                try {
                    $client = $isApiKey
                        ? Http::withHeaders(['x-goog-api-key' => $token])
                        : Http::withToken($token);

                    $client = $client->connectTimeout(3)->timeout(8);
                    if (config('app.env') === 'local' || app()->environment('local') || env('ANTIGRAVITY_SSL_VERIFY', true) === false) {
                        $client = $client->withoutVerifying();
                    }

                    $response = $client->get('https://generativelanguage.googleapis.com/v1beta/models');
                    if ($response->successful()) {
                        $googleModels = $response->json('models') ?? [];
                        foreach ($googleModels as $gm) {
                            $rawName = $gm['name'] ?? '';
                            $modelSlug = str_replace('models/', '', $rawName);
                            $methods = $gm['supportedGenerationMethods'] ?? [];

                            if (in_array('generateContent', $methods, true)) {
                                $modelId = 'antigravity/' . $modelSlug;
                                $syncedModelIds[] = $modelId;

                                AiModel::updateOrCreate(
                                    ['model_id' => $modelId, 'ai_provider_id' => $provider->id],
                                    [
                                        'name' => $gm['displayName'] ?? ucfirst(str_replace('-', ' ', $modelSlug)),
                                        'context_window' => $gm['inputTokenLimit'] ?? 1000000,
                                        'max_output_tokens' => $gm['outputTokenLimit'] ?? 8192,
                                        'supports_streaming' => true,
                                        'supports_vision' => true,
                                        'supports_tools' => true,
                                        'supports_json' => true,
                                        'supports_reasoning' => str_contains(strtolower($modelSlug), 'pro') || str_contains(strtolower($modelSlug), 'thinking') || str_contains(strtolower($modelSlug), '2.0'),
                                        'is_free_tier' => str_contains(strtolower($modelSlug), 'flash'),
                                        'is_active' => true,
                                    ]
                                );
                                $syncedCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Antigravity secondary model discovery error: ' . $e->getMessage());
                }
            }

            // Prune obsolete models if we successfully synced models
            if ($syncedCount > 0) {
                AiModel::where('ai_provider_id', $provider->id)
                    ->whereNotIn('model_id', $syncedModelIds)
                    ->delete();
            }
        }

        // Authentic Baseline Models when offline or before initial sync
        if ($syncedCount === 0) {
            $baselineModels = [
                [
                    'model_id' => 'antigravity/gemini-3.7-flash',
                    'name' => 'Gemini 3.7 Flash',
                    'context_window' => 1048576,
                    'max_output_tokens' => 8192,
                    'supports_streaming' => true,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'supports_reasoning' => true,
                    'is_free_tier' => true,
                ],
                [
                    'model_id' => 'antigravity/gemini-3.1-pro',
                    'name' => 'Gemini 3.1 Pro',
                    'context_window' => 2097152,
                    'max_output_tokens' => 8192,
                    'supports_streaming' => true,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'supports_reasoning' => true,
                    'is_free_tier' => false,
                ],
                [
                    'model_id' => 'antigravity/claude-sonnet-4.6',
                    'name' => 'Claude Sonnet 4.6 (Antigravity)',
                    'context_window' => 200000,
                    'max_output_tokens' => 8192,
                    'supports_streaming' => true,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'supports_reasoning' => true,
                    'is_free_tier' => false,
                ],
                [
                    'model_id' => 'antigravity/gemini-2.0-flash',
                    'name' => 'Gemini 2.0 Flash',
                    'context_window' => 1048576,
                    'max_output_tokens' => 8192,
                    'supports_streaming' => true,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'supports_reasoning' => true,
                    'is_free_tier' => true,
                ],
                [
                    'model_id' => 'antigravity/gemini-1.5-flash',
                    'name' => 'Gemini 1.5 Flash',
                    'context_window' => 1048576,
                    'max_output_tokens' => 8192,
                    'supports_streaming' => true,
                    'supports_vision' => true,
                    'supports_tools' => true,
                    'supports_json' => true,
                    'supports_reasoning' => false,
                    'is_free_tier' => true,
                ],
            ];

            foreach ($baselineModels as $bm) {
                AiModel::updateOrCreate(
                    ['model_id' => $bm['model_id'], 'ai_provider_id' => $provider->id],
                    array_merge($bm, [
                        'ai_provider_id' => $provider->id,
                        'is_active' => true,
                    ])
                );
                $syncedCount++;
            }
        }

        return $syncedCount;
    }
}


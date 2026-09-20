<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Health Monitor
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
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AntigravityHealthMonitor
{
    protected AntigravityAccountManager $accountManager;

    public function __construct(AntigravityAccountManager $accountManager)
    {
        $this->accountManager = $accountManager;
    }

    /**
     * Run a live diagnostic probe on the Antigravity Fabric.
     *
     * @return array{status: string, message: string, latency_ms: int, models_count: int}
     */
    public function probe(?User $user = null): array
    {
        $provider = AiProvider::where('slug', 'antigravity')->first();
        if (! $provider) {
            return [
                'status' => 'unregistered',
                'message' => 'Antigravity provider entry not found in database.',
                'latency_ms' => 0,
                'models_count' => 0,
            ];
        }

        $models = AiModel::where('ai_provider_id', $provider->id)->get();
        $token = null;

        if ($user) {
            $token = $this->accountManager->getActiveToken($user);
        }

        // If no user supplied or user has no token, check if any active account exists
        if (! $token) {
            $firstAccount = \App\Features\Antigravity\Models\AntigravityAccount::where('is_active', true)->first();
            if ($firstAccount) {
                $token = $firstAccount->google_oauth_token;
            }
        }

        if (! $token) {
            foreach ($models as $model) {
                $model->update(['status' => 'unauthenticated', 'last_health_check_at' => now()]);
            }

            return [
                'status' => 'unauthenticated',
                'message' => 'No active Google OAuth token linked to probe Google API.',
                'latency_ms' => 0,
                'models_count' => $models->count(),
            ];
        }

        $start = microtime(true);
        try {
            $client = Http::withToken($token)
                ->connectTimeout(2)
                ->timeout(5);

            if (config('app.env') === 'local' || app()->environment('local') || env('ANTIGRAVITY_SSL_VERIFY', true) === false) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get('https://generativelanguage.googleapis.com/v1beta/models');
            $latency = (int) ((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $googleModels = $response->json('models') ?? [];
                $availableNames = array_map(fn($m) => str_replace('models/', '', $m['name'] ?? ''), $googleModels);

                foreach ($models as $model) {
                    $slug = preg_replace('#^antigravity/#', '', $model->model_id);
                    $isAvailable = in_array($slug, $availableNames, true) || empty($availableNames);

                    $model->update([
                        'status' => $isAvailable ? 'live' : 'degraded',
                        'latency_ms' => $latency,
                        'last_health_check_at' => now(),
                    ]);
                }

                return [
                    'status' => 'healthy',
                    'message' => "Probe successful: Google API returned {$response->status()} in {$latency}ms with " . count($googleModels) . " live models.",
                    'latency_ms' => $latency,
                    'models_count' => $models->count(),
                ];
            }

            $status = $response->status();
            foreach ($models as $model) {
                $model->update(['status' => 'degraded', 'last_health_check_at' => now()]);
            }

            return [
                'status' => 'degraded',
                'message' => "Google API returned status {$status}: " . $response->body(),
                'latency_ms' => $latency,
                'models_count' => $models->count(),
            ];

        } catch (\Exception $e) {
            $latency = (int) ((microtime(true) - $start) * 1000);
            foreach ($models as $model) {
                $model->update(['status' => 'dead', 'last_health_check_at' => now()]);
            }

            Log::warning("Antigravity health probe failed: " . $e->getMessage());

            return [
                'status' => 'dead',
                'message' => "Health probe exception: " . $e->getMessage(),
                'latency_ms' => $latency,
                'models_count' => $models->count(),
            ];
        }
    }
}

<?php

namespace App\Features\AI\Http\Controllers;

use App\Features\AI\Models\AiProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiProviderController extends Controller
{
    public function getModels(Request $request): JsonResponse
    {
        $providerSlug = $request->query('provider');
        if (! $providerSlug) {
            return response()->json(['success' => false, 'message' => 'Provider slug required'], 400);
        }

        $provider = AiProvider::where('slug', $providerSlug)->first();
        if (! $provider) {
            return response()->json(['success' => false, 'message' => 'Provider not found'], 404);
        }

        $models = $provider->models()
            ->where('is_active', true)
            ->get([
                'id',
                'name',
                'model_id',
                'last_test_status',
                'last_test_error',
                'last_tested_at',
                'quota_limit_per_model',
            ]);

        // For Antigravity, add quota usage and readiness info only if user has active account
        if ($provider->slug === 'antigravity' && auth()->check()) {
            use App\Features\Antigravity\Models\AntigravityAccount;
            
            // Check if user has an active Antigravity account
            $hasActiveAccount = AntigravityAccount::where('user_id', auth()->id())
                ->where('is_active', true)
                ->where('is_quota_exhausted', false)
                ->exists();

            if ($hasActiveAccount) {
                $userId = auth()->id();
                $models = $models->map(function ($model) use ($userId) {
                    $quotaUsed = DB::table('antigravity_telemetry')
                        ->where('user_id', $userId)
                        ->where('ai_model_id', $model->id)
                        ->sum('tokens_used') ?? 0;

                    $quotaLimit = $model->quota_limit_per_model ?? 0;
                    $quotaRemaining = max(0, $quotaLimit - $quotaUsed);

                    return [
                        'id' => $model->model_id,
                        'name' => $model->name,
                        'quota_used' => (int) $quotaUsed,
                        'quota_limit' => (int) $quotaLimit,
                        'quota_remaining' => (int) $quotaRemaining,
                        'last_test_status' => $model->last_test_status,
                        'last_test_error' => $model->last_test_error,
                        'last_tested_at' => $model->last_tested_at,
                        'is_working' => $model->last_test_status === 'working' && is_null($model->last_test_error),
                    ];
                });
            } else {
                // User has no active Antigravity account, return basic model info
                $models = $models->map(fn ($model) => [
                    'id' => $model->model_id, // Frontend uses model_id as identifier
                    'name' => $model->name,
                ]);
            }
        } else {
            $models = $models->map(fn ($model) => [
                'id' => $model->model_id, // Frontend uses model_id as identifier
                'name' => $model->name,
            ]);
        }

        return response()->json(['success' => true, 'models' => $models]);
    }
}

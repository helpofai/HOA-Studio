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

namespace App\Features\KnowledgeBase\Services;

use App\Features\KnowledgeBase\Models\VectorEmbeddingCache;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class VectorCacheManager
{
    /**
     * Generate canonical deterministic SHA-256 hash for text + embedding model
     */
    public function getCacheHash(string $text, string $model): string
    {
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $text)));
        return hash('sha256', "{$normalized}::{$model}");
    }

    /**
     * Retrieve vector from L1 (Memory/Redis) or L2 (Database Persistent Cache)
     *
     * @return array<float>|null
     */
    public function getCachedVector(string $text, string $model, ?User $user = null): ?array
    {
        $hash = $this->getCacheHash($text, $model);
        $l1Key = "hoa_vec_l1:{$hash}";

        // 1. Check L1 Fast Memory/Redis Cache
        $l1Vector = Cache::get($l1Key);
        if (is_array($l1Vector) && !empty($l1Vector)) {
            $this->recordHitAsync($hash);
            return $l1Vector;
        }

        // 2. Check L2 Database Vector Store
        try {
            $record = VectorEmbeddingCache::where('hash', $hash)->first();
            if ($record && is_array($record->vector) && !empty($record->vector)) {
                $ttlSeconds = $this->resolveTtlSeconds($user);
                // Populate L1 cache for sub-millisecond future lookups
                Cache::put($l1Key, $record->vector, min($ttlSeconds, 86400));

                $record->increment('hit_count');
                $record->update(['last_accessed_at' => now()]);

                return $record->vector;
            }
        } catch (Throwable $e) {
            // Graceful fallback if database temporary lock occurs
        }

        return null;
    }

    /**
     * Store vector embedding in both L1 Fast Cache and L2 Persistent Database Cache
     *
     * @param array<float> $vector
     */
    public function storeVector(string $text, string $model, array $vector, ?User $user = null, int $tokenCount = 0): void
    {
        if (empty($vector)) {
            return;
        }

        $hash = $this->getCacheHash($text, $model);
        $ttlSeconds = $this->resolveTtlSeconds($user);
        $l1Key = "hoa_vec_l1:{$hash}";

        // 1. Store in L1 Cache
        Cache::put($l1Key, $vector, min($ttlSeconds, 86400));

        // 2. Persist in L2 Vector Database
        try {
            VectorEmbeddingCache::updateOrCreate(
                ['hash' => $hash],
                [
                    'user_id' => $user?->id,
                    'model' => $model,
                    'dimensions' => count($vector),
                    'vector' => $vector,
                    'token_count' => $tokenCount > 0 ? $tokenCount : (int) ceil(mb_strlen($text) / 4),
                    'hit_count' => DB::raw('hit_count + 1'),
                    'last_accessed_at' => now(),
                ]
            );
        } catch (Throwable $e) {
            // Continue safely
        }
    }

    /**
     * Retrieve aggregate Vector Cache Telemetry & Storage Metrics
     */
    public function getTelemetryStats(?User $user = null): array
    {
        try {
            $query = VectorEmbeddingCache::query();
            if ($user && !$user->isAdmin()) {
                $query->where('user_id', $user->id);
            }

            $totalVectors = $query->count();
            $totalHits = (int) $query->sum('hit_count');
            $tokensSaved = (int) $query->sum(DB::raw('token_count * GREATEST(0, hit_count - 1)'));

            // Approx cost saved ($0.02 / 1M tokens for text-embedding-3-small)
            $estimatedCostSaved = round(($tokensSaved / 1_000_000) * 0.02, 4);

            return [
                'total_cached_vectors' => $totalVectors,
                'total_cache_hits' => $totalHits,
                'tokens_saved' => $tokensSaved,
                'estimated_cost_saved_usd' => $estimatedCostSaved,
                'avg_dimensions' => $totalVectors > 0 ? (int) $query->avg('dimensions') : 1536,
                'cache_hit_ratio' => $totalVectors > 0 ? round(($totalHits / ($totalVectors + $totalHits)) * 100, 1) . '%' : '100%',
            ];
        } catch (Throwable $e) {
            return [
                'total_cached_vectors' => 0,
                'total_cache_hits' => 0,
                'tokens_saved' => 0,
                'estimated_cost_saved_usd' => 0.0,
                'avg_dimensions' => 1536,
                'cache_hit_ratio' => '100%',
            ];
        }
    }

    /**
     * Purge vectors older than specified days
     */
    public function purgeStaleCache(int $days = 30): int
    {
        try {
            $cutoff = now()->subDays($days);
            return VectorEmbeddingCache::where('last_accessed_at', '<', $cutoff)->delete();
        } catch (Throwable $e) {
            return 0;
        }
    }

    protected function resolveTtlSeconds(?User $user): int
    {
        $days = 7;
        if ($user && isset($user->preferences['embedding_cache_days'])) {
            $userDays = (int) $user->preferences['embedding_cache_days'];
            if (in_array($userDays, [1, 7, 30, 90, 365], true)) {
                $days = $userDays;
            }
        }
        return $days * 86400;
    }

    protected function recordHitAsync(string $hash): void
    {
        try {
            VectorEmbeddingCache::where('hash', $hash)->increment('hit_count');
        } catch (Throwable $e) {}
    }
}
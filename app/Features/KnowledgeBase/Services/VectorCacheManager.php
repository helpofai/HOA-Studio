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

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class VectorCacheManager
{
    /**
     * Cache key generator for text + model vector embedding
     */
    public function getCacheKey(string $text, string $model): string
    {
        $hash = hash('sha256', mb_strtolower(trim($text)) . '::' . $model);
        return "hoa_vec_emb:{$hash}";
    }

    /**
     * Get cached vector embedding if present
     *
     * @return array<float>|null
     */
    public function getCachedVector(string $text, string $model): ?array
    {
        $key = $this->getCacheKey($text, $model);
        return Cache::get($key);
    }

    /**
     * Store vector embedding with user/system configured TTL (1, 7, or 30 days)
     */
    public function storeVector(string $text, string $model, array $vector, ?User $user = null): void
    {
        if (empty($vector)) {
            return;
        }

        $ttlDays = 7; // Default 7 days

        if ($user && isset($user->preferences['embedding_cache_days'])) {
            $userDays = (int) $user->preferences['embedding_cache_days'];
            if (in_array($userDays, [1, 7, 30], true)) {
                $ttlDays = $userDays;
            }
        }

        $key = $this->getCacheKey($text, $model);
        $seconds = $ttlDays * 86400;

        Cache::put($key, $vector, $seconds);
    }
}
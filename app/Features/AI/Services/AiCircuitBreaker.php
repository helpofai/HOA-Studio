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

use Illuminate\Support\Facades\Cache;

class AiCircuitBreaker
{
    protected const CACHE_KEY = 'hoa_ai_circuit_breaker';

    public function isTripped(): bool
    {
        return (bool) Cache::get(self::CACHE_KEY . '_tripped', false);
    }

    public function trip(string $reason = 'Emergency maintenance triggered by administrator', ?string $adminName = null): void
    {
        Cache::forever(self::CACHE_KEY . '_tripped', true);
        Cache::forever(self::CACHE_KEY . '_reason', $reason);
        Cache::forever(self::CACHE_KEY . '_tripped_by', $adminName ?: 'System Admin');
        Cache::forever(self::CACHE_KEY . '_tripped_at', now()->toIso8601String());
    }

    public function reset(): void
    {
        Cache::forget(self::CACHE_KEY . '_tripped');
        Cache::forget(self::CACHE_KEY . '_reason');
        Cache::forget(self::CACHE_KEY . '_tripped_by');
        Cache::forget(self::CACHE_KEY . '_tripped_at');
    }

    public function getStatus(): array
    {
        $tripped = $this->isTripped();

        return [
            'is_tripped' => $tripped,
            'reason' => Cache::get(self::CACHE_KEY . '_reason', 'Normal Operations'),
            'tripped_by' => Cache::get(self::CACHE_KEY . '_tripped_by', null),
            'tripped_at' => Cache::get(self::CACHE_KEY . '_tripped_at', null),
        ];
    }
}
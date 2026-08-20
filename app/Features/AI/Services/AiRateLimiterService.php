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

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class AiRateLimiterService
{
    /**
     * Determine if a user generation request is permitted under rate limiting rules
     *
     * Rule:
     * - If user uses their own API key or custom/local endpoint: UNLIMITED (no rate limit).
     * - If user uses system / admin shared gateway: Tiered limits apply (Starter: 15/min, Pro: 60/min, Enterprise: 180/min).
     */
    public function checkRateLimit(User $user, ?string $providerSlug = 'omniroute'): array
    {
        // 1. Check if user has active BYOK or Local Custom Endpoint for this provider
        $userKey = $user->getActiveApiKeyFor($providerSlug ?: 'omniroute');

        if ($userKey !== null && (!empty($userKey->api_key) || !empty($userKey->custom_base_url))) {
            return [
                'allowed' => true,
                'is_unlimited' => true,
                'reason' => 'User BYOK API key active (unlimited requests)',
                'remaining' => 999999,
                'retry_after' => 0,
            ];
        }

        // 2. Platform Shared Admin Gateway -> Apply Tiered Rate Limits
        $maxAttempts = match (mb_strtolower($user->plan ?? 'starter')) {
            'enterprise' => 180,
            'pro' => 60,
            default => 15,
        };

        if ($user->isAdmin()) {
            $maxAttempts = 300;
        }

        $key = 'hoa_ai_rate_limit:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return [
                'allowed' => false,
                'is_unlimited' => false,
                'reason' => "Shared AI Gateway rate limit exceeded for plan '{$user->plan}'. Please wait {$retryAfter}s or connect your own API key in Profile for unlimited access.",
                'remaining' => 0,
                'retry_after' => $retryAfter,
            ];
        }

        RateLimiter::hit($key, 60);

        return [
            'allowed' => true,
            'is_unlimited' => false,
            'reason' => "Platform shared gateway active",
            'remaining' => RateLimiter::remaining($key, $maxAttempts),
            'retry_after' => 0,
        ];
    }
}
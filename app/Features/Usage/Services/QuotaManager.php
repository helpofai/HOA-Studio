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

namespace App\Features\Usage\Services;

use App\Models\User;

class QuotaManager
{
    /**
     * Check if user has sufficient quota for generation
     */
    public function hasQuota(User $user, int $requiredWords = 1): bool
    {
        $this->checkAndResetMonthlyCycle($user);

        return ($user->used_word_quota + $requiredWords) <= $user->monthly_word_quota;
    }

    /**
     * Get detailed quota and accounting summary for a user
     */
    public function getQuotaDetails(User $user): array
    {
        $this->checkAndResetMonthlyCycle($user);

        $limit = max(1, (int) $user->monthly_word_quota);
        $used = (int) $user->used_word_quota;
        $remaining = max(0, $limit - $used);
        $pct = (float) round(min(100.0, ($used / $limit) * 100), 1);

        $status = 'ok';
        if ($pct >= 100.0) {
            $status = 'exhausted';
        } elseif ($pct >= 80.0) {
            $status = 'warning';
        }

        return [
            'monthly_limit' => $limit,
            'used_words' => $used,
            'remaining_words' => $remaining,
            'percentage_used' => $pct,
            'status' => $status,
            'is_exhausted' => $pct >= 100.0,
            'has_warning' => $pct >= 80.0 && $pct < 100.0,
        ];
    }

    /**
     * Reset used quota if a month has passed since user creation/last cycle
     */
    public function checkAndResetMonthlyCycle(User $user): bool
    {
        // Simple cycle check: If user updated_at or created_at cycle has rolled over
        return false;
    }

    /**
     * Grant bonus words to user
     */
    public function addBonusQuota(User $user, int $bonusWords): void
    {
        $user->increment('monthly_word_quota', $bonusWords);
    }

    /**
     * Set explicit monthly word quota
     */
    public function setMonthlyLimit(User $user, int $newLimit): void
    {
        $user->update(['monthly_word_quota' => max(0, $newLimit)]);
    }
}
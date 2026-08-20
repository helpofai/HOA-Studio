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
use Illuminate\Support\Facades\DB;

class UsageAnalyticsService
{
    public function __construct(
        protected TokenCostCalculator $costCalculator,
        protected QuotaManager $quotaManager
    ) {}

    /**
     * Get complete user usage analytics and accounting telemetry
     */
    public function getUserAnalytics(User $user): array
    {
        $quota = $this->quotaManager->getQuotaDetails($user);

        $usageRecords = DB::table('generation_usage')
            ->where('user_id', $user->id)
            ->orderBy('recorded_at', 'desc')
            ->get();

        $totalWords = 0;
        $totalTokens = 0;
        $totalCost = 0.0;
        $totalSavings = 0.0;
        $modelBreakdown = [];

        foreach ($usageRecords as $rec) {
            $words = (int) $rec->words_used;
            $tokens = (int) $rec->tokens_used;
            $model = $rec->model_slug ?: 'default';

            $totalWords += $words;
            $totalTokens += $tokens;

            // Estimate 40% prompt input / 60% completion output
            $inputTokens = (int) round($tokens * 0.4);
            $outputTokens = (int) round($tokens * 0.6);

            $cost = $this->costCalculator->calculateCost($model, $inputTokens, $outputTokens);
            $savings = $this->costCalculator->calculateSavings($model, $inputTokens, $outputTokens);

            $totalCost += $cost;
            $totalSavings += $savings;

            if (!isset($modelBreakdown[$model])) {
                $modelBreakdown[$model] = [
                    'model' => $model,
                    'words' => 0,
                    'tokens' => 0,
                    'count' => 0,
                    'cost' => 0.0,
                ];
            }

            $modelBreakdown[$model]['words'] += $words;
            $modelBreakdown[$model]['tokens'] += $tokens;
            $modelBreakdown[$model]['count'] += 1;
            $modelBreakdown[$model]['cost'] += $cost;
        }

        // Sort model breakdown by words descending
        usort($modelBreakdown, function ($a, $b) {
            return $b['words'] <=> $a['words'];
        });

        return [
            'quota' => $quota,
            'summary' => [
                'total_generations' => count($usageRecords),
                'total_words' => $totalWords,
                'total_tokens' => $totalTokens,
                'total_cost_usd' => round($totalCost, 4),
                'total_savings_usd' => round($totalSavings, 4),
            ],
            'model_breakdown' => $modelBreakdown,
            'recent_logs' => $usageRecords->take(15)->map(function ($rec) {
                $tokens = (int) $rec->tokens_used;
                $model = $rec->model_slug ?: 'default';
                $cost = $this->costCalculator->calculateCost($model, (int) ($tokens * 0.4), (int) ($tokens * 0.6));

                return [
                    'id' => $rec->id,
                    'model' => $model,
                    'words' => (int) $rec->words_used,
                    'tokens' => $tokens,
                    'cost_usd' => round($cost, 5),
                    'recorded_at' => $rec->recorded_at,
                ];
            })->all(),
        ];
    }
}
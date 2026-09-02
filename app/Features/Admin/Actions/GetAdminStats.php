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

namespace App\Features\Admin\Actions;

use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GetAdminStats
{
    public function execute(): array
    {
        // Optimized single aggregate query for user counts
        $userCounts = User::query()
            ->selectRaw("
                COUNT(*) as total_users,
                COUNT(CASE WHEN (plan IN ('pro', 'enterprise') OR role = 'pro') THEN 1 END) as pro_users,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users
            ")
            ->first();

        $totalUsers = (int) ($userCounts->total_users ?? 0);
        $proUsers = (int) ($userCounts->pro_users ?? 0);
        $adminUsers = (int) ($userCounts->admin_users ?? 0);
        $activeUsers = (int) ($userCounts->active_users ?? 0);

        // Optimized single aggregate query for document counts and word sums
        $docStats = Document::query()
            ->selectRaw('COUNT(*) as total_docs, COALESCE(SUM(word_count), 0) as total_words')
            ->first();

        $totalDocuments = (int) ($docStats->total_docs ?? 0);
        $totalWordsWritten = (int) ($docStats->total_words ?? 0);

        // Optimized single aggregate query for generation usage stats
        $usageStats = DB::table('generation_usage')
            ->selectRaw('
                COALESCE(SUM(words_used), 0) as total_words_consumed,
                COALESCE(SUM(tokens_used), 0) as total_tokens_consumed,
                COUNT(*) as total_generations
            ')
            ->first();

        $totalWordsConsumed = (int) ($usageStats->total_words_consumed ?? 0);
        $totalTokensConsumed = (int) ($usageStats->total_tokens_consumed ?? 0);
        $totalGenerations = (int) ($usageStats->total_generations ?? 0);

        $recentUsers = User::latest()->take(5)->get();

        // Check OmniRoute Gateway Health
        $gatewayOnline = false;
        $gatewayLatency = 0;
        $gatewayVersion = 'v3.8.50';

        try {
            $start = microtime(true);
            $baseUrl = rtrim(config('omniroute.base_url', 'http://127.0.0.1:20128'), '/');
            $res = Http::timeout(2)->get("{$baseUrl}/v1/models");
            $gatewayLatency = (int) round((microtime(true) - $start) * 1000);
            $gatewayOnline = $res->successful() || $res->status() === 401; // 401 means online but requires auth
        } catch (\Exception $e) {
            $gatewayOnline = false;
        }

        return [
            'total_users' => $totalUsers,
            'pro_users' => $proUsers,
            'admin_users' => $adminUsers,
            'active_users' => $activeUsers,
            'total_documents' => $totalDocuments,
            'total_words_written' => $totalWordsWritten,
            'total_words_consumed' => $totalWordsConsumed,
            'total_tokens_consumed' => $totalTokensConsumed,
            'total_generations' => $totalGenerations,
            'recent_users' => $recentUsers,
            'gateway_online' => $gatewayOnline,
            'gateway_latency' => $gatewayLatency,
            'gateway_version' => $gatewayVersion,
        ];
    }
}

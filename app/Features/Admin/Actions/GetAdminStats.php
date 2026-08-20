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
        $totalUsers = User::count();
        $proUsers = User::whereIn('plan', ['pro', 'enterprise'])->orWhere('role', 'pro')->count();
        $adminUsers = User::where('role', 'admin')->count();
        $activeUsers = User::where('is_active', true)->count();

        $totalDocuments = Document::count();
        $totalWordsWritten = Document::sum('word_count');

        $totalWordsConsumed = DB::table('generation_usage')->sum('words_used') ?? 0;
        $totalTokensConsumed = DB::table('generation_usage')->sum('tokens_used') ?? 0;
        $totalGenerations = DB::table('generation_usage')->count();

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
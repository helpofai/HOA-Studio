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

namespace App\Features\Usage\Livewire;

use App\Features\Usage\Services\UsageAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('Usage & Quota Accounting — HelpOfAi Studio')]
class UserUsagePage extends Component
{
    public function render(UsageAnalyticsService $analyticsService)
    {
        $analytics = $analyticsService->getUserAnalytics(Auth::user());

        return view('usage.index', [
            'quota' => $analytics['quota'],
            'summary' => $analytics['summary'],
            'modelBreakdown' => $analytics['model_breakdown'],
            'recentLogs' => $analytics['recent_logs'],
        ]);
    }
}
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

namespace App\Features\Dashboard\Livewire;

use App\Features\AI\Services\OmniRouteGraphTelemetryService;
use App\Features\Dashboard\Actions\GetDashboardStats;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('Dashboard — HelpOfAi Studio')]
class DashboardPage extends Component
{
    public bool $readyToLoad = false;
    public int $graphTimeRange = 24; // 1, 5, 12, 24
    public string $graphStatusFilter = 'all'; // 'all', 'pass', 'info', 'warning', 'fail'

    public function mount(): void
    {
        if (app()->runningUnitTests()) {
            $this->readyToLoad = true;
        }
    }

    public function loadDashboard(): void
    {
        $this->readyToLoad = true;
    }

    public function render(GetDashboardStats $statsAction)
    {
        $user = Auth::user();
        
        $stats = $this->readyToLoad ? $statsAction->execute($user) : [
            'total_documents' => 0,
            'total_projects' => 0,
            'total_words' => 0,
            'monthly_quota' => $user->monthly_word_quota ?? 0,
            'used_quota' => $user->used_word_quota ?? 0,
            'remaining_quota' => max(0, ($user->monthly_word_quota ?? 0) - ($user->used_word_quota ?? 0)),
            'quota_percentage' => 0,
            'recent_documents' => collect(),
            'recent_versions' => collect(),
        ];

        $graphData = $this->readyToLoad ? app(OmniRouteGraphTelemetryService::class)->generate(
            $this->graphTimeRange,
            $user->id,
            $this->graphStatusFilter
        ) : [
            'hours' => $this->graphTimeRange,
            'status_filter' => $this->graphStatusFilter,
            'buckets' => [],
            'max_bucket_requests' => 1,
            'svg_paths' => ['all' => null, 'pass' => null, 'info' => null, 'warning' => null, 'fail' => null, 'points' => []],
            'summary' => [
                'total_requests' => 0,
                'pass' => 0,
                'info' => 0,
                'warning' => 0,
                'fail' => 0,
                'total_tokens' => 0,
                'avg_latency_ms' => 12,
                'success_rate' => 100.0,
            ],
        ];

        return view('dashboard.index', [
            'stats' => $stats,
            'graphData' => $graphData,
            'readyToLoad' => $this->readyToLoad,
        ]);
    }
}
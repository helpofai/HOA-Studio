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

namespace App\Features\Admin\Livewire;

use App\Features\AI\Services\OmniRouteGraphTelemetryService;
use App\Features\Admin\Actions\GetAdminStats;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Admin Overview — HelpOfAi Studio')]
class AdminDashboardPage extends Component
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

    public function render(GetAdminStats $statsAction)
    {
        $stats = $this->readyToLoad ? $statsAction->execute() : [
            'total_users' => 0,
            'pro_users' => 0,
            'admin_users' => 0,
            'active_users' => 0,
            'total_documents' => 0,
            'total_words_written' => 0,
            'total_words_consumed' => 0,
            'total_tokens_consumed' => 0,
            'total_generations' => 0,
            'recent_users' => collect(),
            'gateway_online' => false,
            'gateway_latency' => 0,
            'gateway_version' => 'v3.8.50',
        ];

        $graphData = $this->readyToLoad ? app(OmniRouteGraphTelemetryService::class)->generate(
            $this->graphTimeRange,
            null, // Platform-wide admin telemetry
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

        return view('admin.dashboard', [
            'stats' => $stats,
            'graphData' => $graphData,
            'graphTimeRange' => $this->graphTimeRange,
            'graphStatusFilter' => $this->graphStatusFilter,
            'readyToLoad' => $this->readyToLoad,
        ]);
    }
}
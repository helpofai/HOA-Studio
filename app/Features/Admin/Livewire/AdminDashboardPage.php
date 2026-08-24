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
    public int $graphTimeRange = 24; // 1, 5, 12, 24
    public string $graphStatusFilter = 'all'; // 'all', 'pass', 'info', 'warning', 'fail'

    public function render(GetAdminStats $statsAction)
    {
        $stats = $statsAction->execute();

        $graphData = app(OmniRouteGraphTelemetryService::class)->generate(
            $this->graphTimeRange,
            null, // Platform-wide admin telemetry
            $this->graphStatusFilter
        );

        return view('admin.dashboard', [
            'stats' => $stats,
            'graphData' => $graphData,
            'graphTimeRange' => $this->graphTimeRange,
            'graphStatusFilter' => $this->graphStatusFilter,
        ]);
    }
}
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
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('AI Generation & Usage Logs — HelpOfAi Studio')]
class AdminUsageLogsPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedModel = '';
    public int $graphTimeRange = 24; // 1, 5, 12, 24
    public string $graphStatusFilter = 'all'; // 'all', 'pass', 'info', 'warning', 'fail'

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DB::table('generation_usage')
            ->join('users', 'generation_usage.user_id', '=', 'users.id')
            ->select(
                'generation_usage.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.role as user_role'
            );

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('users.name', 'like', '%' . $this->search . '%')
                  ->orWhere('users.email', 'like', '%' . $this->search . '%')
                  ->orWhere('generation_usage.model_slug', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedModel)) {
            $query->where('generation_usage.model_slug', $this->selectedModel);
        }

        $logs = $query->orderByDesc('generation_usage.recorded_at')->paginate(20);

        $models = DB::table('generation_usage')
            ->distinct()
            ->pluck('model_slug');

        $graphData = app(OmniRouteGraphTelemetryService::class)->generate(
            $this->graphTimeRange,
            null, // Platform-wide across all users
            $this->graphStatusFilter
        );

        return view('admin.usage-logs', [
            'logs' => $logs,
            'models' => $models,
            'graphData' => $graphData,
        ]);
    }
}
<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Antigravity Setup Page
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Antigravity\Livewire;

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Services\AntigravityHealthMonitor;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Antigravity Gateway Admin Setup — HelpOfAi Studio')]
class AdminAntigravitySetupPage extends Component
{
    public ?array $probeResult = null;
    public bool $isProbing = false;

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
    }

    public function runHealthProbe(AntigravityHealthMonitor $monitor): void
    {
        $this->isProbing = true;
        $this->probeResult = $monitor->probe(auth()->user());
        $this->isProbing = false;
    }

    public function render()
    {
        $provider = AiProvider::where('slug', 'antigravity')->first();
        $modelsCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->count() : 0;
        $activeAccountsCount = AntigravityAccount::where('is_active', true)->count();
        $totalAccountsCount = AntigravityAccount::count();
        $totalTokensUsed = 0;
        try {
            $totalTokensUsed = (int) DB::table('antigravity_telemetry')->sum('tokens_used');
        } catch (\Throwable $e) {
        }

        return view('features.antigravity.admin-antigravity-setup-page', [
            'provider' => $provider,
            'modelsCount' => $modelsCount,
            'activeAccountsCount' => $activeAccountsCount,
            'totalAccountsCount' => $totalAccountsCount,
            'totalTokensUsed' => $totalTokensUsed,
        ]);
    }
}

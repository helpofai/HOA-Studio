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

use App\Features\Admin\Actions\SeedDefaultAiProviders;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\AI\Services\AiCircuitBreaker;
use App\Features\AI\Services\ModelGovernanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('AI Providers & Model Governance — HelpOfAi Studio')]
class AdminAiSettingsPage extends Component
{
    public bool $isTestingPing = false;
    public ?int $testingModelId = null;

    public function mount(SeedDefaultAiProviders $seeder)
    {
        $seeder->execute();
    }

    public function toggleProviderActive(int $providerId)
    {
        $provider = AiProvider::findOrFail($providerId);
        $provider->is_active = !$provider->is_active;
        $provider->save();

        session()->flash('status', "Provider '{$provider->name}' " . ($provider->is_active ? 'enabled' : 'disabled') . " successfully.");
    }

    public function toggleAllowUserKey(int $providerId)
    {
        $provider = AiProvider::findOrFail($providerId);
        $provider->allow_user_key = !$provider->allow_user_key;
        $provider->save();

        session()->flash('status', "User BYOK API key policy updated for '{$provider->name}'.");
    }

    public function pingModel(int $modelId, ModelGovernanceService $service)
    {
        $model = AiModel::findOrFail($modelId);
        $this->isTestingPing = true;
        $this->testingModelId = $modelId;

        $res = $service->pingModel($model);
        $this->isTestingPing = false;
        $this->testingModelId = null;

        if ($res['status'] === 'healthy') {
            session()->flash('status', "Model '{$model->name}' is healthy! Latency: {$res['latency_ms']}ms");
        } else {
            session()->flash('warning', "Model '{$model->name}' ping returned {$res['status']}: " . ($res['error'] ?? 'Unknown error'));
        }
    }

    public function setDefaultModel(int $modelId, ModelGovernanceService $service)
    {
        $model = AiModel::findOrFail($modelId);
        $service->setDefaultModel($model);

        session()->flash('status', "'{$model->name}' is now set as the primary fallback model.");
    }

    public function toggleModelActive(int $modelId, ModelGovernanceService $service)
    {
        $model = AiModel::findOrFail($modelId);
        $active = $service->toggleActive($model);

        session()->flash('status', "Model '{$model->name}' " . ($active ? 'activated' : 'deactivated') . " successfully.");
    }

    public function toggleModelFreeTier(int $modelId, ModelGovernanceService $service)
    {
        $model = AiModel::findOrFail($modelId);
        $free = $service->toggleFreeTier($model);

        session()->flash('status', "Model '{$model->name}' is now " . ($free ? 'available to Starter / Free tier' : 'restricted to Pro / Enterprise plans') . ".");
    }

    public function toggleCircuitBreaker(AiCircuitBreaker $breaker)
    {
        if ($breaker->isTripped()) {
            $breaker->reset();
            session()->flash('status', 'AI Gateway Circuit Breaker RESET. Normal AI traffic restored.');
        } else {
            $user = Auth::user();
            $breaker->trip('Emergency stop initiated by admin ' . $user->name, $user->name);
            session()->flash('warning', 'EMERGENCY: AI Circuit Breaker TRIPPED. All outgoing AI calls paused.');
        }
    }

    public function render(AiCircuitBreaker $breaker)
    {
        $providers = AiProvider::with('models')->get();
        $models = AiModel::with('provider')->orderByDesc('is_default')->orderBy('ai_provider_id')->get();
        $circuitStatus = $breaker->getStatus();

        // Calculate token and word usage aggregated by model slug
        $usageStats = DB::table('generation_usage')
            ->select('model_slug', DB::raw('SUM(words_used) as total_words'), DB::raw('SUM(tokens_used) as total_tokens'), DB::raw('COUNT(*) as total_calls'))
            ->groupBy('model_slug')
            ->get()
            ->keyBy('model_slug');

        $totalSystemWords = DB::table('generation_usage')->sum('words_used') ?? 0;
        $totalSystemTokens = DB::table('generation_usage')->sum('tokens_used') ?? 0;

        return view('admin.ai-settings.index', [
            'providers' => $providers,
            'models' => $models,
            'circuitStatus' => $circuitStatus,
            'usageStats' => $usageStats,
            'totalSystemWords' => $totalSystemWords,
            'totalSystemTokens' => $totalSystemTokens,
        ]);
    }
}
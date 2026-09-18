<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Antigravity Setup Page
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

namespace App\Features\AI\Livewire;

use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Services\AntigravityAccountManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Antigravity Gateway Models — HelpOfAi Studio')]
class UserAntigravityModelsPage extends Component
{
    public function mount(AntigravityAccountManager $manager)
    {
        // Auto-sync models when user visits the page
        $manager->fetchAntigravityModels();
    }

    public function render(AntigravityAccountManager $manager)
    {
        $provider = AiProvider::where('slug', 'antigravity')->with('models')->first();

        // Only show models that are active
        $models = $provider 
            ? $provider->models()->where('is_active', true)->orderBy('name')->get() 
            : collect();

        $account = $manager->getActiveAccount(Auth::user());

        return view('ai.models.antigravity', [
            'provider' => $provider,
            'models' => $models,
            'account' => $account,
        ]);
    }
}

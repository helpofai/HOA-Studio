<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Antigravity Gateway Setup Page
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

namespace App\Features\Admin\Livewire;

use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Services\AntigravityAccountManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Antigravity Gateway & Multi-Account Manager — HelpOfAi Studio')]
class AdminAntigravitySetupPage extends Component
{
    public string $newApiKey = '';
    public string $newAccountEmail = '';

    public function toggleAccountActive(int $accountId)
    {
        $account = AntigravityAccount::where('id', $accountId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $account->is_active = ! $account->is_active;
        $account->save();

        session()->flash('status', "Antigravity account '{$account->email}' ".($account->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function resetQuotaExhaustion(int $accountId)
    {
        $account = AntigravityAccount::where('id', $accountId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $account->is_quota_exhausted = false;
        $account->quota_reset_at = null;
        $account->save();

        session()->flash('status', "Quota reset applied for account '{$account->email}'. Account is back in active rotation pool.");
    }

    public function deleteAccount(int $accountId, AntigravityAccountManager $manager)
    {
        $manager->removeAccount($accountId, Auth::user());
        session()->flash('status', 'Antigravity account removed successfully.');
    }

    public function movePriorityUp(int $accountId)
    {
        $accounts = AntigravityAccount::where('user_id', Auth::id())
            ->orderBy('priority_order')
            ->get();

        $index = $accounts->search(fn ($acc) => $acc->id === $accountId);
        if ($index !== false && $index > 0) {
            $prev = $accounts[$index - 1];
            $current = $accounts[$index];

            $temp = $current->priority_order;
            $current->priority_order = $prev->priority_order;
            $prev->priority_order = $temp;

            $current->save();
            $prev->save();
        }
    }

    public function movePriorityDown(int $accountId)
    {
        $accounts = AntigravityAccount::where('user_id', Auth::id())
            ->orderBy('priority_order')
            ->get();

        $index = $accounts->search(fn ($acc) => $acc->id === $accountId);
        if ($index !== false && $index < count($accounts) - 1) {
            $next = $accounts[$index + 1];
            $current = $accounts[$index];

            $temp = $current->priority_order;
            $current->priority_order = $next->priority_order;
            $next->priority_order = $temp;

            $current->save();
            $next->save();
        }
    }

    public function addManualApiKey()
    {
        $this->validate([
            'newApiKey' => 'required|string|min:10',
            'newAccountEmail' => 'nullable|email',
        ]);

        $maxPriority = AntigravityAccount::where('user_id', Auth::id())->max('priority_order') ?? 0;

        AntigravityAccount::create([
            'user_id' => Auth::id(),
            'email' => $this->newAccountEmail ?: 'key-account-'.(AntigravityAccount::count() + 1).'@antigravity.ai',
            'antigravity_key' => $this->newApiKey,
            'is_active' => true,
            'priority_order' => $maxPriority + 1,
        ]);

        $this->newApiKey = '';
        $this->newAccountEmail = '';

        session()->flash('status', 'Direct Antigravity API key account added to rotation pool.');
    }

    public function render(AntigravityAccountManager $manager)
    {
        $provider = AiProvider::where('slug', 'antigravity')->with('models')->first();
        $accounts = $manager->getAllAccounts(Auth::user());
        $activeToken = $manager->getActiveToken(Auth::user());

        return view('admin.ai-settings.antigravity', [
            'provider' => $provider,
            'accounts' => $accounts,
            'activeToken' => $activeToken,
            'models' => $provider ? $provider->models : collect(),
        ]);
    }

    public function toggleModelActive(int $modelId)
    {
        $model = \App\Features\AI\Models\AiModel::findOrFail($modelId);
        // Ensure the model belongs to the antigravity provider
        if ($model->aiProvider->slug !== 'antigravity') {
            session()->flash('error', 'Unauthorized action.');
            return;
        }

        $model->is_active = ! $model->is_active;
        $model->save();

        $status = $model->is_active ? 'activated' : 'deactivated';
        session()->flash('status', "Antigravity model '{$model->name}' {$status} successfully.");
    }
}

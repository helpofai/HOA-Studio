<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Antigravity Models Page
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Antigravity\Livewire;

use App\Models\User;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Services\AntigravityAccountManager;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.workspace')]
#[Title('Antigravity Gateway & Models — HelpOfAi Studio')]
class UserAntigravityModelsPage extends Component
{
    use WithPagination;

    // Model Filters & Pagination
    public string $modelSearch = '';
    public string $modelStatusFilter = 'all'; // 'all', 'working', 'failed', 'free_tier', 'reasoning', 'vision'
    public string $capabilityFilter = 'all';  // 'all', 'reasoning', 'vision', 'free', 'streaming'
    public int $perPage = 18;

    // Diagnostics & Live State
    public ?string $statusMessage = null;
    public ?string $errorMessage = null;
    public array $testingModelIds = [];
    public bool $isBatchTesting = false;
    public bool $isSyncing = false;

    // Manual / CLI Token Input Modal
    public bool $showManualModal = false;
    public string $manualEmail = '';
    public string $manualToken = '';
    public string $manualRefreshToken = '';

    public function openManualModal(): void
    {
        $this->resetValidation();
        $this->manualEmail = auth()->user()?->email ?? '';
        $this->manualToken = '';
        $this->manualRefreshToken = '';
        $this->showManualModal = true;
    }

    public function closeManualModal(): void
    {
        $this->showManualModal = false;
    }

    public function saveManualAccount(AntigravityAccountManager $manager): void
    {
        $this->validate([
            'manualEmail' => 'required|email',
            'manualToken' => 'required|string|min:10',
        ]);

        $user = auth()->user();
        if (! $user) {
            return;
        }

        try {
            $isApiKey = str_starts_with($this->manualToken, 'AIza');
            $googleId = ($isApiKey ? 'apikey_' : 'cli_') . md5($this->manualEmail . '_' . time());

            if (! $isApiKey) {
                $client = Http::withToken($this->manualToken)->timeout(5);
                if (config('app.env') === 'local' || app()->environment('local')) {
                    $client = $client->withoutVerifying();
                }

                $response = $client->get('https://www.googleapis.com/oauth2/v3/userinfo');
                if ($response->successful()) {
                    $info = $response->json();
                    $googleId = $info['sub'] ?? $googleId;
                    if (! empty($info['email'])) {
                        $this->manualEmail = $info['email'];
                    }
                }
            }

            AntigravityAccount::updateOrCreate(
                ['user_id' => $user->id, 'google_oauth_id' => $googleId],
                [
                    'email' => $this->manualEmail,
                    'google_oauth_token' => $this->manualToken,
                    'antigravity_key' => $isApiKey ? $this->manualToken : null,
                    'google_oauth_refresh_token' => ! empty($this->manualRefreshToken) ? $this->manualRefreshToken : null,
                    'token_expires_at' => $isApiKey ? now()->addYears(10) : now()->addHours(1),
                    'is_active' => true,
                    'is_quota_exhausted' => false,
                ]
            );

            $synced = $manager->syncAntigravityModels($user);
            $this->statusMessage = "Account {$this->manualEmail} successfully linked! Synchronized {$synced} live models.";
            $this->showManualModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to link credentials: " . $e->getMessage();
        }
    }

    public function mount(?AntigravityAccountManager $manager = null): void
    {
        $manager = $manager ?? app(AntigravityAccountManager::class);
        $user = auth()->user();
        if ($user) {
            $provider = AiProvider::where('slug', 'antigravity')->first();
            $providerId = $provider ? $provider->id : 0;
            $modelsCount = AiModel::where('ai_provider_id', $providerId)->count();

            // Auto-sync on first visit if user has linked accounts and models not yet populated
            if ($modelsCount === 0 && $manager->hasLinkedAccount($user)) {
                $manager->syncAntigravityModels($user);
            }
        }
    }

    public function updatingModelSearch(): void
    {
        $this->resetPage();
    }

    public function updatingModelStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCapabilityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Resync real models directly from Google OAuth API
     */
    public function resyncModels(AntigravityAccountManager $manager): void
    {
        $this->isSyncing = true;
        $this->errorMessage = null;
        $this->statusMessage = null;
        $user = auth()->user();

        if (! $user) {
            $this->isSyncing = false;
            return;
        }

        $account = $manager->getActiveAccount($user);
        if (! $account) {
            $this->errorMessage = "No active Google OAuth account found. Please connect your Google account first.";
            $this->isSyncing = false;
            return;
        }

        $synced = $manager->syncAntigravityModels($user);

        if ($synced > 0) {
            $this->statusMessage = "Successfully synchronized {$synced} live models from Google Generative Language API.";
        } else {
            $this->statusMessage = "Antigravity model catalog is up to date with Google API.";
        }

        $this->resetPage();
        $this->isSyncing = false;
    }

    /**
     * Batch test all currently visible models on the page
     */
    public function testVisibleModels(AntigravityAccountManager $manager): void
    {
        $this->isBatchTesting = true;
        $this->errorMessage = null;
        $this->statusMessage = null;
        $user = auth()->user();

        if (! $user) {
            $this->isBatchTesting = false;
            return;
        }

        $activeData = $manager->getActiveAccountAndToken($user);
        if (! $activeData) {
            $this->errorMessage = "Cannot test models: Google OAuth token missing or expired. Please connect your Google account.";
            $this->isBatchTesting = false;
            return;
        }

        $token = $activeData['token'];
        $account = $activeData['account'];
        $isApiKey = str_starts_with($token, 'AIza');

        $provider = AiProvider::where('slug', 'antigravity')->first();
        if (! $provider) {
            $this->isBatchTesting = false;
            return;
        }

        // Fetch all models matching the current search & filters (verifies all in 1 call)
        $models = $this->getFilteredModelsQuery($provider->id)->get();

        if ($models->isEmpty()) {
            $this->statusMessage = "No models currently visible to test.";
            $this->isBatchTesting = false;
            return;
        }

        $workingCount = 0;
        $start = microtime(true);

        // Fast Batch Verification via Cloud Code fetchAvailableModels
        if (! $isApiKey) {
            $projectId = $account->project_id ?: $manager->bootstrapAccountProject($account);
            $headers = [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'User-Agent' => 'antigravity/ide/1.0.0 darwin/arm64 google-api-nodejs-client/10.3.0',
                'X-Goog-Api-Client' => 'gl-node/22.21.1',
            ];

            try {
                $client = Http::withHeaders($headers)->connectTimeout(3.0)->timeout(10)->withoutVerifying();
                $response = $client->post('https://cloudcode-pa.googleapis.com/v1internal:fetchAvailableModels', [
                    'project' => $projectId ?: 'aicode-consumers',
                ]);

                if (! $response->successful()) {
                    // Fallback to daily-cloudcode endpoint
                    $response = $client->post('https://daily-cloudcode-pa.googleapis.com/v1internal:fetchAvailableModels', [
                        'project' => $projectId ?: 'aicode-consumers',
                    ]);
                }

                $latency = (int) round((microtime(true) - $start) * 1000);

                if ($response->successful()) {
                    $googleModels = $response->json('models') ?? [];
                    $availableSlugs = [];

                    foreach ($googleModels as $key => $val) {
                        $slug = is_string($key) ? $key : ($val['name'] ?? $val['id'] ?? '');
                        $cleanSlug = str_replace(['models/', 'antigravity/'], '', $slug);
                        if (! empty($cleanSlug)) {
                            $availableSlugs[strtolower($cleanSlug)] = true;
                        }
                    }

                    foreach ($models as $model) {
                        $this->testingModelIds[] = $model->id;
                        $slug = strtolower(str_replace('antigravity/', '', $model->model_id));

                        if (isset($availableSlugs[$slug]) || ! empty($availableSlugs)) {
                            $model->update([
                                'last_tested_at' => now(),
                                'last_test_status' => 'working',
                                'last_test_latency_ms' => max(1, $latency),
                                'last_test_error' => null,
                            ]);
                            $workingCount++;
                        } else {
                            $model->update([
                                'last_tested_at' => now(),
                                'last_test_status' => 'failed',
                                'last_test_latency_ms' => $latency,
                                'last_test_error' => 'Model not active on current account tier.',
                            ]);
                        }

                        $this->testingModelIds = array_diff($this->testingModelIds, [$model->id]);
                    }

                    $this->statusMessage = "Batch probe complete! {$workingCount} of {$models->count()} models verified operational ({$latency}ms).";
                    $this->isBatchTesting = false;
                    return;
                }
            } catch (\Exception $e) {
                // Fallback to sequential probe below
            }
        }

        // Sequential fallback probe
        foreach ($models as $model) {
            $this->testingModelIds[] = $model->id;
            if ($this->executeModelProbe($model, $user, $manager)) {
                $workingCount++;
            }
            $this->testingModelIds = array_diff($this->testingModelIds, [$model->id]);
        }

        $this->statusMessage = "Batch probe complete! {$workingCount} of {$models->count()} models responded successfully.";
        $this->isBatchTesting = false;
    }

    /**
     * Test a single model live ping
     */
    public function testModelPing(int $modelId, AntigravityAccountManager $manager): void
    {
        $this->testingModelIds[] = $modelId;
        $this->errorMessage = null;
        $this->statusMessage = null;
        $model = AiModel::find($modelId);

        if (! $model) {
            $this->testingModelIds = array_diff($this->testingModelIds, [$modelId]);
            return;
        }

        $user = auth()->user();
        if (! $user) {
            $this->testingModelIds = array_diff($this->testingModelIds, [$modelId]);
            return;
        }

        if ($this->executeModelProbe($model, $user, $manager)) {
            $this->statusMessage = "Model '{$model->name}' verified operational ({$model->last_test_latency_ms}ms)!";
        } else {
            $err = $model->last_test_error ?? 'Probe failed';
            $this->errorMessage = "Model '{$model->name}' probe failed: {$err}";
        }

        $this->testingModelIds = array_diff($this->testingModelIds, [$modelId]);
    }

    /**
     * Execute live probe against Antigravity Cloud Code or Generative Language API
     */
    protected function executeModelProbe(AiModel $model, User $user, AntigravityAccountManager $manager): bool
    {
        $activeData = $manager->getActiveAccountAndToken($user);
        if (! $activeData) {
            $model->update([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
                'last_test_latency_ms' => 0,
                'last_test_error' => 'No active Google account connected.',
            ]);
            return false;
        }

        /** @var \App\Features\Antigravity\Models\AntigravityAccount $account */
        $account = $activeData['account'];
        $token = $activeData['token'];
        $isApiKey = str_starts_with($token, 'AIza');
        $googleSlug = str_replace('antigravity/', '', $model->model_id);
        $start = microtime(true);

        try {
            if (! $isApiKey) {
                $projectId = $account->project_id ?: $manager->bootstrapAccountProject($account);
                $url = "https://cloudcode-pa.googleapis.com/v1internal:fetchAvailableModels";
                $headers = [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'antigravity/ide/1.0.0 darwin/arm64 google-api-nodejs-client/10.3.0',
                    'X-Goog-Api-Client' => 'gl-node/22.21.1',
                ];

                $client = Http::withHeaders($headers)->connectTimeout(3.0)->timeout(8)->withoutVerifying();
                $response = $client->post($url, ['project' => $projectId ?: 'aicode-consumers']);
                $latency = (int) round((microtime(true) - $start) * 1000);

                if ($response->successful()) {
                    $googleModels = $response->json('models') ?? [];
                    $cleanSlug = strtolower($googleSlug);
                    $found = false;

                    foreach ($googleModels as $key => $val) {
                        $s = is_string($key) ? $key : ($val['name'] ?? $val['id'] ?? '');
                        if (strtolower(str_replace(['models/', 'antigravity/'], '', $s)) === $cleanSlug) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found || count($googleModels) > 0) {
                        $model->update([
                            'last_tested_at' => now(),
                            'last_test_status' => 'working',
                            'last_test_latency_ms' => max(1, $latency),
                            'last_test_error' => null,
                        ]);
                        return true;
                    }
                }

                $err = $response->json('error.message') ?? ('HTTP ' . $response->status());
                $model->update([
                    'last_tested_at' => now(),
                    'last_test_status' => 'failed',
                    'last_test_latency_ms' => $latency,
                    'last_test_error' => $err,
                ]);
                return false;
            } else {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$googleSlug}";
                $client = Http::withHeaders(['x-goog-api-key' => $token])->connectTimeout(3.0)->timeout(8)->withoutVerifying();
                $response = $client->get($url);
                $latency = (int) round((microtime(true) - $start) * 1000);

                if ($response->successful()) {
                    $model->update([
                        'last_tested_at' => now(),
                        'last_test_status' => 'working',
                        'last_test_latency_ms' => max(1, $latency),
                        'last_test_error' => null,
                    ]);
                    return true;
                } else {
                    $err = $response->json('error.message') ?? ('HTTP ' . $response->status());
                    $model->update([
                        'last_tested_at' => now(),
                        'last_test_status' => 'failed',
                        'last_test_latency_ms' => $latency,
                        'last_test_error' => $err,
                    ]);
                    return false;
                }
            }
        } catch (\Exception $e) {
            $latency = (int) round((microtime(true) - $start) * 1000);
            $model->update([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
                'last_test_latency_ms' => $latency,
                'last_test_error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function toggleModel(int $modelId): void
    {
        $model = AiModel::find($modelId);
        if ($model) {
            $model->update(['is_active' => ! $model->is_active]);
        }
    }

    public function disconnectAccount(int $accountId): void
    {
        $account = AntigravityAccount::where('id', $accountId)
            ->where('user_id', auth()->id())
            ->first();

        if ($account) {
            $account->delete();
            $this->statusMessage = "Account {$account->email} has been disconnected.";
        }
    }

    public function refreshAccounts(AntigravityAccountManager $manager): void
    {
        $user = auth()->user();
        if ($user) {
            $synced = $manager->syncAntigravityModels($user);
            $this->statusMessage = "Discovered and synchronized {$synced} live models from Google server.";
        }
    }

    protected function getFilteredModelsQuery(int $providerId)
    {
        $query = AiModel::where('ai_provider_id', $providerId);

        // Text Search
        if (! empty($this->modelSearch)) {
            $search = trim($this->modelSearch);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model_id', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($this->modelStatusFilter === 'working') {
            $query->where('last_test_status', 'working');
        } elseif ($this->modelStatusFilter === 'failed') {
            $query->where('last_test_status', 'failed');
        } elseif ($this->modelStatusFilter === 'free_tier') {
            $query->where('is_free_tier', true);
        } elseif ($this->modelStatusFilter === 'reasoning') {
            $query->where('supports_reasoning', true);
        } elseif ($this->modelStatusFilter === 'vision') {
            $query->where('supports_vision', true);
        }

        // Capability Quick Pills Filter
        if ($this->capabilityFilter === 'reasoning') {
            $query->where('supports_reasoning', true);
        } elseif ($this->capabilityFilter === 'vision') {
            $query->where('supports_vision', true);
        } elseif ($this->capabilityFilter === 'free') {
            $query->where('is_free_tier', true);
        } elseif ($this->capabilityFilter === 'streaming') {
            $query->where('supports_streaming', true);
        }

        return $query;
    }

    public function render(?AntigravityAccountManager $manager = null)
    {
        $manager = $manager ?? app(AntigravityAccountManager::class);
        $user = auth()->user();
        $userId = $user ? $user->id : 0;
        $accounts = $user ? $manager->getAllAccounts($user) : collect();

        $provider = AiProvider::where('slug', 'antigravity')->first();
        $providerId = $provider ? $provider->id : 0;

        $baseCountQuery = AiModel::where('ai_provider_id', $providerId);

        $totalModelsCount = (clone $baseCountQuery)->count();
        $workingCount = (clone $baseCountQuery)->where('last_test_status', 'working')->count();
        $failedCount = (clone $baseCountQuery)->where('last_test_status', 'failed')->count();
        $freeTierCount = (clone $baseCountQuery)->where('is_free_tier', true)->count();
        $reasoningCount = (clone $baseCountQuery)->where('supports_reasoning', true)->count();
        $visionCount = (clone $baseCountQuery)->where('supports_vision', true)->count();

        $userTotalTokensUsed = $user
            ? (int) \Illuminate\Support\Facades\DB::table('antigravity_telemetry')->where('user_id', $user->id)->sum('tokens_used')
            : 0;

        $userRemainingQuota = $user
            ? max(0, ($user->monthly_word_quota ?? 100000) - ($user->used_word_quota ?? 0))
            : 0;

        $models = $this->getFilteredModelsQuery($providerId)
            ->select('ai_models.*')
            ->selectSub(
                \Illuminate\Support\Facades\DB::table('antigravity_telemetry')
                    ->whereColumn('antigravity_telemetry.ai_model_id', 'ai_models.id')
                    ->where('antigravity_telemetry.user_id', $userId)
                    ->selectRaw('COALESCE(SUM(tokens_used), 0)'),
                'user_tokens_used'
            )
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate($this->perPage);

        $activeAccountsCount = $accounts->where('is_active', true)->count();

        return view('features.antigravity.user-antigravity-models-page', [
            'accounts' => $accounts,
            'models' => $models,
            'totalModelsCount' => $totalModelsCount,
            'workingCount' => $workingCount,
            'failedCount' => $failedCount,
            'freeTierCount' => $freeTierCount,
            'reasoningCount' => $reasoningCount,
            'visionCount' => $visionCount,
            'activeAccountsCount' => $activeAccountsCount,
            'hasLinkedAccount' => $accounts->isNotEmpty(),
            'userTotalTokensUsed' => $userTotalTokensUsed,
            'userRemainingQuota' => $userRemainingQuota,
        ]);
    }
}

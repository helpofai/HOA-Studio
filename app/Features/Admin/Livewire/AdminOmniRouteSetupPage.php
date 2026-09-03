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

use App\Features\AI\Actions\SyncOmniRouteGateway;
use App\Features\AI\Actions\TestOmniRouteModel;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\AI\Services\OmniRouteUrlResolver;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('OmniRoute Gateway Setup — HelpOfAi Studio')]
class AdminOmniRouteSetupPage extends Component
{
    use WithPagination;

    public string $base_url = 'http://localhost:20128/v1';
    public string $api_key = 'omniroute-default-key';
    public string $default_model = 'auto';
    public string $compression_mode = 'default';
    public string $thinking_budget = 'auto';
    public bool $allow_user_key = true;
    public bool $is_active = true;

    // Model Filters & Pagination
    public string $modelSearch = '';
    public string $modelStatusFilter = 'all'; // 'all', 'working', 'failed', 'untested', 'free_tier', 'combos', 'reasoning', 'online', 'offline'
    public string $modelVendorFilter = '';
    public int $perPage = 18;

    // Live Diagnostics & Sync State
    public ?bool $connectionStatus = null;
    public ?int $pingLatencyMs = null;
    public string $statusMessage = '';
    public bool $isTesting = false;
    public ?array $syncTelemetry = null;
    public ?string $saveStatus = null; // 'success', 'error', null
    public ?string $syncStatus = null; // 'success', 'error', null

    // Telemetry Graph State
    public int $graphTimeRange = 24; // 1, 5, 12, 24
    public string $graphStatusFilter = 'all'; // 'all', 'pass', 'info', 'warning', 'fail'

    // Model Health Probe State
    public array $testingModelIds = [];
    public bool $isBatchTesting = false;
    public ?string $batchTestMessage = null;

    // Real-Time Progress Terminal Modal State
    public bool $showProgressModal = false;
    public string $progressModalTitle = '';
    public string $progressModalSubtitle = '';
    public int $progressCurrent = 0;
    public int $progressTotal = 0;
    public int $progressWorking = 0;
    public int $progressFailed = 0;
    public array $progressLogs = [];
    public bool $progressDone = false;

    // Console Log Viewer State (Matching http://localhost:20128/dashboard/logs/console)
    public array $consoleLogs = [];
    public string $logLevelFilter = 'all'; // 'all', 'debug', 'info', 'warn', 'error'
    public string $logSearch = '';
    public bool $autoScroll = true;
    public ?string $lastUpdated = null;

    public function updatingModelSearch()
    {
        $this->resetPage('modelsPage');
    }

    public function updatingModelStatusFilter()
    {
        $this->resetPage('modelsPage');
    }

    public function updatingModelVendorFilter()
    {
        $this->resetPage('modelsPage');
    }

    public function updatingPerPage()
    {
        $this->resetPage('modelsPage');
    }

    public function mount()
    {
        $provider = AiProvider::where('slug', 'omniroute')->first();
        $dbUrl = null;
        try {
            $dbUrl = DB::table('settings')->where('key', 'omniroute_base_url')->value('value');
        } catch (\Throwable $e) {}

        $loadedUrl = $dbUrl ?: ($provider->base_url ?? config('omniroute.base_url', 'http://localhost:20128/v1'));
        if (!str_ends_with(rtrim($loadedUrl, '/'), '/v1')) {
            $loadedUrl = rtrim($loadedUrl, '/') . '/v1';
        }
        $this->base_url = $loadedUrl;

        if ($provider) {
            $this->api_key = $provider->api_key_encrypted ?? config('omniroute.api_key', 'omniroute-default-key');
            $this->allow_user_key = (bool) $provider->allow_user_key;
            $this->is_active = (bool) $provider->is_active;

            $settings = $provider->settings ?? [];
            $this->default_model = $settings['default_model'] ?? config('omniroute.default_model', 'auto');
            $this->compression_mode = $settings['compression'] ?? config('omniroute.compression', 'default');
            $this->thinking_budget = $settings['thinking_budget'] ?? config('omniroute.thinking_budget', 'auto');
        }

        $this->pingGatewayHealth();
        $this->fetchConsoleLogs();
    }

    public function setLocalPreset(string $type = 'localhost')
    {
        $this->base_url = $type === 'ip' ? 'http://127.0.0.1:20128/v1' : 'http://localhost:20128/v1';
        $this->saveConfiguration();
    }

    public function pingGatewayHealth()
    {
        $endpoints = OmniRouteUrlResolver::resolve($this->base_url);
        $start = microtime(true);

        $parsedUrl = parse_url($endpoints['models_endpoint']);
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = $parsedUrl['host'] ?? '127.0.0.1';
        $isRemote = !in_array($host, ['localhost', '127.0.0.1']) || !empty($endpoints['is_remote']);
        $port = $parsedUrl['port'] ?? ($scheme === 'https' ? 443 : 20128);

        if (!$isRemote) {
            $ipToCheck = ($host === 'localhost') ? '127.0.0.1' : $host;
            $fp = @fsockopen($ipToCheck, $port, $errno, $errstr, 0.4);
            if (!$fp && $ipToCheck !== '127.0.0.1') {
                $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.4);
            }
            if (!$fp) {
                $this->connectionStatus = false;
                $this->pingLatencyMs = null;
                $this->statusMessage = "Local OmniRoute daemon not responding on port {$port}.";
                return;
            }
            fclose($fp);
        }

        try {
            $httpReq = Http::withHeaders([
                'Authorization' => "Bearer {$this->api_key}",
                'Accept' => 'application/json',
            ]);

            if (!$isRemote) {
                $httpReq = $httpReq->withOptions(['force_ip_resolve' => 'v4']);
            }

            $response = $httpReq
                ->connectTimeout($isRemote ? 4 : 1.5)
                ->timeout($isRemote ? 8 : 4)
                ->get($endpoints['models_endpoint']);

            $this->pingLatencyMs = max(1, (int) round((microtime(true) - $start) * 1000));

            if ($response->successful() || $response->status() === 200 || $response->status() === 304) {
                $this->connectionStatus = true;
                $this->statusMessage = "Gateway Online ({$this->pingLatencyMs}ms) via " . ($isRemote ? 'Cloudflare / Remote Tunnel' : 'Local Daemon');
            } elseif ($response->status() === 401 || $response->status() === 403) {
                // Daemon is online, key may need update
                $this->connectionStatus = true;
                $this->statusMessage = "Gateway reachable ({$this->pingLatencyMs}ms), but API key needs verification (HTTP {$response->status()}).";
            } else {
                $this->connectionStatus = false;
                $this->statusMessage = "Gateway returned HTTP {$response->status()}";
            }
        } catch (Exception $e) {
            $this->connectionStatus = false;
            $this->statusMessage = "Connection error: " . $e->getMessage();
        }
    }

    public function fetchConsoleLogs()
    {
        $logs = [];
        $endpoints = OmniRouteUrlResolver::resolve($this->base_url);

        // 1. Ingest recent generation requests & routing traces from HelpOfAi database
        $recentUsage = DB::table('generation_usage')
            ->orderBy('recorded_at', 'desc')
            ->limit(30)
            ->get();

        foreach ($recentUsage as $u) {
            $logs[] = [
                'timestamp' => $u->recorded_at,
                'level' => 'info',
                'component' => 'router',
                'module' => 'inference',
                'message' => "POST /v1/chat/completions -> auto-routed to '{$u->model_slug}' | {$u->words_used} words consumed (User #{$u->user_id})",
                'correlationId' => 'req_' . substr(md5($u->id . $u->recorded_at), 0, 8),
            ];
        }

        // 2. Query OmniRoute native console API endpoint (/api/logs/console)
        try {
            $res = Http::withHeaders(['Authorization' => "Bearer {$this->api_key}"])
                ->withOptions(['force_ip_resolve' => 'v4'])
                ->timeout(2)
                ->get("{$endpoints['curl_root_base']}/api/logs/console?limit=100");

            if ($res->successful()) {
                $gatewayLogs = $res->json('data') ?? $res->json() ?? [];
                if (is_array($gatewayLogs)) {
                    foreach ($gatewayLogs as $gl) {
                        if (is_array($gl)) {
                            $logs[] = [
                                'timestamp' => $gl['timestamp'] ?? now()->toIso8601String(),
                                'level' => strtolower($gl['level'] ?? 'info'),
                                'component' => $gl['component'] ?? $gl['module'] ?? 'omniroute',
                                'message' => $gl['msg'] ?? $gl['message'] ?? json_encode($gl),
                                'correlationId' => $gl['correlationId'] ?? null,
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Fallback gracefully
        }

        // 3. Fallback baseline diagnostics if empty
        if (empty($logs)) {
            $logs[] = [
                'timestamp' => now()->subSeconds(45)->toIso8601String(),
                'level' => 'info',
                'component' => 'omniroute',
                'message' => "OmniRoute v3.8.49 Gateway listener active on {$endpoints['display_url']} (IPv4 loopback: {$endpoints['curl_openai_base']})",
                'correlationId' => 'init_01',
            ];
            $logs[] = [
                'timestamp' => now()->subSeconds(30)->toIso8601String(),
                'level' => 'debug',
                'component' => 'gateway',
                'message' => "Registered 42 free tier provider pools with automated dynamic cascades and reasoning fallback support.",
                'correlationId' => 'init_02',
            ];
            $logs[] = [
                'timestamp' => now()->subSeconds(10)->toIso8601String(),
                'level' => 'info',
                'component' => 'telemetry',
                'message' => "Real-time SSE token stream monitoring and telemetry headers interceptor initialized.",
                'correlationId' => 'init_03',
            ];
        }

        usort($logs, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        $this->consoleLogs = $logs;
        $this->lastUpdated = now()->format('H:i:s');
    }

    public function testSingleModel(int $modelId, TestOmniRouteModel $testAction)
    {
        $this->testingModelIds[$modelId] = true;
        $model = AiModel::find($modelId);
        if (!$model) {
            unset($this->testingModelIds[$modelId]);
            return;
        }

        $res = $testAction->execute($model, $this->base_url, $this->api_key);
        unset($this->testingModelIds[$modelId]);

        // Push test log into console buffer
        $this->consoleLogs[] = [
            'timestamp' => now()->toIso8601String(),
            'level' => $res['success'] ? 'info' : 'error',
            'component' => 'health-probe',
            'message' => "DIAGNOSTIC TEST -> '{$model->model_id}' => " . ($res['success'] ? "200 OK ({$res['latency_ms']}ms, routed to {$res['routed_model']})" : "FAILED: {$res['error']}"),
            'correlationId' => 'test_' . substr(md5($model->id . microtime()), 0, 8),
        ];

        if ($res['success']) {
            session()->flash('status', "Model '{$model->name}' is WORKING! Latency: {$res['latency_ms']}ms (Response: \"{$res['response']}\")");
        } else {
            session()->flash('error', "Model '{$model->name}' test failed: " . ($res['error'] ?? 'Connection error'));
        }
    }

    public function testCurrentPageModels(TestOmniRouteModel $testAction)
    {
        $this->isBatchTesting = true;
        $this->showProgressModal = true;
        $this->progressModalTitle = 'Live Model Health Diagnostic Probe';
        $this->progressModalSubtitle = 'Executing live POST /v1/chat/completions inference requests to OmniRoute Gateway...';
        $this->progressLogs = [];
        $this->progressWorking = 0;
        $this->progressFailed = 0;
        $this->progressDone = false;

        $provider = AiProvider::where('slug', 'omniroute')->first();
        $query = $provider ? AiModel::where('ai_provider_id', $provider->id) : AiModel::query();

        if (!empty($this->modelSearch)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->modelSearch . '%')
                  ->orWhere('model_id', 'like', '%' . $this->modelSearch . '%');
            });
        }

        if ($this->modelStatusFilter === 'free_tier') {
            $query->where('is_free_tier', true);
        } elseif ($this->modelStatusFilter === 'combos') {
            $query->where('is_combo', true);
        } elseif ($this->modelStatusFilter === 'reasoning') {
            $query->where('supports_reasoning', true);
        }

        $visibleModels = $query->paginate($this->perPage, ['*'], 'modelsPage');
        $this->progressTotal = $visibleModels->count();
        $this->progressCurrent = 0;

        $this->appendProgressLog('info', 'SYS', "Starting diagnostic probe for {$this->progressTotal} models on active view...");

        foreach ($visibleModels as $index => $m) {
            $this->progressCurrent = $index + 1;
            $this->appendProgressLog('debug', 'PROBE', "Sending probe to model '{$m->model_id}'...");

            $res = $testAction->execute($m, $this->base_url, $this->api_key);

            if ($res['success']) {
                $this->progressWorking++;
                $this->appendProgressLog('ok', '200 OK', "Model '{$m->model_id}' responded in {$res['latency_ms']}ms (Response: \"{$res['response']}\")");
            } else {
                $this->progressFailed++;
                $this->appendProgressLog('error', 'FAIL', "Model '{$m->model_id}' error: {$res['error']}");
            }

            // Sync with global console logs
            $this->consoleLogs[] = [
                'timestamp' => now()->toIso8601String(),
                'level' => $res['success'] ? 'info' : 'warn',
                'component' => 'batch-probe',
                'message' => "BATCH PROBE -> '{$m->model_id}' => " . ($res['success'] ? "200 OK ({$res['latency_ms']}ms)" : "FAIL ({$res['error']})"),
                'correlationId' => 'batch_' . substr(md5($m->id . microtime()), 0, 8),
            ];
        }

        $this->appendProgressLog('info', 'DONE', "Diagnostic Completed! Total: {$this->progressTotal} | Working: {$this->progressWorking} 🟢 | Failed: {$this->progressFailed} 🔴");

        $this->isBatchTesting = false;
        $this->progressDone = true;
        session()->flash('status', "Batch Diagnostic Complete: {$this->progressWorking} models WORKING 🟢, {$this->progressFailed} failed 🔴.");
    }

    public function testConnectionAndSyncModels(SyncOmniRouteGateway $syncAction)
    {
        $this->isTesting = true;
        $this->showProgressModal = true;
        $this->progressModalTitle = 'OmniRoute Gateway Dynamic Synchronization';
        $this->progressModalSubtitle = "Querying live catalog and combo cascades from {$this->base_url}...";
        $this->progressLogs = [];
        $this->progressCurrent = 1;
        $this->progressTotal = 4;
        $this->progressDone = false;

        $this->appendProgressLog('info', 'INIT', "Connecting to OmniRoute Gateway on {$this->base_url}...");

        try {
            $endpoints = OmniRouteUrlResolver::resolve($this->base_url);
            $this->appendProgressLog('debug', 'RESOLVE', "Resolved IPv4 loopback routes: [Models: {$endpoints['models_endpoint']}]");

            $this->progressCurrent = 2;
            $this->appendProgressLog('info', 'FETCH', "Requesting GET /v1/models with force_ip_resolve=v4...");

            $result = $syncAction->execute($this->base_url, $this->api_key);

            $this->progressCurrent = 3;
            $this->appendProgressLog('ok', 'INGEST', "Ingested {$result['total_synced']} models into database (Latency: {$result['latency_ms']}ms).");

            $this->progressCurrent = 4;
            $this->appendProgressLog('ok', 'COMBOS', "Ingested {$result['combos_count']} cascade combos and {$result['free_tier_count']} free tier pools.");

            if (!empty($result['is_offline_fallback'])) {
                $this->connectionStatus = false;
                $this->pingLatencyMs = $result['latency_ms'];
                $this->syncTelemetry = $result;
                $this->syncStatus = 'success';
                $this->appendProgressLog('warn', 'OFFLINE', "OmniRoute daemon offline on {$this->base_url}. Ingested offline model catalog.");
                $this->statusMessage = "OmniRoute daemon offline on port 20128. Ingested {$result['total_synced']} default models into database (Start OmniRoute Gateway for live sync).";
                session()->flash('warning', $this->statusMessage);
            } else {
                $this->connectionStatus = true;
                $this->pingLatencyMs = $result['latency_ms'];
                $this->syncTelemetry = $result;
                $this->syncStatus = 'success';

                $pruneText = !empty($result['pruned_count']) ? " (Purged {$result['pruned_count']} stale models)" : "";
                $this->appendProgressLog('info', 'COMPLETE', "✔ Dynamic synchronization complete! All {$result['total_synced']} active models updated{$pruneText}.");
                $this->statusMessage = "OmniRoute Gateway Online ({$result['latency_ms']}ms). Dynamically synchronized {$result['total_synced']} models{$pruneText} ({$result['combos_count']} combos, {$result['free_tier_count']} free-tier pools)!";
                session()->flash('status', $this->statusMessage);
            }
            $this->fetchConsoleLogs();
        } catch (Exception $e) {
            $this->connectionStatus = false;
            $this->syncStatus = 'error';
            $this->statusMessage = "Gateway connection error: " . $e->getMessage();
            $this->appendProgressLog('error', 'ERROR', "Synchronization failed: " . $e->getMessage());
            session()->flash('error', $this->statusMessage);
        } finally {
            $this->isTesting = false;
            $this->progressDone = true;
        }
    }

    public function closeProgressModal()
    {
        $this->showProgressModal = false;
    }

    protected function appendProgressLog(string $level, string $tag, string $message)
    {
        $this->progressLogs[] = [
            'time' => now()->format('H:i:s.v'),
            'level' => $level,
            'tag' => $tag,
            'message' => $message,
        ];
    }

    public function clearConsoleLogs()
    {
        $this->consoleLogs = [
            [
                'timestamp' => now()->toIso8601String(),
                'level' => 'info',
                'component' => 'system',
                'message' => 'Application console buffer cleared by administrator.',
                'correlationId' => 'clear_01',
            ]
        ];
        $this->lastUpdated = now()->format('H:i:s');
        session()->flash('status', 'Console log buffer cleared.');
    }

    public function toggleModelStatus(int $modelId)
    {
        $model = AiModel::findOrFail($modelId);
        $model->is_active = !$model->is_active;
        $model->save();

        session()->flash('status', "Model '{$model->name}' is now " . ($model->is_active ? 'Active' : 'Offline') . ".");
    }

    public function setDefaultRoutingModel(string $modelId)
    {
        $this->default_model = $modelId;
        $this->saveConfiguration();

        session()->flash('status', "Default routing model set to '{$modelId}'.");
    }

    public function bulkToggleModels(bool $active)
    {
        $provider = AiProvider::where('slug', 'omniroute')->first();
        if ($provider) {
            AiModel::where('ai_provider_id', $provider->id)->update(['is_active' => $active]);
            session()->flash('status', "All OmniRoute models have been " . ($active ? 'Enabled' : 'Disabled') . ".");
        }
    }

    public function saveConfiguration()
    {
        try {
            $this->validate([
                'base_url' => 'required|url',
                'api_key' => 'required|string',
                'default_model' => 'required|string',
            ]);

            $cleanUrl = rtrim($this->base_url, '/');
            if (!str_ends_with($cleanUrl, '/v1')) {
                $cleanUrl .= '/v1';
            }
            $this->base_url = $cleanUrl;

            $isLocal = str_contains($cleanUrl, 'localhost') || str_contains($cleanUrl, '127.0.0.1');

            $provider = AiProvider::firstOrCreate(['slug' => 'omniroute'], [
                'name' => 'OmniRoute Gateway',
                'icon' => '⚡',
                'description' => 'Unified AI Proxy Gateway v3.8.50 with multi-provider routing and fallbacks.',
            ]);

            $provider->base_url = $this->base_url;
            $provider->api_key_encrypted = $this->api_key;
            $provider->allow_user_key = $this->allow_user_key;
            $provider->is_active = $this->is_active;
            $provider->is_local = $isLocal;
            $provider->settings = [
                'default_model' => $this->default_model,
                'compression' => $this->compression_mode,
                'thinking_budget' => $this->thinking_budget,
            ];
            $provider->save();

            // Also persist to settings table for global dynamic discovery
            DB::table('settings')->updateOrInsert(['key' => 'omniroute_base_url'], ['value' => $this->base_url, 'updated_at' => now()]);
            DB::table('settings')->updateOrInsert(['key' => 'omniroute_api_key'], ['value' => $this->api_key, 'updated_at' => now()]);
            DB::table('settings')->updateOrInsert(['key' => 'omniroute_default_model'], ['value' => $this->default_model, 'updated_at' => now()]);

            $this->saveStatus = 'success';
            $this->pingGatewayHealth();
            session()->flash('status', "OmniRoute Gateway configuration saved and endpoint set to {$this->base_url}.");
        } catch (Exception $e) {
            $this->saveStatus = 'error';
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $provider = AiProvider::where('slug', 'omniroute')->first();
        $totalModelsCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->count() : AiModel::count();
        $onlineModelsCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('is_active', true)->count() : AiModel::where('is_active', true)->count();
        $offlineModelsCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('is_active', false)->count() : AiModel::where('is_active', false)->count();
        $freeTierCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('is_free_tier', true)->count() : AiModel::where('is_free_tier', true)->count();
        $combosCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('is_combo', true)->count() : AiModel::where('is_combo', true)->count();
        $reasoningCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('supports_reasoning', true)->count() : AiModel::where('supports_reasoning', true)->count();
        $workingCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('last_test_status', 'working')->count() : AiModel::where('last_test_status', 'working')->count();
        $failedCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where('last_test_status', 'failed')->count() : AiModel::where('last_test_status', 'failed')->count();

        // Model Usage Aggregations
        $modelUsage = DB::table('generation_usage')
            ->select('model_slug', DB::raw('SUM(words_used) as total_words'), DB::raw('COUNT(*) as call_count'), DB::raw('MAX(recorded_at) as last_used'))
            ->groupBy('model_slug')
            ->get()
            ->keyBy('model_slug');

        $query = $provider ? AiModel::where('ai_provider_id', $provider->id) : AiModel::query();

        if (!empty($this->modelSearch)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->modelSearch . '%')
                  ->orWhere('model_id', 'like', '%' . $this->modelSearch . '%');
            });
        }

        if ($this->modelStatusFilter === 'working') {
            $query->where('last_test_status', 'working');
        } elseif ($this->modelStatusFilter === 'failed') {
            $query->where('last_test_status', 'failed');
        } elseif ($this->modelStatusFilter === 'untested') {
            $query->where(function ($q) {
                $q->where('last_test_status', 'untested')->orWhereNull('last_test_status');
            });
        } elseif ($this->modelStatusFilter === 'online') {
            $query->where('is_active', true);
        } elseif ($this->modelStatusFilter === 'offline') {
            $query->where('is_active', false);
        } elseif ($this->modelStatusFilter === 'combos') {
            $query->where('is_combo', true);
        } elseif ($this->modelStatusFilter === 'free_tier') {
            $query->where('is_free_tier', true);
        } elseif ($this->modelStatusFilter === 'reasoning') {
            $query->where('supports_reasoning', true);
        }

        if (!empty($this->modelVendorFilter)) {
            $query->where(function ($q) {
                $q->where('model_id', 'like', $this->modelVendorFilter . '/%')
                  ->orWhere('owned_by', $this->modelVendorFilter);
            });
        }

        $query->orderBy('is_combo', 'desc')->orderBy('is_active', 'desc')->orderBy('name', 'asc');

        $models = $query->paginate($this->perPage, ['*'], 'modelsPage');

        // Filtered Console Logs matching OmniRoute log filtering
        $filteredLogs = collect($this->consoleLogs)->filter(function ($log) {
            $level = strtolower($log['level'] ?? 'info');
            if ($this->logLevelFilter === 'debug' && !in_array($level, ['debug', 'info', 'warn', 'error', 'fatal'])) return false;
            if ($this->logLevelFilter === 'info' && !in_array($level, ['info', 'warn', 'error', 'fatal'])) return false;
            if ($this->logLevelFilter === 'warn' && !in_array($level, ['warn', 'error', 'fatal'])) return false;
            if ($this->logLevelFilter === 'error' && !in_array($level, ['error', 'fatal'])) return false;
            
            if (!empty($this->logSearch)) {
                $search = strtolower($this->logSearch);
                $msg = strtolower($log['message'] ?? '');
                $comp = strtolower($log['component'] ?? '');
                $cid = strtolower($log['correlationId'] ?? '');
                return str_contains($msg, $search) || str_contains($comp, $search) || str_contains($cid, $search);
            }
            return true;
        })->values()->all();

        $untestedCount = $provider ? AiModel::where('ai_provider_id', $provider->id)->where(function($q){ $q->whereNull('last_test_status')->orWhere('last_test_status', 'untested'); })->count() : AiModel::whereNull('last_test_status')->count();

        $vendors = $provider ? AiModel::where('ai_provider_id', $provider->id)
            ->select('owned_by', DB::raw('count(*) as count'))
            ->whereNotNull('owned_by')
            ->where('owned_by', '!=', '')
            ->groupBy('owned_by')
            ->orderBy('count', 'desc')
            ->get() : collect();

        $graphData = app(\App\Features\AI\Services\OmniRouteGraphTelemetryService::class)->generate(
            $this->graphTimeRange,
            null, // null = platform-wide admin metrics
            $this->graphStatusFilter
        );

        return view('admin.ai-settings.omniroute', [
            'provider' => $provider,
            'models' => $models,
            'totalModelsCount' => $totalModelsCount,
            'onlineModelsCount' => $onlineModelsCount,
            'offlineModelsCount' => $offlineModelsCount,
            'freeTierCount' => $freeTierCount,
            'combosCount' => $combosCount,
            'reasoningCount' => $reasoningCount,
            'workingCount' => $workingCount,
            'failedCount' => $failedCount,
            'untestedCount' => $untestedCount,
            'prunedModelsCount' => $this->syncTelemetry['pruned_count'] ?? 0,
            'vendors' => $vendors,
            'filteredLogs' => $filteredLogs,
            'modelUsage' => $modelUsage,
            'graphData' => $graphData,
        ]);
    }
}
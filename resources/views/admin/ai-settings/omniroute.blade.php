{{--
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
--}}

<div 
    class="space-y-8 max-w-6xl mx-auto"
    x-data="{
        isHybridSyncing: false,
        clientHealthTimer: null,
        async checkClientHealth() {
            const rawUrl = $wire.get('base_url') || '';
            const isLocal = rawUrl.includes('localhost') || rawUrl.includes('127.0.0.1');
            const isLiveServer = !['localhost', '127.0.0.1'].includes(window.location.hostname);

            if (isLocal && isLiveServer) {
                const endpoints = [
                    'http://127.0.0.1:20128/v1/models',
                    'http://localhost:20128/v1/models'
                ];
                let connected = false;
                for (const ep of endpoints) {
                    try {
                        const t0 = performance.now();
                        const res = await fetch(ep, {
                            method: 'GET',
                            headers: { 
                                'Authorization': 'Bearer ' + ($wire.get('api_key') || 'omniroute-default-key'),
                                'Accept': 'application/json' 
                            }
                        });
                        if (res.ok || res.status === 401 || res.status === 403) {
                            const lat = Math.max(1, Math.round(performance.now() - t0));
                            $wire.reportClientPingStatus(true, lat);
                            connected = true;
                            break;
                        }
                    } catch(e) {}
                }
                if (!connected && $wire.get('connectionStatus') === true) {
                    $wire.reportClientPingStatus(false, null);
                }
            }
        },
        async triggerDynamicSync() {
            const rawUrl = $wire.get('base_url') || '';
            const isLocal = rawUrl.includes('localhost') || rawUrl.includes('127.0.0.1');
            const isLiveServer = !['localhost', '127.0.0.1'].includes(window.location.hostname);

            if (isLocal && isLiveServer) {
                this.isHybridSyncing = true;
                try {
                    const t0 = performance.now();
                    const modelsResp = await fetch('http://127.0.0.1:20128/v1/models', {
                        headers: { 'Authorization': 'Bearer ' + ($wire.get('api_key') || 'omniroute-default-key') }
                    });

                    if (modelsResp.ok) {
                        const modelsJson = await modelsResp.json();
                        const models = modelsJson.data || modelsJson || [];
                        let combos = [];
                        try {
                            const combosResp = await fetch('http://127.0.0.1:20128/api/combos', {
                                headers: { 'Authorization': 'Bearer ' + ($wire.get('api_key') || 'omniroute-default-key') }
                            });
                            if (combosResp.ok) {
                                const combosJson = await combosResp.json();
                                combos = combosJson.data || combosJson || [];
                            }
                        } catch(e) {}

                        const latency = Math.max(1, Math.round(performance.now() - t0));
                        await $wire.syncFromClient(models, combos, latency);
                        this.isHybridSyncing = false;
                        return;
                    }
                } catch(err) {
                    console.warn('[Hybrid Sync] Browser direct fetch failed:', err);
                }
                this.isHybridSyncing = false;
            }

            $wire.testConnectionAndSyncModels();
        }
    }"
    x-init="checkClientHealth(); clientHealthTimer = setInterval(() => checkClientHealth(), 8000); $watch('$wire.base_url', () => checkClientHealth())"
>
    <!-- Breadcrumb & Title Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.ai-settings.index') }}" wire:navigate class="hover:text-white transition-colors">AI Providers</a>
                <span>/</span>
                <span class="text-violet-400 font-semibold">OmniRoute Gateway Setup</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>⚡ OmniRoute Gateway v3.8.50</span>
                <x-glass.badge variant="violet">Dynamic Multi-Route Engine</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage proxy connection credentials, test live latency, configure model cascades, and set BYOK policies.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-settings.index') }}" wire:navigate>
                <x-glass.button variant="secondary" size="sm">
                    &larr; Back to Providers
                </x-glass.button>
            </a>
        </div>
    </div>

    <!-- Live Telemetry Stream Graph & SLA Metrics -->
    <x-omniroute.telemetry-graph 
        :graphData="$graphData" 
        :timeRange="$graphTimeRange" 
        :statusFilter="$graphStatusFilter" 
    />

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-xs text-red-400 flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Top Section: 2 Column Layout (Form & Health Diagnostics) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Form (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <form wire:submit="saveConfiguration" x-data="{ showKey: false }" class="space-y-6">
                <!-- Gateway Endpoint & Key -->
                <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-4 border border-violet-500/20">
                    <div class="flex items-center justify-between pb-3 border-b border-white/5">
                        <div>
                            <h3 class="text-base font-bold text-white">Gateway Connection & Credentials</h3>
                            <p class="text-xs text-slate-400">OpenAI-compatible unified endpoint with auto-fallback cascade</p>
                        </div>
                        <x-glass.badge variant="violet">OmniRoute v3.8.50</x-glass.badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-medium text-slate-300 block">Gateway Base URL</label>
                                <span class="text-[10px] text-violet-400 font-mono">Dynamic Endpoint</span>
                            </div>
                            <x-glass.input wire:model.live.debounce.400ms="base_url" required placeholder="http://localhost:20128/v1 or https://*.trycloudflare.com/v1" />
                            
                            <!-- Quick Switch Presets -->
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                <span class="text-[10px] text-slate-400 font-semibold">Quick Presets:</span>
                                <button type="button" wire:click="setLocalPreset('localhost')" class="px-2 py-0.5 text-[10px] rounded-md bg-white/5 hover:bg-violet-600/30 text-slate-300 hover:text-white border border-white/10 transition-colors cursor-pointer" title="Switch to local localhost daemon">
                                    🏠 localhost:20128
                                </button>
                                <button type="button" wire:click="setLocalPreset('ip')" class="px-2 py-0.5 text-[10px] rounded-md bg-white/5 hover:bg-violet-600/30 text-slate-300 hover:text-white border border-white/10 transition-colors cursor-pointer" title="Switch to IPv4 127.0.0.1 daemon">
                                    🏠 127.0.0.1:20128
                                </button>
                            </div>
                            @php
                                $currentHost = request()->getHost();
                                $isLiveServer = !in_array($currentHost, ['localhost', '127.0.0.1', '::1']);
                            @endphp
                            @if($isLiveServer)
                                <div class="mt-2 p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-300 flex items-start gap-2">
                                    <span class="text-sm shrink-0">☁️</span>
                                    <span>
                                        <strong>Live Server ({{ $currentHost }}):</strong> Use your active <strong>Cloudflare Tunnel URL</strong> (e.g. <code class="text-white font-mono">https://*.trycloudflare.com/v1</code>) to reach OmniRoute running on your PC. <code class="text-amber-200">http://localhost</code> only applies if the daemon is installed directly on this server.
                                    </span>
                                </div>
                            @else
                                <p class="text-[10px] text-slate-500 mt-1.5">Supports local daemons (<code class="text-violet-400">http://localhost:20128/v1</code>) and Cloudflare Tunnels (<code class="text-cyan-400">https://*.trycloudflare.com/v1</code>).</p>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-medium text-slate-300">Master Gateway API Key</label>
                                <button 
                                    type="button" 
                                    x-on:click="showKey = !showKey" 
                                    class="text-[11px] text-violet-400 hover:text-violet-300 flex items-center gap-1 transition-colors cursor-pointer"
                                >
                                    <span x-show="!showKey" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Show</span>
                                    </span>
                                    <span x-show="showKey" class="flex items-center gap-1 text-violet-300" style="display: none;">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                        <span>Hide</span>
                                    </span>
                                </button>
                            </div>
                            <div class="relative">
                                <input 
                                    wire:model="api_key" 
                                    x-bind:type="showKey ? 'text' : 'password'" 
                                    required 
                                    placeholder="omniroute-default-key" 
                                    class="w-full bg-slate-900/90 border border-white/10 rounded-xl pl-3.5 pr-10 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500 font-mono tracking-wider transition-all placeholder:text-slate-500"
                                />
                                <button 
                                    type="button" 
                                    x-on:click="showKey = !showKey" 
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
                                    title="Toggle API Key Visibility"
                                >
                                    <svg x-show="!showKey" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showKey" class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">Bearer token for /v1/* inference authentication</p>
                        </div>
                    </div>

                    <!-- Dynamic Sync Action Button -->
                    <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
                        <button 
                            type="button" 
                            x-on:click="triggerDynamicSync()"
                            wire:loading.attr="disabled"
                            :disabled="isHybridSyncing"
                            class="flex-1 justify-center gap-2 font-bold px-4 py-2.5 rounded-xl text-xs transition-all cursor-pointer shadow-lg flex items-center {{ $syncStatus === 'success' ? 'bg-emerald-600/90 text-white shadow-emerald-500/30 border border-emerald-400' : ($syncStatus === 'error' ? 'bg-red-600/90 text-white shadow-red-500/30 border border-red-400' : 'bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white shadow-violet-500/25') }}"
                        >
                            <span x-show="!isHybridSyncing" wire:loading.remove wire:target="testConnectionAndSyncModels, syncFromClient" class="flex items-center gap-1.5">
                                @if($syncStatus === 'success')
                                    <span class="text-sm font-black">✓</span>
                                    <span>Full Dynamic Sync from OmniRoute</span>
                                @elseif($syncStatus === 'error')
                                    <span class="text-sm font-black">✕</span>
                                    <span>Sync Failed (Retry)</span>
                                @else
                                    <span>🔄</span>
                                    <span>Full Dynamic Sync from OmniRoute</span>
                                @endif
                            </span>
                            <span x-show="isHybridSyncing" class="flex items-center gap-2" style="display: none;">
                                <span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Browser Bridge: Connecting to Local PC...</span>
                            </span>
                            <span wire:loading wire:target="testConnectionAndSyncModels, syncFromClient" class="flex items-center gap-2">
                                <span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Ingesting Gateway Models & Combos...</span>
                            </span>
                        </button>

                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-md flex items-center gap-1.5 {{ $saveStatus === 'success' ? 'bg-emerald-600 text-white border border-emerald-400 shadow-emerald-500/25' : ($saveStatus === 'error' ? 'bg-red-600 text-white border border-red-400 shadow-red-500/25' : 'bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-white/20 hover:border-white/40') }}"
                        >
                            <span wire:loading.remove wire:target="saveConfiguration" class="flex items-center gap-1.5">
                                @if($saveStatus === 'success')
                                    <span class="text-sm font-black text-white">✓</span>
                                    <span>Saved Settings</span>
                                @elseif($saveStatus === 'error')
                                    <span class="text-sm font-black text-white">✕</span>
                                    <span>Save Failed</span>
                                @else
                                    <span>💾</span>
                                    <span>Save Settings</span>
                                @endif
                            </span>
                            <span wire:loading wire:target="saveConfiguration" class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Saving...</span>
                            </span>
                        </button>
                    </div>
                </x-glass.card>

                <!-- Model Cascades & Compression Options -->
                <x-glass.card variant="standard" class="p-6 space-y-4">
                    <h3 class="text-base font-bold text-white pb-3 border-b border-white/5">
                        Routing Strategy & Compression
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-300 block mb-1.5">Default Fallback Model / Routing Mode</label>
                            <select wire:model="default_model" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500 font-medium">
                                <option value="auto">⚡ OmniRoute Auto (Smart Dynamic Selection)</option>
                                <option value="auto:free">⚡ OmniRoute Auto Free (42 Free-Tier Pools)</option>
                                <option value="auto:quality">🧠 OmniRoute Auto Quality (Tier-1 Reasoning)</option>
                                <option value="auto:fast">🚀 OmniRoute Auto Fast (Lowest Latency)</option>
                                <option value="combo:creative-pro">🔀 Combo: Creative Pro (Auto Cascade)</option>
                                <option value="combo:free-tier-fast">🔀 Combo: Free Tier Cascade (Fast)</option>
                                <option value="combo:reasoning-r1">🔀 Combo: Deep Reasoning R1</option>
                                <option value="deepseek/deepseek-chat">🐋 DeepSeek-V3 (deepseek/deepseek-chat)</option>
                                <option value="cc/claude-3-7-sonnet">🎭 Claude 3.7 Sonnet (cc/claude-3-7-sonnet)</option>
                                <option value="openai/gpt-4o">🤖 OpenAI GPT-4o (openai/gpt-4o)</option>
                                <option value="glm/glm-4-flash">✨ GLM 4 Flash (Free Tier)</option>
                                <option value="groq/llama-3.3-70b-versatile">⚡ Groq Llama 3.3 70B (Fast Free)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-300 block mb-1.5">Context Compression Mode</label>
                            <select wire:model="compression_mode" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                                <option value="default">Default Panel Profile (Caveman / RTK)</option>
                                <option value="engine:rtk">RTK Command Output Engine</option>
                                <option value="off">Off (Raw Tokens / No Compression)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="text-xs font-medium text-slate-300 block mb-1.5">Reasoning / Thinking Budget</label>
                            <select wire:model="thinking_budget" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                                <option value="auto">Auto Adaptive Budget</option>
                                <option value="0">Disabled (Fast Direct)</option>
                                <option value="32768">Deep Reasoning (32k Tokens)</option>
                            </select>
                        </div>

                        <div class="space-y-2 pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="allow_user_key" class="rounded bg-slate-900 border-white/20 text-violet-600 focus:ring-violet-500/30">
                                <span class="text-xs text-slate-300">Allow users to set their own BYOK key</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded bg-slate-900 border-white/20 text-violet-600 focus:ring-violet-500/30">
                                <span class="text-xs text-slate-300">Provider is globally active for Studio generation</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/5 flex justify-end">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-md flex items-center gap-1.5 {{ $saveStatus === 'success' ? 'bg-emerald-600 text-white border border-emerald-400 shadow-emerald-500/25' : ($saveStatus === 'error' ? 'bg-red-600 text-white border border-red-400 shadow-red-500/25' : 'bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white shadow-violet-500/20') }}"
                        >
                            <span wire:loading.remove wire:target="saveConfiguration" class="flex items-center gap-1.5">
                                @if($saveStatus === 'success')
                                    <span class="text-sm font-black text-white">✓</span>
                                    <span>Settings Saved Successfully</span>
                                @elseif($saveStatus === 'error')
                                    <span class="text-sm font-black text-white">✕</span>
                                    <span>Failed to Save</span>
                                @else
                                    <span>💾</span>
                                    <span>Save Configuration</span>
                                @endif
                            </span>
                            <span wire:loading wire:target="saveConfiguration" class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Saving...</span>
                            </span>
                        </button>
                    </div>
                </x-glass.card>
            </form>
        </div>

        <!-- Live Diagnostics & Status (1 Col) -->
        <div class="space-y-6">
            <x-glass.card variant="standard" class="p-6 space-y-4 border border-white/10" wire:poll.8s="pingGatewayHealth">
                <h3 class="text-base font-bold text-white tracking-tight flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <span>📊 Gateway Telemetry</span>
                        <span class="text-[10px] font-mono font-normal text-slate-400 bg-white/5 px-2 py-0.5 rounded">Auto-Pulse 8s</span>
                    </span>
                    @if($connectionStatus === true)
                        <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[10px] font-mono font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>LIVE</span>
                        </span>
                    @elseif($connectionStatus === false)
                        <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 text-[10px] font-mono font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span>STANDBY</span>
                        </span>
                    @else
                        <span class="w-3 h-3 rounded-full bg-slate-500"></span>
                    @endif
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Gateway Status:</span>
                        <span class="font-bold {{ $connectionStatus === true ? 'text-emerald-400' : ($connectionStatus === false ? 'text-amber-400' : 'text-slate-400') }}">
                            {{ $connectionStatus === true ? 'ONLINE' : ($connectionStatus === false ? 'STANDBY / OFFLINE' : 'NOT TESTED') }}
                        </span>
                    </div>

                    @if($pingLatencyMs !== null)
                        <div class="flex items-center justify-between pb-2 border-b border-white/5">
                            <span class="text-slate-400">Ping Latency:</span>
                            <span class="font-mono text-indigo-300 font-bold">{{ $pingLatencyMs }}ms</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Total Models in DB:</span>
                        <span class="font-mono font-bold text-white">{{ $totalModelsCount }}</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Free Tier Pools:</span>
                        <span class="font-mono font-bold text-emerald-400">{{ $freeTierCount }} Pools (~1.51B tok)</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Multi-Combos:</span>
                        <span class="font-mono font-bold text-purple-400">{{ $combosCount }} Cascades</span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-white/5">
                        <span class="text-slate-400">Reasoning Models:</span>
                        <span class="font-bold text-indigo-400">{{ $reasoningCount }} Models</span>
                    </div>
                </div>

                <!-- Status Guidance Tip -->
                @if($connectionStatus === true)
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-[11px] text-emerald-300 flex items-center gap-2">
                        <span>✓</span>
                        <span>OmniRoute v3.8.50 Live Daemon Active & Connected.</span>
                    </div>
                @else
                    <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-300 flex items-center gap-2">
                        <span>💡</span>
                        <span>Offline catalog active (23 models). Run <code class="font-mono bg-slate-900 px-1 py-0.5 rounded text-white">omniroute</code> in terminal for live proxy.</span>
                    </div>
                @endif
            </x-glass.card>

            <!-- Console Logs — Application Console Output & Debug Logs (Native OmniRoute ConsoleLogViewer Style) -->
            <x-glass.card variant="standard" class="p-4 sm:p-5 space-y-3" wire:poll.5s="fetchConsoleLogs">
                <!-- Toolbar Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🖥️</span>
                        <div>
                            <h3 class="text-sm font-bold text-white leading-none">Application Console</h3>
                            <span class="text-[10px] text-slate-400">Live output and debug stream (same as OmniRoute dashboard)</span>
                        </div>
                    </div>

                    <!-- Right Controls (Refresh, Pulse, Buffer Clear) -->
                    <div class="flex items-center gap-2 text-xs">
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 bg-slate-900/80 px-2.5 py-1 rounded-lg border border-white/5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="font-mono text-white font-bold">{{ count($filteredLogs) }}</span> entries
                            @if($lastUpdated)
                                <span class="text-slate-600">•</span>
                                <span class="text-slate-400 font-mono text-[9px]">updated {{ $lastUpdated }}</span>
                            @endif
                        </div>

                        <button 
                            type="button" 
                            x-on:click="$dispatch('open-omni-terminal')"
                            class="px-2 py-1 rounded-lg text-violet-300 hover:text-white bg-violet-950/80 hover:bg-violet-900 border border-violet-500/30 transition-colors cursor-pointer text-xs flex items-center gap-1" 
                            title="Pop out Draggable & Minimizable Terminal Window (Persists across pages)"
                        >
                            <span>↗️ Pop Out</span>
                        </button>

                        <button 
                            type="button" 
                            wire:click="fetchConsoleLogs" 
                            class="px-2 py-1 rounded-lg text-slate-300 hover:text-white bg-slate-900/80 hover:bg-slate-800 border border-white/5 transition-colors cursor-pointer text-xs flex items-center gap-1" 
                            title="Refresh Console Logs"
                        >
                            <span>🔄</span>
                        </button>

                        <button 
                            type="button" 
                            wire:click="clearConsoleLogs" 
                            class="px-2 py-1 rounded-lg text-slate-400 hover:text-red-400 bg-slate-900/80 hover:bg-red-500/10 border border-white/5 transition-colors cursor-pointer text-xs flex items-center gap-1" 
                            title="Clear Console Buffer"
                        >
                            <span>🧹</span>
                        </button>
                    </div>
                </div>

                <!-- Filters & Search Toolbar -->
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <!-- Level Filter Dropdown -->
                    <div class="w-full sm:w-32">
                        <select 
                            wire:model.live="logLevelFilter" 
                            class="w-full bg-[#0d1117] border border-[#30363d] rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-cyan-500 font-mono"
                        >
                            <option value="all">All Levels</option>
                            <option value="debug">Debug+</option>
                            <option value="info">Info+</option>
                            <option value="warn">Warn+</option>
                            <option value="error">Error+</option>
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="w-full sm:flex-1">
                        <input 
                            type="text" 
                            wire:model.live.debounce.200ms="logSearch" 
                            placeholder="Filter console logs (message, component, correlationId)..." 
                            class="w-full bg-[#0d1117] border border-[#30363d] rounded-lg px-3 py-1.5 text-xs font-mono text-slate-200 placeholder-[#8b949e] focus:outline-none focus:border-cyan-500"
                        />
                    </div>
                </div>

                <!-- Terminal Output Box (Native OmniRoute macOS Style) -->
                <div 
                    x-data="{ autoScroll: true }"
                    class="rounded-xl border border-[#30363d] bg-[#0d1117] overflow-hidden font-mono text-xs leading-relaxed shadow-2xl"
                >
                    <!-- macOS Header Bar -->
                    <div class="sticky top-0 z-10 px-3.5 py-2 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full bg-[#FF5F56]"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-[#FFBD2E]"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-[#27C93F]"></div>
                            <span class="ml-2 text-[#8b949e] text-[10px] tracking-wide font-sans font-medium">
                                OmniRoute — Application Console Output
                            </span>
                        </div>

                        <span class="text-[10px] font-mono text-cyan-400/70">
                            http://localhost:20128/dashboard/logs/console
                        </span>
                    </div>

                    <!-- Log Stream Lines Window -->
                    <div 
                        x-ref="logWindow"
                        class="p-3 space-y-1 max-h-72 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-[#30363d] text-[11px]"
                    >
                        @forelse($filteredLogs as $idx => $entry)
                            @php
                                $level = strtolower($entry['level'] ?? 'info');
                                $colorClass = match($level) {
                                    'debug' => 'text-gray-400',
                                    'trace' => 'text-gray-500',
                                    'info' => 'text-cyan-400',
                                    'warn' => 'text-yellow-400',
                                    'error' => 'text-red-400',
                                    'fatal' => 'text-fuchsia-400',
                                    default => 'text-cyan-400',
                                };
                                $bgClass = match($level) {
                                    'debug' => 'bg-gray-500/10 border-gray-500/20',
                                    'trace' => 'bg-gray-500/10 border-gray-500/20',
                                    'info' => 'bg-cyan-500/10 border-cyan-500/20',
                                    'warn' => 'bg-yellow-500/10 border-yellow-500/20',
                                    'error' => 'bg-red-500/10 border-red-500/20',
                                    'fatal' => 'bg-fuchsia-500/10 border-fuchsia-500/20',
                                    default => 'bg-cyan-500/10 border-cyan-500/20',
                                };
                                $timeStr = \Carbon\Carbon::parse($entry['timestamp'])->format('H:i:s.v');
                            @endphp

                            <div class="group flex items-start gap-2 px-1.5 py-0.5 rounded hover:bg-white/5 transition-colors {{ in_array($level, ['error', 'fatal']) ? 'bg-red-500/5' : '' }}">
                                <!-- Timestamp -->
                                <span class="text-[#484f58] whitespace-nowrap shrink-0 select-none text-[10px]">
                                    {{ $timeStr }}
                                </span>

                                <!-- Level badge -->
                                <span class="inline-block px-1.5 py-0 rounded text-[9px] font-semibold uppercase border shrink-0 {{ $colorClass }} {{ $bgClass }}">
                                    {{ str_pad($level, 5) }}
                                </span>

                                <!-- Component tag -->
                                @if(!empty($entry['component']))
                                    <span class="text-purple-400/80 shrink-0 text-[10px]">[{{ $entry['component'] }}]</span>
                                @endif

                                <!-- Message body -->
                                <span class="text-[#c9d1d9] flex-1 break-all select-text">
                                    {{ $entry['message'] ?? '' }}
                                    
                                    @if(!empty($entry['correlationId']))
                                        <span class="text-[#484f58] ml-1.5 text-[9px]">cid:{{ substr($entry['correlationId'], 0, 8) }}</span>
                                    @endif
                                </span>
                            </div>
                        @empty
                            <div class="text-[#8b949e] text-center py-10 space-y-1">
                                <span class="text-2xl block mb-1 opacity-40">💻</span>
                                <p class="text-xs font-semibold">No Console Log Entries Found</p>
                                <p class="text-[10px] text-slate-500">Waiting for live inference streams or background gateway telemetry...</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-glass.card>
        </div>
    </div>

    <!-- Active Models Catalog with Advanced Dynamic Filtering & Pagination -->
    <div class="space-y-4">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2 flex-wrap">
                    <span>⚡ Dynamic OmniRoute Catalog</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-violet-500/10 text-violet-300 border border-violet-500/30">
                        {{ $models->total() }} Models
                    </span>
                    @if($workingCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-[11px] font-mono font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            {{ $workingCount }} Working
                        </span>
                    @endif
                    @if($failedCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-red-950/80 border border-red-500/40 text-red-300 text-[11px] font-mono font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                            {{ $failedCount }} Failed
                        </span>
                    @endif
                    @if($prunedModelsCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-amber-950/80 border border-amber-500/40 text-amber-300 text-[11px] font-mono font-bold flex items-center gap-1">
                            <span>🗑️</span>
                            {{ $prunedModelsCount }} Pruned
                        </span>
                    @endif
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Real-time health verification across 42 free tier pools, reasoning engines, and auto-fallback combos.</p>
            </div>

            <!-- Bulk Diagnostics & Per Page Selector -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Batch Test Visible Models Button -->
                <button 
                    type="button" 
                    wire:click="testCurrentPageModels" 
                    wire:loading.attr="disabled"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all cursor-pointer disabled:opacity-50"
                    title="Run live probe test on all visible models on this page"
                >
                    <span wire:loading.remove wire:target="testCurrentPageModels">🧪 Test Visible Models</span>
                    <span wire:loading wire:target="testCurrentPageModels" class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                        Testing In Progress...
                    </span>
                </button>

                <!-- Resync from Gateway Button -->
                <button 
                    type="button" 
                    x-on:click="triggerDynamicSync()"
                    wire:loading.attr="disabled"
                    :disabled="isHybridSyncing"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-violet-500/30 hover:border-violet-500/60 text-violet-300 hover:text-white text-xs font-semibold transition-all cursor-pointer"
                    title="Re-synchronize catalog directly from OmniRoute /v1/models"
                >
                    <span x-show="!isHybridSyncing" wire:loading.remove wire:target="testConnectionAndSyncModels, syncFromClient">🔄 Resync Models</span>
                    <span x-show="isHybridSyncing" style="display: none;">Connecting...</span>
                    <span wire:loading wire:target="testConnectionAndSyncModels, syncFromClient">Syncing...</span>
                </button>

                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="text-[11px]">Per page:</span>
                    <select wire:model.live="perPage" class="bg-slate-900 border border-white/10 rounded-lg px-2 py-1 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                        <option value="12">12</option>
                        <option value="18">18</option>
                        <option value="36">36</option>
                        <option value="72">72</option>
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <x-glass.button type="button" variant="glass" size="sm" wire:click="bulkToggleModels(true)" class="text-emerald-300 hover:bg-emerald-500/10 text-xs">
                        ✓ Enable All
                    </x-glass.button>
                    <x-glass.button type="button" variant="glass" size="sm" wire:click="bulkToggleModels(false)" class="text-red-300 hover:bg-red-500/10 text-xs">
                        ✕ Disable All
                    </x-glass.button>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <x-glass.card variant="subtle" class="p-4 space-y-3 border border-white/10">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="w-full sm:flex-1">
                    <x-glass.input 
                        wire:model.live.debounce.250ms="modelSearch" 
                        placeholder="Search models by name, vendor, or ID (e.g. deepseek, claude, gpt-4o, llama, combo)..." 
                    />
                </div>

                <!-- Status Filter Dropdown -->
                <div class="w-full sm:w-64">
                    <select wire:model.live="modelStatusFilter" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500 font-medium">
                        <option value="all">All Models ({{ $totalModelsCount }})</option>
                        <option value="working">🟢 Working Models ({{ $workingCount }})</option>
                        <option value="failed">🔴 Failed Models ({{ $failedCount }})</option>
                        <option value="untested">⚪ Untested Models ({{ $untestedCount }})</option>
                        <option value="free_tier">⚡ Free Tier Pools ({{ $freeTierCount }})</option>
                        <option value="combos">🔀 Auto Combos ({{ $combosCount }})</option>
                        <option value="reasoning">🧠 Reasoning Engines ({{ $reasoningCount }})</option>
                        <option value="online">● Active ({{ $onlineModelsCount }})</option>
                        <option value="offline">○ Disabled ({{ $offlineModelsCount }})</option>
                    </select>
                </div>
            </div>

            <!-- Quick Engine & Capability Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                <span class="text-[11px] text-slate-400 mr-1 font-bold">Providers:</span>
                
                <button 
                    type="button" 
                    wire:click="$set('modelVendorFilter', '')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelVendorFilter === '' ? 'bg-violet-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    All Providers ({{ $totalModelsCount }})
                </button>

                @foreach($vendors as $v)
                    @php
                        $vendorSlug = strtolower($v->owned_by);
                        $vendorIcon = match($vendorSlug) {
                            'deepseek' => '🐋',
                            'openai' => '🤖',
                            'anthropic' => '🎭',
                            'google' => '✨',
                            'groq' => '⚡',
                            'cerebras' => '🚀',
                            'mistral' => '🌪️',
                            'together' => '🤝',
                            'omniroute' => '⚡',
                            default => '🌐',
                        };
                    @endphp
                    <button 
                        type="button" 
                        wire:click="$set('modelVendorFilter', '{{ $v->owned_by }}')" 
                        class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] flex items-center gap-1 {{ $modelVendorFilter === $v->owned_by ? 'bg-violet-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                    >
                        <span>{{ $vendorIcon }}</span>
                        <span>{{ ucfirst($v->owned_by) }}</span>
                        <span class="text-[10px] opacity-75 font-mono">({{ $v->count }})</span>
                    </button>
                @endforeach
            </div>

            <!-- Capability Pills -->
            <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-white/5 text-xs">
                <span class="text-[11px] text-slate-400 mr-1 font-bold">Capabilities:</span>

                <button 
                    type="button" 
                    wire:click="$set('modelStatusFilter', 'working')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'working' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-emerald-400 hover:text-white border border-emerald-500/20' }}"
                >
                    🟢 Working Only ({{ $workingCount }})
                </button>

                <button 
                    type="button" 
                    wire:click="$set('modelStatusFilter', 'free_tier')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'free_tier' ? 'bg-violet-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    ⚡ Free Tier ({{ $freeTierCount }})
                </button>

                <button 
                    type="button" 
                    wire:click="$set('modelStatusFilter', 'reasoning')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'reasoning' ? 'bg-violet-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    🧠 Reasoning ({{ $reasoningCount }})
                </button>

                <button 
                    type="button" 
                    wire:click="$set('modelStatusFilter', 'combos')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'combos' ? 'bg-violet-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    🔀 Auto Combos ({{ $combosCount }})
                </button>
            </div>
        </x-glass.card>

        <!-- Models Grid (Paginated) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($models as $m)
                @php
                    $isCurrentDefault = $default_model === $m->model_id;
                    $usage = $modelUsage->get($m->model_id);
                    $isBeingTested = isset($testingModelIds[$m->id]);
                @endphp
                <x-glass.card variant="standard" class="p-5 flex flex-col justify-between hover:border-violet-500/40 transition-all relative {{ $isCurrentDefault ? 'border-violet-500/50 bg-violet-950/20' : '' }}">
                    @if($isCurrentDefault)
                        <div class="absolute top-0 right-0 px-2.5 py-0.5 bg-gradient-to-l from-violet-600 to-indigo-600 text-white font-mono text-[9px] font-bold uppercase rounded-bl-lg shadow-sm">
                            ★ DEFAULT ROUTE
                        </div>
                    @endif

                    <div>
                        <!-- Title & Health Status Badge -->
                        <div class="flex items-start justify-between gap-2 mb-2 pr-10">
                            <h4 class="text-sm font-bold text-white truncate" title="{{ $m->name }}">{{ $m->name }}</h4>
                        </div>

                        <!-- Model ID & Live Health Indicator -->
                        <div class="flex items-center justify-between gap-2 font-mono text-[11px] text-indigo-300 bg-slate-900 px-2.5 py-1 rounded-lg border border-white/5 mb-3">
                            <span class="truncate flex-1" title="{{ $m->model_id }}">{{ $m->model_id }}</span>

                            <!-- Health Diagnostic Badge -->
                            @if($m->last_test_status === 'working')
                                <span class="px-1.5 py-0.2 rounded bg-emerald-950 text-emerald-400 border border-emerald-500/40 text-[9px] shrink-0 font-bold flex items-center gap-1" title="Tested {{ $m->last_tested_at ? $m->last_tested_at->diffForHumans() : 'recently' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    {{ $m->last_test_latency_ms ?? 0 }}ms
                                </span>
                            @elseif($m->last_test_status === 'failed')
                                <span class="px-1.5 py-0.2 rounded bg-red-950 text-red-400 border border-red-500/40 text-[9px] shrink-0 font-bold flex items-center gap-1" title="{{ $m->last_test_error ?? 'Probe failed' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                    ERROR
                                </span>
                            @else
                                <span class="text-slate-600 text-[9px] shrink-0">untested</span>
                            @endif
                        </div>

                        <!-- Capability Tags / Badges -->
                        <div class="flex flex-wrap items-center gap-1.5 text-[10px] mb-4 font-mono font-semibold">
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300">
                                {{ number_format($m->context_window / 1000) }}k Ctx
                            </span>

                            @if($m->is_free_tier)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                                    ⚡ Free Tier
                                </span>
                            @endif

                            @if($m->is_combo)
                                <span class="px-2 py-0.5 rounded-md bg-purple-950 text-purple-300 border border-purple-500/30">
                                    🔀 Combo
                                </span>
                            @endif

                            @if($m->supports_reasoning)
                                <span class="px-2 py-0.5 rounded-md bg-cyan-950 text-cyan-300 border border-cyan-500/30">
                                    🧠 &lt;think&gt;
                                </span>
                            @endif

                            @if($usage)
                                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-indigo-300">
                                    {{ number_format($usage->total_words) }} words
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Footer Controls with Live Model Test Button -->
                    <div class="pt-3 border-t border-white/5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <!-- Active / Offline Status Toggle Button -->
                            <button 
                                type="button" 
                                wire:click="toggleModelStatus({{ $m->id }})" 
                                class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase cursor-pointer transition-all {{ $m->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20' }}"
                            >
                                {{ $m->is_active ? '● Active' : '○ Offline' }}
                            </button>

                            <!-- Live Single Model Test Button -->
                            <button 
                                type="button" 
                                wire:click="testSingleModel({{ $m->id }})" 
                                wire:loading.attr="disabled"
                                class="px-2 py-1 rounded-lg text-[10px] font-mono font-bold bg-slate-900 border border-white/10 hover:border-emerald-500/50 hover:bg-emerald-950/50 text-slate-300 hover:text-emerald-300 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-1"
                                title="Send live inference probe to test if model is working right now"
                            >
                                <span wire:loading.remove wire:target="testSingleModel({{ $m->id }})">🧪 Test</span>
                                <span wire:loading wire:target="testSingleModel({{ $m->id }})" class="text-emerald-400 animate-pulse">Testing...</span>
                            </button>
                        </div>

                        <!-- Set as Default Action -->
                        @if(!$isCurrentDefault)
                            <button 
                                type="button" 
                                wire:click="setDefaultRoutingModel('{{ $m->model_id }}')" 
                                class="text-[11px] text-slate-400 hover:text-violet-300 transition-colors font-medium cursor-pointer"
                            >
                                Set Default
                            </button>
                        @else
                            <span class="text-[11px] text-violet-400 font-semibold flex items-center gap-1">
                                ✓ Active Default
                            </span>
                        @endif
                    </div>
                </x-glass.card>
            @empty
                <div class="col-span-full py-12 text-center">
                    <x-glass.card variant="subtle" class="p-8 max-w-md mx-auto">
                        <div class="text-3xl mb-2">🔍</div>
                        <h4 class="text-sm font-bold text-white mb-1">No Models Match Filters</h4>
                        <p class="text-xs text-slate-400 mb-4">Try clearing your search query or selecting "All Models".</p>
                        <x-glass.button type="button" variant="secondary" size="sm" wire:click="$set('modelSearch', ''); $set('modelStatusFilter', 'all'); $set('modelVendorFilter', '')">
                            Clear All Filters
                        </x-glass.button>
                    </x-glass.card>
                </div>
            @endforelse
        </div>

        <!-- Pagination Controls (Centered Dynamic Range Display) -->
        @if($models->hasPages())
            <div class="pt-6 border-t border-white/5 flex flex-col items-center justify-center gap-4 text-center">
                <!-- Centered Dynamic Range Display -->
                <div class="text-xs text-slate-400 bg-slate-900/70 px-4 py-1.5 rounded-full border border-white/5 shadow-inner">
                    Showing <strong class="text-white font-mono">{{ $models->firstItem() }}</strong> to <strong class="text-white font-mono">{{ $models->lastItem() }}</strong> of <strong class="text-violet-400 font-mono font-bold">{{ $models->total() }}</strong> models
                </div>

                <!-- Centered Pagination Buttons -->
                <div class="flex items-center justify-center flex-wrap gap-1.5 text-xs">
                    <!-- Previous Button -->
                    <button 
                        type="button"
                        wire:click="previousPage('modelsPage')" 
                        @disabled($models->onFirstPage())
                        class="px-3 py-1.5 rounded-lg border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
                    >
                        &larr; Prev
                    </button>

                    <!-- Page Numbers (Compact Window) -->
                    @php
                        $currentPage = $models->currentPage();
                        $lastPage = $models->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($start > 1)
                        <button type="button" wire:click="gotoPage(1, 'modelsPage')" class="w-8 h-8 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 font-mono cursor-pointer">1</button>
                        @if($start > 2)
                            <span class="text-slate-600 px-1">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        <button 
                            type="button" 
                            wire:click="gotoPage({{ $page }}, 'modelsPage')" 
                            class="w-8 h-8 rounded-lg font-mono transition-all cursor-pointer {{ $page === $currentPage ? 'bg-violet-600 text-white font-bold shadow-md shadow-violet-500/20' : 'border border-white/10 text-slate-300 hover:text-white hover:bg-white/5' }}"
                        >
                            {{ $page }}
                        </button>
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="text-slate-600 px-1">...</span>
                        @endif
                        <button type="button" wire:click="gotoPage({{ $lastPage }}, 'modelsPage')" class="w-8 h-8 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 font-mono cursor-pointer">{{ $lastPage }}</button>
                    @endif

                    <!-- Next Button -->
                    <button 
                        type="button"
                        wire:click="nextPage('modelsPage')" 
                        @disabled(!$models->hasMorePages())
                        class="px-3 py-1.5 rounded-lg border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
                    >
                        Next &rarr;
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- REAL-TIME DIAGNOSTIC & SYNC PROGRESS TERMINAL POPUP MODAL                 -->
    <!-- ========================================================================= -->
    @if($showProgressModal)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-md"
            x-data="{ 
                init() {
                    this.$nextTick(() => {
                        const el = document.getElementById('progressLogTerminal');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
            }"
        >
            <div 
                class="w-full max-w-2xl bg-[#0d1117] border border-[#30363d] rounded-2xl shadow-[0_25px_70px_rgba(0,0,0,0.95)] ring-1 ring-white/10 overflow-hidden font-mono text-xs flex flex-col max-h-[85vh] animate-in fade-in zoom-in-95 duration-200"
            >
                <!-- macOS Interactive Terminal Header -->
                <div class="h-11 px-4 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between shrink-0 select-none">
                    <div class="flex items-center gap-2">
                        <!-- Red (X) Close button -->
                        <button 
                            type="button" 
                            wire:click="closeProgressModal" 
                            class="w-3 h-3 rounded-full bg-[#FF5F56] border border-[#E0443E] flex items-center justify-center text-[8px] font-bold text-black/70 hover:opacity-100 opacity-90 transition-all cursor-pointer group"
                            title="Close Diagnostics Window (X)"
                        >
                            <span class="opacity-0 group-hover:opacity-100 font-sans leading-none">✕</span>
                        </button>
                        <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-[#DEA123] opacity-80"></span>
                        <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-[#1AAB29] opacity-80"></span>

                        <span class="ml-3 text-slate-200 text-xs font-bold tracking-tight truncate">
                            {{ $progressModalTitle ?: 'Live OmniRoute Diagnostic Terminal' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!$progressDone)
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 text-[10px] font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                IN PROGRESS
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-cyan-950/80 border border-cyan-500/40 text-cyan-400 text-[10px] font-bold">
                                ✓ COMPLETE
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Subtitle / Context Bar -->
                <div class="px-4 py-2.5 bg-[#161b22]/70 border-b border-[#30363d] flex items-center justify-between gap-3 text-[11px] text-slate-400 shrink-0">
                    <div class="truncate">
                        {{ $progressModalSubtitle ?: 'Live real-time output stream from gateway' }}
                    </div>

                    @if($progressTotal > 0)
                        <div class="font-mono text-[11px] text-cyan-300 font-bold shrink-0">
                            {{ $progressCurrent }}/{{ $progressTotal }} ({{ round(($progressCurrent / max(1, $progressTotal)) * 100) }}%)
                        </div>
                    @endif
                </div>

                <!-- Animated Progress Bar -->
                @if($progressTotal > 0)
                    <div class="h-1 bg-slate-900 overflow-hidden shrink-0">
                        <div 
                            class="h-full bg-gradient-to-r from-violet-500 via-indigo-500 to-emerald-400 transition-all duration-300"
                            style="width: {{ round(($progressCurrent / max(1, $progressTotal)) * 100) }}%;"
                        ></div>
                    </div>
                @endif

                <!-- Terminal Log Stream -->
                <div 
                    id="progressLogTerminal"
                    class="p-4 space-y-1.5 overflow-y-auto overflow-x-hidden flex-1 select-text bg-[#0d1117] text-[11px] leading-relaxed scrollbar-thin scrollbar-thumb-[#30363d] min-h-[220px]"
                >
                    @forelse($progressLogs as $pl)
                        @php
                            $color = match($pl['level'] ?? 'info') {
                                'ok' => 'text-emerald-400',
                                'error' => 'text-red-400',
                                'warn' => 'text-yellow-400',
                                'debug' => 'text-slate-400',
                                default => 'text-cyan-300',
                            };
                            $badgeBg = match($pl['level'] ?? 'info') {
                                'ok' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
                                'error' => 'bg-red-500/10 border-red-500/20 text-red-400 font-bold',
                                'warn' => 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400',
                                'debug' => 'bg-slate-500/10 border-slate-500/20 text-slate-400',
                                default => 'bg-cyan-500/10 border-cyan-500/20 text-cyan-300',
                            };
                        @endphp
                        <div class="flex items-start gap-2.5 font-mono">
                            <span class="text-[#484f58] whitespace-nowrap text-[10px] select-none shrink-0">{{ $pl['time'] }}</span>
                            <span class="px-1.5 py-0 rounded text-[9px] uppercase border font-semibold shrink-0 {{ $badgeBg }}">
                                {{ $pl['tag'] ?? 'INFO' }}
                            </span>
                            <span class="text-[#c9d1d9] flex-1 break-all select-text {{ $color }}">
                                {{ $pl['message'] }}
                            </span>
                        </div>
                    @empty
                        <div class="text-slate-500 text-center py-8">
                            Initializing live gateway diagnostic connection...
                        </div>
                    @endforelse
                </div>

                <!-- Footer Summary Bar & Close Trigger -->
                <div class="p-3.5 bg-[#161b22] border-t border-[#30363d] flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-3 text-[11px] text-slate-400 font-mono">
                        @if($progressWorking > 0 || $progressFailed > 0)
                            <span class="text-emerald-400 font-bold">🟢 {{ $progressWorking }} Working</span>
                            <span class="text-slate-600">•</span>
                            <span class="text-red-400 font-bold">🔴 {{ $progressFailed }} Failed</span>
                        @else
                            <span>Gateway: <strong class="text-cyan-300">{{ $base_url }}</strong></span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="closeProgressModal" 
                            class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 hover:border-white/20 text-white text-xs font-semibold transition-all cursor-pointer"
                        >
                            {{ $progressDone ? 'Done & Review Models (X)' : 'Close Window (X)' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
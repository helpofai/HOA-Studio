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
    class="space-y-8 animate-fade-in pb-12"
    x-data="{
        showKey: false,
        activePoint: null,
        hoverIndex: null,
        checkClientHealth() {
            const rawUrl = $wire.get('user_custom_url') || $wire.get('base_url') || '';
            const isLocal = rawUrl.includes('localhost') || rawUrl.includes('127.0.0.1');
            const isLiveServer = !['localhost', '127.0.0.1'].includes(window.location.hostname);

            if (isLocal && isLiveServer) {
                const t0 = performance.now();
                fetch('http://127.0.0.1:20128/v1/models', {
                    headers: { 'Authorization': 'Bearer ' + ($wire.get('user_api_key') || 'omniroute-default-key') }
                }).then(res => {
                    if (res.ok) {
                        const lat = Math.max(1, Math.round(performance.now() - t0));
                        $wire.reportClientPingStatus(true, lat);
                    }
                }).catch(e => {});
            }
        }
    }"
    x-init="checkClientHealth(); $watch('$wire.user_custom_url', () => checkClientHealth())"
>
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('ai-models.index') }}" wire:navigate class="text-xs text-indigo-400 hover:underline flex items-center gap-1 font-mono">
                    <span>&larr; AI Models & Gateways</span>
                </a>
                <span class="text-slate-600">&bull;</span>
                <span class="text-xs font-mono text-slate-400">Gateway Hub</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>⚡ OmniRoute Gateway Hub</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/30">
                    BYOK UNLIMITED
                </span>
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Connect your personal OmniRoute gateway credentials or utilize the platform's multi-provider intelligent router.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                type="button" 
                wire:click="testGatewayConnection" 
                wire:loading.attr="disabled"
                class="px-4 py-2 rounded-xl bg-slate-900 border border-white/15 text-xs font-bold text-slate-200 hover:text-white hover:border-indigo-500/50 transition-all cursor-pointer inline-flex items-center gap-2 shadow-md"
            >
                <span wire:loading.remove wire:target="testGatewayConnection">📡 Test Connection</span>
                <span wire:loading wire:target="testGatewayConnection" class="inline-flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                    Testing...
                </span>
            </button>

            <a href="{{ route('templates.index') }}" wire:navigate>
                <x-glass.button variant="primary" size="md" class="gap-2 shadow-lg shadow-indigo-500/20">
                    <span>Launch Studio &rarr;</span>
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

    <!-- Status Banner Alert -->
    @if(session('status') || $statusMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2.5">
                <span class="text-base">✓</span>
                <span>{{ session('status') ?: $statusMessage }}</span>
            </div>
            @if($pingLatencyMs)
                <span class="font-mono text-[10px] bg-emerald-500/20 px-2 py-0.5 rounded border border-emerald-500/30 font-bold">
                    {{ $pingLatencyMs }}ms
                </span>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-xs text-red-300 flex items-center gap-2.5 shadow-lg">
            <span class="text-base">✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Top Two-Column Section: Configuration (Left) & Diagnostics/Console (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Gateway Configuration & Telemetry (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Gateway Telemetry Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" wire:poll.8s="testGatewayConnection">
                <x-glass.card variant="elevated" class="p-4 border border-white/10">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Gateway Status</span>
                        <span class="text-base">🌐</span>
                    </div>
                    <div class="text-xl font-black flex items-center gap-1.5 {{ $connectionStatus === true ? 'text-emerald-400' : 'text-amber-400' }}">
                        <span>{{ $connectionStatus === true ? 'ONLINE' : 'STANDBY / OFFLINE' }}</span>
                        @if($pingLatencyMs !== null)
                            <span class="text-xs font-mono text-slate-400 font-normal">({{ $pingLatencyMs }}ms)</span>
                        @endif
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                        {{ $connection_type === 'cloudflare_tunnel' ? 'Cloudflare Tunnel (HTTPS)' : ($connection_type === 'local_daemon' ? 'Local Device Loopback' : 'Dedicated OmniRoute Cluster') }}
                    </div>
                </x-glass.card>

                <x-glass.card variant="elevated" class="p-4 border border-white/10">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Catalog Models</span>
                        <span class="text-base">⚡</span>
                    </div>
                    <div class="text-xl font-black text-white">{{ $totalModelsCount }} Models</div>
                    <div class="text-[10px] text-indigo-400 mt-0.5">{{ $freeTierCount }} Free Tier Pools</div>
                </x-glass.card>

                <x-glass.card variant="premium" glow="indigo" class="p-4">
                    <div class="flex items-center justify-between text-slate-300 mb-1">
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Your Rate Limit</span>
                        <span class="text-base">🚀</span>
                    </div>
                    <div class="text-xl font-black text-white">
                        {{ $hasPersonalKey ? 'UNLIMITED' : 'STANDARD' }}
                    </div>
                    <div class="text-[10px] text-indigo-300 mt-0.5">
                        {{ $hasPersonalKey ? 'Personal Key Active' : 'Managed Platform Routing' }}
                    </div>
                </x-glass.card>
            </div>

            <!-- Personal Gateway Key Configuration Form -->
            <x-glass.card variant="standard" class="p-6 space-y-5 border border-white/10">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-3 border-b border-white/10">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span>🔑 Personal OmniRoute API Key & Gateway Endpoint</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono font-bold uppercase">
                                AES-256-GCM
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Connect your own OmniRoute master API key or local custom gateway endpoint to unlock unlimited rates.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($connectionStatus === true)
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-mono font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>ONLINE ({{ $pingLatencyMs ?? 12 }}ms)</span>
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 text-xs font-mono font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span>STANDBY / OFFLINE</span>
                            </span>
                        @endif

                        @if($hasPersonalKey)
                            <button 
                                type="button" 
                                wire:click="removeUserKey" 
                                wire:confirm="Remove your personal OmniRoute API key and revert to managed platform key?"
                                class="px-3 py-1.5 rounded-xl bg-red-500/10 text-red-300 border border-red-500/20 hover:bg-red-500/20 text-xs font-bold transition-all cursor-pointer"
                            >
                                ✕ Remove Key
                            </button>
                        @endif
                    </div>
                </div>

                @if($statusMessage)
                    <div class="p-3.5 rounded-xl text-xs flex items-center gap-2 {{ $connectionStatus === true ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-300' : 'bg-amber-500/10 border border-amber-500/20 text-amber-300' }}">
                        <span>{{ $connectionStatus === true ? '✓' : 'ℹ️' }}</span>
                        <span>{{ $statusMessage }}</span>
                    </div>
                @endif

                @if(!$allowUserKey)
                    <div class="p-4 rounded-xl bg-slate-900/80 border border-white/10 text-xs text-slate-400 flex items-center gap-2.5">
                        <span class="text-base">ℹ️</span>
                        <span>Personal BYOK keys for OmniRoute are currently managed exclusively by the platform administrator.</span>
                    </div>
                @else
                    <!-- Dynamic Multi-Type Connection Selector -->
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-slate-300 block">
                            Choose Gateway Connection Mode:
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <!-- 1. Local Device Daemon -->
                            <button 
                                type="button" 
                                wire:click="setConnectionType('local_daemon')" 
                                class="p-3.5 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between {{ $connection_type === 'local_daemon' ? 'bg-indigo-600/20 border-indigo-500 shadow-lg shadow-indigo-500/10 text-white' : 'bg-slate-900/60 border-white/10 text-slate-400 hover:border-white/20 hover:text-slate-200' }}"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-base">💻</span>
                                        @if($connection_type === 'local_daemon')
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-white">Local Daemon</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">localhost:20128</div>
                                </div>
                                <div class="text-[10px] text-emerald-400 mt-2 font-medium">Direct Browser Loopback</div>
                            </button>

                            <!-- 2. Cloudflare Tunnel / Ngrok -->
                            <button 
                                type="button" 
                                wire:click="setConnectionType('cloudflare_tunnel')" 
                                class="p-3.5 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between {{ $connection_type === 'cloudflare_tunnel' ? 'bg-indigo-600/20 border-indigo-500 shadow-lg shadow-indigo-500/10 text-white' : 'bg-slate-900/60 border-white/10 text-slate-400 hover:border-white/20 hover:text-slate-200' }}"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-base">☁️</span>
                                        @if($connection_type === 'cloudflare_tunnel')
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-white">Cloudflare Tunnel</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">HTTPS Remote URL</div>
                                </div>
                                <div class="text-[10px] text-indigo-400 mt-2 font-medium">Mobile & VPS Access</div>
                            </button>

                            <!-- 3. Admin Platform Cluster -->
                            <button 
                                type="button" 
                                wire:click="setConnectionType('admin_cluster')" 
                                class="p-3.5 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between {{ $connection_type === 'admin_cluster' ? 'bg-indigo-600/20 border-indigo-500 shadow-lg shadow-indigo-500/10 text-white' : 'bg-slate-900/60 border-white/10 text-slate-400 hover:border-white/20 hover:text-slate-200' }}"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-base">🏛️</span>
                                        @if($connection_type === 'admin_cluster')
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-white">Platform Cluster</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Admin Managed</div>
                                </div>
                                <div class="text-[10px] text-amber-400 mt-2 font-medium">Zero Client Setup</div>
                            </button>

                            <!-- 4. Custom Enterprise Proxy -->
                            <button 
                                type="button" 
                                wire:click="setConnectionType('custom_proxy')" 
                                class="p-3.5 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between {{ $connection_type === 'custom_proxy' ? 'bg-indigo-600/20 border-indigo-500 shadow-lg shadow-indigo-500/10 text-white' : 'bg-slate-900/60 border-white/10 text-slate-400 hover:border-white/20 hover:text-slate-200' }}"
                            >
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-base">🌐</span>
                                        @if($connection_type === 'custom_proxy')
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-white">Enterprise Proxy</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Custom HTTPS</div>
                                </div>
                                <div class="text-[10px] text-purple-400 mt-2 font-medium">Self-Hosted / BYOK</div>
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Cloudflare Tunnel / Local Setup Helper -->
                    @if($connection_type === 'cloudflare_tunnel')
                        <div class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-300 flex items-center gap-1.5">
                                    <span>☁️</span> How to connect your local OmniRoute via Cloudflare Tunnel (Free & Secure):
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">No port forwarding needed</span>
                            </div>
                            <ol class="list-decimal list-inside space-y-1 text-slate-300 text-[11px] leading-relaxed">
                                <li>Run OmniRoute in terminal: <code class="bg-slate-900 px-1.5 py-0.5 rounded text-amber-300 font-mono">omniroute</code></li>
                                <li>In a second terminal, launch tunnel: <code class="bg-slate-900 px-1.5 py-0.5 rounded text-emerald-300 font-mono">cloudflared tunnel --url http://localhost:20128</code></li>
                                <li>Copy the generated <code class="text-indigo-300 font-mono">https://xyz.trycloudflare.com</code> URL and paste it below with <code class="text-indigo-300 font-mono">/v1</code> at the end.</li>
                            </ol>
                        </div>
                    @elseif($connection_type === 'local_daemon')
                        <div class="p-3.5 rounded-xl bg-slate-900/60 border border-white/10 text-xs flex items-center justify-between text-slate-300">
                            <div class="flex items-center gap-2">
                                <span>💻</span>
                                <span>Running locally on your PC? Start daemon in PowerShell/CMD: <code class="bg-slate-950 px-2 py-0.5 rounded text-amber-300 font-mono font-bold">omniroute</code></span>
                            </div>
                            <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 font-bold">Direct Browser Loopback</span>
                        </div>
                    @endif

                    <form wire:key="omniroute-user-key-form" wire:submit="saveUserKey" x-data="{ showKey: false }" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Master Gateway API Key with Show/Hide Toggle -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-medium text-slate-300">
                                        Personal Gateway API Key
                                    </label>
                                    <button 
                                        type="button" 
                                        x-on:click="$data.showKey = !$data.showKey" 
                                        class="text-[11px] text-indigo-400 hover:text-indigo-300 font-mono flex items-center gap-1 transition-colors cursor-pointer"
                                    >
                                        <span x-show="!$data.showKey">👁️ Show Key</span>
                                        <span x-show="$data.showKey">🔒 Hide Key</span>
                                    </button>
                                </div>
                                <div wire:key="omniroute-user-key-input-wrapper" class="relative">
                                    <input 
                                        :type="($data.showKey ?? false) ? 'text' : 'password'" 
                                        wire:model="user_api_key" 
                                        placeholder="sk-or-v1-..." 
                                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono pr-10"
                                        required
                                    />
                                    <button 
                                        type="button" 
                                        x-on:click="$data.showKey = !$data.showKey" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs cursor-pointer p-1"
                                        :title="($data.showKey ?? false) ? 'Hide key' : 'Show key'"
                                    >
                                        <span x-show="!$data.showKey">👁️</span>
                                        <span x-show="$data.showKey">🙈</span>
                                    </button>
                                </div>
                                @error('user_api_key') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Custom Gateway URL (Optional) -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-medium text-slate-300 block">
                                        Gateway Endpoint URL
                                    </label>
                                    <span class="text-[10px] text-violet-400 font-mono">Dynamic Route</span>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.400ms="user_custom_url" 
                                    placeholder="{{ $connection_type === 'cloudflare_tunnel' ? 'https://omni-gateway.yourdomain.com/v1' : 'http://localhost:20128/v1' }}" 
                                    class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                                />
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[10px] text-slate-400 font-semibold">Presets:</span>
                                    <button type="button" wire:click="setLocalPreset" class="px-2 py-0.5 text-[10px] rounded-md bg-white/5 hover:bg-violet-600/30 text-slate-300 hover:text-white border border-white/10 transition-colors cursor-pointer" title="Switch to local localhost daemon">
                                        🏠 localhost:20128
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1.5">Supports local daemons (<code class="text-violet-400">http://localhost:20128/v1</code>) and live Cloudflare Tunnels (<code class="text-cyan-400">https://*.trycloudflare.com/v1</code>).</p>
                                @error('user_custom_url') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-3 border-t border-white/5">
                            <button 
                                type="button" 
                                wire:click="testGatewayConnection"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-slate-900 border border-white/10 hover:border-emerald-500/50 hover:bg-emerald-950/30 text-slate-300 hover:text-emerald-300 text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
                            >
                                <span wire:loading.remove wire:target="testGatewayConnection">📡 Test Connection</span>
                                <span wire:loading wire:target="testGatewayConnection" class="flex items-center gap-1.5 text-emerald-400">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    Testing...
                                </span>
                            </button>

                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer flex items-center gap-2"
                            >
                                <span wire:loading.remove wire:target="saveUserKey">
                                    @if($saveStatus === 'success')
                                        ✓ Saved & Verified
                                    @elseif($saveStatus === 'error')
                                        ✕ Save Failed
                                    @else
                                        💾 Save Gateway Key
                                    @endif
                                </span>
                                <span wire:loading wire:target="saveUserKey" class="inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                @endif
            </x-glass.card>
        </div>

        <!-- Right: Application Console Output & Debug Stream (1 Col) -->
        <div class="space-y-6">
            <x-glass.card variant="standard" class="p-4 sm:p-5 space-y-3" wire:poll.5s="fetchConsoleLogs">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🖥️</span>
                        <div>
                            <h3 class="text-sm font-bold text-white leading-none">Application Console</h3>
                            <span class="text-[10px] text-slate-400">Live output and routing telemetry stream</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 text-[10px] text-slate-400 bg-slate-900/80 px-2.5 py-1 rounded-lg border border-white/5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-mono text-white font-bold">{{ count($filteredLogs) }}</span> entries
                        @if($lastUpdated)
                            <span class="text-slate-600">•</span>
                            <span class="text-slate-400 font-mono text-[9px]">{{ $lastUpdated }}</span>
                        @endif
                    </div>
                </div>

                <!-- Console Filter Bar -->
                <div class="flex items-center gap-2 text-xs">
                    <input 
                        type="text" 
                        wire:model.live.debounce.150ms="logSearch" 
                        placeholder="Search logs..." 
                        class="w-full bg-slate-950 border border-white/10 rounded-lg px-2.5 py-1 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 font-mono"
                    />

                    <select wire:model.live="logLevelFilter" class="bg-slate-950 border border-white/10 rounded-lg px-2 py-1 text-[11px] text-slate-300 focus:outline-none focus:border-indigo-500">
                        <option value="all">All</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                </div>

                <!-- Terminal Log Container -->
                <div class="h-64 overflow-y-auto bg-slate-950 rounded-xl border border-white/10 p-3 font-mono text-[11px] space-y-1.5 text-slate-300 select-text scrollbar-thin">
                    @forelse($filteredLogs as $log)
                        <div class="flex items-start gap-2 leading-relaxed border-b border-white/[0.02] pb-1">
                            <span class="text-slate-500 text-[10px] shrink-0 font-mono">
                                {{ \Illuminate\Support\Carbon::parse($log['timestamp'] ?? now())->format('H:i:s') }}
                            </span>

                            <span class="px-1 py-0.2 rounded text-[9px] font-bold uppercase shrink-0 {{ ($log['level'] ?? 'info') === 'info' ? 'bg-indigo-950 text-indigo-300 border border-indigo-500/30' : 'bg-slate-900 text-slate-400 border border-white/5' }}">
                                {{ $log['level'] ?? 'INFO' }}
                            </span>

                            <span class="text-slate-300 break-words flex-1">
                                {{ $log['message'] }}
                            </span>
                        </div>
                    @empty
                        <div class="text-slate-600 text-center py-8">
                            No telemetry logs captured yet.
                        </div>
                    @endforelse
                </div>
            </x-glass.card>
        </div>
    </div>

    <!-- Dynamic OmniRoute Catalog & Unified Model Matrix (Exact Admin Matching Layout) -->
    <div class="space-y-5 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2.5">
                    <span>🎛️ Dynamic OmniRoute Catalog</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 font-mono text-xs font-bold">
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
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Real-time health verification across 42 free tier pools, reasoning engines, and auto-fallback combos.
                </p>
            </div>

            <!-- Bulk Diagnostics & Per Page Selector -->
            <div class="flex flex-wrap items-center gap-3">
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
                    wire:click="resyncModels" 
                    wire:loading.attr="disabled"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-indigo-500/30 hover:border-indigo-500/60 text-indigo-300 hover:text-white text-xs font-semibold transition-all cursor-pointer"
                    title="Re-synchronize catalog directly from OmniRoute /v1/models"
                >
                    <span wire:loading.remove wire:target="resyncModels">🔄 Resync Models</span>
                    <span wire:loading wire:target="resyncModels" class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-ping"></span>
                        Syncing...
                    </span>
                </button>

                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="text-[11px]">Per page:</span>
                    <select wire:model.live="perPage" class="bg-slate-900 border border-white/10 rounded-lg px-2 py-1 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="12">12</option>
                        <option value="18">18</option>
                        <option value="36">36</option>
                        <option value="72">72</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar (Exact Admin Matching Style) -->
        <x-glass.card variant="subtle" class="p-4 space-y-3 border border-white/10">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="w-full sm:flex-1">
                    <input 
                        type="text" 
                        wire:model.live.debounce.250ms="modelSearch" 
                        placeholder="Search models by name, vendor, or ID (e.g. deepseek, claude, gpt-4o, llama, combo)..." 
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-medium"
                    />
                </div>

                <!-- Status Filter Dropdown -->
                <div class="w-full sm:w-64">
                    <select wire:model.live="modelStatusFilter" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 font-medium">
                        <option value="all">All Models ({{ $totalModelsCount }})</option>
                        <option value="working">🟢 Working Models ({{ $workingCount }})</option>
                        <option value="failed">🔴 Failed Models ({{ $failedCount }})</option>
                        <option value="free_tier">⚡ Free Tier Pools ({{ $freeTierCount }})</option>
                        <option value="combos">🔀 Auto Combos ({{ $combosCount }})</option>
                        <option value="reasoning">🧠 Reasoning Engines ({{ $reasoningCount }})</option>
                    </select>
                </div>
            </div>

            <!-- Quick Engine & Capability Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                <span class="text-[11px] text-slate-400 mr-1 font-bold">Providers:</span>
                
                <button 
                    type="button" 
                    wire:click="$set('modelVendorFilter', '')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelVendorFilter === '' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
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
                        class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] flex items-center gap-1 {{ $modelVendorFilter === $v->owned_by ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
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
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'free_tier' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    ⚡ Free Tier ({{ $freeTierCount }})
                </button>

                <button 
                    type="button" 
                    wire:click="$set('modelStatusFilter', 'reasoning')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'reasoning' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    🧠 Reasoning ({{ $reasoningCount }})
                </button>

                <button 
                    type="button" 
                    wire:click="$set('modelStatusFilter', 'combos')" 
                    class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px] {{ $modelStatusFilter === 'combos' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    🔀 Auto Combos ({{ $combosCount }})
                </button>
            </div>
        </x-glass.card>

        <!-- Models Grid (Paginated matching Admin Cards style) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($models as $m)
                @php
                    $isCurrentDefault = $default_model === $m->model_id;
                    $usage = $modelUsage->get($m->model_id);
                    $isBeingTested = in_array($m->id, $testingModelIds, true);
                @endphp
                <x-glass.card variant="standard" class="p-5 flex flex-col justify-between hover:border-indigo-500/40 transition-all relative {{ $isCurrentDefault ? 'border-indigo-500/50 bg-indigo-950/20' : '' }}">
                    @if($isCurrentDefault)
                        <div class="absolute top-0 right-0 px-2.5 py-0.5 bg-gradient-to-l from-indigo-600 to-purple-600 text-white font-mono text-[9px] font-bold uppercase rounded-bl-lg shadow-sm">
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

                    <!-- Footer Actions: Health Probe -->
                    <div class="pt-3 border-t border-white/5 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-400 font-mono">
                            {{ $m->is_free_tier ? '🎁 Zero Quota Cost' : 'Standard Rate' }}
                        </span>

                        <button 
                            type="button" 
                            wire:click="probeModelHealth({{ $m->id }})" 
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-indigo-500/40 text-xs text-indigo-300 hover:text-white transition-all cursor-pointer flex items-center gap-1.5"
                            title="Run instant real-time completion test"
                        >
                            @if($isBeingTested)
                                <span class="w-2.5 h-2.5 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></span>
                                <span>Testing...</span>
                            @else
                                <span>🧪 Probe Test</span>
                            @endif
                        </button>
                    </div>
                </x-glass.card>
            @empty
                <div class="col-span-full">
                    <x-glass.card variant="subtle" class="p-12 text-center text-slate-500">
                        <span class="text-3xl block mb-2">🔍</span>
                        <p class="text-sm font-semibold text-slate-400">No OmniRoute models match your search filters.</p>
                        <p class="text-xs text-slate-600 mt-1">Try clearing your search query or selecting "All Engines".</p>
                    </x-glass.card>
                </div>
            @endforelse
        </div>

        @if($models->hasPages())
            <div class="p-4 border-t border-white/5">
                {{ $models->links() }}
            </div>
        @endif
    </div>
</div>

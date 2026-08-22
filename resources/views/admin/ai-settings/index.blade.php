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

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>AI Providers & Model Gateways</span>
                <x-glass.badge variant="violet">v3.8.50 Ready</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Configure OmniRoute gateway endpoints, manage user BYOK policies, monitor active models, and track token usage.
            </p>
        </div>

        <a href="{{ route('admin.ai-settings.omniroute') }}" wire:navigate>
            <x-glass.button variant="primary" size="sm" class="shadow-lg shadow-violet-500/25 gap-2">
                <span>⚡</span>
                <span>Configure OmniRoute Gateway &rarr;</span>
            </x-glass.button>
        </a>
    </div>

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 font-semibold">
            {{ session('status') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300 font-semibold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Global AI Circuit Breaker Control Card -->
    <div class="p-4 sm:p-6 rounded-2xl border transition-all {{ $circuitStatus['is_tripped'] ? 'bg-red-950/40 border-red-500/50 shadow-2xl shadow-red-900/30' : 'bg-slate-900/60 border-white/10' }}">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $circuitStatus['is_tripped'] ? 'bg-red-600/30 border border-red-500/50 text-red-300 animate-pulse' : 'bg-emerald-600/20 border border-emerald-500/30 text-emerald-400' }}">
                    {{ $circuitStatus['is_tripped'] ? '🛑' : '🛡️' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-white">Emergency AI Circuit Breaker</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ $circuitStatus['is_tripped'] ? 'bg-red-500 text-white' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                            {{ $circuitStatus['is_tripped'] ? 'TRIPPED / PAUSED' : 'NORMAL / ACTIVE' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $circuitStatus['is_tripped'] ? 'All outgoing AI calls are currently stopped. ' . $circuitStatus['reason'] : 'Instant kill-switch to pause all upstream AI API requests across the platform.' }}
                    </p>
                </div>
            </div>

            <button 
                type="button" 
                wire:click="toggleCircuitBreaker" 
                wire:confirm="{{ $circuitStatus['is_tripped'] ? 'Restore normal AI traffic?' : 'Are you sure you want to pause all outgoing AI calls platform-wide?' }}"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-lg {{ $circuitStatus['is_tripped'] ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/30' : 'bg-red-600/80 hover:bg-red-600 text-white border border-red-500/50 shadow-red-600/20' }}"
            >
                {{ $circuitStatus['is_tripped'] ? '✓ Restore AI Traffic' : '🛑 Trip Circuit Breaker' }}
            </button>
        </div>
    </div>

    <!-- Telemetry Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Configured Providers</span>
                <span class="text-lg">🔌</span>
            </div>
            <div class="text-2xl font-black text-white">{{ $providers->count() }} Providers</div>
            <div class="text-[11px] text-emerald-400 mt-1">{{ $providers->where('is_active', true)->count() }} Online & Active</div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Total AI Words Consumed</span>
                <span class="text-lg">✍️</span>
            </div>
            <div class="text-2xl font-black text-indigo-400">{{ number_format($totalSystemWords) }} words</div>
            <div class="text-[11px] text-slate-400 mt-1">Across all provider models</div>
        </x-glass.card>

        <x-glass.card variant="premium" glow="violet" class="p-5">
            <div class="flex items-center justify-between text-slate-300 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Raw Tokens Streamed</span>
                <span class="text-lg">⚡</span>
            </div>
            <div class="text-2xl font-black text-white">{{ number_format($totalSystemTokens) }} tokens</div>
            <div class="text-[11px] text-violet-300 mt-1">Decoupled gateway metering</div>
        </x-glass.card>
    </div>

    <!-- Provider Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($providers as $provider)
            @php
                $isOmni = $provider->slug === 'omniroute';
                $pUsage = $providerUsage->get($provider->id);
                $pTokens = $pUsage->tokens ?? 0;
                $pWords = $pUsage->words ?? 0;
            @endphp
            <x-glass.card variant="standard" class="p-6 flex flex-col justify-between hover:border-violet-500/40 transition-all relative overflow-hidden group {{ $isOmni ? 'border-violet-500/30 bg-violet-950/10 md:col-span-2 lg:col-span-1 shadow-lg shadow-violet-950/20' : '' }}">
                @if($isOmni)
                    <div class="absolute top-0 right-0 px-3 py-1 bg-gradient-to-l from-violet-600 to-indigo-600 text-white font-mono text-[9px] font-bold uppercase rounded-bl-xl shadow-md flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>PRIMARY GATEWAY</span>
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Top Provider Title & Health Indicator -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-slate-900 border border-white/10 flex items-center justify-center text-2xl shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                                {{ $provider->icon ?? '🤖' }}
                            </div>
                            <div class="truncate">
                                <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                                    <span>{{ $provider->name }}</span>
                                </h3>
                                <span class="text-[11px] font-mono text-violet-400 truncate block">{{ $provider->slug }}</span>
                            </div>
                        </div>

                        @if($isOmni)
                            <div class="pt-6 sm:pt-0">
                                @if(!$provider->is_active)
                                    <span class="px-2 py-0.5 rounded-full bg-red-950/80 border border-red-500/40 text-red-400 text-[10px] font-mono font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        DEACTIVATED
                                    </span>
                                @elseif($gatewayOnline)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-[10px] font-mono font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        ONLINE ({{ $gatewayLatencyMs ?? 12 }}ms)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-amber-950/80 border border-amber-500/40 text-amber-300 text-[10px] font-mono font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        STANDBY (FALLBACK)
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-slate-300 leading-relaxed min-h-[32px]">
                        {{ $provider->description }}
                    </p>

                    <!-- Provider Metrics Matrix -->
                    <div class="grid grid-cols-2 gap-2.5 p-3.5 rounded-xl bg-slate-900/70 border border-white/5 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Total Models</span>
                            <span class="font-bold text-white font-mono text-sm">{{ $provider->models->count() }} In DB</span>
                            <span class="text-[10px] text-emerald-400 block">{{ $provider->liveModelsCount() }} Active</span>
                        </div>

                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Tokens Streamed</span>
                            <span class="font-bold text-violet-300 font-mono text-sm">{{ number_format($pTokens) }} tok</span>
                            <span class="text-[10px] text-slate-400 block font-mono">{{ number_format($pWords) }} words</span>
                        </div>

                        <div class="pt-2 border-t border-white/5">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">Endpoint Type</span>
                            <span class="font-mono text-indigo-300 text-[11px] font-semibold block">
                                {{ $isOmni ? 'Local & Cloud Supported' : ($provider->is_local ? 'Local Gateway' : 'Cloud Remote API') }}
                            </span>
                            <span class="text-[9px] text-slate-500 font-mono truncate block">
                                {{ $isOmni ? (str_starts_with($provider->base_url ?? '', 'https') ? 'HTTPS Cloud Tunnel' : '127.0.0.1:20128 Loopback') : ($provider->base_url ?? 'Default Endpoint') }}
                            </span>
                        </div>

                        <div class="pt-2 border-t border-white/5 flex flex-col justify-between">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold tracking-wider">User BYOK Keys</span>
                            <button 
                                type="button"
                                wire:click="toggleAllowUserKey({{ $provider->id }})" 
                                class="mt-1 flex items-center justify-between px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono transition-all cursor-pointer {{ $provider->allow_user_key ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/25' : 'bg-slate-800 text-slate-400 border border-white/10 hover:bg-slate-700' }}"
                                title="Toggle whether users can configure their own personal API keys"
                            >
                                <span>{{ $provider->allow_user_key ? '✓ Users Allowed' : '✕ Admin Managed' }}</span>
                                <span class="w-2 h-2 rounded-full {{ $provider->allow_user_key ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-4 border-t border-white/5 flex items-center justify-between gap-3 mt-4">
                    <button 
                        type="button"
                        wire:click="toggleProviderActive({{ $provider->id }})" 
                        class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase transition-all cursor-pointer flex items-center gap-1.5 {{ $provider->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20' }}"
                    >
                        <span class="w-2 h-2 rounded-full {{ $provider->is_active ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                        <span>{{ $provider->is_active ? 'Gateway Enabled' : 'Gateway Disabled' }}</span>
                    </button>

                    @if($isOmni)
                        <a href="{{ route('admin.ai-settings.omniroute') }}" wire:navigate>
                            <x-glass.button variant="primary" size="sm" class="gap-1.5 shadow-md shadow-violet-500/25 font-bold text-xs">
                                <span>⚡ Setup Gateway &rarr;</span>
                            </x-glass.button>
                        </a>
                    @else
                        <a href="{{ route('admin.ai-settings.omniroute') }}" wire:navigate>
                            <x-glass.button variant="secondary" size="sm" class="gap-1 text-xs">
                                <span>⚙️ Configure</span>
                            </x-glass.button>
                        </a>
                    @endif
                </div>
            </x-glass.card>
        @endforeach
    </div>

    <!-- AI Model Governance & Real-Time Latency Routing Matrix -->
    <div class="space-y-4 pt-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>🎛️ Model Governance & Routing Policy</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-violet-600/20 border border-violet-500/40 text-violet-300 font-mono">{{ $models->count() }} Models</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Control fallback priority, user access tiers, pricing thresholds, and live health pings.</p>
            </div>
        </div>

        <x-glass.card variant="elevated" class="overflow-x-auto border border-white/10">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-white/[0.02]">
                        <th class="py-3.5 px-4">Model & Provider</th>
                        <th class="py-3.5 px-3">Context</th>
                        <th class="py-3.5 px-3">Cost / 1k Tokens</th>
                        <th class="py-3.5 px-3">Tier Access</th>
                        <th class="py-3.5 px-3">Health & Latency</th>
                        <th class="py-3.5 px-3">Total Consumption</th>
                        <th class="py-3.5 px-4 text-right">Governance Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 font-sans">
                    @forelse($models as $model)
                        @php
                            $st = $usageStats->get($model->model_id);
                        @endphp
                        <tr class="hover:bg-white/[0.03] transition-colors {{ $model->is_default ? 'bg-violet-600/10' : '' }}">
                            <!-- Model Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="font-bold text-white flex items-center gap-1.5">
                                        <span>{{ $model->name }}</span>
                                        @if($model->is_default)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-violet-600 text-white font-mono font-bold uppercase tracking-wider shadow-sm">
                                                PRIMARY
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span>{{ $model->model_id }}</span>
                                    <span>•</span>
                                    <span class="text-indigo-400 capitalize">{{ $model->provider->name ?? 'OmniRoute' }}</span>
                                </div>
                            </td>

                            <!-- Context Window -->
                            <td class="py-3.5 px-3 font-mono text-slate-300">
                                {{ number_format($model->context_window) }} tokens
                            </td>

                            <!-- Pricing -->
                            <td class="py-3.5 px-3 font-mono text-[11px]">
                                <span class="text-emerald-400">${{ number_format($model->cost_per_1k_input, 4) }} in</span>
                                <span class="text-slate-500">/</span>
                                <span class="text-indigo-400">${{ number_format($model->cost_per_1k_output, 4) }} out</span>
                            </td>

                            <!-- Tier Access -->
                            <td class="py-3.5 px-3">
                                <button 
                                    type="button" 
                                    wire:click="toggleModelFreeTier({{ $model->id }})" 
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold font-mono uppercase transition-all cursor-pointer {{ $model->is_free_tier ? 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/30' : 'bg-purple-500/10 text-purple-300 border border-purple-500/30' }}"
                                    title="Click to toggle between Free and Pro-Only tier"
                                >
                                    {{ $model->is_free_tier ? 'Free / Starter' : 'Pro Only' }}
                                </button>
                            </td>

                            <!-- Health & Latency -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $model->last_test_status === 'healthy' ? 'bg-emerald-400' : ($model->last_test_status === 'degraded' ? 'bg-yellow-400' : ($model->last_test_status === 'offline' ? 'bg-red-500' : 'bg-slate-500')) }}"></span>
                                    <span class="font-mono text-[11px] {{ $model->last_test_status === 'healthy' ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">
                                        {{ $model->last_test_latency_ms ? $model->last_test_latency_ms . 'ms' : 'Untested' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Total Consumption -->
                            <td class="py-3.5 px-3 font-mono text-[11px]">
                                <div class="text-slate-200 font-semibold">{{ number_format($st->total_words ?? 0) }} words</div>
                                <div class="text-[10px] text-slate-400">{{ number_format($st->total_calls ?? 0) }} calls</div>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Test Ping Button -->
                                    <button 
                                        type="button" 
                                        wire:click="pingModel({{ $model->id }})" 
                                        class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 border border-white/10 hover:border-indigo-500/40 text-[11px] font-semibold text-slate-300 hover:text-white transition-all cursor-pointer"
                                        title="Send test completion ping via OmniRoute"
                                    >
                                        ⚡ Ping
                                    </button>

                                    <!-- Set Default Button -->
                                    @if(!$model->is_default)
                                        <button 
                                            type="button" 
                                            wire:click="setDefaultModel({{ $model->id }})" 
                                            class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-violet-600/20 border border-white/10 hover:border-violet-500/40 text-[11px] font-semibold text-slate-300 hover:text-violet-200 transition-all cursor-pointer"
                                            title="Set as global fallback model"
                                        >
                                            Set Primary
                                        </button>
                                    @endif

                                    <!-- Toggle Active Button -->
                                    <button 
                                        type="button" 
                                        wire:click="toggleModelActive({{ $model->id }})" 
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer {{ $model->is_active ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 hover:bg-red-500/20' }}"
                                    >
                                        {{ $model->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">
                                No models registered in database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-glass.card>
    </div>
</div>
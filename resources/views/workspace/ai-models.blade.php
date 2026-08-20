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

<div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider font-mono">OmniRoute Cluster</span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $gatewayTelemetry['status'] }} ({{ $gatewayTelemetry['latency_ms'] }}ms)
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>⚡ AI Providers & Model Gateways</span>
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Explore connected AI engines, live model catalogs, and connect personal BYOK keys for unlimited generation rates.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('profile') }}" wire:navigate>
                <x-glass.button variant="secondary" size="md" class="gap-2">
                    <span>🔑 Manage BYOK Keys</span>
                </x-glass.button>
            </a>
            <a href="{{ route('templates.index') }}" wire:navigate>
                <x-glass.button variant="primary" size="md" class="gap-2 shadow-lg shadow-indigo-500/20">
                    <span>✨ Launch Studio &rarr;</span>
                </x-glass.button>
            </a>
        </div>
    </div>

    <!-- Live Gateway & Plan Telemetry Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Gateway Status</span>
                <span class="text-lg">🌐</span>
            </div>
            <div class="text-2xl font-black text-emerald-400 flex items-center gap-2">
                <span>{{ $gatewayTelemetry['status'] }}</span>
                <span class="text-xs font-mono text-slate-400 font-normal">({{ $gatewayTelemetry['latency_ms'] }}ms)</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Multi-Provider Dynamic Failover</div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Active Providers</span>
                <span class="text-lg">🔌</span>
            </div>
            <div class="text-2xl font-black text-white">{{ $providers->count() }} Providers</div>
            <div class="text-[11px] text-indigo-400 mt-1">{{ $allowedProviders->count() }} BYOK Enabled</div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Your BYOK Keys</span>
                <span class="text-lg">🔑</span>
            </div>
            <div class="text-2xl font-black text-indigo-300">{{ $apiKeys->count() }} Connected</div>
            <div class="text-[11px] text-emerald-400 mt-1">
                {{ $apiKeys->count() > 0 ? 'Unlimited Rate Limits Active' : 'Standard Quota Limits Apply' }}
            </div>
        </x-glass.card>

        <x-glass.card variant="premium" glow="indigo" class="p-5">
            <div class="flex items-center justify-between text-slate-300 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Plan Word Balance</span>
                <span class="text-lg">✍️</span>
            </div>
            <div class="text-2xl font-black text-white">
                {{ number_format(max(0, ($user->monthly_word_quota ?? 15000) - ($user->used_word_quota ?? 0))) }}
            </div>
            <div class="text-[11px] text-indigo-300 mt-1">Words remaining this cycle</div>
        </x-glass.card>
    </div>

    <!-- Connected AI Providers Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>🔌 Active AI Providers & Gateways</span>
            </h2>
            <span class="text-xs text-slate-400 font-mono">{{ $providers->count() }} Providers Configured</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($providers as $p)
                <x-glass.card variant="standard" class="p-5 flex flex-col justify-between hover:border-indigo-500/40 transition-all group relative overflow-hidden">
                    @if($p->slug === 'omniroute')
                        <div class="absolute top-0 right-0 px-2.5 py-0.5 bg-gradient-to-l from-indigo-600 to-purple-600 text-white font-mono text-[9px] font-bold uppercase rounded-bl-lg shadow-md">
                            PRIMARY GATEWAY
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-white/10 flex items-center justify-center text-xl shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                                {{ $p->icon ?? '🤖' }}
                            </div>
                            <div class="truncate">
                                <h3 class="text-sm font-bold text-white tracking-tight truncate">{{ $p->name }}</h3>
                                <span class="text-[11px] font-mono text-slate-400 truncate block">{{ $p->slug }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-300 mb-4 line-clamp-2 leading-relaxed">
                            {{ $p->description }}
                        </p>

                        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-900/60 border border-white/5 text-xs mb-4">
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase">Models</span>
                                <span class="font-bold text-white font-mono">{{ $p->models->count() }} Available</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase">Endpoint</span>
                                <span class="font-mono text-indigo-300 text-[11px]">{{ $p->is_local ? 'Local Gateway' : 'Cloud Gateway' }}</span>
                            </div>
                            <div class="col-span-2 pt-2 border-t border-white/5 flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 uppercase">BYOK Key Policy:</span>
                                @if($p->allow_user_key)
                                    <span class="text-[10px] font-bold font-mono text-emerald-400 flex items-center gap-1">
                                        <span>✓ Permitted</span>
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold font-mono text-slate-500 flex items-center gap-1">
                                        <span>✕ Managed by Platform</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-white/5 flex items-center justify-between gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-400 font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                        </span>

                        @if($p->slug === 'omniroute')
                            <a href="{{ route('ai-models.omniroute') }}" wire:navigate>
                                <x-glass.button variant="primary" size="sm" class="gap-1 shadow-md shadow-indigo-500/20">
                                    <span>⚡ Setup Gateway &rarr;</span>
                                </x-glass.button>
                            </a>
                        @elseif($p->allow_user_key)
                            @php
                                $hasKey = $apiKeys->where('provider_slug', $p->slug)->isNotEmpty();
                            @endphp
                            @if($hasKey)
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                    🔑 Key Connected
                                </span>
                            @else
                                <a href="#byok-section" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                                    + Add Key &rarr;
                                </a>
                            @endif
                        @endif
                    </div>
                </x-glass.card>
            @endforeach
        </div>
    </div>

    <!-- Live OmniRoute Model Catalog Directory -->
    <div class="space-y-4 pt-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>🎛️ OmniRoute AI Model Catalog</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-600/20 border border-indigo-500/40 text-indigo-300 font-mono">
                        {{ $models->total() }} Models
                    </span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Explore available AI models with real-time health pings and capabilities.</p>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-2">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search models..." 
                    class="bg-slate-900/90 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 min-w-[200px]"
                />

                <select wire:model.live="selectedProvider" class="bg-slate-900/90 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="all">All Providers</option>
                    @foreach($providers as $prov)
                        <option value="{{ $prov->slug }}">{{ $prov->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedTier" class="bg-slate-900/90 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="all">All Tiers</option>
                    <option value="free">Free-Tier Models 🎁</option>
                </select>
            </div>
        </div>

        @if(session('status'))
            <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <x-glass.card variant="elevated" class="overflow-x-auto border border-white/10">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-white/10 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-white/[0.02]">
                        <th class="py-3.5 px-4">Model & Provider</th>
                        <th class="py-3.5 px-3">Context Window</th>
                        <th class="py-3.5 px-3">Capabilities</th>
                        <th class="py-3.5 px-3">Tier</th>
                        <th class="py-3.5 px-3 text-right">Health Check</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($models as $model)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white flex items-center gap-2">
                                    <span>{{ $model->name }}</span>
                                    @if($model->is_free_tier)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] bg-emerald-500/20 text-emerald-300 font-mono border border-emerald-500/30">
                                            FREE POOL
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 flex items-center gap-2">
                                    <span class="text-indigo-400 font-medium">{{ $model->provider->name ?? 'OmniRoute' }}</span>
                                    <span>&bull;</span>
                                    <span class="truncate max-w-[200px]">{{ $model->model_id }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-3 font-mono text-[11px] text-slate-300">
                                {{ number_format($model->context_window) }} tokens
                            </td>

                            <td class="py-3 px-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-white/5 border border-white/10 text-slate-300 font-mono" title="Text & Chat Generation">
                                        📝 Text
                                    </span>
                                    @if(str_contains(strtolower($model->model_id), 'vision') || str_contains(strtolower($model->model_id), '4o') || str_contains(strtolower($model->model_id), 'flash') || str_contains(strtolower($model->model_id), 'claude-3'))
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 font-mono" title="Multimodal Vision Support">
                                            👁️ Vision
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono uppercase font-bold {{ $model->is_free_tier ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-300 border border-white/10' }}">
                                    {{ $model->is_free_tier ? 'Free Tier' : 'Standard' }}
                                </span>
                            </td>

                            <td class="py-3 px-3 text-right">
                                @if(isset($pingResults[$model->id]))
                                    <span class="text-[11px] font-mono font-bold mr-2 {{ $pingResults[$model->id]['status'] === 'healthy' ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $pingResults[$model->id]['latency_ms'] }}ms
                                    </span>
                                @endif

                                <button 
                                    type="button" 
                                    wire:click="pingModel({{ $model->id }})" 
                                    wire:loading.attr="disabled"
                                    class="px-2.5 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-indigo-500/50 text-[11px] text-slate-300 hover:text-white transition-all cursor-pointer inline-flex items-center gap-1.5"
                                >
                                    @if($testingModelId === $model->id)
                                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                                        <span>Pinging...</span>
                                    @else
                                        <span>📡 Ping</span>
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 text-xs">
                                No models found matching your search filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @if($models->hasPages())
            <div class="p-4 border-t border-white/5">
                {{ $models->links() }}
            </div>
        @endif
        </x-glass.card>
    </div>

    <!-- BYOK Key Connection Section for User -->
    <div class="pt-4" id="byok-section">
        <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-6 border border-white/10">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>🔑 Connect Personal BYOK API Keys</span>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono font-bold uppercase">
                            AES-256-GCM Encrypted
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Connect your personal API keys for admin-permitted providers. <strong>Using your own key grants 100% unlimited AI generation rates (no rate limit).</strong>
                    </p>
                </div>
            </div>

            @if ($statusMessage)
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
                    {{ $statusMessage }}
                </div>
            @endif

            @if($allowedProviders->isNotEmpty())
                <form wire:submit="saveApiKey" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-900/60 border border-white/5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Provider (Admin Enabled)</label>
                        <select wire:model="byok_provider" class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            @foreach($allowedProviders as $ap)
                                <option value="{{ $ap->slug }}">{{ $ap->name }} ({{ $ap->slug }})</option>
                            @endforeach
                            <option value="custom">Custom Endpoint (Ollama / vLLM / Local)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">API Key / Token</label>
                        <input 
                            type="password" 
                            wire:model="byok_api_key" 
                            placeholder="sk-..." 
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                            required
                        />
                        @error('byok_api_key') <p class="text-[10px] text-red-400 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Custom Base URL (Optional)</label>
                            <input 
                                type="text" 
                                wire:model="byok_custom_url" 
                                placeholder="http://localhost:11434/v1" 
                                class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                            />
                        </div>
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer shrink-0"
                        >
                            Save Key
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/10 text-xs text-slate-400 flex items-center gap-2.5">
                    <span class="text-base">ℹ️</span>
                    <span>Custom BYOK API keys are currently disabled by the administrator. Platform master gateway keys and plan word quotas are in effect.</span>
                </div>
            @endif

            <!-- Connected Keys Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5 px-3">Provider</th>
                            <th class="py-2.5 px-3">Endpoint</th>
                            <th class="py-2.5 px-3">Encrypted API Key</th>
                            <th class="py-2.5 px-3">Rate Limit</th>
                            <th class="py-2.5 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($apiKeys as $key)
                            @php
                                $isVisible = in_array($key->id, $visibleKeys, true);
                                $rawKey = $key->getRawKeyForOwner(auth()->user());
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-white uppercase font-mono">{{ $key->provider_slug }}</span>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-slate-400">
                                    {{ $key->custom_base_url ?: 'Default Cloud Gateway' }}
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px]">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-300">
                                            {{ $isVisible ? $rawKey : '••••••••••••••••' . substr($rawKey, -4) }}
                                        </span>
                                        <button 
                                            type="button" 
                                            wire:click="toggleKeyVisibility({{ $key->id }})" 
                                            class="text-[10px] text-indigo-400 hover:text-indigo-300 cursor-pointer"
                                        >
                                            {{ $isVisible ? 'Hide' : 'Reveal' }}
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px]">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold uppercase">
                                        100% Unlimited
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <button 
                                        type="button" 
                                        wire:click="deleteApiKey({{ $key->id }})" 
                                        wire:confirm="Remove this custom API key?"
                                        class="text-red-400 hover:text-red-300 text-xs cursor-pointer font-semibold"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500 text-xs">
                                    No personal BYOK keys connected yet. Using platform master gateway keys.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-glass.card>
    </div>
</div>

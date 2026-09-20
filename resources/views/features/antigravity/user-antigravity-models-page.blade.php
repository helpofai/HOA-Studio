{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Antigravity Models Page
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
|
|--------------------------------------------------------------------------
*/
--}}

<div 
    class="space-y-8 animate-fade-in pb-16"
    x-data="{
        connectedNotice: false,
        openOAuthPopup(url) {
            const width = 600;
            const height = 700;
            const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
            const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
            const screenWidth = window.innerWidth || document.documentElement.clientWidth || screen.width;
            const screenHeight = window.innerHeight || document.documentElement.clientHeight || screen.height;
            const left = Math.round(((screenWidth / 2) - (width / 2)) + dualScreenLeft);
            const top = Math.round(((screenHeight / 2) - (height / 2)) + dualScreenTop);
            
            try {
                const popup = window.open(
                    url,
                    'AntigravityOAuthPopup',
                    `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=1,resizable=yes`
                );

                if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                    window.location.href = url;
                } else if (window.focus) {
                    popup.focus();
                }
            } catch (err) {
                window.location.href = url;
            }
        },
        init() {
            window.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'antigravity:oauth_success') {
                    this.connectedNotice = true;
                    if (typeof $wire !== 'undefined' && $wire.refreshAccounts) {
                        $wire.refreshAccounts();
                    }
                    setTimeout(() => { this.connectedNotice = false; }, 6000);
                }
            });
        }
    }"
>
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('ai-models.index') }}" wire:navigate class="text-xs text-indigo-400 hover:underline flex items-center gap-1 font-mono">
                    <span>&larr; AI Models & Gateways</span>
                </a>
                <span class="text-slate-600">&bull;</span>
                <span class="text-xs font-mono text-slate-400">Antigravity Gateway Hub</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>âš¡ Antigravity Gateway</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/30 shadow-sm">
                    GOOGLE OAUTH FABRIC
                </span>
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Multi-account auto-rotating Google Cloud & Gemini fabric with dynamic server model discovery.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <x-glass.button 
                variant="secondary" 
                size="md" 
                class="gap-2"
                wire:click="openManualModal"
            >
                <span>ðŸ”‘ Add CLI Token</span>
            </x-glass.button>
            <x-glass.button 
                variant="primary" 
                size="md" 
                class="gap-2 shadow-lg shadow-indigo-500/20"
                @click="openOAuthPopup('{{ route('oauth.antigravity.redirect') }}')"
            >
                <span>âš¡ Connect Account &rarr;</span>
            </x-glass.button>
        </div>
    </div>

    <!-- Live PostMessage Popup Success Notice -->
    <div 
        x-show="connectedNotice" 
        x-cloak 
        x-transition
        class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 flex items-center gap-2.5 shadow-lg"
    >
        <span>âœ“</span>
        <span>Google OAuth account connected! Models and quota fabric successfully synchronized.</span>
    </div>

    <!-- Status & Error Alerts -->
    @if($statusMessage || session('status') || session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2.5">
                <span>âœ“</span>
                <span>{{ $statusMessage ?: (session('status') ?: session('success')) }}</span>
            </div>
            <button wire:click="$set('statusMessage', null)" class="text-slate-400 hover:text-white text-xs cursor-pointer">&times;</button>
        </div>
    @endif
    @if($errorMessage || session('error'))
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-xs text-red-300 flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2.5">
                <span>âœ•</span>
                <span>{{ $errorMessage ?: session('error') }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" class="text-slate-400 hover:text-white text-xs cursor-pointer">&times;</button>
        </div>
    @endif

    <!-- System & Metric Stats Matrix -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-slate-900/40 border border-white/10 rounded-2xl">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">Linked Accounts</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-white">{{ $activeAccountsCount }}</span>
                <span class="text-xs text-emerald-400 font-medium">/ {{ $accounts->count() }} Total</span>
            </div>
        </div>

        <div class="p-4 bg-slate-900/40 border border-white/10 rounded-2xl">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">Discovered Models</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-indigo-400">{{ $totalModelsCount }}</span>
                <span class="text-xs text-emerald-400 font-mono">{{ $workingCount }} Live</span>
            </div>
        </div>

        <div class="p-4 bg-slate-900/40 border border-white/10 rounded-2xl">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">Tokens Consumed</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-indigo-400">{{ number_format($userTotalTokensUsed) }}</span>
                <span class="text-xs text-slate-400 font-mono">Tokens</span>
            </div>
        </div>

        <div class="p-4 bg-slate-900/40 border border-white/10 rounded-2xl">
            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block">Remaining Quota</span>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-black text-emerald-400">{{ number_format($userRemainingQuota) }}</span>
                <span class="text-xs text-emerald-300/80 font-mono">Words</span>
            </div>
        </div>
    </div>

    <!-- Linked Accounts Panel -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">
                Linked Google Accounts ({{ $accounts->count() }})
            </h2>
            <span class="text-[11px] font-mono text-slate-500">Auto-rotates with dynamic JIT load balancing</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($accounts as $account)
                <div class="p-4 bg-slate-900/60 border border-white/10 rounded-2xl flex flex-col justify-between gap-3 hover:border-indigo-500/30 transition-all">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600/30 to-purple-600/30 border border-indigo-500/20 flex items-center justify-center text-sm font-bold text-indigo-300">
                                {{ strtoupper(substr($account->email, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-white truncate max-w-[160px]" title="{{ $account->email }}">
                                    {{ $account->email }}
                                </h3>
                                <p class="text-[10px] text-slate-400 font-mono">
                                    Priority #{{ $account->priority_order ?? 1 }} &bull; Project: <span class="text-indigo-300">{{ $account->project_id ? Str::limit($account->project_id, 14) : 'cloudcode-pa' }}</span>
                                </p>
                            </div>
                        </div>

                        @if($account->is_quota_exhausted)
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30 rounded-lg">
                                Exhausted (Reset in {{ $account->quota_reset_at ? $account->quota_reset_at->diffForHumans() : '24h' }})
                            </span>
                        @elseif($account->is_active)
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 rounded-lg">
                                Active &bull; Live
                            </span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold bg-red-500/10 text-red-300 border border-red-500/30 rounded-lg">
                                Inactive
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 py-1.5 px-2 bg-slate-950/40 rounded-xl border border-white/5 text-[10px] font-mono">
                        <div>
                            <span class="text-slate-500 block">Account Tokens</span>
                            <span class="text-slate-300 font-bold">{{ number_format($account->total_tokens_used ?? 0) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Requests Served</span>
                            <span class="text-slate-300 font-bold">{{ number_format($account->total_requests_count ?? 0) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-white/5 text-[11px]">
                        <span class="text-slate-500 font-mono text-[10px]">
                            Connected {{ $account->created_at->diffForHumans() }}
                        </span>
                        <button
                            type="button"
                            wire:click="disconnectAccount({{ $account->id }})"
                            wire:confirm="Disconnect this Antigravity account?"
                            class="text-red-400/80 hover:text-red-300 text-[11px] font-medium transition-colors cursor-pointer"
                        >
                            Disconnect
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center border-2 border-dashed border-white/10 rounded-2xl text-slate-400 bg-slate-950/40">
                    <div class="text-2xl mb-2">âš¡</div>
                    <p class="text-sm font-semibold text-white">No Antigravity accounts linked yet</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Connect your Google account above to unlock high-throughput Gemini models with automated quota rotation.
                    </p>
                    <div class="mt-4">
                        <x-glass.button 
                            variant="primary" 
                            size="sm" 
                            @click="openOAuthPopup('{{ route('oauth.antigravity.redirect') }}')"
                        >
                            <span>Connect with Google OAuth</span>
                        </x-glass.button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Dynamic Antigravity Catalog & Unified Model Matrix (Exact OmniRoute Matching Layout) -->
    <div class="space-y-5 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2.5">
                    <span>ðŸŽ›ï¸ Dynamic Antigravity Catalog</span>
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
                    Real generation-capable models fetched dynamically from Google API for your signed-in account.
                </p>
            </div>

            <!-- Bulk Diagnostics & Actions Bar -->
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    wire:click="testVisibleModels"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all cursor-pointer disabled:opacity-50"
                    title="Run live probe test on all visible models on this page"
                >
                    <span wire:loading.remove wire:target="testVisibleModels">ðŸ§ª Test Visible Models</span>
                    <span wire:loading wire:target="testVisibleModels" class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                        Testing In Progress...
                    </span>
                </button>

                <!-- Resync from Google API Button -->
                <button
                    type="button"
                    wire:click="resyncModels"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-indigo-500/30 hover:border-indigo-500/60 text-indigo-300 hover:text-white text-xs font-semibold transition-all cursor-pointer disabled:opacity-50"
                    title="Re-synchronize catalog directly from Google Generative Language API"
                >
                    <span wire:loading.remove wire:target="resyncModels">ðŸ”„ Resync Models</span>
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

        <!-- Filter & Search Toolbar (Exact OmniRoute Matching Style) -->
        <x-glass.card variant="subtle" class="p-4 space-y-3 border border-white/10">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <!-- Search Input -->
                <div class="w-full sm:flex-1">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="modelSearch"
                        placeholder="Search models by name or ID (e.g. gemini-1.5-pro, flash, thinking)..."
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-medium"
                    />
                </div>

                <!-- Status Filter Dropdown -->
                <div class="w-full sm:w-64">
                    <select wire:model.live="modelStatusFilter" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500 font-medium">
                        <option value="all">All Models ({{ $totalModelsCount }})</option>
                        <option value="working">ðŸŸ¢ Working Models ({{ $workingCount }})</option>
                        <option value="failed">ðŸ”´ Failed Models ({{ $failedCount }})</option>
                        <option value="free_tier">âš¡ Free Tier ({{ $freeTierCount }})</option>
                        <option value="reasoning">ðŸ§  Reasoning Engines ({{ $reasoningCount }})</option>
                        <option value="vision">ðŸ‘ï¸ Vision Capable ({{ $visionCount }})</option>
                    </select>
                </div>
            </div>

            <!-- Quick Engine & Capability Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                <span class="text-[11px] text-slate-400 mr-1 font-bold">Capabilities:</span>

                <button
                    type="button"
                    wire:click="$set('capabilityFilter', 'all')"
                    class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer {{ $capabilityFilter === 'all' ? 'bg-indigo-600/90 text-white border-indigo-400/50 shadow-sm' : 'bg-slate-900/80 text-slate-400 border-white/10 hover:text-white hover:border-white/20' }}"
                >
                    All Models
                </button>

                <button
                    type="button"
                    wire:click="$set('capabilityFilter', 'reasoning')"
                    class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer {{ $capabilityFilter === 'reasoning' ? 'bg-purple-600/90 text-white border-purple-400/50 shadow-sm' : 'bg-slate-900/80 text-slate-400 border-white/10 hover:text-purple-300 hover:border-purple-500/30' }}"
                >
                    ðŸ§  Reasoning ({{ $reasoningCount }})
                </button>

                <button
                    type="button"
                    wire:click="$set('capabilityFilter', 'vision')"
                    class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer {{ $capabilityFilter === 'vision' ? 'bg-cyan-600/90 text-white border-cyan-400/50 shadow-sm' : 'bg-slate-900/80 text-slate-400 border-white/10 hover:text-cyan-300 hover:border-cyan-500/30' }}"
                >
                    ðŸ‘ï¸ Vision ({{ $visionCount }})
                </button>

                <button
                    type="button"
                    wire:click="$set('capabilityFilter', 'free')"
                    class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer {{ $capabilityFilter === 'free' ? 'bg-emerald-600/90 text-white border-emerald-400/50 shadow-sm' : 'bg-slate-900/80 text-slate-400 border-white/10 hover:text-emerald-300 hover:border-emerald-500/30' }}"
                >
                    âš¡ Free Tier ({{ $freeTierCount }})
                </button>

                <button
                    type="button"
                    wire:click="$set('capabilityFilter', 'streaming')"
                    class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer {{ $capabilityFilter === 'streaming' ? 'bg-indigo-600/90 text-white border-indigo-400/50 shadow-sm' : 'bg-slate-900/80 text-slate-400 border-white/10 hover:text-indigo-300 hover:border-indigo-500/30' }}"
                >
                    âš¡ Streaming
                </button>
            </div>
        </x-glass.card>

<!-- Models Grid (Exact Match to OmniRoute Cards) -->
        @if($hasLinkedAccount)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($models as $m)
                @php
                    $isWorking = $m->last_test_status === 'working';
                    $isFailed = $m->last_test_status === 'failed';
                    $isUntested = ! $isWorking && ! $isFailed;
                    $isCurrentlyTesting = in_array($m->id, $testingModelIds);
                @endphp

                <x-glass.card 
                    variant="subtle" 
                    class="p-4 flex flex-col justify-between gap-3 hover:border-indigo-500/40 hover:shadow-lg hover:shadow-indigo-500/5 transition-all relative group {{ $isWorking ? 'border-emerald-500/20' : ($isFailed ? 'border-red-500/20' : 'border-white/10') }}"
                >
                    <div class="space-y-2.5">
                        <!-- Header & Active Switch -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors truncate" title="{{ $m->name }}">
                                    {{ $m->name }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-mono text-slate-400 truncate max-w-[200px]" title="{{ $m->model_id }}">
                                        {{ $m->model_id }}
                                    </span>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="toggleModel({{ $m->id }})"
                                class="px-2 py-0.5 text-[9px] font-mono font-bold rounded-lg border transition-all cursor-pointer {{ $m->is_active ? 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-white/5' }}"
                                title="Click to toggle model availability"
                            >
                                {{ $m->is_active ? 'ENABLED' : 'DISABLED' }}
                            </button>
                        </div>

                        <!-- Technical Specs Matrix -->
                        <div class="grid grid-cols-3 gap-1.5 py-2 border-y border-white/5 text-[10px] font-mono">
                            <div>
                                <span class="text-slate-500 text-[9px] block uppercase">Context</span>
                                <span class="text-slate-200 font-bold">
                                    {{ number_format($m->context_window ?? 128000) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500 text-[9px] block uppercase">Output</span>
                                <span class="text-slate-200 font-bold">
                                    {{ number_format($m->max_output_tokens ?? 8192) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-500 text-[9px] block uppercase">Used</span>
                                <span class="text-indigo-300 font-bold">
                                    {{ number_format($m->user_tokens_used ?? 0) }}
                                </span>
                            </div>
                        </div>

                        <!-- Capability Badges -->
                        <div class="flex flex-wrap items-center gap-1">
                            @if($m->supports_streaming)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                    âš¡ Stream
                                </span>
                            @endif
                            @if($m->supports_reasoning)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-purple-500/10 text-purple-300 border border-purple-500/20">
                                    ðŸ§  Reasoning
                                </span>
                            @endif
                            @if($m->supports_vision)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                                    ðŸ‘ï¸ Vision
                                </span>
                            @endif
                            @if($m->supports_tools)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                    ðŸ› ï¸ Tools
                                </span>
                            @endif
                            @if($m->supports_json)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                    ðŸ“¦ JSON
                                </span>
                            @endif
                            @if($m->is_free_tier)
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold">
                                    ðŸŽ Free Tier
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Footer: Status Pill & Probe Trigger Button -->
                    <div class="flex items-center justify-between pt-2 border-t border-white/5 text-[11px]">
                        <div>
                            @if($isWorking)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-500/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Working ({{ $m->last_test_latency_ms ?? 35 }}ms)
                                </span>
                            @elseif($isFailed)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-red-950/80 text-red-300 border border-red-500/40" title="{{ $m->last_test_error ?? 'Probe failed' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                    Failed
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono text-slate-400 bg-slate-900 border border-white/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                    Untested
                                </span>
                            @endif
                        </div>

                        <button
                            type="button"
                            wire:click="testModelPing({{ $m->id }})"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-indigo-500/40 text-slate-300 hover:text-white text-[11px] font-semibold transition-all cursor-pointer disabled:opacity-50"
                        >
                            @if($isCurrentlyTesting)
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-ping"></span>
                                <span class="text-indigo-300">Testing...</span>
                            @else
                                <span>ðŸ§ª Test</span>
                            @endif
                        </button>
                    </div>
                </x-glass.card>
            @empty
                <div class="col-span-full p-8 text-center border-2 border-dashed border-white/10 rounded-2xl text-slate-500 bg-slate-900/20">
                    <p class="text-sm">No models discovered yet or matching your current filter.</p>
                    @if($hasLinkedAccount)
                        <button
                            type="button"
                            wire:click="resyncModels"
                            class="mt-3 px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-500 transition-colors shadow-lg cursor-pointer"
                        >
                            ðŸ”„ Resync Models Now
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
@else
        <div class="py-10 text-center text-slate-500 border border-dashed border-white/10 rounded-2xl">
            Please link an Antigravity account to view available models.
        </div>
        @endif

        <!-- Livewire Pagination Links -->
        <div class="pt-4">
            {{ $models->links() }}
        </div>
    </div>

    <!-- Manual / Antigravity CLI Token Linkage Modal -->
    @if($showManualModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-fade-in">
            <div class="w-full max-w-lg p-6 bg-slate-900 border border-white/10 rounded-3xl shadow-2xl space-y-5" @click.outside="$wire.closeManualModal()">
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">ðŸ”‘</span>
                        <div>
                            <h3 class="text-base font-bold text-white">Link Antigravity CLI / OAuth Token</h3>
                            <p class="text-xs text-slate-400">Connect account using gcloud, Antigravity CLI, or Google bearer token</p>
                        </div>
                    </div>
                    <button wire:click="closeManualModal" class="text-slate-400 hover:text-white text-lg">&times;</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Account Email</label>
                        <input 
                            type="email" 
                            wire:model="manualEmail" 
                            placeholder="your.email@gmail.com" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/60 border border-white/10 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                        />
                        @error('manualEmail') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Google OAuth Access Token / Bearer</label>
                        <textarea 
                            wire:model="manualToken" 
                            rows="3" 
                            placeholder="ya29.a0AfH6SM..." 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/60 border border-white/10 text-white text-xs font-mono placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                        ></textarea>
                        <p class="text-[11px] text-slate-500 mt-1">Generated via <code class="text-indigo-300">gcloud auth print-access-token</code> or Antigravity CLI credentials.</p>
                        @error('manualToken') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Refresh Token (Optional)</label>
                        <input 
                            type="text" 
                            wire:model="manualRefreshToken" 
                            placeholder="1//0g..." 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950/60 border border-white/10 text-white text-xs font-mono placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                        />
                        <p class="text-[11px] text-slate-500 mt-1">Used for automatic token renewal after 1 hour.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-white/10">
                    <x-glass.button variant="secondary" size="sm" wire:click="closeManualModal">
                        Cancel
                    </x-glass.button>
                    <x-glass.button variant="primary" size="sm" wire:click="saveManualAccount">
                        <span>Save & Sync Models &rarr;</span>
                    </x-glass.button>
                </div>
            </div>
        </div>
    @endif
</div>


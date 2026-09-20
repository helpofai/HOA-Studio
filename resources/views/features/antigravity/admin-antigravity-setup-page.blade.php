{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Antigravity Setup
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
--}}

<div class="max-w-7xl mx-auto p-6 space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-indigo-500/20">
                ⚡
            </div>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>Antigravity Admin</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-mono bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        GATEWAY FABRIC
                    </span>
                </h1>
                <p class="text-xs text-slate-400 font-mono">Google OAuth Multi-Account AI Infrastructure & Health Governance</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('ai-models.antigravity') }}" class="px-4 py-2 text-xs font-bold font-mono rounded-xl bg-slate-800 text-slate-300 hover:text-white border border-white/10 hover:border-indigo-500/40 transition">
                <span>View User Hub &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Metrics Matrix -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-white/10">
            <div class="text-xs font-mono text-slate-400">Total Registered Models</div>
            <div class="text-2xl font-black text-white mt-1">{{ $modelsCount }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Google Generative Language API</div>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-white/10">
            <div class="text-xs font-mono text-slate-400">Active Google Accounts</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ $activeAccountsCount }} <span class="text-xs font-normal text-slate-500">/ {{ $totalAccountsCount }} total</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Auto-rotating pool</div>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-white/10">
            <div class="text-xs font-mono text-slate-400">Total Tokens Served</div>
            <div class="text-2xl font-black text-indigo-400 mt-1">{{ number_format($totalTokensUsed) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Direct via Antigravity gateway</div>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-white/10">
            <div class="text-xs font-mono text-slate-400">Provider Status</div>
            <div class="text-2xl font-black {{ $provider?->is_active ? 'text-emerald-400' : 'text-amber-400' }} mt-1">
                {{ $provider?->is_active ? 'ACTIVE' : 'INACTIVE' }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Google OAuth Provider</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- OAuth Configuration (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 bg-slate-900/50 border border-white/10 rounded-3xl space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-white mb-1">OAuth 2.0 Credentials</h2>
                    <p class="text-xs text-slate-400 font-mono">Google Cloud project credentials used for end-user authorization</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-mono text-slate-400 mb-1">Google Client ID</label>
                        <input type="text" class="w-full text-xs font-mono bg-slate-950 border border-white/10 rounded-xl px-3 py-2 text-slate-300" value="{{ config('services.antigravity.client_id') ?: config('services.google.client_id') ?: '1071006060591-tmhssin2h21lcre235vtolojh4g403ep.apps.googleusercontent.com' }}" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-slate-400 mb-1">Authorized Redirect URI</label>
                        <input type="text" class="w-full text-xs font-mono bg-slate-950 border border-white/10 rounded-xl px-3 py-2 text-slate-300" value="{{ url('/oauth/antigravity/callback') }}" readonly />
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-300 space-y-1 font-mono">
                    <div class="font-bold">OAuth Scopes Enabled:</div>
                    <div class="text-[11px] text-slate-400">
                        &bull; openid, profile, email<br>
                        &bull; https://www.googleapis.com/auth/generative-language<br>
                        &bull; https://www.googleapis.com/auth/generative-language.retriever
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Governance (1 Col) -->
        <div class="space-y-6">
            <div class="p-6 bg-slate-900/50 border border-white/10 rounded-3xl space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-white mb-1">Health Governance</h2>
                    <p class="text-xs text-slate-400 font-mono">Real-time probe of Antigravity AI Model Fabric</p>
                </div>
                
                <div>
                    <button 
                        wire:click="runHealthProbe"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 transition disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="runHealthProbe">🔄 Run Live Health Probe</span>
                        <span wire:loading wire:target="runHealthProbe">⏳ Probing Google API...</span>
                    </button>
                </div>

                @if($probeResult)
                    <div class="p-4 rounded-2xl border text-xs font-mono space-y-2 {{ ($probeResult['status'] ?? '') === 'healthy' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold uppercase tracking-wider">{{ $probeResult['status'] }}</span>
                            <span>{{ $probeResult['latency_ms'] }}ms</span>
                        </div>
                        <p class="text-[11px] opacity-90">{{ $probeResult['message'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

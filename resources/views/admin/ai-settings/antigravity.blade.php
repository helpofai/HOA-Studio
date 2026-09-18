{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Setup View
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

<div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/80 p-6 rounded-3xl border border-white/10 backdrop-blur-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-950/80 border border-indigo-500/30 flex items-center justify-center text-2xl shadow-inner">
                    🌌
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                        <span>Antigravity Gateway</span>
                        <span class="text-xs px-3 py-1 rounded-full bg-violet-500/20 border border-violet-500/40 text-violet-300 font-mono">Multi-Account Failover Pool</span>
                    </h1>
                    <p class="text-xs text-slate-400">Google OAuth & BYOK direct login router with automated model quota exhaustion fallback.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai-settings.index') }}" wire:navigate class="inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 cursor-pointer active:scale-[0.98] px-3 py-1.5 text-xs gap-1.5 bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 hover:text-white border border-slate-700/50">
                <span>&larr; Back to AI Settings</span>
            </a>
            
            <a href="{{ route('oauth.antigravity.redirect') }}" class="inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 cursor-pointer active:scale-[0.98] px-3 py-1.5 text-xs gap-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white shadow-lg shadow-indigo-500/25 border border-indigo-400/30">
                <span>🔑 Link Google Antigravity Account</span>
            </a>
        </div>
    </div>

    @if (empty(config('services.google.client_id')))
        <div class="p-5 rounded-3xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs space-y-2">
            <div class="flex items-center gap-2 font-bold text-sm text-amber-300">
                <span>⚙️ Administrator Setup Required: Google OAuth 2.0 Credentials</span>
            </div>
            <p>To enable 1-click Google Account authorization for your users, you need to add your Google OAuth client credentials to your <code>.env</code> file:</p>
            <div class="bg-slate-950 p-3 rounded-xl font-mono text-[11px] text-slate-300 border border-white/5 space-y-1">
                <div>GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com</div>
                <div>GOOGLE_CLIENT_SECRET=your-google-client-secret</div>
                <div>GOOGLE_REDIRECT_URL={{ url('/oauth/antigravity/callback') }}</div>
            </div>
            <p class="text-[11px] text-amber-400/80">Authorized Redirect URI in Google Cloud Console: <code>{{ url('/oauth/antigravity/callback') }}</code>. Alternatively, users can add direct API keys using the form below.</p>
        </div>
    @endif

    @if (session()->has('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

        @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-300 text-sm font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- 🔗 MANUAL OAUTH CALLBACK PASTE BOX -->
    <x-glass.card variant="elevated" class="p-5 border-white/10 space-y-3" x-data="{ showManual: false }">
        <button @click="showManual = !showManual" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-2">
            <span>🔗 Popup blocked or callback failed? Use Manual Entry Mode</span>
        </button>
        
        <div x-show="showManual" x-transition class="space-y-3 p-4 bg-slate-950 rounded-2xl border border-white/5">
            <label class="block text-slate-300 font-semibold text-xs mb-1">Paste Full Redirect URL Here</label>
            <form action="{{ route('oauth.antigravity.manual_callback') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="text" name="full_url" required placeholder="Paste the full URL from the popup window starting with https://..." class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-white focus:ring-violet-500 focus:border-violet-500 outline-none">
                <x-glass.button type="submit" variant="primary" size="sm" class="shrink-0">
                    Connect Account
                </x-glass.button>
            </form>
            <p class="text-[10px] text-slate-500">If your browser blocks the Google login popup or fails to redirect back, copy the entire URL from the address bar of the Google login window after granting permissions and paste it here.</p>
        </div>
    </x-glass.card>

    <!-- Active Routing Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Linked Accounts</div>
            <div class="text-3xl font-black text-white">{{ $accounts->count() }}</div>
            <div class="text-xs text-slate-400 mt-1">Multi-account Google failover pool</div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Active Accounts in Pool</div>
            <div class="text-3xl font-black text-emerald-400">{{ $accounts->where('is_active', true)->where('is_quota_exhausted', false)->count() }}</div>
            <div class="text-xs text-emerald-300/80 mt-1">Available for automatic selection</div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-5 border border-white/10">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Current Active Account Token</div>
            <div class="text-xs font-mono text-violet-300 truncate mt-2 bg-slate-950 p-2 rounded-xl border border-white/5">
                {{ $activeToken ? substr($activeToken, 0, 15) . '...' : 'No Active Account Ready' }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Top priority non-exhausted bearer token</div>
        </x-glass.card>
    </div>

    <!-- Accounts Priority Table -->
    <x-glass.card variant="standard" class="p-6 border border-white/10 space-y-6">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                    <span>⚡ Antigravity Accounts Priority & Rotation Chain</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Accounts are picked from top to bottom. If quota is exhausted on Account #1, the engine automatically selects Account #2.</p>
            </div>
        </div>

        @if($accounts->isEmpty())
            <div class="text-center py-12 space-y-4">
                <div class="text-4xl">🌌</div>
                <div class="text-slate-300 font-bold">No Antigravity Accounts Linked Yet</div>
                <p class="text-xs text-slate-400 max-w-md mx-auto">Click "Link Google Antigravity Account" above or add a direct API key below to enable automatic multi-account model quota rotation.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Account Identifier</th>
                            <th class="py-3 px-4">Auth Type</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Quota Availability</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-slate-300">
                        @foreach($accounts as $index => $account)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-violet-400">
                                    #{{ $index + 1 }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-white">{{ $account->email ?? 'Anonymous Key Account' }}</div>
                                    @if($account->google_oauth_id)
                                        <div class="text-[10px] text-slate-500 font-mono">Google ID: {{ $account->google_oauth_id }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-400">
                                    @if($account->google_oauth_id)
                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-300 border border-blue-500/30">Google OAuth 2.0</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-violet-500/10 text-violet-300 border border-violet-500/30">Direct API Key</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <button
                                        type="button"
                                        wire:click="toggleAccountActive({{ $account->id }})"
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase transition-all cursor-pointer {{ $account->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20' }}"
                                    >
                                        {{ $account->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </td>
                                <td class="py-4 px-4">
                                    @if($account->is_quota_exhausted)
                                        <div class="flex items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold">Quota Exhausted</span>
                                            <button
                                                type="button"
                                                wire:click="resetQuotaExhaustion({{ $account->id }})"
                                                class="text-[10px] text-indigo-400 hover:text-indigo-200 underline font-semibold"
                                            >
                                                Reset Cooldown
                                            </button>
                                        </div>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 font-semibold">Quota Ready</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right space-x-1">
                                    <button
                                        type="button"
                                        wire:click="movePriorityUp({{ $account->id }})"
                                        class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs border border-white/10"
                                        title="Move Up in Priority"
                                    >
                                        &uarr;
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="movePriorityDown({{ $account->id }})"
                                        class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs border border-white/10"
                                        title="Move Down in Priority"
                                    >
                                        &darr;
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteAccount({{ $account->id }})"
                                        wire:confirm="Are you sure you want to remove this Antigravity account?"
                                        class="px-2.5 py-1 rounded-lg bg-red-950/60 hover:bg-red-900 text-red-300 text-xs border border-red-500/30 font-semibold"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-glass.card>

    <!-- Manual API Key Secondary Modal / Form -->
    <x-glass.card variant="elevated" class="p-6 border border-white/10 space-y-4">
        <h3 class="text-base font-bold text-white tracking-tight">➕ Add Antigravity Direct API Key Account</h3>
        <p class="text-xs text-slate-400">If you have a direct Antigravity API key or token, you can add it here as a backup rotation target alongside Google OAuth accounts.</p>

        <form wire:submit.prevent="addManualApiKey" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Account Email (Optional)</label>
                <input
                    type="email"
                    wire:model="newAccountEmail"
                    placeholder="user@antigravity.ai"
                    class="w-full px-3 py-2 bg-slate-950 border border-white/10 rounded-xl text-xs text-white placeholder-slate-600 focus:outline-none focus:border-violet-500"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Antigravity Bearer API Key</label>
                <input
                    type="password"
                    wire:model="newApiKey"
                    placeholder="ag-key-..."
                    class="w-full px-3 py-2 bg-slate-950 border border-white/10 rounded-xl text-xs text-white placeholder-slate-600 focus:outline-none focus:border-violet-500"
                />
            </div>

            <div class="flex items-end">
                <x-glass.button type="submit" variant="secondary" class="w-full justify-center text-xs">
                    <span>Add Key Account</span>
                </x-glass.button>
            </div>
        </form>
    </x-glass.card>
</div>

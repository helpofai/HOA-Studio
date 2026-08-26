{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Auth & Security Control Center
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

<div class="hoa-auth-settings-container space-y-6">

    <!-- Header & Flash Messages -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xl">🛡️</span>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Auth & Security Center</h1>
                    <p class="text-xs text-slate-400">Manage user authentication telemetry, brute-force defense, IP blacklists, active sessions, and bot traps.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-glass.button wire:click="openBlockIpModal" variant="secondary" size="sm" class="flex items-center gap-1.5 border-rose-500/30 text-rose-300 hover:bg-rose-500/10">
                <span>🚫</span>
                <span>Block IP Address</span>
            </x-glass.button>

            <x-glass.button wire:click="flushAllRateLimits" variant="secondary" size="sm" class="flex items-center gap-1.5 text-slate-300">
                <span>🧹</span>
                <span>Prune Old Logs</span>
            </x-glass.button>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 flex items-center justify-between shadow-lg shadow-emerald-500/5">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-300 flex items-center justify-between shadow-lg shadow-rose-500/5">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400 hover:text-rose-200">✕</button>
        </div>
    @endif

    <!-- Telemetry Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Total Registered Users -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-violet-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Users</span>
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 text-sm">👥</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-white">{{ number_format($totalUsersCount) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Platform-wide accounts</p>
        </x-glass.card>

        <!-- 2. Currently Online Users -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-emerald-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Online Now</span>
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-400">{{ number_format($onlineUsers->count()) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Active in last 5 minutes</p>
        </x-glass.card>

        <!-- 3. Failed Logins Today -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-amber-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Failed Logins</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 text-sm">⚠️</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-400">{{ number_format($failedLoginsTodayCount) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Today's invalid attempts</p>
        </x-glass.card>

        <!-- 4. Active Blocked IPs -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-rose-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Blocked IPs</span>
                <span class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 text-sm">🚫</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-rose-400">{{ number_format($activeBlockedIpsCount) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Blacklisted networks</p>
        </x-glass.card>

        <!-- 5. Banned Users -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-purple-500/30 transition-all col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Banned Users</span>
                <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400 text-sm">🔒</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-purple-400">{{ number_format($bannedUsersCount) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Deactivated profiles</p>
        </x-glass.card>
    </div>

    <!-- Navigation Tabs Switcher -->
    <div class="flex items-center gap-2 border-b border-white/10 pb-3 overflow-x-auto scrollbar-none">
        <button 
            type="button" 
            wire:click="$set('activeTab', 'overview')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'overview' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            📊 Security Overview
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'security_logs')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'security_logs' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            📑 Live Auth Logs
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'blocked_ips')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'blocked_ips' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            🚫 Blocked IP Blacklist ({{ $activeBlockedIpsCount }})
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'online_users')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'online_users' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            🟢 Live Online Users ({{ $onlineUsers->count() }})
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'banned_users')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'banned_users' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            🔒 Suspended Accounts ({{ $bannedUsersCount }})
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'config')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'config' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            ⚙️ Auth Configurations
        </button>
    </div>

    <!-- TAB 1: OVERVIEW -->
    @if ($activeTab === 'overview')
        <div class="space-y-6">
            <!-- Active Protection Status Matrix -->
            <x-glass.card variant="elevated" class="p-6 border-white/15">
                <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                    <span>🛡️</span>
                    <span>Active Security Shields Status</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- 1. Honeypot Anti-Bot Trap -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Invisible Honeypot</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Active</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">Off-screen bait traps filtering automated scrapers and web spiders with zero user friction.</p>
                        <div class="text-[11px] text-slate-300 pt-1 font-mono">
                            Traps Tripped: <span class="text-indigo-400 font-bold">{{ $honeypotTrapsCount }}</span>
                        </div>
                    </div>

                    <!-- 2. Dual-Layer Rate Limiting -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Brute-Force Throttle</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Active</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">IP rate limiter (10/min) + Account lockout (5 attempts / 5-min cooldown) with auto-block triggers.</p>
                        <div class="text-[11px] text-slate-300 pt-1 font-mono">
                            Auto-Blocks: <span class="text-rose-400 font-bold">{{ $activeBlockedIpsCount }}</span>
                        </div>
                    </div>

                    <!-- 3. Cloudflare Turnstile -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Cloudflare Turnstile</span>
                            @if (!empty($turnstileSiteKey))
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Configured</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">Optional Key Required</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">Privacy-preserving bot verification widget without puzzle challenges.</p>
                        <div class="text-[11px] text-slate-400 pt-1">
                            Status: <span class="text-white">{{ !empty($turnstileSiteKey) ? 'Ready & Protected' : 'Degraded gracefully (Bypassed)' }}</span>
                        </div>
                    </div>
                </div>
            </x-glass.card>

            <!-- Recent Security Events Preview -->
            <x-glass.card variant="elevated" class="p-6 border-white/15">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>⚡</span>
                        <span>Recent Security Telemetry</span>
                    </h3>
                    <button type="button" wire:click="$set('activeTab', 'security_logs')" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                        View All Logs &rarr;
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-white/10">
                            <tr>
                                <th class="pb-3">Timestamp</th>
                                <th class="pb-3">Event Type</th>
                                <th class="pb-3">IP Address</th>
                                <th class="pb-3">Target Email</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($securityLogs->take(5) as $log)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="py-3 text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                                    <td class="py-3">
                                        @if ($log->event_type === 'failed_login')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">Failed Login</span>
                                        @elseif ($log->event_type === 'successful_login')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Success</span>
                                        @elseif ($log->event_type === 'honeypot_triggered')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Bot Honeypot</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">{{ $log->event_type }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-mono text-slate-300">{{ $log->ip_address }}</td>
                                    <td class="py-3 text-slate-300">{{ $log->email ?: 'N/A' }}</td>
                                    <td class="py-3 text-right">
                                        <button 
                                            type="button" 
                                            wire:click="$set('new_block_ip', '{{ $log->ip_address }}'); $set('showBlockIpModal', true)"
                                            class="px-2 py-1 rounded-lg bg-rose-500/10 text-rose-300 hover:bg-rose-500/20 text-[11px] font-semibold transition-colors"
                                        >
                                            Block IP
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-500">No security events recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>
    @endif

    <!-- TAB 2: LIVE SECURITY LOGS -->
    @if ($activeTab === 'security_logs')
        <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-1">
                    <x-glass.input 
                        wire:model.live.debounce.300ms="searchLog"
                        type="text"
                        placeholder="Search IP or Email..."
                        class="w-full sm:max-w-xs text-xs"
                    />

                    <select 
                        wire:model.live="eventFilter"
                        class="bg-slate-900/80 border border-white/15 text-white text-xs rounded-xl px-3 py-2 focus:ring-violet-500 focus:border-violet-500"
                    >
                        <option value="">All Events</option>
                        <option value="failed_login">Failed Logins</option>
                        <option value="successful_login">Successful Logins</option>
                        <option value="honeypot_triggered">Honeypot Traps</option>
                        <option value="blocked_ip_rejected">Blocked IP Rejections</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-white/10">
                        <tr>
                            <th class="pb-3">Timestamp</th>
                            <th class="pb-3">Event Type</th>
                            <th class="pb-3">IP Address</th>
                            <th class="pb-3">Target Email</th>
                            <th class="pb-3">User Agent</th>
                            <th class="pb-3 text-right">Quick Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($securityLogs as $log)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="py-3">
                                    @if ($log->event_type === 'failed_login')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">Failed Login</span>
                                    @elseif ($log->event_type === 'successful_login')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Success</span>
                                    @elseif ($log->event_type === 'honeypot_triggered')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Bot Honeypot</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">{{ $log->event_type }}</span>
                                    @endif
                                </td>
                                <td class="py-3 font-mono text-slate-200">{{ $log->ip_address }}</td>
                                <td class="py-3 text-slate-200">{{ $log->email ?: 'N/A' }}</td>
                                <td class="py-3 text-slate-500 truncate max-w-xs" title="{{ $log->user_agent }}">{{ $log->user_agent ?: 'Unknown' }}</td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        wire:click="$set('new_block_ip', '{{ $log->ip_address }}'); $set('showBlockIpModal', true)"
                                        class="px-2 py-1 rounded-lg bg-rose-500/10 text-rose-300 hover:bg-rose-500/20 text-[11px] font-semibold transition-colors"
                                    >
                                        🚫 Block
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">No logs found matching criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $securityLogs->links() }}
            </div>
        </x-glass.card>
    @endif

    <!-- TAB 3: BLOCKED IP BLACKLIST -->
    @if ($activeTab === 'blocked_ips')
        <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🚫</span>
                        <span>Active IP Address Blacklist</span>
                    </h3>
                    <p class="text-xs text-slate-400">Blocked IPs are immediately rejected from attempting logins or registrations.</p>
                </div>

                <x-glass.button wire:click="openBlockIpModal" variant="primary" size="sm">
                    + Add IP Block
                </x-glass.button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-white/10">
                        <tr>
                            <th class="pb-3">IP Address</th>
                            <th class="pb-3">Reason</th>
                            <th class="pb-3">Blocked By</th>
                            <th class="pb-3">Expires At</th>
                            <th class="pb-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($blockedIps as $block)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 font-mono font-bold text-rose-400">{{ $block->ip_address }}</td>
                                <td class="py-3 text-slate-300">{{ $block->reason ?: 'No reason provided' }}</td>
                                <td class="py-3 text-slate-400">{{ $block->blocked_by }}</td>
                                <td class="py-3 text-slate-400">
                                    {{ $block->blocked_until ? $block->blocked_until->format('Y-m-d H:i') . ' (' . $block->blocked_until->diffForHumans() . ')' : 'Permanent' }}
                                </td>
                                <td class="py-3 text-right">
                                    <button 
                                        type="button" 
                                        wire:click="unblockIp({{ $block->id }})"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20 text-xs font-semibold transition-colors"
                                    >
                                        ✓ Unblock
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No IP addresses currently blocked. Clean network telemetry!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $blockedIps->links() }}
            </div>
        </x-glass.card>
    @endif

    <!-- TAB 4: LIVE ONLINE USERS -->
    @if ($activeTab === 'online_users')
        <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span>🟢</span>
                    <span>Currently Online Users</span>
                </h3>
                <p class="text-xs text-slate-400">Users actively interacting with the workspace within the last 5 minutes.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($onlineUsers as $user)
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 border-2 border-slate-950"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">{{ $user->name }}</h4>
                                <p class="text-[10px] text-slate-400 truncate">{{ $user->email }}</p>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-violet-500/20 text-violet-300 font-semibold uppercase">{{ $user->role }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <a href="{{ route('admin.users') }}?search={{ urlencode($user->email) }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">
                                Manage &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-500">
                        No users currently online.
                    </div>
                @endforelse
            </div>
        </x-glass.card>
    @endif

    <!-- TAB 5: BANNED / SUSPENDED USERS -->
    @if ($activeTab === 'banned_users')
        <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🔒</span>
                        <span>Suspended & Banned Accounts</span>
                    </h3>
                    <p class="text-xs text-slate-400">Suspended users are blocked from logging in and cannot generate AI content.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-white/10">
                        <tr>
                            <th class="pb-3">User</th>
                            <th class="pb-3">Email</th>
                            <th class="pb-3">Role / Plan</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($bannedUsers as $user)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 font-semibold text-white">{{ $user->name }}</td>
                                <td class="py-3 text-slate-400">{{ $user->email }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-800 text-slate-300">{{ $user->plan }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">Banned</span>
                                </td>
                                <td class="py-3 text-right">
                                    <button 
                                        type="button" 
                                        wire:click="toggleUserBan({{ $user->id }})"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20 text-xs font-semibold transition-colors"
                                    >
                                        ✓ Lift Ban (Restore)
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No banned users found. All accounts are in good standing!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bannedUsers->links() }}
            </div>
        </x-glass.card>
    @endif

    <!-- TAB 6: AUTH CONFIGURATIONS -->
    @if ($activeTab === 'config')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Rate Limiting Rules & Manual Controls -->
            <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>⚡</span>
                        <span>Rate Limiting & Throttle Policies</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        ACTIVE DEFENSE
                    </span>
                </div>

                <form wire:submit="saveSecurityConfig" class="space-y-4 text-xs">
                    <!-- 1. Max Login Attempts Per IP -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-bold text-white block">Max Login Attempts (Per IP)</label>
                                <span class="text-slate-400 text-[11px]">Maximum failed attempts allowed per minute before IP rate lock.</span>
                            </div>
                            <div class="w-24">
                                <x-glass.input 
                                    wire:model="maxLoginAttemptsPerIp"
                                    type="number"
                                    min="1"
                                    max="100"
                                    class="text-center font-mono font-bold"
                                    :error="$errors->has('maxLoginAttemptsPerIp')"
                                />
                            </div>
                        </div>
                        @error('maxLoginAttemptsPerIp')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. Account Brute-Force Lockout Attempts & Duration -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 space-y-3">
                        <div>
                            <span class="font-bold text-white block">Account Brute-Force Lockout</span>
                            <span class="text-slate-400 text-[11px]">Lockout threshold and duration when invalid password attempts target a specific email.</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Failed Attempts Limit</label>
                                <x-glass.input 
                                    wire:model="maxAccountAttempts"
                                    type="number"
                                    min="1"
                                    max="50"
                                    placeholder="5"
                                    class="font-mono font-bold"
                                    :error="$errors->has('maxAccountAttempts')"
                                />
                            </div>
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Cooldown Duration (Minutes)</label>
                                <x-glass.input 
                                    wire:model="lockoutDurationMinutes"
                                    type="number"
                                    min="1"
                                    max="1440"
                                    placeholder="5"
                                    class="font-mono font-bold"
                                    :error="$errors->has('lockoutDurationMinutes')"
                                />
                            </div>
                        </div>
                        @error('maxAccountAttempts')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                        @error('lockoutDurationMinutes')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 3. Auto-Blacklist Trigger & Block Duration -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 space-y-3">
                        <div>
                            <span class="font-bold text-white block">Automated IP Blacklist Trigger</span>
                            <span class="text-slate-400 text-[11px]">Automatically blacklist aggressive attacking networks on cumulative failures.</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Failure Hits Threshold</label>
                                <x-glass.input 
                                    wire:model="autoBlockThreshold"
                                    type="number"
                                    min="3"
                                    max="500"
                                    placeholder="15"
                                    class="font-mono font-bold"
                                    :error="$errors->has('autoBlockThreshold')"
                                />
                            </div>
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Auto-Block Duration (Hours)</label>
                                <x-glass.input 
                                    wire:model="autoBlockHours"
                                    type="number"
                                    min="1"
                                    max="8760"
                                    placeholder="24"
                                    class="font-mono font-bold"
                                    :error="$errors->has('autoBlockHours')"
                                />
                            </div>
                        </div>
                        @error('autoBlockThreshold')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                        @error('autoBlockHours')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 4. Registration Anti-Spam Rate Limit -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-bold text-white block">Registration Anti-Spam Rate</label>
                                <span class="text-slate-400 text-[11px]">Max accounts that can be registered per hour from the same IP network.</span>
                            </div>
                            <div class="w-24">
                                <x-glass.input 
                                    wire:model="maxRegistrationsPerHour"
                                    type="number"
                                    min="1"
                                    max="50"
                                    class="text-center font-mono font-bold"
                                    :error="$errors->has('maxRegistrationsPerHour')"
                                />
                            </div>
                        </div>
                        @error('maxRegistrationsPerHour')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500">Applies immediately to all authentication attempts.</span>
                        <x-glass.button type="submit" variant="primary" size="sm" class="shadow-lg shadow-indigo-500/25">
                            <span wire:loading.remove wire:target="saveSecurityConfig">💾 Save Throttle Policies</span>
                            <span wire:loading wire:target="saveSecurityConfig">Saving...</span>
                        </x-glass.button>
                    </div>
                </form>
            </x-glass.card>

            <!-- Bot Defense & Third-Party CAPTCHA Setup (Cloudflare Turnstile) -->
            <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🤖</span>
                        <span>Cloudflare Turnstile & Bot Defense</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $enableTurnstile ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400' }}">
                        {{ $enableTurnstile ? 'ACTIVE' : 'DISABLED' }}
                    </span>
                </div>

                <form wire:submit="saveSecurityConfig" class="space-y-4 text-xs">
                    <!-- Turnstile Enable / Disable Toggle -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white block">Enable Cloudflare Turnstile Challenge</span>
                            <span class="text-slate-400 text-[11px]">Enforces invisible smart CAPTCHA validation on Login and Registration forms.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="enableTurnstile" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <!-- Site Key Input -->
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Cloudflare Turnstile Site Key (Public Key)</label>
                        <x-glass.input 
                            wire:model="turnstileSiteKey"
                            type="text"
                            placeholder="0x4AAAAAA..."
                            :error="$errors->has('turnstileSiteKey')"
                        />
                        @error('turnstileSiteKey')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Secret Key Input -->
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Cloudflare Turnstile Secret Key (Private Key)</label>
                        <x-glass.input 
                            wire:model="turnstileSecretKey"
                            type="password"
                            placeholder="0x4AAAAAA..."
                            :error="$errors->has('turnstileSecretKey')"
                        />
                        @error('turnstileSecretKey')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Honeypot Global Setting Toggle -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-white block">Invisible Form Honeypot</span>
                            <span class="text-slate-400 text-[11px]">Injects hidden off-screen fields to trap automated crawlers and spammers.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="enableHoneypot" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500">Settings are instantly synchronized to the database.</span>
                        <x-glass.button type="submit" variant="primary" size="sm" class="shadow-lg shadow-violet-500/25">
                            <span wire:loading.remove wire:target="saveSecurityConfig">💾 Save Turnstile Settings</span>
                            <span wire:loading wire:target="saveSecurityConfig">Saving...</span>
                        </x-glass.button>
                    </div>
                </form>
            </x-glass.card>
        </div>
    @endif

    <!-- Manual IP Block Modal -->
    @if ($showBlockIpModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xl">
            <div class="w-full max-w-md bg-slate-900 border border-white/20 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-white/10">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🚫</span>
                        <span>Block IP Address</span>
                    </h3>
                    <button type="button" wire:click="$set('showBlockIpModal', false)" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">IP Address to Block</label>
                        <x-glass.input 
                            wire:model="new_block_ip"
                            type="text"
                            placeholder="e.g. 192.168.1.1 or 203.0.113.195"
                            :error="$errors->has('new_block_ip')"
                        />
                        @error('new_block_ip')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Block Duration</label>
                        <select 
                            wire:model="new_block_duration"
                            class="w-full bg-slate-950 border border-white/15 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-violet-500 focus:border-violet-500"
                        >
                            <option value="1_hour">1 Hour</option>
                            <option value="24_hours">24 Hours (Standard)</option>
                            <option value="7_days">7 Days</option>
                            <option value="permanent">Permanent (Until manual removal)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Reason for Block (Optional)</label>
                        <x-glass.input 
                            wire:model="new_block_reason"
                            type="text"
                            placeholder="e.g. Suspicious brute force attack"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/10">
                    <x-glass.button wire:click="$set('showBlockIpModal', false)" variant="secondary" size="sm">
                        Cancel
                    </x-glass.button>
                    <x-glass.button wire:click="blockIp" variant="primary" size="sm" class="bg-rose-600 hover:bg-rose-500 border-rose-500/50">
                        Confirm Block
                    </x-glass.button>
                </div>
            </div>
        </div>
    @endif

</div>

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
                <span>System Overview</span>
                <x-glass.badge variant="violet">v13.26.1</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Platform analytics, user quota tracking, and OmniRoute gateway metrics.</p>
        </div>

        <!-- Gateway Health Indicator -->
        <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
            <div class="w-3 h-3 rounded-full {{ $stats['gateway_online'] ? 'bg-emerald-400 shadow-[0_0_8px_#34d399]' : 'bg-red-500 shadow-[0_0_8px_#ef4444]' }}"></div>
            <div class="text-xs">
                <span class="font-semibold text-white">OmniRoute Gateway:</span>
                <span class="{{ $stats['gateway_online'] ? 'text-emerald-400' : 'text-red-400' }} font-bold ml-1">
                    {{ $stats['gateway_online'] ? 'ONLINE (' . $stats['gateway_latency'] . 'ms)' : 'STANDALONE MODE' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <x-glass.card variant="elevated" class="p-6 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Registered Users</span>
                <span class="text-lg">👥</span>
            </div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['total_users']) }}</div>
            <div class="flex items-center gap-2 text-[11px] text-violet-400 mt-2">
                <span>{{ $stats['pro_users'] }} Pro</span> &bull; <span>{{ $stats['admin_users'] }} Admin</span>
            </div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-6 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Documents Created</span>
                <span class="text-lg">📄</span>
            </div>
            <div class="text-3xl font-black text-indigo-400">{{ number_format($stats['total_documents']) }}</div>
            <div class="text-[11px] text-slate-400 mt-2">{{ number_format($stats['total_words_written']) }} words stored</div>
        </x-glass.card>

        <x-glass.card variant="elevated" class="p-6 border border-white/10">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">AI Words Consumed</span>
                <span class="text-lg">✍️</span>
            </div>
            <div class="text-3xl font-black text-purple-400">{{ number_format($stats['total_words_consumed']) }}</div>
            <div class="text-[11px] text-purple-300 mt-2">{{ number_format($stats['total_tokens_consumed']) }} raw tokens</div>
        </x-glass.card>

        <x-glass.card variant="premium" glow="violet" class="p-6">
            <div class="flex items-center justify-between text-slate-300 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">AI Operations Executed</span>
                <span class="text-lg">⚡</span>
            </div>
            <div class="text-3xl font-black text-white">{{ number_format($stats['total_generations']) }}</div>
            <div class="text-[11px] text-indigo-300 mt-2">Across all models & combos</div>
        </x-glass.card>
    </div>

    <!-- Live System-Wide AI Telemetry & Inference Graph -->
    <x-omniroute.telemetry-graph 
        :graphData="$graphData" 
        :timeRange="$graphTimeRange" 
        :statusFilter="$graphStatusFilter" 
    />

    <!-- 2 Column Section: Recent Users & Gateway Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Users Table -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white tracking-tight">Recent User Registrations</h3>
                <a href="{{ route('admin.users') }}" class="text-xs text-violet-400 hover:text-violet-300">View All &rarr;</a>
            </div>

            <x-glass.card variant="standard" class="p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900/80 text-slate-400 border-b border-white/5 uppercase text-[10px]">
                            <tr>
                                <th class="p-4">User</th>
                                <th class="p-4">Role</th>
                                <th class="p-4">Plan</th>
                                <th class="p-4">Quota Used</th>
                                <th class="p-4">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-200">
                            @foreach($stats['recent_users'] as $user)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="p-4 font-medium text-white">
                                        <div>{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                    </td>
                                    <td class="p-4">
                                        <x-glass.badge :variant="match($user->role) { 'admin' => 'violet', 'editor' => 'cyan', 'pro' => 'amber', default => 'emerald' }">
                                            {{ ucfirst($user->role) }}
                                        </x-glass.badge>
                                    </td>
                                    <td class="p-4 uppercase font-mono text-[11px] text-indigo-300">{{ $user->plan }}</td>
                                    <td class="p-4 font-mono">{{ number_format($user->used_word_quota) }} / {{ number_format($user->monthly_word_quota) }}</td>
                                    <td class="p-4 text-slate-400">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>

        <!-- Gateway Info Card -->
        <div class="space-y-4">
            <h3 class="text-base font-bold text-white tracking-tight">Gateway Diagnostics</h3>

            <x-glass.card variant="standard" class="p-6 space-y-4 text-xs">
                <div>
                    <div class="text-slate-400 mb-1">Gateway Endpoint</div>
                    <div class="font-mono text-white bg-slate-900 p-2 rounded-lg border border-white/5 truncate">
                        {{ config('omniroute.base_url', 'http://127.0.0.1:20128') }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-400 mb-1">Default Model</div>
                    <div class="font-mono text-indigo-300 bg-slate-900 p-2 rounded-lg border border-white/5 truncate">
                        {{ config('omniroute.default_model') }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-400 mb-1">Context Compression</div>
                    <div class="font-mono text-emerald-300 bg-slate-900 p-2 rounded-lg border border-white/5 truncate">
                        {{ config('omniroute.compression', 'default') }} (Caveman / RTK enabled)
                    </div>
                </div>

                <div class="pt-3 border-t border-white/5 flex items-center justify-between">
                    <span class="text-slate-400">Architecture Mode:</span>
                    <span class="text-violet-400 font-semibold">Decoupled OmniRoute Proxy</span>
                </div>
            </x-glass.card>
        </div>
    </div>
</div>
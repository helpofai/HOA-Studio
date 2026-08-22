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

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">AI Generation Audit Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Live record of words, tokens, and model routing per user request.</p>
        </div>
    </div>

    <!-- Platform-Wide Multi-Color Telemetry Graph -->
    <x-omniroute.telemetry-graph 
        :graphData="$graphData" 
        :timeRange="$graphTimeRange" 
        :statusFilter="$graphStatusFilter" 
    />

    <!-- Filters -->
    <x-glass.card variant="subtle" class="p-4 flex flex-col sm:flex-row items-center gap-3">
        <div class="w-full sm:flex-1">
            <x-glass.input wire:model.live.debounce.300ms="search" placeholder="Search by user or model slug..." />
        </div>

        <div class="w-full sm:w-64">
            <select wire:model.live="selectedModel" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                <option value="">All OmniRoute Models</option>
                @foreach($models as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
    </x-glass.card>

    <!-- Logs Table -->
    <x-glass.card variant="standard" class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/80 text-slate-400 border-b border-white/5 uppercase text-[10px]">
                    <tr>
                        <th class="p-4">User</th>
                        <th class="p-4">Model Routed</th>
                        <th class="p-4">Words Deducted</th>
                        <th class="p-4">Raw Tokens</th>
                        <th class="p-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="p-4 font-medium text-white">
                                <div>{{ $log->user_name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->user_email }}</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-indigo-300 font-mono text-[11px] border border-white/5">
                                    {{ $log->model_slug }}
                                </span>
                            </td>
                            <td class="p-4 font-mono font-bold text-white">{{ number_format($log->words_used) }} words</td>
                            <td class="p-4 font-mono text-slate-400">{{ number_format($log->tokens_used) }} tokens</td>
                            <td class="p-4 text-slate-400 font-mono text-[11px]">{{ \Carbon\Carbon::parse($log->recorded_at)->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">No generation logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-white/5">
                {{ $logs->links() }}
            </div>
        @endif
    </x-glass.card>
</div>
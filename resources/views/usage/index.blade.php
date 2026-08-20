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

<div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>⚡ Quota & Token Accounting</span>
                @if($quota['status'] === 'exhausted')
                    <x-glass.badge variant="rose">Quota Exhausted</x-glass.badge>
                @elseif($quota['status'] === 'warning')
                    <x-glass.badge variant="amber">80%+ Used</x-glass.badge>
                @else
                    <x-glass.badge variant="emerald">Healthy Quota</x-glass.badge>
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Real-time accounting of monthly word consumption, token usage, model distribution, and estimated cost savings.
            </p>
        </div>
    </div>

    <!-- Top Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Monthly Word Quota Usage -->
        <x-glass.card variant="elevated" class="p-5 space-y-3">
            <div class="flex items-center justify-between text-xs font-mono text-slate-400">
                <span>Monthly Quota</span>
                <span class="{{ $quota['percentage_used'] >= 90 ? 'text-red-400 font-bold' : ($quota['percentage_used'] >= 75 ? 'text-yellow-400 font-bold' : 'text-emerald-400 font-bold') }}">
                    {{ $quota['percentage_used'] }}%
                </span>
            </div>

            <div>
                <div class="text-2xl font-black text-white font-mono">
                    {{ number_format($quota['used_words']) }}
                    <span class="text-xs text-slate-400 font-normal">/ {{ number_format($quota['monthly_limit']) }} words</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full h-2 bg-slate-900 rounded-full overflow-hidden border border-white/5">
                <div 
                    class="h-full transition-all duration-500 rounded-full {{ $quota['percentage_used'] >= 90 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : ($quota['percentage_used'] >= 75 ? 'bg-gradient-to-r from-violet-500 to-amber-500' : 'bg-gradient-to-r from-cyan-500 to-emerald-400') }}"
                    style="width: {{ min(100, $quota['percentage_used']) }}%"
                ></div>
            </div>

            <div class="text-[11px] text-slate-400 font-mono flex items-center justify-between">
                <span>Remaining: <strong class="text-white">{{ number_format($quota['remaining_words']) }}</strong> words</span>
            </div>
        </x-glass.card>

        <!-- Total Processed Tokens -->
        <x-glass.card variant="elevated" class="p-5 space-y-2">
            <div class="text-xs font-mono text-slate-400">Total Tokens Processed</div>
            <div class="text-2xl font-black text-cyan-300 font-mono">
                {{ number_format($summary['total_tokens']) }}
            </div>
            <div class="text-[11px] text-slate-400 font-mono">
                Across <strong class="text-white">{{ number_format($summary['total_generations']) }}</strong> AI completions
            </div>
        </x-glass.card>

        <!-- Estimated API Cost -->
        <x-glass.card variant="elevated" class="p-5 space-y-2">
            <div class="text-xs font-mono text-slate-400">Estimated API Cost</div>
            <div class="text-2xl font-black text-white font-mono">
                ${{ number_format($summary['total_cost_usd'], 4) }}
            </div>
            <div class="text-[11px] text-slate-400 font-mono">
                Direct model provider equivalent
            </div>
        </x-glass.card>

        <!-- OmniRoute Efficiency Savings -->
        <x-glass.card variant="elevated" class="p-5 space-y-2 border-emerald-500/30 bg-emerald-950/20">
            <div class="text-xs font-mono text-emerald-300">Cost Savings (vs GPT-4o)</div>
            <div class="text-2xl font-black text-emerald-400 font-mono">
                +${{ number_format($summary['total_savings_usd'], 4) }}
            </div>
            <div class="text-[11px] text-emerald-300/80 font-mono">
                Saved via smart multi-model routing
            </div>
        </x-glass.card>
    </div>

    <!-- Model Usage Distribution Breakdown -->
    <x-glass.card variant="elevated" class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-white tracking-tight">AI Model Consumption Breakdown</h3>
            <span class="text-xs text-slate-400 font-mono">{{ count($modelBreakdown) }} models active</span>
        </div>

        @if(!empty($modelBreakdown))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($modelBreakdown as $m)
                    @php
                        $mWordPct = $summary['total_words'] > 0 ? round(($m['words'] / $summary['total_words']) * 100, 1) : 0;
                    @endphp
                    <div class="p-4 rounded-xl bg-slate-900/80 border border-white/10 space-y-2 text-xs font-mono">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white truncate max-w-[180px]">{{ $m['model'] }}</span>
                            <span class="text-indigo-300 font-bold">{{ $mWordPct }}%</span>
                        </div>

                        <div class="w-full h-1.5 bg-[#0d1117] rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $mWordPct }}%"></div>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                            <span>{{ number_format($m['words']) }} words</span>
                            <span>{{ number_format($m['tokens']) }} tokens</span>
                            <span class="text-slate-300 font-bold">${{ number_format($m['cost'], 4) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-xs text-slate-500">
                No completions recorded yet. Write in documents or execute templates to see model analytics.
            </div>
        @endif
    </x-glass.card>

    <!-- Recent Generation Audit Log Table -->
    <x-glass.card variant="elevated" class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-white tracking-tight">Recent Completion Audit Trail</h3>
            <span class="text-xs text-slate-400 font-mono">Last 15 requests</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="text-slate-400 border-b border-white/10 text-[11px] uppercase tracking-wider">
                        <th class="pb-3 pr-4">Timestamp</th>
                        <th class="pb-3 px-4">Model Engine</th>
                        <th class="pb-3 px-4 text-right">Words</th>
                        <th class="pb-3 px-4 text-right">Tokens</th>
                        <th class="pb-3 pl-4 text-right">Estimated Cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($recentLogs as $log)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="py-3 pr-4 text-slate-400 whitespace-nowrap">{{ $log['recorded_at'] }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-[11px]">
                                    {{ $log['model'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right text-white font-bold">{{ number_format($log['words']) }}</td>
                            <td class="py-3 px-4 text-right text-slate-400">{{ number_format($log['tokens']) }}</td>
                            <td class="py-3 pl-4 text-right text-emerald-400 font-bold">${{ number_format($log['cost_usd'], 5) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">
                                No generation logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-glass.card>
</div>
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

<div class="space-y-8" wire:init="loadDashboard">
    <!-- Welcome Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Welcome back, {{ auth()->user()->name }}! 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Your AI content engine is ready. Create, expand, and optimize articles in seconds.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('documents.index') }}" wire:navigate class="relative group" x-data="{ navigating: false }" @click="navigating = true">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-violet-600 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-70 transition duration-500"></div>
                <button type="button" class="relative w-full sm:w-auto py-2.5 px-6 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-sm shadow-md shadow-violet-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer !border-0">
                    <span x-show="navigating" class="orbit" style="display: none;"></span>
                    <span x-show="!navigating">✨</span>
                    <span x-text="navigating ? 'Opening...' : 'Create Document'"></span>
                </button>
            </a>
        </div>
    </div>

    <!-- 4 Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <x-glass.card variant="standard" class="p-6">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Documents</span>
                <span class="text-lg">📄</span>
            </div>
            @if(!$readyToLoad)
                <div class="h-9 w-24 bg-white/10 rounded-lg animate-pulse my-0.5"></div>
                <div class="h-3.5 w-32 bg-white/5 rounded animate-pulse mt-2"></div>
            @else
                <div class="text-3xl font-black text-white">{{ number_format($stats['total_documents']) }}</div>
                <div class="text-[11px] text-emerald-400 mt-2">Active in Workspace</div>
            @endif
        </x-glass.card>

        <x-glass.card variant="standard" class="p-6">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Words Written</span>
                <span class="text-lg">✍️</span>
            </div>
            @if(!$readyToLoad)
                <div class="h-9 w-28 bg-white/10 rounded-lg animate-pulse my-0.5"></div>
                <div class="h-3.5 w-28 bg-white/5 rounded animate-pulse mt-2"></div>
            @else
                <div class="text-3xl font-black text-indigo-400">{{ number_format($stats['total_words']) }}</div>
                <div class="text-[11px] text-slate-400 mt-2">Across all drafts</div>
            @endif
        </x-glass.card>

        <x-glass.card variant="standard" class="p-6">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Active Projects</span>
                <span class="text-lg">📁</span>
            </div>
            @if(!$readyToLoad)
                <div class="h-9 w-20 bg-white/10 rounded-lg animate-pulse my-0.5"></div>
                <div class="h-3.5 w-36 bg-white/5 rounded animate-pulse mt-2"></div>
            @else
                <div class="text-3xl font-black text-purple-400">{{ number_format($stats['total_projects']) }}</div>
                <div class="text-[11px] text-slate-400 mt-2">Workspaces configured</div>
            @endif
        </x-glass.card>

        <x-glass.card variant="premium" glow="violet" class="p-6">
            <div class="flex items-center justify-between text-slate-300 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">AI Word Balance</span>
                <span class="text-lg">⚡</span>
            </div>
            @if(!$readyToLoad)
                <div class="h-9 w-32 bg-white/10 rounded-lg animate-pulse my-0.5"></div>
                <div class="h-3.5 w-40 bg-white/5 rounded animate-pulse mt-2"></div>
            @else
                <div class="text-3xl font-black text-white">{{ number_format($stats['remaining_quota']) }}</div>
                <div class="text-[11px] text-indigo-300 mt-2">{{ number_format($stats['used_quota']) }} used this cycle</div>
            @endif
        </x-glass.card>
    </div>

    <!-- User Live AI Inference Telemetry Graph -->
    <x-omniroute.telemetry-graph 
        :graphData="$graphData" 
        :timeRange="$graphTimeRange" 
        :statusFilter="$graphStatusFilter" 
    />

    <!-- Multi-Agent Swarm Activity Monitor -->
    <x-ai.agent-activity-monitor />

    <!-- 2 Column Section: Recent Documents & Quick AI Tools -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Documents (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white tracking-tight">Recent Documents</h3>
                <a href="{{ route('documents.index') }}" wire:navigate class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                    View All &rarr;
                </a>
            </div>

            @if(!$readyToLoad)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-glass.skeleton type="card" />
                    <x-glass.skeleton type="card" />
                </div>
            @elseif($stats['recent_documents']->isEmpty())
                <x-glass.card variant="subtle" class="p-8 text-center">
                    <div class="text-3xl mb-3">📝</div>
                    <h4 class="text-sm font-semibold text-white mb-1">No documents created yet</h4>
                    <p class="text-xs text-slate-400 mb-4 max-w-sm mx-auto">Get started with your first long-form article or AI prompt generation.</p>
                    <a href="{{ route('documents.index') }}" wire:navigate>
                        <x-glass.button variant="primary" size="sm">Create First Document</x-glass.button>
                    </a>
                </x-glass.card>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($stats['recent_documents'] as $doc)
                        <a href="{{ route('documents.editor', $doc->id) }}" wire:navigate class="block group">
                            <x-glass.card variant="standard" class="p-5 hover:border-indigo-500/40 hover:-translate-y-0.5 transition-all">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="text-sm font-bold text-white truncate group-hover:text-indigo-300 transition-colors">{{ $doc->title }}</h4>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 uppercase font-mono">{{ $doc->status }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-400 mt-3 pt-3 border-t border-white/5">
                                    <span>{{ number_format($doc->word_count) }} words</span>
                                    <span>&bull;</span>
                                    <span>{{ $doc->reading_time_minutes }}m read</span>
                                    @if($doc->project)
                                        <span>&bull;</span>
                                        <span class="text-indigo-400 truncate">{{ $doc->project->name }}</span>
                                    @endif
                                </div>
                            </x-glass.card>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Activity & Version History (1 Col) -->
        <div class="space-y-4">
            <h3 class="text-base font-bold text-white tracking-tight">Recent AI Activity</h3>

            <x-glass.card variant="standard" class="p-5 space-y-4">
                @if($stats['recent_versions']->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-4">No recent edits or snapshots recorded.</p>
                @else
                    <div class="space-y-3">
                        @foreach($stats['recent_versions'] as $ver)
                            <div class="flex items-start gap-3 text-xs pb-3 border-b border-white/5 last:border-0 last:pb-0">
                                <div class="w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    v{{ $ver->version_number }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-white truncate">{{ $ver->title }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $ver->summary ?? 'Saved version' }} &bull; {{ $ver->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-glass.card>
        </div>
    </div>
</div>
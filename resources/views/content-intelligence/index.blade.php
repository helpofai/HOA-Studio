{{--
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Intelligence Workspace
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
--}}

<div class="hoa-content-intelligence-workspace space-y-6 pb-16" x-data="{ activeTab: @entangle('inspectorTab') }" x-on:trigger-next-ci-step.window="setTimeout(() => { if ($wire.isAutoRunning) { $wire.stepWorkflow($event.detail.runId); } }, 350)">
    <!-- 1. Header with Quick Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/5">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2.5">
                    <span>🧠</span>
                    <span>Content Intelligence</span>
                </h1>
                <x-glass.badge variant="violet">Dynamic Workflow Graph</x-glass.badge>
                <x-glass.badge variant="emerald">10-Node Synapse Engine</x-glass.badge>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1.5 max-w-3xl leading-relaxed">
                Deterministic multi-stage AI research architecture. Generates structured knowledge triples, fact-verified claim graphs, adaptive outlines, self-correcting critic evaluations, and publish-ready TipTap documents.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-900/80 border border-white/10 text-xs shadow-inner">
                <span class="text-slate-400 font-medium text-[11px]">⚡ Engine:</span>
                <select wire:model.live="selectedAiModel" class="bg-transparent text-violet-300 font-medium text-xs focus:outline-none cursor-pointer max-w-[140px] sm:max-w-[180px] truncate pr-1">
                    @if(isset($aiModels) && count($aiModels) > 0)
                        @foreach($aiModels as $m)
                            <option value="{{ $m->model_id }}" class="bg-slate-900 text-slate-200">{{ $m->name }}</option>
                        @endforeach
                    @else
                        <option value="auto" class="bg-slate-900 text-slate-200">Auto-Route</option>
                    @endif
                </select>
            </div>

            <x-glass.button variant="primary" size="sm" wire:click="openCreateModal" class="!py-1.5 !px-3.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>New Mission</span>
            </x-glass.button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if ($statusMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/70 border border-emerald-500/40 text-emerald-200 text-sm flex items-center justify-between shadow-xl backdrop-blur-md animate-fade-in">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-300 text-base">✓</span>
                <span class="font-medium">{{ $statusMessage }}</span>
            </div>
            <button wire:click="$set('statusMessage', '')" class="w-7 h-7 rounded-lg hover:bg-emerald-900/50 flex items-center justify-center text-emerald-400 hover:text-white text-xs cursor-pointer transition-colors">✕</button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-950/70 border border-rose-500/40 text-rose-200 text-sm flex items-center justify-between shadow-xl backdrop-blur-md animate-fade-in">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-rose-500/20 border border-rose-500/40 flex items-center justify-center text-rose-300 text-base">⚠</span>
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', '')" class="w-7 h-7 rounded-lg hover:bg-rose-900/50 flex items-center justify-center text-rose-400 hover:text-white text-xs cursor-pointer transition-colors">✕</button>
        </div>
    @endif

    <!-- 2. Metric Cards Matrix -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-white/10 shadow-lg backdrop-blur-xl hover:border-violet-500/30 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Missions</span>
                <span class="p-2 rounded-xl bg-violet-500/10 text-violet-400 text-sm">🎯</span>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($stats['total_missions']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Configured strategy briefs</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-white/10 shadow-lg backdrop-blur-xl hover:border-indigo-500/30 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Active Pipelines</span>
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 text-sm">⚡</span>
            </div>
            <div class="text-3xl font-extrabold text-indigo-300 tracking-tight">{{ number_format($stats['active_runs']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Runs in execution queue</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-white/10 shadow-lg backdrop-blur-xl hover:border-emerald-500/30 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Assembled Articles</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 text-sm">✍️</span>
            </div>
            <div class="text-3xl font-extrabold text-emerald-300 tracking-tight">{{ number_format($stats['completed_runs']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Compiled TipTap documents</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/80 border border-white/10 shadow-lg backdrop-blur-xl hover:border-purple-500/30 transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-purple-400 uppercase tracking-wider">Avg Confidence</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400 text-sm">💎</span>
            </div>
            <div class="text-3xl font-extrabold text-purple-300 tracking-tight">{{ $stats['average_confidence'] }}%</div>
            <div class="text-[11px] text-slate-400 mt-1">Multi-rubric validation score</div>
        </div>
    </div>

    <!-- 3. Interactive Cyber Pipeline Stepper (Visualized for Selected Run) -->
    @if ($selectedRun)
        <div class="p-6 rounded-3xl bg-slate-900/90 border border-violet-500/40 shadow-2xl backdrop-blur-xl relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6 pb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-xl bg-violet-600/20 text-violet-300 text-lg border border-violet-500/30">🧬</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-white">Live Pipeline Synapse Graph</h2>
                            <span class="font-mono text-xs px-2 py-0.5 rounded-md bg-violet-500/20 text-violet-300">Run #{{ $selectedRun->id }}</span>
                        </div>
                        <p class="text-xs text-slate-400 truncate max-w-xl">{{ $selectedRun->mission->topic }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if ($selectedRun->status->value !== 'completed')
                        <button
                            wire:click="stepWorkflow({{ $selectedRun->id }})"
                            wire:loading.attr="disabled"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer disabled:opacity-50 transition-all hover:scale-[1.02]"
                        >
                            <span>Step Stage</span>
                            <span wire:loading.remove wire:target="stepWorkflow({{ $selectedRun->id }})">⏭</span>
                            <span wire:loading wire:target="stepWorkflow({{ $selectedRun->id }})" class="animate-spin">⟳</span>
                        </button>

                        @if ($isAutoRunning)
                            <button
                                wire:click="stopAutoRun"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white flex items-center gap-1.5 shadow-lg shadow-amber-600/30 cursor-pointer transition-all hover:scale-[1.02] animate-pulse"
                            >
                                <span>⏸ Pause Auto-Run</span>
                            </button>
                        @else
                            <button
                                wire:click="startAutoRun({{ $selectedRun->id }})"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white flex items-center gap-1.5 shadow-lg shadow-violet-600/20 cursor-pointer disabled:opacity-50 transition-all hover:scale-[1.02]"
                            >
                                <span>⚡ Auto-Run Step-by-Step</span>
                                <span wire:loading.remove wire:target="startAutoRun({{ $selectedRun->id }})">➔</span>
                                <span wire:loading wire:target="startAutoRun({{ $selectedRun->id }})" class="animate-spin">⟳</span>
                            </button>
                        @endif
                    @else
                        <a
                            href="{{ route('documents.editor', $selectedRun->document_id) }}"
                            wire:navigate
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 text-white flex items-center gap-1.5 shadow-lg shadow-emerald-600/20"
                        >
                            <span>✍️ Open in TipTap Editor</span>
                            <span>➔</span>
                        </a>
                    @endif

                    <button
                        wire:click="selectRun(null)"
                        class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white text-xs cursor-pointer"
                        title="Close Inspector"
                    >✕</button>
                </div>
            </div>

            <!-- Horizontal Synapse Node Pipeline Track -->
            @php
                $orderedNodes = [
                    'mission_intake' => ['title' => 'Intake', 'icon' => '🎯'],
                    'search_intelligence' => ['title' => 'Search Intel', 'icon' => '🔍'],
                    'research_director' => ['title' => 'Director', 'icon' => '🧭'],
                    'knowledge_fabric' => ['title' => 'Claims & Triples', 'icon' => '🧬'],
                    'content_blueprint' => ['title' => 'Blueprint', 'icon' => '📐'],
                    'adaptive_outline' => ['title' => 'Outline', 'icon' => '📑'],
                    'section_draftsman' => ['title' => 'Draftsman', 'icon' => '✍️'],
                    'seo_optimization' => ['title' => 'SEO & Schema', 'icon' => '🎯'],
                    'media_enhancement' => ['title' => 'Rich Media', 'icon' => '🖼️'],
                    'master_assembly' => ['title' => 'TipTap Assembly', 'icon' => '🚀'],
                ];
                $recordsMap = $selectedRun->nodes->keyBy('node_name');
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-2 relative">
                @foreach ($orderedNodes as $nodeKey => $nodeMeta)
                    @php
                        $rec = $recordsMap->get($nodeKey);
                        $isCurrent = ($selectedRun->current_node === $nodeKey && $selectedRun->status->value !== 'completed');
                        $isDone = ($rec && $rec->status === 'success');
                    @endphp
                    <div
                        class="p-3 rounded-2xl flex flex-col items-center text-center transition-all relative border {{ $isCurrent ? 'bg-gradient-to-b from-indigo-950/80 to-violet-950/80 border-indigo-400 shadow-xl shadow-indigo-500/20' : ($isDone ? 'bg-slate-950/80 border-emerald-500/30' : 'bg-slate-950/40 border-white/5 opacity-60') }}"
                    >
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm mb-1.5 {{ $isDone ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($isCurrent ? 'bg-indigo-500/30 text-indigo-300 border border-indigo-400 animate-pulse' : 'bg-white/5 text-slate-400') }}">
                            @if ($isDone)
                                <span>✓</span>
                            @elseif ($isCurrent)
                                <span class="animate-spin text-xs">⟳</span>
                            @else
                                <span>{{ $nodeMeta['icon'] }}</span>
                            @endif
                        </div>

                        <div class="text-[11px] font-bold truncate w-full {{ $isCurrent ? 'text-indigo-300' : ($isDone ? 'text-white' : 'text-slate-400') }}">
                            {{ $nodeMeta['title'] }}
                        </div>

                        <div class="text-[10px] mt-1 font-mono">
                            @if ($rec)
                                <span class="text-emerald-400 font-semibold">{{ $rec->latency_ms }}ms</span>
                            @elseif ($isCurrent)
                                <span class="text-indigo-400 font-semibold animate-pulse">Running</span>
                            @else
                                <span class="text-slate-600">Pending</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 4. Runs Pipeline Table & Deep Multi-Tab Inspector Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Runs List (Left 6 Cols or 12 Cols if none selected) -->
        <div class="{{ $selectedRun ? 'lg:col-span-6' : 'lg:col-span-12' }} space-y-4">
            <div class="p-5 rounded-3xl bg-slate-900/80 border border-white/10 shadow-2xl backdrop-blur-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-violet-400"></span>
                        <h2 class="text-base font-bold text-white tracking-tight">Active & Historical Pipeline Runs</h2>
                    </div>

                    <!-- Filter buttons & Search -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search missions..."
                                class="w-40 sm:w-48 pl-7 pr-2.5 py-1 rounded-xl bg-slate-950 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                            />
                            <span class="absolute left-2.5 top-1.5 text-slate-500 text-xs">🔍</span>
                        </div>

                        <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-white/10 text-xs">
                            <button
                                wire:click="$set('filterStatus', 'all')"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $filterStatus === 'all' ? 'bg-violet-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white' }}"
                            >All</button>
                            <button
                                wire:click="$set('filterStatus', 'running')"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $filterStatus === 'running' ? 'bg-violet-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white' }}"
                            >Running</button>
                            <button
                                wire:click="$set('filterStatus', 'completed')"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $filterStatus === 'completed' ? 'bg-violet-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white' }}"
                            >Completed</button>
                        </div>
                    </div>
                </div>

                @if ($runs->isEmpty())
                    <div class="text-center py-16 border border-dashed border-white/10 rounded-2xl p-6">
                        <div class="w-16 h-16 rounded-3xl bg-violet-600/10 border border-violet-500/20 flex items-center justify-center text-3xl mx-auto mb-3">🎯</div>
                        <h3 class="text-base font-bold text-white mb-1">No Content Missions Found</h3>
                        <p class="text-slate-400 text-xs max-w-sm mx-auto mb-5">Launch your first autonomous mission to trigger multi-stage research, knowledge extraction, and TipTap document creation.</p>
                        <button
                            wire:click="openCreateModal"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white text-xs font-semibold shadow-lg shadow-violet-600/20 cursor-pointer"
                        >
                            Initialize First Mission
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($runs as $run)
                            @php
                                $totalStages = 10;
                                $completedStages = $run->nodes->where('status', 'success')->count();
                                $progressPercent = min(100, round(($completedStages / $totalStages) * 100));
                            @endphp
                            <div
                                wire:key="run-card-{{ $run->id }}"
                                class="p-4 rounded-2xl border transition-all relative overflow-hidden {{ $selectedRunId === $run->id ? 'bg-gradient-to-r from-violet-950/40 via-slate-900/90 to-indigo-950/30 border-violet-500/60 shadow-xl shadow-violet-500/10' : 'bg-slate-950/70 border-white/5 hover:border-white/20' }}"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        @if ($run->status->value === 'completed')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                                <span>✓</span> COMPLETED
                                            </span>
                                        @elseif ($run->status->value === 'running')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 animate-pulse flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> RUNNING
                                            </span>
                                        @elseif ($run->status->value === 'failed')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center gap-1">
                                                <span>⚠</span> FAILED
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">QUEUED</span>
                                        @endif

                                        @if(isset($run->mission->article_archetype))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                {{ $run->mission->article_archetype->shortLabel() }}
                                            </span>
                                        @endif

                                        <h3 class="text-sm font-bold text-white truncate max-w-xs sm:max-w-md">
                                            {{ $run->mission->topic ?? 'Untitled Mission' }}
                                        </h3>
                                    </div>

                                    <div class="text-[11px] text-slate-400">
                                        {{ $run->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                <!-- Progress Track -->
                                <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden my-2">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 {{ $run->status->value === 'completed' ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-violet-500 to-indigo-500' }}"
                                        style="width: {{ $progressPercent }}%"
                                    ></div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-slate-400 my-2">
                                    <div>
                                        <span class="text-slate-500">Active Node:</span>
                                        <span class="font-mono text-violet-300 font-semibold truncate block">{{ $run->current_node }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Confidence:</span>
                                        <span class="text-slate-200 font-bold">{{ round($run->overall_confidence * 100) }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Stages Done:</span>
                                        <span class="text-slate-200">{{ $completedStages }} / 10 ({{ $progressPercent }}%)</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Budget Tier:</span>
                                        <span class="text-slate-200 uppercase font-semibold text-[10px]">{{ $run->mission->research_budget_tier->value ?? 'STANDARD' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons Row -->
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-white/5 mt-2">
                                    <div class="flex items-center gap-2">
                                        <button
                                            wire:click="selectRun({{ $run->id }})"
                                            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $selectedRunId === $run->id ? 'bg-violet-600 text-white shadow-md shadow-violet-600/30' : 'bg-white/5 hover:bg-white/10 text-slate-300' }}"
                                        >
                                            Inspect Graph 🔍
                                        </button>

                                        @if ($run->document_id)
                                            <a
                                                href="{{ route('documents.editor', $run->document_id) }}"
                                                wire:navigate
                                                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600/25 hover:bg-emerald-600/35 text-emerald-200 border border-emerald-500/40 flex items-center gap-1 shadow-sm"
                                            >
                                                <span>✍️ Open TipTap</span>
                                            </a>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if ($run->status->value !== 'completed')
                                             <button
                                                 wire:click="stepWorkflow({{ $run->id }})"
                                                 wire:loading.attr="disabled"
                                                 class="px-3 py-1.5 rounded-xl text-xs font-medium bg-indigo-600/80 hover:bg-indigo-600 text-white flex items-center gap-1 cursor-pointer disabled:opacity-50"
                                             >
                                                 <span>Step Node</span>
                                             </button>

                                             @if ($isAutoRunning && $selectedRunId === $run->id)
                                                 <button
                                                     wire:click="stopAutoRun"
                                                     class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white flex items-center gap-1 cursor-pointer shadow-md shadow-amber-600/30 animate-pulse"
                                                 >
                                                     <span>⏸ Pause</span>
                                                 </button>
                                             @else
                                                 <button
                                                     wire:click="startAutoRun({{ $run->id }})"
                                                     wire:loading.attr="disabled"
                                                     class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white flex items-center gap-1 cursor-pointer disabled:opacity-50 shadow-md shadow-violet-600/20"
                                                 >
                                                     <span>⚡ Auto-Run</span>
                                                 </button>
                                             @endif
                                         @endif

                                        <button
                                            wire:click="deleteRun({{ $run->id }})"
                                            wire:confirm="Are you sure you want to remove this workflow run?"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-950/30 transition-colors cursor-pointer text-xs"
                                            title="Delete Run"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-2">
                        {{ $runs->links('livewire.custom-pagination') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Selected Run Deep Multi-Tab Inspector Drawer (Right 6 Cols) -->
        @if ($selectedRun)
            <div class="lg:col-span-6 space-y-4">
                <div class="p-5 rounded-3xl bg-slate-900/90 border border-violet-500/40 shadow-2xl backdrop-blur-2xl space-y-4">
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <h3 class="text-base font-bold text-white">Inspector Console #{{ $selectedRun->id }}</h3>
                            </div>
                            <div class="text-xs text-slate-400 font-medium truncate max-w-sm">{{ $selectedRun->mission->topic }}</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-violet-500/20 text-violet-300 font-semibold">
                                Conf: {{ round($selectedRun->overall_confidence * 100) }}%
                            </span>
                            <button wire:click="selectRun(null)" class="w-7 h-7 rounded-lg hover:bg-white/10 flex items-center justify-center text-slate-400 hover:text-white text-xs cursor-pointer">✕</button>
                        </div>
                    </div>

                    <!-- Inspector Multi-Tab Navigation -->
                    <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-2xl border border-white/10 overflow-x-auto text-xs">
                        <button
                            wire:click="setInspectorTab('progression')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'progression' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >⚡ Progression</button>
                        <button
                            wire:click="setInspectorTab('dossier')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'dossier' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >📋 Mission</button>
                        <button
                            wire:click="setInspectorTab('claims')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'claims' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🧬 Claims ({{ $selectedRun->mission->claims->count() }})</button>
                        <button
                            wire:click="setInspectorTab('blueprint')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'blueprint' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >📐 Blueprint</button>
                        <button
                            wire:click="setInspectorTab('drafts')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'drafts' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >✍️ Drafts ({{ $selectedRun->drafts->count() }})</button>
                        <button
                            wire:click="setInspectorTab('seo')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'seo' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🎯 SEO</button>
                        <button
                            wire:click="setInspectorTab('memory_os')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'memory_os' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🧠 Memory OS ({{ $memories->count() }})</button>
                        <button
                            wire:click="setInspectorTab('world_truth')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'world_truth' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🌐 World & Truth</button>
                        <button
                            wire:click="setInspectorTab('agents_router')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'agents_router' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🤖 Agents & Router</button>
                        <button
                            wire:click="setInspectorTab('health_repairs')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'health_repairs' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🔬 Health & Micro-Repair</button>
                        <button
                            wire:click="setInspectorTab('lineage_learning')"
                            class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-all {{ $inspectorTab === 'lineage_learning' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}"
                        >🧭 Lineage & Strategy</button>
                    </div>

                    <!-- TAB 1: PROGRESSION & NODE AUDIT LOG -->
                    @if ($inspectorTab === 'progression')
                        <div class="space-y-3">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Node Execution Audit Log</div>
                            <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                                @forelse ($selectedRun->nodes->sortBy('id') as $nodeRec)
                                    <div class="p-3 rounded-xl bg-slate-950/70 border border-white/5 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">✓</span>
                                            <div>
                                                <div class="font-semibold text-white font-mono">{{ $nodeRec->node_name }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $nodeRec->created_at->format('H:i:s') }} • Confidence: {{ round($nodeRec->confidence * 100) }}%</div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <span class="font-mono text-emerald-400 font-bold">{{ $nodeRec->latency_ms }}ms</span>
                                            @if ($nodeRec->retry_count > 0)
                                                <div class="text-[10px] text-amber-400 font-mono">Retries: {{ $nodeRec->retry_count }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                        No node records logged yet. Click 'Step Stage' or 'Autonomous Run'.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <!-- TAB 2: MISSION DOSSIER & CONSTRAINTS -->
                    @if ($inspectorTab === 'dossier')
                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-2">
                                <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Primary Objective & Thesis</div>
                                <p class="text-slate-200 leading-relaxed">{{ $selectedRun->mission->primary_objective }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-slate-950/70 border border-violet-500/20 col-span-2 flex items-center justify-between">
                                    <div class="text-slate-400 text-[10px] font-semibold uppercase">Article Archetype & Layout Engine</div>
                                    <div class="text-violet-300 font-bold text-xs">{{ $selectedRun->mission->article_archetype ? $selectedRun->mission->article_archetype->label() : 'Smart Auto-Detect (AI Inferred)' }}</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-950/70 border border-white/5">
                                    <div class="text-slate-400 text-[10px] font-semibold uppercase">Target Persona</div>
                                    <div class="text-white font-semibold mt-1">{{ $selectedRun->mission->target_audience['persona'] ?? 'Practitioner' }}</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-950/70 border border-white/5">
                                    <div class="text-slate-400 text-[10px] font-semibold uppercase">Expertise Level</div>
                                    <div class="text-white font-semibold mt-1">{{ $selectedRun->mission->target_audience['expertise_level'] ?? 'Intermediate' }}</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-950/70 border border-white/5">
                                    <div class="text-slate-400 text-[10px] font-semibold uppercase">Fact Risk Level</div>
                                    <div class="text-white font-semibold uppercase mt-1">{{ $selectedRun->mission->risk_level->value ?? 'MEDIUM' }}</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-950/70 border border-white/5">
                                    <div class="text-slate-400 text-[10px] font-semibold uppercase">Target Word Envelope</div>
                                    <div class="text-white font-semibold mt-1">{{ number_format($selectedRun->mission->target_word_count_min) }} - {{ number_format($selectedRun->mission->target_word_count_max) }} words</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 3: KNOWLEDGE FABRIC & CLAIM GRAPH -->
                    @if ($inspectorTab === 'claims')
                        <div class="space-y-3">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Verified Claim Graph & Citations</div>
                            <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                                @forelse ($selectedRun->mission->claims as $claim)
                                    <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-2 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $claim->epistemic_state->value === 'verified' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($claim->epistemic_state->value === 'contradicted' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                                                {{ $claim->epistemic_state->value }}
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-400">Score: {{ round($claim->confidence_score * 100) }}%</span>
                                        </div>
                                        <div class="text-slate-200 font-medium leading-relaxed">{{ $claim->statement }}</div>
                                        @if ($claim->source)
                                            <div class="text-[11px] text-violet-300 truncate">
                                                Source: <a href="{{ $claim->source->url }}" target="_blank" class="hover:underline">{{ $claim->source->title ?: $claim->source->url }}</a>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                        No claim nodes extracted yet. Step past 'knowledge_fabric' to generate.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <!-- TAB 4: BLUEPRINT & CONTENT ARCHITECTURE -->
                    @if ($inspectorTab === 'blueprint')
                        <div class="space-y-3 text-xs">
                            @if ($selectedRun->mission->blueprint)
                                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-1.5">
                                    <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Article Narrative Angle</div>
                                    <p class="text-white font-medium leading-relaxed">{{ $selectedRun->mission->blueprint->article_angle }}</p>
                                </div>

                                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-1.5">
                                    <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Unique Value Proposition (UVP)</div>
                                    <p class="text-indigo-200 leading-relaxed">{{ $selectedRun->mission->blueprint->unique_value_proposition }}</p>
                                </div>

                                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-1.5">
                                    <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Required Entities & Concepts</div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        @foreach ($selectedRun->mission->blueprint->required_entities ?? [] as $entity)
                                            <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-slate-300 text-[11px]">{{ $entity }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="p-6 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                    Content Blueprint pending. Advance to 'content_blueprint' stage.
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- TAB 5: SECTION DRAFTS & CRITIC EVALUATION -->
                    @if ($inspectorTab === 'drafts')
                        <div class="space-y-3">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Drafted Sections & Critic Scores</div>
                            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                                @forelse ($selectedRun->drafts as $draft)
                                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 space-y-2 text-xs">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-bold text-white text-sm">{{ $draft->heading }}</h4>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-mono font-bold">Critic: {{ $draft->critic_score }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">{{ $draft->word_count }} words</span>
                                            </div>
                                        </div>
                                        <div class="prose prose-invert prose-xs text-slate-300 line-clamp-4 leading-relaxed">
                                            {!! strip_tags($draft->content_html, '<p><strong><em>') !!}
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                        No section drafts generated yet. Advance through 'section_draftsman'.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <!-- TAB 6: SEO METADATA & SCHEMA GRAPH -->
                    @if ($inspectorTab === 'seo')
                        <div class="space-y-3 text-xs">
                            @if ($selectedRun->seoMetadata)
                                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-1">
                                    <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Meta Title</div>
                                    <p class="text-white font-bold">{{ $selectedRun->seoMetadata->meta_title }}</p>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ strlen($selectedRun->seoMetadata->meta_title) }} / 60 chars</span>
                                </div>

                                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-1">
                                    <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Meta Description</div>
                                    <p class="text-slate-300 leading-relaxed">{{ $selectedRun->seoMetadata->meta_description }}</p>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ strlen($selectedRun->seoMetadata->meta_description) }} / 160 chars</span>
                                </div>

                                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 space-y-1.5">
                                    <div class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider">Primary & Secondary Keywords</div>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-violet-600/30 text-violet-200 border border-violet-500/40 font-semibold">{{ $selectedRun->seoMetadata->primary_keyword }}</span>
                                        @foreach ($selectedRun->seoMetadata->secondary_keywords ?? [] as $kw)
                                            <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-slate-300">{{ $kw }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="p-6 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                    SEO Metadata pending. Advance to 'seo_optimization' stage.
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- TAB 7: COGNITIVE MEMORY OS & 3 BRAINS -->
                    @if ($inspectorTab === 'memory_os')
                        <div class="space-y-4">
                            <!-- 3-Tier Brain Status Bar -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="p-3 rounded-2xl bg-slate-950/70 border border-violet-500/20">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Level 1: Site Brain</div>
                                    <div class="text-xs font-bold text-violet-300 mt-1">Active Catalog</div>
                                    <div class="text-[10px] text-slate-500">Cannibalization Shield ON</div>
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-950/70 border border-indigo-500/20">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Level 2: Project Brain</div>
                                    <div class="text-xs font-bold text-indigo-300 mt-1 truncate">{{ $selectedRun->mission->topic }}</div>
                                    <div class="text-[10px] text-slate-500">Risk: {{ strtoupper($selectedRun->mission->risk_level instanceof \BackedEnum ? $selectedRun->mission->risk_level->value : (string) $selectedRun->mission->risk_level) }}</div>
                                </div>
                                <div class="p-3 rounded-2xl bg-slate-950/70 border border-emerald-500/20">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Level 3: Article Brain</div>
                                    <div class="text-xs font-bold text-emerald-300 mt-1">Elemental Graph</div>
                                    <div class="text-[10px] {{ $staleElementsCount > 0 ? 'text-amber-400 font-semibold' : 'text-slate-500' }}">
                                        {{ $staleElementsCount > 0 ? "⚠️ {$staleElementsCount} Stale Elements" : 'All Elements Clean' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Controls: Layer Filter & Evaluate Decay -->
                            <div class="flex items-center justify-between gap-2 p-2.5 rounded-2xl bg-slate-950/80 border border-white/5">
                                <div class="flex items-center gap-1">
                                    <select
                                        wire:model.live="memoryFilterLayer"
                                        class="px-2.5 py-1 rounded-xl bg-slate-900 border border-white/10 text-xs text-white focus:outline-none focus:border-violet-500"
                                    >
                                        <option value="all">All Cognitive Layers</option>
                                        <option value="working">Working Memory</option>
                                        <option value="semantic">Semantic Memory</option>
                                        <option value="episodic">Episodic Memory</option>
                                        <option value="procedural">Procedural Memory</option>
                                        <option value="strategic">Strategic Memory</option>
                                        <option value="brand">Brand Voice</option>
                                    </select>

                                    <select
                                        wire:model.live="memoryFilterScope"
                                        class="px-2.5 py-1 rounded-xl bg-slate-900 border border-white/10 text-xs text-white focus:outline-none focus:border-violet-500"
                                    >
                                        <option value="all">All Scopes</option>
                                        <option value="site">Site Brain</option>
                                        <option value="project">Project Brain</option>
                                        <option value="article">Article Brain</option>
                                    </select>
                                </div>

                                <button
                                    wire:click="triggerDecayCheck"
                                    class="px-3 py-1 rounded-xl bg-violet-600/30 hover:bg-violet-600/50 border border-violet-500/40 text-violet-300 text-xs font-semibold flex items-center gap-1 cursor-pointer transition-all"
                                    title="Evaluate Freshness Decay & Flag Stale Claims"
                                >
                                    <span>⏳ Check Decay</span>
                                </button>
                            </div>

                            <!-- Memories List -->
                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                @forelse ($memories as $mem)
                                    <div class="p-3 rounded-xl bg-slate-950/60 border {{ $mem->status->value === 'active' ? 'border-white/5' : 'border-amber-500/30 bg-amber-950/10' }} space-y-1.5 text-xs">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                                    {{ $mem->layer->value === 'brand' ? 'bg-amber-500/20 text-amber-300' : ($mem->layer->value === 'working' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-violet-500/20 text-violet-300') }}">
                                                    {{ $mem->layer->value }}
                                                </span>
                                                <span class="text-[10px] font-mono text-slate-400">v{{ $mem->version }}</span>
                                                @if ($mem->lineage_parent_id)
                                                    <span class="text-[10px] text-indigo-400 font-mono" title="Superseded Parent">⮑ parent: {{ substr($mem->lineage_parent_id, 0, 8) }}...</span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-semibold {{ $mem->freshness_score >= 0.8 ? 'text-emerald-400' : ($mem->freshness_score >= 0.5 ? 'text-amber-400' : 'text-rose-400') }}">
                                                    Freshness: {{ round($mem->freshness_score * 100) }}%
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                    {{ $mem->status->value === 'active' ? 'bg-emerald-500/20 text-emerald-300' : ($mem->status->value === 'superseded' ? 'bg-slate-700 text-slate-300' : 'bg-amber-500/20 text-amber-300') }}">
                                                    {{ ucfirst($mem->status->value) }}
                                                </span>
                                            </div>
                                        </div>

                                        @if ($mem->subject)
                                            <div class="font-bold text-white text-xs">
                                                <span class="text-violet-400">{{ $mem->subject }}</span>
                                                <span class="text-slate-400 font-mono text-[11px]">{{ $mem->predicate }}</span>
                                                <span class="text-emerald-300 font-normal">"{{ $mem->object }}"</span>
                                            </div>
                                        @else
                                            <div class="text-slate-200 font-medium text-xs">{{ $mem->content }}</div>
                                        @endif

                                        <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-white/5">
                                            <span>Confidence: {{ round($mem->confidence * 100) }}% • Authority: {{ $mem->authority }}/100</span>
                                            <span>{{ $mem->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-slate-500 text-xs border border-white/5 rounded-2xl">
                                        No memories admitted for this layer/scope filter yet.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Admission Gate Candidates Log -->
                            <div class="pt-2 border-t border-white/10 space-y-2">
                                <div class="text-xs font-bold text-slate-300 flex items-center justify-between">
                                    <span>🛡️ Memory Admission Gate Log</span>
                                    <span class="text-[10px] text-slate-500">Auto-Deduplication & Truth Verification</span>
                                </div>

                                <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                    @forelse ($memoryCandidates as $cand)
                                        <div class="p-2 rounded-lg bg-slate-950/80 border border-white/5 flex items-center justify-between text-xs">
                                            <div class="truncate max-w-xs">
                                                <span class="font-mono text-[10px] text-slate-400">[{{ $cand->layer }}]</span>
                                                <span class="text-slate-300 truncate">{{ $cand->content }}</span>
                                                @if ($cand->rejection_reason)
                                                    <div class="text-[10px] text-rose-400 truncate">Reason: {{ $cand->rejection_reason }}</div>
                                                @endif
                                            </div>

                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold
                                                {{ $cand->gate_status === 'admitted' ? 'bg-emerald-500/20 text-emerald-300' : ($cand->gate_status === 'merged' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-rose-500/20 text-rose-300') }}">
                                                {{ strtoupper($cand->gate_status) }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-[11px] text-slate-600 text-center py-2">No candidates evaluated yet.</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Surgical Fact Invalidation Tool -->
                            <div class="p-3 rounded-2xl bg-rose-950/20 border border-rose-500/30 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-rose-300">⚡ Surgical Fact Invalidation Tool</span>
                                    <span class="text-[10px] text-rose-400/80">Level 3 Article Brain Downstream Invalidator</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input
                                        type="text"
                                        wire:model="invalidationSubject"
                                        placeholder="Fact Subject (e.g. PHP 8.3)"
                                        class="px-2.5 py-1.5 rounded-xl bg-slate-950 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"
                                    />
                                    <input
                                        type="text"
                                        wire:model="invalidationReason"
                                        placeholder="Invalidation reason (e.g. Deprecated)"
                                        class="px-2.5 py-1.5 rounded-xl bg-slate-950 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"
                                    />
                                </div>
                                <button
                                    wire:click="triggerInvalidation"
                                    class="w-full py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-all shadow-md shadow-rose-600/20 cursor-pointer"
                                >
                                    Invalidate Fact & Mark Affected Sentences Stale
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 8: World Model & Truth Graph -->
                    @if ($inspectorTab === 'world_truth')
                        <div class="space-y-4">
                            <!-- Truth Layer Audit Scorecard -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-violet-500/30 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">⚖️</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Truth Layer Epistemic Audit</span>
                                    </div>
                                    @if ($truthAudit)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide
                                            {{ $truthAudit->riskRating === 'low' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : ($truthAudit->riskRating === 'medium' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40') }}">
                                            Risk: {{ strtoupper($truthAudit->riskRating) }}
                                        </span>
                                    @endif
                                </div>

                                @if ($truthAudit)
                                    <!-- Truth Score Big Gauge -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                                        <div class="p-2.5 rounded-xl bg-slate-900/80 border border-white/5 text-center">
                                            <span class="block text-[10px] uppercase text-slate-400 font-semibold">Truth Score</span>
                                            <span class="text-lg font-black {{ $truthAudit->overallTruthScore >= 80 ? 'text-emerald-400' : ($truthAudit->overallTruthScore >= 60 ? 'text-amber-400' : 'text-rose-400') }}">
                                                {{ round($truthAudit->overallTruthScore, 1) }}%
                                            </span>
                                        </div>
                                        <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/20 text-center">
                                            <span class="block text-[10px] uppercase text-emerald-400 font-semibold">Verified</span>
                                            <span class="text-lg font-black text-emerald-300">{{ $truthAudit->verifiedCount }}</span>
                                        </div>
                                        <div class="p-2.5 rounded-xl bg-indigo-950/20 border border-indigo-500/20 text-center">
                                            <span class="block text-[10px] uppercase text-indigo-400 font-semibold">Partially Ver.</span>
                                            <span class="text-lg font-black text-indigo-300">{{ $truthAudit->partiallyVerifiedCount }}</span>
                                        </div>
                                        <div class="p-2.5 rounded-xl bg-rose-950/20 border border-rose-500/20 text-center">
                                            <span class="block text-[10px] uppercase text-rose-400 font-semibold">Contradicted</span>
                                            <span class="text-lg font-black text-rose-300">{{ $truthAudit->contradictedCount }}</span>
                                        </div>
                                    </div>

                                    <!-- Recommendations Directives -->
                                    @if (!empty($truthAudit->recommendedActions))
                                        <div class="p-3 rounded-xl bg-violet-950/20 border border-violet-500/20 space-y-1.5">
                                            <div class="text-[11px] font-bold text-violet-300 flex items-center gap-1.5">
                                                <span>🎯</span> Actionable Truth Directives:
                                            </div>
                                            <ul class="space-y-1 text-xs text-slate-300 pl-4 list-disc marker:text-violet-400">
                                                @foreach ($truthAudit->recommendedActions as $rec)
                                                    <li class="text-[11px] leading-relaxed">{{ $rec }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Unresolved Issues -->
                                    @if (!empty($truthAudit->unresolvedIssues))
                                        <div class="p-3 rounded-xl bg-rose-950/20 border border-rose-500/20 space-y-1">
                                            <div class="text-[11px] font-bold text-rose-300 flex items-center gap-1.5">
                                                <span>⚠️</span> Epistemic Uncertainties Detected:
                                            </div>
                                            <ul class="space-y-1 text-xs text-rose-200/90 pl-4 list-disc marker:text-rose-400">
                                                @foreach ($truthAudit->unresolvedIssues as $issue)
                                                    <li class="text-[11px]">{{ $issue }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @else
                                    <div class="p-4 rounded-xl bg-slate-900/50 border border-white/5 text-center text-xs text-slate-400">
                                        No truth audit available yet. Execute research & synthesis stages to verify claims.
                                    </div>
                                @endif
                            </div>

                            <!-- Deep Evidence Lineage Explorer -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🔗</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Deep Lineage Trace</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-mono">Source ➔ Evidence ➔ Claim ➔ Sentence ➔ Doc</span>
                                </div>

                                <!-- Claim Selector Pills if multiple claims exist -->
                                @if ($selectedRun && $selectedRun->mission && $selectedRun->mission->claims->count() > 0)
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Select Claim to Trace Lineage:</label>
                                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-1">
                                            @foreach ($selectedRun->mission->claims as $c)
                                                <button
                                                    wire:click="selectClaim({{ $c->id }})"
                                                    class="px-2.5 py-1 rounded-xl text-[10px] font-semibold transition-all cursor-pointer border
                                                        {{ ($selectedClaimId ?? $selectedRun->mission->claims->first()?->id) === $c->id
                                                            ? 'bg-violet-600 border-violet-400 text-white shadow-sm'
                                                            : 'bg-slate-900/80 border-white/10 text-slate-300 hover:bg-slate-800' }}"
                                                    title="{{ $c->statement }}"
                                                >
                                                    Claim #{{ $c->id }}: {{ \Illuminate\Support\Str::limit($c->statement, 28) }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($claimLineage)
                                    <!-- Selected Claim Detail Card -->
                                    <div class="p-3 rounded-xl bg-slate-900 border border-violet-500/30 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-mono text-violet-400 uppercase font-bold">Lineage Focus Claim #{{ $claimLineage['claim_id'] }}</span>
                                            <div class="flex items-center gap-2">
                                                @if ($claimConsensus)
                                                    <span class="text-[10px] font-semibold {{ $claimConsensus['is_contradicted'] ? 'text-rose-400' : 'text-emerald-400' }}">
                                                        Consensus: {{ round($claimConsensus['consensus_score'] * 100) }}%
                                                    </span>
                                                @endif
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                    {{ $claimLineage['epistemic_state']->value === 'verified' ? 'bg-emerald-500/20 text-emerald-300' : ($claimLineage['epistemic_state']->value === 'partially_verified' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800 text-slate-300') }}">
                                                    {{ $claimLineage['epistemic_state']->value }}
                                                </span>
                                            </div>
                                        </div>

                                        <p class="text-xs font-semibold text-white leading-relaxed">
                                            "{{ $claimLineage['statement'] }}"
                                        </p>

                                        <!-- Chain Visualization -->
                                        <div class="p-2 rounded-lg bg-slate-950 border border-white/5 text-[10px] font-mono text-indigo-300/90">
                                            ⛓️ {{ $claimLineage['lineage_chain'] }}
                                        </div>

                                        <!-- Grounded Verbatim Evidence Extracts -->
                                        <div class="space-y-1.5 pt-1">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                Verbatim Evidence Quotes ({{ count($claimLineage['evidence_nodes']) }}):
                                            </span>
                                            @forelse ($claimLineage['evidence_nodes'] as $evNode)
                                                <div class="p-2.5 rounded-lg bg-slate-950/70 border border-white/5 space-y-1 text-[11px]">
                                                    <div class="flex items-center justify-between">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase
                                                            {{ $evNode['relation'] === 'supports' ? 'bg-emerald-500/20 text-emerald-300' : ($evNode['relation'] === 'refutes' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300') }}">
                                                            {{ $evNode['relation'] }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400 font-medium">
                                                            Reliability: {{ round(($evNode['reliability'] ?? 0.8) * 100) }}%
                                                        </span>
                                                    </div>
                                                    <blockquote class="italic text-slate-200 pl-2 border-l-2 border-violet-500/50">
                                                        "{{ $evNode['quote'] }}"
                                                    </blockquote>
                                                    @if (!empty($evNode['source_title']))
                                                        <div class="text-[10px] text-slate-400 flex items-center justify-between pt-0.5">
                                                            <span class="truncate">Source: {{ $evNode['source_title'] }}</span>
                                                            @if (!empty($evNode['source_url']))
                                                                <a href="{{ $evNode['source_url'] }}" target="_blank" rel="noopener" class="text-indigo-400 hover:underline">Link ↗</a>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="text-[11px] text-slate-500 italic">No direct evidence snippets grounded yet.</div>
                                            @endforelse
                                        </div>

                                        <!-- Grounded Article Sentences -->
                                        @if (!empty($claimLineage['article_sentences']))
                                            <div class="space-y-1.5 pt-1">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                    Grounded TipTap Sentences ({{ count($claimLineage['article_sentences']) }}):
                                                </span>
                                                @foreach ($claimLineage['article_sentences'] as $stNode)
                                                    <div class="p-2 rounded-lg bg-slate-950/50 border {{ $stNode['is_stale'] ? 'border-amber-500/40 bg-amber-950/10' : 'border-white/5' }} text-[11px]">
                                                        <div class="flex items-center justify-between text-[9px] text-slate-400 pb-0.5">
                                                            <span>Section #{{ $stNode['section_index'] }} • Sentence #{{ $stNode['sentence_index'] }}</span>
                                                            @if ($stNode['is_stale'])
                                                                <span class="text-amber-400 font-bold">⚠️ Stale (Requires Regeneration)</span>
                                                            @else
                                                                <span class="text-emerald-400 font-medium">✓ Validated</span>
                                                            @endif
                                                        </div>
                                                        <p class="text-slate-200">"{{ $stNode['sentence_text'] }}"</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="p-4 rounded-xl bg-slate-900/50 border border-white/5 text-center text-xs text-slate-400">
                                        Select a workflow run with extracted claims to inspect full deep lineage.
                                    </div>
                                @endif
                            </div>

                            <!-- World Model Domain Entities Matrix -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🌐</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">World Model Domain Knowledge Graph</span>
                                    </div>
                                    <span class="text-[10px] text-indigo-400 font-semibold">{{ $worldEntities->count() }} Entities Cataloged</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1">
                                    @forelse ($worldEntities as $ent)
                                        <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-white">{{ $ent->name }}</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-violet-500/20 text-violet-300">
                                                    {{ $ent->category }}
                                                </span>
                                            </div>
                                            @if ($ent->description)
                                                <p class="text-[11px] text-slate-400 line-clamp-2">{{ $ent->description }}</p>
                                            @endif
                                            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-0.5">
                                                <span class="font-mono">{{ $ent->canonical_name }}</span>
                                                <span>Confidence: {{ round($ent->confidence_score * 100) }}%</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-2 p-4 rounded-xl bg-slate-900/50 border border-white/5 text-center text-xs text-slate-500">
                                            World model graph empty. Entities will be resolved dynamically during research synthesis.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 9: AGENTS, ORCHESTRATOR, BLACKBOARD & MODEL ROUTING -->
                    @if ($inspectorTab === 'agents_router')
                        <div class="space-y-4">
                            <!-- Section A: 7 Specialized Worker Agents -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-violet-500/30 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🤖</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Agent Orchestrator Worker Pool</span>
                                    </div>
                                    <span class="text-[10px] text-violet-400 font-mono">{{ count($orchestratorAgents) }} Agents Armed</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach ($orchestratorAgents as $name => $agent)
                                        <div class="p-3 rounded-xl bg-slate-900 border border-white/5 space-y-2 hover:border-violet-500/30 transition-all">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-base">{{ $agent->getRole()->icon() }}</span>
                                                    <span class="text-xs font-bold text-white">{{ $agent->getRole()->displayName() }}</span>
                                                </div>
                                                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase bg-violet-500/20 text-violet-300">
                                                    {{ $name }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">{{ $agent->getDescription() }}</p>
                                            <div class="flex items-center justify-between pt-1 border-t border-white/5">
                                                <span class="text-[9px] text-slate-500 font-mono truncate max-w-[140px]">
                                                    {{ implode(', ', array_map(fn($t) => $t->value, $agent->getSupportedTaskTypes())) }}
                                                </span>
                                                <button
                                                    wire:click="dispatchWorkerAgent('{{ $name }}')"
                                                    wire:loading.attr="disabled"
                                                    class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-[10px] font-bold text-white transition-all shadow-sm cursor-pointer"
                                                >
                                                    ⚡ Run
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Section B: Active Brain Blackboard -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">📋</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Brain Blackboard (Cognitive Workspace)</span>
                                    </div>
                                    <span class="text-[10px] text-emerald-400 font-mono">Status: Active</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Current Goal</span>
                                        <p class="text-slate-200 font-medium leading-relaxed">{{ $blackboard['current_goal'] ?? ($selectedRun->mission->primary_objective ?? 'Execute Content Pipeline') }}</p>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Next Best Action</span>
                                        <p class="text-indigo-300 font-medium leading-relaxed">{{ $blackboard['next_best_action'] ?? 'Formulate outline architecture and compose verified sections' }}</p>
                                    </div>
                                </div>

                                <!-- Known Facts on Blackboard -->
                                @if (!empty($blackboard['known_facts']))
                                    <div class="space-y-1.5 pt-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Blackboard Known Facts:</span>
                                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-1">
                                            @foreach ((array)$blackboard['known_facts'] as $f)
                                                <span class="px-2 py-1 rounded-lg text-[10px] bg-slate-900 border border-white/5 text-slate-300 flex items-center gap-1">
                                                    <span class="text-emerald-400">✓</span> {{ \Illuminate\Support\Str::limit($f, 36) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Section C: Multi-Model Dynamic Router Matrix -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">⚡</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Multi-Model Router Matrix (OmniRoute)</span>
                                    </div>
                                    <span class="text-[10px] text-indigo-400 font-semibold">Dynamic Task Mapping</span>
                                </div>

                                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                    @foreach ($routingMatrix as $taskKey => $route)
                                        <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 flex items-center justify-between text-xs">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-white">{{ $route['task_name'] }}</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                                        {{ $route['quality_tier'] === 'fast' ? 'bg-cyan-500/20 text-cyan-300' : ($route['quality_tier'] === 'reasoning' ? 'bg-fuchsia-500/20 text-fuchsia-300' : ($route['quality_tier'] === 'high_accuracy' ? 'bg-amber-500/20 text-amber-300' : 'bg-indigo-500/20 text-indigo-300')) }}">
                                                        {{ $route['quality_tier'] }}
                                                    </span>
                                                    @if ($route['is_reasoning'])
                                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-mono font-extrabold bg-violet-500 text-white uppercase">REASONING</span>
                                                    @endif
                                                </div>
                                                <p class="text-[10px] text-slate-400 truncate max-w-sm">{{ $route['rationale'] }}</p>
                                            </div>

                                            <div class="text-right space-y-0.5">
                                                <span class="font-mono text-xs font-bold text-emerald-400 block">{{ $route['model_id'] }}</span>
                                                <span class="text-[10px] text-slate-500 font-mono">~{{ $route['estimated_latency_ms'] }}ms</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Section D: Auditable Brain Decisions -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">⚖️</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Explainable Brain Decisions Log</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $brainDecisions->count() }} Decisions Audited</span>
                                </div>

                                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                    @forelse ($brainDecisions as $decision)
                                        <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1.5 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-white">{{ $decision->question }}</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-500/20 text-emerald-300">
                                                    {{ $decision->decision }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-300 leading-relaxed">{{ $decision->reasoning }}</p>
                                            <div class="flex items-center justify-between text-[9px] text-slate-500 pt-1 border-t border-white/5">
                                                <span>Confidence: {{ round($decision->confidence * 100) }}%</span>
                                                <span>{{ $decision->created_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                            No explicit brain decisions logged for this run yet. Run the workflow to record explainable decisions.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Section E: Agent Activities Telemetry -->
                            @if ($agentActivities->isNotEmpty())
                                <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3 shadow-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">📊</span>
                                            <span class="text-xs font-bold text-white uppercase tracking-wider">Recent Agent Activity Telemetry</span>
                                        </div>
                                        <span class="text-[10px] text-indigo-400 font-mono">{{ $agentActivities->count() }} Executions</span>
                                    </div>

                                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                        @foreach ($agentActivities as $act)
                                            <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1 text-xs">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-white uppercase font-mono text-[10px]">{{ $act->agent_name }}</span>
                                                    <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400">
                                                        <span>Model: {{ $act->model_used ?? 'N/A' }}</span>
                                                        <span>•</span>
                                                        <span class="text-indigo-300">{{ $act->latency_ms }}ms</span>
                                                    </div>
                                                </div>
                                                <p class="text-[11px] text-slate-300">{{ $act->output_summary }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- TAB 10: CONTENT HEALTH, SURGICAL MICRO-REPAIR & RISK ENGINE -->
                    @if ($inspectorTab === 'health_repairs')
                        <div class="space-y-4">
                            <!-- Section A: 15-Dimension Multidimensional Content Health Model -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-violet-500/30 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🩺</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">15-Dimension Content Health</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($qualityAudit)
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-black font-mono {{ in_array($qualityAudit->grade, ['A+', 'A']) ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($qualityAudit->grade === 'B' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                                                Grade {{ $qualityAudit->grade }} ({{ $qualityAudit->overall_score }}/100)
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 font-mono">Unassessed</span>
                                        @endif
                                        <button
                                            wire:click="runQualityAudit"
                                            wire:loading.attr="disabled"
                                            class="px-2.5 py-1 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-[11px] font-semibold transition-all shadow-sm cursor-pointer disabled:opacity-50"
                                        >
                                            ⚡ Audit Health
                                        </button>
                                    </div>
                                </div>

                                @if ($qualityAudit)
                                    <!-- Strengths & Gaps Pills -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                        <div class="p-2.5 rounded-xl bg-emerald-950/30 border border-emerald-500/20 space-y-1">
                                            <div class="font-bold text-emerald-300 uppercase text-[10px] tracking-wider">✓ Verified Strengths</div>
                                            <ul class="space-y-0.5 text-slate-300">
                                                @foreach ($qualityAudit->key_strengths ?? [] as $st)
                                                    <li class="line-clamp-1">• {{ $st }}</li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="p-2.5 rounded-xl bg-amber-950/30 border border-amber-500/20 space-y-1">
                                            <div class="font-bold text-amber-300 uppercase text-[10px] tracking-wider">⚠ Critical Directives</div>
                                            <ul class="space-y-0.5 text-slate-300">
                                                @foreach ($qualityAudit->critical_gaps ?? ['Zero critical health gaps detected.'] as $cg)
                                                    <li class="line-clamp-1">• {{ $cg }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- 15 Dimensions Matrix Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto pr-1">
                                        @foreach ($qualityAudit->dimensions ?? [] as $dim)
                                            <div class="p-2 rounded-xl bg-slate-900 border border-white/5 space-y-1 text-xs">
                                                <div class="flex items-center justify-between text-[11px]">
                                                    <span class="font-semibold text-white truncate max-w-[120px]">{{ $dim['name'] }}</span>
                                                    <span class="font-mono font-bold {{ $dim['score'] >= 85 ? 'text-emerald-400' : ($dim['score'] >= 70 ? 'text-indigo-300' : 'text-amber-400') }}">
                                                        {{ round($dim['score']) }}%
                                                    </span>
                                                </div>
                                                <div class="w-full h-1 rounded-full bg-white/10 overflow-hidden">
                                                    <div class="h-full rounded-full {{ $dim['score'] >= 85 ? 'bg-emerald-500' : ($dim['score'] >= 70 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ $dim['score'] }}%"></div>
                                                </div>
                                                <p class="text-[10px] text-slate-400 line-clamp-1">{{ $dim['reasons'][0] ?? 'Calibrated metric' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-6 border border-dashed border-white/10 rounded-xl">
                                        <p class="text-xs text-slate-400 mb-2">Multidimensional Content Health model uncomputed for this run.</p>
                                        <button wire:click="runQualityAudit" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold cursor-pointer">
                                            Run 15-Dimension Audit
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Section B: Surgical Micro-Repair Loop & Smallest-Unit Escalation Ladder -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🩹</span>
                                        <div>
                                            <span class="text-xs font-bold text-white uppercase tracking-wider">Surgical Micro-Repair Loop</span>
                                            <div class="text-[10px] text-slate-400 font-mono">Ladder: Sentence ➔ Paragraph ➔ Section ➔ Article</div>
                                        </div>
                                    </div>
                                    <button
                                        wire:click="triggerMicroRepair"
                                        wire:loading.attr="disabled"
                                        class="px-2.5 py-1 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 text-white text-[11px] font-semibold transition-all shadow-sm cursor-pointer disabled:opacity-50"
                                    >
                                        ⚡ Run Micro-Repair
                                    </button>
                                </div>

                                <!-- Micro Repairs Feed -->
                                <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                                    @forelse ($microRepairs as $rep)
                                        <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1.5 text-xs">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase font-bold {{ $rep->unit_type->value === 'sentence' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : ($rep->unit_type->value === 'paragraph' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-violet-500/20 text-violet-300 border border-violet-500/30') }}">
                                                        {{ $rep->unit_type->value }}
                                                    </span>
                                                    <span class="text-[11px] font-semibold text-slate-300">{{ $rep->problem_category->label() }}</span>
                                                </div>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold text-emerald-400 bg-emerald-500/10">
                                                    {{ $rep->status->value }}
                                                </span>
                                            </div>

                                            <p class="text-[10px] text-slate-400 italic">Root Cause: {{ $rep->root_cause }}</p>

                                            @if ($rep->diff_summary)
                                                <div class="p-1.5 rounded-lg bg-slate-950 font-mono text-[10px] text-indigo-300 truncate">
                                                    {{ $rep->diff_summary }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center py-5 text-xs text-slate-500 border border-dashed border-white/5 rounded-xl">
                                            Zero micro-repairs logged yet. Click "Run Micro-Repair" to detect and surgically repair the smallest affected unit.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Section C: Risk Engine & Verification Gating -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-amber-500/30 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🛡️</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Content Risk & Verification Gating</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($riskAssessment)
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-black font-mono uppercase {{ in_array($riskAssessment->risk_level->value, ['high', 'critical']) ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : ($riskAssessment->risk_level->value === 'medium' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30') }}">
                                                {{ $riskAssessment->risk_level->value }} Risk ({{ $riskAssessment->risk_score }}/100)
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($riskAssessment)
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                        <div class="p-2 rounded-xl bg-slate-900 border border-white/5 space-y-0.5">
                                            <div class="text-[10px] text-slate-400 uppercase">YMYL Classification</div>
                                            <div class="font-bold {{ $riskAssessment->is_ymyl ? 'text-amber-400' : 'text-emerald-400' }}">
                                                {{ $riskAssessment->is_ymyl ? '⚠ YMYL Regulated' : '✓ Standard Domain' }}
                                            </div>
                                        </div>

                                        <div class="p-2 rounded-xl bg-slate-900 border border-white/5 space-y-0.5">
                                            <div class="text-[10px] text-slate-400 uppercase">Primary Sources Required</div>
                                            <div class="font-bold {{ $riskAssessment->requires_primary_sources ? 'text-cyan-400' : 'text-slate-300' }}">
                                                {{ $riskAssessment->requires_primary_sources ? 'Mandatory Citations' : 'Optional' }}
                                            </div>
                                        </div>

                                        <div class="p-2 rounded-xl bg-slate-900 border border-white/5 space-y-0.5">
                                            <div class="text-[10px] text-slate-400 uppercase">Human Signoff Gate</div>
                                            <div class="font-bold {{ $riskAssessment->is_approved_by_human ? 'text-emerald-400' : ($riskAssessment->requires_human_approval ? 'text-rose-400 animate-pulse' : 'text-slate-400') }}">
                                                {{ $riskAssessment->is_approved_by_human ? '✓ Approved by Human' : ($riskAssessment->requires_human_approval ? '⏳ Signoff Pending' : 'Auto-Cleared') }}
                                            </div>
                                        </div>
                                    </div>

                                    @if ($riskAssessment->requires_human_approval && ! $riskAssessment->is_approved_by_human)
                                        <div class="p-3 rounded-xl bg-rose-950/30 border border-rose-500/30 flex items-center justify-between text-xs">
                                            <span class="text-rose-300 font-semibold">High-risk verification gate active. Grant human signoff before final publishing.</span>
                                            <button
                                                wire:click="approveRiskGate"
                                                class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition-all shadow-md cursor-pointer"
                                            >
                                                Grant Human Signoff ✓
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-4 text-xs text-slate-400">
                                        Risk profile computed automatically upon workflow execution.
                                    </div>
                                @endif
                            </div>

                            <!-- Section D: Content Genome (Cross-Article Reusable Knowledge Asset) -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-cyan-500/30 space-y-3 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">🧬</span>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Content Genome (Reusable Knowledge DNA)</span>
                                    </div>
                                    <button
                                        wire:click="synthesizeContentGenome"
                                        wire:loading.attr="disabled"
                                        class="px-2.5 py-1 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-[11px] font-semibold transition-all shadow-sm cursor-pointer disabled:opacity-50"
                                    >
                                        ⚡ Synthesize Genome
                                    </button>
                                </div>

                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    @forelse ($contentGenomes as $gen)
                                        <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5 space-y-1.5 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-white truncate max-w-xs">{{ $gen->title }}</span>
                                                <span class="font-mono text-[10px] text-cyan-400">{{ substr($gen->genome_signature, 0, 12) }}...</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-400 font-mono">
                                                <span>Claims: {{ count($gen->claims_dna ?? []) }}</span>
                                                <span>•</span>
                                                <span>Entities: {{ count($gen->entities_dna ?? []) }}</span>
                                                <span>•</span>
                                                <span>Benchmarks: {{ count($gen->facts_dna ?? []) }}</span>
                                                <span>•</span>
                                                <span class="text-emerald-300">Grade: {{ $gen->quality_dna['grade'] ?? 'B' }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-xs text-slate-500 border border-dashed border-white/5 rounded-xl">
                                            No Content Genomes synthesized yet. Click "Synthesize Genome" to convert this mission into a reusable knowledge object.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TAB 11: CONTENT LINEAGE, AUTONOMOUS LEARNING & SITE TOPIC STRATEGY -->
                    @if ($inspectorTab === 'lineage_learning')
                        <div class="space-y-6">
                            <!-- Section A: 7-Tier Content Lineage & Traceability (brain.md Section 26) -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-violet-500/30 space-y-4 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🧭</span>
                                        <div>
                                            <span class="text-xs font-bold text-white uppercase tracking-wider">7-Tier Content Lineage Graph</span>
                                            <div class="text-[10px] text-slate-400 font-mono">Source ➔ Evidence ➔ Claim ➔ Sentence ➔ Paragraph ➔ Section ➔ Article ➔ URL</div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-violet-500/20 text-violet-300 border border-violet-500/30">
                                        {{ $lineageNodes->count() }} Sentences Traced
                                    </span>
                                </div>

                                <!-- Sentence Feed & Selector -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <div class="text-[11px] font-semibold text-slate-400">Tracked Document Sentences:</div>
                                        <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                                            @forelse ($lineageNodes as $node)
                                                <div
                                                    wire:click="selectLineageNode({{ $node->id }})"
                                                    class="p-2 rounded-xl border text-xs cursor-pointer transition-all {{ $selectedLineageNodeId === $node->id ? 'bg-violet-950/60 border-violet-500/60 text-white shadow-sm' : ($node->is_stale ? 'bg-rose-950/30 border-rose-500/30 text-rose-300' : 'bg-slate-900 border-white/5 text-slate-300 hover:bg-slate-850') }}"
                                                >
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-[10px] font-mono text-slate-400">Sec {{ $node->section_index }} • P{{ $node->paragraph_index }} • S{{ $node->sentence_index }}</span>
                                                        @if ($node->is_stale)
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">STALE</span>
                                                        @else
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300">FRESH</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-[11px] line-clamp-2">{{ $node->sentence_text }}</p>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                                    No lineage nodes recorded for this run yet. Run the pipeline to map prose to claims.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Deep Upstream Lineage Inspector -->
                                    <div class="p-3.5 rounded-xl bg-slate-900 border border-white/10 space-y-2.5 text-xs">
                                        <div class="text-[11px] font-bold text-white uppercase tracking-wider flex items-center justify-between">
                                            <span>Upstream Epistemic Roots</span>
                                            @if ($selectedLineageTrace?->isStale)
                                                <span class="text-rose-400 font-bold text-[10px]">⚠ Fact Invalidated</span>
                                            @endif
                                        </div>

                                        @if ($selectedLineageTrace)
                                            <div class="space-y-2 text-[11px]">
                                                <div class="p-2 rounded-lg bg-slate-950 border border-white/5 space-y-0.5">
                                                    <span class="text-[10px] text-slate-500 uppercase font-mono">1. Statement (Prose):</span>
                                                    <div class="text-slate-200 font-medium">"{{ $selectedLineageTrace->sentenceText }}"</div>
                                                </div>

                                                <div class="p-2 rounded-lg bg-slate-950 border border-white/5 space-y-0.5">
                                                    <span class="text-[10px] text-slate-500 uppercase font-mono">2. Root Claim:</span>
                                                    <div class="text-indigo-300 font-medium">
                                                        {{ $selectedLineageTrace->claimText ?? 'Direct Editorial Axiom' }}
                                                    </div>
                                                    @if ($selectedLineageTrace->claimStatus)
                                                        <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] font-mono uppercase bg-indigo-500/20 text-indigo-300">
                                                            Status: {{ $selectedLineageTrace->claimStatus }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="p-2 rounded-lg bg-slate-950 border border-white/5 space-y-0.5">
                                                    <span class="text-[10px] text-slate-500 uppercase font-mono">3. Grounding Evidence:</span>
                                                    <div class="text-slate-300 italic text-[10px]">
                                                        "{{ $selectedLineageTrace->evidenceQuote ?? 'General consensus / expert synthesis' }}"
                                                    </div>
                                                </div>

                                                <div class="p-2 rounded-lg bg-slate-950 border border-white/5 space-y-0.5">
                                                    <span class="text-[10px] text-slate-500 uppercase font-mono">4. Primary Source Authority:</span>
                                                    <div class="text-cyan-300 font-semibold truncate">
                                                        {{ $selectedLineageTrace->sourceTitle ?? 'Internal Knowledge Base' }}
                                                    </div>
                                                    @if ($selectedLineageTrace->sourceUrl)
                                                        <a href="{{ $selectedLineageTrace->sourceUrl }}" target="_blank" class="text-[10px] text-violet-400 hover:underline block truncate">
                                                            {{ $selectedLineageTrace->sourceUrl }} ↗
                                                        </a>
                                                    @endif
                                                </div>

                                                @if ($selectedLineageTrace->isStale)
                                                    <div class="p-2.5 rounded-lg bg-rose-950/40 border border-rose-500/40 text-rose-200 space-y-1">
                                                        <div class="font-bold text-[10px] uppercase">Reason for Invalidation:</div>
                                                        <div class="text-[11px]">{{ $selectedLineageTrace->invalidationReason }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-center py-6 text-slate-500">
                                                Select a sentence on the left to trace its upstream roots.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Section B: Autonomous Learning Engine (brain.md Section 27) -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-indigo-500/30 space-y-4 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🧠</span>
                                        <div>
                                            <span class="text-xs font-bold text-white uppercase tracking-wider">Autonomous Learning Engine</span>
                                            <div class="text-[10px] text-slate-400 font-mono">Lifecycle: Observation ➔ Candidate ➔ Validated ➔ Adopted</div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                        {{ $strategyMemories->count() }} Strategies Discovered
                                    </span>
                                </div>

                                <!-- Strategy Memories Matrix -->
                                <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                    @forelse ($strategyMemories as $strat)
                                        <div class="p-3 rounded-xl bg-slate-900 border border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-white font-mono">{{ $strat->strategy_key }}</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $strat->status->value === 'adopted' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($strat->status->value === 'validated' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : ($strat->status->value === 'candidate' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-amber-500/20 text-amber-300')) }}">
                                                        {{ $strat->status->value }}
                                                    </span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-white/5 text-slate-400 uppercase">
                                                        {{ $strat->category->value }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-300">{{ $strat->learning_payload['rule'] ?? 'Strategy synthesized from workflow performance.' }}</p>
                                                <div class="text-[10px] text-slate-400 flex items-center gap-3">
                                                    <span>Evidence Count: <strong class="text-white font-mono">{{ $strat->evidence_count }}</strong></span>
                                                    <span>Confidence: <strong class="text-indigo-300 font-mono">{{ round($strat->confidence * 100) }}%</strong></span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-1.5 self-end sm:self-center">
                                                @if ($strat->status->value !== 'adopted')
                                                    <button
                                                        wire:click="adoptStrategyCandidate({{ $strat->id }})"
                                                        class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-all cursor-pointer shadow-sm"
                                                    >
                                                        Adopt ✓
                                                    </button>
                                                @endif
                                                @if ($strat->status->value !== 'rejected')
                                                    <button
                                                        wire:click="rejectStrategyCandidate({{ $strat->id }})"
                                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-white/5 hover:bg-rose-950/40 text-slate-400 hover:text-rose-300 transition-all cursor-pointer"
                                                    >
                                                        Reject
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                            No autonomous strategy memories formed yet. As missions complete, successful patterns are observed and promoted.
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Manual Strategy Observation Form -->
                                <div class="p-3 rounded-xl bg-slate-900/90 border border-white/5 space-y-2">
                                    <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Register Custom Strategic Observation</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <input
                                            type="text"
                                            wire:model="newStrategyKey"
                                            placeholder="Strategy Key (e.g. data_table_first)"
                                            class="px-3 py-1.5 rounded-xl bg-slate-950 border border-white/10 text-white text-xs"
                                        />
                                        <select
                                            wire:model="newStrategyCategory"
                                            class="px-3 py-1.5 rounded-xl bg-slate-950 border border-white/10 text-white text-xs"
                                        >
                                            <option value="structure">Structure</option>
                                            <option value="sources">Sources</option>
                                            <option value="tone">Tone</option>
                                            <option value="workflow">Workflow</option>
                                            <option value="seo">SEO</option>
                                        </select>
                                        <input
                                            type="text"
                                            wire:model="newStrategyRule"
                                            placeholder="Behavioral rule definition..."
                                            class="px-3 py-1.5 rounded-xl bg-slate-950 border border-white/10 text-white text-xs"
                                        />
                                    </div>
                                    <button
                                        wire:click="recordStrategyObservation"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold cursor-pointer transition-all shadow-sm"
                                    >
                                        Record Strategic Observation ➔
                                    </button>
                                </div>
                            </div>

                            <!-- Section C: User Feedback Intelligence (brain.md Section 28) -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-teal-500/30 space-y-4 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">✍️</span>
                                        <div>
                                            <span class="text-xs font-bold text-white uppercase tracking-wider">User Feedback Intelligence (Style Preferences)</span>
                                            <div class="text-[10px] text-slate-400 font-mono">Learned automatically from manual user prose adjustments</div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                        {{ $stylePreferences->count() }} Style Rules
                                    </span>
                                </div>

                                <!-- Learned Preferences Feed -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @forelse ($stylePreferences as $pref)
                                        <div class="p-3 rounded-xl bg-slate-900 border border-white/5 space-y-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-mono font-bold text-teal-300">{{ $pref->preference_key }}</span>
                                                <button
                                                    wire:click="toggleStylePreference({{ $pref->id }}, {{ $pref->is_active ? 'false' : 'true' }})"
                                                    class="px-2 py-0.5 rounded text-[10px] font-bold cursor-pointer {{ $pref->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/5 text-slate-500' }}"
                                                >
                                                    {{ $pref->is_active ? 'Active' : 'Muted' }}
                                                </button>
                                            </div>
                                            <p class="text-[11px] text-slate-300">{{ $pref->rule_description }}</p>
                                            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                                                <span>Diffs Observed: <strong class="text-white">{{ $pref->observed_diff_count }}</strong></span>
                                                <span>Confidence: <strong class="text-teal-300">{{ round($pref->confidence * 100) }}%</strong></span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-2 p-4 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                            No user style rules recorded yet. Edit AI paragraphs in TipTap to teach the model your authorial voice.
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Interactive Diff Intelligence Tester -->
                                <div class="p-3 rounded-xl bg-slate-900/90 border border-white/5 space-y-2">
                                    <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Test Author Diff Analysis</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <textarea
                                            wire:model="testOriginalEdit"
                                            rows="2"
                                            placeholder="Original AI generated paragraph..."
                                            class="w-full p-2 rounded-xl bg-slate-950 border border-white/10 text-xs text-white resize-none"
                                        ></textarea>
                                        <textarea
                                            wire:model="testManualEdit"
                                            rows="2"
                                            placeholder="Your edited concise version..."
                                            class="w-full p-2 rounded-xl bg-slate-950 border border-white/10 text-xs text-white resize-none"
                                        ></textarea>
                                    </div>
                                    <button
                                        wire:click="analyzeUserEditDiff"
                                        class="px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-semibold cursor-pointer transition-all shadow-sm"
                                    >
                                        Analyze Edit Diff & Learn Preference ➔
                                    </button>
                                </div>
                            </div>

                            <!-- Section D: Site-Level Topic Strategy & Portfolio (brain.md Section 29) -->
                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-emerald-500/30 space-y-4 shadow-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">🌐</span>
                                        <div>
                                            <span class="text-xs font-bold text-white uppercase tracking-wider">Site-Level Topic Strategy & Portfolio</span>
                                            <div class="text-[10px] text-slate-400 font-mono">Portfolio Clusters, Keyword Cannibalization & Cross-Linking Matrix</div>
                                        </div>
                                    </div>
                                    <button
                                        wire:click="refreshTopicPortfolio"
                                        class="px-2.5 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold transition-all shadow-sm cursor-pointer"
                                    >
                                        ⟳ Refresh Portfolio
                                    </button>
                                </div>

                                <!-- Portfolio Executive Metrics -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5">
                                        <div class="text-[10px] text-slate-400 uppercase">Topic Clusters</div>
                                        <div class="text-lg font-bold text-white font-mono">{{ $portfolioReport->totalClusters }}</div>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5">
                                        <div class="text-[10px] text-slate-400 uppercase">Avg Coverage</div>
                                        <div class="text-lg font-bold text-emerald-400 font-mono">{{ $portfolioReport->averageCoverageScore }}%</div>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5">
                                        <div class="text-[10px] text-slate-400 uppercase">Cannibalization Risks</div>
                                        <div class="text-lg font-bold {{ $portfolioReport->cannibalizationAlertsCount > 0 ? 'text-amber-400' : 'text-slate-300' }} font-mono">
                                            {{ $portfolioReport->cannibalizationAlertsCount }}
                                        </div>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-white/5">
                                        <div class="text-[10px] text-slate-400 uppercase">Uncovered Topics</div>
                                        <div class="text-lg font-bold text-cyan-300 font-mono">{{ $portfolioReport->uncoveredTopicsCount }}</div>
                                    </div>
                                </div>

                                <!-- Clusters Matrix -->
                                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                    @forelse ($topicClusters as $cluster)
                                        <div class="p-3 rounded-xl bg-slate-900 border border-white/5 space-y-2 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-white">{{ $cluster->cluster_name }}</span>
                                                <span class="font-mono text-emerald-400 font-bold">Coverage: {{ $cluster->coverage_score }}%</span>
                                            </div>

                                            <div class="w-full bg-white/5 h-1 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ $cluster->coverage_score }}%"></div>
                                            </div>

                                            @if (! empty($cluster->cannibalization_risks))
                                                <div class="p-2 rounded-lg bg-amber-950/30 border border-amber-500/30 text-amber-200 text-[11px] space-y-1">
                                                    <div class="font-bold text-[10px] uppercase">⚠ Cannibalization Detected:</div>
                                                    @foreach ($cluster->cannibalization_risks as $risk)
                                                        <div>{{ $risk['recommendation'] ?? 'Review similar title angles.' }}</div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if (! empty($cluster->uncovered_subtopics))
                                                <div class="text-[10px] text-slate-400">
                                                    <span class="font-semibold text-slate-300">Uncovered Subtopics:</span>
                                                    {{ implode(' • ', array_slice($cluster->uncovered_subtopics, 0, 3)) }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-xs text-slate-500 border border-dashed border-white/10 rounded-xl">
                                            Zero topic clusters generated yet. Click "Refresh Portfolio" to analyze documents.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Drawer Footer Actions -->
                    <div class="pt-3 border-t border-white/10 flex items-center gap-2">
                        @if ($selectedRun->status->value !== 'completed')
                            <button
                                wire:click="stepWorkflow({{ $selectedRun->id }})"
                                wire:loading.attr="disabled"
                                class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold cursor-pointer disabled:opacity-50 transition-all shadow-md"
                            >
                                Step Next Node ⏭
                            </button>
                            @if ($isAutoRunning)
                                <button
                                    wire:click="stopAutoRun"
                                    class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold cursor-pointer transition-all shadow-lg shadow-amber-600/30 animate-pulse"
                                >
                                    ⏸ Pause Auto-Run
                                </button>
                            @else
                                <button
                                    wire:click="startAutoRun({{ $selectedRun->id }})"
                                    wire:loading.attr="disabled"
                                    class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white text-xs font-bold cursor-pointer disabled:opacity-50 transition-all shadow-lg shadow-violet-600/20"
                                >
                                    Auto-Run All ⚡
                                </button>
                            @endif
                        @else
                            <a
                                href="{{ route('documents.editor', $selectedRun->document_id) }}"
                                wire:navigate
                                class="w-full py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 text-white text-xs font-bold text-center block shadow-lg shadow-emerald-600/20 transition-all hover:scale-[1.01]"
                            >
                                ✍️ Open Final Assembled TipTap Document
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- 5. Multi-Step Mission Studio Modal Dialog -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-xl animate-fade-in">
            <div class="w-full max-w-2xl rounded-3xl bg-slate-900 border border-violet-500/40 p-6 md:p-8 shadow-2xl space-y-6 max-h-[92vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-violet-600/20 border border-violet-500/40 flex items-center justify-center text-xl text-violet-300">🎯</span>
                        <div>
                            <h2 class="text-lg md:text-xl font-extrabold text-white tracking-tight">Initialize Content Mission</h2>
                            <p class="text-xs text-slate-400">Configure strategy, fact governance, and research constraints</p>
                        </div>
                    </div>
                    <button wire:click="closeCreateModal" class="w-8 h-8 rounded-xl hover:bg-white/10 flex items-center justify-center text-slate-400 hover:text-white text-sm cursor-pointer">✕</button>
                </div>

                <!-- Quick Preset Selection Bar -->
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">⚡ Quick Strategy Presets</div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button
                            type="button"
                            wire:click="applyPreset('technical_teardown')"
                            class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $selectedPreset === 'technical_teardown' || $selectedPreset === 'technical_guide' ? 'bg-violet-600/30 border-violet-400 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                        >
                            <div class="text-xs font-bold">🛠 Tech Teardown</div>
                            <div class="text-[10px] text-slate-400">Deep architecture</div>
                        </button>

                        <button
                            type="button"
                            wire:click="applyPreset('comparative_roundup')"
                            class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $selectedPreset === 'comparative_roundup' || $selectedPreset === 'seo_pillar' ? 'bg-violet-600/30 border-violet-400 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                        >
                            <div class="text-xs font-bold">⚖️ Top Alternatives</div>
                            <div class="text-[10px] text-slate-400">Roundup & specs</div>
                        </button>

                        <button
                            type="button"
                            wire:click="applyPreset('thought_leadership')"
                            class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $selectedPreset === 'thought_leadership' ? 'bg-violet-600/30 border-violet-400 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                        >
                            <div class="text-xs font-bold">💡 Opinion & Angle</div>
                            <div class="text-[10px] text-slate-400">Contrarian thesis</div>
                        </button>

                        <button
                            type="button"
                            wire:click="applyPreset('executive_strategy')"
                            class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $selectedPreset === 'executive_strategy' || $selectedPreset === 'executive_brief' ? 'bg-violet-600/30 border-violet-400 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                        >
                            <div class="text-xs font-bold">📈 Exec Strategy</div>
                            <div class="text-[10px] text-slate-400">ROI & governance</div>
                        </button>
                    </div>
                </div>

                <form wire:submit="createMission" class="space-y-4">
                    <!-- Step 1: Topic & Scope -->
                    <div class="space-y-4">
                        <!-- AI Provider & Intelligence Model Selection -->
                        <div class="p-4 rounded-2xl bg-violet-950/20 border border-violet-500/30 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-violet-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🤖</span> AI Intelligence Engine & Gateway Model
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-300 font-mono">Dynamic Multi-Gateway</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">AI Provider Gateway</label>
                                    <select
                                        wire:model.live="selectedAiProviderId"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-xs focus:border-violet-500 focus:outline-none"
                                    >
                                        <option value="">Auto-Route (All Active Gateways)</option>
                                        @if(isset($aiProviders))
                                            @foreach($aiProviders as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->slug }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Target Model</label>
                                    <select
                                        wire:model="selectedAiModel"
                                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-xs focus:border-violet-500 focus:outline-none"
                                    >
                                        @if(isset($aiModels))
                                            @foreach($aiModels as $m)
                                                @if(!$selectedAiProviderId || $m->ai_provider_id == $selectedAiProviderId)
                                                    <option value="{{ $m->model_id }}">{{ $m->name }} ({{ $m->model_id }})</option>
                                                @endif
                                            @endforeach
                                        @endif
                                        <option value="auto">Auto Model (OmniRoute Gateway)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Article Archetype Selection Matrix -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                    Article Archetype & Structural Blueprint <span class="text-rose-400">*</span>
                                </label>
                                <span class="text-[10px] text-violet-400 font-medium">Adaptive 6-Layout Engine</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <button
                                    type="button"
                                    wire:click="$set('articleArchetype', 'auto_detect')"
                                    class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $articleArchetype === 'auto_detect' ? 'bg-violet-600/30 border-violet-400 shadow-lg shadow-violet-600/20 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center gap-1.5 text-xs font-bold">
                                        <span>✨</span> Auto-Detect
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">AI infers optimal outline</div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyPreset('comparative_roundup')"
                                    class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $articleArchetype === 'comparative_roundup' ? 'bg-violet-600/30 border-violet-400 shadow-lg shadow-violet-600/20 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center gap-1.5 text-xs font-bold">
                                        <span>⚖️</span> Comparative Roundup
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Alternatives & spec matrix</div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyPreset('technical_teardown')"
                                    class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $articleArchetype === 'technical_teardown' ? 'bg-violet-600/30 border-violet-400 shadow-lg shadow-violet-600/20 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center gap-1.5 text-xs font-bold">
                                        <span>🛠</span> Tech Teardown
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Architecture & code internals</div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyPreset('step_by_step_tutorial')"
                                    class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $articleArchetype === 'step_by_step_tutorial' ? 'bg-violet-600/30 border-violet-400 shadow-lg shadow-violet-600/20 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center gap-1.5 text-xs font-bold">
                                        <span>📖</span> Step Tutorial
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Prereqs & code steps</div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyPreset('executive_strategy')"
                                    class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $articleArchetype === 'executive_strategy' ? 'bg-violet-600/30 border-violet-400 shadow-lg shadow-violet-600/20 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center gap-1.5 text-xs font-bold">
                                        <span>📈</span> Exec Strategy
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">ROI models & governance</div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="applyPreset('thought_leadership')"
                                    class="p-2.5 rounded-xl border text-left transition-all cursor-pointer {{ $articleArchetype === 'thought_leadership' ? 'bg-violet-600/30 border-violet-400 shadow-lg shadow-violet-600/20 text-white' : 'bg-slate-950/70 border-white/10 text-slate-300 hover:border-white/20' }}"
                                >
                                    <div class="flex items-center gap-1.5 text-xs font-bold">
                                        <span>💡</span> Thought Leader
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Contrarian perspective</div>
                                </button>
                            </div>
                            @error('articleArchetype') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Topic / Primary Subject <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="topic"
                                placeholder="e.g. Production Redis Queue Scaling with Supervisor"
                                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none placeholder-slate-500 transition-colors"
                            />
                            @error('topic') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">
                                Primary Content Objective & Narrative Angle <span class="text-rose-400">*</span>
                            </label>
                            <textarea
                                wire:model="primaryObjective"
                                rows="3"
                                placeholder="e.g. Provide a rigorous architectural guide to scaling queue workers with zero worker drops, memory limits, and SIGTERM signal traps."
                                class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none placeholder-slate-500 transition-colors"
                            ></textarea>
                            @error('primaryObjective') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Step 2: Audience & Expertise Persona -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Target Persona</label>
                            <input
                                type="text"
                                wire:model="audiencePersona"
                                placeholder="e.g. Senior DevOps / Backend Engineer"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none placeholder-slate-500"
                            />
                            @error('audiencePersona') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Expertise Level</label>
                            <select
                                wire:model="expertiseLevel"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            >
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                                <option value="Expert">Expert</option>
                            </select>
                        </div>
                    </div>

                    <!-- Step 3: Risk Level & Research Tier -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Fact Risk Level</label>
                            <select
                                wire:model="riskLevel"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            >
                                <option value="low">Low (Standard common knowledge)</option>
                                <option value="medium">Medium (Secondary source verification)</option>
                                <option value="high">High (Mandatory primary source proof)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Research Budget Tier</label>
                            <select
                                wire:model="researchBudgetTier"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            >
                                <option value="quick">Quick (5 Tasks)</option>
                                <option value="standard">Standard (15 Tasks)</option>
                                <option value="deep">Deep (30 Tasks)</option>
                                <option value="expert">Expert (60+ Tasks)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Step 4: Target Word Count Envelope -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Min Target Words</label>
                            <input
                                type="number"
                                wire:model="minWords"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Max Target Words</label>
                            <input
                                type="number"
                                wire:model="maxWords"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Estimated Reading Time Banner -->
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <span>Estimated Article Scope:</span>
                        <span class="font-bold text-violet-300 font-mono">
                            ~{{ round(($minWords + $maxWords) / 2) }} words (approx. {{ round((($minWords + $maxWords) / 2) / 200) }} min read)
                        </span>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                        <button
                            type="button"
                            wire:click="closeCreateModal"
                            class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-medium cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-600 hover:from-violet-500 text-white text-sm font-bold shadow-xl shadow-violet-600/30 cursor-pointer transition-all hover:scale-[1.02]"
                        >
                            Launch Workflow Run 🚀
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
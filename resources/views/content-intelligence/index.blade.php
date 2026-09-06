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

<div class="hoa-content-intelligence-workspace space-y-6 pb-12">
    <!-- 1. Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 rounded-2xl bg-gradient-to-r from-slate-900/90 via-violet-950/40 to-slate-900/90 border border-violet-500/20 shadow-xl backdrop-blur-xl">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-xl bg-violet-600/20 border border-violet-500/40 flex items-center justify-center text-xl text-violet-300">🧠</span>
                <h1 class="text-2xl font-bold text-white tracking-tight">Content Intelligence Hub</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-500/20 text-violet-300 border border-violet-500/30">Dynamic Workflow Graph</span>
            </div>
            <p class="text-sm text-slate-400">Multi-stage research, Claim Graph verification, self-correcting Critic loop, and automated TipTap AST assembly.</p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                wire:click="openCreateModal" 
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-semibold shadow-lg shadow-violet-600/20 border border-violet-400/30 flex items-center gap-2 transition-all hover:scale-[1.02] active:scale-98 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>New Content Mission</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if ($statusMessage)
        <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 text-sm flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $statusMessage }}</span>
            </div>
            <button wire:click="$set('statusMessage', '')" class="text-emerald-400 hover:text-white text-xs cursor-pointer">✕</button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 text-sm flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-white text-xs cursor-pointer">✕</button>
        </div>
    @endif

    <!-- 2. Metric Cards Matrix -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/70 border border-white/10 backdrop-blur-md">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Missions</div>
            <div class="text-2xl font-bold text-white">{{ number_format($stats['total_missions']) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/70 border border-white/10 backdrop-blur-md">
            <div class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-1">Active Pipeline Runs</div>
            <div class="text-2xl font-bold text-indigo-300">{{ number_format($stats['active_runs']) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/70 border border-white/10 backdrop-blur-md">
            <div class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-1">Assembled Articles</div>
            <div class="text-2xl font-bold text-emerald-300">{{ number_format($stats['completed_runs']) }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/70 border border-white/10 backdrop-blur-md">
            <div class="text-xs font-semibold text-violet-400 uppercase tracking-wider mb-1">Avg Confidence</div>
            <div class="text-2xl font-bold text-violet-300">{{ $stats['average_confidence'] }}%</div>
        </div>
    </div>

    <!-- 3. Workflow Runs Pipeline List & Details Inspector -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Runs List (Left 7 Cols or 12 Cols if none selected) -->
        <div class="{{ $selectedRun ? 'lg:col-span-7' : 'lg:col-span-12' }} space-y-4">
            <div class="p-5 rounded-2xl bg-slate-900/80 border border-white/10 shadow-xl backdrop-blur-md">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <span>Active & Historical Workflow Runs</span>
                    </h2>

                    <!-- Filter buttons -->
                    <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-white/10 text-xs">
                        <button 
                            wire:click="$set('filterStatus', 'all')" 
                            class="px-2.5 py-1 rounded-lg transition-all {{ $filterStatus === 'all' ? 'bg-violet-600 text-white font-semibold' : 'text-slate-400 hover:text-white' }}"
                        >All</button>
                        <button 
                            wire:click="$set('filterStatus', 'running')" 
                            class="px-2.5 py-1 rounded-lg transition-all {{ $filterStatus === 'running' ? 'bg-violet-600 text-white font-semibold' : 'text-slate-400 hover:text-white' }}"
                        >Running</button>
                        <button 
                            wire:click="$set('filterStatus', 'completed')" 
                            class="px-2.5 py-1 rounded-lg transition-all {{ $filterStatus === 'completed' ? 'bg-violet-600 text-white font-semibold' : 'text-slate-400 hover:text-white' }}"
                        >Completed</button>
                    </div>
                </div>

                @if ($runs->isEmpty())
                    <div class="text-center py-12 border border-dashed border-white/10 rounded-xl">
                        <div class="text-3xl mb-2">🎯</div>
                        <p class="text-slate-400 text-sm mb-4">No content missions found. Initialize your first mission to launch the pipeline.</p>
                        <button 
                            wire:click="openCreateModal" 
                            class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold"
                        >
                            Create First Mission
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($runs as $run)
                            <div 
                                wire:key="run-{{ $run->id }}" 
                                class="p-4 rounded-xl border transition-all {{ $selectedRunId === $run->id ? 'bg-violet-950/30 border-violet-500/50 shadow-md shadow-violet-500/10' : 'bg-slate-950/60 border-white/5 hover:border-white/15' }}"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        @if ($run->status->value === 'completed')
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">COMPLETED</span>
                                        @elseif ($run->status->value === 'running')
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 animate-pulse">RUNNING</span>
                                        @elseif ($run->status->value === 'failed')
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">FAILED</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">QUEUED</span>
                                        @endif

                                        <h3 class="text-sm font-semibold text-white truncate max-w-xs sm:max-w-md">
                                            {{ $run->mission->topic ?? 'Untitled Mission' }}
                                        </h3>
                                    </div>

                                    <div class="text-xs text-slate-400">
                                        {{ $run->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-slate-400 my-2">
                                    <div>
                                        <span class="text-slate-500">Node:</span> 
                                        <span class="font-mono text-violet-300">{{ $run->current_node }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Confidence:</span> 
                                        <span class="text-slate-200">{{ round($run->overall_confidence * 100) }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Executed Nodes:</span> 
                                        <span class="text-slate-200">{{ $run->nodes->count() }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-500">Budget:</span> 
                                        <span class="text-slate-200 uppercase">{{ $run->mission->research_budget_tier->value ?? 'STANDARD' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons Row -->
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-white/5 mt-2">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            wire:click="selectRun({{ $run->id }})" 
                                            class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $selectedRunId === $run->id ? 'bg-violet-600 text-white' : 'bg-white/5 hover:bg-white/10 text-slate-300' }}"
                                        >
                                            Inspect Graph
                                        </button>

                                        @if ($run->document_id)
                                            <a 
                                                href="{{ route('documents.editor', $run->document_id) }}" 
                                                wire:navigate 
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600/30 hover:bg-emerald-600/40 text-emerald-200 border border-emerald-500/30 flex items-center gap-1"
                                            >
                                                <span>✍️ Open in TipTap</span>
                                            </a>
                                        @endif
                                    </div>

                                    @if ($run->status->value !== 'completed')
                                        <div class="flex items-center gap-2">
                                            <button 
                                                wire:click="stepWorkflow({{ $run->id }})" 
                                                wire:loading.attr="disabled"
                                                class="px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-600 hover:bg-indigo-500 text-white flex items-center gap-1 cursor-pointer disabled:opacity-50"
                                            >
                                                <span>Step Node</span>
                                            </button>

                                            <button 
                                                wire:click="runFullWorkflow({{ $run->id }})" 
                                                wire:loading.attr="disabled"
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white flex items-center gap-1 cursor-pointer disabled:opacity-50"
                                            >
                                                <span>⚡ Auto-Run All</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $runs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Selected Run Detail Drawer / Inspector (Right 5 Cols) -->
        @if ($selectedRun)
            <div class="lg:col-span-5 space-y-4">
                <div class="p-5 rounded-2xl bg-slate-900/90 border border-violet-500/30 shadow-2xl backdrop-blur-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div>
                            <h3 class="text-sm font-bold text-white">Graph Inspector #{{ $selectedRun->id }}</h3>
                            <div class="text-xs text-slate-400">{{ $selectedRun->mission->topic }}</div>
                        </div>
                        <button wire:click="selectRun(null)" class="text-slate-400 hover:text-white text-sm cursor-pointer">✕</button>
                    </div>

                    <!-- Workflow Progress Stepper -->
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Stage Progression</div>
                        <div class="space-y-1.5 max-h-80 overflow-y-auto pr-1">
                            @php
                                $orderedNodes = [
                                    'mission_intake' => 'Mission Intake',
                                    'search_intelligence' => 'Search Intelligence',
                                    'research_director' => 'Research Director',
                                    'knowledge_fabric' => 'Knowledge Fabric & Claims',
                                    'content_blueprint' => 'Content Blueprint',
                                    'adaptive_outline' => 'Adaptive Outline',
                                    'section_draftsman' => 'Section Draftsman & Critic',
                                    'seo_optimization' => 'SEO & Schema Optimizer',
                                    'media_enhancement' => 'Rich Media Enhancer',
                                    'master_assembly' => 'TipTap AST Assembly',
                                ];
                                $recordsMap = $selectedRun->nodes->keyBy('node_name');
                            @endphp

                            @foreach ($orderedNodes as $nodeKey => $nodeTitle)
                                @php
                                    $rec = $recordsMap->get($nodeKey);
                                    $isCurrent = ($selectedRun->current_node === $nodeKey && $selectedRun->status->value !== 'completed');
                                    $isDone = ($rec && $rec->status === 'success');
                                @endphp
                                <div class="flex items-center justify-between p-2.5 rounded-lg text-xs {{ $isCurrent ? 'bg-indigo-950/60 border border-indigo-500/40 text-indigo-200' : ($isDone ? 'bg-slate-950 border border-emerald-500/20 text-slate-300' : 'bg-slate-950/40 border border-white/5 text-slate-500') }}">
                                    <div class="flex items-center gap-2">
                                        @if ($isDone)
                                            <span class="text-emerald-400">✓</span>
                                        @elseif ($isCurrent)
                                            <span class="animate-spin text-indigo-400">⟳</span>
                                        @else
                                            <span class="text-slate-600">○</span>
                                        @endif
                                        <span class="font-medium {{ $isCurrent ? 'text-indigo-300 font-bold' : '' }}">{{ $nodeTitle }}</span>
                                    </div>

                                    <div>
                                        @if ($rec)
                                            <span class="font-mono text-[10px] text-slate-400">{{ $rec->latency_ms }}ms</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mission Details Card -->
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5 space-y-2 text-xs">
                        <div class="font-semibold text-violet-300">Mission Parameters</div>
                        <div><strong class="text-slate-400">Primary Objective:</strong> <span class="text-slate-300">{{ $selectedRun->mission->primary_objective }}</span></div>
                        <div><strong class="text-slate-400">Target Persona:</strong> <span class="text-slate-300">{{ $selectedRun->mission->target_audience['persona'] ?? 'Standard' }}</span></div>
                        <div><strong class="text-slate-400">Word Count:</strong> <span class="text-slate-300">{{ $selectedRun->mission->target_word_count_min }} - {{ $selectedRun->mission->target_word_count_max }} words</span></div>
                    </div>

                    <!-- Inspector Actions -->
                    @if ($selectedRun->status->value !== 'completed')
                        <div class="flex items-center gap-2 pt-2">
                            <button 
                                wire:click="stepWorkflow({{ $selectedRun->id }})" 
                                wire:loading.attr="disabled"
                                class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold cursor-pointer disabled:opacity-50"
                            >
                                Step Next Node
                            </button>
                            <button 
                                wire:click="runFullWorkflow({{ $selectedRun->id }})" 
                                wire:loading.attr="disabled"
                                class="flex-1 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold cursor-pointer disabled:opacity-50"
                            >
                                Auto-Run All
                            </button>
                        </div>
                    @else
                        <a 
                            href="{{ route('documents.editor', $selectedRun->document_id) }}" 
                            wire:navigate
                            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 text-white text-xs font-bold text-center block shadow-lg shadow-emerald-600/20"
                        >
                            Open Final Assembled TipTap Document
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- 4. New Content Mission Modal Dialog -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            <div class="w-full max-w-2xl rounded-3xl bg-slate-900 border border-violet-500/30 p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🎯</span>
                        <h2 class="text-lg font-bold text-white">Initialize Content Mission</h2>
                    </div>
                    <button wire:click="closeCreateModal" class="text-slate-400 hover:text-white text-sm cursor-pointer">✕</button>
                </div>

                <form wire:submit="createMission" class="space-y-4">
                    <!-- Topic Field -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Topic / Primary Subject <span class="text-rose-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="topic" 
                            placeholder="e.g. Production Redis Queue Scaling with Supervisor"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                        />
                        @error('topic') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Primary Objective -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                            Primary Content Objective & Angle <span class="text-rose-400">*</span>
                        </label>
                        <textarea 
                            wire:model="primaryObjective" 
                            rows="3"
                            placeholder="e.g. Provide a rigorous, step-by-step architectural guide to scaling queue workers with zero-downtime signal trapping and memory threshold management."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                        ></textarea>
                        @error('primaryObjective') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Two-Col: Persona & Expertise -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Target Persona</label>
                            <input 
                                type="text" 
                                wire:model="audiencePersona" 
                                placeholder="e.g. Senior DevOps / Backend Engineer"
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            />
                            @error('audiencePersona') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Expertise Level</label>
                            <select 
                                wire:model="expertiseLevel" 
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            >
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                                <option value="Expert">Expert</option>
                            </select>
                        </div>
                    </div>

                    <!-- Two-Col: Risk & Research Budget -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Fact Risk Level</label>
                            <select 
                                wire:model="riskLevel" 
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            >
                                <option value="low">Low (Standard common knowledge)</option>
                                <option value="medium">Medium (Requires secondary sources)</option>
                                <option value="high">High (Mandatory primary source proof)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Research Budget Tier</label>
                            <select 
                                wire:model="researchBudgetTier" 
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            >
                                <option value="quick">Quick (5 Tasks)</option>
                                <option value="standard">Standard (15 Tasks)</option>
                                <option value="deep">Deep (30 Tasks)</option>
                                <option value="expert">Expert (60+ Tasks)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Word Count Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Min Target Words</label>
                            <input 
                                type="number" 
                                wire:model="minWords" 
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Max Target Words</label>
                            <input 
                                type="number" 
                                wire:model="maxWords" 
                                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-white/15 text-white text-sm focus:border-violet-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                        <button 
                            type="button" 
                            wire:click="closeCreateModal" 
                            class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 text-sm font-medium"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white text-sm font-semibold shadow-lg shadow-violet-600/20"
                        >
                            Launch Workflow Run
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

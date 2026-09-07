{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Brain & Lineage Intelligence Tab
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

<!-- ─── TAB 9: 🧠 BRAIN & LINEAGE CONTENT INTELLIGENCE ─────────────────── -->
<div x-show="rightTab === 'brain'" class="space-y-3.5" style="display: none;" x-data="{ brainSubTab: 'lineage' }">

    <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-violet-500/20 space-y-3 shadow-inner font-mono text-xs backdrop-blur-md">

        {{-- Header & Sub-Tab Navigation --}}
        <div class="flex items-center justify-between pb-2 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-violet-400 animate-pulse"></span>
                <span class="text-xs font-extrabold text-white tracking-wider flex items-center gap-1.5">
                    <span>🧠</span>
                    <span>Neuro-Brain & Lineage</span>
                </span>
            </div>

            <button
                type="button"
                wire:click="loadBrainState"
                class="px-2 py-0.5 rounded-lg bg-violet-600/30 hover:bg-violet-600 text-violet-300 hover:text-white font-mono text-[10px] font-bold border border-violet-500/30 transition-all cursor-pointer flex items-center gap-1"
                wire:loading.attr="disabled"
                title="Synchronize real-time neuro-brain state"
            >
                <span wire:loading.remove wire:target="loadBrainState">↻ Sync</span>
                <span wire:loading wire:target="loadBrainState" class="flex items-center gap-1">
                    <svg class="animate-spin h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>

        {{-- Ambient Status Toast --}}
        @if (! empty($brainStatusMessage))
            <div class="p-2.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-[11px] flex items-center justify-between gap-2 animate-in">
                <span>✓ {{ $brainStatusMessage }}</span>
                <button type="button" wire:click="$set('brainStatusMessage', '')" class="text-emerald-400 hover:text-white text-xs">✕</button>
            </div>
        @endif

        {{-- Sub-Navigation Pills --}}
        <div class="grid grid-cols-5 gap-1 p-1 rounded-xl bg-slate-950/80 border border-white/10 text-[10px] select-none">
            <button
                type="button"
                x-on:click="brainSubTab = 'lineage'"
                :class="brainSubTab === 'lineage' ? 'bg-violet-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                title="7-Tier Sentence Lineage & Stale Alert"
            >
                Lineage
            </button>
            <button
                type="button"
                x-on:click="brainSubTab = 'health'"
                :class="brainSubTab === 'health' ? 'bg-violet-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                title="15-Dimension Content Health Assessment"
            >
                Health
            </button>
            <button
                type="button"
                x-on:click="brainSubTab = 'style'"
                :class="brainSubTab === 'style' ? 'bg-violet-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                title="Continuous Author Style Intelligence"
            >
                Style
            </button>
            <button
                type="button"
                x-on:click="brainSubTab = 'cannibalization'"
                :class="brainSubTab === 'cannibalization' ? 'bg-violet-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                title="Site Cannibalization Shield & Linking"
            >
                Cannibal
            </button>
            <button
                type="button"
                x-on:click="brainSubTab = 'genome'"
                :class="brainSubTab === 'genome' ? 'bg-violet-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                class="py-1 px-1 rounded-lg text-center transition-all cursor-pointer truncate"
                title="Content Genome Snapshot Synthesizer"
            >
                Genome
            </button>
        </div>

        {{-- ─── SUB-TAB 1: 7-TIER LINEAGE & STALE REPAIR ───────────────────── --}}
        <div x-show="brainSubTab === 'lineage'" class="space-y-3 pt-1">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-300 font-bold">Tracked Sentences: {{ count($brainLineageNodes) }}</span>
                @if ($brainStaleNodesCount > 0)
                    <span class="px-2 py-0.5 rounded-full bg-rose-950/90 text-rose-300 border border-rose-500/40 text-[10px] font-bold animate-pulse">
                        ⚠️ {{ $brainStaleNodesCount }} Stale Node{{ $brainStaleNodesCount > 1 ? 's' : '' }}
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-emerald-950/80 text-emerald-300 border border-emerald-500/30 text-[10px]">
                        ✓ All Facts Fresh
                    </span>
                @endif
            </div>

            {{-- Stale Node Action Banner --}}
            @if ($brainStaleNodesCount > 0)
                <div class="p-3 rounded-xl bg-gradient-to-r from-rose-950/80 via-amber-950/60 to-rose-950/80 border border-rose-500/40 space-y-2">
                    <div class="text-xs font-bold text-rose-200 flex items-center gap-1.5">
                        <span>🚨</span>
                        <span>Stale Fact Detected in Document</span>
                    </div>
                    <p class="text-[10.5px] text-slate-300 leading-relaxed font-sans">
                        Underlying research source facts changed. Click below to perform localized surgical micro-repair without re-drafting the document.
                    </p>
                    @foreach (array_filter($brainLineageNodes, fn($n) => !empty($n['is_stale'])) as $staleNode)
                        <div class="p-2 rounded-lg bg-slate-950/80 border border-rose-500/30 text-[10.5px] space-y-1.5">
                            <div class="text-slate-300 font-sans italic line-clamp-2">"{{ $staleNode['sentence_text'] }}"</div>
                            @if (! empty($staleNode['invalidation_reason']))
                                <div class="text-[10px] text-amber-300">Reason: {{ $staleNode['invalidation_reason'] }}</div>
                            @endif
                            <button
                                type="button"
                                wire:click="repairStaleSentence({{ $staleNode['id'] }})"
                                class="w-full py-1 px-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-[10.5px] shadow transition-all cursor-pointer flex items-center justify-center gap-1"
                            >
                                <span>⚡ Surgically Repair & Ground Fact</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Sentence Selector Grid --}}
            @if (! empty($brainLineageNodes))
                <div class="space-y-1.5">
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Select Sentence to Trace:</div>
                    <div class="max-h-36 overflow-y-auto space-y-1 pr-1 hoa-custom-scrollbar">
                        @foreach ($brainLineageNodes as $node)
                            <button
                                type="button"
                                wire:click="selectLineageNode({{ $node['id'] }})"
                                class="w-full text-left p-2 rounded-lg border text-[11px] font-sans transition-all cursor-pointer flex items-start gap-2 {{ $selectedLineageNodeId === $node['id'] ? 'bg-violet-950/60 border-violet-500/60 text-white shadow' : 'bg-slate-950/50 border-white/5 text-slate-300 hover:bg-white/5' }}"
                            >
                                <span class="text-xs shrink-0 mt-0.5">
                                    {{ !empty($node['is_stale']) ? '🔴' : '🟢' }}
                                </span>
                                <span class="truncate flex-1 font-normal">{{ $node['sentence_text'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 7-Tier Synaptic Trace Card --}}
            @if ($brainSelectedTrace)
                <div class="p-3 rounded-xl bg-slate-950/90 border border-violet-500/30 space-y-2 text-[11px]">
                    <div class="flex items-center justify-between border-b border-white/10 pb-1 text-violet-300 font-bold">
                        <span>7-Tier Synaptic Lineage Trace</span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-violet-950 border border-violet-500/40">Node #{{ $brainSelectedTrace['id'] }}</span>
                    </div>

                    <div class="space-y-1.5 font-sans">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase font-bold">Tier 1: Sentence</span>
                            <p class="text-slate-200 text-xs italic bg-slate-900/60 p-1.5 rounded-lg border border-white/5">"{{ $brainSelectedTrace['sentence_text'] }}"</p>
                        </div>

                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase font-bold">Tier 2: Claim</span>
                            <p class="text-slate-300 text-[11px]">{{ $brainSelectedTrace['claim_text'] ?: 'Synthetic verified claim node' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[10.5px]">
                            <div>
                                <span class="text-[9px] font-mono text-slate-400 uppercase font-bold">Tier 3: Epistemic State</span>
                                <div class="text-emerald-400 font-bold capitalize">{{ $brainSelectedTrace['claim_status'] ?: 'Verified' }}</div>
                            </div>
                            <div>
                                <span class="text-[9px] font-mono text-slate-400 uppercase font-bold">Tier 4: Status</span>
                                <div class="{{ !empty($brainSelectedTrace['is_stale']) ? 'text-rose-400 font-bold' : 'text-emerald-400' }}">
                                    {{ !empty($brainSelectedTrace['is_stale']) ? 'Stale Fact' : 'Fresh & Grounded' }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] font-mono text-slate-400 uppercase font-bold">Tier 5 & 6: Source & Citation</span>
                            <div class="text-indigo-300 text-[11px] truncate">{{ $brainSelectedTrace['source_title'] ?: 'Domain Documentation & World Model' }}</div>
                            @if (! empty($brainSelectedTrace['source_url']))
                                <a href="{{ $brainSelectedTrace['source_url'] }}" target="_blank" class="text-[10px] text-violet-400 hover:underline truncate block">
                                    🔗 {{ $brainSelectedTrace['source_url'] }}
                                </a>
                            @endif
                        </div>

                        @if (! empty($brainSelectedTrace['evidence_quote']))
                            <div>
                                <span class="text-[10px] font-mono text-slate-400 uppercase font-bold">Tier 7: Verbatim Evidence Quote</span>
                                <p class="text-slate-400 text-[10.5px] italic bg-slate-900/40 p-1.5 rounded border border-white/5">"{{ $brainSelectedTrace['evidence_quote'] }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ─── SUB-TAB 2: 15-DIMENSION CONTENT HEALTH ─────────────────────── --}}
        <div x-show="brainSubTab === 'health'" class="space-y-3 pt-1" style="display: none;">
            {{-- Scorecard Header --}}
            <div class="p-3 rounded-xl bg-gradient-to-r from-violet-950/60 to-indigo-950/60 border border-violet-500/30 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Overall Health Score</div>
                    <div class="text-xl font-extrabold text-white">{{ $brainHealthScore }}<span class="text-xs text-slate-400 font-normal"> / 100</span></div>
                </div>
                <div class="text-center px-3 py-1 rounded-xl bg-violet-600/30 border border-violet-400/40">
                    <div class="text-[9px] text-violet-300 uppercase font-bold">Grade</div>
                    <div class="text-lg font-black text-white">{{ $brainHealthGrade }}</div>
                </div>
            </div>

            {{-- 15-Dimension Matrix Bars --}}
            <div class="space-y-2">
                <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">15 Health Dimensions:</div>
                <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 hoa-custom-scrollbar">
                    @foreach ($brainHealthDimensions as $dim)
                        <div class="p-1.5 rounded-lg bg-slate-950/60 border border-white/5 text-[10.5px] space-y-1">
                            <div class="flex items-center justify-between font-sans">
                                <span class="text-slate-300 font-medium">{{ $dim['name'] }}</span>
                                <span class="font-mono font-bold {{ $dim['score'] >= 85 ? 'text-emerald-400' : ($dim['score'] >= 75 ? 'text-amber-400' : 'text-rose-400') }}">
                                    {{ round($dim['score']) }}%
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full {{ $dim['score'] >= 85 ? 'bg-emerald-400' : ($dim['score'] >= 75 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ $dim['score'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Actionable Recommendations --}}
            @if (! empty($brainHealthRecommendations))
                <div class="p-2.5 rounded-xl bg-slate-950/70 border border-white/10 space-y-1.5">
                    <div class="text-[10px] uppercase font-bold text-indigo-300">Actionable Health Optimizations:</div>
                    <ul class="text-[10.5px] font-sans text-slate-300 space-y-1">
                        @foreach ($brainHealthRecommendations as $rec)
                            <li class="flex items-start gap-1.5">
                                <span class="text-indigo-400 mt-0.5">•</span>
                                <span>{{ $rec }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- ─── SUB-TAB 3: CONTINUOUS AUTHOR STYLE RULES ───────────────────── --}}
        <div x-show="brainSubTab === 'style'" class="space-y-3 pt-1" style="display: none;">
            <div class="text-[11px] text-slate-300 leading-relaxed font-sans">
                The Autonomous Learning Engine analyzes your manual edits on prose during autosave to continuously master your personal author style.
            </div>

            @if (empty($brainStyleRules))
                <div class="p-4 rounded-xl bg-slate-950/60 border border-dashed border-white/10 text-center space-y-1 text-slate-400 text-xs font-sans">
                    <div class="text-base">✍️</div>
                    <p>No active style preferences yet.</p>
                    <p class="text-[10px] text-slate-500">Edit prose manually in the editor canvas; the engine automatically extracts your patterns upon autosave.</p>
                </div>
            @else
                <div class="space-y-2">
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Learned Style Rules:</div>
                    @foreach ($brainStyleRules as $rule)
                        <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/10 space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-violet-300 text-[11px]">{{ ucwords(str_replace('_', ' ', $rule['key'])) }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-violet-950 text-violet-300 border border-violet-500/30 text-[9px] font-mono font-bold">
                                    {{ $rule['confidence'] }}% Conf
                                </span>
                            </div>
                            <p class="text-[10.5px] font-sans text-slate-300 leading-relaxed">{{ $rule['description'] }}</p>
                            <div class="flex items-center justify-between text-[9.5px] text-slate-500 font-mono pt-1 border-t border-white/5">
                                <span>Evidence Diffs: {{ $rule['diff_count'] }}</span>
                                <button
                                    type="button"
                                    wire:click="toggleBrainStyleRule({{ $rule['id'] }}, {{ $rule['is_active'] ? 'false' : 'true' }})"
                                    class="text-[10px] font-bold {{ $rule['is_active'] ? 'text-emerald-400 hover:text-emerald-300' : 'text-slate-500 hover:text-slate-300' }} cursor-pointer"
                                >
                                    {{ $rule['is_active'] ? '● Active' : '○ Disabled' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── SUB-TAB 4: CANNIBALIZATION & LINKING MATRIX ────────────────── --}}
        <div x-show="brainSubTab === 'cannibalization'" class="space-y-3 pt-1" style="display: none;">
            {{-- Cannibalization Risks --}}
            <div class="space-y-1.5">
                <div class="text-[10px] uppercase font-bold text-amber-400 tracking-wider flex items-center gap-1">
                    <span>🛡️</span>
                    <span>Cannibalization Shield:</span>
                </div>
                @if (empty($brainCannibalization))
                    <div class="p-2.5 rounded-xl bg-emerald-950/50 border border-emerald-500/30 text-emerald-300 text-[11px] font-sans">
                        ✓ Zero topic cannibalization risks detected across your project portfolio.
                    </div>
                @else
                    @foreach ($brainCannibalization as $risk)
                        <div class="p-2.5 rounded-xl bg-amber-950/40 border border-amber-500/30 space-y-1 text-[11px] font-sans">
                            <div class="text-amber-200 font-bold flex items-center justify-between">
                                <span>Risk: {{ $risk['item_a'] ?? 'Article' }}</span>
                                <span class="text-[10px] font-mono text-amber-400">{{ $risk['similarity_percent'] ?? 70 }}% overlap</span>
                            </div>
                            <p class="text-[10.5px] text-slate-300">{{ $risk['recommendation'] ?? 'Differentiate search intent' }}</p>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Internal Linking Opportunities --}}
            <div class="space-y-1.5 pt-1">
                <div class="text-[10px] uppercase font-bold text-indigo-300 tracking-wider flex items-center gap-1">
                    <span>🔗</span>
                    <span>Internal Linking Matrix:</span>
                </div>
                @if (empty($brainInternalLinks))
                    <div class="p-2.5 rounded-xl bg-slate-950/60 border border-white/10 text-slate-400 text-[11px] font-sans">
                        Publish more sibling articles to unlock high-intent internal link equity.
                    </div>
                @else
                    @foreach ($brainInternalLinks as $link)
                        <div class="p-2 rounded-lg bg-slate-950/80 border border-white/10 text-[10.5px] font-sans space-y-0.5">
                            <div class="text-slate-300 font-medium truncate">Target: {{ $link['target'] ?? 'Sibling Article' }}</div>
                            <div class="text-[10px] text-indigo-300 font-mono">Anchor: "{{ $link['recommended_anchor'] ?? 'learn more' }}"</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ─── SUB-TAB 5: CONTENT GENOME SYNTHESIZER ──────────────────────── --}}
        <div x-show="brainSubTab === 'genome'" class="space-y-3 pt-1" style="display: none;">
            <div class="text-[11px] text-slate-300 leading-relaxed font-sans">
                Snapshots the complete structural, claim, entity, and quality DNA of this article into reusable memory so future articles can inherit verified facts without starting from zero.
            </div>

            @if ($brainGenomeSnapshot)
                <div class="p-3 rounded-xl bg-slate-950/90 border border-violet-500/30 space-y-2 text-[11px] font-sans">
                    <div class="flex items-center justify-between border-b border-white/10 pb-1 font-mono text-violet-300 font-bold">
                        <span>🧬 Genome Snapshot #{{ $brainGenomeSnapshot['id'] }}</span>
                        <span class="text-[10px] text-emerald-400 font-bold">Grade: {{ $brainGenomeSnapshot['quality_grade'] }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center text-[10px] font-mono py-1">
                        <div class="p-1.5 rounded-lg bg-white/5">
                            <div class="text-slate-400">Claims</div>
                            <div class="text-sm font-bold text-white">{{ $brainGenomeSnapshot['verified_claims_count'] }}</div>
                        </div>
                        <div class="p-1.5 rounded-lg bg-white/5">
                            <div class="text-slate-400">Facts</div>
                            <div class="text-sm font-bold text-white">{{ $brainGenomeSnapshot['facts_count'] }}</div>
                        </div>
                        <div class="p-1.5 rounded-lg bg-white/5">
                            <div class="text-slate-400">Entities</div>
                            <div class="text-sm font-bold text-white">{{ $brainGenomeSnapshot['entities_count'] }}</div>
                        </div>
                    </div>

                    <div class="text-[10px] font-mono text-slate-500 flex items-center justify-between">
                        <span>Sig: {{ $brainGenomeSnapshot['signature'] }}</span>
                        <span>Saved: {{ $brainGenomeSnapshot['updated_at'] }}</span>
                    </div>
                </div>
            @endif

            <button
                type="button"
                wire:click="synthesizeGenomeForCurrentDocument"
                class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-600 hover:from-violet-500 hover:to-purple-500 text-white font-mono text-xs font-bold shadow-md shadow-violet-600/30 transition-all cursor-pointer flex items-center justify-center gap-2"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="synthesizeGenomeForCurrentDocument">🧬 Synthesize Fresh Genome Snapshot</span>
                <span wire:loading wire:target="synthesizeGenomeForCurrentDocument" class="flex items-center gap-1.5">
                    <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Synthesizing DNA...
                </span>
            </button>
        </div>

    </div>
</div>

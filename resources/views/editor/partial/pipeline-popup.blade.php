{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - 15-Stage Pipeline Monitor Modal
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

<div 
    x-show="showPipelinePopup" 
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-3 sm:p-5 bg-slate-950/85 backdrop-blur-md select-none"
    @keydown.escape.window="showPipelinePopup = false"
    style="display: none;"
>
    <!-- Backdrop Click to Close -->
    <div class="fixed inset-0" @click="showPipelinePopup = false"></div>

    <!-- Modal Dialog Window -->
    <div 
        class="relative w-full max-w-3xl rounded-3xl glass-elevated border border-indigo-500/30 shadow-2xl flex flex-col max-h-[88vh] overflow-hidden bg-slate-900/95 text-slate-100 z-10"
        @click.stop
    >
        <!-- Modal Top Bar -->
        <div class="px-5 py-3.5 bg-indigo-950/70 border-b border-indigo-500/30 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm shadow-md shadow-indigo-500/30">
                    ⚡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs sm:text-sm font-extrabold text-white tracking-wide uppercase">
                            15-Stage Production Pipeline
                        </h3>
                        <span 
                            class="text-[9.5px] font-mono font-bold px-2 py-0.5 rounded-full border transition-all"
                            :class="isTransforming 
                                ? 'bg-indigo-600/30 border-indigo-400/50 text-indigo-300 animate-pulse' 
                                : 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300'"
                            x-text="isTransforming ? 'LIVE EXECUTING' : 'COMPLETED'"
                        ></span>
                    </div>
                    <p class="text-[10.5px] text-slate-400 truncate max-w-md" x-text="pipelinePopupData.topic ? 'Topic: ' + pipelinePopupData.topic : 'Multi-Agent Content Production Engine'"></p>
                </div>
            </div>

            <!-- (x) Close Button -->
            <button 
                type="button" 
                @click="showPipelinePopup = false" 
                class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/15 text-slate-400 hover:text-white flex items-center justify-center transition-colors cursor-pointer border border-white/10"
                title="Close Pipeline Popup (x)"
                aria-label="Close"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Progress Indicator Strip -->
        <div class="px-5 py-2.5 bg-slate-950/60 border-b border-white/5 shrink-0 flex flex-col gap-1.5">
            <div class="flex items-center justify-between text-[10px] font-mono">
                <div class="flex items-center gap-1.5 text-slate-300">
                    <span class="text-indigo-400">⚡ Status:</span>
                    <span class="font-bold text-white" x-text="getPipelineProgressLabel()"></span>
                </div>
                <div class="text-slate-400">
                    <span class="text-indigo-300 font-bold" x-text="getPipelineCompletedCount()"></span> / 15 Stages (<span x-text="Math.round((getPipelineCompletedCount() / 15) * 100) + '%'"></span>)
                </div>
            </div>
            
            <!-- Animated Progress Bar -->
            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden relative">
                <div 
                    class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-400 transition-all duration-300 rounded-full"
                    :style="'width: ' + Math.max(5, Math.round((getPipelineCompletedCount() / 15) * 100)) + '%'"
                ></div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="px-5 pt-3 pb-2 border-b border-white/5 flex items-center gap-2 shrink-0 bg-slate-900/40 text-xs font-mono">
            <button 
                type="button" 
                @click="pipelineActiveTab = 'stages'" 
                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 font-bold"
                :class="pipelineActiveTab === 'stages' 
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' 
                    : 'bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white'"
            >
                <span>🚀 15 Stages</span>
                <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-black/30" x-text="getPipelineCompletedCount() + '/15'"></span>
            </button>

            <button 
                type="button" 
                @click="pipelineActiveTab = 'intelligence'" 
                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 font-bold"
                :class="pipelineActiveTab === 'intelligence' 
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' 
                    : 'bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white'"
            >
                <span>🧠 Extracted Intelligence</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" x-show="pipelinePopupData.outline.length > 0 || pipelinePopupData.lsiKeywords"></span>
            </button>

            <button 
                type="button" 
                @click="pipelineActiveTab = 'log'" 
                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 font-bold"
                :class="pipelineActiveTab === 'log' 
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' 
                    : 'bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white'"
            >
                <span>📟 Execution Terminal</span>
                <span class="text-[9px] px-1.5 py-0.2 rounded-full bg-black/30" x-text="pipelineStageLog.length"></span>
            </button>
        </div>

        <!-- Tab 1: 15 Stages Grid -->
        <div x-show="pipelineActiveTab === 'stages'" class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-sans">
                <template x-for="stage in pipelinePopupData.stages" :key="stage.id">
                    <div 
                        class="p-3 rounded-2xl border transition-all flex items-start gap-2.5 select-none"
                        :class="stage.status === 'completed' 
                            ? 'bg-indigo-950/30 border-indigo-500/30 text-white' 
                            : (stage.status === 'running' 
                                ? 'bg-indigo-900/40 border-indigo-400/60 ring-1 ring-indigo-500/40' 
                                : 'bg-slate-950/40 border-white/5 text-slate-400 opacity-70')"
                    >
                        <!-- Stage Icon / Status Marker -->
                        <div class="shrink-0 mt-0.5">
                            <template x-if="stage.status === 'completed'">
                                <div class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex items-center justify-center text-[10px] text-emerald-400 font-bold">
                                    ✓
                                </div>
                            </template>
                            <template x-if="stage.status === 'running'">
                                <div class="w-5 h-5 rounded-full bg-indigo-500/30 border border-indigo-400 flex items-center justify-center text-[10px] text-indigo-300 font-bold animate-spin">
                                    ⚙
                                </div>
                            </template>
                            <template x-if="stage.status === 'pending'">
                                <div class="w-5 h-5 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center text-[9px] text-slate-500 font-mono" x-text="stage.id"></div>
                            </template>
                        </div>

                        <!-- Stage Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold truncate" :class="stage.status === 'completed' ? 'text-white' : (stage.status === 'running' ? 'text-indigo-300' : 'text-slate-400')" x-text="stage.icon + ' ' + stage.name"></span>
                                <span 
                                    class="text-[9px] font-mono uppercase px-1.5 py-0.2 rounded"
                                    :class="stage.status === 'completed' ? 'bg-emerald-950 text-emerald-300' : (stage.status === 'running' ? 'bg-indigo-950 text-indigo-300 animate-pulse' : 'text-slate-600')"
                                    x-text="stage.status"
                                ></span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-tight line-clamp-2" x-text="stage.detail"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab 2: Extracted Intelligence Data -->
        <div x-show="pipelineActiveTab === 'intelligence'" class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-4 font-mono text-xs">
            <!-- Topic & Keyword -->
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 space-y-1">
                <span class="text-[10px] uppercase font-bold text-indigo-400 block">Target Topic & SEO Keyword</span>
                <div class="text-sm font-bold text-white" x-text="pipelinePopupData.topic || 'Awaiting dispatch...'"></div>
            </div>

            <!-- Extracted LSI Entities -->
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-purple-400">Extracted LSI Entities & SERP Signals</span>
                    <button 
                        type="button" 
                        x-show="pipelinePopupData.lsiKeywords"
                        @click="navigator.clipboard.writeText(pipelinePopupData.lsiKeywords); alert('LSI Entities copied to clipboard!')" 
                        class="text-[9.5px] text-slate-400 hover:text-white underline cursor-pointer"
                    >Copy</button>
                </div>
                <div class="text-[11px] text-slate-300 leading-relaxed bg-slate-900/80 p-2.5 rounded-xl border border-white/5 select-all" x-text="pipelinePopupData.lsiKeywords || 'Keywords will be extracted during Stage 2.'"></div>
            </div>

            <!-- Section Outline Plan -->
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 space-y-2">
                <span class="text-[10px] uppercase font-bold text-emerald-400 block">Architected Section Outline (H2 / H3)</span>
                <template x-if="pipelinePopupData.outline.length === 0">
                    <div class="text-[11px] text-slate-500 italic">Outline hierarchy will appear once Stage 5 completes.</div>
                </template>
                <div class="space-y-1.5" x-show="pipelinePopupData.outline.length > 0">
                    <template x-for="(sec, idx) in pipelinePopupData.outline" :key="idx">
                        <div class="p-2 rounded-xl bg-slate-900/80 border border-white/5 flex items-start gap-2">
                            <span class="text-[10px] text-indigo-400 font-bold shrink-0 mt-0.5" x-text="'H2.' + (idx + 1)"></span>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-white truncate" x-text="sec.title"></div>
                                <div class="text-[10px] text-slate-400" x-text="sec.focus"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Schema JSON-LD Script Preview -->
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-cyan-400">Schema.org Article & FAQ Metadata</span>
                    <button 
                        type="button" 
                        x-show="pipelinePopupData.schemaJsonLd"
                        @click="navigator.clipboard.writeText(pipelinePopupData.schemaJsonLd); alert('Schema code copied!')" 
                        class="text-[9.5px] text-slate-400 hover:text-white underline cursor-pointer"
                    >Copy Schema</button>
                </div>
                <pre class="text-[10px] text-cyan-300 leading-tight bg-slate-900/90 p-2.5 rounded-xl border border-white/5 overflow-x-auto max-h-36 custom-scrollbar select-all" x-text="pipelinePopupData.schemaJsonLd || 'Schema.org JSON-LD will be generated in Stage 13.'"></pre>
            </div>
        </div>

        <!-- Tab 3: Execution Terminal Log -->
        <div x-show="pipelineActiveTab === 'log'" class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-2 font-mono text-xs">
            <template x-if="pipelineStageLog.length === 0">
                <div class="text-slate-500 italic text-center py-8">No pipeline events recorded yet. Run an AI transform to see live telemetry.</div>
            </template>
            <div class="space-y-1.5">
                <template x-for="(log, idx) in pipelineStageLog" :key="idx">
                    <div class="p-2 rounded-xl bg-slate-950/80 border border-white/5 flex items-start gap-2.5">
                        <span class="text-[9.5px] text-slate-500 shrink-0 font-mono mt-0.5" x-text="log.time"></span>
                        <div class="flex-1 text-[11px] text-slate-200" x-text="log.msg"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal Bottom Sticky Banner & Actions -->
        <div class="px-5 py-3 bg-slate-950/90 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2 text-[11px] text-slate-300">
                <span class="text-emerald-400 text-sm">✨</span>
                <span><strong>Editor Canvas Isolation:</strong> Only the publication-ready Final Article is written into your editor canvas.</span>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button 
                    type="button" 
                    @click="copyPipelineData()" 
                    class="px-3.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/15 text-slate-200 hover:text-white text-xs font-semibold transition-colors cursor-pointer"
                >
                    Copy Intelligence Report
                </button>
                <button 
                    type="button" 
                    @click="showPipelinePopup = false" 
                    class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer"
                >
                    Close (x)
                </button>
            </div>
        </div>
    </div>
</div>

{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas AI Diff Review Inspector
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

    <!-- Interactive Visual AI Red/Green Diff Review Inspector with Multi-Candidate Variations -->
    <div
        x-show="showDiffReview"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-3 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="mb-4 p-4 rounded-2xl bg-slate-950/98 border border-indigo-500/40 shadow-2xl backdrop-blur-2xl space-y-3 text-xs"
        style="display: none;"
    >
        <!-- Header Bar -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 pb-2.5 border-b border-white/10 select-none">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-indigo-400 animate-ping"></span>
                <span class="font-bold text-white tracking-tight flex items-center gap-1.5">
                    <span>✦ AI Diff Review</span>
                    <span class="text-[10px] font-mono text-indigo-300 px-2 py-0.5 rounded-full bg-indigo-950/80 border border-indigo-500/30" x-text="(pendingDiff.actionType || 'TRANSFORM').toUpperCase()"></span>
                </span>
            </div>

            <!-- Review Action Controls (Accept ✓, Keep Both, Reject ✕) -->
            <div class="flex items-center gap-2 font-mono">
                <button
                    type="button"
                    x-on:click="acceptAiDiff()"
                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/30 transition-all cursor-pointer active:scale-95"
                    title="Accept active variation and replace selection"
                >
                    <span>✓</span> <span>Accept & Apply</span>
                </button>
                <button
                    type="button"
                    x-on:click="keepBothDiff()"
                    class="hidden sm:flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 hover:text-white font-bold text-xs border border-indigo-500/30 transition-all cursor-pointer"
                    title="Keep both original and active variation"
                >
                    <span>⚡</span> <span>Keep Both</span>
                </button>
                <button
                    type="button"
                    x-on:click="rejectAiDiff()"
                    class="px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white font-bold text-xs border border-rose-500/30 transition-all cursor-pointer active:scale-95"
                    title="Discard AI proposed change"
                >
                    <span>✕</span> <span>Discard</span>
                </button>
            </div>
        </div>

        <!-- Candidate Variations Switcher & Quick Tone Presets -->
        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] font-mono bg-slate-900/80 p-2 rounded-xl border border-white/5 select-none">
            <!-- Variation Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto hoa-custom-scrollbar max-w-full">
                <span class="text-slate-400 text-[10px] uppercase font-bold shrink-0">Variations:</span>
                <template x-for="(candidate, cIdx) in (pendingDiff.candidates || [])" :key="cIdx">
                    <button
                        type="button"
                        x-on:click="selectCandidate(cIdx)"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1 shrink-0"
                        :class="activeCandidateIndex === cIdx ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/40 border border-indigo-400/50' : 'bg-slate-950/80 hover:bg-white/10 text-slate-400 hover:text-white border border-white/10'"
                    >
                        <span x-text="'#' + (cIdx + 1)"></span>
                        <span x-show="activeCandidateIndex === cIdx" class="text-emerald-300 text-[10px]">●</span>
                    </button>
                </template>
            </div>

            <!-- View Mode Switcher (Split vs Unified) & Style Presets -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Split / Unified Toggle -->
                <div class="flex items-center bg-slate-950 p-0.5 rounded-lg border border-white/10 text-[10px]">
                    <button
                        type="button"
                        x-on:click="diffViewMode = 'split'"
                        :class="diffViewMode === 'split' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
                        class="px-2 py-0.5 rounded-md transition-all cursor-pointer"
                        title="Side-by-Side Split View"
                    >
                        ◫ Split
                    </button>
                    <button
                        type="button"
                        x-on:click="diffViewMode = 'unified'"
                        :class="diffViewMode === 'unified' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
                        class="px-2 py-0.5 rounded-md transition-all cursor-pointer"
                        title="Unified Inline Git-Style Diff"
                    >
                        ≡ Unified
                    </button>
                </div>

                <span class="text-slate-600">|</span>

                <!-- Sliders & Modifiers Drawer Toggle -->
                <button
                    type="button"
                    x-on:click="showControlsDrawer = !showControlsDrawer"
                    :class="showControlsDrawer ? 'bg-indigo-600/30 text-indigo-300 border-indigo-500/50' : 'bg-white/5 text-slate-400 hover:text-white border-white/10'"
                    class="px-2 py-0.5 rounded-lg border text-[10px] font-bold flex items-center gap-1 transition-all cursor-pointer"
                    title="Fine-tune Intensity, Tone & Length Modifiers"
                >
                    <span>⚙️ Sliders</span>
                    <span class="text-[8px]" x-text="showControlsDrawer ? '▲' : '▼'"></span>
                </button>

                <!-- Regenerate Variation Button -->
                <button
                    type="button"
                    x-on:click="regenerateVariation()"
                    :disabled="isRegeneratingCandidate"
                    class="px-2.5 py-0.5 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-[10px] shadow-sm shadow-indigo-600/30 cursor-pointer transition-all disabled:opacity-50 flex items-center gap-1 active:scale-95"
                    title="Regenerate alternate candidate variation"
                >
                    <span>↻</span>
                    <span x-text="isRegeneratingCandidate ? 'Thinking...' : 'Regenerate'"></span>
                </button>
            </div>
        </div>

        <!-- 4. Live Before-vs-After SEO & Readability Delta Telemetry Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-900/90 p-2.5 rounded-xl border border-indigo-500/20 font-mono text-[11px] select-none">
            <!-- Metric 1: Word Count Change -->
            <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-950/70 border border-white/5">
                <span class="text-slate-400 text-[10px]">Words:</span>
                <div class="flex items-center gap-1">
                    <span class="text-slate-200 font-bold" x-text="computeMetricsDelta().newWords"></span>
                    <span
                        class="text-[9.5px] font-bold px-1 rounded"
                        :class="computeMetricsDelta().wordDelta >= 0 ? 'bg-emerald-950 text-emerald-400' : 'bg-amber-950 text-amber-400'"
                        x-text="(computeMetricsDelta().wordDelta >= 0 ? '+' : '') + computeMetricsDelta().wordDelta"
                    ></span>
                </div>
            </div>

            <!-- Metric 2: Readability Score Delta -->
            <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-950/70 border border-white/5">
                <span class="text-slate-400 text-[10px]">Readability:</span>
                <div class="flex items-center gap-1">
                    <span class="text-cyan-300 font-bold" x-text="computeMetricsDelta().newReadability.score + '/100'"></span>
                    <span
                        class="text-[9.5px] font-bold px-1 rounded"
                        :class="computeMetricsDelta().readabilityDelta >= 0 ? 'bg-emerald-950 text-emerald-400' : 'bg-rose-950 text-rose-400'"
                        x-text="(computeMetricsDelta().readabilityDelta >= 0 ? '+' : '') + computeMetricsDelta().readabilityDelta"
                    ></span>
                </div>
            </div>

            <!-- Metric 3: Focus Keyword Density Count -->
            <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-950/70 border border-white/5">
                <span class="text-slate-400 text-[10px] truncate max-w-[80px]" :title="'Focus Keyword: ' + computeMetricsDelta().targetKeyword">Focus KW:</span>
                <div class="flex items-center gap-1">
                    <span class="text-indigo-300 font-bold" x-text="computeMetricsDelta().newKwCount + 'x'"></span>
                    <span
                        class="text-[9.5px] font-bold px-1 rounded"
                        :class="computeMetricsDelta().kwDelta > 0 ? 'bg-emerald-950 text-emerald-400' : 'bg-slate-900 text-slate-400'"
                        x-text="(computeMetricsDelta().kwDelta >= 0 ? '+' : '') + computeMetricsDelta().kwDelta"
                    ></span>
                </div>
            </div>

            <!-- Metric 4: Power & Action Verbs -->
            <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-950/70 border border-white/5">
                <span class="text-slate-400 text-[10px]">Power Words:</span>
                <div class="flex items-center gap-1">
                    <span class="text-emerald-300 font-bold" x-text="computeMetricsDelta().newPowerCount"></span>
                    <span
                        class="text-[9.5px] font-bold px-1 rounded"
                        :class="computeMetricsDelta().powerDelta > 0 ? 'bg-emerald-950 text-emerald-400' : 'bg-slate-900 text-slate-400'"
                        x-text="(computeMetricsDelta().powerDelta >= 0 ? '+' : '') + computeMetricsDelta().powerDelta"
                    ></span>
                </div>
            </div>
        </div>

        <!-- Interactive AI Intensity & Tone Tuning Drawer -->
        <div
            x-show="showControlsDrawer"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="p-2.5 rounded-xl bg-slate-950 border border-indigo-500/25 grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-[11px] select-none"
            style="display: none;"
        >
            <!-- 1. Intensity (Temperature) Slider / Segmented -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <span class="font-bold text-slate-300">Creativity / Drift:</span>
                    <span class="text-indigo-400 font-bold" x-text="transformIntensity.toUpperCase()"></span>
                </div>
                <div class="grid grid-cols-3 gap-1 bg-slate-900 p-0.5 rounded-lg border border-white/5 text-[10px] text-center">
                    <button type="button" x-on:click="transformIntensity = 'conservative'" :class="transformIntensity === 'conservative' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="py-1 rounded cursor-pointer transition-all">Precise</button>
                    <button type="button" x-on:click="transformIntensity = 'balanced'" :class="transformIntensity === 'balanced' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="py-1 rounded cursor-pointer transition-all">Balanced</button>
                    <button type="button" x-on:click="transformIntensity = 'creative'" :class="transformIntensity === 'creative' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="py-1 rounded cursor-pointer transition-all">Creative</button>
                </div>
            </div>

            <!-- 2. Target Tone Preset -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <span class="font-bold text-slate-300">Tone Persona:</span>
                    <span class="text-emerald-400 font-bold" x-text="transformTone.toUpperCase()"></span>
                </div>
                <select x-model="transformTone" class="w-full bg-slate-900 border border-white/10 rounded-lg px-2 py-1 text-[10.5px] text-white focus:outline-none focus:border-indigo-500 cursor-pointer">
                    <option value="inherit">Matching Surrounding Document</option>
                    <option value="professional">Executive & Professional</option>
                    <option value="persuasive">High-Impact Persuasive Copy</option>
                    <option value="casual">Warm & Conversational</option>
                    <option value="academic">Academic & Analytical</option>
                </select>
            </div>

            <!-- 3. Target Length Modifier -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <span class="font-bold text-slate-300">Length Modifier:</span>
                    <span class="text-cyan-400 font-bold" x-text="transformLength.toUpperCase()"></span>
                </div>
                <div class="grid grid-cols-3 gap-1 bg-slate-900 p-0.5 rounded-lg border border-white/5 text-[10px] text-center">
                    <button type="button" x-on:click="transformLength = 'shorter'" :class="transformLength === 'shorter' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="py-1 rounded cursor-pointer transition-all">Shorter</button>
                    <button type="button" x-on:click="transformLength = 'same'" :class="transformLength === 'same' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="py-1 rounded cursor-pointer transition-all">Same</button>
                    <button type="button" x-on:click="transformLength = 'longer'" :class="transformLength === 'longer' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="py-1 rounded cursor-pointer transition-all">Longer</button>
                </div>
            </div>
        </div>

        <!-- 1. SPLIT VIEW: Side-by-Side Granular Word Diff Screens -->
        <div x-show="diffViewMode === 'split'" class="grid grid-cols-1 md:grid-cols-2 gap-3 font-mono text-[11.5px] leading-relaxed">
            <!-- Red Deleted / Previous Text with Granular Strikethrough -->
            <div class="rounded-xl bg-rose-950/40 border border-rose-500/30 p-3 space-y-1.5">
                <div class="flex items-center justify-between text-[10px] text-rose-400 font-bold uppercase tracking-wider pb-1 border-b border-rose-500/20 select-none">
                    <span class="flex items-center gap-1"><span>✕</span> <span>Original (Strikethrough = Deleted)</span></span>
                    <button type="button" x-on:click="rejectAiDiff()" class="text-[9.5px] text-rose-300 hover:text-white underline cursor-pointer">Keep Original</button>
                </div>
                <div class="max-h-52 overflow-y-auto text-rose-200/90 leading-relaxed hoa-custom-scrollbar select-text pr-1" x-html="getGranularDiff().oldHtml"></div>
            </div>

            <!-- Green New / Proposed Text with Granular Additions -->
            <div class="rounded-xl bg-emerald-950/40 border border-emerald-500/30 p-3 space-y-1.5">
                <div class="flex items-center justify-between text-[10px] text-emerald-400 font-bold uppercase tracking-wider pb-1 border-b border-emerald-500/20 select-none">
                    <span class="flex items-center gap-1">
                        <span>✓</span>
                        <span x-text="'AI Variation #' + (activeCandidateIndex + 1) + ' (Highlighted = Added)'"></span>
                        <span x-show="isRegeneratingCandidate" class="text-amber-400 text-[9px] animate-pulse">(Generating...)</span>
                    </span>
                    <button type="button" x-on:click="acceptAiDiff()" class="text-[9.5px] text-emerald-300 hover:text-white underline cursor-pointer">Apply This</button>
                </div>
                <div class="max-h-52 overflow-y-auto text-emerald-200/95 leading-relaxed hoa-custom-scrollbar select-text pr-1" x-html="getGranularDiff().newHtml"></div>
            </div>
        </div>

        <!-- 2. UNIFIED VIEW: Single Inline Git-Style Word Diff Screen -->
        <div x-show="diffViewMode === 'unified'" class="rounded-xl bg-slate-900/95 border border-indigo-500/30 p-3.5 space-y-2 font-mono text-[12px] leading-relaxed" style="display: none;">
            <div class="flex items-center justify-between text-[10px] text-indigo-300 font-bold uppercase tracking-wider pb-1 border-b border-white/10 select-none">
                <span class="flex items-center gap-2">
                    <span>≡ Unified Word-by-Word Diff View</span>
                    <span class="text-slate-500 text-[9px] font-normal">(<del class="text-rose-400 bg-rose-950/60 px-1 rounded">Red Strikethrough</del> = Removed, <ins class="text-emerald-400 bg-emerald-950/60 px-1 rounded no-underline">Green Highlight</ins> = Added)</span>
                </span>
                <span class="text-[9.5px] text-slate-400" x-text="'Variation #' + (activeCandidateIndex + 1)"></span>
            </div>
            <div class="max-h-60 overflow-y-auto hoa-custom-scrollbar select-text p-2 rounded-lg bg-slate-950/80 border border-white/5 pr-2" x-html="getGranularDiff().unifiedHtml"></div>
        </div>
    </div>


{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas & Overlays Partial
|--------------------------------------------------------------------------
|
| Features:
| 1. High-Performance Prose Canvas Container (wire:ignore Protected)
| 2. Direct Token Streaming Inside TipTap ProseMirror Canvas
| 3. In-Canvas AI Floating Prompt Bar (Cmd+K / Slash / / Command)
| 4. Local Draft Auto-Recovery Ambient Banner
| 5. Direct In-Canvas AI Generation Active Telemetry Stream Bar
| 6. Floating Selection AI Assistant Bar
| 7. Custom Right-Click Context Menu
| 8. Interactive Slash Commands Palette
|
*/
--}}

<div 
    class="editor-canvas"
    @contextmenu.prevent="openContextMenu($event)"
>
    <!-- Direct In-Canvas AI Generation Active Telemetry Stream Bar (Positioned at Top of Canvas & Formatting Ribbon) -->
    <div 
        x-show="isTransforming"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="mb-4 px-4 py-2 rounded-2xl bg-indigo-950/80 border border-indigo-500/50 shadow-xl backdrop-blur-xl flex items-center justify-between gap-3 text-xs animate-in"
        style="display: none;"
    >
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
            </span>
            <span class="font-bold text-white flex items-center gap-1.5">
                <span>✦ AI Typing Live in Editor Canvas...</span>
                <span class="text-[10px] font-mono text-indigo-300 px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30" x-text="routedModel"></span>
            </span>
        </div>

        <div class="flex items-center gap-3 font-mono text-xs">
            <span class="text-indigo-300 font-bold" x-text="streamSpeedTokSec + ' tok/s'"></span>
            <span class="text-slate-500">&bull;</span>
            <span class="text-slate-300" x-text="receivedTokens + ' tok'"></span>
            <button 
                type="button" 
                x-on:click="abortAiTransform()" 
                class="px-2.5 py-1 rounded-xl bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white font-bold text-xs transition-colors flex items-center gap-1 cursor-pointer"
            >
                <span>■</span> <span>Stop (Esc)</span>
            </button>
        </div>
    </div>

    <!-- Collapsible In-Canvas Master Formatting Ribbon -->
    @include('editor.partial.formatting-ribbon')

    <!-- In-Canvas Floating AI Prompt Bar (Cmd+K / / / Slash command) -->
    <div 
        x-show="showInlineAiPrompt"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="editor-floating-panel mb-6"
        style="display: none;"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse"></span>
                <span class="text-xs font-bold text-white flex items-center gap-1">
                    <span>✦ In-Canvas AI Assistant</span>
                    <span class="text-[10px] text-indigo-300 font-mono" x-text="'(' + aiModel + ')'"></span>
                </span>
            </div>
            <button type="button" x-on:click="showInlineAiPrompt = false" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕ Esc</button>
        </div>

        <!-- Active Selection Targeting Banner -->
        <template x-if="hasSelection && selectedText">
            <div class="p-2 rounded-xl bg-indigo-950/80 border border-indigo-500/40 flex flex-wrap items-center justify-between gap-2 text-xs font-mono">
                <div class="flex items-center gap-1.5 truncate max-w-sm text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                    <span class="text-indigo-300 font-bold">Selection:</span>
                    <span class="text-white italic truncate" x-text="'&ldquo;' + (selectedText.length > 50 ? selectedText.substring(0, 50) + '...' : selectedText) + '&rdquo;'"></span>
                </div>
                <div class="flex items-center gap-1 shrink-0 text-[10px]">
                    <button 
                        type="button" 
                        x-on:click="inlineAiPlacement = 'replace'" 
                        :class="inlineAiPlacement === 'replace' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900 text-slate-400 hover:text-white'"
                        class="px-2 py-0.5 rounded-lg border border-white/10 transition-colors cursor-pointer"
                    >
                        ✓ Replace Selection
                    </button>
                    <button 
                        type="button" 
                        x-on:click="inlineAiPlacement = 'insert_below'" 
                        :class="inlineAiPlacement === 'insert_below' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-slate-900 text-slate-400 hover:text-white'"
                        class="px-2 py-0.5 rounded-lg border border-white/10 transition-colors cursor-pointer"
                    >
                        ↓ Insert Below
                    </button>
                </div>
            </div>
        </template>

        <div class="flex items-center gap-2">
            <input 
                id="inline-ai-input"
                type="text" 
                x-model="inlineAiPrompt" 
                x-on:keydown.enter="submitInlineAiPrompt()" 
                placeholder="Instruct AI: e.g. Rewrite with technical depth, improve clarity, add comparison..."
                class="flex-1 bg-slate-950/90 border border-white/15 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-sans shadow-inner"
            />
            <button 
                type="button" 
                x-on:click="submitInlineAiPrompt()" 
                :disabled="isTransforming || !inlineAiPrompt.trim()"
                class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-1.5 transition-all disabled:opacity-50 cursor-pointer"
            >
                <span x-show="!isTransforming">✦ Generate</span>
                <span x-show="isTransforming" class="animate-spin text-xs">⟳</span>
                <span x-show="isTransforming">Writing...</span>
            </button>
        </div>

        <!-- Quick Prompt Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-[10.5px]">
            <span class="text-slate-400 font-mono text-[10px]">Shortcuts:</span>
            <button type="button" x-on:click="inlineAiPrompt = 'Polish and enhance the phrasing with authoritative technical depth'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">✨ Polish Phrasing</button>
            <button type="button" x-on:click="inlineAiPrompt = 'Expand this section with detailed real-world examples and architecture'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">+ Expand Depth</button>
            <button type="button" x-on:click="inlineAiPrompt = 'Create a structured comparison table analyzing pros, cons, and metrics'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">📊 Comparison Table</button>
            <button type="button" x-on:click="inlineAiPrompt = 'Generate 4 high-value schema FAQ questions and authoritative answers'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">❓ FAQ Block</button>
        </div>
    </div>

    <!-- Local Draft Auto-Recovery Ambient Banner -->
    <div 
        x-show="showRestoredDraftBanner" 
        x-cloak
        x-transition
        class="mb-4 p-3 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 shadow-xl backdrop-blur-xl flex flex-wrap items-center justify-between gap-3 text-xs font-sans animate-in"
        style="display: none;"
    >
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-white font-medium">
                <strong class="text-emerald-300">✦ Unsaved Draft Auto-Restored:</strong>
                <span class="text-slate-300" x-text="'Recovered ' + restoredWordCount + ' words from local backup (' + restoredDraftTime + ')'"></span>
            </span>
        </div>

        <div class="flex items-center gap-2 font-mono text-[11px]">
            <button type="button" x-on:click="dismissRestoredBanner()" class="px-2.5 py-1 rounded-xl bg-emerald-600/30 hover:bg-emerald-600 text-emerald-200 hover:text-white font-bold transition-colors cursor-pointer">
                ✓ Keep & Sync
            </button>
            <button type="button" x-on:click="revertToServerBackup()" class="px-2.5 py-1 rounded-xl bg-slate-900 border border-white/10 text-slate-400 hover:text-red-400 transition-colors cursor-pointer">
                Revert to Server
            </button>
        </div>
    </div>

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

    <!-- Advanced TipTap Floating Selection Bubble Toolbar (Teleported to body to avoid backdrop-filter coordinate displacement) -->
    <template x-teleport="body">
        <div 
            id="tiptap-bubble-menu"
            x-ref="bubbleMenu"
            x-on:mousedown.prevent
            class="max-w-[calc(100vw-24px)] rounded-2xl bg-slate-950/98 border border-white/20 shadow-[0_20px_50px_rgba(0,0,0,0.95)] backdrop-blur-2xl p-1.5 flex flex-wrap items-center gap-1.5 text-xs select-none transition-all duration-150"
            style="display: none; z-index: var(--z-index-floating);"
        >
            <!-- 1. AI Actions Group -->
            <div class="flex items-center gap-1 bg-white/[0.04] p-0.5 rounded-xl border border-white/5" x-data="{ bubbleAiOpen: false }">
                <button 
                    type="button" 
                    x-on:mousedown.prevent
                    x-on:click="bubbleAiOpen = !bubbleAiOpen" 
                    class="px-2.5 py-1 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/30 cursor-pointer text-xs transition-transform active:scale-95"
                >
                    <span>✦ Ask AI</span>
                    <span class="text-[9px]">▼</span>
                </button>
                <div 
                    x-show="bubbleAiOpen" 
                    x-on:click.outside="bubbleAiOpen = false" 
                    x-on:mousedown.prevent
                    class="absolute left-0 mt-2 w-56 rounded-2xl bg-slate-900/98 border border-white/20 p-1.5 shadow-2xl z-50 space-y-0.5 backdrop-blur-2xl text-xs"
                    style="display: none;"
                >
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('recreate'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl bg-purple-950/80 hover:bg-purple-900/80 text-purple-200 hover:text-white flex items-center justify-between cursor-pointer font-bold border border-purple-500/40">
                        <span class="flex items-center gap-2"><span class="text-purple-400">🤖</span> <span>Recreate Paragraph</span></span>
                        <span class="text-[9px] font-mono px-1 rounded bg-purple-900 text-purple-300">AI</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('rewrite'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-cyan-400">↻</span> <span>Rewrite & Polish</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('expand'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-violet-400">+</span> <span>Expand with Depth</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('shorten'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-amber-400">−</span> <span>Shorten & Condense</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('simplify'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-pink-400">⚡</span> <span>Simplify (8th-Grade)</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('generate_faq'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-indigo-400">❓</span> <span>Generate FAQ on this</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('key_takeaways'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-teal-400">💡</span> <span>Extract Key Takeaways</span>
                    </button>
                </div>
            </div>

            <!-- 2. Inline Typography Group (Bold, Italic, Underline, Strike, Highlight, Code) -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('bold')" :class="activeFormats.bold ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-bold cursor-pointer transition-colors" title="Bold (Ctrl+B)">B</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('italic')" :class="activeFormats.italic ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg italic font-serif cursor-pointer transition-colors" title="Italic (Ctrl+I)">I</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('underline')" :class="activeFormats.underline ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg underline cursor-pointer transition-colors" title="Underline (Ctrl+U)">U</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('strike')" :class="activeFormats.strike ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg line-through cursor-pointer transition-colors" title="Strikethrough">S</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('highlight')" :class="activeFormats.highlight ? 'bg-amber-500/80 text-black font-bold shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors" title="Highlight">⬚</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('codeBlock')" :class="activeFormats.codeBlock ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono text-[11px] cursor-pointer transition-colors" title="Code Block">&lt;/&gt;</button>
            </div>

            <!-- 3. Headings & Blockquote Group -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 1)" :class="activeFormats.heading1 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono font-bold cursor-pointer transition-colors" title="Heading 1">H1</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 2)" :class="activeFormats.heading2 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono font-bold cursor-pointer transition-colors" title="Heading 2">H2</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 3)" :class="activeFormats.heading3 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono font-bold cursor-pointer transition-colors" title="Heading 3">H3</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('blockquote')" :class="activeFormats.blockquote ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-serif font-bold cursor-pointer transition-colors" title="Blockquote">"</button>
            </div>

            <!-- 4. Lists & Table Group -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('bulletList')" :class="activeFormats.bulletList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors" title="Bullet List">●</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('orderedList')" :class="activeFormats.orderedList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors text-[11px] font-bold" title="Numbered List">1.</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('taskList')" :class="activeFormats.taskList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors text-[11px]" title="Task Checklist">✓</button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertTable?.({ rows: 3, cols: 3, withHeaderRow: true })" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer transition-colors" title="Insert Table">▦</button>
            </div>

            <!-- 5. Alignment Group -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('left')" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer transition-colors" title="Align Left">⇤</button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('center')" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer transition-colors" title="Align Center">↔</button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('right')" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer transition-colors" title="Align Right">⇥</button>
            </div>
        </div>
    </template>

    <!-- Custom Right-Click Context Menu (Teleported to body to avoid backdrop-filter coordinate displacement) -->
    <template x-teleport="body">
        <div 
            id="hoa-editor-context-menu"
            x-show="showContextMenu"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            :style="showContextMenu ? `position: fixed; left: ${contextMenuX}px; top: ${contextMenuY}px; z-index: var(--z-index-overlay);` : 'display: none !important;'"
            class="editor-floating-panel w-64 p-1.5 rounded-2xl bg-slate-950/98 border border-white/20 shadow-[0_25px_60px_rgba(0,0,0,0.95)] backdrop-blur-2xl text-xs select-none space-y-1"
            x-on:click.outside="closeContextMenu()"
            x-on:contextmenu.prevent
            style="display: none;"
        >
            <!-- Menu Header -->
            <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-indigo-400 tracking-wider flex items-center justify-between border-b border-white/10 mb-1 select-none">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                    <span>✦ AI & Editor Context</span>
                </span>
                <span class="text-slate-500 text-[9px] font-mono">Menu</span>
            </div>

            <!-- SECTION 1: CLIPBOARD & SELECTION ACTIONS -->
            <div class="space-y-0.5">
                <button type="button" x-on:click="closeContextMenu(); cutSelection()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>✂️</span> <span>Cut</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+X</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); copySelection()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>📋</span> <span>Copy</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+C</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); pasteClipboard()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>📄</span> <span>Paste</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+V</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); selectAllCanvas()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>🔲</span> <span>Select All</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+A</span>
                </button>
            </div>

            <div class="border-t border-white/10 my-1"></div>

            <!-- SECTION 2: AI REASONING & WRITING INTELLIGENCE (sub-content-sub-agent) -->
            <div id="sub-content-sub-agent" class="sub-content-sub-agent space-y-0.5">
                <div class="px-2 py-0.5 text-[9px] font-mono text-purple-400 font-bold flex items-center justify-between">
                    <span>sub-content-sub-agent</span>
                    <span class="text-[8px] text-slate-500">PARAGRAPH AI</span>
                </div>
                <button type="button" x-on:click="closeContextMenu(); openInlineAiPrompt()" class="w-full text-left px-2.5 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 font-bold flex items-center justify-between cursor-pointer transition-colors border border-indigo-500/30">
                    <span class="flex items-center gap-2"><span>✦</span> <span>Ask AI Inline...</span></span>
                    <span class="text-[10px] text-indigo-400 font-mono">Ctrl+K</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('recreate')" class="w-full text-left px-2.5 py-1.5 rounded-xl bg-purple-950/60 hover:bg-purple-900/60 text-purple-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors border border-purple-500/40 font-bold">
                    <span class="flex items-center gap-2"><span>🔄</span> <span>Recreate Paragraph (sub-agent)</span></span>
                    <span class="text-[9px] font-mono px-1 rounded bg-purple-900/80 text-purple-300">AI</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('rewrite')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-cyan-400">↻</span> <span>Rewrite & Polish</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('expand')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-violet-400">+</span> <span>Expand with Depth</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('shorten')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-amber-400">−</span> <span>Shorten & Condense</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('simplify')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-pink-400">⚡</span> <span>Simplify (8th-Grade)</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('generate_faq')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-indigo-400">❓</span> <span>Generate FAQ Block</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('key_takeaways')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-teal-400">💡</span> <span>Extract Key Takeaways</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('seo_optimize')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-emerald-400">⌁</span> <span>SEO Optimize Text</span>
                </button>
            </div>

            <!-- SECTION 3: TONE SHIFTER SUBMENU -->
            <div class="relative" x-data="{ toneSubOpen: false }">
                <button type="button" x-on:click.stop="toneSubOpen = !toneSubOpen" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>🎨</span> <span>Tone Shifter</span></span>
                    <span class="text-[10px] text-slate-400" x-text="toneSubOpen ? '▼' : '▶'"></span>
                </button>
                <div x-show="toneSubOpen" class="mt-1 p-1.5 bg-slate-950 rounded-xl border border-white/10 space-y-1 text-xs shadow-xl">
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:professional')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">👔 Executive & Professional</button>
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:casual')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">☕ Warm & Conversational</button>
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:persuasive')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">🎯 High-Impact Persuasive</button>
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:academic')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">📚 Academic & Analytical</button>
                </div>
            </div>

            <div class="border-t border-white/10 my-1"></div>

            <!-- SECTION 4: EDITORIAL QUICK INSERTERS -->
            <div class="space-y-0.5">
                <button type="button" x-on:click="closeContextMenu(); insertCurrentDate()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer transition-colors">
                    <span>📅</span> <span>Insert Today's Date</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); applyFormat('hr')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer transition-colors">
                    <span>—</span> <span>Insert Divider</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); deleteSelection()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-red-600/20 text-slate-400 hover:text-red-300 flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>🗑️</span> <span>Delete Selection</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Del</span>
                </button>
            </div>

            <div class="border-t border-white/10 my-1"></div>

            <button type="button" x-on:click="closeContextMenu()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/5 text-slate-400 hover:text-slate-200 text-[11px] cursor-pointer">
                ✕ Close Menu (Esc)
            </button>
        </div>
    </template>

    <!-- Interactive Floating Slash Commands Palette (Triggered on '/') -->
    <div 
        x-show="showSlashMenu"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            :style="`position: fixed; left: ${slashMenuX}px; top: ${slashMenuY}px; z-index: var(--z-index-floating);`"
            class="editor-floating-panel max-h-[380px] overflow-y-auto scrollbar-thin scrollbar-thumb-white/10"
            x-on:click.outside="showSlashMenu = false"
style="display: none;"
    >
        <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-indigo-400 tracking-wider flex items-center justify-between border-b border-white/10 mb-1 select-none">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                <span>✦ Slash AI Commands</span>
            </span>
            <span class="text-slate-500 text-[9px] font-mono">Type / to filter</span>
        </div>

        <!-- AI Actions Group -->
        <div class="space-y-0.5">
            <button type="button" x-on:click="executeSlashAction('ask_ai')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-indigo-300 hover:text-white font-bold flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-600/30 flex items-center justify-center text-xs">✦</span>
                <div>
                    <div>Ask AI Anything</div>
                    <div class="text-[10px] text-slate-400 font-normal">Generate custom text or prompt directly</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('continue_writing')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-violet-400">✍️</span>
                <div>
                    <div>Continue Writing</div>
                    <div class="text-[10px] text-slate-400">AI continues drafting the next thoughts</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('generate_outline')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-cyan-400">📑</span>
                <div>
                    <div>Article Outline</div>
                    <div class="text-[10px] text-slate-400">Generate structured headings tree</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('quick_answer')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-amber-400">⚡</span>
                <div>
                    <div>Quick Answer Box</div>
                    <div class="text-[10px] text-slate-400">Insert TL;DR search-intent snippet</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('faq')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-emerald-400">❓</span>
                <div>
                    <div>FAQ Schema Block</div>
                    <div class="text-[10px] text-slate-400">Generate high-intent Q&A section</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('comparison_table')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-indigo-400">📊</span>
                <div>
                    <div>Comparison Table</div>
                    <div class="text-[10px] text-slate-400">Generate structured pros/cons table</div>
                </div>
            </button>
        </div>

        <!-- Advanced Blog Components Group -->
        <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-violet-400 tracking-wider border-t border-white/10 mt-1 select-none">Editorial & Trust Blocks</div>
        <div class="space-y-0.5">
            <button type="button" x-on:click="executeSlashAction('tip')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-emerald-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>💡</span> <span>Pro-Tip Callout Box</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('warning')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-amber-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>⚠️</span> <span>Warning Precaution Box</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('proscons')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>⚖️</span> <span>Dual Pros & Cons Grid</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('faq_accordion')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>❓</span> <span>Interactive FAQ Accordion</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('trust_box')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>🏆</span> <span>E-E-A-T Testing Trust Box</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('step_timeline')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>🔢</span> <span>Step-by-Step Timeline</span>
            </button>
        </div>

        <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider border-t border-white/10 mt-1 select-none">Structure & Blocks</div>

        <!-- Standard Formatting Group -->
        <div class="space-y-0.5">
            <button type="button" x-on:click="executeSlashAction('h1')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H1</span> <span>Heading 1</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('h2')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H2</span> <span>Heading 2</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('h3')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H3</span> <span>Heading 3</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('h4')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H4</span> <span>Heading 4</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('bullet')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">●</span> <span>Bullet List</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('number')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400 font-mono">1.</span> <span>Numbered List</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('task')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">✓</span> <span>Task / Checklist</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('quote')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">"</span> <span>Blockquote</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('table')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400 font-mono">▦</span> <span>3x3 Data Table</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('code')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400 font-mono">&lt;/&gt;</span> <span>Code Block</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('divider')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">—</span> <span>Horizontal Divider</span>
            </button>
        </div>
    </div>

    <!-- SUB-CONTENT-SUB-AGENT In-Canvas Paragraph Proposal Inspector -->
    <div 
        x-show="showSubAgentProposal"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="ai-proposal-green-box mx-3 sm:mx-6 my-2 p-4 rounded-2xl bg-slate-950/98 border border-emerald-500/50 shadow-[0_20px_50px_rgba(0,0,0,0.9)] backdrop-blur-2xl text-xs space-y-3 shrink-0"
        style="display: none;"
    >
        <div class="ai-proposal-header flex items-center justify-between pb-2.5 border-b border-emerald-500/30 font-mono">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span class="text-emerald-400 font-bold tracking-wider text-xs">✦ SUB-CONTENT-SUB-AGENT (RECREATING)</span>
                <span class="text-[10px] text-slate-400 font-mono" x-text="streamSpeedTokSec > 0 ? (streamSpeedTokSec + ' tok/s') : ''"></span>
                <span class="text-[10px] text-indigo-300 font-mono px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30" x-text="routedModel"></span>
            </div>

            <div class="ai-proposal-actions flex items-center gap-2">
                <button 
                    type="button" 
                    x-show="!isTransforming && subAgentProposedText" 
                    x-on:click="acceptSubAgentProposal()" 
                    class="ai-btn-tick px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-600/40 transition-all cursor-pointer active:scale-95"
                    title="Accept AI recreation and replace yellow selection in document"
                >
                    ✓ Accept & Replace
                </button>
                <button 
                    type="button" 
                    x-on:click="discardSubAgentProposal()" 
                    class="ai-btn-cross px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white font-bold text-xs border border-rose-500/30 transition-all cursor-pointer active:scale-95"
                    title="Discard AI proposal and restore original text"
                >
                    ✕ Discard
                </button>
            </div>
        </div>

        <!-- Live Real-Time Token Content Body with Syntax and Typography -->
        <div class="ai-proposal-content max-h-72 overflow-y-auto hoa-custom-scrollbar p-3.5 rounded-xl bg-emerald-950/25 border border-emerald-500/25 text-emerald-100 text-sm leading-relaxed font-sans select-text shadow-inner break-words">
            <template x-if="isTransforming && !subAgentProposedText">
                <div class="flex items-center gap-2 text-emerald-400 animate-pulse font-mono text-xs py-2">
                    <span class="animate-spin text-sm">⟳</span> <span>sub-content-sub-agent is writing...</span>
                </div>
            </template>
            <div x-html="subAgentProposedText" class="prose prose-invert max-w-none text-slate-100 whitespace-pre-wrap break-words"></div>
            <span x-show="isTransforming && subAgentProposedText" class="inline-block w-2 h-4 bg-emerald-400 animate-pulse ml-0.5 align-middle"></span>
        </div>
    </div>

    <!-- Active Editor Engine Canvas Mount Target (wire:ignore for zero-latency, error-free typing & high-capacity 10,000+ words scrollbar) -->
    <div 
        id="tiptap-content-target" 
        class="flex-1 min-h-0 overflow-y-auto hoa-custom-scrollbar px-3 sm:px-6 py-4 scroll-smooth focus:outline-none" 
        wire:ignore
    ></div>
</div>

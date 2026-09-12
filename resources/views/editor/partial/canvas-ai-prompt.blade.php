{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas AI Stream & Prompt Panel
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

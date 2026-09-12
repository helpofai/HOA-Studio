{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas & Overlays Master Orchestrator
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
    class="editor-canvas"
    @contextmenu.prevent="openContextMenu($event)"
>
    <!-- Direct Token Stream Telemetry & Floating AI Prompt Bar -->
    @include('editor.partial.canvas-ai-prompt')

    <!-- Collapsible In-Canvas Master Formatting Ribbon -->
    <div x-show="!showSeoHeatmap">
        @include('editor.partial.formatting-ribbon')
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

    <!-- Visual AI Red/Green Diff Review Inspector -->
    @include('editor.partial.canvas-diff-review')

    <!-- Floating Selection Bubble Toolbar & Table Operations Toolbar -->
    @include('editor.partial.canvas-floating-toolbars')

    <!-- Custom Right-Click Context Menu & Slash Commands Palette -->
    @include('editor.partial.canvas-context-menus')

    <!-- Sub-Content Sub-Agent In-Canvas Paragraph Proposal Inspector -->
    @include('editor.partial.canvas-subagent-proposal')

    <!-- Active Editor Engine Canvas Mount Target -->
    <div
        id="tiptap-content-target"
        x-show="!showSeoHeatmap"
        class="flex-1 min-h-0 overflow-y-auto hoa-custom-scrollbar px-3 sm:px-6 py-4 scroll-smooth focus:outline-none"
        wire:ignore
    ></div>

    <!-- Dedicated Visual SEO & GEO Heatmap Inspection Mode Overlay -->
    <div
        x-show="showSeoHeatmap"
        x-cloak
        class="flex-1 min-h-0 overflow-y-auto hoa-custom-scrollbar px-3 sm:px-6 py-4 scroll-smooth focus:outline-none space-y-3"
    >
        <!-- Heatmap Floating Control Header Bar -->
        <div class="sticky top-0 z-20 p-3 rounded-2xl bg-slate-950/90 border border-indigo-500/40 shadow-xl backdrop-blur-xl flex flex-wrap items-center justify-between gap-3 text-xs font-mono select-none">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold text-white tracking-tight flex items-center gap-1.5">
                    <span>✦ Visual SEO & GEO Heatmap Mode</span>
                    <span class="text-[10px] text-indigo-300 px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30" x-text="(targetKeyword || (this.$wire ? this.$wire.targetKeyword : '')) ? ('Keyword: ' + (targetKeyword || (this.$wire ? this.$wire.targetKeyword : ''))) : 'Holistic Density'"></span>
                </span>
            </div>

            <!-- Heatmap Legend Indicators -->
            <div class="flex items-center gap-3 text-[11px]">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-emerald-500/40 border border-emerald-400"></span>
                    <span class="text-emerald-300">Optimal (1-2.5%)</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-amber-500/40 border border-amber-400"></span>
                    <span class="text-amber-300">Over-Optimized (&gt;3%)</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-indigo-500/40 border border-indigo-400"></span>
                    <span class="text-indigo-300">GEO Entity Anchor</span>
                </span>
            </div>

            <button
                type="button"
                x-on:click="toggleSeoHeatmap(false)"
                class="px-3 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white font-bold border border-white/10 transition-colors cursor-pointer"
            >
                ✕ Exit Heatmap
            </button>
        </div>

        <!-- Rendered Heatmap Markup -->
        <template x-if="seoHeatmapHtml">
            <div
                class="prose prose-invert max-w-none p-6 rounded-3xl bg-slate-900/60 border border-white/10 shadow-inner leading-relaxed select-text"
                x-html="seoHeatmapHtml"
                @click="handleHeatmapClick($event)"
            ></div>
        </template>

        <!-- Empty State Fallback -->
        <template x-if="!seoHeatmapHtml">
            <div class="p-12 text-center text-slate-400 font-sans space-y-3">
                <span class="text-3xl block">🔍</span>
                <p class="font-medium text-white">Analyzing Document Heatmap Density...</p>
                <p class="text-xs text-slate-500">Scanning H1/H2 subheadings, intro hook, LSI entity distribution, and keyword density.</p>
            </div>
        </template>
    </div>
</div>

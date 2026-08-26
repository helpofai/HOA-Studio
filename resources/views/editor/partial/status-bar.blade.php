{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Sticky Bottom Status Bar Partial
|--------------------------------------------------------------------------
|
| Features:
| 1. High-Performance Glass-Elevated Frosted Footer
| 2. Real-Time Dynamic Word, Character, Reading Time & Target Goal Progress
| 3. Dynamic Interactive SEO Health & Readability Score Badges ($wire reactive)
| 4. Live AI Model Routing & Speed (tok/s) Telemetry
| 5. Live Cloud Auto-Sync Pulse with Manual Snapshot Trigger (Ctrl+S)
| 6. Quick Panel Toggles (Focus Mode, SEO Drawer, AI Center)
|
*/
--}}

<div class="editor-statusbar">
    <!-- Left Section: Content Metrics & SEO Telemetry -->
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-3">
        <!-- Word Count with Target Progress -->
        <div class="flex items-center gap-1.5 bg-slate-900/80 px-2 sm:px-2.5 py-1 rounded-xl border border-white/5 shadow-inner">
            <span class="text-indigo-400 font-bold">📝</span>
            <span class="text-[11px] sm:text-xs">Words: <strong class="text-white font-bold" x-text="wordCount">0</strong></span>
            <span class="text-slate-600 hidden xs:inline">/</span>
            <span class="text-slate-400 hidden xs:inline" x-text="targetWordGoal">1200</span>
            <div class="w-8 sm:w-10 h-1.5 bg-slate-800 rounded-full overflow-hidden ml-1 hidden sm:block">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400 transition-all duration-300" :style="`width: ${Math.min(100, Math.round((wordCount / targetWordGoal) * 100))}%`"></div>
            </div>
        </div>

        <span class="text-slate-700 hidden sm:inline">&bull;</span>

        <!-- Character Count -->
        <div class="hidden md:flex items-center gap-1 text-[11px] sm:text-xs">
            <span>Chars: <strong class="text-slate-200 font-bold" x-text="characterCount">0</strong></span>
        </div>

        <span class="text-slate-700 hidden md:inline">&bull;</span>

        <!-- Reading Time -->
        <div class="flex items-center gap-1 text-[11px] sm:text-xs">
            <span>⏱️</span>
            <span><strong class="text-indigo-300 font-bold" x-text="readingTime + 'm'">1m</strong></span>
        </div>

        <span class="text-slate-700 hidden xs:inline">&bull;</span>

        <!-- SEO Score Badge (Clickable to open SEO Drawer, Livewire Reactive) -->
        <button 
            type="button" 
            x-on:click="if (!showRightPanel) { toggleRightPanel(); } rightTab = 'seo'"
            class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-900/80 hover:bg-white/10 border border-white/5 transition-colors cursor-pointer group text-[11px] sm:text-xs"
            title="Click to open Real-Time SEO Analyzer"
        >
            <span 
                class="w-2 h-2 rounded-full"
                :class="($wire.seoData?.score ?? 0) >= 80 ? 'bg-emerald-400' : (($wire.seoData?.score ?? 0) >= 60 ? 'bg-amber-400' : 'bg-red-400')"
            ></span>
            <span class="text-slate-400 group-hover:text-slate-200 hidden xs:inline">SEO:</span>
            <strong 
                class="font-bold"
                :class="($wire.seoData?.score ?? 0) >= 80 ? 'text-emerald-400' : (($wire.seoData?.score ?? 0) >= 60 ? 'text-amber-400' : 'text-red-400')"
                x-text="($wire.seoData?.score ?? 0) + '/100'"
            >0/100</strong>
        </button>

        <span class="text-slate-700 hidden xl:inline">&bull;</span>

        <!-- Readability Grade (Livewire Reactive) -->
        <div class="hidden xl:flex items-center gap-1">
            <span>Readability:</span>
            <strong 
                class="text-cyan-400 font-bold"
                x-text="($wire.seoData?.readability_score ?? 0) >= 60 ? 'Good' : 'Standard'"
            >Good</strong>
        </div>
    </div>

    <!-- Right Section: AI Model Routing, Telemetry, Cloud Sync & Terminal -->
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
        <!-- Live AI Streaming Speed (Visible during active transformation) -->
        <div x-show="streamSpeedTokSec > 0" x-cloak class="flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-950/80 border border-indigo-500/40 text-indigo-300 font-bold text-[10px] sm:text-[11px] animate-pulse">
            <span>⚡</span>
            <span x-text="streamSpeedTokSec + ' tok/s'"></span>
        </div>

        <!-- Active Model Indicator (Clickable to open AI Center) -->
        <button 
            type="button" 
            x-on:click="if (!showLeftPanel) { toggleLeftPanel(); }" 
            class="hidden sm:flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-900/80 hover:bg-white/10 border border-white/5 text-slate-300 hover:text-white transition-colors cursor-pointer text-[11px] sm:text-xs"
            title="Click to configure AI Router"
        >
            <span class="text-indigo-400">✦</span>
            <span class="text-slate-400">Model:</span>
            <strong class="text-white truncate max-w-[110px] sm:max-w-[130px]" x-text="aiModel">Auto</strong>
        </button>

        <span class="text-slate-700 hidden sm:inline">&bull;</span>

        <!-- Cloud Auto-Sync & Save Status Pill (Livewire Reactive) -->
        <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-2.5 py-1 rounded-xl bg-slate-900/70 border border-white/5 text-[10px] sm:text-[11px]">
            <span class="relative flex h-2 w-2">
                <span 
                    x-show="$wire.isSaving"
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"
                ></span>
                <span 
                    class="relative inline-flex rounded-full h-2 w-2"
                    :class="$wire.isSaving ? 'bg-amber-500' : 'bg-emerald-400'"
                ></span>
            </span>
            <span class="text-slate-300 font-medium truncate max-w-[90px] xs:max-w-none" x-text="$wire.isSaving ? 'Saving...' : ($wire.saveStatusText || 'Saved')">Saved</span>
        </div>

        <!-- Quick Manual Snapshot Save Button -->
        <button 
            type="button" 
            wire:click="saveExplicitSnapshot" 
            class="hidden lg:flex items-center gap-1 px-2.5 py-1 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 text-[11px] transition-colors cursor-pointer shadow-sm active:scale-95"
            title="Create manual document snapshot (Ctrl+S)"
        >
            <span>💾</span>
            <span>Save</span>
        </button>

        <!-- Focus Mode Toggle -->
        <button 
            type="button" 
            x-on:click="toggleFocusMode()" 
            class="p-1 rounded-lg hover:bg-white/10 text-slate-400 hover:text-white transition-colors cursor-pointer text-xs"
            :title="showLeftPanel || showRightPanel ? 'Enable Zen Focus Mode (Hide Panels)' : 'Exit Focus Mode'"
        >
            <span x-text="showLeftPanel || showRightPanel ? '⛶' : '🗗'"></span>
        </button>
    </div>
</div>

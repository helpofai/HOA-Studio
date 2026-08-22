{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Terminal UI Telemetry Console Partial
|--------------------------------------------------------------------------
*/
--}}

<div class="rounded-2xl bg-slate-950/95 border border-white/15 p-3.5 space-y-2.5 shadow-2xl font-mono text-xs overflow-hidden backdrop-blur-2xl">
    <!-- Terminal Header Bar with Window Dots & Status -->
    <div class="flex items-center justify-between pb-2 border-b border-white/10 select-none">
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500/80"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
            </div>
            <span class="text-[10px] text-slate-300 font-bold tracking-tight">ai-telemetry.log</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-950/60 border border-emerald-500/30 text-[9.5px] text-emerald-400 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>KERNEL LIVE</span>
            </span>
            <button type="button" x-on:click="clearLogs()" class="text-[10px] text-slate-500 hover:text-slate-300 transition-colors" title="Clear console">
                Clear
            </button>
        </div>
    </div>

    <!-- Log Filter Bar -->
    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5 font-mono">
        <div class="flex items-center gap-1">
            <button type="button" x-on:click="logFilter = 'ALL'" :class="logFilter === 'ALL' ? 'bg-indigo-600/30 text-indigo-300 font-bold border border-indigo-500/40' : 'hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">ALL</button>
            <button type="button" x-on:click="logFilter = 'AI'" :class="logFilter === 'AI' ? 'bg-cyan-600/30 text-cyan-300 font-bold border border-cyan-500/40' : 'hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">AI</button>
            <button type="button" x-on:click="logFilter = 'SEO'" :class="logFilter === 'SEO' ? 'bg-emerald-600/30 text-emerald-300 font-bold border border-emerald-500/40' : 'hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">SEO</button>
            <button type="button" x-on:click="logFilter = 'ERR'" :class="logFilter === 'ERR' ? 'bg-red-600/30 text-red-300 font-bold border border-red-500/40' : 'hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">ERR</button>
        </div>
        <span class="text-[9.5px] text-slate-500" x-text="filteredLogs.length + ' events'"></span>
    </div>

    <!-- Terminal Screen Log Stream with Auto-Scroll -->
    <div 
        id="terminal-logs-screen"
        class="rounded-xl bg-slate-900/90 border border-white/10 p-2.5 max-h-48 overflow-y-auto space-y-1 text-[10.5px] font-mono leading-snug scrollbar-thin scrollbar-thumb-white/10 select-text"
    >
        <template x-for="(log, idx) in filteredLogs" :key="idx">
            <div class="flex items-start gap-1.5 py-0.5 font-mono">
                <span class="text-slate-500 shrink-0 select-none text-[9.5px]" x-text="'[' + log.time + ']'"></span>
                <span 
                    class="px-1 py-0.2 rounded text-[9px] font-bold shrink-0"
                    :class="{
                        'bg-cyan-950 text-cyan-400 border border-cyan-500/30': log.level === 'SYSTEM' || log.level === 'INFO',
                        'bg-indigo-950 text-indigo-300 border border-indigo-500/30': log.level === 'STREAM' || log.level === 'GENERATE' || log.level === 'AI',
                        'bg-emerald-950 text-emerald-400 border border-emerald-500/30': log.level === 'SEO',
                        'bg-amber-950 text-amber-400 border border-amber-500/30': log.level === 'WARN',
                        'bg-red-950 text-red-400 border border-red-500/30': log.level === 'ERROR' || log.level === 'ERR'
                    }"
                    x-text="log.level"
                ></span>
                <span 
                    class="break-words text-slate-300"
                    :class="{
                        'text-indigo-200': log.level === 'STREAM' || log.level === 'GENERATE' || log.level === 'AI',
                        'text-emerald-300': log.level === 'SEO',
                        'text-amber-300': log.level === 'WARN',
                        'text-red-400 font-bold': log.level === 'ERROR' || log.level === 'ERR'
                    }"
                    x-text="log.msg"
                ></span>
            </div>
        </template>
        <div x-show="filteredLogs.length === 0" class="text-slate-600 italic py-1 text-[10px]">No logs recorded for this filter.</div>
    </div>
</div>

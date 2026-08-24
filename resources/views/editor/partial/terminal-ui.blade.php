{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Floating Movable AI Telemetry Terminal
|--------------------------------------------------------------------------
*/
--}}

<div 
    x-show="showTerminalModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    class="fixed z-[9999] w-[92vw] sm:w-[460px] md:w-[500px] rounded-2xl bg-slate-950/98 border border-white/20 shadow-[0_25px_60px_rgba(0,0,0,0.95)] backdrop-blur-2xl font-mono text-xs overflow-hidden select-none"
    :style="`left: ${terminalPos.x}px; top: ${terminalPos.y}px; cursor: ${isDraggingTerminal ? 'grabbing' : 'auto'};`"
>
    <!-- Draggable Window Title Header -->
    <div 
        x-on:mousedown="startDragTerminal($event)"
        x-on:touchstart="startDragTerminal($event)"
        class="flex items-center justify-between p-3 border-b border-white/10 bg-slate-900/90 cursor-grab active:cursor-grabbing hover:bg-slate-900 transition-colors"
    >
        <div class="flex items-center gap-2.5">
            <!-- Window Control Dots -->
            <div class="flex items-center gap-1.5">
                <button type="button" x-on:click.stop="showTerminalModal = false" class="w-3 h-3 rounded-full bg-red-500/90 hover:bg-red-400 cursor-pointer flex items-center justify-center text-[8px] text-black font-bold opacity-80 hover:opacity-100" title="Close Terminal">✕</button>
                <button type="button" x-on:click.stop="clearLogs()" class="w-3 h-3 rounded-full bg-amber-500/90 hover:bg-amber-400 cursor-pointer opacity-80 hover:opacity-100" title="Clear Logs"></button>
                <button type="button" x-on:click.stop="logFilter = 'ALL'" class="w-3 h-3 rounded-full bg-emerald-500/90 hover:bg-emerald-400 cursor-pointer opacity-80 hover:opacity-100" title="All Logs"></button>
            </div>
            <span class="text-[11px] text-white font-bold tracking-tight flex items-center gap-1.5">
                <span>📟</span>
                <span>ai-telemetry.log</span>
            </span>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-[9px] text-emerald-300 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>KERNEL LIVE</span>
            </span>
            <button 
                type="button" 
                x-on:click.stop="showTerminalModal = false" 
                class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 text-xs cursor-pointer transition-colors"
                title="Close"
            >
                ✕
            </button>
        </div>
    </div>

    <!-- Terminal Controls & Filter Tabs -->
    <div class="flex items-center justify-between px-3 py-2 bg-slate-950/90 border-b border-white/5 text-[10.5px] text-slate-400 font-mono">
        <div class="flex items-center gap-1">
            <button type="button" x-on:click="logFilter = 'ALL'" :class="logFilter === 'ALL' ? 'bg-indigo-600 text-white font-bold shadow-sm shadow-indigo-600/30' : 'hover:text-white hover:bg-white/5 text-slate-400'" class="px-2 py-0.5 rounded-lg transition-all cursor-pointer">ALL</button>
            <button type="button" x-on:click="logFilter = 'AI'" :class="logFilter === 'AI' ? 'bg-cyan-600 text-white font-bold shadow-sm shadow-cyan-600/30' : 'hover:text-white hover:bg-white/5 text-slate-400'" class="px-2 py-0.5 rounded-lg transition-all cursor-pointer">AI</button>
            <button type="button" x-on:click="logFilter = 'SEO'" :class="logFilter === 'SEO' ? 'bg-emerald-600 text-white font-bold shadow-sm shadow-emerald-600/30' : 'hover:text-white hover:bg-white/5 text-slate-400'" class="px-2 py-0.5 rounded-lg transition-all cursor-pointer">SEO</button>
            <button type="button" x-on:click="logFilter = 'ERR'" :class="logFilter === 'ERR' ? 'bg-red-600 text-white font-bold shadow-sm shadow-red-600/30' : 'hover:text-white hover:bg-white/5 text-slate-400'" class="px-2 py-0.5 rounded-lg transition-all cursor-pointer">ERR</button>
        </div>
        
        <div class="flex items-center gap-2">
            <span class="text-[10px] text-slate-500" x-text="filteredLogs.length + ' events'"></span>
            <button type="button" x-on:click="clearLogs()" class="text-[10px] text-slate-400 hover:text-slate-200 underline cursor-pointer">Clear</button>
        </div>
    </div>

    <!-- Live Real-Time Logs Terminal Screen -->
    <div 
        id="terminal-logs-screen"
        class="p-3 max-h-64 sm:max-h-72 overflow-y-auto space-y-1.5 text-[11px] font-mono leading-relaxed hoa-custom-scrollbar select-text bg-slate-950"
    >
        <template x-for="(log, idx) in filteredLogs" :key="idx">
            <div class="flex items-start gap-2 py-0.5 border-b border-white/[0.03]">
                <span class="text-slate-500 shrink-0 select-none text-[9.5px]" x-text="'[' + log.time + ']'"></span>
                <span 
                    class="px-1.5 py-0.2 rounded text-[9px] font-bold shrink-0"
                    :class="{
                        'bg-cyan-950 text-cyan-300 border border-cyan-500/30': log.level === 'SYSTEM' || log.level === 'INFO',
                        'bg-indigo-950 text-indigo-300 border border-indigo-500/30': log.level === 'STREAM' || log.level === 'GENERATE' || log.level === 'AI',
                        'bg-emerald-950 text-emerald-300 border border-emerald-500/30': log.level === 'SEO',
                        'bg-amber-950 text-amber-300 border border-amber-500/30': log.level === 'WARN',
                        'bg-red-950 text-red-300 border border-red-500/30': log.level === 'ERROR' || log.level === 'ERR'
                    }"
                    x-text="log.level"
                ></span>
                <span 
                    class="break-words text-slate-300 flex-1 leading-snug"
                    :class="{
                        'text-indigo-200': log.level === 'STREAM' || log.level === 'GENERATE' || log.level === 'AI',
                        'text-emerald-300': log.level === 'SEO',
                        'text-amber-300': log.level === 'WARN',
                        'text-red-300 font-bold': log.level === 'ERROR' || log.level === 'ERR'
                    }"
                    x-text="log.msg"
                ></span>
            </div>
        </template>
        <div x-show="filteredLogs.length === 0" class="text-slate-500 italic py-3 text-center text-xs">
            No telemetry events recorded for this filter.
        </div>
    </div>
</div>

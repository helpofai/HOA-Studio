{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Floating Telemetry Window
|--------------------------------------------------------------------------
*/
--}}
<div 
    x-show="showFloatingTelemetry" 
    x-cloak
    x-transition
    class="fixed z-[9999] shadow-2xl glass-elevated border border-indigo-500/30 rounded-2xl overflow-hidden flex flex-col"
    x-data="{
        dragging: false,
        resizing: false,
        startX: 0,
        startY: 0,
        startW: 0,
        startH: 0,
        x: window.innerWidth > 1024 ? window.innerWidth - 420 : 20,
        y: window.innerHeight > 1024 ? window.innerHeight - 500 : 80,
        w: 380,
        h: 400,
        startDrag(e) {
            this.dragging = true;
            const cx = e.clientX || (e.touches && e.touches[0].clientX);
            const cy = e.clientY || (e.touches && e.touches[0].clientY);
            this.startX = cx - this.x;
            this.startY = cy - this.y;
        },
        startResize(e) {
            e.preventDefault();
            e.stopPropagation();
            this.resizing = true;
            this.startX = e.clientX || (e.touches && e.touches[0].clientX);
            this.startY = e.clientY || (e.touches && e.touches[0].clientY);
            this.startW = this.w;
            this.startH = this.h;
        },
        doMove(e) {
            const cx = e.clientX || (e.touches && e.touches[0].clientX);
            const cy = e.clientY || (e.touches && e.touches[0].clientY);
            if (this.dragging) {
                this.x = Math.max(0, Math.min(window.innerWidth - this.w, cx - this.startX));
                this.y = Math.max(0, Math.min(window.innerHeight - 50, cy - this.startY));
            } else if (this.resizing) {
                this.w = Math.max(300, Math.min(window.innerWidth - this.x - 10, this.startW + (cx - this.startX)));
                this.h = Math.max(250, Math.min(window.innerHeight - this.y - 10, this.startH + (cy - this.startY)));
            }
        },
        endMove() {
            this.dragging = false;
            this.resizing = false;
        }
    }"
    :style="`left: ${x}px; top: ${y}px; width: ${w}px; height: ${h}px;`"
    @mousemove.window="doMove"
    @mouseup.window="endMove"
    @touchmove.window="doMove"
    @touchend.window="endMove"
>
    <!-- Window Header -->
    <div 
        @mousedown="startDrag"
        @touchstart.passive="startDrag"
        class="bg-indigo-950/90 border-b border-indigo-500/30 px-4 py-2.5 flex items-center justify-between cursor-move hover:bg-indigo-900 transition-colors shrink-0"
    >
        <div class="flex items-center gap-2 select-none">
            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-indigo-400 to-purple-400 animate-pulse" x-show="isTransforming"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-slate-600" x-show="!isTransforming"></span>
            <span class="text-xs font-bold text-white uppercase tracking-wider block">Pipeline Process</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFloatingTelemetry = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Window Body (Auto Scroll Log) -->
    <div class="p-4 bg-slate-950/95 flex-1 flex flex-col gap-3 min-h-0 relative">
        <div class="flex items-center justify-between shrink-0 border-b border-white/5 pb-2">
            <span class="text-[10px] uppercase font-bold text-indigo-400">Execution Log</span>
            <span class="text-[9px] px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-300 font-mono" x-text="pipelineStageLog.length + ' Stages'"></span>
        </div>

        <!-- Log Container -->
        <div id="pipeline-log-container" class="flex-1 overflow-y-auto custom-scrollbar space-y-2 pr-2">
            <template x-if="pipelineStageLog.length === 0">
                <div class="text-xs text-slate-500 italic py-4 text-center">Pipeline idle. Start an AI Transform to see real-time steps.</div>
            </template>
            <template x-for="(log, i) in pipelineStageLog" :key="i">
                <div class="flex gap-3 items-start animate-fade-in group">
                    <div class="shrink-0 flex flex-col items-center mt-0.5">
                        <div class="w-2 h-2 rounded-full" :class="i === pipelineStageLog.length - 1 && isTransforming ? 'bg-indigo-400 animate-pulse' : 'bg-emerald-500'"></div>
                        <div class="w-reverse h-full border-l border-white/10 my-1" x-show="i !== pipelineStageLog.length - 1"></div>
                    </div>
                    <div class="pb-3 border-b border-white/[0.03] w-full group-last:border-transparent">
                        <span class="text-[9px] font-mono text-slate-500 block mb-0.5" x-text="log.time"></span>
                        <span class="text-[11.5px] font-medium text-slate-200 leading-snug block" x-text="log.msg"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Resize Handle -->
    <div 
        @mousedown="startResize"
        @touchstart.passive="startResize"
        class="absolute bottom-0 right-0 w-5 h-5 cursor-se-resize flex items-end justify-end p-1 opacity-50 hover:opacity-100 z-50 bg-transparent"
    >
        <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2h-4M15 21l6-6M9 21H5a2 2 0 0 1-2-2v-4M3 15l6 6M21 9V5a2 2 0 0 0-2-2h-4M15 3l6 6"/></svg>
    </div>
</div>

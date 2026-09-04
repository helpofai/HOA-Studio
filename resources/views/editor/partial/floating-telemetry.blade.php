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
        startX: 0,
        startY: 0,
        x: window.innerWidth > 1024 ? window.innerWidth - 380 : 20,
        y: window.innerHeight > 1024 ? window.innerHeight - 350 : 80,
        startDrag(e) {
            this.dragging = true;
            this.startX = (e.clientX || (e.touches && e.touches[0].clientX)) - this.x;
            this.startY = (e.clientY || (e.touches && e.touches[0].clientY)) - this.y;
        },
        doDrag(e) {
            if (!this.dragging) return;
            const cx = e.clientX || (e.touches && e.touches[0].clientX);
            const cy = e.clientY || (e.touches && e.touches[0].clientY);
            // clamp
            this.x = Math.max(0, Math.min(window.innerWidth - 300, cx - this.startX));
            this.y = Math.max(0, Math.min(window.innerHeight - 100, cy - this.startY));
        },
        endDrag() {
            this.dragging = false;
        }
    }"
    :style="`left: ${x}px; top: ${y}px; width: auto; height: auto; resize: both; overflow: auto; min-width: 320px; min-height: 200px; max-width: 90vw; max-height: 80vh; transform: translate3d(0,0,0);`"
    @mousemove.window="doDrag"
    @mouseup.window="endDrag"
    @touchmove.window="doDrag"
    @touchend.window="endDrag"
>
    <!-- Window Header (Custom Drag Handle) -->
    <div 
        @mousedown="startDrag"
        @touchstart.passive="startDrag"
        class="bg-indigo-950/80 border-b border-indigo-500/30 px-4 py-2.5 flex items-center justify-between cursor-move hover:bg-indigo-900/80 transition-colors"
    >
        <div class="flex items-center gap-2 select-none">
            <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-indigo-400 to-purple-400 animate-pulse" x-show="isTransforming"></span>
            <span class="text-xs font-bold text-white uppercase tracking-wider block">AI Pipeline Telemetry</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFloatingTelemetry = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Window Body -->
    <div class="p-4 bg-slate-950/90 flex-1 flex flex-col gap-4 overflow-y-auto custom-scrollbar">
        <!-- Status Box -->
        <div class="bg-indigo-950/40 border-l-2 border-indigo-500 p-3 rounded-r-xl">
            <div class="text-[10px] font-bold text-indigo-300 uppercase mb-1">Live Agent Status</div>
            <div class="text-xs font-medium text-white break-words" x-text="swarmStatusMessage || 'Idle. Ready to receive commands.'"></div>
        </div>

        <!-- Telemetry Grids -->
        <div class="grid grid-cols-2 gap-3">
            <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Sent (Prompt)</span>
                <div class="text-sm font-black text-cyan-300">
                    <span x-text="sendTokens"></span> <span class="text-[10px] text-cyan-500 font-bold ml-1">tok</span>
                </div>
            </div>
            
            <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Received (Stream)</span>
                <div class="text-sm font-black text-emerald-300">
                    <span x-text="receivedTokens"></span> <span class="text-[10px] text-emerald-500 font-bold ml-1">tok</span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-3 mt-1">
            <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 space-y-1 flex justify-between items-end">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Speed</span>
                    <div class="text-sm font-black text-amber-300">
                        <span x-text="streamSpeedTokSec"></span> <span class="text-[10px] text-amber-500 font-bold ml-1">t/s</span>
                    </div>
                </div>
            </div>
            
            <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 space-y-1 flex justify-between items-end">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Latency (TTFT)</span>
                    <div class="text-sm font-black text-pink-300">
                        <span x-text="streamLatencyMs"></span> <span class="text-[10px] text-pink-500 font-bold ml-1">ms</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

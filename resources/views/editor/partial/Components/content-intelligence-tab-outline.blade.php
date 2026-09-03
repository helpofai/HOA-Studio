        <!-- ─── TAB 6: DOCUMENT OUTLINE TREE WITH CLICK-TO-SCROLL ─────────── -->
        <div x-show="rightTab === 'outline'" class="space-y-3" style="display: none;">
            <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-2.5 shadow-inner">
                <div class="flex items-center justify-between pb-1 border-b border-white/5">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-indigo-400">📝‘</span>
                        <span>Document Outline</span>
                    </span>
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('generate_outline')" 
                        class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-mono text-[10px] font-bold border border-indigo-500/30 transition-colors cursor-pointer"
                    >
                        ⚡ AI Structure
                    </button>
                </div>

                <div class="space-y-1 max-h-96 overflow-y-auto font-mono text-xs pr-1">
                    <template x-if="docOutline.length === 0">
                        <p class="text-slate-500 text-xs italic py-2">No headings detected yet. Add H1, H2, or H3 in the canvas.</p>
                    </template>
                    <template x-for="(item, idx) in docOutline" :key="idx">
                        <div 
                            x-on:click="scrollToHeading(item.text)"
                            class="p-2 rounded-xl hover:bg-white/10 transition-colors cursor-pointer text-slate-300 hover:text-white flex items-center justify-between gap-2"
                            :class="{
                                'pl-2 font-bold text-indigo-300 bg-indigo-950/30 border border-indigo-500/20': item.level === 1,
                                'pl-5 text-slate-300': item.level === 2,
                                'pl-8 text-slate-400 text-[11px]': item.level === 3
                            }"
                        >
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="text-[10px] font-bold text-slate-500" x-text="'H' + item.level"></span>
                                <span class="truncate" x-text="item.text"></span>
                            </div>
                            <span class="text-[9px] text-slate-500">Jump &rarr;</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>


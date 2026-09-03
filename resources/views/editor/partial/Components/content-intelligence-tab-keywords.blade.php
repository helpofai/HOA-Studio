        <!-- ─── TAB 4: KEYWORDS & REAL-TIME DENSITY MATRIX ────────────────── -->
        <div x-show="rightTab === 'keywords'" class="space-y-3.5" style="display: none;">
            <div class="space-y-3 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-indigo-400">🏷️</span>
                        <span>Keywords & Entity Matrix</span>
                    </label>
                    <button 
                        type="button" 
                        wire:click="suggestLsiKeywords" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="suggestLsiKeywords">⚡ AI Suggest</span>
                        <span wire:loading wire:target="suggestLsiKeywords" class="animate-pulse">...</span>
                    </button>
                </div>

                <!-- Add Keyword Input -->
                <div class="flex items-center gap-1.5">
                    <input 
                        type="text" 
                        wire:model="newSecondaryKeyword" 
                        wire:keydown.enter.prevent="addSecondaryKeyword"
                        placeholder="Add secondary / LSI keyword..." 
                        class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                    />
                    <button type="button" wire:click="addSecondaryKeyword" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer">+</button>
                </div>

                <!-- Keyword Occurrence & Density Matrix Table -->
                <div class="space-y-1.5">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Real-time Keyword Distribution:</span>
                    <div class="space-y-1 max-h-56 overflow-y-auto font-mono text-[11px] pr-1">
                        <!-- Primary Keyword Row -->
                        @if(!empty($targetKeyword))
                            <div class="p-2 rounded-xl bg-indigo-950/40 border border-indigo-500/30 flex items-center justify-between">
                                <div>
                                    <span class="text-white font-bold">{{ $targetKeyword }}</span>
                                    <span class="text-[9.5px] text-indigo-300 ml-1">(Primary)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-300 text-[10px] border border-emerald-500/30 font-bold">1.4% Density</span>
                                    <button type="button" x-on:click="triggerAiTransform('fix_kw_density')" class="text-indigo-400 hover:text-indigo-300 font-bold text-[10px]" title="Auto-Weave">⚡ Weave</button>
                                </div>
                            </div>
                        @endif

                        <!-- Secondary Keywords Rows -->
                        @forelse($secondaryKeywords as $index => $skw)
                            <div class="p-2 rounded-xl bg-slate-950/80 border border-white/5 flex items-center justify-between">
                                <span class="text-slate-300">{{ $skw }}</span>
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        x-on:click="triggerAiTransform('custom', 'Naturally integrate the secondary keyword \'' + @js($skw) + '\' into the document. Output clean HTML.')" 
                                        class="px-2 py-0.5 rounded bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-bold transition-colors"
                                    >
                                        ⚡ Auto-Weave
                                    </button>
                                    <button type="button" wire:click="removeSecondaryKeyword({{ $index }})" class="text-slate-400 hover:text-red-400 text-xs">✕</button>
                                </div>
                            </div>
                        @empty
                            <span class="text-slate-500 text-[11px] italic">No secondary keywords added yet.</span>
                        @endforelse
                    </div>
                </div>

                <!-- AI Suggested Keywords List -->
                @if(!empty($aiSeoResults) && $aiSeoType === 'lsi')
                    <div class="pt-2 border-t border-white/5 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400">AI Suggested Entities (Click + to add)</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach($aiSeoResults as $suggested)
                                <button 
                                    type="button" 
                                    wire:click="addSuggestedKeyword(@js($suggested))"
                                    class="px-2 py-0.5 rounded-md bg-slate-950 border border-indigo-500/20 hover:border-indigo-500/50 text-slate-300 hover:text-white text-[10.5px] font-mono flex items-center gap-1 cursor-pointer"
                                >
                                    <span>+</span> <span>{{ $suggested }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>


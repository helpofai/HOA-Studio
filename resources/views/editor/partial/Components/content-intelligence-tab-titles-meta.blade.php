        <!-- â”€â”€â”€ TAB 2: AI VIRAL TITLES & META DESCRIPTIONS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
        <div x-show="rightTab === 'titles_meta'" class="space-y-3.5" style="display: none;">
            <!-- Viral Titles Generator -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-violet-400">âœ¨</span>
                        <span>AI Viral Title Generator</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateSeoTitles" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateSeoTitles">âš¡ Generate Titles</span>
                        <span wire:loading wire:target="generateSeoTitles" class="animate-pulse">Generating...</span>
                    </button>
                </div>

                <!-- Current Title Input -->
                <div class="space-y-1">
                    <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Active Title Tag ({{ strlen($title) }} chars):</label>
                    <div class="flex items-center gap-1.5">
                        <input 
                            type="text" 
                            wire:model.lazy="title" 
                            class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-sans"
                        />
                        <button type="button" wire:click="applyTitle(title)" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs">Save</button>
                    </div>
                </div>

                <!-- Generated Suggestions -->
                <div class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">
                    @if(!empty($aiTitles))
                        @foreach($aiTitles as $t)
                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9.5px] font-mono text-emerald-400 bg-emerald-950/80 px-1.5 py-0.2 rounded border border-emerald-500/30">ðŸ”¥ High CTR</span>
                                    <span class="text-[9.5px] font-mono text-slate-500">{{ strlen($t) }} chars</span>
                                </div>
                                <div class="text-slate-200 text-xs leading-snug font-medium">{{ $t }}</div>
                                <button type="button" wire:click="applyTitle(@js($t))" class="w-full py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10.5px] font-bold transition-colors">
                                    âœ“ Apply Title to Document
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-slate-500 text-[11px] italic py-1">Click "Generate Titles" to generate search-optimized headline angles.</p>
                    @endif
                </div>
            </div>

            <!-- Meta Descriptions Generator -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-emerald-400">ðŸ“</span>
                        <span>AI Meta Description</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateMetaDescriptions" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateMetaDescriptions">âš¡ Generate Meta</span>
                        <span wire:loading wire:target="generateMetaDescriptions" class="animate-pulse">Generating...</span>
                    </button>
                </div>

                <!-- Active Meta Textarea -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[10px] font-mono">
                        <span class="uppercase font-bold text-slate-400">Active Meta Description:</span>
                        @php $metaLen = strlen($metaDescription ?? ''); @endphp
                        <span class="{{ $metaLen >= 140 && $metaLen <= 160 ? 'text-emerald-400 font-bold' : ($metaLen > 160 ? 'text-red-400' : 'text-amber-400') }}">
                            {{ $metaLen }}/160 chars
                        </span>
                    </div>
                    <textarea 
                        wire:model.lazy="metaDescription" 
                        rows="2" 
                        placeholder="Enter compelling meta description..." 
                        class="w-full bg-slate-950 border border-white/15 rounded-xl p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none font-sans leading-relaxed shadow-inner"
                    ></textarea>
                    <button type="button" wire:click="applyMetaDescription(metaDescription)" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold text-xs">Save Meta</button>
                </div>

                <!-- Generated Meta List -->
                <div class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">
                    @if(!empty($aiMetaDescriptions))
                        @foreach($aiMetaDescriptions as $meta)
                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5">
                                <p class="text-slate-300 text-xs leading-relaxed">{{ $meta }}</p>
                                <div class="flex items-center justify-between pt-1 text-[10px] font-mono">
                                    <span class="text-slate-500">{{ strlen($meta) }} chars</span>
                                    <button type="button" wire:click="applyMetaDescription(@js($meta))" class="px-2.5 py-1 rounded-lg bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white font-bold transition-colors">
                                        Use Meta
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-slate-500 text-[11px] italic py-1">Click "Generate Meta" for click-magnet snippet copy.</p>
                    @endif
                </div>
            </div>
        </div>


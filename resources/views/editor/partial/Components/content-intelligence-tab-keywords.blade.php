{{--
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Secondary & LSI Keywords Tab
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
--}}

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
                <span wire:loading wire:target="suggestLsiKeywords" class="animate-pulse">Analyzing...</span>
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
            <button type="button" wire:click="addSecondaryKeyword" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer shadow-md transition-all">+</button>
        </div>

        @php
            $plainContent = strip_tags($contentHtml ?? '');
            $lowerPlain = mb_strtolower($plainContent);
            $totalDocWords = max(1, $wordCount ?: str_word_count($plainContent));
            
            $pkCount = 0;
            $pkDensity = 0.0;
            if (!empty($targetKeyword)) {
                $pkCount = mb_substr_count($lowerPlain, mb_strtolower($targetKeyword));
                $pkWordsCount = count(preg_split('/\s+/u', trim($targetKeyword), -1, PREG_SPLIT_NO_EMPTY));
                $pkDensity = round((($pkCount * $pkWordsCount) / $totalDocWords) * 100, 2);
            }

            $pkStatusClass = match(true) {
                $pkDensity >= 0.8 && $pkDensity <= 2.5 => 'bg-emerald-950/80 text-emerald-300 border-emerald-500/30',
                $pkDensity > 2.5 => 'bg-rose-950/80 text-rose-300 border-rose-500/30',
                default => 'bg-amber-950/80 text-amber-300 border-amber-500/30',
            };
        @endphp

        <!-- Keyword Occurrence & Density Matrix Table -->
        <div class="space-y-1.5">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Real-time Keyword Distribution:</span>
            <div class="space-y-1 max-h-56 overflow-y-auto font-mono text-[11px] pr-1">
                <!-- Primary Keyword Row -->
                @if(!empty($targetKeyword))
                    <div class="p-2 rounded-xl bg-indigo-950/40 border border-indigo-500/30 flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <span class="text-white font-bold truncate block">{{ $targetKeyword }}</span>
                            <span class="text-[9.5px] text-indigo-300">(Primary Focus)</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="px-1.5 py-0.5 rounded text-[10px] border font-bold {{ $pkStatusClass }}" title="{{ $pkCount }} occurrences ({{ $pkDensity }}% density)">
                                {{ $pkDensity }}% ({{ $pkCount }}x)
                            </span>
                            <button type="button" x-on:click="triggerAiTransform('fix_kw_density')" class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold text-[10px] transition-colors cursor-pointer" title="Auto-Weave primary keyword naturally">
                                ⚡ Weave
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Secondary Keywords Rows -->
                @forelse($secondaryKeywords as $index => $skw)
                    @php
                        $skwCount = mb_substr_count($lowerPlain, mb_strtolower($skw));
                    @endphp
                    <div class="p-2 rounded-xl bg-slate-950/80 border border-white/5 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0 flex-1">
                            <span class="text-slate-300 truncate">{{ $skw }}</span>
                            @if($skwCount > 0)
                                <span class="px-1.5 py-0.2 rounded bg-emerald-950/80 text-emerald-300 border border-emerald-500/30 text-[9px] font-bold shrink-0">
                                    ✓ {{ $skwCount }}x
                                </span>
                            @else
                                <span class="px-1.5 py-0.2 rounded bg-slate-900 text-slate-500 border border-white/10 text-[9px] shrink-0">
                                    0x
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button 
                                type="button" 
                                x-on:click="triggerAiTransform('custom', 'Naturally integrate the secondary keyword \'' + @js($skw) + '\' into the document. Output clean HTML.')" 
                                class="px-2 py-0.5 rounded bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-bold transition-colors cursor-pointer"
                                title="Have AI naturally weave this keyword into the content"
                            >
                                ⚡ Weave
                            </button>
                            <button type="button" wire:click="removeSecondaryKeyword({{ $index }})" class="text-slate-400 hover:text-red-400 text-xs px-1 cursor-pointer" title="Remove keyword">✕</button>
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
                            class="px-2 py-0.5 rounded-md bg-slate-950 border border-indigo-500/20 hover:border-indigo-500/50 text-slate-300 hover:text-white text-[10.5px] font-mono flex items-center gap-1 cursor-pointer transition-colors"
                        >
                            <span>+</span> <span>{{ $suggested }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>


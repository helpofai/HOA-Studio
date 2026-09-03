        <!-- ─── TAB 3: AI CONTENT GAPS, FAQS, E-E-A-T & TABLES ────────────── -->
        <div x-show="rightTab === 'ai_ideas'" class="space-y-3.5" style="display: none;">
            <!-- Semantic Content Gaps -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-cyan-400">🎯</span>
                        <span>Semantic Content Gaps</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateContentGaps" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-cyan-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateContentGaps">⚡ Find Gaps</span>
                        <span wire:loading wire:target="generateContentGaps" class="animate-pulse">Analyzing...</span>
                    </button>
                </div>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1 text-xs">
                    @if(!empty($aiContentGaps))
                        @foreach($aiContentGaps as $gap)
                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5">
                                <div class="font-bold text-white text-[11px]">{{ $gap['topic'] ?? 'Missing Subtopic' }}</div>
                                <p class="text-[10.5px] text-slate-400 leading-snug">{{ $gap['reason'] ?? '' }}</p>
                                @if(!empty($gap['suggested_h2']))
                                    <div class="flex items-center gap-1.5 pt-1">
                                        <button 
                                            type="button" 
                                            x-on:click="insertContentIntoCanvas('<h2>' + @js($gap['suggested_h2']) + '</h2><p>Comprehensive coverage of ' + @js($gap['topic']) + '...</p>', true)"
                                            class="flex-1 py-1 rounded-lg bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white text-[10px] font-mono font-bold transition-all shadow-sm cursor-pointer"
                                        >
                                            + Insert Section: {{ $gap['suggested_h2'] }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-slate-500 text-[11px] italic py-1">Identify competitive subtopics and missing search intent angles.</p>
                    @endif
                </div>
            </div>

            <!-- Schema FAQ Generator -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-indigo-400">â“</span>
                        <span>Schema-Ready FAQs</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateFaqSuggestions" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateFaqSuggestions">⚡ Generate FAQs</span>
                        <span wire:loading wire:target="generateFaqSuggestions" class="animate-pulse">Generating...</span>
                    </button>
                </div>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1 text-xs">
                    @if(!empty($aiFaqs))
                        @foreach($aiFaqs as $faq)
                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5">
                                <div class="font-bold text-indigo-300 text-[11px]">Q: {{ $faq['question'] ?? '' }}</div>
                                <p class="text-[10.5px] text-slate-300 leading-relaxed">{{ $faq['answer'] ?? '' }}</p>
                                <button 
                                    type="button" 
                                    x-on:click="insertContentIntoCanvas('<h3>' + @js($faq['question']) + '</h3><p>' + @js($faq['answer']) + '</p>', true)"
                                    class="w-full py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-mono font-bold transition-all shadow-sm cursor-pointer"
                                >
                                    + Insert Q&A into Editor Canvas
                                </button>
                            </div>
                        @endforeach
                    @else
                        <p class="text-slate-500 text-[11px] italic py-1">Generate high-intent FAQ questions and answers.</p>
                    @endif
                </div>
            </div>

            <!-- Quick Answer / TL;DR Snippet -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-amber-400">⚡</span>
                        <span>Quick Answer Box</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateQuickAnswer" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-amber-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateQuickAnswer">⚡ Generate</span>
                        <span wire:loading wire:target="generateQuickAnswer" class="animate-pulse">...</span>
                    </button>
                </div>
                @if(!empty($aiQuickAnswer))
                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-amber-500/30 space-y-2 text-xs text-slate-200">
                        <div class="text-[11px] leading-relaxed">{!! $aiQuickAnswer !!}</div>
                        <button 
                            type="button" 
                            x-on:click="insertContentIntoCanvas('<blockquote><strong>Quick Answer:</strong> ' + @js($aiQuickAnswer) + '</blockquote>', true)"
                            class="w-full py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-[10.5px] shadow-md transition-all cursor-pointer"
                        >
                            + Insert Quick Answer Box into Intro
                        </button>
                    </div>
                @else
                    <p class="text-slate-500 text-[11px] italic py-1">Generate instant search-intent satisfying TL;DR snippet.</p>
                @endif
            </div>

            <!-- E-E-A-T Trust Card Generator -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-emerald-400">💡</span>
                        <span>E-E-A-T Methodology Box</span>
                    </span>
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('eeat_trust')" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-emerald-600/25 transition-all cursor-pointer"
                    >
                        ⚡ Generate & Insert
                    </button>
                </div>
                <p class="text-slate-400 text-[11px] leading-relaxed">
                    Automatically injects an editorial methodology, testing criteria, and author verification card to boost Google Trust signals.
                </p>
            </div>
        </div>


        <!-- ─── TAB 1: RANK MATH ENTERPRISE SEO ENGINE ───────────────────────── -->



        <div x-show="rightTab === 'seo'" class="space-y-3.5">



            @php



                $score = $seoData['score'] ?? 0;



                $scoreBadge = $score >= 80 ? ['class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40', 'glow' => 'from-emerald-500/20 to-teal-500/10', 'label' => 'GREAT (OPTIMIZED)', 'color' => 'text-emerald-400'] : ($score >= 50 ? ['class' => 'bg-amber-500/20 text-amber-300 border-amber-500/40', 'glow' => 'from-amber-500/20 to-yellow-500/10', 'label' => 'FAIR (NEEDS WORK)', 'color' => 'text-amber-400'] : ['class' => 'bg-red-500/20 text-red-300 border-red-500/40', 'glow' => 'from-red-500/20 to-rose-500/10', 'label' => 'POOR (CRITICAL)', 'color' => 'text-red-400']);



            @endphp







            <!-- Rank Math Master Score & Focus Keyword Header Card -->



            <div class="p-4 rounded-2xl bg-gradient-to-br {{ $scoreBadge['glow'] }} bg-slate-900/90 border border-white/10 space-y-3 shadow-xl">



                <div class="flex items-center justify-between">



                    <div>



                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Rank Math SEO Score</span>



                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-3xl font-black font-mono {{ $scoreBadge['color'] }}">{{ $score }}</span>
                            <span class="text-xs font-mono text-slate-400">/ 100</span>
                        </div>
                    </div>
                    <!-- SEO Heatmap Toggle -->
                    <button 
                        type="button" 
                        x-on:click="
                            showSeoHeatmap = !showSeoHeatmap; 
                            const ed = this.getEditor ? this.getEditor() : (typeof getEditor === 'function' ? getEditor() : null);
                            if(ed) {
                                if(showSeoHeatmap) {
                                    window._originalSeoDraft = ed.getHTML();
                                    ed.setContent($wire.seoData?.marked_html || '', false);
                                    ed.setEditable(false);
                                } else {
                                    ed.setContent(window._originalSeoDraft || '', false);
                                    ed.setEditable(true);
                                }
                            }
                        "
                        :class="showSeoHeatmap ? 'bg-indigo-600 text-white border-indigo-400 shadow-indigo-500/30' : 'bg-slate-950/50 text-slate-300 border-white/10 hover:text-white'"
                        class="px-2.5 py-1.5 rounded-xl border text-xs font-bold shadow-sm transition-all flex flex-col items-center justify-center h-[42px] cursor-pointer gap-0.5"
                        title="Toggle the Offline Color-Coded SEO Heatmap directly in the editor"
                    >
                        <svg x-show="showSeoHeatmap" style="display:none;" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span x-text="showSeoHeatmap ? 'CLOSE' : '👁️ HEATMAP'"></span>
                    </button>
                    







                    <div class="text-right space-y-1">



                        <span class="text-[9.5px] font-mono px-2 py-0.5 rounded-full border {{ $scoreBadge['class'] }} font-bold inline-block">



                            {{ $scoreBadge['label'] }}



                        </span>



                        <div class="text-[10px] text-slate-400 font-mono">



                            Readability: <strong class="text-cyan-300">{{ $seoData['readability_score'] ?? 0 }}/100</strong>



                        </div>



                    </div>



                </div>







                <!-- Focus Keyword Setting Box -->



                <div class="pt-2 border-t border-white/10 flex items-center justify-between gap-2 text-xs">



                    <div class="truncate">



                        <span class="text-slate-400 font-mono text-[10.5px]">Focus Keyword:</span>



                        <strong class="text-white font-mono ml-1">{{ !empty($targetKeyword) ? $targetKeyword : 'None (Click + Set)' }}</strong>



                    </div>



                    <button 



                        type="button" 



                        wire:click="toggleSeoDrawer" 



                        class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-mono text-[10.5px] font-bold border border-indigo-500/30 transition-colors shrink-0 cursor-pointer"



                    >



                        {{ !empty($targetKeyword) ? 'Edit Keyword' : '+ Set Keyword' }}



                    </button>



                </div>



            </div>

            <!-- Color System Visual Legend -->
            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/10 flex items-center justify-between text-[10px] font-mono select-none shadow-sm">
                <span class="text-slate-400 font-bold">Audit Colors:</span>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="flex items-center gap-1 text-rose-300"><span class="w-2 h-2 rounded-full bg-rose-500 shadow-sm shadow-rose-500/50"></span> 🔴 Critical</span>
                    <span class="flex items-center gap-1 text-amber-300"><span class="w-2 h-2 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span> 🟡 Warning</span>
                    <span class="flex items-center gap-1 text-blue-300"><span class="w-2 h-2 rounded-full bg-blue-500 shadow-sm shadow-blue-500/50"></span> 🔵 Authority</span>
                    <span class="flex items-center gap-1 text-emerald-300"><span class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span> 🟢 Passed</span>
                </div>
            </div>







            <!-- Target Keyword Input Drawer -->



            @if($showSeoDrawer)



                <div class="p-3.5 rounded-2xl bg-indigo-950/50 border border-indigo-500/50 space-y-2.5 animate-in shadow-xl">



                    <div class="flex items-center justify-between">



                        <label class="text-xs font-bold text-white flex items-center gap-1.5">



                            <span class="text-indigo-400">✓</span>



                            <span>Set Focus Target Keyword</span>



                        </label>



                        <span class="text-[10px] font-mono text-slate-400">Rank Math Algorithm</span>



                    </div>







                    <div class="flex items-center gap-1.5">



                        <input 



                            type="text" 



                            wire:model.lazy="targetKeyword" 



                            placeholder="e.g. deepseek v4 flash review"



                            class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"



                        />



                        <button 



                            type="button" 



                            wire:click="runSeoAudit" 



                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition-all cursor-pointer"



                        >



                            Analyze



                        </button>



                    </div>



                </div>



            @endif







            <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->



            <!-- RANK MATH 4-PILLAR ACCORDIONS WITH DUAL AI & MANUAL CONTROLS       -->



            <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->



            
            <div class="space-y-2" x-data="{ openPillar: 'basic_seo', manualView: {} }">
                <!-- DYNAMIC ADVANCED 6-PILLAR SEO ARCHITECTURE -->
                @php
                    $rmPillars = $seoData['rank_math'] ?? [];
                    if (empty($rmPillars)) { $rmPillars = []; }
                @endphp

                @foreach($rmPillars as $pillarKey => $pillarData)
                <div class="border border-white/10 bg-slate-900/50 rounded-xl overflow-hidden shadow-sm shadow-black/20">
                    <button 
                        type="button"
                        x-on:click="openPillar = openPillar === '{{ $pillarKey }}' ? '' : '{{ $pillarKey }}'" 
                        class="w-full flex items-center justify-between p-3 bg-slate-800/80 hover:bg-slate-700/80 transition-all"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white">{{ $pillarData['title'] ?? 'SEO Tasks' }}</span>
                            @if(isset($pillarData['score_label']))
                                <span class="px-1.5 py-0.5 rounded bg-black/40 border border-white/10 text-[10px] font-mono text-slate-300">{{ $pillarData['score_label'] }}</span>
                            @endif
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="openPillar === '{{ $pillarKey }}' ? 'rotate-180 text-white' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openPillar === '{{ $pillarKey }}'" x-transition style="display: none;" class="p-2 space-y-1.5 bg-slate-900/95 border-t border-white/5 max-h-[450px] overflow-y-auto hoa-custom-scrollbar">
                        @foreach($pillarData['checks'] ?? [] as $check)
                            <div class="p-2.5 rounded-xl border {{ $check['pass'] ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-slate-800/80 border-white/5' }} flex flex-col gap-2 transition-all">
                                <div class="flex items-start gap-2">
                                    @if($check['pass'])
                                        <div class="w-5 h-5 shrink-0 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mt-0.5 shadow-sm shadow-emerald-500/20">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    @else
                                        @php
                                            $sev = $check['severity'] ?? 'warning';
                                            $badgeBg = $sev === 'critical' ? 'bg-rose-500/20 border-rose-500/40 text-rose-400 shadow-rose-500/20' : ($sev === 'warning' ? 'bg-amber-500/20 border-amber-500/40 text-amber-400 shadow-amber-500/20' : 'bg-blue-500/20 border-blue-500/40 text-blue-400 shadow-blue-500/20');
                                        @endphp
                                        <div class="w-5 h-5 shrink-0 rounded-full {{ $badgeBg }} border flex items-center justify-center mt-0.5 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </div>
                                    @endif
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1.5 mb-0.5">
                                            <h4 class="text-[11px] font-bold {{ $check['pass'] ? 'text-white' : 'text-slate-200' }} leading-tight truncate">{{ $check['title'] }}</h4>
                                            @if(!$check['pass'] && isset($check['severity']))
                                                @php
                                                    $sevPill = $check['severity'] === 'critical' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' : ($check['severity'] === 'warning' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-blue-500/20 text-blue-300 border-blue-500/40');
                                                @endphp
                                                <span class="text-[8.5px] font-mono font-extrabold px-1.5 py-0.2 rounded border {{ $sevPill }} uppercase shrink-0">
                                                    {{ $check['severity'] }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] {{ $check['pass'] ? 'text-emerald-300' : 'text-slate-400' }} leading-snug">{{ $check['desc'] }}</p>
                                    </div>
                                </div>
                                
                                @if(!$check['pass'])
                                <div class="mt-1 p-2 rounded-xl bg-slate-950/80 border border-white/10 space-y-2 text-[10px]">
                                    @if(isset($check['current_val']) || isset($check['goal_val']))
                                        <div class="flex items-center justify-between font-mono text-[9.5px] text-slate-400 border-b border-white/5 pb-1">
                                            <span>Current: <strong class="text-white">{{ $check['current_val'] ?? 'Missing' }}</strong></span>
                                            <span>Goal: <strong class="text-indigo-300">{{ $check['goal_val'] ?? 'Optimized' }}</strong></span>
                                        </div>
                                    @endif

                                    @if(isset($check['actionable_tip']))
                                        <div class="text-[10px] text-amber-200/90 leading-relaxed flex items-start gap-1.5">
                                            <span class="text-amber-400 shrink-0 mt-0.5">💡</span>
                                            <span>{{ $check['actionable_tip'] }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center gap-1.5 pt-0.5 flex-wrap">
                                        @if(isset($check['ai_prompt']))
                                            <button 
                                                type="button" 
                                                @php
                                                    $aiFixTarget = in_array($check['id'], ['kw_in_title', 'kw_at_beginning_of_title', 'title_has_number', 'title_has_power_word', 'title_length_optimal', 'title_sentiment_positive']) ? 'title' : ($check['id'] === 'kw_in_meta' ? 'meta' : 'insert');
                                                @endphp
                                                x-on:click="applyTargetedIntelligenceFix('{{ $check['id'] }}', @js($check['title']), @js($check['ai_prompt']), '{{ $aiFixTarget }}')"
                                                class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[10px] shadow-sm shadow-indigo-600/30 flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-50"
                                                :disabled="activeAction === '{{ $check['id'] }}'"
                                                title="AI surgically optimizes this check"
                                            >
                                                <span class="hourglass w-3 h-3" x-show="activeAction === '{{ $check['id'] }}'" style="display: none;"></span>
                                                <span x-text="activeAction === '{{ $check['id'] }}' ? 'Working...' : '✨ AI Fix'"></span>
                                            </button>
                                        @endif
                                        
                                        @if(isset($check['target_canvas_id']))
                                            <button 
                                                type="button" 
                                                x-on:click="locateSeoTarget('{{ $check['target_canvas_id'] }}')"
                                                class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-white/15 text-slate-300 hover:text-white border border-white/10 transition-all flex items-center gap-1 cursor-pointer text-[10px]"
                                                title="Locate line in content editor"
                                            >
                                                <span>🎯 Locate in Content</span>
                                            </button>
                                        @endif

                                        <button 
                                            type="button" 
                                            x-on:click="manualView['{{ $check['id'] }}'] = !manualView['{{ $check['id'] }}']"
                                            class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-400 hover:text-slate-200 border border-white/10 transition-all flex items-center gap-1 cursor-pointer text-[10px]"
                                        >
                                            <span>✏️ Manual</span>
                                        </button>
                                    </div>

                                    <div x-show="manualView['{{ $check['id'] }}']" x-transition style="display: none;" class="mt-1.5 p-2 rounded-lg bg-slate-900 border border-white/5 text-[9.5px] text-slate-300 font-mono">
                                        {{ $check['manual_prompt'] ?? 'Manually edit this section to pass the check.' }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Google SERP Snippet Preview -->



            <div class="space-y-2 p-3 rounded-2xl bg-slate-900/80 border border-white/10 shadow-inner">



                <div class="flex items-center justify-between">



                    <span class="text-xs font-bold text-white">🔍Ž Google SERP Snippet Preview</span>



                    <div class="flex items-center gap-1 bg-slate-950 p-0.5 rounded-lg border border-white/10 text-[10px] font-mono">



                        <button type="button" x-on:click="serpView = 'desktop'" :class="serpView === 'desktop' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="px-1.5 py-0.5 rounded transition-all">Desktop</button>



                        <button type="button" x-on:click="serpView = 'mobile'" :class="serpView === 'mobile' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="px-1.5 py-0.5 rounded transition-all">Mobile</button>



                    </div>



                </div>







                <div class="p-3 rounded-xl bg-slate-950/90 border border-white/5 space-y-1 text-xs">



                    <div class="text-[11px] text-slate-400 font-mono truncate">https://helpofai.com/article/{{ \Illuminate\Support\Str::slug($title ?: 'untitled-document') }}</div>



                    <div class="text-sm font-semibold text-blue-400 hover:underline cursor-pointer leading-snug">{{ $title ?: 'Untitled Document' }} | HelpOfAi Studio</div>



                    <div class="text-[11px] text-slate-300 leading-relaxed">{{ !empty($metaDescription) ? $metaDescription : 'Authoritative AI content generated with the HelpOfAi Studio engine...' }}</div>



                </div>



            </div>



        </div>








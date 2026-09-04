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
                        x-on:click="toggleSeoHeatmap()"
                        :class="showSeoHeatmap ? 'bg-indigo-600 text-white border-indigo-400 shadow-indigo-500/30 ring-2 ring-indigo-500/50' : 'bg-slate-950/50 text-slate-300 border-white/10 hover:text-white hover:border-white/20'"
                        class="px-2.5 py-1.5 rounded-xl border text-xs font-bold shadow-sm transition-all flex flex-col items-center justify-center h-[42px] cursor-pointer gap-0.5"
                        title="Toggle the Offline Color-Coded SEO Heatmap directly in the editor"
                    >
                        <svg x-show="showSeoHeatmap" style="display:none;" class="w-3.5 h-3.5 text-white animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span x-text="showSeoHeatmap ? 'CLOSE' : '👁️ HEATMAP'"></span>
                    </button>
                    







                    <div class="text-right space-y-1">



                        <span class="text-[9.5px] font-mono px-2 py-0.5 rounded-full border {{ $scoreBadge['class'] }} font-bold inline-block">



                            {{ $scoreBadge['label'] }}



                        </span>



                        <div class="text-[10px] text-slate-400 font-mono flex items-center justify-end gap-1.5">
                            <span>Readability: <strong class="text-cyan-300">{{ $seoData['readability_score'] ?? 0 }}/100</strong></span>
                            @if(isset($seoData['geo_readiness']))
                                <span class="text-white/20">•</span>
                                <span>GEO: <strong class="text-purple-300">{{ ($seoData['geo_readiness']['has_direct_answer'] ? 25 : 0) + ($seoData['geo_readiness']['has_table'] ? 25 : 0) + min(25, ($seoData['geo_readiness']['data_points'] ?? 0) * 8) + min(25, ($seoData['geo_readiness']['paa_count'] ?? 0) * 12) }}%</strong></span>
                            @endif
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
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="flex items-center gap-1 text-rose-300"><span class="w-2 h-2 rounded-full bg-rose-500 shadow-sm shadow-rose-500/50"></span> 🔴 Critical</span>
                    <span class="flex items-center gap-1 text-amber-300"><span class="w-2 h-2 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span> 🟡 Warning</span>
                    <span class="flex items-center gap-1 text-purple-300"><span class="w-2 h-2 rounded-full bg-purple-500 shadow-sm shadow-purple-500/50"></span> 🟣 AI/GEO</span>
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



            
            <!-- ⚡ 1-CLICK MAGIC SEO & GEO AUTO-HEAL MASTER ACTION -->
            @php
                $allPillarChecks = [];
                foreach(($seoData['rank_math'] ?? []) as $p) {
                    foreach(($p['checks'] ?? []) as $c) {
                        $allPillarChecks[] = $c;
                    }
                }
                $failingChecks = array_filter($allPillarChecks, fn($c) => !$c['pass']);
                $failingCount = count($failingChecks);
            @endphp

            @if($failingCount > 0)
                <div class="p-3.5 rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/50 to-slate-900 border border-indigo-500/40 shadow-xl space-y-2.5 relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-purple-600/20 rounded-full blur-2xl pointer-events-none group-hover:bg-purple-600/30 transition-all"></div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">⚡</span>
                            <h4 class="text-xs font-black text-white tracking-wide uppercase">Magic SEO Auto-Healer</h4>
                        </div>
                        <span class="text-[9px] font-mono px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold">
                            {{ $failingCount }} Gaps Detected
                        </span>
                    </div>
                    <p class="text-[10.5px] text-slate-300 leading-relaxed">
                        Holistically weaves missing keywords, structures direct answers for Google AI Overviews, breaks bulky text, and integrates citations in a single pass.
                    </p>
                    <button 
                        type="button" 
                        x-on:click="autoHealDocumentSeo()"
                        :disabled="isTransforming"
                        class="w-full py-2.5 px-3.5 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50 select-none"
                    >
                        <span class="hourglass w-3.5 h-3.5" x-show="isTransforming && activeAction === 'seo_auto_heal'" style="display: none;"></span>
                        <span x-text="isTransforming && activeAction === 'seo_auto_heal' ? 'Auto-Healing Document...' : '⚡ 1-Click Auto-Optimize Content'"></span>
                    </button>
                </div>
            @endif

            <!-- ═════════════════════════════════════════════════════════════════════════ -->
            <!-- RANK MATH CORE 7-PILLAR AUDIT CHECKLIST WITH DUAL AI & MANUAL CONTROLS   -->
            <!-- ═════════════════════════════════════════════════════════════════════════ -->
            @php
                $rmPillars = $seoData['rank_math'] ?? [];
                if (empty($rmPillars) && !empty($seoData['recommendations'])) {
                    $basic = [];
                    $additional = [];
                    $titleChecks = [];
                    $contentReadabilityChecks = [];
                    $eatChecks = [];
                    $geoChecks = [];
                    $technicalChecks = [];
                    foreach(($seoData['recommendations'] ?? []) as $rec) {
                        $id = $rec['id'] ?? '';
                        if (in_array($id, ['kw_in_title', 'kw_in_meta', 'kw_in_slug', 'kw_in_intro', 'kw_in_content', 'content_length_min'])) {
                            $basic[] = $rec;
                        } elseif (in_array($id, ['kw_in_subheadings', 'kw_in_image_alt', 'keyword_density_optimal', 'external_links', 'internal_links'])) {
                            $additional[] = $rec;
                        } elseif (str_starts_with($id, 'title_') || $id === 'kw_at_beginning_of_title') {
                            $titleChecks[] = $rec;
                        } elseif (str_starts_with($id, 'readability_') || in_array($id, ['paragraph_length_optimal', 'sentence_length_optimal', 'reading_ease_high', 'content_scannability'])) {
                            $contentReadabilityChecks[] = $rec;
                        } elseif (str_starts_with($id, 'eeat_') || in_array($id, ['expert_quotes_present', 'original_data_referenced', 'trust_terms_density', 'clinical_or_research_citations'])) {
                            $eatChecks[] = $rec;
                        } elseif (str_starts_with($id, 'geo_')) {
                            $geoChecks[] = $rec;
                        } else {
                            $technicalChecks[] = $rec;
                        }
                    }
                    $countP = fn($arr) => count(array_filter($arr, fn($c) => $c['pass'] ?? false));
                    $rmPillars = [
                        'basic_seo' => ['title' => 'Basic SEO', 'score_label' => $countP($basic).'/'.count($basic).' Passed', 'checks' => $basic],
                        'additional_seo' => ['title' => 'Additional SEO', 'score_label' => $countP($additional).'/'.count($additional).' Passed', 'checks' => $additional],
                        'title_readability' => ['title' => 'Title Readability & CTR', 'score_label' => $countP($titleChecks).'/'.count($titleChecks).' Passed', 'checks' => $titleChecks],
                        'content_readability' => ['title' => 'Content Readability', 'score_label' => $countP($contentReadabilityChecks).'/'.count($contentReadabilityChecks).' Passed', 'checks' => $contentReadabilityChecks],
                        'eeat_authority' => ['title' => 'E-E-A-T & Authority', 'score_label' => $countP($eatChecks).'/'.count($eatChecks).' Passed', 'checks' => $eatChecks],
                        'geo_ai_search' => ['title' => 'AI Overviews & GEO Readiness', 'score_label' => $countP($geoChecks).'/'.count($geoChecks).' Passed', 'checks' => $geoChecks],
                    ];
                }
                if (empty($rmPillars)) {
                    $fresh = app(\App\Features\SEO\Services\SeoAnalyzer::class)->analyze(
                        $contentHtml ?? '', 
                        $title ?? '', 
                        $targetKeyword ?: null, 
                        $secondaryKeywords ?? []
                    );
                    $rmPillars = $fresh['rank_math'] ?? [];
                }
            @endphp

            <div class="space-y-2.5" x-data="{ 
                openPillars: { 
                    basic_seo: true, 
                    additional_seo: true, 
                    title_readability: true, 
                    content_readability: true, 
                    eeat_authority: false, 
                    geo_ai_search: false, 
                    technical_competitive: false 
                }, 
                manualView: {},
                allExpanded: false,
                togglePillar(key) {
                    this.openPillars[key] = !this.openPillars[key];
                },
                toggleAllPillars() {
                    this.allExpanded = !this.allExpanded;
                    Object.keys(this.openPillars).forEach(k => this.openPillars[k] = this.allExpanded);
                }
            }">
                <div class="flex items-center justify-between px-1 text-[11px] font-mono text-slate-400 select-none">
                    <span class="font-bold text-white uppercase tracking-wider text-[10.5px]">SEO Optimization Checklist</span>
                    <button type="button" x-on:click="toggleAllPillars()" class="text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer text-[10.5px] font-bold">
                        <span x-text="allExpanded ? 'Collapse All' : 'Expand All'"></span>
                    </button>
                </div>

                @foreach($rmPillars as $pillarKey => $pillarData)
                <div class="border border-white/10 bg-slate-900/50 rounded-xl overflow-hidden shadow-sm shadow-black/20">
                    <button 
                        type="button"
                        x-on:click="togglePillar('{{ $pillarKey }}')" 
                        class="w-full flex items-center justify-between p-3 bg-slate-800/80 hover:bg-slate-700/80 transition-all cursor-pointer select-none text-left"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white">{{ $pillarData['title'] ?? 'SEO Tasks' }}</span>
                            @if(isset($pillarData['score_label']))
                                <span class="px-1.5 py-0.5 rounded bg-black/40 border border-white/10 text-[10px] font-mono text-slate-300">{{ $pillarData['score_label'] }}</span>
                            @endif
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openPillars['{{ $pillarKey }}'] ? 'rotate-180 text-white' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openPillars['{{ $pillarKey }}']" x-transition class="p-2 space-y-1.5 bg-slate-900/95 border-t border-white/5 max-h-[500px] overflow-y-auto hoa-custom-scrollbar">
                        @foreach($pillarData['checks'] ?? [] as $check)
                            <div class="p-2.5 rounded-xl border {{ $check['pass'] ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-slate-800/80 border-white/10' }} flex flex-col gap-2 transition-all">
                                <div class="flex items-start gap-2">
                                    @if($check['pass'])
                                        <div class="w-5 h-5 shrink-0 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mt-0.5 shadow-sm shadow-emerald-500/20">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    @else
                                        @php
                                            $sev = $check['severity'] ?? 'warning';
                                            $isGeo = str_starts_with($check['id'], 'geo_');
                                            $badgeBg = $isGeo 
                                                ? 'bg-purple-500/20 border-purple-500/40 text-purple-400 shadow-purple-500/20' 
                                                : ($sev === 'critical' ? 'bg-rose-500/20 border-rose-500/40 text-rose-400 shadow-rose-500/20' : ($sev === 'warning' ? 'bg-amber-500/20 border-amber-500/40 text-amber-400 shadow-amber-500/20' : 'bg-blue-500/20 border-blue-500/40 text-blue-400 shadow-blue-500/20'));
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
                                                    $isGeo = str_starts_with($check['id'], 'geo_');
                                                    $sevPill = $isGeo 
                                                        ? 'bg-purple-500/25 text-purple-300 border-purple-500/40' 
                                                        : ($check['severity'] === 'critical' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' : ($check['severity'] === 'warning' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-blue-500/20 text-blue-300 border-blue-500/40'));
                                                @endphp
                                                <div class="flex items-center gap-1 shrink-0">
                                                    @if($isGeo)
                                                        <span class="text-[8px] font-mono font-black px-1.5 py-0.2 rounded bg-purple-900/60 text-purple-200 border border-purple-400/30 uppercase">GEO</span>
                                                    @endif
                                                    <span class="text-[8.5px] font-mono font-extrabold px-1.5 py-0.2 rounded border {{ $sevPill }} uppercase">
                                                        {{ $check['severity'] }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="text-[10px] {{ $check['pass'] ? 'text-emerald-300' : 'text-slate-400' }} leading-snug">{{ $check['desc'] }}</p>
                                    </div>
                                </div>
                                
                                <div class="mt-1 p-2 rounded-xl bg-slate-950/80 border border-white/10 space-y-2 text-[10px]">
                                    @if(isset($check['current_val']) || isset($check['goal_val']))
                                        <div class="flex items-center justify-between font-mono text-[9.5px] text-slate-400 border-b border-white/5 pb-1">
                                            <span>Current: <strong class="text-white">{{ $check['current_val'] ?? 'Missing' }}</strong></span>
                                            <span>Goal: <strong class="text-indigo-300">{{ $check['goal_val'] ?? 'Optimized' }}</strong></span>
                                        </div>
                                    @endif

                                    @if(!$check['pass'] && isset($check['actionable_tip']))
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
                                                class="px-2.5 py-1 rounded-lg {{ $check['pass'] ? 'bg-indigo-600/25 hover:bg-indigo-600 text-indigo-200 hover:text-white border border-indigo-500/30' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm shadow-indigo-600/30' }} font-bold text-[10px] flex items-center justify-center gap-1 transition-all cursor-pointer disabled:opacity-50"
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
                                                x-on:click="locateSeoTarget('{{ $check['target_canvas_id'] }}', '{{ $check['id'] }}')"
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
                            </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- 🧬 SEMANTIC NLP ENTITIES & LSI DENSITY MATRIX (SurferSEO / Clearscope Style) -->
            @php
                $semanticEntities = $seoData['semantic_entities'] ?? [];
            @endphp

            @if(!empty($semanticEntities))
                <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner space-y-2.5" x-data="{ entityFilter: 'all', copiedEntity: '' }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs">🧬</span>
                            <span class="text-xs font-bold text-white">NLP Semantic Entities & LSI</span>
                            <span class="text-[9.5px] font-mono px-1.5 py-0.2 rounded bg-indigo-950 text-indigo-300 border border-indigo-500/30 font-bold">
                                {{ count($semanticEntities) }} Terms
                            </span>
                        </div>
                        <div class="flex items-center gap-1 text-[9px] font-mono">
                            <button type="button" x-on:click="entityFilter = 'all'" :class="entityFilter === 'all' ? 'bg-white/15 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">All</button>
                            <button type="button" x-on:click="entityFilter = 'underused'" :class="entityFilter === 'underused' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'text-slate-400 hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">Underused</button>
                            <button type="button" x-on:click="entityFilter = 'optimal'" :class="entityFilter === 'optimal' ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-400 hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">Optimal</button>
                            <button type="button" x-on:click="entityFilter = 'overused'" :class="entityFilter === 'overused' ? 'bg-rose-500/20 text-rose-300 font-bold' : 'text-slate-400 hover:text-white'" class="px-1.5 py-0.5 rounded transition-all">Overused</button>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 leading-tight">
                        Topical coverage matrix. Click any entity to copy term.
                    </p>

                    <div class="flex flex-wrap gap-1.5 max-h-44 overflow-y-auto hoa-custom-scrollbar pr-1">
                        @foreach($semanticEntities as $entity)
                            @php
                                $status = $entity['status'] ?? 'optimal';
                                $chipStyle = $status === 'underused' 
                                    ? 'bg-amber-500/10 text-amber-300 border-amber-500/30 hover:bg-amber-500/20' 
                                    : ($status === 'overused' 
                                        ? 'bg-rose-500/10 text-rose-300 border-rose-500/30 hover:bg-rose-500/20' 
                                        : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30 hover:bg-emerald-500/20');
                            @endphp
                            <button 
                                type="button"
                                x-show="entityFilter === 'all' || entityFilter === '{{ $status }}'"
                                x-on:click="navigator.clipboard.writeText('{{ $entity['term'] }}'); copiedEntity = '{{ $entity['term'] }}'; setTimeout(() => copiedEntity = '', 1500)"
                                class="px-2 py-1 rounded-lg border text-[10px] font-mono flex items-center gap-1.5 transition-all cursor-pointer select-none group {{ $chipStyle }}"
                                title="{{ $entity['type'] }}: {{ $entity['count'] }} used (Goal: {{ $entity['min'] }}-{{ $entity['max'] }}) — Click to copy"
                            >
                                <span class="font-sans font-medium">{{ $entity['term'] }}</span>
                                <span class="text-[9px] font-bold opacity-80 group-hover:opacity-100">
                                    {{ $entity['count'] }}/{{ $entity['max'] }}
                                </span>
                                <span x-show="copiedEntity === '{{ $entity['term'] }}'" class="text-[8px] text-emerald-400 font-bold ml-0.5">✓</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 🏷️ SCHEMA STUDIO & RICH SNIPPETS (JSON-LD) -->
            @php
                $schemaData = $seoData['schema_data'] ?? null;
            @endphp

            @if(!empty($schemaData))
                <div class="border border-white/10 bg-slate-900/60 rounded-xl overflow-hidden shadow-sm" x-data="{ showSchemaStudio: false, schemaCopied: false, activeSchemaTab: 'code' }">
                    <button 
                        type="button" 
                        x-on:click="showSchemaStudio = !showSchemaStudio"
                        class="w-full flex items-center justify-between p-3 bg-slate-800/80 hover:bg-slate-700/80 transition-all text-left cursor-pointer"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white flex items-center gap-1.5">
                                <span>🏷️</span>
                                <span>Schema Studio (JSON-LD)</span>
                            </span>
                            <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 border border-emerald-500/40 text-[9.5px] font-mono text-emerald-300 font-bold">
                                {{ $schemaData['recommended_type'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono text-slate-400" x-text="showSchemaStudio ? 'Hide' : 'Inspect'"></span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="showSchemaStudio ? 'rotate-180 text-white' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </button>

                    <div x-show="showSchemaStudio" x-transition style="display: none;" class="p-3 space-y-2.5 bg-slate-950/95 border-t border-white/5 text-xs">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <button type="button" x-on:click="activeSchemaTab = 'code'" :class="activeSchemaTab === 'code' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white bg-slate-900'" class="px-2.5 py-1 rounded-lg text-[10px] font-mono transition-all cursor-pointer">
                                    JSON-LD Code
                                </button>
                                <button type="button" x-on:click="activeSchemaTab = 'preview'" :class="activeSchemaTab === 'preview' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white bg-slate-900'" class="px-2.5 py-1 rounded-lg text-[10px] font-mono transition-all cursor-pointer">
                                    Rich Snippet Preview
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button 
                                    type="button" 
                                    x-on:click="navigator.clipboard.writeText(@js($schemaData['script_tag'])); schemaCopied = true; setTimeout(() => schemaCopied = false, 2000)"
                                    class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 font-mono text-[10px] transition-all flex items-center gap-1 cursor-pointer"
                                >
                                    <span x-text="schemaCopied ? '✓ Copied!' : '📋 Copy Code'"></span>
                                </button>
                                <button 
                                    type="button" 
                                    x-on:click="insertContentIntoCanvas(@js($schemaData['script_tag']), false); $dispatch('autosave'); addLog('SEO', 'Injected Schema.org JSON-LD structured data into document footer.');"
                                    class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-mono text-[10px] font-bold shadow-sm transition-all flex items-center gap-1 cursor-pointer"
                                >
                                    <span>📥 Inject</span>
                                </button>
                            </div>
                        </div>

                        <!-- JSON-LD Code View -->
                        <div x-show="activeSchemaTab === 'code'" class="space-y-1">
                            <pre class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 font-mono text-[9.5px] text-emerald-300 overflow-x-auto max-h-56 select-all hoa-custom-scrollbar leading-relaxed"><code>{{ $schemaData['script_tag'] }}</code></pre>
                            <div class="flex items-center justify-between text-[9px] font-mono text-slate-500">
                                <span>Detected: {{ implode(', ', $schemaData['detected_types']) }}</span>
                                <span class="text-emerald-400 font-bold">✓ Schema.org Valid</span>
                            </div>
                        </div>

                        <!-- Rich Snippet SERP Preview -->
                        <div x-show="activeSchemaTab === 'preview'" style="display: none;" class="p-3 rounded-xl bg-slate-900/90 border border-white/10 space-y-2">
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Google Search Result with FAQ / HowTo Accordion:</div>
                            <div class="p-3 rounded-xl bg-white text-slate-900 shadow-md space-y-1 text-left font-sans">
                                <div class="flex items-center gap-2 text-xs text-slate-600 truncate font-mono">
                                    <span class="w-4 h-4 rounded-full bg-indigo-600 text-white text-[9px] flex items-center justify-center font-bold">H</span>
                                    <span class="truncate">{{ config('app.url') }} &rsaquo; {{ $targetKeyword ?: 'guide' }}</span>
                                </div>
                                <h4 class="text-blue-700 hover:underline text-sm font-semibold leading-tight line-clamp-1 cursor-pointer">
                                    {{ $title ?: 'The Complete Guide' }}
                                </h4>
                                <p class="text-xs text-slate-600 leading-snug line-clamp-2">
                                    {{ !empty($metaDescription) ? $metaDescription : 'Comprehensive breakdown featuring step-by-step methodologies, verified data points, and expert guidance.' }}
                                </p>
                                @if(!empty($schemaData['schemas']['faq']['mainEntity']))
                                    <div class="pt-2 border-t border-slate-200 space-y-1">
                                        @foreach(array_slice($schemaData['schemas']['faq']['mainEntity'], 0, 2) as $faqItem)
                                            <div class="text-xs text-slate-700 flex items-center justify-between font-medium">
                                                <span>{{ $faqItem['name'] }}</span>
                                                <span class="text-slate-400 text-[10px]">&blacktriangledown;</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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








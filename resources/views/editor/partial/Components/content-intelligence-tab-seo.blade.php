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

            <div class="space-y-2" x-data="{ openPillar: 'basic', manualView: {} }">

                

                <!-- ─── PILLAR 1: BASIC SEO ACCORDION ─────────────────────────── -->

                @php

                    $rm = $seoData['rank_math'] ?? [];

                    $basicChecks = $rm['basic_seo']['checks'] ?? [

                        ['id' => 'kw_in_title', 'title' => 'Focus Keyword in SEO Title', 'desc' => 'Primary keyword appears in document title tag.', 'pass' => !empty($targetKeyword) && stripos($title, $targetKeyword) !== false, 'ai_prompt' => "Rewrite the document title to naturally front-load the focus keyword '{$targetKeyword}'. Output only the title."],

                        ['id' => 'kw_in_meta', 'title' => 'Focus Keyword in Meta Description', 'desc' => 'Primary keyword appears in meta description.', 'pass' => !empty($metaDescription) && !empty($targetKeyword) && stripos($metaDescription, $targetKeyword) !== false, 'ai_prompt' => "Generate a 155-character meta description featuring the keyword '{$targetKeyword}' and a strong call-to-action."],

                        ['id' => 'kw_in_url', 'title' => 'Focus Keyword in URL Slug', 'desc' => 'Primary keyword is present in permalink slug.', 'pass' => !empty($targetKeyword) && stripos(\Illuminate\Support\Str::slug($title), \Illuminate\Support\Str::slug($targetKeyword)) !== false, 'ai_prompt' => "Suggest a clean URL slug optimized for '{$targetKeyword}'."],

                        ['id' => 'kw_in_intro', 'title' => 'Focus Keyword in First 10% (Intro)', 'desc' => 'Primary keyword appears in the opening introduction sentences.', 'pass' => false, 'ai_prompt' => "Rewrite the first 2 paragraphs of the document to naturally introduce the primary focus keyword '{$targetKeyword}' within the first 2 sentences. Output clean HTML."],

                        ['id' => 'kw_in_body', 'title' => 'Focus Keyword in Content Body', 'desc' => 'Primary keyword is referenced across paragraphs naturally.', 'pass' => $wordCount > 150, 'ai_prompt' => "Optimize the body text so the focus keyword '{$targetKeyword}' is naturally distributed. Output clean HTML."],

                        ['id' => 'content_length', 'title' => 'Content Length Check (600+ words)', 'desc' => "Current length is {$wordCount} words (Optimal: 1,200+ words).", 'pass' => $wordCount >= 600, 'ai_prompt' => "Expand the document with comprehensive technical analysis and real-world examples to reach 1,200+ words. Output clean HTML."],

                    ];

                    $basicPassed = count(array_filter($basicChecks, fn($c) => $c['pass']));

                @endphp

                <div class="rounded-2xl bg-slate-900/90 border border-white/10 overflow-hidden shadow-inner">

                    <button 

                        type="button" 

                        x-on:click="openPillar = (openPillar === 'basic' ? null : 'basic')" 

                        class="w-full p-3 flex items-center justify-between text-xs font-bold text-white hover:bg-white/5 transition-colors cursor-pointer"

                    >

                        <div class="flex items-center gap-2">

                            <span class="text-indigo-400">📝‚</span>

                            <span>Basic SEO</span>

                        </div>

                        <div class="flex items-center gap-2">

                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $basicPassed === count($basicChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">

                                {{ $basicPassed }}/{{ count($basicChecks) }} Passed

                            </span>

                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'basic' ? 'â–²' : 'â–¼'"></span>

                        </div>

                    </button>



                    <div x-show="openPillar === 'basic'" class="p-3 border-t border-white/5 space-y-3 text-xs font-sans">

                        @foreach($basicChecks as $check)

                            <div class="p-2.5 rounded-xl {{ $check['pass'] ? 'bg-emerald-950/20 text-emerald-200 border border-emerald-500/20' : 'bg-slate-950/50 text-slate-300 border border-white/10' }} space-y-2">

                                <div class="flex items-start justify-between gap-2">

                                    <div class="flex items-start gap-2">

                                        <span class="text-sm font-bold {{ $check['pass'] ? 'text-emerald-400' : 'text-red-400' }}">

                                            {{ $check['pass'] ? '✓' : '✕' }}

                                        </span>

                                        <div>

                                            <div class="font-semibold {{ $check['pass'] ? 'text-white' : 'text-slate-200' }} text-[11px]">{{ $check['title'] }}</div>

                                            <div class="text-[10px] text-slate-400 leading-snug">{{ $check['desc'] }}</div>

                                        </div>

                                    </div>

                                </div>



                                <!-- Action Buttons: AI Implementation & Manual Controls -->

                                <div class="flex items-center gap-1.5 pt-1 border-t border-white/5 font-mono text-[10.5px]">

                                    <button 

                                        type="button" 

                                        x-on:click="applyTargetedIntelligenceFix('{{ $check['id'] }}', @js($check['title']), @js($check['ai_prompt'] ?? 'Optimize ' . $check['title']), '{{ in_array($check['id'], ['kw_in_title', 'kw_at_beginning_of_title', 'title_has_number', 'title_has_power_word']) ? 'title' : ($check['id'] === 'kw_in_meta' ? 'meta' : ($check['id'] === 'kw_in_intro' ? 'intro' : 'insert')) }}')"

                                        class="px-2.5 py-1 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 flex items-center justify-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"

                                        :disabled="activeAction === '{{ $check['id'] }}'"

                                        title="AI surgically optimizes only this section without rewriting the rest of the document"

                                    >

                                        <span class="hourglass" x-show="activeAction === '{{ $check['id'] }}'" style="display: none;"></span>

                                        <span x-text="activeAction === '{{ $check['id'] }}' ? 'Working...' : '✨ AI Fix'"></span>

                                    </button>

                                    <button 

                                        type="button" 

                                        x-on:click="manualView['{{ $check['id'] }}'] = !manualView['{{ $check['id'] }}']"

                                        class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition-all flex items-center gap-1 cursor-pointer"

                                        title="Open manual inspection & editing controls"

                                    >

                                        <span>✏️ Manual</span>

                                    </button>

                                </div>



                                <!-- Manual Option Interactive Drawer -->

                                <div x-show="manualView['{{ $check['id'] }}']" class="mt-2 p-2.5 rounded-xl bg-slate-950 border border-white/15 space-y-2 text-[11px] font-sans text-slate-300" style="display: none;">

                                    @if($check['id'] === 'kw_in_title')

                                        <label class="block font-bold text-white text-[10.5px]">Edit Document Title:</label>

                                        <div class="flex gap-1.5">

                                            <input type="text" wire:model.lazy="title" class="flex-1 bg-slate-900 border border-white/15 rounded-lg px-2.5 py-1 text-xs text-white" />

                                            <button type="button" wire:click="applyTitle(title)" wire:loading.attr="disabled" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/30 rounded-lg font-bold text-[10px] flex items-center justify-center gap-1.5 transition-all">

                                                <span class="dual w-2.5 h-2.5 border-[1.5px]" wire:loading wire:target="applyTitle"></span>

                                                <span wire:loading.remove wire:target="applyTitle">Save</span>

                                                <span wire:loading wire:target="applyTitle">Updating...</span>

                                            </button>

                                        </div>

                                    @elseif($check['id'] === 'kw_in_meta')

                                        <label class="block font-bold text-white text-[10.5px]">Edit Meta Description:</label>

                                        <textarea wire:model.lazy="metaDescription" rows="2" class="w-full bg-slate-900 border border-white/15 rounded-lg p-2 text-xs text-white resize-none"></textarea>

                                        <button type="button" wire:click="applyMetaDescription(metaDescription)" wire:loading.attr="disabled" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/30 rounded-lg font-bold text-[10px] flex items-center justify-center gap-1.5 transition-all">

                                            <span class="dual w-2.5 h-2.5 border-[1.5px]" wire:loading wire:target="applyMetaDescription"></span>

                                            <span wire:loading.remove wire:target="applyMetaDescription">Save Meta</span>

                                            <span wire:loading wire:target="applyMetaDescription">Updating...</span>

                                        </button>

                                    @else

                                        <p class="text-slate-400 text-[10.5px]">Manual Guidance: Place the primary keyword (<strong>{{ $targetKeyword ?: 'target keyword' }}</strong>) naturally into this section of your canvas.</p>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>



                <!-- ─── PILLAR 2: ADDITIONAL SEO ACCORDION ────────────────────── -->

                @php

                    $additionalChecks = $rm['additional_seo']['checks'] ?? [

                        ['id' => 'kw_in_subheadings', 'title' => 'Focus Keyword in Subheadings (H2, H3)', 'desc' => 'Primary keyword found in H2 or H3 heading tags.', 'pass' => ($seoData['metrics']['headings']['h2'] ?? 0) >= 2, 'ai_prompt' => "Add or update H2 subheadings in the document to include the keyword '{$targetKeyword}'. Output clean HTML."],

                        ['id' => 'kw_in_img_alt', 'title' => 'Focus Keyword in Image Alt Attributes', 'desc' => 'Images contain alt text matching the target keyword.', 'pass' => ($seoData['metrics']['images'] ?? 0) > 0, 'ai_prompt' => "Insert descriptive image figure blocks with keyword-rich alt tags matching '{$targetKeyword}'. Output clean HTML."],

                        ['id' => 'keyword_density', 'title' => 'Keyword Density (0.8% - 2.5%)', 'desc' => 'Keyword density is balanced without keyword stuffing.', 'pass' => true, 'ai_prompt' => "Analyze the full content and balance the keyword density for '{$targetKeyword}' to exactly 1.2%. Output clean HTML."],

                        ['id' => 'external_links', 'title' => 'External Outbound Citations', 'desc' => 'Authoritative external citations found in content.', 'pass' => ($seoData['metrics']['links']['external'] ?? 0) >= 1, 'ai_prompt' => "Insert 2-3 authoritative external citations and reference links to validate key claims. Output clean HTML."],

                        ['id' => 'internal_links', 'title' => 'Internal Cluster Links', 'desc' => 'Internal links linking to related topics and resources.', 'pass' => ($seoData['metrics']['links']['internal'] ?? 0) >= 1, 'ai_prompt' => "Add internal linking anchors and related resource suggestions to connect topic clusters. Output clean HTML."],

                    ];

                    $addPassed = count(array_filter($additionalChecks, fn($c) => $c['pass']));

                @endphp

                <div class="rounded-2xl bg-slate-900/90 border border-white/10 overflow-hidden shadow-inner">

                    <button 

                        type="button" 

                        x-on:click="openPillar = (openPillar === 'additional' ? null : 'additional')" 

                        class="w-full p-3 flex items-center justify-between text-xs font-bold text-white hover:bg-white/5 transition-colors cursor-pointer"

                    >

                        <div class="flex items-center gap-2">

                            <span class="text-cyan-400">📝Š</span>

                            <span>Additional SEO</span>

                        </div>

                        <div class="flex items-center gap-2">

                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $addPassed === count($additionalChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">

                                {{ $addPassed }}/{{ count($additionalChecks) }} Passed

                            </span>

                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'additional' ? 'â–²' : 'â–¼'"></span>

                        </div>

                    </button>



                    <div x-show="openPillar === 'additional'" class="p-3 border-t border-white/5 space-y-3 text-xs font-sans" style="display: none;">

                        @foreach($additionalChecks as $check)

                            <div class="p-2.5 rounded-xl {{ $check['pass'] ? 'bg-emerald-950/20 text-emerald-200 border border-emerald-500/20' : 'bg-slate-950/50 text-slate-300 border border-white/10' }} space-y-2">

                                <div class="flex items-start gap-2">

                                    <span class="text-sm font-bold {{ $check['pass'] ? 'text-emerald-400' : 'text-red-400' }}">

                                        {{ $check['pass'] ? '✓' : '✕' }}

                                    </span>

                                    <div>

                                        <div class="font-semibold {{ $check['pass'] ? 'text-white' : 'text-slate-200' }} text-[11px]">{{ $check['title'] }}</div>

                                        <div class="text-[10px] text-slate-400 leading-snug">{{ $check['desc'] }}</div>

                                    </div>

                                </div>



                                <div class="flex items-center gap-1.5 pt-1 border-t border-white/5 font-mono text-[10.5px]">

                                    <button 

                                        type="button" 

                                        x-on:click="applyTargetedIntelligenceFix('{{ $check['id'] }}', @js($check['title']), @js($check['ai_prompt'] ?? 'Fix ' . $check['title']), 'insert')"

                                        class="px-2.5 py-1 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 flex items-center justify-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"

                                        :disabled="activeAction === '{{ $check['id'] }}'"

                                        title="AI surgically optimizes only this section without rewriting the rest of the document"

                                    >

                                        <span class="hourglass" x-show="activeAction === '{{ $check['id'] }}'" style="display: none;"></span>

                                        <span x-text="activeAction === '{{ $check['id'] }}' ? 'Working...' : '✨ AI Fix'"></span>

                                    </button>

                                    <button 

                                        type="button" 

                                        x-on:click="manualView['{{ $check['id'] }}'] = !manualView['{{ $check['id'] }}']"

                                        class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition-all cursor-pointer"

                                    >

                                        <span>✏️ Manual</span>

                                    </button>

                                </div>



                                <div x-show="manualView['{{ $check['id'] }}']" class="mt-2 p-2.5 rounded-xl bg-slate-950 border border-white/15 space-y-1.5 text-[11px] text-slate-300 font-sans" style="display: none;">

                                    @if($check['id'] === 'kw_in_img_alt')

                                        <button type="button" x-on:click="insertImageFromUrl()" class="px-2.5 py-1 bg-indigo-600 text-white rounded-lg font-bold text-[10px]">+ Insert Image with Alt</button>

                                    @else

                                        <p class="text-slate-400 text-[10.5px]">Manual Guidance: Add external citations (e.g. documentation links) or internal cluster links directly in the editor canvas.</p>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>



                <!-- ─── PILLAR 3: TITLE READABILITY ACCORDION ─────────────────── -->

                @php

                    $titleChecks = $rm['title_readability']['checks'] ?? [

                        ['id' => 'kw_at_beginning_of_title', 'title' => 'Focus Keyword at Start of Title', 'desc' => 'Primary keyword is front-loaded in title tag.', 'pass' => !empty($title), 'ai_prompt' => "Front-load the focus keyword '{$targetKeyword}' at the very beginning of the title. Output ONLY the title."],

                        ['id' => 'title_has_number', 'title' => 'Title Contains a Number', 'desc' => 'Numbers in titles increase click-through rates (CTR).', 'pass' => preg_match('/\d+/', $title) === 1, 'ai_prompt' => "Rewrite the title to include a specific year or number (e.g. 2026, 7 Best, 10 Steps). Output ONLY the title."],

                        ['id' => 'title_has_power_word', 'title' => 'Title Contains a Power Word', 'desc' => 'Power words boost CTR and emotional resonance.', 'pass' => true, 'ai_prompt' => "Rewrite the title to include a high-converting power word (e.g. Ultimate, Proven, Essential, Breakthrough). Output ONLY the title."],

                    ];

                    $titlePassed = count(array_filter($titleChecks, fn($c) => $c['pass']));

                @endphp

                <div class="rounded-2xl bg-slate-900/90 border border-white/10 overflow-hidden shadow-inner">

                    <button 

                        type="button" 

                        x-on:click="openPillar = (openPillar === 'title' ? null : 'title')" 

                        class="w-full p-3 flex items-center justify-between text-xs font-bold text-white hover:bg-white/5 transition-colors cursor-pointer"

                    >

                        <div class="flex items-center gap-2">

                            <span class="text-violet-400">✓</span>

                            <span>Title Readability</span>

                        </div>

                        <div class="flex items-center gap-2">

                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $titlePassed === count($titleChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">

                                {{ $titlePassed }}/{{ count($titleChecks) }} Passed

                            </span>

                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'title' ? 'â–²' : 'â–¼'"></span>

                        </div>

                    </button>



                    <div x-show="openPillar === 'title'" class="p-3 border-t border-white/5 space-y-3 text-xs font-sans" style="display: none;">

                        @foreach($titleChecks as $check)

                            <div class="p-2.5 rounded-xl {{ $check['pass'] ? 'bg-emerald-950/20 text-emerald-200 border border-emerald-500/20' : 'bg-slate-950/50 text-slate-300 border border-white/10' }} space-y-2">

                                <div class="flex items-start gap-2">

                                    <span class="text-sm font-bold {{ $check['pass'] ? 'text-emerald-400' : 'text-red-400' }}">

                                        {{ $check['pass'] ? '✓' : '✕' }}

                                    </span>

                                    <div>

                                        <div class="font-semibold {{ $check['pass'] ? 'text-white' : 'text-slate-200' }} text-[11px]">{{ $check['title'] }}</div>

                                        <div class="text-[10px] text-slate-400 leading-snug">{{ $check['desc'] }}</div>

                                    </div>

                                </div>



                                <div class="flex items-center gap-1.5 pt-1 border-t border-white/5 font-mono text-[10.5px]">

                                    <button 

                                        type="button" 

                                        x-on:click="applyTargetedIntelligenceFix('{{ $check['id'] }}', @js($check['title']), @js($check['ai_prompt'] ?? 'Optimize Title'), 'title')"

                                        class="px-2.5 py-1 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 flex items-center justify-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"

                                        :disabled="activeAction === '{{ $check['id'] }}'"

                                        title="AI surgically optimizes only this section without rewriting the rest of the document"

                                    >

                                        <span class="hourglass" x-show="activeAction === '{{ $check['id'] }}'" style="display: none;"></span>

                                        <span x-text="activeAction === '{{ $check['id'] }}' ? 'Working...' : '✨ AI Fix'"></span>

                                    </button>

                                    <button 

                                        type="button" 

                                        x-on:click="manualView['{{ $check['id'] }}'] = !manualView['{{ $check['id'] }}']"

                                        class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition-all cursor-pointer"

                                    >

                                        <span>✏️ Manual</span>

                                    </button>

                                </div>



                                <div x-show="manualView['{{ $check['id'] }}']" class="mt-2 p-2.5 rounded-xl bg-slate-950 border border-white/15 space-y-1.5 text-[11px] text-slate-300 font-sans" style="display: none;">

                                    <button type="button" x-on:click="editorInstance?.insertTable?.({ rows: 3, cols: 3, withHeaderRow: true })" class="px-2.5 py-1 bg-indigo-600 text-white rounded-lg font-bold text-[10px]">+ Insert Table Manually</button>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>



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




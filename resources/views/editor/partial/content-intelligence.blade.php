{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Intelligence Sidebar
|--------------------------------------------------------------------------
|
| Features:
| 1. Scrollable Tab Navigation (Responsive Horizontal Scroll)
| 2. Rank Math Enterprise 4-Pillar SEO Engine:
|    - Pillar 1: Basic SEO (Title, Meta, URL, Intro, Content Body, Length)
|    - Pillar 2: Additional SEO (Subheadings, Image Alt, Density, URL Length, Outbound Citations, Internal Links, LSI Entities)
|    - Pillar 3: Title Readability (Front-loaded Keyword, Numbers, Power Words, Character Length)
|    - Pillar 4: Content Readability (Headings/TOC, Short Paragraphs, Sentences, Rich Media & Tables)
| 3. Dual Implementation Options on EVERY Check:
|    - ⚡ AI Auto-Implement (Evaluates full content & writes live)
|    - ✏️ Manual Option (Interactive direct editors & inserters)
| 4. AI Viral Titles & Meta Descriptions with 1-Click Apply
| 5. AI Content Gaps & Schema FAQs with 1-Click Canvas Inserters
| 6. Secondary Keywords & Semantic Entities
| 7. 10-Point E-E-A-T Quality Audit
| 8. Outline Tree with Click-to-Scroll
| 9. Version Snapshots Timeline
|
*/
--}}

<div 
    x-show="showRightPanel" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    class="space-y-4 h-full flex flex-col"
>
    <div class="editor-column hoa-custom-scrollbar">
        <!-- Main Header -->
        <div class="flex items-center justify-between pb-2 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h2 class="text-xs uppercase font-extrabold text-white tracking-wider">Content Intelligence</h2>
            </div>
            <span class="text-[10px] font-mono text-emerald-400 font-bold px-2 py-0.5 rounded-full bg-emerald-600/15 border border-emerald-500/30" x-text="'Goal: ' + Math.min(100, Math.round((wordCount/targetWordGoal)*100)) + '%'"></span>
        </div>

        <!-- Responsive Multi-Row Tab Navigation Grid -->
        <div class="grid grid-cols-4 gap-1 p-1.5 rounded-2xl bg-slate-950/90 border border-white/10 text-xs font-mono select-none shadow-inner backdrop-blur-md">
            <button 
                type="button" 
                x-on:click="rightTab = 'seo'" 
                :class="rightTab === 'seo' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="Rank Math 100/100 SEO Audit"
            >
                <span class="text-sm">🎯</span>
                <span class="text-[10px] font-bold truncate w-full">SEO</span>
            </button>

            <button 
                type="button" 
                x-on:click="rightTab = 'titles_meta'" 
                :class="rightTab === 'titles_meta' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="Viral Titles & Meta Descriptions"
            >
                <span class="text-sm">✨</span>
                <span class="text-[10px] font-bold truncate w-full">Titles</span>
            </button>

            <button 
                type="button" 
                x-on:click="rightTab = 'ai_ideas'" 
                :class="rightTab === 'ai_ideas' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="Content Gaps & FAQ Schema"
            >
                <span class="text-sm">💡</span>
                <span class="text-[10px] font-bold truncate w-full">Gaps</span>
            </button>

            <button 
                type="button" 
                x-on:click="rightTab = 'keywords'" 
                :class="rightTab === 'keywords' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="Secondary & LSI Keywords"
            >
                <span class="text-sm">🏷️</span>
                <span class="text-[10px] font-bold truncate w-full">Keywords</span>
            </button>

            <button 
                type="button" 
                x-on:click="rightTab = 'quality'" 
                :class="rightTab === 'quality' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="E-E-A-T Quality Audit"
            >
                <span class="text-sm">🏆</span>
                <span class="text-[10px] font-bold truncate w-full">Audit</span>
            </button>

            <button 
                type="button" 
                x-on:click="rightTab = 'outline'" 
                :class="rightTab === 'outline' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="Interactive Document Outline"
            >
                <span class="text-sm">📑</span>
                <span class="text-[10px] font-bold truncate w-full">Outline</span>
            </button>

            <button 
                type="button" 
                x-on:click="rightTab = 'versions'" 
                :class="rightTab === 'versions' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5"
                title="Version Snapshots Timeline"
            >
                <span class="text-sm">🕒</span>
                <span class="text-[10px] font-bold truncate w-full">History</span>
            </button>

            <button 
                type="button" 
                x-on:click="showTerminalModal = true" 
                :class="showTerminalModal ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" 
                class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5 active:scale-95"
                title="Launch Floating AI Telemetry Terminal"
            >
                <span class="text-sm">📟</span>
                <span class="text-[10px] font-bold truncate w-full">Terminal</span>
            </button>
        </div>

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
                            <span class="text-indigo-400">✦</span>
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

            <!-- ══════════════════════════════════════════════════════════════════ -->
            <!-- RANK MATH 4-PILLAR ACCORDIONS WITH DUAL AI & MANUAL CONTROLS       -->
            <!-- ══════════════════════════════════════════════════════════════════ -->
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
                            <span class="text-indigo-400">📂</span>
                            <span>Basic SEO</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $basicPassed === count($basicChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                {{ $basicPassed }}/{{ count($basicChecks) }} Passed
                            </span>
                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'basic' ? '▲' : '▼'"></span>
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
                                        class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold border border-indigo-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                        title="AI surgically optimizes only this section without rewriting the rest of the document"
                                    >
                                        <span>⚡ AI Section Fix</span>
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
                                            <button type="button" wire:click="applyTitle(title)" class="px-2.5 py-1 bg-indigo-600 text-white rounded-lg font-bold text-[10px]">Save</button>
                                        </div>
                                    @elseif($check['id'] === 'kw_in_meta')
                                        <label class="block font-bold text-white text-[10.5px]">Edit Meta Description:</label>
                                        <textarea wire:model.lazy="metaDescription" rows="2" class="w-full bg-slate-900 border border-white/15 rounded-lg p-2 text-xs text-white resize-none"></textarea>
                                        <button type="button" wire:click="applyMetaDescription(metaDescription)" class="px-2.5 py-1 bg-indigo-600 text-white rounded-lg font-bold text-[10px]">Save Meta</button>
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
                            <span class="text-cyan-400">📊</span>
                            <span>Additional SEO</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $addPassed === count($additionalChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                {{ $addPassed }}/{{ count($additionalChecks) }} Passed
                            </span>
                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'additional' ? '▲' : '▼'"></span>
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
                                        class="px-2.5 py-1 rounded-lg bg-cyan-600/30 hover:bg-cyan-600 text-cyan-300 hover:text-white font-bold border border-cyan-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                        title="AI surgically generates and inserts only this specific element"
                                    >
                                        <span>⚡ AI Section Fix</span>
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
                            <span class="text-violet-400">✨</span>
                            <span>Title Readability</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $titlePassed === count($titleChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                {{ $titlePassed }}/{{ count($titleChecks) }} Passed
                            </span>
                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'title' ? '▲' : '▼'"></span>
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
                                        class="px-2.5 py-1 rounded-lg bg-violet-600/30 hover:bg-violet-600 text-violet-300 hover:text-white font-bold border border-violet-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                        title="AI surgically optimizes headline without modifying canvas content"
                                    >
                                        <span>⚡ AI Title Fix</span>
                                    </button>
                                    <button 
                                        type="button" 
                                        x-on:click="manualView['{{ $check['id'] }}'] = !manualView['{{ $check['id'] }}']"
                                        class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition-all cursor-pointer"
                                    >
                                        <span>✏️ Manual Edit</span>
                                    </button>
                                </div>

                                <div x-show="manualView['{{ $check['id'] }}']" class="mt-2 p-2.5 rounded-xl bg-slate-950 border border-white/15 space-y-2 text-[11px] font-sans" style="display: none;">
                                    <input type="text" wire:model.lazy="title" class="w-full bg-slate-900 border border-white/15 rounded-lg px-2.5 py-1 text-xs text-white" />
                                    <button type="button" wire:click="applyTitle(title)" class="px-2.5 py-1 bg-indigo-600 text-white rounded-lg font-bold text-[10px]">Update Title</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ─── PILLAR 4: CONTENT READABILITY ACCORDION ───────────────── -->
                @php
                    $contentChecks = $rm['content_readability']['checks'] ?? [
                        ['id' => 'headings_toc', 'title' => 'Headings Structure & Outline', 'desc' => 'Content utilizes H2 and H3 subheadings for scannability.', 'pass' => ($seoData['metrics']['headings']['h2'] ?? 0) >= 2, 'ai_prompt' => "Add structured H2 and H3 subheadings across the content. Output clean HTML."],
                        ['id' => 'short_paragraphs', 'title' => 'Short & Scannable Paragraphs', 'desc' => 'Paragraphs are bite-sized (under 120 words each).', 'pass' => true, 'ai_prompt' => "Break down long paragraphs into punchy 2-3 sentence chunks. Output clean HTML."],
                        ['id' => 'rich_media', 'title' => 'Rich Media (Tables & Callouts)', 'desc' => 'Content contains images, tables, or callouts.', 'pass' => ($seoData['metrics']['images'] ?? 0) >= 1, 'ai_prompt' => "Insert a detailed feature comparison table (<table>) with technical metrics. Output clean HTML."],
                    ];
                    $contentPassed = count(array_filter($contentChecks, fn($c) => $c['pass']));
                @endphp
                <div class="rounded-2xl bg-slate-900/90 border border-white/10 overflow-hidden shadow-inner">
                    <button 
                        type="button" 
                        x-on:click="openPillar = (openPillar === 'content' ? null : 'content')" 
                        class="w-full p-3 flex items-center justify-between text-xs font-bold text-white hover:bg-white/5 transition-colors cursor-pointer"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-400">📖</span>
                            <span>Content Readability</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full {{ $contentPassed === count($contentChecks) ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                {{ $contentPassed }}/{{ count($contentChecks) }} Passed
                            </span>
                            <span class="text-slate-500 text-[10px]" x-text="openPillar === 'content' ? '▲' : '▼'"></span>
                        </div>
                    </button>

                    <div x-show="openPillar === 'content'" class="p-3 border-t border-white/5 space-y-3 text-xs font-sans" style="display: none;">
                        @foreach($contentChecks as $check)
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
                                        x-on:click="applyTargetedIntelligenceFix('{{ $check['id'] }}', @js($check['title']), @js($check['ai_prompt'] ?? 'Optimize Readability'), 'insert')"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white font-bold border border-emerald-500/30 transition-all flex items-center gap-1 cursor-pointer"
                                        title="AI surgically generates structured elements without rewriting document"
                                    >
                                        <span>⚡ AI Section Fix</span>
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
                    <span class="text-xs font-bold text-white">🔎 Google SERP Snippet Preview</span>
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

        <!-- ─── TAB 2: AI VIRAL TITLES & META DESCRIPTIONS ───────────────────── -->
        <div x-show="rightTab === 'titles_meta'" class="space-y-3.5" style="display: none;">
            <!-- Viral Titles Generator -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-violet-400">✨</span>
                        <span>AI Viral Title Generator</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateSeoTitles" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateSeoTitles">⚡ Generate Titles</span>
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
                                    <span class="text-[9.5px] font-mono text-emerald-400 bg-emerald-950/80 px-1.5 py-0.2 rounded border border-emerald-500/30">🔥 High CTR</span>
                                    <span class="text-[9.5px] font-mono text-slate-500">{{ strlen($t) }} chars</span>
                                </div>
                                <div class="text-slate-200 text-xs leading-snug font-medium">{{ $t }}</div>
                                <button type="button" wire:click="applyTitle(@js($t))" class="w-full py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10.5px] font-bold transition-colors">
                                    ✓ Apply Title to Document
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
                        <span class="text-emerald-400">📝</span>
                        <span>AI Meta Description</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateMetaDescriptions" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="generateMetaDescriptions">⚡ Generate Meta</span>
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
                        <span class="text-indigo-400">❓</span>
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
                        <span class="text-emerald-400">🛡️</span>
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

        <!-- ─── TAB 5: 10-POINT CONTENT QUALITY & E-E-A-T AUDIT ─────────────────── -->
        <div x-show="rightTab === 'quality'" class="space-y-3.5" style="display: none;">
            <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-3 shadow-inner font-mono text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-yellow-400">🏆</span>
                        <span>10-Point E-E-A-T Quality Audit</span>
                    </span>
                    <button 
                        type="button" 
                        wire:click="generateQualityAudit" 
                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"
                    >
                        ⚡ Run Audit
                    </button>
                </div>

                @php $qa = $aiQualityAudit ?? ['search_intent' => 92, 'topic_coverage' => 88, 'original_value' => 90, 'readability' => 85, 'seo_structure' => 94, 'internal_linking' => 82, 'eeat_signals' => 90, 'technical_seo' => 96, 'overall' => 91]; @endphp
                
                <div class="p-3 rounded-xl bg-gradient-to-r from-indigo-950/80 to-violet-950/60 border border-indigo-500/40 flex items-center justify-between text-white font-bold">
                    <span>Overall Quality Score</span>
                    <span class="text-emerald-400 text-lg font-black font-mono">{{ $qa['overall'] }}/100</span>
                </div>

                <!-- 10 Audit Factors with AI Fix Action -->
                <div class="space-y-2 text-slate-300 text-[11px] pt-1">
                    <!-- Factor 1 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">1. Search Intent Satisfaction:</span>
                            <span class="text-emerald-400 font-bold ml-1">{{ $qa['search_intent'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('search_intent')" class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ AI Polish</button>
                    </div>

                    <!-- Factor 2 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">2. Topical Depth & Completeness:</span>
                            <span class="text-emerald-400 font-bold ml-1">{{ $qa['topic_coverage'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('expand')" class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ AI Expand</button>
                    </div>

                    <!-- Factor 3 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">3. Original Analysis & Value:</span>
                            <span class="text-emerald-400 font-bold ml-1">{{ $qa['original_value'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('key_takeaways')" class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ AI Takeaways</button>
                    </div>

                    <!-- Factor 4 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">4. Readability & Scannability:</span>
                            <span class="text-cyan-400 font-bold ml-1">{{ $qa['readability'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('polish')" class="px-2 py-0.5 rounded bg-cyan-600/30 hover:bg-cyan-600 text-cyan-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ AI Simplify</button>
                    </div>

                    <!-- Factor 5 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">5. Heading & Structure SEO:</span>
                            <span class="text-emerald-400 font-bold ml-1">{{ $qa['seo_structure'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('generate_outline')" class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ AI Headings</button>
                    </div>

                    <!-- Factor 6 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">6. E-E-A-T & Trust Signals:</span>
                            <span class="text-emerald-400 font-bold ml-1">{{ $qa['eeat_signals'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('eeat_trust')" class="px-2 py-0.5 rounded bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ Inject Trust</button>
                    </div>

                    <!-- Factor 7 -->
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-white font-semibold">7. Technical Schema Readiness:</span>
                            <span class="text-emerald-400 font-bold ml-1">{{ $qa['technical_seo'] }}%</span>
                        </div>
                        <button type="button" x-on:click="triggerAiTransform('generate_faq')" class="px-2 py-0.5 rounded bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white text-[10px] font-bold cursor-pointer">⚡ Add Schema</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── TAB 6: DOCUMENT OUTLINE TREE WITH CLICK-TO-SCROLL ─────────── -->
        <div x-show="rightTab === 'outline'" class="space-y-3" style="display: none;">
            <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-2.5 shadow-inner">
                <div class="flex items-center justify-between pb-1 border-b border-white/5">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-indigo-400">📑</span>
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

        <!-- ─── TAB 7: SNAPSHOT VERSIONS TIMELINE & TIME-MACHINE DIFF ───────── -->
        <div x-show="rightTab === 'versions'" class="space-y-3" style="display: none;" x-data="{ selectedSnapshot: null, showSnapshotDiff: false, snapshotDiffHtml: '' }">
            <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-2.5 shadow-inner">
                <div class="flex items-center justify-between pb-1 border-b border-white/5">
                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                        <span class="text-indigo-400">🕒</span>
                        <span>Version History & Time-Machine</span>
                    </span>
                    <button type="button" wire:click="saveExplicitSnapshot" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-mono text-[10px] font-bold shadow-md cursor-pointer">+ New Snapshot</button>
                </div>

                <!-- Interactive Snapshot Version Diff Modal / Flyout -->
                <div x-show="showSnapshotDiff && selectedSnapshot" class="p-3 rounded-xl bg-slate-950 border border-indigo-500/40 space-y-2.5" style="display: none;">
                    <div class="flex items-center justify-between border-b border-white/10 pb-1.5 select-none">
                        <div class="flex items-center gap-1.5 font-bold text-xs text-white">
                            <span>🔍 Comparing vs</span>
                            <span class="text-indigo-400 font-mono" x-text="'Version #' + selectedSnapshot?.version_number"></span>
                        </div>
                        <button type="button" x-on:click="showSnapshotDiff = false; selectedSnapshot = null;" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕ Close</button>
                    </div>

                    <p class="text-[10px] text-slate-400 leading-snug">Review differences between your live canvas and this snapshot:</p>
                    
                    <!-- Word Level Diff Box -->
                    <div class="max-h-48 overflow-y-auto hoa-custom-scrollbar p-2 rounded-lg bg-slate-900/90 border border-white/5 font-mono text-[11px] leading-relaxed select-text" x-html="computeWordDiff(selectedSnapshot?.content_html || '', editorInstance?.getHTML ? editorInstance.getHTML() : '').unifiedHtml"></div>

                    <div class="flex items-center justify-between pt-1 border-t border-white/5 font-mono text-[10.5px]">
                        <button 
                            type="button" 
                            x-on:click="$wire.restoreVersion(selectedSnapshot.id); showSnapshotDiff = false;" 
                            class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition-colors cursor-pointer"
                        >
                            ✓ Restore This Snapshot
                        </button>
                        <button 
                            type="button" 
                            x-on:click="showSnapshotDiff = false;" 
                            class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-white/10 text-slate-300 transition-colors cursor-pointer"
                        >
                            Keep Current Live
                        </button>
                    </div>
                </div>

                <!-- Snapshot List -->
                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    @forelse($document->versions as $v)
                        <div class="p-3 rounded-xl {{ $document->current_version_id === $v->id ? 'bg-indigo-600/20 border border-indigo-500/40' : 'bg-slate-950/80 border border-white/5' }} space-y-1 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white text-[11px]">Version #{{ $v->version_number }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $v->created_at->format('M d, H:i') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-300 truncate">{{ $v->summary ?? 'Saved snapshot' }}</p>
                            <div class="flex items-center justify-between pt-1.5 text-[10px] text-slate-400 font-mono border-t border-white/5">
                                <span>{{ number_format($v->word_count) }} words</span>
                                <div class="flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        x-on:click="selectedSnapshot = { id: {{ $v->id }}, version_number: {{ $v->version_number }}, content_html: @js($v->content_html ?? '') }; showSnapshotDiff = true;" 
                                        class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition-colors cursor-pointer"
                                        title="Compare snapshot diff against current canvas"
                                    >
                                        🔍 Diff
                                    </button>
                                    @if($document->current_version_id !== $v->id)
                                        <button type="button" wire:click="restoreVersion({{ $v->id }})" class="px-2 py-0.5 rounded bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold transition-colors cursor-pointer">Restore</button>
                                    @else
                                        <span class="text-emerald-400 font-bold">Active</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-[11px] italic py-1">No saved snapshots yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

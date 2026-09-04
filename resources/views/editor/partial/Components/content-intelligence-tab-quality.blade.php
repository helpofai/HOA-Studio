{{--
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - 10-Point E-E-A-T Quality Audit Tab
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

<!-- ─── TAB 5: 10-POINT CONTENT QUALITY & E-E-A-T AUDIT ─────────────────── -->
<div x-show="rightTab === 'quality'" class="space-y-3.5" style="display: none;">

    <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 space-y-3 shadow-inner font-mono text-xs">

        {{-- Header & Re-Audit Controls --}}
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-white flex items-center gap-1.5">
                <span class="text-yellow-400">🏆</span>
                <span>10-Point E-E-A-T Quality Audit</span>
            </span>

            <button 
                type="button" 
                wire:click="generateQualityAudit" 
                class="px-2.5 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer flex items-center gap-1"
                wire:loading.attr="disabled"
                title="Recalculate all 10 E-E-A-T quality dimensions"
            >
                <span wire:loading.remove wire:target="generateQualityAudit">⚡ Re-Audit</span>
                <span wire:loading wire:target="generateQualityAudit" class="flex items-center gap-1">
                    <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Auditing...
                </span>
            </button>
        </div>

        @php
            $qa = $aiQualityAudit ?? [];
            $factors = $qa['factors'] ?? null;

            // Defensive fallback if factors array is not yet loaded
            if (empty($factors)) {
                $factors = [
                    'search_intent' => [
                        'number' => 1,
                        'id' => 'search_intent',
                        'title' => 'Search Intent Satisfaction',
                        'category' => 'Relevance',
                        'score' => $qa['search_intent'] ?? 80,
                        'status' => 'Query intent alignment',
                        'desc' => 'Primary search intent alignment in title, opening hook, and subheadings.',
                        'action_type' => 'search_intent',
                        'button_label' => '⚡ Align Intent',
                        'button_class' => 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white',
                    ],
                    'topic_coverage' => [
                        'number' => 2,
                        'id' => 'topic_coverage',
                        'title' => 'Topical Depth & Comprehensiveness',
                        'category' => 'Content Depth',
                        'score' => $qa['topic_coverage'] ?? 78,
                        'status' => ($wordCount ?? 0) . ' words analyzed',
                        'desc' => 'Depth, word count volume, and exhaustive subtopic coverage.',
                        'action_type' => 'expand',
                        'button_label' => '⚡ AI Expand',
                        'button_class' => 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white',
                    ],
                    'original_value' => [
                        'number' => 3,
                        'id' => 'original_value',
                        'title' => 'Information Gain & Data Points',
                        'category' => 'Research',
                        'score' => $qa['original_value'] ?? 75,
                        'status' => 'Empirical statistics & metrics',
                        'desc' => 'Verifiable benchmarks, empirical metrics, and research figures.',
                        'action_type' => 'geo_data_points',
                        'button_label' => '⚡ Add Data',
                        'button_class' => 'bg-amber-600/30 hover:bg-amber-600 text-amber-300 hover:text-white',
                    ],
                    'readability' => [
                        'number' => 4,
                        'id' => 'readability',
                        'title' => 'Readability & Scannability',
                        'category' => 'User Experience',
                        'score' => $qa['readability'] ?? 82,
                        'status' => 'Flesch reading score evaluation',
                        'desc' => 'Flesch ease, sentence cadence, and absence of wall-of-text paragraphs.',
                        'action_type' => 'polish',
                        'button_label' => '⚡ Simplify',
                        'button_class' => 'bg-cyan-600/30 hover:bg-cyan-600 text-cyan-300 hover:text-white',
                    ],
                    'seo_structure' => [
                        'number' => 5,
                        'id' => 'seo_structure',
                        'title' => 'Heading Hierarchy & Structure',
                        'category' => 'Architecture',
                        'score' => $qa['seo_structure'] ?? 85,
                        'status' => 'H1-H2-H3 taxonomy',
                        'desc' => 'H1-H2-H3 logical hierarchy, bulleted lists, and scannable visual anchors.',
                        'action_type' => 'generate_outline',
                        'button_label' => '⚡ AI Structure',
                        'button_class' => 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white',
                    ],
                    'internal_linking' => [
                        'number' => 6,
                        'id' => 'internal_linking',
                        'title' => 'Internal Topic Cluster Links',
                        'category' => 'Site Silo',
                        'score' => $qa['internal_linking'] ?? 70,
                        'status' => 'Internal cluster equity flow',
                        'desc' => 'Topical cluster connections passing crawl equity to parent/child pages.',
                        'action_type' => 'custom',
                        'custom_prompt' => 'Analyze this document and suggest 3 contextual internal topic cluster links with descriptive anchor text to build a topical silo.',
                        'button_label' => '⚡ Auto-Cluster',
                        'button_class' => 'bg-violet-600/30 hover:bg-violet-600 text-violet-300 hover:text-white',
                    ],
                    'outbound_citations' => [
                        'number' => 7,
                        'id' => 'outbound_citations',
                        'title' => 'Authoritative Outbound Citations',
                        'category' => 'Authority',
                        'score' => $qa['outbound_citations'] ?? 72,
                        'status' => 'External source attributions',
                        'desc' => 'External references and study attributions supporting key claims.',
                        'action_type' => 'seo_fix_citations',
                        'button_label' => '⚡ Add Citations',
                        'button_class' => 'bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white',
                    ],
                    'eeat_signals' => [
                        'number' => 8,
                        'id' => 'eeat_signals',
                        'title' => 'First-Hand Experience & Trust (E-E-A-T)',
                        'category' => 'Trust & Experience',
                        'score' => $qa['eeat_signals'] ?? 68,
                        'status' => 'Experiential testing signals',
                        'desc' => 'Tangible proof of personal testing, laboratory benchmarks, and author expertise.',
                        'action_type' => 'eeat_trust',
                        'button_label' => '⚡ Inject Trust',
                        'button_class' => 'bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white',
                    ],
                    'geo_readiness' => [
                        'number' => 9,
                        'id' => 'geo_readiness',
                        'title' => 'Google AI Overviews & GEO Readiness',
                        'category' => 'AI Search',
                        'score' => $qa['geo_readiness'] ?? 65,
                        'status' => 'Direct snippet & comparison table',
                        'desc' => 'Direct 40-60w answer definitions, comparison matrices, and PAA queries.',
                        'action_type' => 'geo_direct_answer',
                        'button_label' => '⚡ AI Overview',
                        'button_class' => 'bg-purple-600/30 hover:bg-purple-600 text-purple-300 hover:text-white',
                    ],
                    'technical_seo' => [
                        'number' => 10,
                        'id' => 'technical_seo',
                        'title' => 'Technical Schema.org & Meta Markup',
                        'category' => 'Technical',
                        'score' => $qa['technical_seo'] ?? 88,
                        'status' => 'Schema.org JSON-LD & meta tags',
                        'desc' => 'Validated JSON-LD schema (Article, FAQPage) and optimized metadata.',
                        'action_type' => 'generate_faq',
                        'button_label' => '⚡ Add Schema',
                        'button_class' => 'bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white',
                    ],
                ];
            }

            $overall = $qa['overall'] ?? (int) round(array_sum(array_column($factors, 'score')) / count($factors));
            $passedCount = $qa['passed_count'] ?? count(array_filter($factors, fn($f) => $f['score'] >= 75));
            $grade = $qa['grade'] ?? ($overall >= 80 ? '✨ High Quality (A)' : '⚡ Publication Ready (B)');

            $overallColor = match(true) {
                $overall >= 80 => 'text-emerald-400 border-emerald-500/40 from-emerald-950/60 to-slate-900/80',
                $overall >= 65 => 'text-amber-400 border-amber-500/40 from-amber-950/60 to-slate-900/80',
                default => 'text-rose-400 border-rose-500/40 from-rose-950/60 to-slate-900/80',
            };
        @endphp

        {{-- Overall Score & Grade Card --}}
        <div class="p-3.5 rounded-2xl bg-gradient-to-r {{ $overallColor }} border shadow-lg space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Overall Content Quality</div>
                    <div class="text-xs font-bold text-white mt-0.5">{{ $grade }}</div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-black font-mono {{ $overall >= 80 ? 'text-emerald-400' : ($overall >= 65 ? 'text-amber-400' : 'text-rose-400') }}">
                        {{ $overall }}<span class="text-xs text-slate-400 font-normal">/100</span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono">{{ $passedCount }}/10 Factors Met</div>
                </div>
            </div>

            {{-- 1-Click Master E-E-A-T Auto-Heal Button --}}
            <button 
                type="button" 
                x-on:click="triggerAiTransform('seo_auto_heal', 'Transform this entire article to achieve 100/100 E-E-A-T Quality: Inject first-hand testing phrases (\'in our lab tests\', \'we observed\'), add 3-4 verifiable benchmark data points with percentages, cite authoritative external sources, insert a concise 40-60 word direct definition box under the first H2, add a structured comparison table, format clear H2/H3 headings, and guarantee deep topical completeness. Output clean HTML.', 'document')"
                class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 hover:from-emerald-500 hover:to-indigo-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                title="Automatically boost content across all 10 E-E-A-T dimensions"
            >
                <span>✨</span>
                <span>1-Click E-E-A-T Holistic Auto-Heal</span>
            </button>
        </div>

        {{-- 10 True E-E-A-T Factors --}}
        <div class="space-y-2 text-slate-300 text-[11px] pt-1">
            @foreach($factors as $key => $factor)
                @php
                    $fScore = $factor['score'];
                    $scoreColor = match(true) {
                        $fScore >= 80 => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30',
                        $fScore >= 60 => 'text-amber-400 bg-amber-500/10 border-amber-500/30',
                        default => 'text-rose-400 bg-rose-500/10 border-rose-500/30',
                    };
                    $barGradient = match(true) {
                        $fScore >= 80 => 'from-emerald-500 to-teal-400',
                        $fScore >= 60 => 'from-amber-500 to-yellow-400',
                        default => 'from-rose-500 to-pink-500',
                    };
                @endphp

                <div class="p-2.5 rounded-xl bg-slate-950/60 border border-white/5 space-y-1.5 hover:border-white/15 transition-all">
                    {{-- Row 1: Title, Category & Score --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0 flex-1">
                            <span class="text-white font-bold truncate text-[11px]">
                                {{ $factor['number'] ?? $loop->iteration }}. {{ $factor['title'] }}
                            </span>
                            @if(!empty($factor['category']))
                                <span class="px-1.5 py-0.2 rounded-md bg-white/5 border border-white/10 text-[9px] text-slate-400 uppercase tracking-wider shrink-0">
                                    {{ $factor['category'] }}
                                </span>
                            @endif
                        </div>

                        <span class="px-1.5 py-0.5 rounded-md border text-[10.5px] font-mono font-bold shrink-0 {{ $scoreColor }}">
                            {{ $fScore }}%
                        </span>
                    </div>

                    {{-- Row 2: Micro Progress Bar --}}
                    <div class="w-full bg-slate-800/80 rounded-full h-1 overflow-hidden">
                        <div class="bg-gradient-to-r {{ $barGradient }} h-1 rounded-full transition-all duration-500" style="width: {{ $fScore }}%;"></div>
                    </div>

                    {{-- Row 3: Diagnostic Status & 1-Click Action --}}
                    <div class="flex items-center justify-between gap-2 pt-0.5 text-[10px]">
                        <span class="text-slate-400 truncate flex-1" title="{{ $factor['status'] }}">
                            {{ $factor['status'] }}
                        </span>

                        <button 
                            type="button" 
                            x-on:click="triggerAiTransform('{{ $factor['action_type'] }}'{{ !empty($factor['custom_prompt']) ? ", '" . addslashes($factor['custom_prompt']) . "'" : '' }})" 
                            class="px-2 py-0.5 rounded-lg text-[9.5px] font-bold transition-all cursor-pointer shrink-0 shadow-sm {{ $factor['button_class'] ?? 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white' }}"
                            title="Run AI optimization for {{ $factor['title'] }}"
                        >
                            {{ $factor['button_label'] }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- E-E-A-T Quality Framework Reference Guide --}}
        <div x-data="{ showEeatGuide: false }" class="pt-1">
            <button 
                type="button" 
                x-on:click="showEeatGuide = !showEeatGuide"
                class="w-full text-left p-2 rounded-xl bg-slate-950/40 hover:bg-slate-950/70 border border-white/5 flex items-center justify-between text-[10.5px] text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
            >
                <span class="flex items-center gap-1.5">
                    <span class="text-indigo-400">ℹ️</span>
                    <span class="font-semibold">What is the 10-Point E-E-A-T Standard?</span>
                </span>
                <span class="text-[10px] text-slate-500 font-mono" x-text="showEeatGuide ? '▲ Less' : '▼ More'"></span>
            </button>

            <div x-show="showEeatGuide" x-collapse class="mt-2 p-3 rounded-xl bg-slate-950/70 border border-indigo-500/20 space-y-2 text-[10px] text-slate-300 font-sans leading-relaxed">
                <p>
                    <strong class="text-white">Google E-E-A-T</strong> (Experience, Expertise, Authoritativeness, Trustworthiness) and <strong class="text-white">GEO</strong> (Generative Engine Optimization) evaluate whether content is written by authentic practitioners and structured for AI answer extraction:
                </p>
                <ul class="space-y-1 list-disc list-inside text-slate-400">
                    <li><strong class="text-indigo-300">Experience</strong>: First-hand testing, empirical data points, and authentic test results.</li>
                    <li><strong class="text-indigo-300">Expertise</strong>: Deep topic coverage, proper taxonomy (H1-H2-H3), and actionable takeaways.</li>
                    <li><strong class="text-indigo-300">Authoritativeness</strong>: External citations to reputable studies and internal cluster links.</li>
                    <li><strong class="text-indigo-300">Trustworthiness</strong>: Valid Schema.org JSON-LD, objective tone, and absence of keyword stuffing.</li>
                    <li><strong class="text-indigo-300">GEO Readiness</strong>: Direct 40-60 word definition snippets and comparison matrices prioritized by AI engines.</li>
                </ul>
            </div>
        </div>

    </div>

</div>




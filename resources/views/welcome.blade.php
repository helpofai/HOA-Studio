{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/
--}}

<x-layouts.app title="HelpOfAi Studio (HOA-Studio) — Universal Multi-Editor & AI Workspace">
    <!-- Ambient Background Lighting -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] bg-purple-600/20 rounded-full blur-[140px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[34rem] h-[34rem] bg-indigo-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-2/3 -left-20 w-[30rem] h-[30rem] bg-cyan-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[40rem] h-[40rem] bg-purple-900/20 rounded-full blur-[160px]"></div>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 w-full border-b border-white/5 bg-slate-950/70 backdrop-blur-xl transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-cyan-400 p-[1px] shadow-lg shadow-indigo-500/20 group-hover:shadow-indigo-500/40 transition-all duration-300">
                    <div class="w-full h-full bg-slate-950 rounded-[11px] flex items-center justify-center">
                        <span class="font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300 tracking-wider text-xs">HOA</span>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold text-white tracking-tight group-hover:text-indigo-300 transition-colors">HelpOfAi Studio</span>
                        <x-glass.badge variant="violet" class="hidden sm:inline-flex text-[10px] py-0.5 px-2">v2.0 Architecture</x-glass.badge>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-none">Universal Multi-Editor Platform</p>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-300">
                <a href="#simulator" class="hover:text-white transition-colors">Studio Simulator</a>
                <a href="#engines" class="hover:text-white transition-colors">8 Editor Engines</a>
                <a href="#features" class="hover:text-white transition-colors">Architecture</a>
                <a href="#omniroute" class="hover:text-white transition-colors">OmniRoute AI</a>
                <a href="#glass-system" class="hover:text-white transition-colors">Design System</a>
                <a href="#pricing" class="hover:text-white transition-colors">Plans</a>
            </nav>

            <!-- Navigation Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex">
                            <x-glass.button variant="glass" size="sm" class="border-violet-500/40 text-violet-300 hover:bg-violet-600/20">
                                🛡️ Admin Panel
                            </x-glass.button>
                        </a>
                    @endif

                    <a href="{{ route('editor') }}">
                        <x-glass.button variant="primary" size="sm" class="shadow-indigo-500/30">
                            ✍️ Launch Editor
                        </x-glass.button>
                    </a>

                    <a href="{{ route('profile') }}" class="flex items-center gap-2 p-1 rounded-xl glass-subtle hover:border-white/20 transition-all" title="User Profile">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-xs text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </a>
                @else
                    <a href="{{ route('login') }}">
                        <x-glass.button variant="secondary" size="sm">
                            Sign In
                        </x-glass.button>
                    </a>

                    <a href="{{ route('register') }}">
                        <x-glass.button variant="primary" size="sm" class="shadow-indigo-500/30">
                            Get Started Free
                        </x-glass.button>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Landing Content -->
    <main class="flex-1">
        <!-- ========================================================================= -->
        <!-- HERO SECTION                                                              -->
        <!-- ========================================================================= -->
        <section class="relative pt-16 pb-20 sm:pt-24 sm:pb-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Stack Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-subtle text-xs text-indigo-300 mb-8 border border-indigo-500/20 shadow-inner">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-semibold text-white">Universal Multi-Editor Architecture</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-cyan-300">8 Engines · Canonical Data Model · OmniRoute AI</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.1] max-w-5xl mx-auto mb-8">
                    One Document. Many Editors. <br class="hidden sm:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">
                        Zero Vendor Lock-In.
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-base sm:text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed font-normal">
                    Experience the next generation of content creation with Tiptap, Gutenberg blocks, Notion canvas, Markdown split-preview, and Plain Text—powered by an intelligent Three-Column AI Command Center, real-time SEO intelligence, and decoupled AI routing.
                </p>

                <!-- Dual Action CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto mb-16">
                    @auth
                        <a href="{{ route('editor') }}" class="w-full sm:w-auto">
                            <x-glass.button variant="primary" size="lg" class="w-full px-8 shadow-xl shadow-indigo-600/30">
                                ✍️ Open AI Editor &rarr;
                            </x-glass.button>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto">
                            <x-glass.button variant="primary" size="lg" class="w-full px-8 shadow-xl shadow-indigo-600/30">
                                Launch Free Workspace &rarr;
                            </x-glass.button>
                        </a>
                    @endauth
                    <x-glass.button variant="glass" size="lg" class="w-full sm:w-auto px-8" onclick="document.getElementById('simulator').scrollIntoView({behavior: 'smooth'})">
                        Explore Live Simulator
                    </x-glass.button>
                </div>

                <!-- Live Architectural Metric Badges -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-indigo-400">8 Engines</div>
                        <div class="text-xs text-slate-400 mt-1">Universal Switcher</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-cyan-400">3-Column</div>
                        <div class="text-xs text-slate-400 mt-1">AI Workspace UI</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-purple-400">100% SEO</div>
                        <div class="text-xs text-slate-400 mt-1">Real-time Score & Matrix</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-emerald-400">OmniRoute</div>
                        <div class="text-xs text-slate-400 mt-1">Decoupled AI Proxy</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- INTERACTIVE THREE-COLUMN STUDIO SIMULATOR                                 -->
        <!-- ========================================================================= -->
        <section id="simulator" class="py-16 sm:py-24 border-y border-white/5 relative bg-slate-950/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <x-glass.badge variant="cyan" class="mb-4">Interactive Workspace Simulator</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Three-Column AI Content Operating System</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Test live engine switching between Tiptap, Gutenberg, Notion, and Markdown, trigger AI commands, and inspect real-time SEO intelligence.</p>
                </div>

                <!-- Simulator Container (Alpine.js State) -->
                <div 
                    x-data="{
                        activeEngine: 'tiptap', // tiptap, gutenberg, notion, markdown_split, plaintext
                        activeTab: 'seo', // seo, keywords, recs, outline
                        selectedModel: 'Auto (OmniRoute AI)',
                        isGenerating: false,
                        aiPrompt: 'Write an executive summary of next-gen decoupled AI gateways',
                        wordCount: 342,
                        seoScore: 94,
                        readScore: 78,
                        editorContent: {
                            tiptap: '<h2>Next-Gen Decoupled AI Gateways in 2026</h2><p>Modern content production platforms demand high-throughput intelligence routing with zero vendor lock-in. By abstracting the AI model gateway through an intelligent proxy, SaaS platforms eliminate dependencies while preserving sub-second SSE streaming across shared and dedicated host environments.</p><blockquote>Production rule: The canonical document must not belong to an editor engine.</blockquote>',
                            gutenberg: '<div class="p-3 bg-slate-900/60 rounded-xl border border-indigo-500/20 mb-3"><span class="text-[10px] font-mono text-indigo-400 uppercase font-bold">❖ Heading Block (H2)</span><h2 class="text-xl font-bold text-white mt-1">Next-Gen Decoupled AI Gateways</h2></div><div class="p-3 bg-slate-900/60 rounded-xl border border-white/5"><span class="text-[10px] font-mono text-slate-500 uppercase font-bold">❖ Paragraph Block</span><p class="text-sm text-slate-300 mt-1">Modern distributed architectures enforce modular block boundaries with atomic state synchronization...</p></div>',
                            notion: '<div class="space-y-2"><div class="flex items-center gap-2 text-white font-bold text-lg"><span class="text-slate-500 text-xs">⠿</span> Next-Gen Decoupled AI Gateways</div><div class="p-3 rounded-xl bg-violet-950/30 border border-violet-500/30 flex items-start gap-2 text-xs text-slate-200"><span class="text-sm">💡</span> Notion-style callout block with live drag handle and slash command integration.</div><div class="flex items-center gap-2 text-sm text-slate-300"><span class="text-slate-500 text-xs">⠿</span> Type / for Notion block commands...</div></div>',
                            markdown_split: '<div class="grid grid-cols-2 gap-3 h-48"><div class="p-3 bg-slate-950 rounded-xl font-mono text-xs text-indigo-300 border border-white/10"><span class="text-slate-500"># Markdown Source</span><br/>## Next-Gen Decoupled AI Gateways<br/><br/>Modern content platforms demand **zero vendor lock-in**...</div><div class="p-3 bg-slate-900/50 rounded-xl text-xs text-slate-300 border border-white/10"><span class="text-slate-500 font-mono"># Live HTML Preview</span><br/><strong class="text-white text-sm block">Next-Gen Decoupled AI Gateways</strong>Modern content platforms demand <strong class="text-white">zero vendor lock-in</strong>...</div></div>',
                            plaintext: '<pre class="p-4 bg-slate-950 text-slate-300 font-mono text-xs leading-relaxed rounded-xl">Next-Gen Decoupled AI Gateways in 2026

Modern content production platforms demand high-throughput intelligence routing with zero vendor lock-in. Clean, distraction-free environment for pure text authoring.</pre>'
                        },
                        runAiAction(type) {
                            this.isGenerating = true;
                            setTimeout(() => {
                                if (type === 'generate') {
                                    this.editorContent.tiptap += '<p><strong>AI Generated Addendum:</strong> Real-time token multiplexing allows seamless fallback between Claude 3.7, GPT-4o, and local Ollama nodes without interrupting the active writer session.</p>';
                                    this.wordCount += 38;
                                    this.seoScore = 98;
                                } else if (type === 'rewrite') {
                                    this.editorContent.tiptap = '<h2>Next-Gen Decoupled AI Gateways in 2026</h2><p><em>Polished by AI:</em> High-throughput intelligence routing and vendor independence define production content infrastructure in 2026, delivering sub-second response times across distributed teams.</p>';
                                }
                                this.isGenerating = false;
                            }, 500);
                        }
                    }" 
                    class="glass-elevated rounded-3xl overflow-hidden border border-white/15 shadow-2xl"
                >
                    <!-- Window Topbar -->
                    <div class="px-6 py-3.5 bg-slate-900/90 border-b border-white/10 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                            </div>
                            <span class="text-xs font-mono text-slate-300 font-semibold ml-2">HelpOfAi Studio &mdash; Production Workspace</span>
                        </div>

                        <!-- Engine Selector Switcher in Simulator -->
                        <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-white/10 text-xs font-mono">
                            <span class="text-[10px] text-indigo-400 font-bold px-2">⚡ Switch Engine:</span>
                            <button type="button" x-on:click="activeEngine = 'tiptap'" :class="activeEngine === 'tiptap' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-colors">Tiptap</button>
                            <button type="button" x-on:click="activeEngine = 'gutenberg'" :class="activeEngine === 'gutenberg' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-colors">Gutenberg</button>
                            <button type="button" x-on:click="activeEngine = 'notion'" :class="activeEngine === 'notion' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-colors">Notion</button>
                            <button type="button" x-on:click="activeEngine = 'markdown_split'" :class="activeEngine === 'markdown_split' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-colors">Markdown Split</button>
                            <button type="button" x-on:click="activeEngine = 'plaintext'" :class="activeEngine === 'plaintext' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-colors">Plain Text</button>
                        </div>
                    </div>

                    <!-- 3-Column Simulator Body -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[520px]">
                        <!-- Column 1: AI Command Center (3 cols) -->
                        <div class="lg:col-span-3 border-r border-white/10 p-4 bg-slate-950/70 flex flex-col justify-between text-xs space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-white/5">
                                    <span class="font-bold text-white uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                        AI Command Center
                                    </span>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[11px] font-bold text-slate-300">✦ Ask AI</label>
                                    <textarea x-model="aiPrompt" rows="2" class="w-full bg-slate-900 border border-white/15 rounded-xl p-2 text-xs text-white placeholder-slate-500 focus:outline-none resize-none font-sans"></textarea>
                                    <button type="button" x-on:click="runAiAction('generate')" :disabled="isGenerating" class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold text-xs shadow-md flex items-center justify-center gap-2">
                                        <span x-show="!isGenerating">✦ Generate Output</span>
                                        <span x-show="isGenerating" class="animate-spin">⟳</span>
                                    </button>
                                </div>

                                <!-- Quick Actions -->
                                <div class="space-y-1.5 pt-2 border-t border-white/5">
                                    <span class="text-[10px] uppercase font-bold text-slate-500">Quick Actions</span>
                                    <div class="grid grid-cols-2 gap-1 text-[11px]">
                                        <button type="button" x-on:click="runAiAction('generate')" class="p-1.5 rounded-lg bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left">✦ Generate</button>
                                        <button type="button" x-on:click="runAiAction('rewrite')" class="p-1.5 rounded-lg bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left">↻ Rewrite</button>
                                        <button type="button" x-on:click="runAiAction('improve')" class="p-1.5 rounded-lg bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left">✧ Improve</button>
                                        <button type="button" x-on:click="runAiAction('expand')" class="p-1.5 rounded-lg bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left">+ Expand</button>
                                    </div>
                                </div>

                                <!-- Context -->
                                <div class="space-y-1 pt-2 border-t border-white/5 text-[11px] text-slate-400">
                                    <label class="flex items-center gap-1.5 text-slate-300"><input type="checkbox" checked class="rounded bg-slate-900 border-white/20 text-indigo-600"> Current Document</label>
                                    <label class="flex items-center gap-1.5 text-slate-300"><input type="checkbox" checked class="rounded bg-slate-900 border-white/20 text-indigo-600"> Brand Voice Profile</label>
                                </div>
                            </div>

                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-white/5 text-[10px] text-slate-400 font-mono">
                                <span class="text-indigo-300 font-bold block">OmniRoute Routing:</span>
                                <span>Claude 3.7 Sonnet (Auto-Selected)</span>
                            </div>
                        </div>

                        <!-- Column 2: Content Workspace (6 cols) -->
                        <div class="lg:col-span-6 p-5 flex flex-col justify-between bg-slate-900/30 border-r border-white/10">
                            <div class="space-y-4">
                                <!-- Capability-Aware Ribbon in Simulator -->
                                <div class="flex flex-wrap items-center justify-between gap-2 p-2 bg-slate-900/80 rounded-xl border border-white/10 text-xs">
                                    <div class="flex items-center gap-1 font-bold text-slate-300">
                                        <span class="px-2 py-0.5 rounded bg-indigo-600/20 text-indigo-300 font-mono text-[10px]" x-text="activeEngine.toUpperCase()"></span>
                                        <span class="px-1.5 py-0.5 rounded hover:bg-white/10">H1</span>
                                        <span class="px-1.5 py-0.5 rounded hover:bg-white/10">H2</span>
                                        <span class="px-1.5 py-0.5 rounded hover:bg-white/10">B</span>
                                        <span class="px-1.5 py-0.5 rounded hover:bg-white/10 italic">I</span>
                                        <span class="px-1.5 py-0.5 rounded hover:bg-white/10 font-mono">&lt;/&gt;</span>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400">
                                        <span x-text="wordCount + ' words'"></span> &bull; 
                                        <span class="text-indigo-300">2m read</span>
                                    </div>
                                </div>

                                <!-- Dynamic Editor Surface -->
                                <div class="min-h-[300px] p-4 rounded-2xl bg-slate-950/60 border border-white/5 overflow-y-auto max-h-[360px]">
                                    <div class="prose prose-invert prose-sm max-w-none text-slate-200" x-html="editorContent[activeEngine]"></div>
                                </div>
                            </div>

                            <!-- Bottom Simulator Status Bar -->
                            <div class="pt-3 border-t border-white/5 flex items-center justify-between text-[11px] font-mono text-slate-400">
                                <span>Engine: <strong class="text-white" x-text="activeEngine"></strong></span>
                                <span class="text-emerald-400">● Autosaved at 01:22 AM</span>
                            </div>
                        </div>

                        <!-- Column 3: Content Intelligence (3 cols) -->
                        <div class="lg:col-span-3 p-4 bg-slate-950/70 flex flex-col justify-between text-xs space-y-4">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-white/5">
                                    <span class="font-bold text-white uppercase tracking-wider text-[10px]">Content Intelligence</span>
                                </div>

                                <!-- Intelligence Tab Selector -->
                                <div class="flex items-center gap-1 bg-slate-900 p-1 rounded-xl border border-white/10 text-[10px] font-mono">
                                    <button type="button" x-on:click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg">SEO</button>
                                    <button type="button" x-on:click="activeTab = 'keywords'" :class="activeTab === 'keywords' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg">Keys</button>
                                    <button type="button" x-on:click="activeTab = 'recs'" :class="activeTab === 'recs' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg">Recs</button>
                                    <button type="button" x-on:click="activeTab = 'outline'" :class="activeTab === 'outline' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg">Tree</button>
                                </div>

                                <!-- Tab 1: SEO -->
                                <div x-show="activeTab === 'seo'" class="space-y-3">
                                    <div class="grid grid-cols-2 gap-2 text-center">
                                        <div class="p-3 rounded-xl border border-emerald-500/40 bg-emerald-950/30">
                                            <span class="text-2xl font-black font-mono text-emerald-400" x-text="seoScore">94</span>
                                            <span class="text-[9px] uppercase block font-bold text-slate-300">SEO Score</span>
                                        </div>
                                        <div class="p-3 rounded-xl border border-cyan-500/40 bg-cyan-950/30">
                                            <span class="text-2xl font-black font-mono text-cyan-400" x-text="readScore">78</span>
                                            <span class="text-[9px] uppercase block font-bold text-slate-300">Reading Ease</span>
                                        </div>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-900/80 border border-white/5 space-y-1 font-mono text-[10.5px]">
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword in Title:</span><span class="text-emerald-400 font-bold">✓ Yes</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword in Intro:</span><span class="text-emerald-400 font-bold">✓ Yes</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Density:</span><span class="text-emerald-400 font-bold">2.1% (Optimal)</span></div>
                                    </div>
                                </div>

                                <!-- Tab 2: Recommendations -->
                                <div x-show="activeTab === 'recs'" class="space-y-2" style="display: none;">
                                    <div class="p-2 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px]">✓ Perfect H2 heading hierarchy</div>
                                    <div class="p-2 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px]">✓ Flesch reading ease: Standard (Grade 8)</div>
                                    <div class="p-2 rounded-xl bg-yellow-950/20 border border-yellow-500/30 text-yellow-300 text-[11px]">⚠ Add FAQ section for SERP rich snippet</div>
                                </div>

                                <!-- Tab 3: Outline -->
                                <div x-show="activeTab === 'outline'" class="space-y-1 font-mono text-[11px]" style="display: none;">
                                    <div class="p-1.5 rounded-lg bg-indigo-600/20 text-indigo-300 font-bold">H2 Next-Gen Decoupled AI Gateways</div>
                                    <div class="p-1.5 rounded-lg text-slate-400 pl-4">H3 Multi-Provider Routing</div>
                                    <div class="p-1.5 rounded-lg text-slate-400 pl-4">H3 Canonical Storage Model</div>
                                </div>
                            </div>

                            <a href="{{ route('editor') }}">
                                <x-glass.button variant="primary" size="sm" class="w-full">
                                    Open Full Studio &rarr;
                                </x-glass.button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 8-ENGINE ARCHITECTURE SHOWCASE                                            -->
        <!-- ========================================================================= -->
        <section id="engines" class="py-16 sm:py-24 border-b border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="violet" class="mb-4">Universal Editor Engine Platform</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">8 Dedicated Editor Engines</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Every document is preserved in a semantic canonical model with dedicated adapters for lossless translation across any writing surface.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Engine 1: Tiptap -->
                    <x-glass.card variant="standard" class="hover:border-indigo-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 font-bold text-lg">
                            ✨
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Tiptap ProseMirror</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Fast, modern rich-text editing with floating bubble selection bars, slash commands, and atomic table mutations.
                        </p>
                        <div class="text-[10px] font-mono text-indigo-300 bg-indigo-950/40 p-1.5 rounded-lg border border-indigo-500/20">
                            richText: true &bull; blocks: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 2: Gutenberg -->
                    <x-glass.card variant="standard" class="hover:border-purple-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 font-bold text-lg">
                            ❖
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Gutenberg Block Canvas</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            WordPress-compatible block canvas with modular paragraph, heading, list, quote, and code unit inserters.
                        </p>
                        <div class="text-[10px] font-mono text-purple-300 bg-purple-950/40 p-1.5 rounded-lg border border-purple-500/20">
                            blocks: true &bull; moveControls: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 3: Notion -->
                    <x-glass.card variant="standard" class="hover:border-violet-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400 mb-4 font-bold text-lg">
                            ⠿
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Notion Block Canvas</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Draggable block handles, callout containers, toggle lists, and slash command keyboard accelerators.
                        </p>
                        <div class="text-[10px] font-mono text-violet-300 bg-violet-950/40 p-1.5 rounded-lg border border-violet-500/20">
                            dragHandles: true &bull; callouts: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 4: Markdown Split -->
                    <x-glass.card variant="standard" class="hover:border-cyan-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-4 font-bold text-lg">
                            📝
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Markdown Split Preview</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Dual-pane monospace Markdown source with real-time sanitized HTML live preview and synced scrolling.
                        </p>
                        <div class="text-[10px] font-mono text-cyan-300 bg-cyan-950/40 p-1.5 rounded-lg border border-cyan-500/20">
                            markdown: true &bull; splitLive: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 5: Raw Markdown -->
                    <x-glass.card variant="standard" class="hover:border-blue-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 mb-4 font-bold text-lg">
                            ⌨️
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Raw Markdown</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Distraction-free, zero-DOM-overhead monospace Markdown editor optimized for speed and pure key strokes.
                        </p>
                        <div class="text-[10px] font-mono text-blue-300 bg-blue-950/40 p-1.5 rounded-lg border border-blue-500/20">
                            markdown: true &bull; fastMonospace: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 6: HTML Source -->
                    <x-glass.card variant="standard" class="hover:border-amber-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4 font-bold text-lg">
                            💻
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">HTML Source Editor</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Raw HTML5 code editing with sandbox sanitization for developers, marketers, and power users.
                        </p>
                        <div class="text-[10px] font-mono text-amber-300 bg-amber-950/40 p-1.5 rounded-lg border border-amber-500/20">
                            htmlSource: true &bull; sanitized: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 7: Plain Text -->
                    <x-glass.card variant="standard" class="hover:border-slate-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-slate-500/10 border border-slate-500/20 flex items-center justify-center text-slate-400 mb-4 font-bold text-lg">
                            📄
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Plain Text Editor</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Clean, lightweight text environment with zero formatting artifacts and lossy conversion warnings.
                        </p>
                        <div class="text-[10px] font-mono text-slate-300 bg-slate-900/60 p-1.5 rounded-lg border border-white/10">
                            plainText: true &bull; lightweight: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 8: Future Engines -->
                    <x-glass.card variant="standard" class="hover:border-pink-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 mb-4 font-bold text-lg">
                            🔮
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Future Extensibility</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Plug in spreadsheet canvases, visual builders, and whiteboards via declarative `EditorAdapterInterface`.
                        </p>
                        <div class="text-[10px] font-mono text-pink-300 bg-pink-950/40 p-1.5 rounded-lg border border-pink-500/20">
                            strategyPattern: true &bull; zeroRefactor: true
                        </div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- CORE FEATURE ARCHITECTURE PROTOCOLS                                      -->
        <!-- ========================================================================= -->
        <section id="features" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="cyan" class="mb-4">Enterprise Protocols</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Feature-First Production Architecture</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Every domain contains dedicated Actions, DTOs, Policies, and Livewire components engineered for maximum cohesion.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1: OmniRoute -->
                    <x-glass.card variant="standard" class="hover:border-purple-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 font-bold text-lg">⚡</div>
                        <h3 class="text-base font-bold text-white mb-2">OmniRoute AI Gateway</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Decoupled multi-provider proxy with intelligent failover, streaming token parsing, pre-flight budget checks, and support for Claude 3.7, GPT-4o, Gemini 2.0, DeepSeek, and local Ollama.
                        </p>
                        <div class="text-[11px] font-mono text-purple-300">app/Features/AI/</div>
                    </x-glass.card>

                    <!-- Feature 2: SEO -->
                    <x-glass.card variant="standard" class="hover:border-emerald-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4 font-bold text-lg">🎯</div>
                        <h3 class="text-base font-bold text-white mb-2">Real-Time SEO Intelligence</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Real-time keyword placement matrix (title, intro, headings, density), Flesch-Kincaid reading ease index, actionable recommendations checklist, and AI metadata generators.
                        </p>
                        <div class="text-[11px] font-mono text-emerald-300">app/Features/SEO/</div>
                    </x-glass.card>

                    <!-- Feature 3: Versioning -->
                    <x-glass.card variant="standard" class="hover:border-indigo-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 font-bold text-lg">🕒</div>
                        <h3 class="text-base font-bold text-white mb-2">Snapshot Time-Travel</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Atomic snapshots created on manual save, editor switch, and major AI actions. 1-click restore rollback without losing recent edits.
                        </p>
                        <div class="text-[11px] font-mono text-indigo-300">app/Features/Documents/Actions/</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 4-TIER GLASSMORPHISM DESIGN SYSTEM                                        -->
        <!-- ========================================================================= -->
        <section id="glass-system" class="py-16 sm:py-24 border-t border-white/5 bg-slate-950/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="emerald" class="mb-4">Design Tokens</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">4-Tier Glassmorphism System</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Purpose-built glass elevation hierarchy balancing ambient visual depth with ultra-crisp textual contrast.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-glass.card variant="subtle" class="p-6">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tier 1</div>
                        <h3 class="text-lg font-bold text-white mb-2">Subtle Glass</h3>
                        <p class="text-xs text-slate-400 mb-4">Lightweight background blur (8px) for high-density document editing surfaces and tabular data.</p>
                        <div class="text-[10px] font-mono text-slate-500">.glass-subtle</div>
                    </x-glass.card>

                    <x-glass.card variant="standard" class="p-6">
                        <div class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">Tier 2</div>
                        <h3 class="text-lg font-bold text-white mb-2">Standard Glass</h3>
                        <p class="text-xs text-slate-400 mb-4">Primary UI cards, sidebars, navigation bars, and widget containers with 16px blur & 12% opacity.</p>
                        <div class="text-[10px] font-mono text-indigo-300">.glass-standard</div>
                    </x-glass.card>

                    <x-glass.card variant="elevated" class="p-6">
                        <div class="text-xs font-semibold text-purple-400 uppercase tracking-wider mb-2">Tier 3</div>
                        <h3 class="text-lg font-bold text-white mb-2">Elevated Glass</h3>
                        <p class="text-xs text-slate-400 mb-4">Contextual bubble menus, tooltips, popovers, and elevated modal panels with deep drop shadows.</p>
                        <div class="text-[10px] font-mono text-purple-300">.glass-elevated</div>
                    </x-glass.card>

                    <x-glass.card variant="premium" glow="violet" class="p-6">
                        <div class="text-xs font-semibold text-cyan-400 uppercase tracking-wider mb-2">Tier 4</div>
                        <h3 class="text-lg font-bold text-white mb-2">Premium Glass</h3>
                        <p class="text-xs text-slate-300 mb-4">Active AI generation states, radiant glow borders, and high-impact conversion callouts.</p>
                        <div class="text-[10px] font-mono text-cyan-300">.glass-premium</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- FINAL CTA SECTION                                                         -->
        <!-- ========================================================================= -->
        <section class="py-20 sm:py-32 relative overflow-hidden text-center">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-glass.card variant="premium" glow="indigo" class="p-8 sm:p-16 relative overflow-hidden">
                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-6">
                        Experience the Future of AI Writing
                    </h2>
                    <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed">
                        Start crafting articles, technical documentation, and marketing copy with full engine flexibility and real-time AI assistance today.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @auth
                            <a href="{{ route('editor') }}">
                                <x-glass.button variant="primary" size="lg" class="px-8 shadow-xl shadow-indigo-600/40">
                                    ✍️ Open Document Editor
                                </x-glass.button>
                            </a>
                        @else
                            <a href="{{ route('register') }}">
                                <x-glass.button variant="primary" size="lg" class="px-8 shadow-xl shadow-indigo-600/40">
                                    Get Started Free &rarr;
                                </x-glass.button>
                            </a>
                        @endauth
                    </div>
                </x-glass.card>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-slate-950/80 py-12 text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white">HelpOfAi (HOA) Studio</span>
                <span>&bull;</span>
                <span>Copyright &copy; {{ date('Y') }} Rajib Adhikary. All Rights Reserved.</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="https://helpofai.com" target="_blank" class="hover:text-white transition-colors">HelpOfAi.com</a>
                <a href="#features" class="hover:text-white transition-colors">Architecture</a>
                <a href="#engines" class="hover:text-white transition-colors">Engines</a>
            </div>
        </div>
    </footer>
</x-layouts.app>

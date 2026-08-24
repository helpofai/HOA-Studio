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
    <header class="sticky top-0 z-50 w-full border-b border-white/5 bg-slate-950/75 backdrop-blur-2xl transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <x-glass.logo size="md" text="HOA" />
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-base font-bold text-white tracking-tight group-hover:text-indigo-300 transition-colors">HelpOfAi Studio</span>
                        <x-glass.badge variant="violet" class="hidden sm:inline-flex text-[10px] py-0.5 px-2">v2.0 Universal</x-glass.badge>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-none">Universal Multi-Editor Platform</p>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-300">
                <a href="#demo" class="hover:text-white transition-colors text-indigo-300 font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span> Live Multi-Editor Demo
                </a>
                <a href="#engines" class="hover:text-white transition-colors">8 Engines</a>
                <a href="#features" class="hover:text-white transition-colors">Architecture</a>
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
        <section class="relative pt-16 pb-16 sm:pt-24 sm:pb-24 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Stack Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-subtle text-xs text-indigo-300 mb-8 border border-indigo-500/20 shadow-inner">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-semibold text-white">Universal Multi-Editor Architecture</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-cyan-300">8 Engines · Canonical Data Model · OmniRoute AI Gateway</span>
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
                    Experience seamless authoring across Tiptap, Gutenberg blocks, Notion canvas, Markdown split-preview, HTML, and Plain Text—powered by an intelligent Three-Column AI Command Center, real-time SEO intelligence, and decoupled AI routing.
                </p>

                <!-- Dual Action CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto mb-16">
                    @auth
                        <a href="{{ route('editor') }}" class="w-full sm:w-auto">
                            <x-glass.button variant="primary" size="lg" class="w-full px-8 shadow-xl shadow-indigo-600/30">
                                ✍️ Open Document Editor &rarr;
                            </x-glass.button>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto">
                            <x-glass.button variant="primary" size="lg" class="w-full px-8 shadow-xl shadow-indigo-600/30">
                                Launch Free AI Workspace &rarr;
                            </x-glass.button>
                        </a>
                    @endauth
                    <x-glass.button variant="glass" size="lg" class="w-full sm:w-auto px-8" onclick="document.getElementById('demo').scrollIntoView({behavior: 'smooth'})">
                        ⚡ Try Live Editor Demo
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
        <!-- UPGRADED DEMO: ADVANCED MULTI-EDITOR THREE-COLUMN PLAYGROUND               -->
        <!-- ========================================================================= -->
        <section id="demo" class="py-16 sm:py-24 border-y border-white/10 relative bg-slate-950/60 scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-10">
                    <x-glass.badge variant="cyan" class="mb-3">Live Interactive Demonstration</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Advanced Multi-Editor Studio Playground
                    </h2>
                    <p class="text-slate-400 mt-2 text-sm sm:text-base">
                        Test live engine switching across all 8 supported writing surfaces. Click engines, run AI transformations, inspect real-time SEO intelligence, and explore the Three-Column layout.
                    </p>
                </div>

                <!-- Live Alpine.js Studio Simulation Component (Loaded safely via Alpine.data) -->
                <div 
                    x-data="multiEditorDemo"
                    class="glass-elevated rounded-3xl overflow-hidden border border-white/15 shadow-2xl space-y-0"
                >
                    <!-- Studio Top Navigation & Engine Selector Bar -->
                    <div class="px-6 py-4 bg-slate-900/95 border-b border-white/10 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                            </div>
                            <span class="text-xs font-mono text-slate-300 font-semibold ml-2">HOA-Studio &bull; Live Editor Canvas</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono border border-emerald-500/30">● Connected</span>
                        </div>

                        <!-- 8-Engine Selector Switcher Tabs -->
                        <div class="flex flex-wrap items-center gap-1 bg-slate-950 p-1 rounded-2xl border border-white/10 text-xs font-mono">
                            <span class="text-[10px] text-indigo-400 font-bold px-2 flex items-center gap-1">
                                <span>⚡ Active Engine:</span>
                            </span>
                            <button type="button" x-on:click="activeEngine = 'tiptap'" :class="activeEngine === 'tiptap' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">Tiptap</button>
                            <button type="button" x-on:click="activeEngine = 'gutenberg'" :class="activeEngine === 'gutenberg' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">Gutenberg</button>
                            <button type="button" x-on:click="activeEngine = 'notion'" :class="activeEngine === 'notion' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">Notion</button>
                            <button type="button" x-on:click="activeEngine = 'markdown_split'" :class="activeEngine === 'markdown_split' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">MD Split</button>
                            <button type="button" x-on:click="activeEngine = 'markdown_raw'" :class="activeEngine === 'markdown_raw' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">Raw MD</button>
                            <button type="button" x-on:click="activeEngine = 'html'" :class="activeEngine === 'html' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">HTML</button>
                            <button type="button" x-on:click="activeEngine = 'plaintext'" :class="activeEngine === 'plaintext' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all">Plain Text</button>
                        </div>
                    </div>

                    <!-- 3-Column Studio Workspace Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[560px]">
                        <!-- ─── COLUMN 1: AI COMMAND CENTER (3 cols) ────────────────── -->
                        <div class="lg:col-span-3 border-r border-white/10 p-5 bg-slate-950/75 flex flex-col justify-between text-xs space-y-4">
                            <div class="space-y-3.5">
                                <div class="flex items-center justify-between pb-2 border-b border-white/10">
                                    <span class="font-bold text-white uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                        AI Command Center
                                    </span>
                                    <span class="text-[9px] font-mono text-indigo-400 font-bold px-1.5 py-0.5 rounded bg-indigo-600/20 border border-indigo-500/30">OmniRoute</span>
                                </div>

                                <!-- Ask AI Box -->
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-300 block">✦ Ask AI / Custom Prompt</label>
                                    <textarea 
                                        x-model="aiPromptText" 
                                        rows="3" 
                                        class="w-full bg-slate-900 border border-white/15 rounded-xl p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none font-sans leading-relaxed shadow-inner"
                                    ></textarea>
                                    <button 
                                        type="button" 
                                        x-on:click="runDemoAi('generate')" 
                                        :disabled="isStreaming" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50"
                                    >
                                        <span x-show="!isStreaming">✦ Run AI Generation</span>
                                        <span x-show="isStreaming" class="animate-spin text-sm">⟳</span>
                                        <span x-show="isStreaming">Generating Tokens...</span>
                                    </button>
                                </div>

                                <!-- Quick Actions -->
                                <div class="space-y-1.5 pt-2 border-t border-white/5">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Quick Actions</span>
                                    <div class="grid grid-cols-2 gap-1.5 text-[11px] font-medium">
                                        <button type="button" x-on:click="runDemoAi('generate')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left flex items-center gap-1 transition-colors">
                                            <span class="text-indigo-400">✦</span> Generate
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('rewrite')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left flex items-center gap-1 transition-colors">
                                            <span class="text-cyan-400">↻</span> Rewrite
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('seo')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left flex items-center gap-1 transition-colors">
                                            <span class="text-emerald-400">⌁</span> SEO Boost
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('generate')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 border border-white/5 text-left flex items-center gap-1 transition-colors">
                                            <span class="text-violet-400">+</span> Expand
                                        </button>
                                    </div>
                                </div>

                                <!-- Model Selector -->
                                <div class="space-y-1.5 pt-2 border-t border-white/5">
                                    <label class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">AI Model Routing</label>
                                    <select x-model="selectedAiModel" class="w-full bg-slate-900 border border-white/10 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                                        <option>Claude 3.7 Sonnet (OmniRoute)</option>
                                        <option>OpenAI GPT-4o</option>
                                        <option>Gemini 2.0 Flash</option>
                                        <option>DeepSeek-V3</option>
                                        <option>Local Ollama (vLLM)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Live AI Streaming Stream Output Card -->
                            <div class="p-3 rounded-2xl bg-indigo-950/30 border border-indigo-500/20 text-[11px] text-slate-300 space-y-1">
                                <div class="flex items-center justify-between text-indigo-400 font-bold text-[10px] font-mono">
                                    <span>AI STREAM OUTPUT</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400" :class="isStreaming ? 'animate-ping' : ''"></span>
                                </div>
                                <p class="text-slate-300 leading-relaxed font-sans" x-text="streamingToken || 'Ready. Click any Quick Action or Generate to test live token streaming.'"></p>
                            </div>
                        </div>

                        <!-- ─── COLUMN 2: CONTENT WORKSPACE (CENTER / 6 cols) ────────── -->
                        <div class="lg:col-span-6 p-6 flex flex-col justify-between bg-slate-900/30 border-r border-white/10">
                            <div class="space-y-4">
                                <!-- Dynamic Capability-Aware Ribbon -->
                                <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-slate-900/90 rounded-2xl border border-white/10 text-xs shadow-md">
                                    <div class="flex items-center gap-1 font-bold text-slate-300">
                                        <span class="px-2 py-0.5 rounded-lg bg-indigo-600/25 text-indigo-300 font-mono text-[10px] border border-indigo-500/30" x-text="activeEngine.toUpperCase()"></span>
                                        <span class="w-[1px] h-3.5 bg-white/10 mx-1"></span>
                                        <button type="button" class="px-2 py-1 rounded hover:bg-white/10">H1</button>
                                        <button type="button" class="px-2 py-1 rounded hover:bg-white/10">H2</button>
                                        <button type="button" class="px-2 py-1 rounded hover:bg-white/10">H3</button>
                                        <span class="w-[1px] h-3.5 bg-white/10 mx-1"></span>
                                        <button type="button" class="px-2 py-1 rounded hover:bg-white/10">B</button>
                                        <button type="button" class="px-2 py-1 rounded hover:bg-white/10 italic">I</button>
                                        <button type="button" class="px-2 py-1 rounded hover:bg-white/10 font-mono">&lt;/&gt;</button>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 pr-1">
                                        <span class="text-white font-bold" x-text="wordCount"></span> words &bull; 
                                        <span class="text-indigo-300">3m read</span>
                                    </div>
                                </div>

                                <!-- ENGINE 1: TIPTAP PROSEMIRROR -->
                                <div x-show="activeEngine === 'tiptap'" class="p-6 rounded-2xl bg-slate-950/70 border border-white/5 min-h-[350px] space-y-3">
                                    <h2 class="text-2xl font-extrabold text-white">Next-Gen Decoupled AI Gateways in 2026</h2>
                                    <p class="text-sm text-slate-300 leading-relaxed">
                                        Modern content production platforms demand high-throughput intelligence routing with zero vendor lock-in. By abstracting the AI model gateway through an intelligent proxy, SaaS platforms eliminate dependencies while preserving sub-second SSE streaming.
                                    </p>
                                    <blockquote class="border-l-4 border-indigo-500 pl-4 py-1.5 text-indigo-200/90 italic font-serif text-sm bg-indigo-950/20 rounded-r-xl">
                                        Production rule: The canonical document must not belong to a single editor engine.
                                    </blockquote>
                                    <p class="text-sm text-slate-300 leading-relaxed">
                                        Universal adapters translate between ProseMirror node trees, Gutenberg block schemas, and Markdown without destructive data stripping.
                                    </p>
                                </div>

                                <!-- ENGINE 2: GUTENBERG MODULAR BLOCKS -->
                                <div x-show="activeEngine === 'gutenberg'" class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 min-h-[350px] space-y-3" style="display: none;">
                                    <div class="flex items-center justify-between pb-2 border-b border-white/10 text-xs">
                                        <span class="font-mono text-purple-300 font-bold">❖ Gutenberg Block Canvas</span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" x-on:click="addGtBlock('paragraph')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold">+ Paragraph</button>
                                            <button type="button" x-on:click="addGtBlock('heading')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold">+ Heading</button>
                                            <button type="button" x-on:click="addGtBlock('quote')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold">+ Quote</button>
                                        </div>
                                    </div>
                                    <template x-for="(blk, idx) in gtBlocks" :key="blk.id">
                                        <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 space-y-1 relative group hover:border-purple-500/40 transition-colors">
                                            <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                                                <span class="uppercase font-bold text-purple-400" x-text="'❖ ' + blk.type"></span>
                                                <button type="button" x-on:click="removeGtBlock(idx)" class="text-slate-500 hover:text-red-400">✕ Delete</button>
                                            </div>
                                            <div contenteditable="true" class="text-sm text-slate-200 focus:outline-none" x-text="blk.content"></div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ENGINE 3: NOTION-STYLE BLOCK CANVAS -->
                                <div x-show="activeEngine === 'notion'" class="p-5 rounded-2xl bg-slate-950/70 border border-white/5 min-h-[350px] space-y-2.5" style="display: none;">
                                    <div class="flex items-center justify-between pb-2 border-b border-white/10 text-xs text-slate-400 font-mono">
                                        <span class="text-violet-300 font-bold">⠿ Notion Block Canvas</span>
                                        <span>Type <kbd class="px-1 py-0.5 rounded bg-slate-800 text-slate-300">/</kbd> for quick menu</span>
                                    </div>
                                    <template x-for="n in notionBlocks" :key="n.id">
                                        <div class="flex items-start gap-2 p-2 rounded-xl hover:bg-slate-900/50 transition-colors group">
                                            <span class="text-slate-500 cursor-grab text-xs pt-0.5 opacity-40 group-hover:opacity-100 select-none">⠿</span>
                                            <div class="flex-1">
                                                <template x-if="n.type === 'heading'">
                                                    <h3 class="text-lg font-bold text-white" contenteditable="true" x-text="n.text"></h3>
                                                </template>
                                                <template x-if="n.type === 'callout'">
                                                    <div class="p-3 rounded-xl bg-violet-950/30 border border-violet-500/30 text-xs text-slate-200" contenteditable="true" x-text="n.text"></div>
                                                </template>
                                                <template x-if="n.type === 'text'">
                                                    <p class="text-sm text-slate-300" contenteditable="true" x-text="n.text"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ENGINE 4: MARKDOWN SPLIT PREVIEW -->
                                <div x-show="activeEngine === 'markdown_split'" class="grid grid-cols-2 gap-3 h-[350px] border border-white/10 rounded-2xl overflow-hidden bg-slate-950/80" style="display: none;">
                                    <div class="flex flex-col border-r border-white/10 p-4 font-mono text-xs text-indigo-300 space-y-2">
                                        <span class="text-slate-500 font-bold uppercase text-[10px]">📝 Markdown Input</span>
                                        <textarea class="w-full flex-1 bg-transparent text-slate-200 focus:outline-none resize-none leading-relaxed font-mono">## Universal Multi-Editor Platform

- **Tiptap ProseMirror**
- **Gutenberg Blocks**
- **Notion Canvas**

> Powered by HelpOfAi Studio in 2026.</textarea>
                                    </div>
                                    <div class="flex flex-col p-4 text-xs text-slate-200 space-y-2 bg-slate-900/40">
                                        <span class="text-slate-500 font-mono font-bold uppercase text-[10px]">👁 Live HTML Preview</span>
                                        <div class="prose prose-invert prose-sm">
                                            <h3 class="text-base font-bold text-white">Universal Multi-Editor Platform</h3>
                                            <ul class="list-disc pl-4 space-y-1 text-slate-300">
                                                <li><strong>Tiptap ProseMirror</strong></li>
                                                <li><strong>Gutenberg Blocks</strong></li>
                                                <li><strong>Notion Canvas</strong></li>
                                            </ul>
                                            <blockquote class="text-indigo-300 italic">Powered by HelpOfAi Studio in 2026.</blockquote>
                                        </div>
                                    </div>
                                </div>

                                <!-- ENGINE 5: RAW MARKDOWN -->
                                <div x-show="activeEngine === 'markdown_raw'" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 min-h-[350px] font-mono text-xs text-cyan-300 leading-relaxed" style="display: none;">
                                    <span class="text-slate-500 block mb-2 font-bold uppercase text-[10px]">⌨️ Distraction-Free Raw Monospace</span>
                                    <p># Universal Multi-Editor Platform Specification</p>
                                    <p class="text-slate-400">## Principles of Canonical Transformation</p>
                                    <p class="text-slate-300 mt-2">1. All mutations synchronize against the central document canonical model.<br/>2. Adapters declare lossy fidelity boundaries with user consent modals.<br/>3. Decoupled AI gateways execute semantic operations without editor coupling.</p>
                                </div>

                                <!-- ENGINE 6: HTML SOURCE -->
                                <div x-show="activeEngine === 'html'" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 min-h-[350px] font-mono text-xs text-amber-300 leading-relaxed" style="display: none;">
                                    <span class="text-slate-500 block mb-2 font-bold uppercase text-[10px]">💻 Raw HTML5 Code Editor</span>
                                    <p>&lt;<span class="text-red-400">article</span> <span class="text-amber-400">class</span>=<span class="text-emerald-400">"hoa-document"</span>&gt;</p>
                                    <p class="pl-4">&lt;<span class="text-red-400">h2</span>&gt;Universal Multi-Editor Platform&lt;/<span class="text-red-400">h2</span>&gt;</p>
                                    <p class="pl-4">&lt;<span class="text-red-400">p</span>&gt;Enterprise document authoring with real-time SSE streaming.&lt;/<span class="text-red-400">p</span>&gt;</p>
                                    <p>&lt;/<span class="text-red-400">article</span>&gt;</p>
                                </div>

                                <!-- ENGINE 7: PLAIN TEXT -->
                                <div x-show="activeEngine === 'plaintext'" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 min-h-[350px] font-mono text-xs text-slate-300 leading-relaxed" style="display: none;">
                                    <span class="text-slate-500 block mb-2 font-bold uppercase text-[10px]">📄 Minimalist Plain Text Canvas</span>
                                    Next-Gen Decoupled AI Gateways in 2026

Modern content production platforms demand high-throughput intelligence routing with zero vendor lock-in. Clean, distraction-free environment for pure text authoring.
                                </div>
                            </div>

                            <!-- Bottom Status Bar -->
                            <div class="pt-4 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-[11px] font-mono text-slate-400">
                                <div>
                                    <span>Engine: <strong class="text-white" x-text="activeEngine.toUpperCase()"></strong></span>
                                    <span class="mx-2">&bull;</span>
                                    <span>Model: <strong class="text-indigo-300">Claude 3.7</strong></span>
                                </div>
                                <span class="text-emerald-400">● Autosaved at 01:25 AM</span>
                            </div>
                        </div>

                        <!-- ─── COLUMN 3: CONTENT INTELLIGENCE (RIGHT / 3 cols) ─────── -->
                        <div class="lg:col-span-3 p-5 bg-slate-950/75 flex flex-col justify-between text-xs space-y-4">
                            <div class="space-y-3.5">
                                <div class="flex items-center justify-between pb-2 border-b border-white/10">
                                    <span class="font-bold text-white uppercase tracking-wider text-[10px]">Content Intelligence</span>
                                    <span class="text-[10px] text-emerald-400 font-mono font-bold" x-text="seoScore + '/100'"></span>
                                </div>

                                <!-- Intelligence Tab Selector -->
                                <div class="flex items-center gap-1 bg-slate-900 p-1 rounded-xl border border-white/10 text-[10px] font-mono">
                                    <button type="button" x-on:click="activeIntelTab = 'seo'" :class="activeIntelTab === 'seo' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg transition-colors">SEO</button>
                                    <button type="button" x-on:click="activeIntelTab = 'keys'" :class="activeIntelTab === 'keys' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg transition-colors">Keys</button>
                                    <button type="button" x-on:click="activeIntelTab = 'recs'" :class="activeIntelTab === 'recs' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg transition-colors">Recs</button>
                                    <button type="button" x-on:click="activeIntelTab = 'outline'" :class="activeIntelTab === 'outline' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400'" class="flex-1 py-1 rounded-lg transition-colors">Outline</button>
                                </div>

                                <!-- Tab 1: SEO Gauges -->
                                <div x-show="activeIntelTab === 'seo'" class="space-y-3">
                                    <div class="grid grid-cols-2 gap-2 text-center">
                                        <div class="p-3 rounded-xl border border-emerald-500/40 bg-emerald-950/30">
                                            <span class="text-2xl font-black font-mono text-emerald-400" x-text="seoScore">94</span>
                                            <span class="text-[9px] uppercase block font-bold text-slate-300">SEO Score</span>
                                        </div>
                                        <div class="p-3 rounded-xl border border-cyan-500/40 bg-cyan-950/30">
                                            <span class="text-2xl font-black font-mono text-cyan-400" x-text="readScore">82</span>
                                            <span class="text-[9px] uppercase block font-bold text-slate-300">Reading Ease</span>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 space-y-1.5 font-mono text-[10.5px]">
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword in Title:</span><span class="text-emerald-400 font-bold">✓ Yes</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword in Intro:</span><span class="text-emerald-400 font-bold">✓ Yes</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Subheading Coverage:</span><span class="text-emerald-400 font-bold">✓ 3 H2s</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword Density:</span><span class="text-emerald-400 font-bold">2.4% (Optimal)</span></div>
                                    </div>
                                </div>

                                <!-- Tab 2: Secondary Keywords -->
                                <div x-show="activeIntelTab === 'keys'" class="space-y-2" style="display: none;">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Targeted LSI Keywords</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="px-2 py-1 rounded-lg bg-indigo-950/60 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">multi-editor architecture ✓</span>
                                        <span class="px-2 py-1 rounded-lg bg-indigo-950/60 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">decoupled AI gateway ✓</span>
                                        <span class="px-2 py-1 rounded-lg bg-indigo-950/60 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">prosemirror tiptap ✓</span>
                                        <span class="px-2 py-1 rounded-lg bg-indigo-950/60 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">canonical document model ✓</span>
                                    </div>
                                </div>

                                <!-- Tab 3: Recommendations Checklist -->
                                <div x-show="activeIntelTab === 'recs'" class="space-y-2" style="display: none;">
                                    <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-start gap-2">
                                        <span>✓</span> <span>Optimal H2 heading hierarchy with strong scannability.</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-start gap-2">
                                        <span>✓</span> <span>Flesch reading score 82/100 (Clear & engaging for readers).</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-yellow-950/20 border border-yellow-500/30 text-yellow-300 text-[11px] flex items-start gap-2">
                                        <span>⚠</span> <span>Add FAQ schema section to target Google AI overviews.</span>
                                    </div>
                                </div>

                                <!-- Tab 4: Outline Hierarchy Tree -->
                                <div x-show="activeIntelTab === 'outline'" class="space-y-1.5 font-mono text-[11px]" style="display: none;">
                                    <div class="p-2 rounded-lg bg-indigo-600/20 text-indigo-300 font-bold border border-indigo-500/30">H2 Next-Gen Decoupled AI Gateways</div>
                                    <div class="p-1.5 rounded-lg text-slate-300 pl-4 border-l border-white/10">H3 Multi-Provider Failover</div>
                                    <div class="p-1.5 rounded-lg text-slate-300 pl-4 border-l border-white/10">H3 Canonical Storage Protocols</div>
                                </div>
                            </div>

                            <!-- Launch Full Studio Action -->
                            <a href="{{ route('editor') }}">
                                <x-glass.button variant="primary" size="sm" class="w-full shadow-lg shadow-indigo-600/30">
                                    ✍️ Open Full Live Studio &rarr;
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
                    <x-glass.badge variant="violet" class="mb-4">Universal Engine Platform</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">8 Dedicated Editor Engines</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Every document is preserved in a semantic canonical model with dedicated adapters for lossless translation across any writing surface.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Engine 1: Tiptap -->
                    <x-glass.card variant="standard" class="hover:border-indigo-500/50 transition-all p-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 font-bold text-lg">✨</div>
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
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 font-bold text-lg">❖</div>
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
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400 mb-4 font-bold text-lg">⠿</div>
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
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-4 font-bold text-lg">📝</div>
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
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 mb-4 font-bold text-lg">⌨️</div>
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
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4 font-bold text-lg">💻</div>
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
                        <div class="w-10 h-10 rounded-xl bg-slate-500/10 border border-slate-500/20 flex items-center justify-center text-slate-400 mb-4 font-bold text-lg">📄</div>
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
                        <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 mb-4 font-bold text-lg">🔮</div>
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
        <!-- FINAL CALL TO ACTION                                                      -->
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
                                    ✍️ Open Document Editor &rarr;
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

    <!-- Alpine.js Component Definition Script for Clean, Quote-Safe Initialization -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('multiEditorDemo', () => ({
                activeEngine: 'tiptap',
                activeIntelTab: 'seo',
                isStreaming: false,
                aiPromptText: 'Write a compelling deep-dive on decoupled AI multi-editor architecture in 2026',
                selectedAiModel: 'Claude 3.7 Sonnet (OmniRoute)',
                streamingToken: '',
                wordCount: 428,
                seoScore: 94,
                readScore: 82,

                gtBlocks: [
                    { id: 1, type: 'heading', level: 2, content: 'Architectural Blueprint: Universal Document Model' },
                    { id: 2, type: 'paragraph', content: 'In 2026, content platforms decouple the storage format from the editor UI. A single canonical representation converts seamlessly between ProseMirror JSON, Gutenberg blocks, and Markdown.' },
                    { id: 3, type: 'quote', content: 'The canonical document must never belong to a single editor engine. — Architecture Rule #1' }
                ],
                addGtBlock(type) {
                    this.gtBlocks.push({
                        id: Date.now(),
                        type: type,
                        level: type === 'heading' ? 2 : undefined,
                        content: type === 'heading' ? 'New Section Heading' : (type === 'quote' ? 'Blockquote text...' : 'New paragraph content...')
                    });
                    this.wordCount += 12;
                },
                removeGtBlock(idx) {
                    this.gtBlocks.splice(idx, 1);
                },

                notionBlocks: [
                    { id: 1, type: 'heading', text: 'Universal Content Workspace' },
                    { id: 2, type: 'callout', text: '💡 Notion-style drag handle ⠿ and slash command / trigger quick structural mutations.' },
                    { id: 3, type: 'text', text: 'Click anywhere to edit, drag to rearrange, or use / for fast formatting blocks.' }
                ],

                runDemoAi(type) {
                    this.isStreaming = true;
                    this.streamingToken = 'Routing prompt to ' + this.selectedAiModel + '...';
                    
                    setTimeout(() => {
                        if (type === 'generate') {
                            this.streamingToken = '✓ Generated: Multi-tier streaming proxy provides token quota pre-flight checks and real-time SSE multiplexing across distributed AI clusters.';
                            this.wordCount += 24;
                            this.seoScore = 98;
                        } else if (type === 'rewrite') {
                            this.streamingToken = '✓ Polished: Decoupled AI routing ensures resilient failover and zero downtime for mission-critical enterprise publishing teams.';
                        } else if (type === 'seo') {
                            this.streamingToken = '✓ SEO Optimization Complete: Added LSI keywords, balanced H2 density, and generated SERP meta description.';
                            this.seoScore = 99;
                        }
                        this.isStreaming = false;
                    }, 700);
                }
            }));
        });
    </script>
</x-layouts.app>

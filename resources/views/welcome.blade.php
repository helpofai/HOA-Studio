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

    <!-- Navigation Header with Smooth Auto Hide/Show on Scroll -->
    <x-public-header />

    <!-- Main Landing Content -->
    <main class="flex-1 pt-16">
        <!-- ========================================================================= -->
        <!-- 1. HERO SECTION (Clear, Simple, SEO-Optimized)                           -->
        <!-- ========================================================================= -->
        <section class="relative pt-16 pb-16 sm:pt-24 sm:pb-24 overflow-hidden hoa-welcome-hero-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Dynamic Version Pill -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-subtle text-xs text-indigo-300 mb-8 border border-indigo-500/20 shadow-inner">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-semibold text-white">Next-Gen AI Content Studio</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-cyan-300">v{{ app(\App\Features\Admin\Services\CoreUpdateService::class)->getCurrentVersion() }} &bull; 8 Universal Writing Engines &bull; OmniRoute AI Gateway</span>
                </div>

                <!-- Main High-Impact Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.1] max-w-5xl mx-auto mb-8">
                    Write, Edit & Optimize <br class="hidden sm:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">
                        High-Ranking Content 10x Faster.
                    </span>
                </h1>

                <!-- Simple, Crystal-Clear Subtitle -->
                <p class="text-base sm:text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed font-normal">
                    The only AI workspace that lets you switch between <strong>Tiptap, WordPress Gutenberg blocks, Notion canvas, and Markdown</strong> with zero formatting loss. Backed by real-time SEO intelligence and smart AI model routing.
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
                        <a href="{{ route('register') }}" class="w-full sm:w-auto relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl blur opacity-40 group-hover:opacity-100 transition duration-500 animation-pulse"></div>
                            <x-glass.button variant="primary" size="lg" shimmer="true" class="relative w-full px-8 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-xl shadow-indigo-600/30 text-white font-bold !border-0 text-base lg:text-lg">
                                Start Writing Free &rarr;
                            </x-glass.button>
                        </a>
                    @endauth
                    <x-glass.button variant="glass" size="lg" class="w-full sm:w-auto px-8 cursor-pointer" onclick="document.getElementById('demo').scrollIntoView({behavior: 'smooth'})">
                        ⚡ Try Interactive Demo
                    </x-glass.button>
                </div>

                <!-- Trust & Metric Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                    <x-glass.card variant="subtle" class="text-center p-4 hover:border-indigo-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-2xl sm:text-3xl font-black text-indigo-400">8 Engines</div>
                        <div class="text-xs text-slate-400 mt-1">Zero Format Loss</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4 hover:border-cyan-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-2xl sm:text-3xl font-black text-cyan-400">3-Column</div>
                        <div class="text-xs text-slate-400 mt-1">AI Command Center</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4 hover:border-purple-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-2xl sm:text-3xl font-black text-purple-400">Real-Time</div>
                        <div class="text-xs text-slate-400 mt-1">Live SEO Score 0-100</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4 hover:border-emerald-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-2xl sm:text-3xl font-black text-emerald-400">OmniRoute</div>
                        <div class="text-xs text-slate-400 mt-1">Claude, GPT-4o & Gemini</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 2. INTERACTIVE DEMO: 3-COLUMN AI STUDIO PLAYGROUND                       -->
        <!-- ========================================================================= -->
        <section id="demo" class="py-16 sm:py-24 border-y border-white/10 relative bg-slate-950/60 scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-10">
                    <x-glass.badge variant="cyan" class="mb-3">Live Interactive Studio</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Experience the 3-Column AI Writing Canvas
                    </h2>
                    <p class="text-slate-300 mt-3 text-sm sm:text-base leading-relaxed">
                        Try switching engines in real-time below. Run AI prompts with OmniRoute, check live SEO optimization scores, and format content with zero slowdown.
                    </p>
                </div>

                <!-- Live Alpine.js Studio Simulation Component (Loaded safely via Alpine.data) -->
                <div 
                    x-data="multiEditorDemo"
                    class="glass-elevated rounded-3xl overflow-hidden border border-white/15 shadow-2xl space-y-0 relative hoa-editor-shadow"
                >
                    <!-- ========================================================================= -->
                    <!-- REAL EDITOR TOP TOOLBAR & CONTROLS                                        -->
                    <!-- ========================================================================= -->
                    <div class="px-4 sm:px-6 py-3.5 bg-slate-900/95 border-b border-white/10 flex flex-wrap items-center justify-between gap-3 select-none">
                        <!-- Left Section: Navigation & Document Title -->
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <a href="{{ route('editor') }}" class="text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-white/5 border border-white/5 transition-all flex items-center gap-1 text-xs font-semibold shrink-0" title="Launch Real Editor">
                                <span class="text-indigo-400">&larr;</span>
                                <span class="hidden xs:inline">Demo</span>
                            </a>
                            <div class="h-4 w-[1px] bg-white/10 shrink-0 hidden sm:block"></div>
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="text-[11px] text-indigo-400/90 font-mono font-semibold hidden md:inline shrink-0">
                                    Articles /
                                </span>
                                <input 
                                    type="text" 
                                    x-model="documentTitle" 
                                    placeholder="Document Title..." 
                                    class="text-xs sm:text-sm font-extrabold text-white bg-transparent border-b border-transparent hover:border-white/20 focus:border-indigo-500 focus:outline-none px-1 py-0.5 transition-all w-full max-w-xs sm:max-w-sm truncate"
                                />
                            </div>
                        </div>

                        <!-- Right Section: Engine Switcher Dropdown, Autosave & Panel Toggles -->
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <!-- Engine Selector Pills / Dropdown -->
                            <div class="flex items-center bg-slate-950 p-1 rounded-2xl border border-white/10 text-xs font-mono">
                                <span class="text-[10px] text-indigo-400 font-bold px-2 hidden sm:inline">✦ Engine:</span>
                                <button type="button" x-on:click="activeEngine = 'tiptap'" :class="activeEngine === 'tiptap' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer">Tiptap</button>
                                <button type="button" x-on:click="activeEngine = 'gutenberg'" :class="activeEngine === 'gutenberg' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer">Gutenberg</button>
                                <button type="button" x-on:click="activeEngine = 'notion'" :class="activeEngine === 'notion' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer">Notion</button>
                                <button type="button" x-on:click="activeEngine = 'markdown_split'" :class="activeEngine === 'markdown_split' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer hidden md:inline-block">MD Split</button>
                                <button type="button" x-on:click="activeEngine = 'markdown_raw'" :class="activeEngine === 'markdown_raw' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer hidden lg:inline-block">Raw MD</button>
                                <button type="button" x-on:click="activeEngine = 'html'" :class="activeEngine === 'html' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer hidden lg:inline-block">HTML</button>
                                <button type="button" x-on:click="activeEngine = 'plaintext'" :class="activeEngine === 'plaintext' ? 'bg-indigo-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-xl transition-all cursor-pointer hidden xl:inline-block">Plain Text</button>
                            </div>

                            <!-- Live Autosave Status Indicator -->
                            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-950/80 border border-white/10 text-[10.5px] text-slate-300 font-mono">
                                <span class="w-2 h-2 rounded-full bg-emerald-400" :class="isStreaming ? 'animate-ping' : ''"></span>
                                <span class="hidden md:inline">Saved to Cloud</span>
                                <span class="text-[9px] text-slate-400">(12ms)</span>
                            </div>

                            <!-- Panel Toggle Buttons (AI, Zen Focus, Intel) -->
                            <div class="flex items-center rounded-xl bg-slate-950 p-0.5 border border-white/10">
                                <button 
                                    type="button" 
                                    x-on:click="toggleLeftPanel()" 
                                    :class="showLeftPanel ? 'bg-indigo-600/40 text-indigo-300 font-bold border border-indigo-500/40' : 'text-slate-400 hover:text-white border border-transparent'"
                                    class="px-2 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer" 
                                    title="Toggle AI Command Center"
                                >
                                    ◧ AI
                                </button>
                                <button 
                                    type="button" 
                                    x-on:click="toggleFocusMode()" 
                                    :class="(!showLeftPanel && !showRightPanel) ? 'bg-purple-600/40 text-purple-300 font-bold border border-purple-500/40' : 'text-slate-400 hover:text-white border border-transparent'"
                                    class="px-2 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer" 
                                    title="Zen Focus Mode"
                                >
                                    Zen
                                </button>
                                <button 
                                    type="button" 
                                    x-on:click="toggleRightPanel()" 
                                    :class="showRightPanel ? 'bg-emerald-600/40 text-emerald-300 font-bold border border-emerald-500/40' : 'text-slate-400 hover:text-white border border-transparent'"
                                    class="px-2 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer" 
                                    title="Toggle Content Intelligence"
                                >
                                    ◨ Intel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- 3-COLUMN STUDIO WORKSPACE GRID                                            -->
                    <!-- ========================================================================= -->
                    <div 
                        class="grid grid-cols-1 gap-0 items-start min-h-[580px]"
                        :class="{
                            'lg:grid-cols-[290px_1fr_320px]': showLeftPanel && showRightPanel,
                            'lg:grid-cols-[300px_1fr]': showLeftPanel && !showRightPanel,
                            'lg:grid-cols-[1fr_330px]': !showLeftPanel && showRightPanel,
                            'lg:grid-cols-1 max-w-5xl mx-auto w-full': !showLeftPanel && !showRightPanel
                        }"
                    >
                        <!-- ─── COLUMN 1: AI COMMAND CENTER (OMNIRoute Gateway) ──────── -->
                        <div 
                            x-show="showLeftPanel" 
                            x-transition 
                            class="border-r border-white/10 p-4 sm:p-5 bg-slate-950/85 flex flex-col justify-between text-xs space-y-4 h-full"
                        >
                            <div class="space-y-3.5">
                                <!-- AI Command Center Header -->
                                <div class="flex items-center justify-between pb-2 border-b border-white/10">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 animate-pulse"></span>
                                        <h3 class="text-xs uppercase font-extrabold text-white tracking-wider">AI Command Center</h3>
                                    </div>
                                    <span class="text-[9.5px] font-mono text-indigo-400 font-bold px-2 py-0.5 rounded-full bg-indigo-600/20 border border-indigo-500/30">Live Canvas</span>
                                </div>

                                <!-- AI Gateway Configuration Box -->
                                <div class="space-y-2.5 p-3 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-white">
                                        <span class="flex items-center gap-1.5">
                                            <span class="text-indigo-400">⚡</span>
                                            <span>AI Gateway Configuration</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            ONLINE
                                        </span>
                                    </div>

                                    <!-- Provider Selector -->
                                    <div class="space-y-1">
                                        <label class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">1. AI Provider</label>
                                        <select x-model="selectedProvider" class="w-full bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer">
                                            <option value="omniroute">OmniRoute Gateway (Auto Failover)</option>
                                            <option value="anthropic">Anthropic (Claude)</option>
                                            <option value="openai">OpenAI (GPT-4o)</option>
                                            <option value="google">Google Gemini 2.0</option>
                                            <option value="deepseek">DeepSeek AI</option>
                                            <option value="ollama">Local Ollama / vLLM</option>
                                        </select>
                                    </div>

                                    <!-- Model Selector -->
                                    <div class="space-y-1">
                                        <label class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">2. Provider Models</label>
                                        <select x-model="selectedAiModel" class="w-full bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer">
                                            <option>Claude 3.7 Sonnet (OmniRoute)</option>
                                            <option>OpenAI GPT-4o</option>
                                            <option>Gemini 2.0 Flash</option>
                                            <option>DeepSeek-V3</option>
                                            <option>DeepSeek-R1 (Reasoning)</option>
                                            <option>Llama 3.3 70B (Local Ollama)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- User Brain & Hybrid RAG Vector Memory -->
                                <div class="p-2.5 rounded-2xl bg-purple-950/20 border border-purple-500/25 shadow-inner space-y-1 font-mono text-[10.5px]">
                                    <div class="flex items-center justify-between font-bold text-white">
                                        <span class="flex items-center gap-1.5 text-purple-300">
                                            <span>🧠</span> <span>User Brain & Vector Memory</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[8.5px] bg-purple-900 text-purple-200 border border-purple-400/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span>
                                            HYBRID RAG
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[9.5px] text-slate-400">
                                        <span>Multi-tier Vector Cache</span>
                                        <span class="text-indigo-400">Active</span>
                                    </div>
                                </div>

                                <!-- Ask AI Prompt Box -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">✦ Ask AI / Custom Prompt</label>
                                        <span class="text-[9px] font-mono text-indigo-400">Cmd+K</span>
                                    </div>
                                    <textarea 
                                        x-model="aiPromptText" 
                                        rows="2" 
                                        class="w-full bg-slate-900 border border-white/15 rounded-xl p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none font-sans leading-relaxed shadow-inner"
                                    ></textarea>
                                    <button 
                                        type="button" 
                                        x-on:click="runDemoAi('generate')" 
                                        :disabled="isStreaming" 
                                        :class="isStreaming ? 'btn-shimmer' : ''"
                                        class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-75"
                                    >
                                        <span x-show="!isStreaming" class="flex items-center gap-1.5">
                                            <span>✦</span> <span>Run AI Generation</span>
                                        </span>
                                        <span x-show="isStreaming" class="flex items-center gap-2">
                                            <span class="bars"><span></span><span></span><span></span><span></span></span>
                                            <span x-text="'Streaming (' + receivedTokens + ' tok)...'"></span>
                                        </span>
                                    </button>
                                </div>

                                <!-- 15-Stage Enterprise Production Actions -->
                                <div class="space-y-1.5 pt-2 border-t border-white/10">
                                    <span class="text-[9px] uppercase font-bold text-slate-500 tracking-wider">Direct Semantic Pipelines</span>
                                    <div class="grid grid-cols-2 gap-1.5 text-[11px] font-medium">
                                        <button type="button" x-on:click="runDemoAi('generate')" class="p-2 rounded-xl bg-slate-900/70 hover:bg-white/10 text-slate-200 border border-white/10 text-left flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <span class="text-indigo-400">✦</span> Generate
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('rewrite')" class="p-2 rounded-xl bg-slate-900/70 hover:bg-white/10 text-slate-200 border border-white/10 text-left flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <span class="text-cyan-400">↻</span> Rewrite
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('seo')" class="p-2 rounded-xl bg-slate-900/70 hover:bg-white/10 text-slate-200 border border-white/10 text-left flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <span class="text-emerald-400">⌁</span> SEO Boost
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('generate')" class="p-2 rounded-xl bg-slate-900/70 hover:bg-white/10 text-slate-200 border border-white/10 text-left flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <span class="text-violet-400">+</span> Expand
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('rewrite')" class="p-2 rounded-xl bg-slate-900/70 hover:bg-white/10 text-slate-200 border border-white/10 text-left flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <span class="text-amber-400">✂</span> Shorten
                                        </button>
                                        <button type="button" x-on:click="runDemoAi('humanize')" class="p-2 rounded-xl bg-slate-900/70 hover:bg-white/10 text-slate-200 border border-white/10 text-left flex items-center gap-1.5 transition-colors cursor-pointer">
                                            <span class="text-pink-400">👤</span> Humanize
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Live AI Streaming Telemetry Output Card -->
                            <div class="p-3 rounded-2xl bg-indigo-950/40 border border-indigo-500/30 text-[11px] text-slate-300 space-y-1.5 font-mono">
                                <div class="flex items-center justify-between text-indigo-400 font-bold text-[10px]">
                                    <span class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400" :class="isStreaming ? 'animate-ping' : ''"></span>
                                        <span>AI TELEMETRY STREAM</span>
                                    </span>
                                    <span class="text-[9px] text-indigo-300" x-text="streamSpeed + ' tok/s'"></span>
                                </div>
                                <p class="text-slate-300 leading-relaxed font-sans text-xs" x-text="streamingToken || 'Ready. Click any action or Generate to test live token streaming.'"></p>
                            </div>
                        </div>

                        <!-- ─── COLUMN 2: MAIN DOCUMENT CANVAS (CENTER) ──────────────── -->
                        <div class="p-4 sm:p-6 flex flex-col justify-between bg-slate-900/40 border-r border-white/10 min-h-[580px]">
                            <div class="space-y-4">
                                <!-- Direct In-Canvas AI Generation Active Telemetry Stream Bar -->
                                <div 
                                    x-show="isStreaming" 
                                    x-cloak
                                    x-transition
                                    class="px-4 py-2 rounded-2xl bg-indigo-950/90 border border-indigo-500/50 shadow-xl flex items-center justify-between gap-3 text-xs animate-in"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <span class="relative flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                                        </span>
                                        <span class="font-bold text-white flex items-center gap-1.5">
                                            <span>✦ AI Typing Live in Canvas...</span>
                                            <span class="text-[10px] font-mono text-indigo-300 px-2 py-0.5 rounded-md bg-indigo-900/80 border border-indigo-500/30" x-text="selectedAiModel"></span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 font-mono text-xs">
                                        <span class="text-indigo-300 font-bold" x-text="streamSpeed + ' tok/s'"></span>
                                        <span class="text-slate-500">&bull;</span>
                                        <span class="text-slate-300" x-text="receivedTokens + ' tok'"></span>
                                    </div>
                                </div>

                                <!-- Collapsible Master Formatting Ribbon -->
                                <div class="flex flex-wrap items-center justify-between gap-2 p-2 bg-slate-900/95 rounded-2xl border border-white/10 text-xs shadow-md select-none">
                                    <div class="flex flex-wrap items-center gap-1 font-bold text-slate-300">
                                        <span class="px-2 py-0.5 rounded-lg bg-indigo-600/25 text-indigo-300 font-mono text-[10px] border border-indigo-500/30" x-text="activeEngine.toUpperCase()"></span>
                                        <span class="w-[1px] h-3.5 bg-white/10 mx-1"></span>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors">H1</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors">H2</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors">H3</button>
                                        <span class="w-[1px] h-3.5 bg-white/10 mx-1"></span>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors font-bold">B</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors italic">I</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors underline">U</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors line-through">S</button>
                                        <span class="w-[1px] h-3.5 bg-white/10 mx-1"></span>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors">• List</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors">1. List</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors font-mono">&lt;/&gt;</button>
                                        <button type="button" class="px-2 py-1 rounded-lg hover:bg-white/10 transition-colors font-serif">“ ”</button>
                                    </div>
                                    <button 
                                        type="button" 
                                        x-on:click="showInlinePrompt = !showInlinePrompt" 
                                        class="px-2 py-1 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 border border-indigo-500/30 text-[10.5px] font-mono flex items-center gap-1 transition-all cursor-pointer"
                                    >
                                        <span>✦ In-Canvas AI</span>
                                        <span class="text-[9px] text-slate-400">(/)</span>
                                    </button>
                                </div>

                                <!-- In-Canvas Floating AI Prompt Bar (Cmd+K / Slash Command) -->
                                <div 
                                    x-show="showInlinePrompt"
                                    x-cloak
                                    x-transition
                                    class="p-3.5 rounded-2xl bg-slate-950/95 border border-indigo-500/50 shadow-2xl space-y-2 text-xs"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                                            <span>✦ In-Canvas AI Prompt</span>
                                            <span class="text-[10px] text-indigo-300 font-mono" x-text="'(' + selectedAiModel + ')'"></span>
                                        </span>
                                        <button type="button" x-on:click="showInlinePrompt = false" class="text-slate-400 hover:text-white cursor-pointer">✕</button>
                                    </div>
                                    <div class="flex gap-2">
                                        <input type="text" placeholder="Tell AI what to write or transform next..." class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-3 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                                        <button type="button" x-on:click="runDemoAi('generate'); showInlinePrompt = false" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer">Generate</button>
                                    </div>
                                </div>

                                <!-- ENGINE 1: TIPTAP PROSEMIRROR CANVAS -->
                                <div x-show="activeEngine === 'tiptap'" class="p-6 rounded-2xl bg-slate-950/70 border border-white/5 min-h-[360px] space-y-4 focus:outline-none">
                                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                                        How to Write High-Ranking AI Content in 2026
                                    </h2>
                                    <p class="text-sm text-slate-300 leading-relaxed">
                                        Modern content teams demand flexibility, extreme speed, and bulletproof search engine optimization. With HelpOfAi Studio, you draft smoothly while every word is evaluated in real-time across four distinct SEO pillars.
                                    </p>
                                    <blockquote class="border-l-4 border-indigo-500 pl-4 py-2 text-indigo-200/90 italic font-serif text-sm bg-indigo-950/20 rounded-r-2xl">
                                        Architectural Principle: A universal content canvas must allow authors to shift between block, markdown, and rich-text workflows with zero formatting degradation.
                                    </blockquote>
                                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 space-y-2">
                                        <div class="text-xs font-bold text-indigo-300 font-mono">💡 Smart Callout Box</div>
                                        <p class="text-xs text-slate-300 leading-relaxed">
                                            Automated keyword density audits and Flesch reading calculations run in background web workers, preventing input stutter during rapid typing.
                                        </p>
                                    </div>
                                    <p class="text-sm text-slate-300 leading-relaxed">
                                        Seamlessly export to WordPress REST API, Markdown documents, or clean standalone HTML5 with a single click.
                                    </p>
                                </div>

                                <!-- ENGINE 2: GUTENBERG MODULAR BLOCKS -->
                                <div x-show="activeEngine === 'gutenberg'" class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 min-h-[360px] space-y-3" style="display: none;">
                                    <div class="flex items-center justify-between pb-2 border-b border-white/10 text-xs">
                                        <span class="font-mono text-purple-300 font-bold">❖ Gutenberg Block Canvas</span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" x-on:click="addGtBlock('paragraph')" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10.5px] font-semibold cursor-pointer transition-colors">+ Paragraph</button>
                                            <button type="button" x-on:click="addGtBlock('heading')" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10.5px] font-semibold cursor-pointer transition-colors">+ Heading</button>
                                            <button type="button" x-on:click="addGtBlock('quote')" class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10.5px] font-semibold cursor-pointer transition-colors">+ Quote</button>
                                        </div>
                                    </div>
                                    <template x-for="(blk, idx) in gtBlocks" :key="blk.id">
                                        <div class="p-3.5 rounded-2xl bg-slate-900/80 border border-white/10 space-y-1.5 relative group hover:border-purple-500/40 transition-colors shadow-inner">
                                            <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                                                <span class="uppercase font-bold text-purple-400" x-text="'❖ ' + blk.type"></span>
                                                <button type="button" x-on:click="removeGtBlock(idx)" class="text-slate-500 hover:text-red-400 cursor-pointer transition-colors">✕ Delete</button>
                                            </div>
                                            <div contenteditable="true" class="text-sm text-slate-200 focus:outline-none" x-text="blk.content"></div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ENGINE 3: NOTION-STYLE BLOCK CANVAS -->
                                <div x-show="activeEngine === 'notion'" class="p-5 rounded-2xl bg-slate-950/70 border border-white/5 min-h-[360px] space-y-3" style="display: none;">
                                    <div class="flex items-center justify-between pb-2 border-b border-white/10 text-xs text-slate-400 font-mono">
                                        <span class="text-violet-300 font-bold">⠿ Notion Block Canvas</span>
                                        <span>Type <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 border border-white/10 font-bold">/</kbd> for quick menu</span>
                                    </div>
                                    <template x-for="n in notionBlocks" :key="n.id">
                                        <div class="flex items-start gap-2.5 p-2.5 rounded-2xl hover:bg-slate-900/60 transition-colors group">
                                            <span class="text-slate-500 cursor-grab text-xs pt-0.5 opacity-40 group-hover:opacity-100 select-none">⠿</span>
                                            <div class="flex-1">
                                                <template x-if="n.type === 'heading'">
                                                    <h3 class="text-lg font-bold text-white" contenteditable="true" x-text="n.text"></h3>
                                                </template>
                                                <template x-if="n.type === 'callout'">
                                                    <div class="p-3.5 rounded-2xl bg-violet-950/30 border border-violet-500/30 text-xs text-slate-200" contenteditable="true" x-text="n.text"></div>
                                                </template>
                                                <template x-if="n.type === 'text'">
                                                    <p class="text-sm text-slate-300 leading-relaxed" contenteditable="true" x-text="n.text"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ENGINE 4: MARKDOWN SPLIT PREVIEW -->
                                <div x-show="activeEngine === 'markdown_split'" class="grid grid-cols-2 gap-3 h-[360px] border border-white/10 rounded-2xl overflow-hidden bg-slate-950/80" style="display: none;">
                                    <div class="flex flex-col border-r border-white/10 p-4 font-mono text-xs text-indigo-300 space-y-2">
                                        <span class="text-slate-500 font-bold uppercase text-[10px]">📝 Markdown Input</span>
                                        <textarea class="w-full flex-1 bg-transparent text-slate-200 focus:outline-none resize-none leading-relaxed font-mono">## Universal Multi-Editor Architecture

- **Tiptap ProseMirror** (WYSIWYG)
- **Gutenberg Blocks** (Modular)
- **Notion Canvas** (Slash Commands)

> Engineered for 2026 content publishing.</textarea>
                                    </div>
                                    <div class="flex flex-col p-4 text-xs text-slate-200 space-y-2 bg-slate-900/40 overflow-y-auto">
                                        <span class="text-slate-500 font-mono font-bold uppercase text-[10px]">👁 Live HTML Preview</span>
                                        <div class="prose prose-invert prose-sm">
                                            <h3 class="text-base font-bold text-white">Universal Multi-Editor Architecture</h3>
                                            <ul class="list-disc pl-4 space-y-1 text-slate-300">
                                                <li><strong>Tiptap ProseMirror</strong> (WYSIWYG)</li>
                                                <li><strong>Gutenberg Blocks</strong> (Modular)</li>
                                                <li><strong>Notion Canvas</strong> (Slash Commands)</li>
                                            </ul>
                                            <blockquote class="text-indigo-300 italic">Engineered for 2026 content publishing.</blockquote>
                                        </div>
                                    </div>
                                </div>

                                <!-- ENGINE 5: RAW MARKDOWN -->
                                <div x-show="activeEngine === 'markdown_raw'" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 min-h-[360px] font-mono text-xs text-cyan-300 leading-relaxed" style="display: none;">
                                    <span class="text-slate-500 block mb-2 font-bold uppercase text-[10px]">⌨️ Distraction-Free Raw Monospace</span>
                                    <p class="text-white font-bold"># Universal Multi-Editor Platform Specification</p>
                                    <p class="text-slate-400 mt-2">## Principles of Canonical Transformation</p>
                                    <p class="text-slate-300 mt-2">1. All mutations synchronize against the central document canonical model.<br/>2. Adapters declare lossy fidelity boundaries with user consent modals.<br/>3. Decoupled AI gateways execute semantic operations without editor coupling.</p>
                                </div>

                                <!-- ENGINE 6: HTML SOURCE -->
                                <div x-show="activeEngine === 'html'" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 min-h-[360px] font-mono text-xs text-amber-300 leading-relaxed" style="display: none;">
                                    <span class="text-slate-500 block mb-2 font-bold uppercase text-[10px]">💻 Raw HTML5 Code Editor</span>
                                    <p>&lt;<span class="text-red-400">article</span> <span class="text-amber-400">class</span>=<span class="text-emerald-400">"hoa-document"</span>&gt;</p>
                                    <p class="pl-4">&lt;<span class="text-red-400">h2</span>&gt;Universal Multi-Editor Platform&lt;/<span class="text-red-400">h2</span>&gt;</p>
                                    <p class="pl-4">&lt;<span class="text-red-400">p</span>&gt;Enterprise document authoring with real-time SSE streaming.&lt;/<span class="text-red-400">p</span>&gt;</p>
                                    <p>&lt;/<span class="text-red-400">article</span>&gt;</p>
                                </div>

                                <!-- ENGINE 7: PLAIN TEXT -->
                                <div x-show="activeEngine === 'plaintext'" class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 min-h-[360px] font-mono text-xs text-slate-300 leading-relaxed" style="display: none;">
                                    <span class="text-slate-500 block mb-2 font-bold uppercase text-[10px]">📄 Minimalist Plain Text Canvas</span>
                                    Next-Gen Decoupled AI Gateways in 2026

Modern content production platforms demand high-throughput intelligence routing with zero vendor lock-in. Clean, distraction-free environment for pure text authoring.
                                </div>
                            </div>

                            <!-- Document Bottom Counter & Goal Progress Bar -->
                            <div class="pt-4 border-t border-white/10 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-3 text-[11px] font-mono text-slate-400">
                                    <div class="flex items-center gap-3">
                                        <span class="text-white font-bold" x-text="wordCount + ' words'"></span>
                                        <span>&bull;</span>
                                        <span class="text-indigo-300">3,120 chars</span>
                                        <span>&bull;</span>
                                        <span class="text-cyan-300">3m read</span>
                                    </div>
                                    <div class="text-[10.5px]">
                                        <span>Target Goal: </span>
                                        <strong class="text-white" x-text="wordCount + ' / ' + targetGoal + ' words'"></strong>
                                        <span class="text-emerald-400 font-bold" x-text="' (' + Math.min(100, Math.round((wordCount/targetGoal)*100)) + '%)'"></span>
                                    </div>
                                </div>
                                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-full rounded-full transition-all duration-300" :style="'width: ' + Math.min(100, Math.round((wordCount/targetGoal)*100)) + '%'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- ─── COLUMN 3: CONTENT INTELLIGENCE & SEO AUDIT (RIGHT) ────── -->
                        <div 
                            x-show="showRightPanel" 
                            x-transition 
                            class="p-4 sm:p-5 bg-slate-950/85 flex flex-col justify-between text-xs space-y-4 h-full"
                        >
                            <div class="space-y-3.5">
                                <!-- Intelligence Header -->
                                <div class="flex items-center justify-between pb-2 border-b border-white/10">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <h3 class="text-xs uppercase font-extrabold text-white tracking-wider">Content Intelligence</h3>
                                    </div>
                                    <span class="text-[10px] text-emerald-400 font-mono font-bold" x-text="seoScore + '/100'"></span>
                                </div>

                                <!-- 7-Tab Navigation Grid (Exact Mirror of Real Editor) -->
                                <div class="grid grid-cols-4 gap-1 p-1 rounded-2xl bg-slate-900 border border-white/10 text-xs font-mono select-none shadow-inner">
                                    <button type="button" x-on:click="activeIntelTab = 'seo'" :class="activeIntelTab === 'seo' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                                        <span class="text-xs">🎯</span><span class="text-[9px] truncate">SEO</span>
                                    </button>
                                    <button type="button" x-on:click="activeIntelTab = 'titles'" :class="activeIntelTab === 'titles' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                                        <span class="text-xs">✨</span><span class="text-[9px] truncate">Titles</span>
                                    </button>
                                    <button type="button" x-on:click="activeIntelTab = 'gaps'" :class="activeIntelTab === 'gaps' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                                        <span class="text-xs">💡</span><span class="text-[9px] truncate">Gaps</span>
                                    </button>
                                    <button type="button" x-on:click="activeIntelTab = 'keys'" :class="activeIntelTab === 'keys' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                                        <span class="text-xs">🏷️</span><span class="text-[9px] truncate">Keys</span>
                                    </button>
                                    <button type="button" x-on:click="activeIntelTab = 'audit'" :class="activeIntelTab === 'audit' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                                        <span class="text-xs">🏆</span><span class="text-[9px] truncate">Audit</span>
                                    </button>
                                    <button type="button" x-on:click="activeIntelTab = 'outline'" :class="activeIntelTab === 'outline' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                                        <span class="text-xs">📑</span><span class="text-[9px] truncate">Outline</span>
                                    </button>
                                    <button type="button" x-on:click="activeIntelTab = 'history'" :class="activeIntelTab === 'history' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white'" class="py-1.5 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center col-span-2">
                                        <span class="text-xs">🕒</span><span class="text-[9px] truncate">Version History</span>
                                    </button>
                                </div>

                                <!-- Tab 1: SEO Gauges (Rank Math 100/100) -->
                                <div x-show="activeIntelTab === 'seo'" class="space-y-3">
                                    <div class="grid grid-cols-2 gap-2 text-center">
                                        <div class="p-3 rounded-2xl border border-emerald-500/40 bg-emerald-950/30">
                                            <span class="text-2xl font-black font-mono text-emerald-400" x-text="seoScore">94</span>
                                            <span class="text-[9px] uppercase block font-bold text-slate-300">SEO Score</span>
                                        </div>
                                        <div class="p-3 rounded-2xl border border-cyan-500/40 bg-cyan-950/30">
                                            <span class="text-2xl font-black font-mono text-cyan-400" x-text="readScore">82</span>
                                            <span class="text-[9px] uppercase block font-bold text-slate-300">Reading Ease</span>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-2xl bg-slate-900/90 border border-white/10 space-y-1.5 font-mono text-[10.5px]">
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword in Title:</span><span class="text-emerald-400 font-bold">✓ Yes</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword in Intro:</span><span class="text-emerald-400 font-bold">✓ Yes</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Subheading Coverage:</span><span class="text-emerald-400 font-bold">✓ 3 H2s</span></div>
                                        <div class="flex justify-between"><span class="text-slate-400">Keyword Density:</span><span class="text-emerald-400 font-bold">2.4% (Optimal)</span></div>
                                    </div>
                                </div>

                                <!-- Tab 2: AI Viral Titles -->
                                <div x-show="activeIntelTab === 'titles'" class="space-y-2" style="display: none;">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">AI Viral Headline Suggestions</span>
                                    <div class="space-y-2">
                                        <template x-for="t in titlesList" :key="t.title">
                                            <div class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 space-y-1 hover:border-indigo-500/40 transition-colors cursor-pointer" x-on:click="documentTitle = t.title">
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="font-bold text-indigo-300" x-text="t.score + '/100 Score'"></span>
                                                    <span class="px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300 font-bold" x-show="t.viral">Viral 🔥</span>
                                                </div>
                                                <p class="text-xs text-slate-200 font-medium" x-text="t.title"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Tab 3: Content Gaps -->
                                <div x-show="activeIntelTab === 'gaps'" class="space-y-2" style="display: none;">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">High-Impact Content Gaps</span>
                                    <div class="space-y-1.5">
                                        <template x-for="g in contentGaps" :key="g.topic">
                                            <div class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 flex items-center justify-between gap-2">
                                                <span class="text-[11px] text-slate-300 font-medium truncate" x-text="g.topic"></span>
                                                <span class="text-[9.5px] px-1.5 py-0.5 rounded font-mono font-bold shrink-0" :class="g.status === 'Added' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'" x-text="g.status"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Tab 4: Secondary & LSI Keywords -->
                                <div x-show="activeIntelTab === 'keys'" class="space-y-2" style="display: none;">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Targeted LSI Keyword Matrix</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="px-2 py-1 rounded-xl bg-indigo-950/70 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">ai writing studio ✓</span>
                                        <span class="px-2 py-1 rounded-xl bg-indigo-950/70 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">gutenberg blocks ✓</span>
                                        <span class="px-2 py-1 rounded-xl bg-indigo-950/70 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">seo real time score ✓</span>
                                        <span class="px-2 py-1 rounded-xl bg-indigo-950/70 border border-indigo-500/30 text-indigo-300 font-mono text-[10.5px]">omniroute gateway ✓</span>
                                    </div>
                                </div>

                                <!-- Tab 5: E-E-A-T Quality Audit -->
                                <div x-show="activeIntelTab === 'audit'" class="space-y-2" style="display: none;">
                                    <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-start gap-2">
                                        <span>✓</span> <span>Experience: Real-world workflow screenshots & examples.</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-start gap-2">
                                        <span>✓</span> <span>Expertise: Technical schema & architecture citations.</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-emerald-950/20 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-start gap-2">
                                        <span>✓</span> <span>Authoritativeness: Original insights on universal models.</span>
                                    </div>
                                </div>

                                <!-- Tab 6: Interactive Outline Tree -->
                                <div x-show="activeIntelTab === 'outline'" class="space-y-1.5 font-mono text-[11px]" style="display: none;">
                                    <div class="p-2 rounded-xl bg-indigo-600/20 text-indigo-300 font-bold border border-indigo-500/30">H2 How to Write High-Ranking AI Content</div>
                                    <div class="p-1.5 rounded-lg text-slate-300 pl-4 border-l border-white/10">H3 Multi-Engine Zero Format Loss</div>
                                    <div class="p-1.5 rounded-lg text-slate-300 pl-4 border-l border-white/10">H3 Live Real-Time SEO Scoring</div>
                                </div>

                                <!-- Tab 7: Snapshot Versions Timeline -->
                                <div x-show="activeIntelTab === 'history'" class="space-y-2" style="display: none;">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Snapshot Rollback History</span>
                                    <div class="space-y-2">
                                        <template x-for="snap in snapshots" :key="snap.id">
                                            <div class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 flex items-center justify-between gap-2">
                                                <div>
                                                    <div class="text-xs font-bold text-white" x-text="snap.name"></div>
                                                    <div class="text-[10px] text-slate-400" x-text="snap.time + ' • ' + snap.words + ' words'"></div>
                                                </div>
                                                <button type="button" x-on:click="restoreSnapshot(snap)" class="px-2 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 text-[10px] font-bold cursor-pointer transition-colors">Restore</button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Launch Full Live Studio CTA -->
                            <a href="{{ route('editor') }}">
                                <x-glass.button variant="primary" size="sm" class="w-full shadow-lg shadow-indigo-600/30 justify-center">
                                    ✍️ Open Full Live Studio &rarr;
                                </x-glass.button>
                            </a>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- REAL EDITOR STICKY BOTTOM STATUS BAR                                      -->
                    <!-- ========================================================================= -->
                    <div class="px-6 py-2.5 bg-slate-950 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-[11px] font-mono text-slate-400 select-none">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-300 font-bold">Doc ID: <span class="text-indigo-400">#1042</span></span>
                            <span>&bull;</span>
                            <span>UTF-8</span>
                            <span>&bull;</span>
                            <span>Engine: <strong class="text-white" x-text="activeEngine.toUpperCase()"></strong></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-emerald-400">● Cloud Sync: Active</span>
                            <span>&bull;</span>
                            <span class="text-indigo-300">SSE Latency: 12ms</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 3. HOW IT WORKS SECTION (Simple 3-Step Guided Workflow)                   -->
        <!-- ========================================================================= -->
        <section id="how-it-works" class="py-16 sm:py-24 border-b border-white/5 relative scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="emerald" class="mb-4">Simple & Powerful</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        How HelpOfAi Studio Works in 3 Simple Steps
                    </h2>
                    <p class="text-slate-300 mt-3 text-sm sm:text-base leading-relaxed">
                        From blank page to top-ranking article in minutes. Here is how your content comes to life.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    <!-- Step 1 -->
                    <x-glass.card variant="standard" class="p-8 relative hover:border-indigo-500/50 transition-all group hoa-card-glow-shadow">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-xl text-indigo-300 font-bold mb-6 group-hover:scale-110 transition-transform">
                            1
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Choose Your Canvas</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Pick the writing tool you love most: Notion block canvas, WordPress Gutenberg, Tiptap, or Markdown. Switch anytime with <strong>zero lost formatting</strong>.
                        </p>
                    </x-glass.card>

                    <!-- Step 2 -->
                    <x-glass.card variant="standard" class="p-8 relative hover:border-purple-500/50 transition-all group hoa-card-glow-shadow">
                        <div class="w-12 h-12 rounded-2xl bg-purple-600/20 border border-purple-500/40 flex items-center justify-center text-xl text-purple-300 font-bold mb-6 group-hover:scale-110 transition-transform">
                            2
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Co-Write with OmniRoute AI</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Route prompts to <strong>Claude 3.7 Sonnet, GPT-4o, Gemini 2.0 Flash, or DeepSeek</strong>. Generate outlines, expand paragraphs, or polish your tone with live SSE streaming.
                        </p>
                    </x-glass.card>

                    <!-- Step 3 -->
                    <x-glass.card variant="standard" class="p-8 relative hover:border-cyan-500/50 transition-all group hoa-card-glow-shadow">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-600/20 border border-cyan-500/40 flex items-center justify-center text-xl text-cyan-300 font-bold mb-6 group-hover:scale-110 transition-transform">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Optimize SEO & Export</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Watch your <strong>live SEO score (0-100)</strong> climb as you write. Verify keyword density, readability ease, and export directly to WordPress, Markdown, or HTML.
                        </p>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 4. 8 DEDICATED WRITING ENGINES                                            -->
        <!-- ========================================================================= -->
        <section id="engines" class="py-16 sm:py-24 border-b border-white/5 scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="violet" class="mb-4">Universal Engine Platform</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">8 Dedicated Writing Engines</h2>
                    <p class="text-slate-300 mt-3 text-sm sm:text-base leading-relaxed">Every document is saved in a unified data structure, giving you the freedom to write in whatever editor fits your style best.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Engine 1: Tiptap -->
                    <x-glass.card variant="standard" class="hover:border-indigo-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 font-bold text-lg">✨</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Tiptap Visual Editor</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Smooth, modern rich-text editing with floating bubble selection tools, slash commands, and table formatting.
                        </p>
                        <div class="text-[10px] font-mono text-indigo-300 bg-indigo-950/40 p-1.5 rounded-lg border border-indigo-500/20">
                            richText: true &bull; visual: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 2: Gutenberg -->
                    <x-glass.card variant="standard" class="hover:border-purple-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 font-bold text-lg">❖</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Gutenberg Block Canvas</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            WordPress-compatible modular blocks for paragraphs, headings, quotes, and callouts with one-click ordering.
                        </p>
                        <div class="text-[10px] font-mono text-purple-300 bg-purple-950/40 p-1.5 rounded-lg border border-purple-500/20">
                            blocks: true &bull; wpCompatible: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 3: Notion -->
                    <x-glass.card variant="standard" class="hover:border-violet-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400 mb-4 font-bold text-lg">⠿</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Notion Workspace</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Draggable block handles, colored callout boxes, toggle dropdowns, and keyboard slash commands.
                        </p>
                        <div class="text-[10px] font-mono text-violet-300 bg-violet-950/40 p-1.5 rounded-lg border border-violet-500/20">
                            dragHandles: true &bull; callouts: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 4: Markdown Split -->
                    <x-glass.card variant="standard" class="hover:border-cyan-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-4 font-bold text-lg">📝</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Markdown Split Preview</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Side-by-side editing with raw markdown code on the left and instant live HTML preview on the right.
                        </p>
                        <div class="text-[10px] font-mono text-cyan-300 bg-cyan-950/40 p-1.5 rounded-lg border border-cyan-500/20">
                            splitPreview: true &bull; liveSync: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 5: Raw Markdown -->
                    <x-glass.card variant="standard" class="hover:border-blue-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 mb-4 font-bold text-lg">⌨️</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Raw Monospace</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Distraction-free, zero-lag monospace editor optimized for keyboard flow, developers, and markdown purists.
                        </p>
                        <div class="text-[10px] font-mono text-blue-300 bg-blue-950/40 p-1.5 rounded-lg border border-blue-500/20">
                            monospace: true &bull; fastTyping: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 6: HTML Source -->
                    <x-glass.card variant="standard" class="hover:border-amber-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4 font-bold text-lg">💻</div>
                        <h3 class="text-base font-bold text-white mb-1.5">HTML5 Source</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Direct HTML source editing with built-in sandbox security for marketers, developers, and custom embeds.
                        </p>
                        <div class="text-[10px] font-mono text-amber-300 bg-amber-950/40 p-1.5 rounded-lg border border-amber-500/20">
                            html5: true &bull; sanitized: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 7: Plain Text -->
                    <x-glass.card variant="standard" class="hover:border-slate-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-slate-500/10 border border-slate-500/20 flex items-center justify-center text-slate-400 mb-4 font-bold text-lg">📄</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Plain Text Canvas</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Ultra-lightweight drafting environment with zero tags or styles for pure brainstorming and note taking.
                        </p>
                        <div class="text-[10px] font-mono text-slate-300 bg-slate-900/60 p-1.5 rounded-lg border border-white/10">
                            plainText: true &bull; lightweight: true
                        </div>
                    </x-glass.card>

                    <!-- Engine 8: Universal Adapters -->
                    <x-glass.card variant="standard" class="hover:border-pink-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 mb-4 font-bold text-lg">🔮</div>
                        <h3 class="text-base font-bold text-white mb-1.5">Universal Adapters</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Switch between any of the 8 engines in real time with automated format translation and zero content destruction.
                        </p>
                        <div class="text-[10px] font-mono text-pink-300 bg-pink-950/40 p-1.5 rounded-lg border border-pink-500/20">
                            zeroLockIn: true &bull; lossless: true
                        </div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 5. CORE FEATURES & INTELLIGENCE                                           -->
        <!-- ========================================================================= -->
        <section id="features" class="py-16 sm:py-24 border-b border-white/5 scroll-mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="cyan" class="mb-4">Intelligent Co-Pilot</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Built for Writers Who Want to Rank #1</h2>
                    <p class="text-slate-300 mt-3 text-sm sm:text-base leading-relaxed">Enterprise AI infrastructure, live SEO metrics, and snapshot versioning designed for publishing peace of mind.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1: OmniRoute -->
                    <x-glass.card variant="standard" class="hover:border-purple-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 font-bold text-lg">⚡</div>
                        <h3 class="text-base font-bold text-white mb-2">OmniRoute AI Gateway</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Connect once to access Claude 3.7 Sonnet, GPT-4o, Gemini 2.0 Flash, or DeepSeek with automatic failover and streaming token output.
                        </p>
                        <div class="text-[11px] font-mono text-purple-300">OmniRoute Gateway &bull; Multi-Model</div>
                    </x-glass.card>

                    <!-- Feature 2: SEO -->
                    <x-glass.card variant="standard" class="hover:border-emerald-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4 font-bold text-lg">🎯</div>
                        <h3 class="text-base font-bold text-white mb-2">Real-Time SEO Scoring</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Live keyword placement checks across title, intro, and headings, combined with reading ease calculations and actionable recommendations.
                        </p>
                        <div class="text-[11px] font-mono text-emerald-300">Live 0-100 Score &bull; LSI Keywords</div>
                    </x-glass.card>

                    <!-- Feature 3: Versioning -->
                    <x-glass.card variant="standard" class="hover:border-indigo-500/50 transition-all p-6 hoa-card-glow-shadow">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 font-bold text-lg">🕒</div>
                        <h3 class="text-base font-bold text-white mb-2">Snapshot Time-Travel</h3>
                        <p class="text-xs text-slate-300 leading-relaxed mb-4">
                            Automatic checkpoints created before major AI edits and editor switches. Restore any previous revision with a single click.
                        </p>
                        <div class="text-[11px] font-mono text-indigo-300">1-Click Rollback &bull; Zero Data Loss</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 6. DESIGN SYSTEM & ANIMATED BUTTON SHOWCASE                              -->
        <!-- ========================================================================= -->
        <section id="glass-system" class="py-16 sm:py-24 border-b border-white/5 scroll-mt-16 relative" x-data="{ demoLoading: true }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="purple" class="mb-4">Design System & Micro-Interactions</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Interactive Button Loading Animations
                    </h2>
                    <p class="text-slate-300 mt-3 text-sm sm:text-base leading-relaxed">
                        10 ultra-lightweight, CSS-only animated loading states engineered for high-performance Livewire & Alpine micro-interactions.
                    </p>
                    <div class="mt-6 flex items-center justify-center gap-3">
                        <button 
                            type="button" 
                            @click="demoLoading = !demoLoading" 
                            class="px-4 py-2 rounded-xl bg-slate-900 border border-indigo-500/40 text-indigo-300 hover:text-white text-xs font-mono font-bold shadow-lg shadow-indigo-600/20 hover:border-indigo-400 transition-all cursor-pointer flex items-center gap-2"
                        >
                            <span class="w-2 h-2 rounded-full" :class="demoLoading ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'"></span>
                            <span x-text="demoLoading ? '✦ Toggle to Normal State' : '✦ Toggle to Loading State'"></span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- 1. Classic Spinner -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-indigo-400 font-bold uppercase">1. Classic Spinner</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="spinner" x-show="demoLoading"></span>
                            <span x-text="demoLoading ? 'Saving...' : 'Save Draft'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="spinner"</div>
                    </x-glass.card>

                    <!-- 2. Three Dots -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-purple-400 font-bold uppercase">2. Three Dots</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md shadow-purple-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span x-text="demoLoading ? 'Loading' : 'Analyze SEO'"></span>
                            <span class="dots" x-show="demoLoading"><span></span><span></span><span></span></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="dots"</div>
                    </x-glass.card>

                    <!-- 3. Pulse Ring -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-cyan-400 font-bold uppercase">3. Pulse Ring</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-md shadow-cyan-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="pulse-ring" x-show="demoLoading"></span>
                            <span x-text="demoLoading ? 'Processing' : 'Index Page'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="pulse-ring"</div>
                    </x-glass.card>

                    <!-- 4. Dual Ring -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-emerald-400 font-bold uppercase">4. Dual Ring</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="dual" x-show="demoLoading"></span>
                            <span x-text="demoLoading ? 'Updating...' : 'Sync Cloud'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="dual"</div>
                    </x-glass.card>

                    <!-- 5. Animated Bars -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-amber-400 font-bold uppercase">5. Animated Bars</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span x-text="demoLoading ? 'Streaming' : 'AI Transform'"></span>
                            <span class="bars" x-show="demoLoading"><span></span><span></span><span></span><span></span></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="bars"</div>
                    </x-glass.card>

                    <!-- 6. Fading Ellipsis -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-pink-400 font-bold uppercase">6. Fading Ellipsis</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-pink-600 hover:bg-pink-500 text-white font-bold text-xs shadow-md shadow-pink-600/30 flex items-center justify-center gap-1 transition-all cursor-pointer">
                            <span x-text="demoLoading ? 'Please wait' : 'Export HTML'"></span>
                            <span class="ellipsis" x-show="demoLoading">...</span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="ellipsis"</div>
                    </x-glass.card>

                    <!-- 7. Orbiting Satellite -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-violet-400 font-bold uppercase">7. Orbiting Dot</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-md shadow-violet-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="orbit" x-show="demoLoading"></span>
                            <span x-text="demoLoading ? 'Routing AI' : 'OmniRoute'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="orbit"</div>
                    </x-glass.card>

                    <!-- 8. Spinning Hourglass -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-blue-400 font-bold uppercase">8. Hourglass</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="hourglass" x-show="demoLoading"></span>
                            <span x-text="demoLoading ? 'Working...' : 'Restore State'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="hourglass"</div>
                    </x-glass.card>

                    <!-- 9. Rotating Square -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-teal-400 font-bold uppercase">9. Morph Square</div>
                        <button type="button" class="w-full py-2.5 px-3 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs shadow-md shadow-teal-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="square" x-show="demoLoading"></span>
                            <span x-text="demoLoading ? 'Generating' : 'Create Article'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="square"</div>
                    </x-glass.card>

                    <!-- 10. Shimmer Sweep -->
                    <x-glass.card variant="subtle" class="p-5 text-center space-y-3 hoa-card-glow-shadow">
                        <div class="text-[11px] font-mono text-indigo-300 font-bold uppercase">10. Shimmer Sweep</div>
                        <button type="button" :class="demoLoading ? 'btn-shimmer' : ''" class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span>✦</span> <span x-text="demoLoading ? 'Magic Shimmer' : 'Pro License'"></span>
                        </button>
                        <div class="text-[10px] font-mono text-slate-400">class="shimmer"</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- 7. FREQUENTLY ASKED QUESTIONS (SEO FAQ Accordion + Schema)                -->
        <!-- ========================================================================= -->
        <section id="faq" class="py-16 sm:py-24 border-b border-white/5 scroll-mt-16" x-data="{ activeFaq: 1 }">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <x-glass.badge variant="amber" class="mb-4">Got Questions?</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Frequently Asked Questions
                    </h2>
                    <p class="text-slate-300 mt-3 text-sm sm:text-base leading-relaxed">
                        Everything you need to know about HelpOfAi Studio, engine compatibility, and AI credits.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- FAQ 1 -->
                    <div class="glass-standard rounded-2xl p-5 border border-white/10 transition-all">
                        <button 
                            type="button" 
                            @click="activeFaq = (activeFaq === 1 ? null : 1)" 
                            class="w-full flex items-center justify-between text-left font-bold text-white text-base cursor-pointer"
                        >
                            <span>Can I really switch between Tiptap, Notion, Gutenberg, and Markdown without losing my formatting?</span>
                            <span class="text-indigo-400 font-mono text-xl" x-text="activeFaq === 1 ? '−' : '+'"></span>
                        </button>
                        <div x-show="activeFaq === 1" x-transition class="mt-3 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
                            Yes! HelpOfAi Studio stores your content in a centralized, universal data format. When you switch engines, our lossless adapters translate every heading, paragraph, list, quote, and code block into the target editor's native representation with zero formatting destruction.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="glass-standard rounded-2xl p-5 border border-white/10 transition-all">
                        <button 
                            type="button" 
                            @click="activeFaq = (activeFaq === 2 ? null : 2)" 
                            class="w-full flex items-center justify-between text-left font-bold text-white text-base cursor-pointer"
                        >
                            <span>Which AI models can I use with OmniRoute?</span>
                            <span class="text-indigo-400 font-mono text-xl" x-text="activeFaq === 2 ? '−' : '+'"></span>
                        </button>
                        <div x-show="activeFaq === 2" x-transition class="mt-3 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3" style="display: none;">
                            OmniRoute connects with top AI providers including Anthropic (Claude 3.7 Sonnet, Claude 3.5 Haiku), OpenAI (GPT-4o, GPT-4o-mini), Google (Gemini 2.0 Flash, Gemini 1.5 Pro), DeepSeek (DeepSeek-V3, DeepSeek-R1), and local Ollama or vLLM instances.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="glass-standard rounded-2xl p-5 border border-white/10 transition-all">
                        <button 
                            type="button" 
                            @click="activeFaq = (activeFaq === 3 ? null : 3)" 
                            class="w-full flex items-center justify-between text-left font-bold text-white text-base cursor-pointer"
                        >
                            <span>Does HelpOfAi Studio run on standard cPanel / shared hosting?</span>
                            <span class="text-indigo-400 font-mono text-xl" x-text="activeFaq === 3 ? '−' : '+'"></span>
                        </button>
                        <div x-show="activeFaq === 3" x-transition class="mt-3 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3" style="display: none;">
                            Yes! HOA-Studio is architected with a strict Pure-PHP fallback layer. It requires zero Node.js daemon or CLI execution on production servers, allowing seamless 1-click deployments on shared hosting, cPanel, VPS, or cloud servers with PHP 8.2+.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="glass-standard rounded-2xl p-5 border border-white/10 transition-all">
                        <button 
                            type="button" 
                            @click="activeFaq = (activeFaq === 4 ? null : 4)" 
                            class="w-full flex items-center justify-between text-left font-bold text-white text-base cursor-pointer"
                        >
                            <span>How does the Real-Time SEO scoring work?</span>
                            <span class="text-indigo-400 font-mono text-xl" x-text="activeFaq === 4 ? '−' : '+'"></span>
                        </button>
                        <div x-show="activeFaq === 4" x-transition class="mt-3 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3" style="display: none;">
                            As you type, our integrated SEO analyzer computes your keyword placement in title, introduction, and headings (H2/H3), calculates Flesch-Kincaid readability indices, monitors target keyword density, and provides clear recommendations to maximize your Google ranking potential.
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="glass-standard rounded-2xl p-5 border border-white/10 transition-all">
                        <button 
                            type="button" 
                            @click="activeFaq = (activeFaq === 5 ? null : 5)" 
                            class="w-full flex items-center justify-between text-left font-bold text-white text-base cursor-pointer"
                        >
                            <span>What happens if an AI rewrite makes an unwanted change?</span>
                            <span class="text-indigo-400 font-mono text-xl" x-text="activeFaq === 5 ? '−' : '+'"></span>
                        </button>
                        <div x-show="activeFaq === 5" x-transition class="mt-3 text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3" style="display: none;">
                            Our Snapshot Time-Travel engine automatically saves an atomic backup before executing any AI command or engine change. If you do not like an AI modification, you can revert back to the previous snapshot with a single click.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Structured JSON-LD Schema for Google Rich Search Results -->
            <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Can I switch between Tiptap, Notion, Gutenberg, and Markdown without losing formatting?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Yes. HelpOfAi Studio stores your content in a centralized universal format with lossless adapters translating between ProseMirror, Gutenberg blocks, and Markdown with zero formatting loss.'
                        ]
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Which AI models can I use with OmniRoute?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'OmniRoute supports Claude 3.7 Sonnet, GPT-4o, Gemini 2.0 Flash, DeepSeek-V3, and self-hosted Ollama models with live SSE streaming.'
                        ]
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Does HelpOfAi Studio run on standard cPanel / shared hosting?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Yes, HelpOfAi Studio is engineered with Pure-PHP fallbacks requiring zero CLI or Node.js server dependencies for standard cPanel and VPS hosting.'
                        ]
                    ]
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
            </script>
        </section>

        <!-- ========================================================================= -->
        <!-- 7. FINAL CALL TO ACTION                                                   -->
        <!-- ========================================================================= -->
        <section class="py-20 sm:py-32 relative overflow-hidden text-center">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-glass.card variant="premium" glow="indigo" class="p-8 sm:p-16 relative overflow-hidden hoa-welcome-glow-border hoa-editor-shadow">
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
                            <a href="{{ route('register') }}" class="relative group block w-max">
                                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl blur opacity-30 group-hover:opacity-100 transition duration-500"></div>
                                <x-glass.button variant="primary" size="lg" shimmer="true" class="relative px-10 py-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-2xl shadow-indigo-600/40 text-white font-bold !border-0 text-lg">
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
    <x-public-footer />

    <!-- Alpine.js Component Definition Script for Clean, Quote-Safe Initialization -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('multiEditorDemo', () => ({
                activeEngine: 'tiptap',
                activeIntelTab: 'seo',
                showLeftPanel: true,
                showRightPanel: true,
                isStreaming: false,
                showInlinePrompt: false,
                documentTitle: 'How to Write High-Ranking AI Content in 2026',
                aiPromptText: 'Write a comprehensive guide on AI multi-editor architecture in 2026',
                selectedProvider: 'omniroute',
                selectedAiModel: 'Claude 3.7 Sonnet (OmniRoute)',
                streamingToken: '',
                streamSpeed: 48,
                receivedTokens: 0,
                wordCount: 428,
                seoScore: 94,
                readScore: 82,
                targetGoal: 1500,

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

                titlesList: [
                    { title: 'The 2026 Guide to Decoupled AI Writing Platforms', score: 98, viral: true },
                    { title: 'How to Build High-Ranking Content with Multi-Editor AI', score: 95, viral: true },
                    { title: 'Why 8 Writing Engines Beat Vendor Lock-in Every Time', score: 92, viral: false }
                ],

                contentGaps: [
                    { topic: 'Zero-CLI Shared Hosting Deployment Guide', impact: 'High', status: 'Missing' },
                    { topic: 'Token Quota Circuit Breakers & Failover', impact: 'High', status: 'Added' },
                    { topic: 'ProseMirror to Markdown Conversion Matrix', impact: 'Medium', status: 'Missing' }
                ],

                snapshots: [
                    { id: 1, name: 'Initial AI Outline Draft', time: '10 mins ago', words: 210 },
                    { id: 2, name: 'Added Gutenberg Modular Blocks', time: '5 mins ago', words: 340 },
                    { id: 3, name: 'SEO Optimization & LSI Infusion', time: 'Just now', words: 428 }
                ],

                restoreSnapshot(snap) {
                    this.streamingToken = '✓ Restored snapshot: ' + snap.name;
                    this.wordCount = snap.words;
                },

                toggleLeftPanel() {
                    this.showLeftPanel = !this.showLeftPanel;
                },
                toggleRightPanel() {
                    this.showRightPanel = !this.showRightPanel;
                },
                toggleFocusMode() {
                    if (this.showLeftPanel || this.showRightPanel) {
                        this.showLeftPanel = false;
                        this.showRightPanel = false;
                    } else {
                        this.showLeftPanel = true;
                        this.showRightPanel = true;
                    }
                },

                runDemoAi(type) {
                    this.isStreaming = true;
                    this.receivedTokens = 0;
                    this.streamingToken = 'Routing prompt to ' + this.selectedAiModel + ' via OmniRoute proxy...';
                    
                    let interval = setInterval(() => {
                        if (this.receivedTokens < 180) {
                            this.receivedTokens += 36;
                        }
                    }, 120);

                    setTimeout(() => {
                        clearInterval(interval);
                        this.receivedTokens = 240;
                        if (type === 'generate') {
                            this.streamingToken = '✓ Generated 240 tokens in 0.6s: Multi-tier streaming proxy provides token quota pre-flight checks and real-time SSE multiplexing across distributed AI clusters.';
                            this.wordCount += 24;
                            this.seoScore = 98;
                        } else if (type === 'rewrite') {
                            this.streamingToken = '✓ Polished & Refined: Decoupled AI routing ensures resilient failover and zero downtime for mission-critical enterprise publishing teams.';
                        } else if (type === 'seo') {
                            this.streamingToken = '✓ SEO Boost Complete: Integrated 4 LSI keywords, optimized H2 scannability, and generated schema metadata.';
                            this.seoScore = 100;
                        } else if (type === 'humanize') {
                            this.streamingToken = '✓ Humanized: Balanced sentence variation, conversational transitions, and improved reading ease to 88/100.';
                            this.readScore = 88;
                        }
                        this.isStreaming = false;
                    }, 750);
                }
            }));
        });
    </script>
</x-layouts.app>

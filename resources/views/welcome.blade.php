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

<x-layouts.app title="HelpOfAi Studio (HOA-Studio) — Production AI Content Workspace">
    <!-- Ambient Background Lighting -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[32rem] h-[32rem] bg-purple-600/20 rounded-full blur-[128px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[30rem] h-[30rem] bg-indigo-600/15 rounded-full blur-[128px]"></div>
        <div class="absolute top-2/3 -left-20 w-[28rem] h-[28rem] bg-cyan-600/15 rounded-full blur-[128px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[36rem] h-[36rem] bg-purple-900/20 rounded-full blur-[140px]"></div>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 w-full border-b border-white/5 bg-slate-950/60 backdrop-blur-xl transition-all">
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
                        <x-glass.badge variant="violet" class="hidden sm:inline-flex text-[10px] py-0.5 px-2">v1.0</x-glass.badge>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-none">AI Content Architecture</p>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#simulator" class="hover:text-white transition-colors">Studio Preview</a>
                <a href="#omniroute" class="hover:text-white transition-colors">OmniRoute AI</a>
                <a href="#templates" class="hover:text-white transition-colors">Templates</a>
                <a href="#glass-system" class="hover:text-white transition-colors">Design System</a>
                <a href="#pricing" class="hover:text-white transition-colors">Quotas</a>
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

                    <a href="{{ route('dashboard') }}">
                        <x-glass.button variant="primary" size="sm" class="shadow-indigo-500/30">
                            🚀 Open Studio
                        </x-glass.button>
                    </a>

                    <a href="{{ route('profile') }}" class="flex items-center gap-2 p-1 rounded-xl glass-subtle hover:border-white/20 transition-all" title="User Profile">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-xs text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </a>
                @else
                    <x-glass.button variant="glass" size="sm" class="hidden md:inline-flex" x-data x-on:click="$dispatch('open-modal', 'demo-modal')">
                        Live Demo
                    </x-glass.button>

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

    <!-- Main Landing Container -->
    <main class="flex-1">
        <!-- Hero Section -->
        <section class="relative pt-16 pb-20 sm:pt-24 sm:pb-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Stack Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-subtle text-xs text-indigo-300 mb-8 border border-indigo-500/20 shadow-inner">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-semibold text-white">Laravel 13 + Livewire 4</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-cyan-300">OmniRoute Decoupled AI Gateway</span>
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.1] max-w-5xl mx-auto mb-8">
                    The Modern AI Writing Suite <br class="hidden sm:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">
                        Built for True Production Scale
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-base sm:text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed font-normal">
                    Transform long-form content generation with a feature-first domain architecture, Tiptap ProseMirror editor, brand voice embeddings, and provider-agnostic AI routing for local and cloud models.
                </p>

                <!-- Dual Action CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto mb-16">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto">
                            <x-glass.button variant="primary" size="lg" class="w-full px-8 shadow-xl shadow-indigo-600/30">
                                Open Studio Dashboard &rarr;
                            </x-glass.button>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto">
                            <x-glass.button variant="primary" size="lg" class="w-full px-8 shadow-xl shadow-indigo-600/30">
                                Launch Free AI Workspace
                            </x-glass.button>
                        </a>
                    @endauth
                    <x-glass.button variant="glass" size="lg" class="w-full sm:w-auto px-8" onclick="document.getElementById('simulator').scrollIntoView({behavior: 'smooth'})">
                        Explore Architecture
                    </x-glass.button>
                </div>

                <!-- Live Metrics Bar -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-white">100%</div>
                        <div class="text-xs text-slate-400 mt-1">Provider Agnostic</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-indigo-400">&lt; 150ms</div>
                        <div class="text-xs text-slate-400 mt-1">Livewire 4 Hydration</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-purple-400">4-Tier</div>
                        <div class="text-xs text-slate-400 mt-1">Glass Design System</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-4">
                        <div class="text-2xl sm:text-3xl font-black text-cyan-400">Zero</div>
                        <div class="text-xs text-slate-400 mt-1">Node Runtime at Prod</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- Interactive Studio Simulator Section -->
        <section id="simulator" class="py-16 sm:py-24 border-y border-white/5 relative bg-slate-950/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <x-glass.badge variant="cyan" class="mb-4">Interactive Demo</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Experience HelpOfAi Studio in Action</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Test the ProseMirror editor layout, AI transformation toolbar, and real-time streaming simulation.</p>
                </div>

                <!-- 3-Column Studio Simulator Component (Alpine.js State) -->
                <div x-data="{
                    activeAction: 'generate',
                    selectedModel: 'OmniRoute: DeepSeek-V3',
                    isGenerating: false,
                    editorContent: '<h3>Building Resilient Cloud Architectures in 2026</h3><p>Modern distributed systems demand decoupled intelligence, sub-second latency, and fault-tolerant storage pipelines. By abstracting the AI model gateway through an intelligent proxy, SaaS platforms eliminate vendor lock-in while preserving real-time SSE stream performance across shared and dedicated host environments.</p>',
                    runAiAction(action) {
                        this.activeAction = action;
                        this.isGenerating = true;
                        let additions = {
                            'generate': '<h4>Key Pillars of Resiliency</h4><ul><li><strong>Zero-Lock-in Gateways:</strong> Seamless multi-provider failover.</li><li><strong>State Hydration:</strong> Atomic version snapshots on every mutation.</li><li><strong>Vector Grounding:</strong> Low-latency local RAG retrieval.</li></ul>',
                            'rewrite': '<p><em>Polished by AI:</em> In 2026, building scalable cloud architectures requires decoupling AI compute gateways, achieving microsecond responsiveness, and enforcing hardened state snapshots across all execution layers.</p>',
                            'shorten': '<p><strong>Summary:</strong> Next-gen cloud apps require decoupled AI routing, sub-second latency, and resilient snapshot storage without vendor lock-in.</p>',
                            'seo': '<p class=\'text-xs text-emerald-400 font-mono\'>[SEO Audit Passed: 94/100 | Keyword Density: 2.8% | Readability: Grade 9 | Meta Description Generated]</p>'
                        };

                        setTimeout(() => {
                            if (action === 'generate') {
                                this.editorContent += additions[action];
                            } else {
                                this.editorContent = additions[action];
                            }
                            this.isGenerating = false;
                        }, 600);
                    }
                }" class="glass-elevated rounded-2xl overflow-hidden border border-white/15 shadow-2xl">
                    <!-- Simulator Window Header -->
                    <div class="px-6 py-3.5 bg-slate-900/80 border-b border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                            </div>
                            <span class="text-xs font-mono text-slate-400 ml-2">HelpOfAi Studio &mdash; Document Editor</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400" x-text="'Active Model: ' + selectedModel"></span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                    </div>

                    <!-- 3-Column Body -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[480px]">
                        <!-- Left Panel: Explorer -->
                        <div class="lg:col-span-3 border-r border-white/5 p-4 bg-slate-950/40 hidden lg:block text-xs">
                            <div class="font-semibold text-slate-300 uppercase tracking-wider text-[10px] mb-3">Project Documents</div>
                            <div class="space-y-1">
                                <div class="px-3 py-2 rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 flex items-center justify-between">
                                    <span class="font-medium truncate">Cloud_Architecture_2026.md</span>
                                    <span class="text-[10px] bg-indigo-500/30 px-1.5 py-0.5 rounded">v3</span>
                                </div>
                                <div class="px-3 py-2 rounded-lg text-slate-400 hover:bg-white/5 transition-colors">
                                    <span>OmniRoute_SDK_Spec.md</span>
                                </div>
                                <div class="px-3 py-2 rounded-lg text-slate-400 hover:bg-white/5 transition-colors">
                                    <span>BrandVoice_Enterprise_Tone.md</span>
                                </div>
                            </div>

                            <div class="font-semibold text-slate-300 uppercase tracking-wider text-[10px] mt-6 mb-3">Brand Voice Grounding</div>
                            <div class="p-3 rounded-lg glass-subtle text-[11px] text-slate-400">
                                <div class="text-indigo-400 font-medium mb-1">Technical & Authoritative</div>
                                Precise, active voice, zero fluff, production-grade rationale.
                            </div>
                        </div>

                        <!-- Center Panel: Editor Surface -->
                        <div class="lg:col-span-6 p-6 flex flex-col justify-between bg-slate-900/30">
                            <div>
                                <!-- Editor Toolbar -->
                                <div class="flex flex-wrap items-center gap-2 pb-4 mb-4 border-b border-white/5 text-xs text-slate-400">
                                    <span class="font-bold text-slate-200">H1</span>
                                    <span class="font-bold text-slate-200">H2</span>
                                    <span class="font-bold text-slate-200">B</span>
                                    <span class="italic text-slate-200">I</span>
                                    <span class="text-slate-600">|</span>
                                    <span class="text-emerald-400">Words: 184</span>
                                    <span class="text-slate-600">|</span>
                                    <span class="text-cyan-400">SEO Score: 94%</span>
                                </div>

                                <!-- Dynamic Content Area -->
                                <div class="prose prose-invert prose-sm max-w-none text-slate-200" x-html="editorContent"></div>
                            </div>

                            <!-- Floating AI Context Toolbar -->
                            <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap items-center gap-2">
                                <span class="text-xs text-slate-400 mr-2">Quick AI Actions:</span>
                                <x-glass.button variant="glass" size="sm" x-on:click="runAiAction('generate')" x-bind:disabled="isGenerating">
                                    ✨ Expand Content
                                </x-glass.button>
                                <x-glass.button variant="glass" size="sm" x-on:click="runAiAction('rewrite')" x-bind:disabled="isGenerating">
                                    🔄 Polish Tone
                                </x-glass.button>
                                <x-glass.button variant="glass" size="sm" x-on:click="runAiAction('shorten')" x-bind:disabled="isGenerating">
                                    ⚡ Summarize
                                </x-glass.button>
                                <x-glass.button variant="glass" size="sm" x-on:click="runAiAction('seo')" x-bind:disabled="isGenerating">
                                    🎯 SEO Audit
                                </x-glass.button>
                            </div>
                        </div>

                        <!-- Right Panel: AI Generation Panel -->
                        <div class="lg:col-span-3 border-l border-white/5 p-4 bg-slate-950/60 flex flex-col justify-between text-xs">
                            <div>
                                <div class="font-semibold text-slate-300 uppercase tracking-wider text-[10px] mb-3">AI Assistant Panel</div>

                                <div class="mb-4">
                                    <label class="text-[11px] text-slate-400 mb-1 block">Model Router</label>
                                    <select x-model="selectedModel" class="w-full bg-slate-900 border border-white/10 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                                        <option>OmniRoute: DeepSeek-V3</option>
                                        <option>OmniRoute: Claude 3.7 Sonnet</option>
                                        <option>OmniRoute: OpenAI GPT-4o</option>
                                        <option>OmniRoute: Local Ollama (vLLM)</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="text-[11px] text-slate-400 mb-1 block">Creativity Temperature</label>
                                    <div class="flex items-center gap-2">
                                        <input type="range" min="0" max="1" step="0.1" value="0.7" class="w-full accent-indigo-500">
                                        <span class="text-[11px] font-mono text-indigo-300">0.7</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-lg glass-subtle border border-indigo-500/20 text-slate-300">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-semibold text-indigo-300 text-[11px]">Stream Status</span>
                                        <span class="w-2 h-2 rounded-full bg-emerald-400" x-bind:class="isGenerating ? 'animate-ping' : ''"></span>
                                    </div>
                                    <p class="text-[10px] text-slate-400" x-text="isGenerating ? 'Streaming tokens from OmniRoute proxy...' : 'Ready for contextual generation.'"></p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-white/5">
                                <x-glass.button variant="primary" size="sm" class="w-full" x-on:click="runAiAction('generate')">
                                    Run AI Generation
                                </x-glass.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Architecture Deep-Dive -->
        <section id="features" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="violet" class="mb-4">Domain Architecture</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Feature-First Architecture Protocols</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Every major system module lives in its own cohesive domain with dedicated Actions, DTOs, Policies, and Livewire components.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <x-glass.card variant="standard" class="hover:border-indigo-500/50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4 font-bold text-lg">
                            📄
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">Documents & Versioning</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Atomic snapshots on every edit and AI action. Instant time-travel rollback, delta comparisons, and ProseMirror JSON structure preservation.
                        </p>
                        <div class="text-[11px] font-mono text-indigo-300">app/Features/Documents/</div>
                    </x-glass.card>

                    <!-- Feature 2 -->
                    <x-glass.card variant="standard" class="hover:border-purple-500/50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 mb-4 font-bold text-lg">
                            ⚡
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">OmniRoute AI Gateway</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Decoupled proxy routing requests to local Ollama/vLLM instances or remote cloud providers with token quota reservation and pre-flight checks.
                        </p>
                        <div class="text-[11px] font-mono text-purple-300">app/Features/AI/</div>
                    </x-glass.card>

                    <!-- Feature 3 -->
                    <x-glass.card variant="standard" class="hover:border-cyan-500/50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-4 font-bold text-lg">
                            🎯
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">Live SEO Intelligence</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Real-time keyword density calculation, heading hierarchy auditing, readability indexing, and dynamic SERP snippet generation.
                        </p>
                        <div class="text-[11px] font-mono text-cyan-300">app/Features/SEO/</div>
                    </x-glass.card>

                    <!-- Feature 4 -->
                    <x-glass.card variant="standard" class="hover:border-emerald-500/50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4 font-bold text-lg">
                            🧠
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">Knowledge Base & RAG</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Chunked document vector embeddings, cosine similarity ranking, and untrusted knowledge sanitization against prompt injection vectors.
                        </p>
                        <div class="text-[11px] font-mono text-emerald-300">app/Features/KnowledgeBase/</div>
                    </x-glass.card>

                    <!-- Feature 5 -->
                    <x-glass.card variant="standard" class="hover:border-amber-500/50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4 font-bold text-lg">
                            🎭
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">Brand Voice Profiles</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Define custom personas, tone rules, vocabulary constraints, and target audience profiles injected seamlessly into generation prompts.
                        </p>
                        <div class="text-[11px] font-mono text-amber-300">app/Features/BrandVoice/</div>
                    </x-glass.card>

                    <!-- Feature 6 -->
                    <x-glass.card variant="standard" class="hover:border-pink-500/50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 mb-4 font-bold text-lg">
                            🚀
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">Shared-Hosting Optimized</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Engineered for standard Apache PHP 8.3+ environments with fallback database polling, queued workers, and zero Node.js daemon overhead.
                        </p>
                        <div class="text-[11px] font-mono text-pink-300">app/Core/Infrastructure/</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- 4-Tier Glassmorphism Design System -->
        <section id="glass-system" class="py-16 sm:py-24 border-t border-white/5 bg-slate-950/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="emerald" class="mb-4">UI Tokens</x-glass.badge>
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

        <!-- Templates Showcase Section -->
        <section id="templates" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="amber" class="mb-4">Content Blueprints</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Pre-Configured AI Workflows</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Deploy specialized prompts calibrated for search visibility, conversion copy, and technical documentation.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @php
                    $templates = [
                        ['icon' => '📝', 'name' => 'SEO Long-Form Article', 'category' => 'Blogging'],
                        ['icon' => '🚀', 'name' => 'Product Launch Copy', 'category' => 'Marketing'],
                        ['icon' => '📧', 'name' => 'Cold Outreach Sequence', 'category' => 'Sales'],
                        ['icon' => '📱', 'name' => 'Viral Social Thread', 'category' => 'Social'],
                        ['icon' => '💡', 'name' => 'Brainstorm & Outlines', 'category' => 'Ideation'],
                        ['icon' => '🔍', 'name' => 'Keyword & Meta Suite', 'category' => 'SEO'],
                        ['icon' => '📑', 'name' => 'Whitepaper Brief', 'category' => 'Enterprise'],
                        ['icon' => '💻', 'name' => 'API Docs & Changelog', 'category' => 'Engineering'],
                    ];
                    @endphp

                    @foreach($templates as $tpl)
                    <x-glass.card variant="standard" class="p-4 hover:border-indigo-500/40 hover:-translate-y-1 transition-all duration-200 cursor-pointer" x-data x-on:click="$dispatch('open-modal', 'demo-modal')">
                        <div class="text-2xl mb-2">{{ $tpl['icon'] }}</div>
                        <div class="text-sm font-semibold text-white truncate">{{ $tpl['name'] }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $tpl['category'] }}</div>
                    </x-glass.card>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Pricing / Quotas Section -->
        <section id="pricing" class="py-16 sm:py-24 border-t border-white/5 bg-slate-950/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <x-glass.badge variant="cyan" class="mb-4">Scalable Quotas</x-glass.badge>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Predictable Usage Accounting</h2>
                    <p class="text-slate-400 mt-3 text-sm sm:text-base">Full control over token consumption with support for bring-your-own-key (BYOK) OmniRoute self-hosting.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Starter -->
                    <x-glass.card variant="standard" class="p-8 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Starter</h3>
                            <p class="text-xs text-slate-400 mt-1 mb-6">For individual writers and creators.</p>
                            <div class="text-3xl font-black text-white mb-6">$0 <span class="text-xs font-normal text-slate-400">/ forever</span></div>
                            <ul class="space-y-3 text-xs text-slate-300 mb-8">
                                <li class="flex items-center gap-2">✓ 15,000 Monthly AI Words</li>
                                <li class="flex items-center gap-2">✓ Tiptap ProseMirror Editor</li>
                                <li class="flex items-center gap-2">✓ 5 Document Snapshots / Doc</li>
                                <li class="flex items-center gap-2 text-slate-500">✗ Custom Brand Voices</li>
                            </ul>
                        </div>
                        <x-glass.button variant="glass" size="md" class="w-full">Start Free</x-glass.button>
                    </x-glass.card>

                    <!-- Pro -->
                    <x-glass.card variant="premium" glow="violet" class="p-8 flex flex-col justify-between relative">
                        <div class="absolute -top-3 right-6">
                            <x-glass.badge variant="violet" class="text-[10px]">MOST POPULAR</x-glass.badge>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Professional</h3>
                            <p class="text-xs text-slate-300 mt-1 mb-6">For content teams & growth agencies.</p>
                            <div class="text-3xl font-black text-white mb-6">$29 <span class="text-xs font-normal text-slate-400">/ month</span></div>
                            <ul class="space-y-3 text-xs text-slate-200 mb-8">
                                <li class="flex items-center gap-2">✓ 500,000 Monthly AI Words</li>
                                <li class="flex items-center gap-2">✓ Full OmniRoute Multi-Model Access</li>
                                <li class="flex items-center gap-2">✓ Unlimited Document Versions</li>
                                <li class="flex items-center gap-2">✓ 10 Custom Brand Voice Profiles</li>
                                <li class="flex items-center gap-2">✓ RAG Knowledge Base Grounding</li>
                            </ul>
                        </div>
                        <x-glass.button variant="primary" size="md" class="w-full shadow-lg shadow-indigo-500/30">Get Pro Access</x-glass.button>
                    </x-glass.card>

                    <!-- Enterprise -->
                    <x-glass.card variant="standard" class="p-8 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-white">Self-Hosted / BYOK</h3>
                            <p class="text-xs text-slate-400 mt-1 mb-6">For private cloud & dedicated VPS.</p>
                            <div class="text-3xl font-black text-white mb-6">Custom <span class="text-xs font-normal text-slate-400">/ license</span></div>
                            <ul class="space-y-3 text-xs text-slate-300 mb-8">
                                <li class="flex items-center gap-2">✓ Unlimited Self-Hosted Tokens</li>
                                <li class="flex items-center gap-2">✓ Local Ollama / vLLM Support</li>
                                <li class="flex items-center gap-2">✓ Shared-Hosting Compatibility</li>
                                <li class="flex items-center gap-2">✓ Dedicated API v1 Endpoints</li>
                            </ul>
                        </div>
                        <x-glass.button variant="glass" size="md" class="w-full">Deploy Self-Hosted</x-glass.button>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- Final CTA Banner -->
        <section class="py-16 sm:py-24 relative overflow-hidden">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <x-glass.card variant="premium" glow="purple" class="p-8 sm:p-12 text-center relative overflow-hidden">
                    <div class="max-w-2xl mx-auto">
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
                            Ready to Upgrade Your AI Content Operations?
                        </h2>
                        <p class="text-sm sm:text-base text-slate-300 mb-8">
                            Experience the speed of Livewire 4, the elegance of 4-tier glassmorphism, and the flexibility of OmniRoute AI routing today.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <x-glass.button variant="primary" size="lg" class="w-full sm:w-auto px-8" x-data x-on:click="$dispatch('open-modal', 'demo-modal')">
                                Launch HelpOfAi Studio
                            </x-glass.button>
                        </div>
                    </div>
                </x-glass.card>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-slate-950/80 backdrop-blur-md py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12 text-xs">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center text-[10px] font-black text-white">H</div>
                        <span class="font-bold text-white text-sm">HelpOfAi Studio</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed">Modern SPA-like AI content architecture powered by Laravel 13 and OmniRoute.</p>
                </div>

                <div>
                    <h4 class="font-semibold text-white mb-3 uppercase tracking-wider text-[10px]">Architecture</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#features" class="hover:text-white transition-colors">Feature-First Domain</a></li>
                        <li><a href="#omniroute" class="hover:text-white transition-colors">OmniRoute Gateway</a></li>
                        <li><a href="#glass-system" class="hover:text-white transition-colors">4-Tier Glass Design</a></li>
                        <li><a href="#simulator" class="hover:text-white transition-colors">Tiptap ProseMirror</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-white mb-3 uppercase tracking-wider text-[10px]">Features</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="#templates" class="hover:text-white transition-colors">AI Templates</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">Brand Voice Profiles</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">Knowledge RAG</a></li>
                        <li><a href="#features" class="hover:text-white transition-colors">SEO Intelligence</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-white mb-3 uppercase tracking-wider text-[10px]">Project</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><span class="text-emerald-400 font-mono">Status: Phase 1 Active</span></li>
                        <li><span class="text-slate-400 font-mono">PHP 8.3+ / MySQL 8+</span></li>
                        <li><span class="text-slate-400 font-mono">Livewire 4 + Alpine.js</span></li>
                        <li><span class="text-slate-400 font-mono">Target: HOA-Studio</span></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <div>
                    HelpOfAi Studio (HOA-Studio) &copy; {{ date('Y') }} &mdash; Engineered for production reliability.
                </div>
                <div class="flex items-center gap-6">
                    <a href="#features" class="hover:text-slate-400 transition-colors">Architecture Plan</a>
                    <a href="#pricing" class="hover:text-slate-400 transition-colors">Quotas</a>
                    <a href="#" class="hover:text-slate-400 transition-colors">Security</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Live Demo Modal -->
    <x-glass.modal name="demo-modal" maxWidth="2xl">
        <div class="p-6 sm:p-8" x-data="{
            prompt: 'Write an authoritative introduction on why decoupled AI gateways protect SaaS applications from LLM provider outages.',
            model: 'OmniRoute: Claude 3.7 Sonnet',
            isStreaming: false,
            streamOutput: '',
            simulateGeneration() {
                this.isStreaming = true;
                this.streamOutput = '';
                const text = 'In an era where AI uptime directly dictates SaaS availability, coupling core business logic directly to a single LLM provider introduces unacceptable single-point-of-failure risks. Decoupled AI gateways like OmniRoute solve this vulnerability through dynamic request routing, automatic multi-provider failover, and token budgeting—guaranteeing 99.99% generation reliability regardless of external cloud disruptions.';
                let idx = 0;
                const timer = setInterval(() => {
                    if (idx < text.length) {
                        this.streamOutput += text[idx];
                        idx++;
                    } else {
                        clearInterval(timer);
                        this.isStreaming = false;
                    }
                }, 15);
            }
        }">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-sm">✨</div>
                    <div>
                        <h3 class="text-base font-bold text-white">Live OmniRoute Generation Demo</h3>
                        <p class="text-xs text-slate-400">Experience real-time AI token streaming</p>
                    </div>
                </div>
                <button x-on:click="$dispatch('close')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5 transition-colors cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="text-xs font-semibold text-slate-300 mb-1.5 block">AI Generation Prompt</label>
                    <textarea x-model="prompt" rows="3" class="w-full rounded-xl bg-slate-900/80 border border-white/15 px-3.5 py-2.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-300 mb-1.5 block">Routing Engine</label>
                        <select x-model="model" class="w-full rounded-xl bg-slate-900/80 border border-white/15 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                            <option>OmniRoute: Claude 3.7 Sonnet</option>
                            <option>OmniRoute: DeepSeek-V3</option>
                            <option>OmniRoute: OpenAI GPT-4o</option>
                            <option>OmniRoute: Ollama (Local)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-300 mb-1.5 block">Brand Voice</label>
                        <div class="w-full rounded-xl bg-slate-900/80 border border-white/15 px-3 py-2 text-xs text-indigo-300 flex items-center justify-between">
                            <span>Authoritative Tech</span>
                            <span class="text-[10px] bg-indigo-500/20 px-1.5 py-0.5 rounded">Active</span>
                        </div>
                    </div>
                </div>

                <!-- Stream Output Box -->
                <div>
                    <label class="text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Streaming Output</span>
                        <span class="text-[10px] text-emerald-400 font-mono" x-show="streamOutput.length > 0" x-text="'Generated ' + streamOutput.split(' ').length + ' words'"></span>
                    </label>
                    <div class="min-h-[110px] p-3.5 rounded-xl bg-slate-950/80 border border-white/10 text-xs text-slate-200 leading-relaxed font-sans" x-bind:class="isStreaming ? 'border-indigo-500/40 shadow-[0_0_15px_rgba(99,102,241,0.15)]' : ''">
                        <p x-text="streamOutput || 'Click Simulate Stream below to test real-time token hydration...'"></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                <x-glass.button variant="secondary" size="sm" x-on:click="$dispatch('close')">Close</x-glass.button>
                <x-glass.button variant="primary" size="sm" x-on:click="simulateGeneration()" x-bind:disabled="isStreaming">
                    <span x-text="isStreaming ? 'Streaming Tokens...' : 'Simulate Stream'"></span>
                </x-glass.button>
            </div>
        </div>
    </x-glass.modal>
</x-layouts.app>
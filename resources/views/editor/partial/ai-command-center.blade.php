{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - AI Command Center Partial
|--------------------------------------------------------------------------
|
| Features:
| 1. AI Router & Provider Gateway
| 2. Real-Time Token Telemetry (Send, Received, Latency, tok/s)
| 3. 15-Stage Enterprise Production Pipeline Matrix
| 4. Contextual Grounding (Canvas, Voice, RAG, Web)
| 5. Direct Semantic Pipelines
|
*/
--}}

<div class="space-y-4 h-full flex flex-col">
    <div class="editor-column hoa-custom-scrollbar">
        <!-- Main Header -->
        <div class="flex items-center justify-between pb-2 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 animate-pulse"></span>
                <h2 class="text-xs uppercase font-extrabold text-white tracking-wider">AI Command Center</h2>
            </div>
            <span class="text-[10px] font-mono text-indigo-400 font-bold px-2 py-0.5 rounded-full bg-indigo-600/20 border border-indigo-500/30">Live Canvas</span>
        </div>

        <!-- 1. AI ROUTER & PROVIDER GATEWAY SECTION -->
        <div class="space-y-3 p-4 rounded-2xl bg-slate-950 border border-white/10 shadow-inner">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-white flex items-center gap-1.5">
                    <span class="text-indigo-400">⚡</span>
                    <span>AI Gateway Configuration</span>
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    ONLINE
                </span>
            </div>

            <div class="space-y-3">
                <!-- 1st Field: AI Provider Select (OmniRoute, OpenAI, DeepSeek, Anthropic, etc.) -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">1. AI Provider (Configured & Active)</label>
                        <span class="text-[9px] font-mono text-indigo-400" x-text="availableProviders.length + ' Available'"></span>
                    </div>
                    <select 
                        x-model="selectedProvider" 
                        x-on:change="fetchModelsForProvider($event.target.value)"
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer"
                    >
                        <option value="">⚡ Select Provider...</option>
                        <template x-for="provider in availableProviders" :key="provider.id || provider.slug">
                            <option :value="provider.slug" x-text="provider.name + (provider.is_local ? ' (Local)' : ' (Cloud)')"></option>
                        </template>
                    </select>
                </div>

                <!-- 2nd Field: AI Model Select (Filtered for that specific Provider only) -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">2. Provider Models</label>
                        <span class="text-[9px] font-mono text-emerald-400" x-text="availableModels.length + ' Models'"></span>
                    </div>
                    <select 
                        x-model="aiModel" 
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer"
                    >
                        <option value="auto">⚡ Auto (OmniRoute Smart Router)</option>
                        <template x-for="model in availableModels" :key="model.id">
                            <option :value="model.id" x-text="model.name"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. USER BRAIN & VECTOR MEMORY RAG INDICATOR -->
        <div class="p-2.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner space-y-1.5 font-mono">
            <div class="flex items-center justify-between text-[11px] font-bold text-white">
                <span class="flex items-center gap-1.5 text-purple-300">
                    <span>🧠</span> <span>User Brain & Vector Memory</span>
                </span>
                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-purple-950 text-purple-300 border border-purple-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span>
                    HYBRID RAG
                </span>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-400">
                <span>Multi-tier Vector Cache</span>
                <a href="{{ route('knowledge-base.index') }}" target="_blank" class="text-indigo-400 hover:text-indigo-200 underline flex items-center gap-0.5">
                    <span>Configure Brain</span>
                    <span class="text-[8px]">↗</span>
                </a>
            </div>
        </div>

        <!-- 3. MULTI-AGENT SWARM (full-content-main-agent) MONITOR -->
        <div id="full-content-main-agent" class="full-content-main-agent p-3 rounded-2xl bg-slate-900/90 border border-indigo-500/30 shadow-inner space-y-2.5 font-mono" x-data="{ showSwarmSteps: false }">
            <div class="flex items-center justify-between text-[11px] font-bold text-white">
                <span class="flex items-center gap-1.5 text-indigo-300">
                    <span>🤖</span> 
                    <span>Multi-Agent Swarm</span>
                    <span class="text-[9px] font-mono px-1.5 py-0.2 rounded bg-purple-950/80 text-purple-300 border border-purple-500/40 font-bold" title="Full Content Main Agent Engine">full-content-main-agent</span>
                </span>
                <button 
                    type="button" 
                    x-on:click="showSwarmSteps = !showSwarmSteps" 
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-500/30 hover:bg-indigo-900 transition-colors cursor-pointer"
                >
                    <span x-text="showSwarmSteps ? '▼ Hide Steps' : '⚙️ Custom Steps'"></span>
                </button>
            </div>

            <!-- Mini Swarm Chain -->
            <div class="grid grid-cols-5 gap-1 text-center">
                <div class="p-1 rounded border transition-colors" :class="swarmSteps.researcher ? 'bg-indigo-950/80 border-indigo-500/50 text-indigo-300' : 'bg-slate-950/40 border-white/5 text-slate-600'" title="Agent 1: Search Intent & RAG Researcher">
                    <span class="text-xs block">🎯</span>
                    <span class="text-[8px] block truncate">Res</span>
                </div>
                <div class="p-1 rounded border transition-colors" :class="swarmSteps.outliner ? 'bg-indigo-950/80 border-indigo-500/50 text-purple-300' : 'bg-slate-950/40 border-white/5 text-slate-600'" title="Agent 2: Outline & Title Architect">
                    <span class="text-xs block">📑</span>
                    <span class="text-[8px] block truncate">Arch</span>
                </div>
                <div class="p-1 rounded border transition-colors" :class="swarmSteps.draftsman ? 'bg-indigo-950/80 border-indigo-500/50 text-emerald-300' : 'bg-slate-950/40 border-white/5 text-slate-600'" title="Agent 3: Deep Section Draftsman">
                    <span class="text-xs block">✍️</span>
                    <span class="text-[8px] block truncate">Draft</span>
                </div>
                <div class="p-1 rounded border transition-colors" :class="swarmSteps.rich_media ? 'bg-indigo-950/80 border-indigo-500/50 text-teal-300' : 'bg-slate-950/40 border-white/5 text-slate-600'" title="Agent 4: Comparison Table & Media">
                    <span class="text-xs block">▦</span>
                    <span class="text-[8px] block truncate">Media</span>
                </div>
                <div class="p-1 rounded border transition-colors" :class="swarmSteps.seo_meta ? 'bg-indigo-950/80 border-indigo-500/50 text-violet-300' : 'bg-slate-950/40 border-white/5 text-slate-600'" title="Agent 5: SEO Meta & Quality Audit">
                    <span class="text-xs block">🚀</span>
                    <span class="text-[8px] block truncate">Meta</span>
                </div>
            </div>

            <!-- Custom Swarm Step Checkboxes (User Options) -->
            <div x-show="showSwarmSteps" class="space-y-1 pt-1.5 border-t border-white/10 text-xs font-mono select-none">
                <div class="text-[9px] uppercase font-bold text-slate-400 tracking-wider mb-1">Select Active Swarm Steps:</div>
                <label class="flex items-center justify-between p-1 rounded hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="swarmSteps.researcher" class="rounded bg-slate-900 border-white/20 text-indigo-600">
                        <span class="text-[10.5px]">Step 1: Search Intent & SEO Research</span>
                    </div>
                </label>
                <label class="flex items-center justify-between p-1 rounded hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="swarmSteps.outliner" class="rounded bg-slate-900 border-white/20 text-indigo-600">
                        <span class="text-[10.5px]">Step 2: Title & Heading Architecture</span>
                    </div>
                </label>
                <label class="flex items-center justify-between p-1 rounded hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="swarmSteps.draftsman" class="rounded bg-slate-900 border-white/20 text-indigo-600">
                        <span class="text-[10.5px]">Step 3: Full Article Drafting</span>
                    </div>
                </label>
                <label class="flex items-center justify-between p-1 rounded hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="swarmSteps.rich_media" class="rounded bg-slate-900 border-white/20 text-indigo-600">
                        <span class="text-[10.5px]">Step 4: Comparison Table & Media</span>
                    </div>
                </label>
                <label class="flex items-center justify-between p-1 rounded hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="swarmSteps.seo_meta" class="rounded bg-slate-900 border-white/20 text-indigo-600">
                        <span class="text-[10.5px]">Step 5: Meta Description & Quality Audit</span>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-between text-[9.5px] text-slate-400 pt-0.5 border-t border-white/5">
                <span>Active Steps: <strong class="text-white" x-text="Object.values(swarmSteps).filter(Boolean).length + '/5'">5/5</strong></span>
                <span class="text-emerald-400">Zero-Loss Handshake</span>
            </div>
        </div>

        <!-- 3. REAL-TIME AI TOKENS & LATENCY TELEMETRY -->
        <div class="p-3 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner space-y-2 font-mono">
            <div class="flex items-center justify-between text-[11px] font-bold text-white">
                <span class="flex items-center gap-1.5 text-violet-300">
                    <span>📊</span> <span>AI Tokens Telemetry</span>
                </span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-indigo-400" x-text="streamSpeedTokSec > 0 ? (streamSpeedTokSec + ' tok/s') : 'Idle'"></span>
                    <button type="button" @click="showFloatingTelemetry = !showFloatingTelemetry" class="text-indigo-400 hover:text-white p-1 rounded hover:bg-white/10 transition-colors" title="Pop out Telemetry Monitor" aria-label="Pop out Telemetry Monitor">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 rounded-xl bg-slate-950/80 border border-white/5 space-y-0.5">
                    <span class="text-[9.5px] uppercase font-bold text-slate-400 block">Sent (Prompt)</span>
                    <div class="text-sm font-black text-cyan-300 flex items-center justify-between">
                        <span x-text="sendTokens">0</span>
                        <span class="text-[9px] text-slate-500">tok</span>
                    </div>
                </div>

                <div class="p-2 rounded-xl bg-slate-950/80 border border-white/5 space-y-0.5">
                    <span class="text-[9.5px] uppercase font-bold text-slate-400 block">Received (Completion)</span>
                    <div class="text-sm font-black text-emerald-400 flex items-center justify-between">
                        <span x-text="receivedTokens">0</span>
                        <span class="text-[9px] text-slate-500">tok</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5 px-0.5">
                <span>Total Session: <strong class="text-white" x-text="totalTokens">0</strong> tokens</span>
                <span>Latency: <strong class="text-emerald-400" x-text="streamLatencyMs + 'ms'">12ms</strong></span>
            </div>
        </div>

        <!-- 3. ADVANCED ASK AI & 15-STAGE CONTENT PIPELINE -->
        <div class="space-y-3 p-3.5 rounded-2xl bg-slate-900/90 border border-indigo-500/30 shadow-inner">
            <div class="flex items-center justify-between">
                <label class="text-[11px] font-bold text-white flex items-center gap-1.5">
                    <span class="text-indigo-400">✦</span>
                    <span>Ask AI (Writes Live into Editor)</span>
                </label>
                <span class="text-[9px] font-mono text-slate-500">Ctrl+K</span>
            </div>

            <!-- Topic / Prompt Textarea -->
            <textarea 
                id="ai-command-prompt"
                x-model="aiPrompt"
                rows="3"
                placeholder="Enter topic or prompt: e.g. Complete review on DeepSeek V4 Flash with benchmarks, architecture, and live coding examples..."
                class="w-full bg-slate-950 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none font-sans leading-relaxed shadow-inner"
            ></textarea>

            <!-- 15-Stage Pipeline Interactive Selector Accordion -->
            <div class="space-y-2 pt-1" x-data="{ showPipelineDetails: false }">
                <div class="flex items-center justify-between pb-1 border-b border-white/5">
                    <button 
                        type="button" 
                        x-on:click="showPipelineDetails = !showPipelineDetails"
                        class="text-[11px] font-bold text-indigo-300 hover:text-white flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <span class="text-[9px]" x-text="showPipelineDetails ? '▼' : '▶'"></span>
                        <span>15-Stage Production Pipeline</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-indigo-950 text-indigo-400 border border-indigo-500/30" x-text="getSelectedStagesCount() + '/15'"></span>
                    </button>

                    <!-- Pipeline Quick Presets -->
                    <div class="flex items-center gap-1 text-[9.5px] font-mono">
                        <button type="button" x-on:click="setPipelinePreset('all')" class="text-indigo-400 hover:text-indigo-200">All</button>
                        <span class="text-slate-600">&bull;</span>
                        <button type="button" x-on:click="setPipelinePreset('seo')" class="text-emerald-400 hover:text-emerald-200">SEO</button>
                        <span class="text-slate-600">&bull;</span>
                        <button type="button" x-on:click="setPipelinePreset('quick')" class="text-amber-400 hover:text-amber-200">Quick</button>
                        <span class="text-slate-600">&bull;</span>
                        <button type="button" x-on:click="setPipelinePreset('clear')" class="text-slate-400 hover:text-red-400">None</button>
                    </div>
                </div>

                <!-- Full 15-Stage Checkbox Grid -->
                <div x-show="showPipelineDetails" class="space-y-1 max-h-56 overflow-y-auto pr-1 text-xs font-mono select-none scrollbar-thin scrollbar-thumb-white/10 pt-1">
                    <template x-for="(stage, key) in pipelineStages" :key="key">
                        <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-white/5 cursor-pointer transition-colors" :class="stage.enabled ? 'bg-indigo-950/40 text-white' : 'text-slate-400'">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="stage.enabled" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                                <span class="text-[11px]" x-text="stage.icon + ' ' + stage.label"></span>
                            </div>
                            <span class="text-[9px] text-slate-500 font-mono" x-text="stage.category"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Action Buttons: Write Live & Multi-Agent Swarm -->
            <div class="space-y-1.5 pt-1">
                <!-- 5. Animated Bars Multi-Agent Swarm Button -->
                <button 
                    type="button" 
                    x-on:click="triggerAiTransform('multi_agent_pipeline', aiPrompt, 'document')"
                    :disabled="isTransforming"
                    class="w-full py-2.5 px-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/30 flex items-center justify-center gap-2 transition-all disabled:opacity-50 cursor-pointer select-none"
                >
                    <span class="bars text-white" x-show="isTransforming" style="display: none;">
                        <span></span><span></span><span></span><span></span>
                    </span>
                    <span x-text="isTransforming ? 'Streaming...' : 'AI Transform'"></span>
                </button>

                <button 
                    type="button" 
                    x-on:click="triggerAiTransform('custom', aiPrompt)"
                    :disabled="isTransforming"
                    class="w-full py-2 px-3 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-white/10 flex items-center justify-center gap-1.5 transition-all disabled:opacity-50 cursor-pointer"
                >
                    <span x-show="!isTransforming">✍️ Direct Draft Stream</span>
                    <span x-show="isTransforming">Streaming Tokens...</span>
                </button>
            </div>
        </div>

        <!-- 4. CONTEXTUAL GROUNDING SECTION -->
        <div class="space-y-2 p-3 rounded-2xl bg-slate-900/90 border border-white/10 text-xs text-slate-300">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Contextual Grounding</span>
            <div class="space-y-1.5">
                <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="aiContext.currentDoc" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                        <span class="text-[11px]">Current Document Canvas</span>
                    </div>
                    <span class="text-[9.5px] font-mono text-emerald-400 font-bold">Active</span>
                </label>
                <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="aiContext.brandVoice" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                        <span class="text-[11px]">Brand Voice Profile</span>
                    </div>
                    <span class="text-[9.5px] font-mono text-indigo-400">Tone Sync</span>
                </label>
                <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="aiContext.knowledgeBase" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                        <span class="text-[11px]">Knowledge Base RAG</span>
                    </div>
                    <span class="text-[9.5px] font-mono text-purple-400">Vectors</span>
                </label>
                <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-white/5 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="aiContext.webResearch" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                        <span class="text-[11px]">Live Web Grounding</span>
                    </div>
                    <span class="text-[9.5px] font-mono text-cyan-400">Real-Time</span>
                </label>
            </div>
        </div>

        <!-- 5. DIRECT TRANSFORMATION PIPELINES -->
        <div class="space-y-2 pt-2 border-t border-white/10">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Semantic Pipelines</span>
            <div class="grid grid-cols-2 gap-1.5 text-xs font-medium">
                <button type="button" x-on:click="triggerAiTransform('generate_outline')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                    <span class="text-cyan-400">📑</span> Outline
                </button>
                <button type="button" x-on:click="triggerAiTransform('quick_answer')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                    <span class="text-amber-400">⚡</span> Quick Answer
                </button>
                <button type="button" x-on:click="triggerAiTransform('generate_faq')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                    <span class="text-indigo-400">❓</span> FAQ Schema
                </button>
                <button type="button" x-on:click="triggerAiTransform('comparison_table')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                    <span class="text-pink-400">📊</span> Table Block
                </button>
                <button type="button" x-on:click="triggerAiTransform('rewrite')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                    <span class="text-cyan-400">↻</span> Rewrite
                </button>
                <button type="button" x-on:click="triggerAiTransform('expand')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                    <span class="text-violet-400">+</span> Expand
                </button>
            </div>
        </div>
    </div>
</div>

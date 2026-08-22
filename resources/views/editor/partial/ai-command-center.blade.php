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

<div 
    x-show="showLeftPanel" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    class="space-y-4 lg:sticky lg:top-4"
>
    <x-glass.card variant="standard" class="p-4 space-y-4 border border-white/10 shadow-xl">
        <!-- Main Header -->
        <div class="flex items-center justify-between pb-2 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 animate-pulse"></span>
                <h2 class="text-xs uppercase font-extrabold text-white tracking-wider">AI Command Center</h2>
            </div>
            <span class="text-[10px] font-mono text-indigo-400 font-bold px-2 py-0.5 rounded-full bg-indigo-600/20 border border-indigo-500/30">Live Canvas</span>
        </div>

        <!-- 1. AI ROUTER & PROVIDER GATEWAY SECTION -->
        <div class="space-y-2 p-3 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-white flex items-center gap-1.5">
                    <span class="text-indigo-400">⚡</span>
                    <span>AI Router & Gateway</span>
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-mono font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    ONLINE (12ms)
                </span>
            </div>

            <div class="space-y-1.5">
                <select 
                    x-model="aiModel" 
                    class="w-full bg-slate-950 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer"
                >
                    <option value="Auto (OmniRoute)">⚡ Auto (OmniRoute Smart Router)</option>
                    @if(!empty($availableAiModels) && $availableAiModels->count() > 0)
                        <optgroup label="Available AI Models & Combos">
                            @foreach($availableAiModels as $model)
                                <option value="{{ $model->model_id }}">
                                    {{ $model->name }} ({{ strtoupper($model->provider_family ?? 'Cloud') }})
                                </option>
                            @endforeach
                        </optgroup>
                    @else
                        <optgroup label="Standard Gateway Models">
                            <option value="Claude 3.7 Sonnet">Claude 3.7 Sonnet (Anthropic)</option>
                            <option value="GPT-4o">GPT-4o (OpenAI)</option>
                            <option value="Gemini 2.0 Flash">Gemini 2.0 Flash (Google Deepmind)</option>
                            <option value="DeepSeek-V3">DeepSeek-V3 (Cloud/Ollama)</option>
                        </optgroup>
                    @endif
                </select>

                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 pt-0.5 px-0.5">
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                        <span>Multi-Modal Fallback: <strong class="text-emerald-400">ON</strong></span>
                    </span>
                    <span class="text-slate-500">Auto-Cascade</span>
                </div>
            </div>
        </div>

        <!-- 2. REAL-TIME AI TOKENS & LATENCY TELEMETRY -->
        <div class="p-3 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner space-y-2 font-mono">
            <div class="flex items-center justify-between text-[11px] font-bold text-white">
                <span class="flex items-center gap-1.5 text-violet-300">
                    <span>📊</span> <span>AI Tokens Telemetry</span>
                </span>
                <span class="text-[10px] text-indigo-400" x-text="streamSpeedTokSec > 0 ? (streamSpeedTokSec + ' tok/s') : 'Idle'"></span>
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

            <!-- Write Live Button -->
            <button 
                type="button" 
                x-on:click="triggerAiTransform('custom', aiPrompt)"
                :disabled="isTransforming"
                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all disabled:opacity-50 cursor-pointer"
            >
                <span x-show="!isTransforming">✍️ Write Live to Editor</span>
                <span x-show="isTransforming" class="animate-spin text-sm">⟳</span>
                <span x-show="isTransforming">Streaming Tokens to Canvas...</span>
            </button>
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
    </x-glass.card>
</div>

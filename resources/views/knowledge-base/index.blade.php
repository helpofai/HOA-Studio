{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Brain & Vector Memory System
|--------------------------------------------------------------------------
|
| Features:
| 1. High-Performance Knowledge Vectorization & Second Brain
| 2. Two-Tier Vector Cache Telemetry (L1 Memory + L2 DB)
| 3. Hybrid Semantic Search Playground (Dense Vector Cosine + BM25 RRF)
| 4. User Brain Collections (Brand Voice, Specs, Competitor, FAQ, Docs)
| 5. 1-Click Active/Inactive Toggle & Re-indexing Engine
|
*/
--}}

<div class="space-y-8 pb-12">
    <!-- Main Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>🧠 Knowledge Base &amp; RAG Pipeline</span>
                <x-glass.badge variant="violet">{{ $sources->count() }} Knowledge Sources</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Vectorize brand guidelines, product specs, and documentation into semantic embeddings for grounded, hallucination-free AI generation.
            </p>
        </div>

        <button 
            type="button" 
            wire:click="openIngestModal"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer flex items-center gap-2"
        >
            <span>+ Ingest Knowledge Source</span>
        </button>
    </div>

    <!-- Status Alert -->
    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-lg">
            <span class="flex items-center gap-2">
                <span>✦</span>
                <span>{{ session('status') }}</span>
            </span>
            <button type="button" wire:click="$refresh" class="text-emerald-400 hover:text-white cursor-pointer">✕</button>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 1. TWO-TIER VECTOR CACHE TELEMETRY & STORAGE METRICS                      -->
    <!-- ========================================================================= -->
    <x-glass.card variant="standard" class="p-5 border border-white/10 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="text-indigo-400 text-base">⚡</span>
                <h3 class="text-sm font-bold text-white tracking-tight">Two-Tier Vector Cache Telemetry (L1 Memory + L2 DB)</h3>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
                    <span>TTL:</span>
                    <select 
                        wire:model="cacheTtlDays" 
                        wire:change="updateCacheTtl"
                        class="bg-slate-900 border border-white/15 rounded-lg px-2.5 py-1 text-xs text-white focus:outline-none focus:border-indigo-500"
                    >
                        <option value="1">1 Day</option>
                        <option value="7">7 Days (Standard)</option>
                        <option value="30">30 Days (Extended)</option>
                        <option value="365">1 Year (Permanent)</option>
                    </select>
                </div>

                <button 
                    type="button" 
                    wire:click="purgeCache"
                    wire:confirm="Are you sure you want to purge the vector cache buffer?"
                    class="px-2.5 py-1 rounded-lg bg-red-950/40 hover:bg-red-900/60 text-red-300 border border-red-500/30 text-[11px] font-mono transition-colors cursor-pointer"
                    title="Purge cached vector embeddings"
                >
                    🗑️ Purge Cache
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-mono">
            <div class="p-3 rounded-xl bg-slate-900/70 border border-white/5 space-y-1">
                <span class="text-[11px] text-slate-400">Cached Vectors</span>
                <p class="text-lg font-extrabold text-white">{{ number_format($cacheStats['total_cached_vectors'] ?? 0) }}</p>
                <span class="text-[10px] text-slate-500">Dense Embeddings</span>
            </div>

            <div class="p-3 rounded-xl bg-slate-900/70 border border-white/5 space-y-1">
                <span class="text-[11px] text-slate-400">Cache Hit Ratio</span>
                <p class="text-lg font-extrabold text-emerald-400">{{ $cacheStats['cache_hit_ratio'] ?? '100%' }}</p>
                <span class="text-[10px] text-slate-500">{{ number_format($cacheStats['total_cache_hits'] ?? 0) }} Hits recorded</span>
            </div>

            <div class="p-3 rounded-xl bg-slate-900/70 border border-white/5 space-y-1">
                <span class="text-[11px] text-slate-400">Tokens Saved</span>
                <p class="text-lg font-extrabold text-indigo-300">{{ number_format($cacheStats['tokens_saved'] ?? 0) }}</p>
                <span class="text-[10px] text-slate-500">API bypass tokens</span>
            </div>

            <div class="p-3 rounded-xl bg-slate-900/70 border border-white/5 space-y-1">
                <span class="text-[11px] text-slate-400">Est. API Savings</span>
                <p class="text-lg font-extrabold text-cyan-400">${{ number_format($cacheStats['estimated_cost_saved_usd'] ?? 0, 4) }}</p>
                <span class="text-[10px] text-slate-500">Cost reduction</span>
            </div>
        </div>
    </x-glass.card>

    <!-- ========================================================================= -->
    <!-- 2. HYBRID SEMANTIC VECTOR SEARCH TESTER & RAG PLAYGROUND                  -->
    <!-- ========================================================================= -->
    <x-glass.card variant="elevated" class="p-6 space-y-4 border border-indigo-500/30 shadow-2xl relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h3 class="text-sm font-bold text-white tracking-tight">Hybrid Semantic Vector Search & RAG Tester</h3>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono">
                <span class="text-slate-400">Category Filter:</span>
                <select 
                    wire:model="searchCategory"
                    class="bg-slate-900 border border-white/15 rounded-lg px-2.5 py-1 text-xs text-white focus:outline-none focus:border-indigo-500"
                >
                    <option value="all">All Categories</option>
                    <option value="brand_voice">Brand Voice & Guidelines</option>
                    <option value="product_specs">Product Catalog & Specs</option>
                    <option value="competitor_research">Competitor Intel</option>
                    <option value="faq">FAQs & Answers</option>
                    <option value="general_docs">General Documentation</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input 
                type="text" 
                wire:model="searchQuery" 
                wire:keydown.enter="performSemanticSearch"
                placeholder="Ask or query your Brain (e.g. 'What are our primary core values?', 'Pricing tiers and refund terms')..." 
                class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono"
            />
            <button 
                type="button" 
                wire:click="performSemanticSearch"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2 shrink-0 shadow-lg shadow-violet-600/30"
            >
                <span wire:loading.remove wire:target="performSemanticSearch">🔍 Query Brain</span>
                <span wire:loading wire:target="performSemanticSearch" class="flex items-center gap-1">
                    <span class="w-3 h-3 border border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>Searching...</span>
                </span>
            </button>
        </div>

        <!-- Retrieved Chunks Display -->
        @if(!empty($searchResults))
            <div class="pt-4 border-t border-white/10 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-slate-400">
                    <span>Retrieved Top {{ count($searchResults) }} Hybrid Semantic Chunks:</span>
                    <span class="text-indigo-400">Dense Vector Cosine (70%) + BM25 Keyword (30%)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($searchResults as $idx => $chunk)
                        @php
                            $pct = round(($chunk['score'] ?? 0.8) * 100);
                            $cat = $chunk['category'] ?? 'general_docs';
                        @endphp
                        <div class="p-3.5 rounded-xl bg-slate-900/90 border border-white/10 space-y-2 text-xs font-mono">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white truncate max-w-[200px]">{{ $chunk['source_title'] }}</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold uppercase {{ $cat === 'brand_voice' ? 'bg-purple-950 text-purple-300 border border-purple-500/30' : ($cat === 'product_specs' ? 'bg-cyan-950 text-cyan-300 border border-cyan-500/30' : ($cat === 'faq' ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-300')) }}">
                                        {{ str_replace('_', ' ', $cat) }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pct >= 80 ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' : ($pct >= 60 ? 'bg-amber-950 text-amber-300 border border-amber-500/40' : 'bg-slate-800 text-slate-400') }}">
                                        {{ $pct }}% Match
                                    </span>
                                </div>
                            </div>
                            <p class="text-slate-300 text-[11px] leading-relaxed line-clamp-3 bg-slate-950/60 p-2 rounded-lg border border-white/5">
                                {{ $chunk['content'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($isSearching)
            <div class="p-8 text-center text-xs font-mono text-slate-500 animate-pulse">
                Running hybrid vector cosine & sparse keyword search across User Brain...
            </div>
        @endif
    </x-glass.card>

    <!-- ========================================================================= -->
    <!-- 3. KNOWLEDGE SOURCES & USER BRAIN COLLECTIONS                             -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white tracking-tight">Active Knowledge Sources ({{ $sources->count() }})</h2>
        </div>

        @if($sources->isEmpty())
            <x-glass.card variant="standard" class="p-12 text-center space-y-4 border border-dashed border-white/15">
                <div class="w-12 h-12 rounded-full bg-violet-600/20 text-violet-400 flex items-center justify-center mx-auto text-2xl">
                    🧠
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-white">No knowledge sources ingested yet</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Add your company guidelines, product documentation, FAQs, or reference URLs to ground all AI generation in verified facts.
                    </p>
                </div>
                <button 
                    type="button" 
                    wire:click="openIngestModal"
                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2"
                >
                    <span>+ Ingest First Knowledge Source</span>
                </button>
            </x-glass.card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($sources as $source)
                    @php
                        $cat = $source->category ?? 'general_docs';
                        $chunkCount = $source->chunks_count ?? 0;
                    @endphp
                    <x-glass.card variant="standard" class="p-5 space-y-4 border border-white/10 hover:border-violet-500/40 transition-all flex flex-col justify-between group shadow-lg">
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1 min-w-0">
                                    <span class="px-2 py-0.5 rounded text-[9.5px] font-bold uppercase tracking-wider {{ $cat === 'brand_voice' ? 'bg-purple-950 text-purple-300 border border-purple-500/30' : ($cat === 'product_specs' ? 'bg-cyan-950 text-cyan-300 border border-cyan-500/30' : ($cat === 'faq' ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/30' : ($cat === 'competitor_research' ? 'bg-amber-950 text-amber-300 border border-amber-500/30' : 'bg-slate-800 text-slate-300 border border-white/10'))) }}">
                                        {{ str_replace('_', ' ', $cat) }}
                                    </span>
                                    <h3 class="text-sm font-bold text-white truncate" title="{{ $source->title }}">{{ $source->title }}</h3>
                                </div>

                                <!-- Active Status Toggle Button -->
                                <button 
                                    type="button" 
                                    wire:click="toggleSourceActive({{ $source->id }})"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-colors shrink-0 {{ $source->is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-900/50' : 'bg-slate-800 text-slate-500 border border-white/10 hover:text-slate-300' }}"
                                    title="Click to toggle active status"
                                >
                                    {{ $source->is_active ? '● Active' : '○ Disabled' }}
                                </button>
                            </div>

                            <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                                {{ mb_substr(strip_tags($source->content), 0, 200) }}...
                            </p>
                        </div>

                        <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono text-slate-400">
                            <div class="flex items-center gap-2">
                                <span>{{ $chunkCount }} {{ Str::plural('chunk', $chunkCount) }}</span>
                                <span>&bull;</span>
                                <span class="capitalize">{{ $source->source_type }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    wire:click="reindex({{ $source->id }})"
                                    class="p-1.5 rounded-lg hover:bg-white/10 text-slate-400 hover:text-white transition-colors cursor-pointer"
                                    title="Re-index and vectorize"
                                >
                                    🔄
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="deleteSource({{ $source->id }})"
                                    wire:confirm="Are you sure you want to delete this knowledge source?"
                                    class="p-1.5 rounded-lg hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors cursor-pointer"
                                    title="Delete source"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </x-glass.card>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- 4. INGEST KNOWLEDGE SOURCE MODAL                                          -->
    <!-- ========================================================================= -->
    @if($showIngestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
            <x-glass.card variant="elevated" class="w-full max-w-2xl p-6 space-y-5 border border-white/15 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                        <span>🧠</span>
                        <span>Ingest Knowledge Source into User Brain</span>
                    </h3>
                    <button type="button" wire:click="$set('showIngestModal', false)" class="text-slate-400 hover:text-white cursor-pointer">✕</button>
                </div>

                @if($ingestErrorMessage)
                    <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                        {{ $ingestErrorMessage }}
                    </div>
                @endif

                <!-- Source Category -->
                <div class="space-y-1.5">
                    <label class="text-xs font-mono text-slate-300 font-bold">Brain Category Collection</label>
                    <select 
                        wire:model="category"
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-violet-500 font-mono"
                    >
                        <option value="brand_voice">Brand Voice & Guidelines (Tone, Rules, Persona)</option>
                        <option value="product_specs">Product Catalog & Technical Specs</option>
                        <option value="competitor_research">Competitor Intel & Market Research</option>
                        <option value="faq">FAQs & Verified Answers</option>
                        <option value="general_docs">General Documentation & Knowledge</option>
                    </select>
                </div>

                <!-- Source Title -->
                <div class="space-y-1.5">
                    <label class="text-xs font-mono text-slate-300 font-bold">Source Title</label>
                    <input 
                        type="text" 
                        wire:model="title" 
                        placeholder="e.g. 2026 Company Brand Guidelines, API Technical Documentation"
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-violet-500 font-mono"
                    />
                </div>

                <!-- Tab Navigation: Text vs URL -->
                <div class="flex items-center gap-2 border-b border-white/10 pb-2">
                    <button 
                        type="button" 
                        wire:click="$set('activeTab', 'text')"
                        class="px-3 py-1.5 rounded-lg text-xs font-mono transition-colors {{ $activeTab === 'text' ? 'bg-violet-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}"
                    >
                        📝 Plain Text / Markdown
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('activeTab', 'url')"
                        class="px-3 py-1.5 rounded-lg text-xs font-mono transition-colors {{ $activeTab === 'url' ? 'bg-violet-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}"
                    >
                        🌐 Fetch from Web URL
                    </button>
                </div>

                @if($activeTab === 'url')
                    <div class="space-y-2 p-3 rounded-xl bg-slate-900/60 border border-white/5">
                        <label class="text-xs font-mono text-slate-300">Public Web URL</label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="url" 
                                wire:model="urlInput"
                                placeholder="https://example.com/docs/terms"
                                class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-violet-500 font-mono"
                            />
                            <button 
                                type="button" 
                                wire:click="fetchFromUrl"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold cursor-pointer disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="fetchFromUrl">Fetch Content</span>
                                <span wire:loading wire:target="fetchFromUrl">Fetching...</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Content Area -->
                <div class="space-y-1.5">
                    <label class="text-xs font-mono text-slate-300 font-bold">Knowledge Content</label>
                    <textarea 
                        wire:model="content"
                        rows="8" 
                        placeholder="Paste full documentation, guidelines, product catalog items, or FAQ pairs here..."
                        class="w-full bg-slate-900 border border-white/15 rounded-xl p-3.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono leading-relaxed resize-y"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <button 
                        type="button" 
                        wire:click="$set('showIngestModal', false)"
                        class="px-4 py-2 rounded-xl hover:bg-white/10 text-slate-400 hover:text-white text-xs font-mono cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="saveSource"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
                    >
                        <span wire:loading.remove wire:target="saveSource">Vectorize & Index into Brain</span>
                        <span wire:loading wire:target="saveSource" class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Vectorizing...</span>
                        </span>
                    </button>
                </div>
            </x-glass.card>
        </div>
    @endif
</div>
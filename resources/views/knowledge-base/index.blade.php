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

<div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>🧠 Knowledge Base & RAG Pipeline</span>
                <x-glass.badge variant="violet">{{ $sources->count() }} Sources</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Vectorize company docs, product specs, and URLs into semantic embeddings for grounded, hallucination-free AI generation.
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
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
            <span>{{ session('status') }}</span>
            <button type="button" wire:click="$refresh" class="text-emerald-400 hover:text-white">✕</button>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- RAG SEMANTIC VECTOR SEARCH TESTER & PLAYGROUND                            -->
    <!-- ========================================================================= -->
    <x-glass.card variant="elevated" class="p-6 space-y-4 border border-indigo-500/30 shadow-2xl relative">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h3 class="text-sm font-bold text-white tracking-tight">Semantic Vector Search & RAG Tester</h3>
            </div>
            <span class="text-[10px] font-mono text-indigo-300">Hybrid Dense Cosine + BM25</span>
        </div>

        <div class="flex items-center gap-3">
            <input 
                type="text" 
                wire:model="searchQuery" 
                wire:keydown.enter="performSemanticSearch"
                placeholder="Ask or search your knowledge base (e.g. 'What is our refund policy?', 'Technical API specs')..." 
                class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono"
            />
            <button 
                type="button" 
                wire:click="performSemanticSearch"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2 shrink-0"
            >
                <span wire:loading.remove wire:target="performSemanticSearch">🔍 Query Chunks</span>
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
                    <span>Retrieved Top {{ count($searchResults) }} Semantic Chunks:</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($searchResults as $idx => $chunk)
                        @php
                            $pct = round($chunk['score'] * 100);
                            $badgeColor = $pct >= 80 ? 'text-emerald-400 border-emerald-500/30 bg-emerald-950/40' : ($pct >= 50 ? 'text-yellow-400 border-yellow-500/30 bg-yellow-950/40' : 'text-slate-300 border-white/10 bg-slate-900');
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-900/90 border border-white/10 space-y-2 text-xs flex flex-col justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-mono">
                                    <span class="font-bold text-white truncate max-w-[200px]">{{ $chunk['source_title'] }}</span>
                                    <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold {{ $badgeColor }}">
                                        {{ $pct }}% Match
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono">Chunk #{{ $chunk['chunk_index'] }} • {{ $chunk['token_count'] }} tokens</div>
                            </div>

                            <p class="text-slate-300 leading-relaxed line-clamp-4 font-mono text-[11px] bg-[#0d1117] p-2.5 rounded-lg border border-white/5">
                                {{ $chunk['content'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if($previewSnippet)
                    <div class="mt-4 p-4 rounded-xl bg-[#0d1117] border border-white/10 text-xs font-mono space-y-2">
                        <div class="flex items-center justify-between text-[10px] font-bold text-indigo-300 uppercase">
                            <span>Compiled RAG System Context Prompt:</span>
                        </div>
                        <pre class="text-slate-300 text-[11px] whitespace-pre-wrap leading-relaxed max-h-40 overflow-y-auto">{{ $previewSnippet }}</pre>
                    </div>
                @endif
            </div>
        @endif
    </x-glass.card>

    <!-- ========================================================================= -->
    <!-- KNOWLEDGE SOURCES LIST & GRID                                             -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white tracking-tight">Indexed Knowledge Sources</h2>
            <span class="text-xs text-slate-400 font-mono">{{ $sources->count() }} active sources</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($sources as $source)
                <x-glass.card variant="elevated" class="p-6 flex flex-col justify-between hover:border-violet-500/40 transition-all relative group">
                    <div class="space-y-4">
                        <!-- Type & Status Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">
                                    {{ $source->source_type === 'url' ? '🌐' : ($source->source_type === 'markdown' ? '📄' : '📝') }}
                                </span>
                                <div>
                                    <h3 class="text-base font-bold text-white tracking-tight line-clamp-1" title="{{ $source->title }}">
                                        {{ $source->title }}
                                    </h3>
                                    <span class="text-[10px] text-slate-400 uppercase font-mono">{{ $source->source_type }}</span>
                                </div>
                            </div>

                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono {{ $source->status === 'ready' ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-300 border border-amber-500/30 animate-pulse' }}">
                                ● {{ $source->status }}
                            </span>
                        </div>

                        <!-- Content Excerpt -->
                        <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                            {{ $source->content }}
                        </p>

                        <!-- Chunks Count Badge -->
                        <div class="flex items-center gap-3 text-xs font-mono text-slate-400 pt-2 border-t border-white/5">
                            <span class="text-indigo-300 font-bold">{{ $source->chunks_count }} Chunks</span>
                            <span>•</span>
                            <span>{{ $source->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-4 mt-4 border-t border-white/5 flex items-center justify-between gap-2">
                        <button 
                            type="button" 
                            wire:click="reindex({{ $source->id }})"
                            wire:loading.attr="disabled"
                            class="text-xs text-slate-400 hover:text-violet-300 transition-colors font-medium cursor-pointer"
                        >
                            🔄 Re-index
                        </button>

                        <button 
                            type="button" 
                            wire:click="deleteSource({{ $source->id }})"
                            wire:confirm="Are you sure you want to delete this knowledge source and its vector chunks?"
                            class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-red-500/20 text-slate-400 hover:text-red-400 border border-white/10 text-xs transition-all cursor-pointer"
                            title="Delete Knowledge Source"
                        >
                            🗑️ Delete
                        </button>
                    </div>
                </x-glass.card>
            @empty
                <div class="col-span-full py-16 text-center">
                    <x-glass.card variant="subtle" class="p-8 max-w-lg mx-auto space-y-4">
                        <div class="text-4xl">🧠</div>
                        <h3 class="text-base font-bold text-white">No Knowledge Sources Ingested</h3>
                        <p class="text-xs text-slate-400">
                            Add company docs, customer policies, product FAQs, or web URLs to your knowledge base. The AI will automatically reference them during generation.
                        </p>
                        <button 
                            type="button" 
                            wire:click="openIngestModal"
                            class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition-all cursor-pointer"
                        >
                            + Ingest First Knowledge Source
                        </button>
                    </x-glass.card>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- INGEST KNOWLEDGE SOURCE MODAL                                             -->
    <!-- ========================================================================= -->
    @if($showIngestModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/85 backdrop-blur-md">
            <div class="w-full max-w-2xl bg-[#0d1117] border border-[#30363d] rounded-2xl shadow-[0_25px_80px_rgba(0,0,0,0.95)] ring-1 ring-white/10 overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Header -->
                <div class="h-12 px-6 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between shrink-0">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>🧠 Ingest New Knowledge Source</span>
                    </h3>
                    <button 
                        type="button" 
                        wire:click="$set('showIngestModal', false)"
                        class="text-slate-400 hover:text-white text-sm cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body Form -->
                <div class="p-6 space-y-5 overflow-y-auto flex-1 text-xs">
                    <!-- Ingestion Type Tabs -->
                    <div class="flex items-center gap-2 p-1 rounded-xl bg-slate-900 border border-white/10">
                        <button 
                            type="button" 
                            wire:click="$set('activeTab', 'text')"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $activeTab === 'text' ? 'bg-violet-600 text-white' : 'text-slate-400 hover:text-white' }}"
                        >
                            📝 Raw Text
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('activeTab', 'markdown')"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $activeTab === 'markdown' ? 'bg-violet-600 text-white' : 'text-slate-400 hover:text-white' }}"
                        >
                            📄 Markdown / Docs
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('activeTab', 'url')"
                            class="flex-1 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $activeTab === 'url' ? 'bg-violet-600 text-white' : 'text-slate-400 hover:text-white' }}"
                        >
                            🌐 Import from URL
                        </button>
                    </div>

                    @if($ingestErrorMessage)
                        <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                            {{ $ingestErrorMessage }}
                        </div>
                    @endif

                    <!-- URL Fetcher Bar (If URL Tab) -->
                    @if($activeTab === 'url')
                        <div class="p-4 rounded-xl bg-slate-900 border border-white/10 space-y-2">
                            <label class="font-bold text-slate-300 block">Web URL to Scrape</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="url" 
                                    wire:model="urlInput" 
                                    placeholder="https://example.com/docs/api-guide"
                                    class="flex-1 bg-[#0d1117] border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono"
                                />
                                <button 
                                    type="button" 
                                    wire:click="fetchFromUrl"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs cursor-pointer disabled:opacity-50"
                                >
                                    Fetch
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Source Title <span class="text-red-400">*</span></label>
                        <input 
                            type="text" 
                            wire:model="title" 
                            placeholder="e.g. Q3 Product Roadmap, Support FAQ, Refund Policy, Technical API Guide"
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                        />
                        @error('title') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Project Scope (Optional) -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Assign to Project (Optional)</label>
                        <select 
                            wire:model="projectId"
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-violet-500"
                        >
                            <option value="">Global (All Projects & Documents)</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Content Textarea -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-300 block">Knowledge Content <span class="text-red-400">*</span></label>
                            <span class="text-[10px] text-slate-400 font-mono">{{ mb_strlen($content) }} chars</span>
                        </div>
                        <textarea 
                            wire:model="content" 
                            rows="8"
                            placeholder="Paste knowledge text, markdown documentation, or company guidelines..."
                            class="w-full bg-slate-900 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono leading-relaxed"
                        ></textarea>
                        @error('content') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-white/5 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="$set('showIngestModal', false)"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white font-semibold transition-all cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button 
                            type="button"
                            wire:click="saveSource"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="saveSource">⚡ Ingest & Vectorize Chunks</span>
                            <span wire:loading wire:target="saveSource" class="flex items-center gap-1.5">
                                <span class="w-3 h-3 border border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>Vectorizing...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
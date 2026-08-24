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

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Documents</h1>
            <p class="text-xs text-slate-400 mt-1">Manage, write, and version your AI-crafted long-form articles.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-glass.button variant="secondary" size="md" wire:click="openImportModal">
                ⬆ Import File
            </x-glass.button>
            <x-glass.button variant="primary" size="md" wire:click="openCreateModal">
                + New Document
            </x-glass.button>
        </div>
    </div>

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <!-- Filters & Search Toolbar -->
    <x-glass.card variant="subtle" class="p-4 flex flex-col sm:flex-row items-center gap-3">
        <div class="w-full sm:flex-1">
            <x-glass.input wire:model.live.debounce.300ms="search" placeholder="Search documents by title..." />
        </div>

        <div class="w-full sm:w-48">
            <select wire:model.live="selectedProject" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-36">
            <select wire:model.live="selectedStatus" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </x-glass.card>

    <!-- Loading Skeleton Grid (Instant UI during search / filters) -->
    <div wire:loading.grid wire:target="search,selectedProject,selectedStatus" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @for($i = 0; $i < 6; $i++)
            <x-glass.skeleton type="card" />
        @endfor
    </div>

    <!-- Documents Listing Grid -->
    <div wire:loading.remove wire:target="search,selectedProject,selectedStatus">
        @if($documents->isEmpty())
            <x-glass.card variant="subtle" class="p-12 text-center max-w-md mx-auto">
                <div class="text-4xl mb-3">📄</div>
                <h3 class="text-base font-bold text-white mb-1">No Documents Found</h3>
                <p class="text-xs text-slate-400 mb-6">Create a document to start writing with AI assistance.</p>
                <x-glass.button variant="primary" size="sm" wire:click="openCreateModal">
                    Create First Document
                </x-glass.button>
            </x-glass.card>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($documents as $doc)
                <x-glass.card variant="standard" class="p-6 flex flex-col justify-between hover:border-indigo-500/40 hover:-translate-y-0.5 transition-all">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <span class="text-xs font-mono px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 uppercase">
                                {{ $doc->status }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button wire:confirm="Are you sure you want to delete this document?" wire:click="delete({{ $doc->id }})" class="text-slate-400 hover:text-red-400 p-1 rounded hover:bg-white/5 cursor-pointer" title="Delete">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('documents.editor', $doc->id) }}" wire:navigate class="block group">
                            <h3 class="text-base font-bold text-white mb-2 line-clamp-1 group-hover:text-indigo-300 transition-colors">{{ $doc->title }}</h3>
                        </a>
                        
                        @if($doc->project)
                            <div class="inline-flex items-center gap-1 text-[11px] text-indigo-400 mb-3">
                                <span>📁</span>
                                <span class="truncate">{{ $doc->project->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span>{{ number_format($doc->word_count) }} words</span>
                            <span>&bull;</span>
                            <span>{{ $doc->reading_time_minutes }}m</span>
                        </div>
                        <span class="text-[10px] text-slate-500">{{ $doc->updated_at->diffForHumans() }}</span>
                    </div>
                </x-glass.card>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($documents->hasPages())
            <div class="pt-4">
                {{ $documents->links() }}
            </div>
        @endif
    @endif
    </div>

    <!-- Create Document Modal -->
    <div 
        x-data="{ show: @entangle('showCreateModal') }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4" 
        style="display: none;"
    >
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" x-on:click="show = false"></div>
        <x-glass.card variant="elevated" class="w-full max-w-md p-6 sm:p-8 z-10 border border-white/15 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-4">Create New Document</h3>

            <form wire:submit="createDocument" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Document Title</label>
                    <x-glass.input wire:model="newTitle" placeholder="e.g. 10 Strategies for AI SEO in 2026" required :error="$errors->has('newTitle')" />
                    @error('newTitle') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Assign to Project (Optional)</label>
                    <select wire:model="newProjectId" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                        <option value="">No Project (Standalone)</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                    <x-glass.button type="button" variant="secondary" size="sm" x-on:click="show = false">Cancel</x-glass.button>
                    <x-glass.button type="submit" variant="primary" size="sm">Create Document</x-glass.button>
                </div>
            </form>
        </x-glass.card>
    </div>

    <!-- Import Document File Modal -->
    <div 
        x-data="{ showImport: @entangle('showImportModal') }" 
        x-show="showImport" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4" 
        style="display: none;"
    >
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" x-on:click="showImport = false"></div>
        <x-glass.card variant="elevated" class="w-full max-w-md p-6 sm:p-8 z-10 border border-white/15 shadow-2xl relative space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl">📥</span>
                    <h3 class="text-base font-bold text-white tracking-tight">Import Document File</h3>
                </div>
                <button type="button" x-on:click="showImport = false" class="text-slate-400 hover:text-white p-1 cursor-pointer">✕</button>
            </div>

            <p class="text-xs text-slate-400">
                Upload your existing Markdown (<code class="text-indigo-300">.md</code>), HTML (<code class="text-indigo-300">.html</code>), or Text (<code class="text-indigo-300">.txt</code>) file to convert into an editable document.
            </p>

            <form wire:submit="importDocument" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Select File (Max 10MB)</label>
                    <input 
                        type="file" 
                        wire:model="importFile" 
                        accept=".md,.markdown,.html,.htm,.txt" 
                        class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer bg-slate-900 border border-white/10 rounded-xl p-2"
                        required
                    />
                    @error('importFile') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Assign to Project (Optional)</label>
                    <select wire:model="importProjectId" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                        <option value="">No Project (Standalone)</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                    <x-glass.button type="button" variant="secondary" size="sm" x-on:click="showImport = false">Cancel</x-glass.button>
                    <x-glass.button type="submit" variant="primary" size="sm">
                        <span wire:loading.remove wire:target="importDocument">Import & Open Editor</span>
                        <span wire:loading wire:target="importDocument">Importing...</span>
                    </x-glass.button>
                </div>
            </form>
        </x-glass.card>
    </div>
</div>
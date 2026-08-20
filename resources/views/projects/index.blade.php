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
            <h1 class="text-2xl font-bold text-white tracking-tight">Workspaces & Projects</h1>
            <p class="text-xs text-slate-400 mt-1">Organize your AI articles, brand rules, and knowledge sources by workspace.</p>
        </div>
        <x-glass.button variant="primary" size="md" wire:click="openCreateModal">
            + New Project
        </x-glass.button>
    </div>

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <!-- Projects Grid -->
    @if($projects->isEmpty())
        <x-glass.card variant="subtle" class="p-12 text-center max-w-lg mx-auto">
            <div class="text-4xl mb-3">📁</div>
            <h3 class="text-base font-bold text-white mb-1">No Projects Found</h3>
            <p class="text-xs text-slate-400 mb-6">Create your first project folder to group related AI articles.</p>
            <x-glass.button variant="primary" size="sm" wire:click="openCreateModal">
                Create First Project
            </x-glass.button>
        </x-glass.card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                <x-glass.card variant="standard" class="p-6 flex flex-col justify-between hover:border-indigo-500/40 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shadow-md" style="background-color: {{ $project->color }}20; border: 1px solid {{ $project->color }}40;">
                                <span style="color: {{ $project->color }};">📁</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="openEditModal({{ $project->id }})" class="text-slate-400 hover:text-white p-1 rounded hover:bg-white/5 cursor-pointer" title="Edit">
                                    ✏️
                                </button>
                                <button wire:confirm="Are you sure you want to delete this project?" wire:click="delete({{ $project->id }})" class="text-slate-400 hover:text-red-400 p-1 rounded hover:bg-white/5 cursor-pointer" title="Delete">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <h3 class="text-base font-bold text-white mb-1 truncate">{{ $project->name }}</h3>
                        <p class="text-xs text-slate-400 line-clamp-2 mb-4">{{ $project->description ?: 'No description provided.' }}</p>
                    </div>

                    <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ $project->documents_count }} {{ Str::plural('document', $project->documents_count) }}</span>
                        <span>{{ $project->created_at->format('M d, Y') }}</span>
                    </div>
                </x-glass.card>
            @endforeach
        </div>
    @endif

    <!-- Project Create / Edit Modal -->
    <div 
        x-data="{ show: @entangle('showModal') }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4" 
        style="display: none;"
    >
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" x-on:click="show = false"></div>
        <x-glass.card variant="elevated" class="w-full max-w-md p-6 sm:p-8 z-10 border border-white/15 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-4">{{ $editingProjectId ? 'Edit Project' : 'Create New Project' }}</h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Project Name</label>
                    <x-glass.input wire:model="name" placeholder="e.g. SaaS Blog 2026" required :error="$errors->has('name')" />
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Description (Optional)</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-xl bg-slate-900/80 border border-white/10 px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500" placeholder="Brief workspace overview..."></textarea>
                    @error('description') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Accent Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model="color" class="w-10 h-8 rounded border border-white/10 bg-transparent cursor-pointer">
                        <span class="text-xs font-mono text-slate-300" x-text="$wire.color"></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                    <x-glass.button type="button" variant="secondary" size="sm" x-on:click="show = false">Cancel</x-glass.button>
                    <x-glass.button type="submit" variant="primary" size="sm">Save Project</x-glass.button>
                </div>
            </form>
        </x-glass.card>
    </div>
</div>
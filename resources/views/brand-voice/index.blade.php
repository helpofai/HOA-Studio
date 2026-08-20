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
    <!-- Header with Quick Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                <span>🎭 Brand Voice Profiles</span>
                <x-glass.badge variant="violet">{{ $brandVoices->count() }} Profiles</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Train AI to write in your company's signature tone, audience persona, and style guidelines across all documents and templates.
            </p>
        </div>

        <button 
            type="button" 
            wire:click="openCreateModal"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer flex items-center gap-2"
        >
            <span>+ Create Brand Voice</span>
        </button>
    </div>

    <!-- Status Alert -->
    @if(session('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
            <span>{{ session('status') }}</span>
            <button type="button" wire:click="$refresh" class="text-emerald-400 hover:text-white">✕</button>
        </div>
    @endif

    <!-- Brand Voice Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($brandVoices as $voice)
            <x-glass.card variant="elevated" class="p-6 flex flex-col justify-between hover:border-violet-500/40 transition-all relative group {{ $voice->is_default ? 'border-violet-500/50 bg-violet-950/20' : '' }}">
                @if($voice->is_default)
                    <div class="absolute top-0 right-0 px-3 py-1 bg-gradient-to-l from-violet-600 to-indigo-600 text-white font-mono text-[9px] font-bold uppercase rounded-bl-xl shadow-sm">
                        ★ DEFAULT VOICE
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Title & Audience -->
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span>🎭 {{ $voice->name }}</span>
                        </h3>
                        @if($voice->target_audience)
                            <p class="text-xs text-indigo-300 font-mono mt-0.5">Audience: {{ $voice->target_audience }}</p>
                        @endif
                    </div>

                    <!-- Tone Description -->
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-white/5 text-xs text-slate-300 leading-relaxed">
                        <span class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Tone & Voice:</span>
                        <p class="line-clamp-3">{{ $voice->tone_description }}</p>
                    </div>

                    <!-- Guidelines & Forbidden Words -->
                    @if($voice->guidelines || !empty($voice->forbidden_words))
                        <div class="space-y-2 text-[11px] text-slate-400">
                            @if($voice->guidelines)
                                <div class="truncate">
                                    <strong class="text-slate-300">Rules:</strong> {{ $voice->guidelines }}
                                </div>
                            @endif
                            @if(!empty($voice->forbidden_words))
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <strong class="text-slate-300">Avoid:</strong>
                                    @foreach($voice->forbidden_words as $w)
                                        <span class="px-1.5 py-0.2 rounded bg-red-950 text-red-300 text-[9px] border border-red-500/20 font-mono">{{ $w }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 mt-4 border-t border-white/5 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        @if(!$voice->is_default)
                            <button 
                                type="button" 
                                wire:click="setDefault({{ $voice->id }})"
                                class="text-[11px] text-slate-400 hover:text-violet-300 transition-colors font-medium cursor-pointer"
                            >
                                Set as Default
                            </button>
                        @else
                            <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                                ✓ Active Default
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="openEditModal({{ $voice->id }})"
                            class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-white/10 text-xs font-semibold transition-all cursor-pointer"
                        >
                            ✏️ Edit
                        </button>
                        <button 
                            type="button" 
                            wire:click="delete({{ $voice->id }})"
                            wire:confirm="Are you sure you want to delete this brand voice profile?"
                            class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-red-500/20 text-slate-400 hover:text-red-400 border border-white/10 text-xs transition-all cursor-pointer"
                            title="Delete Profile"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </x-glass.card>
        @empty
            <div class="col-span-full py-16 text-center">
                <x-glass.card variant="subtle" class="p-8 max-w-lg mx-auto space-y-4">
                    <div class="text-4xl">🎭</div>
                    <h3 class="text-base font-bold text-white">No Brand Voices Defined</h3>
                    <p class="text-xs text-slate-400">
                        Create your first brand voice to guide all AI generation, ensuring every article, cold email, and social post sounds distinctly like your company.
                    </p>
                    <button 
                        type="button" 
                        wire:click="openCreateModal"
                        class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition-all cursor-pointer"
                    >
                        + Create Your First Voice Profile
                    </button>
                </x-glass.card>
            </div>
        @endforelse
    </div>

    <!-- ========================================================================= -->
    <!-- CREATE / EDIT BRAND VOICE MODAL                                           -->
    <!-- ========================================================================= -->
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/85 backdrop-blur-md">
            <div class="w-full max-w-2xl bg-[#0d1117] border border-[#30363d] rounded-2xl shadow-[0_25px_80px_rgba(0,0,0,0.95)] ring-1 ring-white/10 overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Titlebar -->
                <div class="h-12 px-6 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between shrink-0">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>🎭 {{ $editingId ? 'Edit Brand Voice' : 'Create New Brand Voice' }}</span>
                    </h3>
                    <button 
                        type="button" 
                        wire:click="$set('showCreateModal', false); $set('showEditModal', false);"
                        class="text-slate-400 hover:text-white text-sm cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form wire:submit="save" class="p-6 space-y-5 overflow-y-auto flex-1 text-xs">
                    <!-- Quick Presets Selector -->
                    @if(!$editingId)
                        <div class="space-y-2">
                            <label class="font-bold text-slate-300 block">⚡ Quick Start Presets (Optional)</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($presets as $idx => $p)
                                    <button 
                                        type="button" 
                                        wire:click="applyPreset({{ $idx }})"
                                        class="p-2.5 text-left rounded-xl bg-slate-900/80 hover:bg-violet-950/40 border border-white/5 hover:border-violet-500/30 transition-all cursor-pointer"
                                    >
                                        <div class="font-bold text-white truncate">{{ $p['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 truncate">{{ $p['audience'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Voice Name -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Profile Name <span class="text-red-400">*</span></label>
                        <input 
                            type="text" 
                            wire:model="name" 
                            placeholder="e.g. Acme Tech Visionary, B2B SaaS Executive, Friendly Coach"
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                        />
                        @error('name') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tone Description -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Tone & Style Description <span class="text-red-400">*</span></label>
                        <textarea 
                            wire:model="tone_description" 
                            rows="3"
                            placeholder="Describe how the AI should sound (e.g. Authoritative, direct, punchy, optimistic, data-driven)..."
                            class="w-full bg-slate-900 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                        ></textarea>
                        @error('tone_description') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Target Audience -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Target Audience Persona (Optional)</label>
                        <input 
                            type="text" 
                            wire:model="target_audience" 
                            placeholder="e.g. Early-Stage Tech Founders, Growth Marketers, Enterprise CTOs"
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                        />
                    </div>

                    <!-- Specific Writing Guidelines -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Specific Writing Rules & Guidelines (Optional)</label>
                        <textarea 
                            wire:model="guidelines" 
                            rows="2"
                            placeholder="e.g. Always use active voice. Format lists with bullet points. Emphasize speed and ROI."
                            class="w-full bg-slate-900 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                        ></textarea>
                    </div>

                    <!-- Forbidden Words -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Words to Avoid / Forbidden Words (Comma-separated)</label>
                        <input 
                            type="text" 
                            wire:model="forbidden_words_input" 
                            placeholder="e.g. synergy, utilize, game-changing, revolutionize, leverage"
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                        />
                    </div>

                    <!-- Sample Content -->
                    <div class="space-y-1.5">
                        <label class="font-bold text-slate-300 block">Reference Sample Content (Optional)</label>
                        <textarea 
                            wire:model="sample_content" 
                            rows="3"
                            placeholder="Paste an excerpt of writing that perfectly exemplifies this brand voice..."
                            class="w-full bg-slate-900 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono text-[11px]"
                        ></textarea>
                    </div>

                    <!-- Is Default Toggle -->
                    <div class="flex items-center gap-3 pt-2">
                        <input 
                            type="checkbox" 
                            id="is_default_checkbox" 
                            wire:model="is_default"
                            class="w-4 h-4 rounded bg-slate-900 border-white/20 text-violet-600 focus:ring-0 cursor-pointer"
                        />
                        <label for="is_default_checkbox" class="text-xs text-slate-300 cursor-pointer">
                            Set as default brand voice for new generations and templates
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-white/5 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="$set('showCreateModal', false); $set('showEditModal', false);"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white font-semibold transition-all cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-5 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer"
                        >
                            {{ $editingId ? 'Update Brand Voice' : 'Create Profile' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
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
                <span>⚡ AI Templates Hub</span>
                <x-glass.badge variant="violet">{{ $templates->count() }} Recipes</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Battle-tested copywriting frameworks, SEO blueprints, and sales outreach templates powered by OmniRoute AI.
            </p>
        </div>

        <a href="{{ route('brand-voices.index') }}" wire:navigate class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-300 hover:text-white text-xs font-semibold flex items-center gap-2 transition-all">
            <span>🎭 Brand Voices ({{ $brandVoices->count() }})</span>
        </a>
    </div>

    <!-- Category Tabs & Search Bar -->
    <x-glass.card variant="subtle" class="p-4 space-y-4">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:flex-1">
                <x-glass.input 
                    wire:model.live.debounce.250ms="search" 
                    placeholder="Search templates (e.g. SEO article, landing page, cold email, LinkedIn, press release)..." 
                />
            </div>
        </div>

        <!-- Category Pills -->
        <div class="flex flex-wrap items-center gap-2 pt-1">
            <button 
                type="button" 
                wire:click="$set('selectedCategory', 'all')"
                class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer font-medium {{ $selectedCategory === 'all' ? 'bg-violet-600 text-white font-bold shadow-md shadow-violet-600/30' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
            >
                🌟 All Templates
            </button>

            @foreach($categories as $cat)
                <button 
                    type="button" 
                    wire:click="$set('selectedCategory', '{{ $cat->slug }}')"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-1.5 font-medium {{ $selectedCategory === $cat->slug ? 'bg-violet-600 text-white font-bold shadow-md shadow-violet-600/30' : 'bg-slate-900/80 text-slate-400 hover:text-white border border-white/5' }}"
                >
                    <span>{{ $cat->icon }}</span>
                    <span>{{ $cat->name }}</span>
                </button>
            @endforeach
        </div>
    </x-glass.card>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $tmpl)
            <x-glass.card 
                variant="elevated" 
                class="p-6 flex flex-col justify-between hover:border-violet-500/50 hover:shadow-2xl hover:shadow-violet-500/10 transition-all cursor-pointer group relative"
                wire:click="selectTemplate({{ $tmpl->id }})"
            >
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-10 h-10 rounded-xl bg-violet-950/80 border border-violet-500/30 flex items-center justify-center text-xl shadow-md group-hover:scale-105 transition-transform shrink-0">
                            {{ $tmpl->icon }}
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-slate-900 border border-white/5 text-[10px] text-slate-400 font-mono">
                            {{ $tmpl->category->name ?? 'General' }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-white group-hover:text-violet-200 transition-colors">
                            {{ $tmpl->name }}
                        </h3>
                        <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed">
                            {{ $tmpl->description }}
                        </p>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-white/5 flex items-center justify-between text-xs text-indigo-300 font-semibold">
                    <span class="flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        <span>Use Recipe</span>
                        <span>&rarr;</span>
                    </span>
                    <span class="text-[10px] font-mono text-slate-500">
                        {{ count($tmpl->inputs_schema ?? []) }} fields
                    </span>
                </div>
            </x-glass.card>
        @empty
            <div class="col-span-full py-16 text-center">
                <x-glass.card variant="subtle" class="p-8 max-w-md mx-auto">
                    <div class="text-3xl mb-2">🔍</div>
                    <h4 class="text-sm font-bold text-white">No Templates Found</h4>
                    <p class="text-xs text-slate-400 mt-1 mb-4">Try clearing your search query or choosing another category.</p>
                    <button type="button" wire:click="$set('search', ''); $set('selectedCategory', 'all')" class="px-3 py-1.5 rounded-xl bg-violet-600 text-white text-xs font-bold">
                        Reset Filters
                    </button>
                </x-glass.card>
            </div>
        @endforelse
    </div>

    <!-- ========================================================================= -->
    <!-- TEMPLATE RUNNER & GENERATOR MODAL                                         -->
    <!-- ========================================================================= -->
    @if($showRunnerModal && $activeTemplate)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/85 backdrop-blur-md">
            <div class="w-full max-w-3xl bg-[#0d1117] border border-[#30363d] rounded-2xl shadow-[0_25px_80px_rgba(0,0,0,0.95)] ring-1 ring-white/10 overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Modal Titlebar -->
                <div class="h-12 px-6 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between shrink-0 select-none">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $activeTemplate->icon }}</span>
                        <h3 class="text-sm font-bold text-white truncate">{{ $activeTemplate->name }}</h3>
                    </div>
                    <button type="button" wire:click="closeRunnerModal" class="text-slate-400 hover:text-white text-sm cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Modal Content Area -->
                <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
                    <!-- Template Info -->
                    <div class="p-3.5 rounded-xl bg-slate-900/80 border border-white/5 text-slate-300 leading-relaxed">
                        <p>{{ $activeTemplate->description }}</p>
                    </div>

                    @if($errorMessage)
                        <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <!-- Dynamic Input Fields Form -->
                    <form wire:submit="generate" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Brand Voice Selector -->
                            <div class="space-y-1">
                                <label class="font-bold text-slate-300 block">🎭 Apply Brand Voice (Optional)</label>
                                <select 
                                    wire:model="selectedBrandVoiceId"
                                    class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-violet-500"
                                >
                                    <option value="">Default AI Voice</option>
                                    @foreach($brandVoices as $bv)
                                        <option value="{{ $bv->id }}">{{ $bv->name }} {{ $bv->is_default ? '(Default)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Model Selector -->
                            <div class="space-y-1">
                                <label class="font-bold text-slate-300 block">⚡ Engine / Routing</label>
                                <select 
                                    wire:model="selectedModel"
                                    class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-violet-500 font-mono"
                                >
                                    <option value="auto">OmniRoute Auto (Dynamic)</option>
                                    <option value="auto:quality">OmniRoute Auto Quality (Reasoning)</option>
                                    <option value="auto:free">OmniRoute Auto Free (42 Pools)</option>
                                    <option value="cc/claude-3-7-sonnet">Claude 3.7 Sonnet</option>
                                    <option value="deepseek/deepseek-chat">DeepSeek-V3</option>
                                    <option value="openai/gpt-4o">OpenAI GPT-4o</option>
                                </select>
                            </div>
                        </div>

                        <!-- Template Specific Fields -->
                        @foreach($activeTemplate->inputs_schema ?? [] as $field)
                            <div class="space-y-1.5">
                                <label class="font-bold text-slate-300 block">
                                    {{ $field['label'] }}
                                    @if(!empty($field['required'])) <span class="text-red-400">*</span> @endif
                                </label>

                                @if(($field['type'] ?? 'text') === 'textarea')
                                    <textarea 
                                        wire:model="formInputs.{{ $field['name'] }}"
                                        rows="3"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        class="w-full bg-slate-900 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                                    ></textarea>
                                @else
                                    <input 
                                        type="text" 
                                        wire:model="formInputs.{{ $field['name'] }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500"
                                    />
                                @endif
                                @error('formInputs.' . $field['name']) <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
                            </div>
                        @endforeach

                        <div class="pt-2 flex items-center justify-end">
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-2"
                            >
                                <span wire:loading.remove wire:target="generate">✨ Generate Copy with AI</span>
                                <span wire:loading wire:target="generate" class="flex items-center gap-2">
                                    <span class="w-3 h-3 border border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Generating...</span>
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Generated Output Surface -->
                    @if($generatedContent)
                        <div class="pt-4 border-t border-white/10 space-y-3">
                            <div class="flex items-center justify-between text-[11px] font-mono text-emerald-400">
                                <span class="font-bold flex items-center gap-1.5">
                                    <span>✓ Generated Content</span>
                                    <span class="text-slate-500">•</span>
                                    <span class="text-indigo-300">{{ $generationTelemetry['words_used'] ?? 0 }} words</span>
                                </span>
                                <span class="text-slate-400">{{ $generationTelemetry['latency_ms'] ?? 0 }}ms</span>
                            </div>

                            <div class="p-4 rounded-xl bg-gradient-to-b from-indigo-950/30 to-[#161b22] border border-indigo-500/30 text-white text-sm leading-relaxed max-h-[300px] overflow-y-auto whitespace-pre-wrap select-text font-sans">
                                {{ $generatedContent }}
                            </div>

                            <!-- Export & Editor Action Bar -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                <button 
                                    type="button" 
                                    onclick="navigator.clipboard.writeText({{ json_encode($generatedContent) }}); alert('Copied to clipboard!');"
                                    class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <span>📋 Copy Output</span>
                                </button>

                                <button 
                                    type="button" 
                                    wire:click="createDocumentFromGeneration"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <span>🚀 Open in Document Editor</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
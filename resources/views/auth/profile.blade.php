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
    <!-- Profile Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xl font-bold border border-indigo-400/30 shadow-lg shadow-indigo-500/20">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ auth()->user()->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-slate-400">{{ auth()->user()->email }}</span>
                        <span>&bull;</span>
                        <x-glass.badge :variant="match(auth()->user()->role) { 'admin' => 'violet', 'editor' => 'cyan', 'pro' => 'amber', default => 'emerald' }">
                            Role: {{ ucfirst(auth()->user()->role ?? 'user') }}
                        </x-glass.badge>
                        <span class="text-xs text-slate-400">&bull; Plan: <strong class="capitalize text-indigo-400">{{ auth()->user()->plan }}</strong></span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-glass.button type="submit" variant="glass" size="sm">
                    Log Out
                </x-glass.button>
            </form>
        </div>

        <!-- Word Quota Status Card -->
        <x-glass.card variant="premium" glow="violet" class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                <div>
                    <div class="text-xs font-semibold text-purple-400 uppercase tracking-wider mb-1">Monthly AI Word Balance</div>
                    <div class="text-3xl font-black text-white">
                        {{ number_format(auth()->user()->monthly_word_quota - auth()->user()->used_word_quota) }}
                        <span class="text-sm font-normal text-slate-400">words remaining</span>
                    </div>
                </div>
                <x-glass.badge variant="emerald">Active Subscription</x-glass.badge>
            </div>

            @php
            $pct = auth()->user()->monthly_word_quota > 0 
                ? min(100, round((auth()->user()->used_word_quota / auth()->user()->monthly_word_quota) * 100))
                : 0;
            @endphp

            <!-- Progress Bar -->
            <div class="w-full bg-slate-900 rounded-full h-2.5 overflow-hidden mb-2">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>{{ number_format(auth()->user()->used_word_quota) }} used</span>
                <span>{{ number_format(auth()->user()->monthly_word_quota) }} total limit</span>
            </div>
        </x-glass.card>

        <!-- Profile Settings Form -->
        <x-glass.card variant="standard" class="p-6 sm:p-8">
            <h3 class="text-lg font-bold text-white mb-6 pb-3 border-b border-white/5">Account Preferences</h3>

            @if ($statusMessage)
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 mb-6">
                    {{ $statusMessage }}
                </div>
            @endif

            <form wire:submit="updateProfile" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Full Name</label>
                        <x-glass.input wire:model="name" type="text" required :error="$errors->has('name')" />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Email Address</label>
                        <x-glass.input wire:model="email" type="email" required :error="$errors->has('email')" />
                        @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Default OmniRoute Model</label>
                        <select wire:model="default_model" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                            <option>OmniRoute: DeepSeek-V3</option>
                            <option>OmniRoute: Claude 3.7 Sonnet</option>
                            <option>OmniRoute: OpenAI GPT-4o</option>
                            <option>OmniRoute: Local Ollama (vLLM)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Vector Embedding Cache Duration</label>
                        <select wire:model="embedding_cache_days" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                            <option value="1">1 Day (Frequent updates)</option>
                            <option value="7">7 Days (Recommended standard)</option>
                            <option value="30">30 Days (Maximum speed & token saving)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Caches semantic vector queries in Knowledge Base & RAG.</p>
                    </div>
                </div>

                <div class="pt-6 border-t border-white/5">
                    <h4 class="text-sm font-semibold text-white mb-4">Change Password (Optional)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-300 block mb-1.5">New Password</label>
                            <x-glass.input wire:model="new_password" type="password" placeholder="Leave blank to keep current" :error="$errors->has('new_password')" />
                            @error('new_password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-300 block mb-1.5">Confirm New Password</label>
                            <x-glass.input wire:model="new_password_confirmation" type="password" placeholder="Repeat new password" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <x-glass.button type="submit" variant="primary" size="md">
                        <span wire:loading.remove wire:target="updateProfile">Save Preferences</span>
                        <span wire:loading wire:target="updateProfile">Saving...</span>
                    </x-glass.button>
                </div>
            </form>
        </x-glass.card>

        <!-- BYOK Custom API Keys & Local Endpoints (AES-256-GCM Encrypted) -->
        <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>🔑 BYOK API Keys & Local Endpoints</span>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono font-bold uppercase">
                            AES-256-GCM Encrypted
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Connect your personal OpenAI, DeepSeek, Anthropic, or local Ollama endpoints. <strong>Using your own key grants 100% unlimited AI generation rates (no rate limit).</strong>
                    </p>
                </div>
            </div>

            <!-- Connect Key Form -->
            @if($allowedProviders->isNotEmpty())
                <form wire:submit="saveApiKey" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-900/60 border border-white/5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Provider (Admin Enabled)</label>
                        <select wire:model="byok_provider" class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-medium">
                            @foreach($allowedProviders as $ap)
                                <option value="{{ $ap->slug }}">{{ $ap->name }} ({{ $ap->slug }})</option>
                            @endforeach
                            <option value="custom">Custom Endpoint (Ollama / vLLM / Local)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">API Key / Token</label>
                        <input 
                            type="password" 
                            wire:model="byok_api_key" 
                            placeholder="sk-..." 
                            class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                            required
                        />
                        @error('byok_api_key') <p class="text-[10px] text-red-400 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Custom Base URL (Optional)</label>
                            <input 
                                type="text" 
                                wire:model="byok_custom_url" 
                                placeholder="http://localhost:11434/v1" 
                                class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                            />
                        </div>
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer shrink-0"
                        >
                            Save Key
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/10 text-xs text-slate-400 flex items-center gap-2.5">
                    <span class="text-base">ℹ️</span>
                    <span>Custom BYOK API keys are currently disabled by the administrator in the Admin Control Panel. Platform master gateway keys and plan word quotas are in effect.</span>
                </div>
            @endif

            <!-- Connected Keys Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5 px-3">Provider</th>
                            <th class="py-2.5 px-3">Endpoint</th>
                            <th class="py-2.5 px-3">Encrypted API Key</th>
                            <th class="py-2.5 px-3">Rate Limit</th>
                            <th class="py-2.5 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($apiKeys as $key)
                            @php
                                $isVisible = in_array($key->id, $visibleKeys, true);
                                $rawKey = $key->getRawKeyForOwner(auth()->user());
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-white uppercase font-mono">{{ $key->provider_slug }}</span>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-slate-400">
                                    {{ $key->custom_base_url ?: 'Default Cloud Gateway' }}
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px]">
                                    <div class="flex items-center gap-2">
                                        @if($isVisible)
                                            <span class="text-emerald-300 select-all">{{ $rawKey }}</span>
                                        @else
                                            <span class="text-slate-500 tracking-widest">••••••••••••••••••••••••••••</span>
                                        @endif

                                        <button 
                                            type="button" 
                                            wire:click="toggleKeyVisibility({{ $key->id }})" 
                                            class="text-xs text-slate-400 hover:text-white transition-colors cursor-pointer p-0.5"
                                            title="{{ $isVisible ? 'Hide Key' : 'Reveal Raw Key' }}"
                                        >
                                            {{ $isVisible ? '🙈' : '👁️' }}
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 font-bold uppercase">
                                        ⚡ UNLIMITED
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <button 
                                        type="button" 
                                        wire:click="deleteApiKey({{ $key->id }})" 
                                        wire:confirm="Remove this API key?"
                                        class="text-[11px] text-red-400 hover:text-red-300 font-semibold cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">
                                    No custom API keys registered. The platform shared gateway with plan rate limits is currently active.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-glass.card>
</div>
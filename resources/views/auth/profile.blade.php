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

<div class="space-y-6 pb-12">
    <!-- Header with User Identity & Quick Metrics -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-6 rounded-3xl bg-slate-950/80 border border-white/10 shadow-2xl backdrop-blur-2xl relative overflow-hidden">
        <!-- Ambient Glowing Aura -->
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-gradient-to-br from-indigo-600/20 via-purple-600/10 to-transparent blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-4 z-10">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-400 flex items-center justify-center text-white text-2xl font-black border border-indigo-400/40 shadow-xl shadow-indigo-500/20 shrink-0">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-black text-white tracking-tight">{{ $user->name }}</h1>
                    <x-glass.badge :variant="match($user->role) { 'admin' => 'violet', 'editor' => 'cyan', 'pro' => 'amber', default => 'emerald' }">
                        {{ ucfirst($user->role ?? 'user') }}
                    </x-glass.badge>
                    <x-glass.badge variant="cyan" class="uppercase font-mono text-[10px]">
                        {{ $user->plan ?? 'Starter' }}
                    </x-glass.badge>
                </div>
                <div class="flex items-center gap-3 mt-1 text-xs text-slate-400 flex-wrap">
                    <span>{{ $user->email }}</span>
                    <span>&bull;</span>
                    <span>Joined {{ $user->created_at ? $user->created_at->format('M Y') : '2026' }}</span>
                    <span>&bull;</span>
                    <span class="text-indigo-300 font-mono">{{ number_format($contentStats['total_documents']) }} Documents ({{ number_format($contentStats['total_words_written']) }} words)</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 z-10">
            <a href="{{ route('editor') }}" wire:navigate>
                <x-glass.button variant="primary" size="md" class="shadow-lg shadow-indigo-500/25 gap-1.5 font-bold">
                    <span>✍️</span> <span>Write New Document</span>
                </x-glass.button>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-glass.button type="submit" variant="glass" size="md" class="text-slate-300 hover:text-red-400 border-white/10 hover:border-red-500/30">
                    <span>🚪 Log Out</span>
                </x-glass.button>
            </form>
        </div>
    </div>

    <!-- Notification Toasts -->
    @if ($statusMessage)
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <span class="text-base">✓</span>
                <span>{{ $statusMessage }}</span>
            </div>
            <button type="button" wire:click="$set('statusMessage', null)" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕</button>
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300 flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Unified Main Layout: Left Side Tabs Navigation + Right Dynamic Active Tab Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Side Tabs Navigation Bar (Sticky on Desktop) -->
        <div class="lg:col-span-3 lg:sticky lg:top-24 space-y-4">
            <div class="flex flex-col gap-1.5 p-2 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner select-none">
                <button 
                    type="button" 
                    wire:click="switchTab('profile')"
                    class="flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-left {{ $activeTab === 'profile' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">👤</span>
                        <span>Profile & Security</span>
                    </div>
                    <span class="text-[10px] {{ $activeTab === 'profile' ? 'text-white' : 'text-slate-500' }}">→</span>
                </button>

                <button 
                    type="button" 
                    wire:click="switchTab('tokens')"
                    class="flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-left {{ $activeTab === 'tokens' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">⚡</span>
                        <span>AI Tokens & Words</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono {{ $quota['percentage_used'] >= 90 ? 'bg-red-500/20 text-red-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                        {{ $quota['percentage_used'] }}%
                    </span>
                </button>

                <button 
                    type="button" 
                    wire:click="switchTab('content')"
                    class="flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-left {{ $activeTab === 'content' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">📄</span>
                        <span>My Content</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 font-mono">
                        {{ $contentStats['total_documents'] }}
                    </span>
                </button>

                <button 
                    type="button" 
                    wire:click="switchTab('byok')"
                    class="flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-left {{ $activeTab === 'byok' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">🔑</span>
                        <span>BYOK Custom Keys</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-500/20 text-indigo-300 font-mono">
                        {{ count($apiKeys) }}
                    </span>
                </button>

                <button 
                    type="button" 
                    wire:click="switchTab('connect')"
                    class="flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-left {{ $activeTab === 'connect' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">🔌</span>
                        <span>WordPress Connect Keys</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-500/20 text-emerald-300 font-mono">
                        {{ count($studioTokens) }}
                    </span>
                </button>

                <button 
                    type="button" 
                    wire:click="switchTab('preferences')"
                    class="flex items-center justify-between px-3.5 py-3 rounded-xl text-xs font-bold transition-all cursor-pointer text-left {{ $activeTab === 'preferences' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">⚙️</span>
                        <span>Studio Preferences</span>
                    </div>
                    <span class="text-[10px] {{ $activeTab === 'preferences' ? 'text-white' : 'text-slate-500' }}">→</span>
                </button>
            </div>

            <!-- Quick Account Metadata Card in Side Panel -->
            <x-glass.card variant="subtle" class="p-4 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span>Account ID</span>
                    <span class="font-mono text-white">#{{ $user->id }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400">
                    <span>Word Balance</span>
                    <span class="font-mono font-bold text-indigo-300">{{ number_format($quota['remaining_words']) }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-400">
                    <span>Subscription</span>
                    <span class="font-bold text-cyan-300 uppercase">{{ $user->plan ?? 'Starter' }}</span>
                </div>
            </x-glass.card>
        </div>

        <!-- Right Content Body Panel -->
        <div class="lg:col-span-9 space-y-6">
            <!-- ─── TAB 1: PROFILE & SECURITY ─────────────────────────────────── -->
            @if ($activeTab === 'profile')
                <div class="space-y-6">
                    <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-6">
                        <div>
                            <h2 class="text-lg font-bold text-white tracking-tight">Personal Profile & Credentials</h2>
                            <p class="text-xs text-slate-400 mt-1">Update your display name, official workspace email address, and authentication security password.</p>
                        </div>

                        <form wire:submit="updateProfile" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-300 block mb-1.5">Full Display Name</label>
                                <x-glass.input wire:model="name" type="text" placeholder="Your Name" required :error="$errors->has('name')" />
                                @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-300 block mb-1.5">Email Address</label>
                                <x-glass.input wire:model="email" type="email" placeholder="you@company.com" required :error="$errors->has('email')" />
                                @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/5 space-y-4">
                            <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                                <span>🔒 Change Password</span>
                                <span class="text-[10px] text-slate-500 font-normal lowercase">(optional, leave blank to keep existing)</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-medium text-slate-300 block mb-1.5">New Password</label>
                                    <x-glass.input wire:model="new_password" type="password" placeholder="Minimum 8 characters" :error="$errors->has('new_password')" />
                                    @error('new_password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Confirm New Password</label>
                                    <x-glass.input wire:model="new_password_confirmation" type="password" placeholder="Repeat new password" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                            <x-glass.button type="submit" variant="primary" size="md" class="shadow-lg shadow-indigo-600/30">
                                <span wire:loading.remove wire:target="updateProfile">Save Profile Changes</span>
                                <span wire:loading wire:target="updateProfile">Updating...</span>
                            </x-glass.button>
                        </div>
                    </form>
                </x-glass.card>
            </div>
    @endif

    <!-- ─── TAB 2: AI TOKENS & WORD QUOTA ─────────────────────────────── -->
    @if ($activeTab === 'tokens')
        <div class="space-y-6">
            <!-- 4 Top Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Monthly Word Quota -->
                <x-glass.card variant="elevated" class="p-5 space-y-3 border-indigo-500/30">
                    <div class="flex items-center justify-between text-xs font-mono text-slate-400">
                        <span>Monthly Words</span>
                        <span class="{{ $quota['percentage_used'] >= 90 ? 'text-red-400 font-bold' : ($quota['percentage_used'] >= 75 ? 'text-yellow-400 font-bold' : 'text-emerald-400 font-bold') }}">
                            {{ $quota['percentage_used'] }}%
                        </span>
                    </div>

                    <div>
                        <div class="text-2xl font-black text-white font-mono">
                            {{ number_format($quota['used_words']) }}
                            <span class="text-xs text-slate-400 font-normal">/ {{ number_format($quota['monthly_limit']) }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-2 bg-slate-900 rounded-full overflow-hidden border border-white/5">
                        <div 
                            class="h-full transition-all duration-500 rounded-full {{ $quota['percentage_used'] >= 90 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : ($quota['percentage_used'] >= 75 ? 'bg-gradient-to-r from-violet-500 to-amber-500' : 'bg-gradient-to-r from-cyan-500 to-emerald-400') }}"
                            style="width: {{ min(100, $quota['percentage_used']) }}%"
                        ></div>
                    </div>

                    <div class="text-[11px] text-slate-400 font-mono flex items-center justify-between">
                        <span>Remaining: <strong class="text-white">{{ number_format($quota['remaining_words']) }}</strong></span>
                        <span class="text-indigo-300 font-bold capitalize">{{ $quota['status'] }}</span>
                    </div>
                </x-glass.card>

                <!-- Card 2: Total Processed Tokens -->
                <x-glass.card variant="elevated" class="p-5 space-y-2">
                    <div class="text-xs font-mono text-slate-400">Tokens Processed</div>
                    <div class="text-2xl font-black text-cyan-300 font-mono">
                        {{ number_format($summary['total_tokens']) }}
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">
                        Across <strong class="text-white">{{ number_format($summary['total_generations']) }}</strong> AI generations
                    </div>
                </x-glass.card>

                <!-- Card 3: Direct API Equivalent Cost -->
                <x-glass.card variant="elevated" class="p-5 space-y-2">
                    <div class="text-xs font-mono text-slate-400">Estimated API Cost</div>
                    <div class="text-2xl font-black text-white font-mono">
                        ${{ number_format($summary['total_cost_usd'], 4) }}
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">
                        Direct provider market price
                    </div>
                </x-glass.card>

                <!-- Card 4: OmniRoute Efficiency Savings -->
                <x-glass.card variant="elevated" class="p-5 space-y-2 border-emerald-500/30 bg-emerald-950/20">
                    <div class="text-xs font-mono text-emerald-300">Cost Savings (vs GPT-4o)</div>
                    <div class="text-2xl font-black text-emerald-400 font-mono">
                        +${{ number_format($summary['total_savings_usd'], 4) }}
                    </div>
                    <div class="text-[11px] text-emerald-300/80 font-mono">
                        Saved via OmniRoute gateway
                    </div>
                </x-glass.card>
            </div>

            <!-- Model Usage Breakdown -->
            <x-glass.card variant="elevated" class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-tight">AI Model Consumption Distribution</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Word counts and estimated costs allocated by individual AI model routing.</p>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ count($modelBreakdown) }} models utilized</span>
                </div>

                @if(!empty($modelBreakdown))
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($modelBreakdown as $m)
                            @php
                                $mWordPct = $summary['total_words'] > 0 ? round(($m['words'] / $summary['total_words']) * 100, 1) : 0;
                            @endphp
                            <div class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 space-y-2 text-xs font-mono">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white truncate max-w-[180px]">{{ $m['model'] }}</span>
                                    <span class="text-indigo-300 font-bold">{{ $mWordPct }}%</span>
                                </div>

                                <div class="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" style="width: {{ $mWordPct }}%"></div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1">
                                    <span>{{ number_format($m['words']) }} words</span>
                                    <span>{{ number_format($m['tokens']) }} tokens</span>
                                    <span class="text-slate-300 font-bold">${{ number_format($m['cost'], 4) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-xs text-slate-500 rounded-2xl bg-slate-900/40 border border-white/5">
                        No AI completions recorded yet. Create documents or run templates to inspect model distribution.
                    </div>
                @endif
            </x-glass.card>

            <!-- Audit Trail Table -->
            <x-glass.card variant="elevated" class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white tracking-tight">Recent AI Generation Audit Trail</h3>
                    <span class="text-xs text-slate-400 font-mono">Last 15 requests</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead>
                            <tr class="text-slate-400 border-b border-white/10 text-[11px] uppercase tracking-wider">
                                <th class="pb-3 pr-4">Timestamp</th>
                                <th class="pb-3 px-4">Model Engine</th>
                                <th class="pb-3 px-4 text-right">Words</th>
                                <th class="pb-3 px-4 text-right">Tokens</th>
                                <th class="pb-3 pl-4 text-right">Cost (USD)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-slate-300">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="py-3 pr-4 text-slate-400 whitespace-nowrap">{{ $log['recorded_at'] }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-[11px]">
                                            {{ $log['model'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-white font-bold">{{ number_format($log['words']) }}</td>
                                    <td class="py-3 px-4 text-right text-slate-400">{{ number_format($log['tokens']) }}</td>
                                    <td class="py-3 pl-4 text-right text-emerald-400 font-bold">${{ number_format($log['cost_usd'], 5) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500 italic">
                                        No recent generation logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>
    @endif

    <!-- ─── TAB 3: MY CONTENT & DOCUMENTS ─────────────────────────────── -->
    @if ($activeTab === 'content')
        <div class="space-y-6">
            <!-- Content Metric Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <x-glass.card variant="elevated" class="p-4 space-y-1">
                    <span class="text-xs text-slate-400">Total Articles</span>
                    <div class="text-2xl font-bold text-white">{{ number_format($contentStats['total_documents']) }}</div>
                </x-glass.card>

                <x-glass.card variant="elevated" class="p-4 space-y-1">
                    <span class="text-xs text-slate-400">Words Generated</span>
                    <div class="text-2xl font-bold text-indigo-300 font-mono">{{ number_format($contentStats['total_words_written']) }}</div>
                </x-glass.card>

                <x-glass.card variant="elevated" class="p-4 space-y-1">
                    <span class="text-xs text-slate-400">Active Projects</span>
                    <div class="text-2xl font-bold text-purple-300">{{ number_format($contentStats['total_projects']) }}</div>
                </x-glass.card>

                <x-glass.card variant="elevated" class="p-4 space-y-1">
                    <span class="text-xs text-slate-400">Published Rate</span>
                    <div class="text-2xl font-bold text-emerald-400">
                        {{ $contentStats['total_documents'] > 0 ? round(($contentStats['published_count'] / $contentStats['total_documents']) * 100) : 0 }}%
                    </div>
                </x-glass.card>
            </div>

            <!-- Content Controls & Filter Toolbar -->
            <x-glass.card variant="subtle" class="p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="w-full sm:flex-1">
                    <x-glass.input wire:model.live.debounce.300ms="contentSearch" placeholder="Search your documents..." />
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <select wire:model.live="contentStatusFilter" class="bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="all">All Statuses</option>
                        <option value="draft">Drafts Only</option>
                        <option value="published">Published Only</option>
                    </select>

                    <select wire:model.live="contentSortBy" class="bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                        <option value="updated_at">Recently Modified</option>
                        <option value="word_count">Highest Word Count</option>
                        <option value="title">Alphabetical Title</option>
                    </select>

                    <a href="{{ route('editor') }}" wire:navigate>
                        <x-glass.button variant="primary" size="sm" class="gap-1 shadow-md shadow-indigo-600/30 whitespace-nowrap">
                            <span>+ New</span>
                        </x-glass.button>
                    </a>
                </div>
            </x-glass.card>

            <!-- Document List Table -->
            <x-glass.card variant="elevated" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-[10px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-900/60">
                                <th class="py-3 px-4">Document Title</th>
                                <th class="py-3 px-4">Project</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Words</th>
                                <th class="py-3 px-4">Last Modified</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($documents as $doc)
                                <tr class="hover:bg-white/[0.02] transition-colors group">
                                    <td class="py-3.5 px-4 font-semibold text-white">
                                        <a href="{{ route('documents.editor', $doc->id) }}" wire:navigate class="hover:text-indigo-300 transition-colors flex items-center gap-2">
                                            <span>📄</span>
                                            <span class="truncate max-w-xs sm:max-w-md">{{ $doc->title }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400">
                                        {{ $doc->project?->name ?? '—' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-glass.badge :variant="$doc->status === 'published' ? 'emerald' : 'amber'" class="text-[10px] uppercase font-mono">
                                            {{ $doc->status }}
                                        </x-glass.badge>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-300">
                                        {{ number_format($doc->word_count) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                                        {{ $doc->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('documents.editor', $doc->id) }}" wire:navigate class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 hover:text-white font-bold transition-all">
                                                Edit
                                            </a>
                                            <button 
                                                type="button" 
                                                wire:click="deleteDocument({{ $doc->id }})" 
                                                wire:confirm="Are you sure you want to trash this document?"
                                                class="p-1 text-slate-500 hover:text-red-400 transition-colors cursor-pointer"
                                                title="Delete Document"
                                            >
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <div class="space-y-2">
                                            <span class="text-3xl">📝</span>
                                            <div class="text-sm font-semibold text-slate-400">No documents found</div>
                                            <p class="text-xs text-slate-500">Try changing your search query or create a new document in the AI text editor.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($documents->hasPages())
                    <div class="p-4 border-t border-white/5">
                        {{ $documents->links() }}
                    </div>
                @endif
            </x-glass.card>
        </div>
    @endif

    <!-- ─── TAB 4: BYOK CUSTOM API KEYS ───────────────────────────────── -->
    @if ($activeTab === 'byok')
        <div class="space-y-6">
            <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>🔑 Bring Your Own Key (BYOK) & Custom Endpoints</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono font-bold uppercase">
                                AES-256-GCM Encrypted
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Store your personal API credentials for OpenAI, DeepSeek, Anthropic, or local Ollama instances. <strong>Supplying a BYOK key bypasses all monthly platform word limits.</strong>
                        </p>
                    </div>
                </div>

                <!-- Connect Key Form -->
                @if($allowedProviders->isNotEmpty())
                    <form wire:submit="saveApiKey" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">AI Provider Engine</label>
                            <select wire:model="byok_provider" class="w-full bg-slate-950 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-medium">
                                @foreach($allowedProviders as $ap)
                                    <option value="{{ $ap->slug }}">{{ $ap->name }} ({{ $ap->slug }})</option>
                                @endforeach
                                <option value="custom">Custom Endpoint (Ollama / vLLM / Local)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Secret Key / Bearer Token</label>
                            <input 
                                type="password" 
                                wire:model="byok_api_key" 
                                placeholder="sk-..." 
                                class="w-full bg-slate-950 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
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
                                    class="w-full bg-slate-950 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                                />
                            </div>
                            <button 
                                type="submit" 
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer shrink-0"
                            >
                                Register Key
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
                                <th class="py-2.5 px-3">Endpoint URL</th>
                                <th class="py-2.5 px-3">Encrypted API Key</th>
                                <th class="py-2.5 px-3">Quota Mode</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($apiKeys as $key)
                                @php
                                    $isVisible = in_array($key->id, $visibleKeys, true);
                                    $rawKey = $key->getRawKeyForOwner($user);
                                @endphp
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-white uppercase font-mono">{{ $key->provider_slug }}</span>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-[11px] text-slate-400">
                                        {{ $key->custom_base_url ?: 'Default OmniRoute Cloud Gateway' }}
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
                                    <td colspan="5" class="py-8 text-center text-slate-500">
                                        No custom BYOK keys registered. Platform shared gateway and plan word quotas are currently active.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>
    @endif

    <!-- ─── TAB 5: WORDPRESS & STUDIO CONNECT KEYS ─────────────────── -->
    @if ($activeTab === 'connect')
        <div class="space-y-6">
            <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span>🔌 Studio Connect Keys (WordPress / Desktop / External API)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-mono font-bold uppercase">
                                SHA-256 Scoped Auth
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Generate unique personal connect keys to bridge HOA-Studio's TipTap editor, AI streaming, and custom templates into your WordPress websites.
                        </p>
                    </div>
                </div>

                <!-- Newly Generated Plaintext Token Alert -->
                @if($generatedPlainTextToken)
                    <div class="p-5 rounded-2xl bg-emerald-950/60 border border-emerald-500/50 space-y-3">
                        <div class="flex items-center gap-2 text-emerald-300 font-bold text-xs">
                            <span class="text-base">🎉</span>
                            <span>Your New Studio Connect Key Has Been Generated!</span>
                        </div>
                        <div class="p-3 rounded-xl bg-black/80 border border-emerald-500/30 flex items-center justify-between gap-3">
                            <code class="font-mono text-xs text-emerald-400 select-all break-all">{{ $generatedPlainTextToken }}</code>
                            <button 
                                type="button" 
                                onclick="navigator.clipboard.writeText('{{ $generatedPlainTextToken }}'); this.innerText = 'Copied!';"
                                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors shrink-0 cursor-pointer"
                            >
                                Copy Key
                            </button>
                        </div>
                        <p class="text-[11px] text-emerald-300/80">
                            <strong>Important:</strong> Copy this key and paste it into your WordPress HOA-Studio Plugin settings. It will not be shown in full again for security.
                        </p>
                    </div>
                @endif

                <!-- Generate New Key Form -->
                <form wire:submit.prevent="generateStudioToken" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-900/80 border border-white/10">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Integration / Site Label</label>
                        <input 
                            type="text" 
                            wire:model="newTokenName" 
                            placeholder="e.g. My Tech Blog (WordPress)" 
                            class="w-full bg-slate-950 border border-white/15 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-medium disabled:opacity-50"
                            wire:loading.attr="disabled"
                            wire:target="generateStudioToken"
                            required
                        />
                        @error('newTokenName') <p class="text-[10px] text-red-400 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            wire:target="generateStudioToken"
                            class="w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-60 disabled:cursor-not-allowed text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer flex items-center justify-center gap-2"
                        >
                            <span wire:loading.remove wire:target="generateStudioToken">+ Generate Connect Key</span>
                            <span wire:loading wire:target="generateStudioToken" class="inline-flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Generating Secure Key...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Active Connect Keys Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-white/10 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                <th class="py-2.5 px-3">Site / Client Name</th>
                                <th class="py-2.5 px-3">Connected Domain / Origin</th>
                                <th class="py-2.5 px-3">Key Identifier</th>
                                <th class="py-2.5 px-3">Created</th>
                                <th class="py-2.5 px-3">Last Active</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($studioTokens as $token)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-3 font-semibold text-white">
                                        <div class="flex items-center gap-1.5">
                                            <span>🌐</span>
                                            <span>{{ $token->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-[11px]">
                                        @if($token->connected_domain)
                                            <span class="inline-flex items-center gap-1 text-cyan-300 bg-cyan-950/60 px-2 py-0.5 rounded border border-cyan-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                                                {{ $token->connected_domain }}
                                            </span>
                                        @else
                                            <span class="text-slate-500 italic">Waiting for 1st connect...</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 font-mono text-[11px] text-indigo-300">
                                        {{ $token->token_prefix }}••••••••
                                    </td>
                                    <td class="py-3 px-3 text-slate-400 text-[11px]">
                                        {{ $token->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-3 px-3 text-slate-400 text-[11px]">
                                        {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 font-bold uppercase">
                                            ACTIVE
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <button 
                                            type="button" 
                                            wire:click="deleteStudioToken({{ $token->id }})" 
                                            wire:confirm="Revoke this Studio Connect Key? The connected WordPress site will no longer be able to stream AI."
                                            class="text-[11px] text-red-400 hover:text-red-300 font-semibold cursor-pointer"
                                        >
                                            Revoke
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-500">
                                        No active Studio Connect keys. Generate a key above to link your WordPress sites.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-glass.card>
        </div>
    @endif

    <!-- ─── TAB 6: STUDIO PREFERENCES ─────────────────────────────────── -->
    @if ($activeTab === 'preferences')
        <div class="max-w-3xl space-y-6">
            <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-white tracking-tight">Editor & Studio Work Environment</h2>
                    <p class="text-xs text-slate-400 mt-1">Configure your default AI model router, active editor engine, and semantic caching durations.</p>
                </div>

                <form wire:submit="updatePreferences" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Default AI Generation Model</label>
                            <select wire:model="default_model" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option>OmniRoute: DeepSeek-V3</option>
                                <option>OmniRoute: Claude 3.7 Sonnet</option>
                                <option>OmniRoute: OpenAI GPT-4o</option>
                                <option>OmniRoute: Local Ollama (vLLM)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Vector RAG Cache Duration</label>
                            <select wire:model="embedding_cache_days" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="1">1 Day (Frequent updates)</option>
                                <option value="7">7 Days (Recommended standard)</option>
                                <option value="30">30 Days (Maximum speed & token saving)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Caches semantic vector queries in Knowledge Base & RAG.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-white/5">
                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Default Editor Canvas Driver</label>
                            <select wire:model="default_editor_engine" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="tiptap">TipTap ProseMirror (Recommended Enterprise Standard)</option>
                                <option value="gutenberg">Gutenberg Block Canvas</option>
                                <option value="notion">Notion Slash Driver</option>
                                <option value="markdown">Pure Markdown Streamer</option>
                            </select>
                        </div>

                        <div class="space-y-3 pt-2">
                            <label class="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                                <input type="checkbox" wire:model="auto_seo_audit" class="rounded border-white/20 bg-slate-900 text-indigo-600 focus:ring-indigo-500" />
                                <span>Enable real-time automatic SEO & Readability scoring</span>
                            </label>

                            <label class="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                                <input type="checkbox" wire:model="email_notifications" class="rounded border-white/20 bg-slate-900 text-indigo-600 focus:ring-indigo-500" />
                                <span>Receive security and quota notification emails</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-white/5">
                        <x-glass.button type="submit" variant="primary" size="md">
                            <span wire:loading.remove wire:target="updatePreferences">Save Preferences</span>
                            <span wire:loading wire:target="updatePreferences">Saving...</span>
                        </x-glass.button>
                    </div>
                </form>
            </x-glass.card>
        </div>
    @endif
        </div>
    </div>
</div>
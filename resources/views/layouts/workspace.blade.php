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

<x-layouts.app :title="$title ?? 'Workspace — HelpOfAi Studio'">
    <div 
        class="min-h-screen flex bg-slate-950 text-slate-100 selection:bg-indigo-500/30 selection:text-indigo-200" 
        x-data="{ 
            sidebarOpen: false, 
            collapsed: false,
            init() {
                try {
                    const saved = localStorage.getItem('hoa_sidebar_collapsed');
                    if (saved !== null) {
                        this.collapsed = JSON.parse(saved);
                    }
                } catch(e) {}
            },
            toggleCollapse() {
                this.collapsed = !this.collapsed;
                try {
                    localStorage.setItem('hoa_sidebar_collapsed', JSON.stringify(this.collapsed));
                } catch(e) {}
            }
        }"
    >
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-on:click="sidebarOpen = false"
            class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;"
        ></div>

        <!-- Sleek Collapsible Sidebar Navigation -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 glass-standard border-r border-white/10 flex flex-col justify-between transition-[width,transform] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] lg:static lg:z-0 select-none shrink-0"
            :class="{
                'w-64': !collapsed,
                'w-20': collapsed,
                'translate-x-0': sidebarOpen,
                '-translate-x-full lg:translate-x-0': !sidebarOpen
            }"
        >
            <div class="flex flex-col h-full overflow-hidden">
                <!-- Brand Header -->
                <div class="h-16 px-4 flex items-center border-b border-white/5 shrink-0 transition-all duration-300" :class="collapsed ? 'justify-center' : 'justify-between'">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 overflow-hidden group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-cyan-400 p-[1px] shadow-md shadow-indigo-500/20 group-hover:scale-105 group-hover:shadow-indigo-500/40 transition-all duration-200 shrink-0">
                            <div class="w-full h-full bg-slate-950 rounded-[11px] flex items-center justify-center font-black text-xs text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300">
                                HOA
                            </div>
                        </div>
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate min-w-0 overflow-hidden whitespace-nowrap"
                        >
                            <span class="text-sm font-bold text-white tracking-tight block truncate">HelpOfAi Studio</span>
                            <span class="block text-[10px] text-indigo-400 font-medium leading-none truncate">AI Content Engine</span>
                        </div>
                    </a>

                    <!-- Mobile Close Button -->
                    <button x-on:click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 hover:scale-110 active:scale-95 transition-all cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Navigation Links List (SPA Mode with wire:navigate) -->
                <nav class="p-3 space-y-1 text-xs font-medium overflow-y-auto flex-1 scrollbar-none">
                    <!-- Core Navigation Section -->
                    <a 
                        href="{{ route('dashboard') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">📊</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Dashboard</span>

                        <!-- Floating Tooltip in Collapsed Mode -->
                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Dashboard
                        </div>
                    </a>

                    <a 
                        href="{{ route('documents.index') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('documents.*') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">📄</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Documents</span>

                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Documents
                        </div>
                    </a>

                    <a 
                        href="{{ route('projects.index') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('projects.*') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">📁</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Projects</span>

                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Projects
                        </div>
                    </a>

                    <!-- Section Divider: AI Workflows -->
                    <div 
                        x-show="!collapsed" 
                        x-transition:enter="transition-all duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider"
                    >
                        AI Workflows
                    </div>
                    <div x-show="collapsed" class="py-1.5 my-1 border-t border-white/5"></div>

                    <a 
                        href="{{ route('templates.index') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('templates.*') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">⚡</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Templates Hub</span>

                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Templates Hub
                        </div>
                    </a>

                    <a 
                        href="{{ route('brand-voices.index') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('brand-voices.*') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🎭</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Brand Voices</span>

                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Brand Voices
                        </div>
                    </a>

                    <a 
                        href="{{ route('knowledge-base.index') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('knowledge-base.*') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🧠</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Knowledge Base</span>

                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Knowledge Base
                        </div>
                    </a>

                    <a 
                        href="{{ route('usage.index') }}" 
                        wire:navigate
                        class="flex items-center rounded-xl transition-all duration-200 group relative {{ request()->routeIs('usage.*') ? 'bg-gradient-to-r from-indigo-600/25 to-purple-600/15 text-indigo-200 border border-indigo-500/40 font-semibold shadow-md shadow-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                    >
                        <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">📈</span>
                        <span 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate overflow-hidden whitespace-nowrap"
                        >Usage & Quotas</span>

                        <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                            Usage & Quotas
                        </div>
                    </a>

                    <!-- Section Divider: Administration (Admins Only) -->
                    @if(auth()->user()->isAdmin())
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="pt-4 pb-1 px-3 text-[10px] font-bold text-violet-400 uppercase tracking-wider"
                        >
                            Administration
                        </div>
                        <div x-show="collapsed" class="py-1.5 my-1 border-t border-white/5"></div>

                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            wire:navigate
                            class="flex items-center rounded-xl bg-gradient-to-r from-violet-600/20 to-purple-600/10 text-violet-300 border border-violet-500/30 font-semibold hover:border-violet-400 shadow-lg shadow-violet-500/10 transition-all duration-200 group relative"
                            :class="collapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5'"
                        >
                            <span class="text-base shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🛡️</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap"
                            >Admin Dashboard</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                Admin Dashboard
                            </div>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Sidebar Footer / Quota Balance & User Profile -->
            <div class="p-3 border-t border-white/5 space-y-3 shrink-0">
                @php
                $u = auth()->user();
                $rem = max(0, ($u->monthly_word_quota ?? 15000) - ($u->used_word_quota ?? 0));
                $pct = ($u->monthly_word_quota ?? 15000) > 0 ? min(100, round((($u->used_word_quota ?? 0) / ($u->monthly_word_quota ?? 15000)) * 100)) : 0;
                @endphp

                <!-- Expanded Quota Card -->
                <div 
                    x-show="!collapsed" 
                    x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition-all duration-150 ease-in"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="p-3 rounded-xl glass-subtle border border-white/5 text-xs"
                >
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] text-slate-400 font-medium">Monthly Word Quota</span>
                        <x-glass.badge variant="cyan" class="text-[9px] py-0 px-1.5 font-bold uppercase">{{ $u->plan ?? 'Starter' }}</x-glass.badge>
                    </div>
                    <div class="w-full bg-slate-900 rounded-full h-1.5 overflow-hidden mb-1.5">
                        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400 h-1.5 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                        <span>{{ number_format($rem) }} left</span>
                        <span class="font-bold text-white">{{ $pct }}%</span>
                    </div>
                </div>

                <!-- Collapsed Quota Mini Indicator -->
                <div 
                    x-show="collapsed" 
                    x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="flex justify-center relative group"
                >
                    <a href="{{ route('usage.index') }}" wire:navigate class="w-10 h-10 rounded-xl bg-slate-900 border border-white/10 hover:border-indigo-500/40 flex flex-col items-center justify-center transition-all duration-200 cursor-pointer group-hover:scale-105">
                        <span class="text-xs">⚡</span>
                        <span class="text-[8px] font-mono text-indigo-300 font-bold">{{ $pct }}%</span>
                    </a>

                    <div class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                        Quota: {{ number_format($rem) }} words left ({{ $pct }}%)
                    </div>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center justify-between gap-2 pt-1">
                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2.5 overflow-hidden group flex-1" :class="collapsed ? 'justify-center' : ''">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-xs font-bold text-white shrink-0 group-hover:border-indigo-400 group-hover:scale-105 transition-all duration-200 shadow-sm">
                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                        </div>
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-300 ease-out"
                            x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                            x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave="transition-all duration-150 ease-in"
                            x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                            x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                            class="truncate min-w-0 overflow-hidden whitespace-nowrap"
                        >
                            <div class="text-xs font-medium text-white truncate">{{ $u->name }}</div>
                            <div class="text-[10px] text-indigo-400 capitalize truncate">{{ $u->role ?? 'user' }}</div>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" x-show="!collapsed">
                        @csrf
                        <button type="submit" title="Log Out" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-white/5 hover:scale-110 active:scale-95 transition-all duration-150 cursor-pointer">
                            🚪
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Dynamic Workspace Surface -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Advanced Dynamic Top Header -->
            <header class="h-16 border-b border-white/5 bg-slate-950/70 backdrop-blur-xl px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 shadow-lg shadow-black/20">
                <!-- Left Navigation Segment & Smart Breadcrumbs -->
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Mobile Hamburger -->
                    <button x-on:click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-white/5 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                        ☰
                    </button>

                    <!-- Desktop Toggle Button in Top Header (Linear / VS Code style) -->
                    <button 
                        type="button" 
                        x-on:click="toggleCollapse()" 
                        class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg bg-slate-900/80 border border-white/10 hover:border-indigo-500/40 text-slate-400 hover:text-white hover:scale-105 active:scale-95 transition-all duration-150 cursor-pointer shadow-sm"
                        :title="collapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                    >
                        <svg class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </button>

                    <!-- Breadcrumb Hierarchy -->
                    <div class="flex items-center gap-2 text-xs text-slate-400 truncate">
                        <span class="font-bold text-white text-sm tracking-tight truncate">HelpOfAi Studio</span>
                        <span>/</span>
                        @php
                            $seg1 = request()->segment(1);
                            $seg2 = request()->segment(2);
                            $featureLabel = match($seg2) {
                                'documents' => 'Documents',
                                'projects' => 'Projects',
                                'templates' => 'Templates Hub',
                                'brand-voices' => 'Brand Voices',
                                'knowledge-base' => 'Knowledge Base',
                                'usage' => 'Usage & Quotas',
                                'profile' => 'User Profile',
                                default => ($seg1 === 'admin' ? 'Admin Panel' : 'Dashboard')
                            };
                        @endphp
                        <span class="text-indigo-300 font-semibold truncate">
                            {{ $featureLabel }}
                        </span>
                    </div>
                </div>

                <!-- Right Action Stack (Dynamic & Role Aware) -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Live Quota Balance Pill -->
                    <a href="{{ route('usage.index') }}" wire:navigate class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl glass-subtle border border-white/10 hover:border-indigo-500/40 text-xs text-slate-300 hover:text-white transition-all shadow-inner group">
                        <span class="text-indigo-400 group-hover:scale-110 transition-transform">⚡</span>
                        <span class="font-mono font-bold text-white">{{ number_format($rem) }}</span>
                        <span class="text-[10px] text-slate-400">words left</span>
                    </a>

                    <!-- Admin Dashboard Button (Only for Admins) -->
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" wire:navigate class="inline-flex">
                            <x-glass.button variant="glass" size="sm" class="border-violet-500/40 text-violet-300 hover:bg-violet-600/20 shadow-md shadow-violet-500/10 gap-1.5 font-bold">
                                <span>🛡️</span>
                                <span class="hidden md:inline">Admin Panel</span>
                            </x-glass.button>
                        </a>
                    @endif

                    <!-- Create New Document Button -->
                    <a href="{{ route('documents.index') }}" wire:navigate>
                        <x-glass.button variant="primary" size="sm" class="shadow-lg shadow-indigo-500/25 gap-1.5">
                            <span>✨</span>
                            <span class="hidden sm:inline">New Document</span>
                        </x-glass.button>
                    </a>

                    <!-- User Profile Avatar Pill -->
                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 p-1 rounded-xl glass-subtle hover:border-indigo-500/40 transition-all border border-white/5" title="View Profile & Quotas">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-xs text-white shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                </div>
            </header>

            <!-- Dynamic Page Body (Swapped via wire:navigate without full reload) -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>
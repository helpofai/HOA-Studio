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

<x-layouts.app :title="$title ?? 'Admin Control Center — HelpOfAi Studio'">
    <div 
        class="min-h-screen flex text-slate-100 selection:bg-violet-500/30 selection:text-violet-200" 
        x-data="{ 
            sidebarOpen: false, 
            collapsed: false,
            init() {
                try {
                    const saved = localStorage.getItem('hoa_admin_sidebar_collapsed');
                    if (saved !== null) {
                        this.collapsed = JSON.parse(saved);
                    }
                } catch(e) {}
            },
            toggleCollapse() {
                this.collapsed = !this.collapsed;
                try {
                    localStorage.setItem('hoa_admin_sidebar_collapsed', JSON.stringify(this.collapsed));
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

        <!-- Collapsible Admin Sidebar (Fixed Viewport Height) -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 glass-standard border-r border-violet-500/20 flex flex-col justify-between transition-[width,transform] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] lg:sticky lg:top-0 lg:h-screen lg:z-30 select-none shrink-0 overflow-hidden"
            :class="{
                'w-64': !collapsed,
                'w-20': collapsed,
                'translate-x-0': sidebarOpen,
                '-translate-x-full lg:translate-x-0': !sidebarOpen
            }"
        >
            <div class="flex flex-col h-full overflow-hidden min-h-0">
                <!-- Brand Header -->
                <div class="h-16 px-4 flex items-center border-b border-white/5 bg-gradient-to-r from-violet-950/40 to-transparent shrink-0 transition-all duration-300" :class="collapsed ? 'justify-center' : 'justify-between'">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3 overflow-hidden group">
                        <x-glass.logo size="sm" text="HOA" subtitle="ADMIN" />
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
                            <span class="text-sm font-bold text-white tracking-tight block truncate">HOA Admin</span>
                            <span class="block text-[10px] text-violet-400 font-medium leading-none truncate">Control Center</span>
                        </div>
                    </a>

                    <!-- Mobile Close Button -->
                    <button x-on:click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 hover:scale-110 active:scale-95 transition-all cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Navigation Links List (SPA Mode with wire:navigate) -->
                <nav class="sidebar-nav-menu p-3 space-y-2.5 overflow-y-auto overflow-x-hidden flex-1 scrollbar-none">
                                     <!-- 1. MANAGEMENT SECTION -->
                    <div class="sidebar-nav-section space-y-0.5">
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="px-3 pt-2 pb-1 text-[9.5px] font-bold text-slate-500 uppercase tracking-widest flex items-center justify-between"
                        >
                            <span>Management</span>
                        </div>
                        <div x-show="collapsed" class="py-0.5"></div>

                        <!-- System Overview -->
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">📊</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >Overview</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                System Overview
                            </div>
                        </a>

                        <!-- Users & Quotas -->
                        <a 
                            href="{{ route('admin.users') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.users') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">👥</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >Users & Quotas</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                Users & Quotas
                            </div>
                        </a>

                        <!-- AI Usage & Logs -->
                        <a 
                            href="{{ route('admin.usage') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.usage') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">📈</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >AI Usage & Logs</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                AI Usage & Logs
                            </div>
                        </a>
                    </div>

                    <!-- 2. AI INFRASTRUCTURE & TOOLS SECTION -->
                    <div class="sidebar-nav-section space-y-0.5 pt-1">
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="px-3 pt-2 pb-1 text-[9.5px] font-bold text-slate-500 uppercase tracking-widest flex items-center justify-between"
                        >
                            <span>AI Infrastructure</span>
                        </div>
                        <div x-show="collapsed" class="py-1 my-0.5 border-t border-white/5"></div>

                        <!-- AI Providers & Gateway -->
                        <a 
                            href="{{ route('admin.ai-settings.index') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.ai-settings.index') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">⚡</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >AI Providers & Models</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                AI Providers & Models
                            </div>
                        </a>

                        <!-- OmniRoute Config -->
                        <a 
                            href="{{ route('admin.ai-settings.omniroute') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.ai-settings.omniroute') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🤖</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >OmniRoute Gateway</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                OmniRoute Gateway
                            </div>
                        </a>
                    </div>

                    <!-- 3. GOVERNANCE & SETTINGS SECTION -->
                    <div class="sidebar-nav-section space-y-0.5 pt-1">
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="px-3 pt-2 pb-1 text-[9.5px] font-bold text-slate-500 uppercase tracking-widest flex items-center justify-between"
                        >
                            <span>Governance & Safety</span>
                        </div>
                        <div x-show="collapsed" class="py-1 my-0.5 border-t border-white/5"></div>

                        <!-- System Settings -->
                        <a 
                            href="{{ route('admin.settings') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.settings') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">⚙️</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >System Settings</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                System Settings
                            </div>
                        </a>

                        <!-- System Info & Docs -->
                        <a 
                            href="{{ route('admin.system-info') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.system-info') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">ℹ️</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >System Info & Docs</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                System Info & Docs
                            </div>
                        </a>

                        <!-- Core Updates & Time Machine -->
                        <a 
                            href="{{ route('admin.updates') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative {{ request()->routeIs('admin.updates') ? 'bg-gradient-to-r from-violet-600/25 to-indigo-600/15 text-violet-200 border border-violet-500/40 font-semibold shadow-md shadow-violet-500/10' : 'text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5' }}"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🔄</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >Core Updates & Rollback</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                Core Updates & Rollback
                            </div>
                        </a>

                        <!-- Circuit Breaker & Safety -->
                        <a 
                            href="{{ route('admin.ai-settings.index') }}#circuit-breaker" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-all duration-200 group relative text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🛡️</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >Circuit Breaker</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                Circuit Breaker & BYOK
                            </div>
                        </a>
                    </div>

                    <!-- 4. QUICK SWITCH -->
                    <div class="sidebar-nav-section pt-1">
                        <div 
                            x-show="!collapsed" 
                            x-transition:enter="transition-all duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="px-3 pt-2 pb-1 text-[9.5px] font-bold text-slate-500 uppercase tracking-widest"
                        >
                            Quick Switch
                        </div>
                        <div x-show="collapsed" class="py-1 my-0.5 border-t border-white/5"></div>

                        <a 
                            href="{{ route('dashboard') }}" 
                            wire:navigate.hover
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-slate-300 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/5 transition-all duration-200 group relative"
                        >
                            <span class="w-8 h-8 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 group-active:scale-95 transition-transform duration-200">🚀</span>
                            <span 
                                x-show="!collapsed" 
                                x-transition:enter="transition-all duration-300 ease-out"
                                x-transition:enter-start="opacity-0 -translate-x-2 max-w-0"
                                x-transition:enter-end="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave="transition-all duration-150 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 max-w-[180px]"
                                x-transition:leave-end="opacity-0 -translate-x-2 max-w-0"
                                class="truncate overflow-hidden whitespace-nowrap text-[13.5px]"
                            >Studio Workspace</span>

                            <div x-show="collapsed" class="absolute left-full ml-3 px-2.5 py-1 rounded-lg bg-slate-900/95 border border-white/15 text-white text-xs font-semibold shadow-xl backdrop-blur-md opacity-0 pointer-events-none group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-50 whitespace-nowrap">
                                Studio Workspace
                            </div>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-white/5 flex items-center justify-between gap-2">
                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2.5 overflow-hidden group flex-1" :class="collapsed ? 'justify-center' : ''">
                    <div class="w-8 h-8 rounded-lg bg-violet-900/60 border border-violet-500/30 flex items-center justify-center text-xs font-bold text-violet-200 shrink-0 group-hover:border-violet-400 group-hover:scale-105 transition-all duration-200 shadow-sm">
                        🛡️
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
                        <div class="text-xs font-medium text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-violet-400 font-semibold uppercase">Admin</div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" x-show="!collapsed">
                    @csrf
                    <button type="submit" title="Log Out" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-white/5 hover:scale-110 active:scale-95 transition-all duration-150 cursor-pointer">
                        🚪
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Surface -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar -->
            <header class="h-16 border-b border-white/5 bg-slate-950/70 backdrop-blur-xl px-4 sm:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 shadow-lg shadow-black/20">
                <div class="flex items-center gap-3 min-w-0">
                    <button x-on:click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-white/5 hover:scale-105 active:scale-95 transition-all cursor-pointer">
                        ☰
                    </button>

                    <!-- Desktop Toggle Button in Top Header (Linear / VS Code style, outside sidebar flex) -->
                    <button 
                        type="button" 
                        x-on:click="toggleCollapse()" 
                        class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg bg-slate-900/80 border border-white/10 hover:border-violet-500/40 text-slate-400 hover:text-white hover:scale-105 active:scale-95 transition-all duration-150 cursor-pointer shadow-sm"
                        :title="collapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                    >
                        <svg class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-2 truncate">
                        <span class="text-xs px-2 py-0.5 rounded-md bg-violet-500/20 text-violet-300 font-mono font-semibold border border-violet-500/30">ADMIN</span>
                        <span class="text-sm font-semibold text-white truncate">HelpOfAi Control Center</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Global Floating Console Trigger Button -->
                    <button 
                        type="button"
                        x-on:click="$dispatch('toggle-omni-terminal')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900/80 hover:bg-slate-800 border border-violet-500/30 text-xs font-mono text-violet-300 hover:text-white shadow-sm hover:border-violet-500/50 transition-all cursor-pointer"
                        title="Toggle Global Floating Console Logs Terminal"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>🖥️ Console</span>
                    </button>

                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-glass.button variant="glass" size="sm" class="border-indigo-500/30 text-indigo-300 hover:bg-indigo-600/20 gap-1.5">
                            <span>🚀</span>
                            <span class="hidden sm:inline">Return to Studio</span>
                        </x-glass.button>
                    </a>

                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 p-1 rounded-xl glass-subtle hover:border-violet-500/40 transition-all border border-white/5" title="Admin Profile">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center font-bold text-xs text-white shadow-sm">
                            🛡️
                        </div>
                    </a>
                </div>
            </header>

            <!-- Page Body (Swapped dynamically with wire:navigate without full reload) -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                {{ $slot }}
            </main>
        </div>

        <!-- ========================================================================= -->
        <!-- GLOBAL PERSISTENT DRAGGABLE & MINIMIZABLE OMNIROUTE CONSOLE TERMINAL      -->
        <!-- ========================================================================= -->
        <div 
            x-data="floatingOmniTerminal()"
            x-on:toggle-omni-terminal.window="toggleOpen()"
            x-on:open-omni-terminal.window="openTerminal()"
            x-cloak
        >
            <!-- 1. FLOATING MINIMIZED PILL (Bottom Right) -->
            <div 
                x-show="isOpen && isMinimized" 
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="fixed bottom-5 right-6 z-50 flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-slate-950/90 border border-violet-500/40 shadow-2xl backdrop-blur-xl cursor-pointer hover:border-violet-400 group transition-all"
                x-on:click="restore()"
                title="Click to restore OmniRoute Console"
                style="display: none;"
            >
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse"></div>
                <div class="flex items-center gap-2 font-mono text-xs">
                    <span class="font-bold text-white group-hover:text-violet-200">🖥️ OmniRoute Console</span>
                    <span class="px-1.5 py-0.2 rounded bg-violet-950 text-violet-300 text-[10px] border border-violet-500/30" x-text="logs.length + ' logs'"></span>
                </div>
                <button type="button" x-on:click.stop="close()" class="text-slate-500 hover:text-red-400 p-0.5 ml-1 text-xs" title="Close">✕</button>
            </div>

            <!-- 2. DRAGGABLE FLOATING POPUP TERMINAL WINDOW -->
            <div 
                x-show="isOpen && !isMinimized"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-ref="terminalWindow"
                :style="isMaximized ? 'position: fixed; inset: 24px; z-index: 50;' : 'position: fixed; left: ' + posX + 'px; top: ' + posY + 'px; width: ' + width + 'px; z-index: 50;'"
                class="flex flex-col bg-[#0d1117]/95 border border-[#30363d] rounded-2xl shadow-[0_25px_70px_rgba(0,0,0,0.85)] ring-1 ring-white/10 backdrop-blur-2xl overflow-hidden font-mono text-xs select-none"
                style="display: none;"
            >
                <!-- macOS Interactive Window Titlebar (Drag Handle) -->
                <div 
                    class="h-10 px-4 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between cursor-move shrink-0"
                    x-on:mousedown="startDrag($event)"
                    x-on:touchstart="startTouchDrag($event)"
                >
                    <!-- Window Controls (Red / Yellow / Green Dots) -->
                    <div class="flex items-center gap-2">
                        <!-- Red Dot (Close completely) -->
                        <button 
                            type="button" 
                            x-on:click.stop="close()" 
                            class="w-3 h-3 rounded-full bg-[#FF5F56] border border-[#E0443E] flex items-center justify-center text-[8px] font-bold text-black/70 hover:opacity-100 opacity-90 transition-all cursor-pointer group"
                            title="Close Terminal (X)"
                        >
                            <span class="opacity-0 group-hover:opacity-100 font-sans leading-none">✕</span>
                        </button>

                        <!-- Yellow Dot (Minimize to bottom pill) -->
                        <button 
                            type="button" 
                            x-on:click.stop="minimize()" 
                            class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-[#DEA123] flex items-center justify-center text-[8px] font-bold text-black/70 hover:opacity-100 opacity-90 transition-all cursor-pointer group"
                            title="Minimize Terminal (—)"
                        >
                            <span class="opacity-0 group-hover:opacity-100 font-sans leading-none">−</span>
                        </button>

                        <!-- Green Dot (Maximize / Restore Size) -->
                        <button 
                            type="button" 
                            x-on:click.stop="toggleMaximize()" 
                            class="w-3 h-3 rounded-full bg-[#27C93F] border border-[#1AAB29] flex items-center justify-center text-[8px] font-bold text-black/70 hover:opacity-100 opacity-90 transition-all cursor-pointer group"
                            title="Maximize / Restore (+)"
                        >
                            <span class="opacity-0 group-hover:opacity-100 font-sans leading-none">+</span>
                        </button>

                        <span class="ml-3 text-[#8b949e] text-[11px] font-medium tracking-wide">
                            OmniRoute — Application Console Output
                        </span>
                    </div>

                    <!-- Window Stats & Live Pulse -->
                    <div class="flex items-center gap-2 text-[10px] text-slate-400">
                        <span class="inline-flex items-center gap-1 text-emerald-400 font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            LIVE
                        </span>
                        <span class="text-slate-600">•</span>
                        <span class="font-mono text-cyan-300/80" x-text="logs.length + ' entries'"></span>
                    </div>
                </div>

                <!-- Terminal Controls Toolbar -->
                <div class="px-3.5 py-2.5 bg-[#161b22]/70 border-b border-[#30363d] flex flex-wrap items-center gap-2 select-text shrink-0">
                    <!-- Level Filter Dropdown -->
                    <select 
                        x-model="levelFilter" 
                        class="bg-[#0d1117] border border-[#30363d] rounded-lg px-2.5 py-1 text-[11px] text-slate-200 focus:outline-none focus:border-cyan-500 font-mono"
                    >
                        <option value="all">All Levels</option>
                        <option value="debug">Debug+</option>
                        <option value="info">Info+</option>
                        <option value="warn">Warn+</option>
                        <option value="error">Error+</option>
                    </select>

                    <!-- Realtime Search Box -->
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Search logs (message, component, cid)..." 
                        class="flex-1 min-w-[150px] bg-[#0d1117] border border-[#30363d] rounded-lg px-2.5 py-1 text-[11px] font-mono text-slate-200 placeholder-[#8b949e] focus:outline-none focus:border-cyan-500"
                    />

                    <!-- Auto-Scroll Toggle Button -->
                    <button 
                        type="button" 
                        x-on:click="autoScroll = !autoScroll" 
                        :class="autoScroll ? 'bg-cyan-500/15 text-cyan-400 border-cyan-500/30 font-semibold' : 'bg-[#0d1117] text-slate-400 border-[#30363d]'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-mono transition-colors cursor-pointer"
                        title="Toggle Auto-Scroll to bottom on new logs"
                    >
                        <span x-text="autoScroll ? '⬇ Auto-Scroll: ON' : '⏸ Auto-Scroll: OFF'"></span>
                    </button>

                    <!-- Manual Refresh Button -->
                    <button 
                        type="button" 
                        x-on:click="fetchLogs()" 
                        class="p-1 rounded-lg bg-[#0d1117] border border-[#30363d] text-slate-300 hover:text-white transition-colors cursor-pointer text-xs" 
                        title="Refresh Logs Immediately"
                    >
                        🔄
                    </button>

                    <!-- Clear Log Stream Button -->
                    <button 
                        type="button" 
                        x-on:click="clearLogs()" 
                        class="p-1 rounded-lg bg-[#0d1117] border border-[#30363d] text-slate-400 hover:text-red-400 transition-colors cursor-pointer text-xs" 
                        title="Clear Buffer"
                    >
                        🧹
                    </button>
                </div>

                <!-- Log Stream Lines Window -->
                <div 
                    x-ref="logContainer"
                    class="p-3 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-[#30363d] text-[11px] leading-relaxed flex-1 select-text"
                    :style="isMaximized ? 'height: calc(100vh - 170px);' : 'height: ' + height + 'px;'"
                >
                    <template x-for="(entry, index) in filteredLogs" :key="index">
                        <div 
                            class="group flex items-start gap-2 px-1.5 py-0.5 rounded hover:bg-white/5 transition-colors"
                            :class="entry.level === 'error' || entry.level === 'fatal' ? 'bg-red-500/10' : ''"
                        >
                            <!-- Timestamp -->
                            <span class="text-[#484f58] whitespace-nowrap shrink-0 select-none text-[10px]" x-text="formatTime(entry.timestamp)"></span>

                            <!-- Level badge -->
                            <span 
                                class="inline-block px-1.5 py-0 rounded text-[9px] font-semibold uppercase border shrink-0"
                                :class="getLevelClasses(entry.level)"
                                x-text="entry.level.toUpperCase()"
                            ></span>

                            <!-- Component tag -->
                            <template x-if="entry.component">
                                <span class="text-purple-400/80 shrink-0 text-[10px]" x-text="'[' + entry.component + ']'"></span>
                            </template>

                            <!-- Message body -->
                            <span class="text-[#c9d1d9] flex-1 break-all select-text">
                                <span x-text="entry.message"></span>
                                <template x-if="entry.correlationId">
                                    <span class="text-[#484f58] ml-1.5 text-[9px]" x-text="'cid:' + entry.correlationId.slice(0, 8)"></span>
                                </template>
                            </span>

                            <!-- Quick Copy Button -->
                            <button 
                                type="button" 
                                x-on:click="copyEntry(entry, index)" 
                                class="opacity-0 group-hover:opacity-100 text-[#8b949e] hover:text-white transition-opacity text-[10px] shrink-0"
                                title="Copy JSON"
                            >
                                <span x-text="copiedIndex === index ? '✓' : '📋'"></span>
                            </button>
                        </div>
                    </template>

                    <!-- Empty state -->
                    <div x-show="filteredLogs.length === 0" class="text-center py-16 text-[#8b949e]">
                        <div class="text-3xl mb-2 opacity-30">💻</div>
                        <p class="text-xs font-bold text-slate-300">No Console Log Events</p>
                        <p class="text-[10px] text-slate-500 mt-1">Waiting for gateway activity on http://localhost:20128/v1...</p>
                    </div>
                </div>

                <!-- Terminal Bottom Status Bar -->
                <div class="h-6 px-3.5 bg-[#161b22] border-t border-[#30363d] flex items-center justify-between text-[10px] text-[#8b949e] shrink-0 relative">
                    <div class="flex items-center gap-3 font-mono">
                        <span>Status: <strong class="text-emerald-400">READY</strong></span>
                        <span class="text-slate-700">|</span>
                        <span>Gateway: <span class="text-cyan-300" x-text="endpoint"></span></span>
                    </div>
                    <div class="font-mono text-[9px] text-slate-500 pr-4">
                        Draggable • Resizable • Persistent
                    </div>

                    <!-- Resize Grabber Handle (Bottom-Right Corner) -->
                    <div 
                        class="absolute bottom-0 right-0 w-5 h-5 cursor-se-resize flex items-end justify-end p-1 z-20 text-slate-500 hover:text-cyan-400 select-none transition-colors group"
                        x-on:mousedown.stop="startResize($event)"
                        x-on:touchstart.stop="startTouchResize($event)"
                        title="Drag to resize window"
                    >
                        <svg class="w-3 h-3 opacity-60 group-hover:opacity-100" viewBox="0 0 6 6" fill="currentColor">
                            <circle cx="5" cy="5" r="0.8"/>
                            <circle cx="5" cy="3" r="0.8"/>
                            <circle cx="3" cy="5" r="0.8"/>
                            <circle cx="1" cy="5" r="0.8"/>
                            <circle cx="5" cy="1" r="0.8"/>
                            <circle cx="3" cy="3" r="0.8"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script: Floating Persistent Terminal Logic with Smooth Window Dragging & Resizing -->
    <script>
        function floatingOmniTerminal() {
            return {
                isOpen: true,
                isMinimized: false,
                isMaximized: false,
                posX: Math.max(20, window.innerWidth - 660),
                posY: 85,
                width: 620,
                height: 380,
                isDragging: false,
                dragOffsetX: 0,
                dragOffsetY: 0,
                isResizing: false,
                resizeStartX: 0,
                resizeStartY: 0,
                initialWidth: 620,
                initialHeight: 380,
                levelFilter: 'all',
                search: '',
                autoScroll: true,
                logs: [],
                endpoint: 'http://localhost:20128/v1',
                copiedIndex: null,
                pollTimer: null,

                init() {
                    try {
                        const savedState = localStorage.getItem('hoa_terminal_state');
                        if (savedState) {
                            const parsed = JSON.parse(savedState);
                            this.isOpen = parsed.isOpen ?? true;
                            this.isMinimized = parsed.isMinimized ?? false;
                            this.isMaximized = parsed.isMaximized ?? false;
                            if (parsed.posX !== undefined) this.posX = Math.min(window.innerWidth - 250, Math.max(10, parsed.posX));
                            if (parsed.posY !== undefined) this.posY = Math.min(window.innerHeight - 100, Math.max(10, parsed.posY));
                            if (parsed.width !== undefined) this.width = Math.min(window.innerWidth - 40, Math.max(380, parsed.width));
                            if (parsed.height !== undefined) this.height = Math.min(window.innerHeight - 150, Math.max(220, parsed.height));
                        }
                    } catch(e) {}

                    this.fetchLogs();
                    this.pollTimer = setInterval(() => {
                        if (this.isOpen) this.fetchLogs();
                    }, 4000);

                    // Global window drag & resize listeners
                    window.addEventListener('mousemove', (e) => {
                        this.onDrag(e);
                        this.onResize(e);
                    });
                    window.addEventListener('mouseup', () => {
                        this.stopDrag();
                        this.stopResize();
                    });
                    window.addEventListener('touchmove', (e) => {
                        this.onTouchDrag(e);
                        this.onTouchResize(e);
                    });
                    window.addEventListener('touchend', () => {
                        this.stopDrag();
                        this.stopResize();
                    });
                },

                saveState() {
                    try {
                        localStorage.setItem('hoa_terminal_state', JSON.stringify({
                            isOpen: this.isOpen,
                            isMinimized: this.isMinimized,
                            isMaximized: this.isMaximized,
                            posX: this.posX,
                            posY: this.posY,
                            width: this.width,
                            height: this.height,
                        }));
                    } catch(e) {}
                },

                openTerminal() {
                    this.isOpen = true;
                    this.isMinimized = false;
                    this.saveState();
                    this.fetchLogs();
                },

                toggleOpen() {
                    if (!this.isOpen) {
                        this.openTerminal();
                    } else if (this.isMinimized) {
                        this.restore();
                    } else {
                        this.minimize();
                    }
                },

                close() {
                    this.isOpen = false;
                    this.isMinimized = false;
                    this.saveState();
                },

                minimize() {
                    this.isMinimized = true;
                    this.saveState();
                },

                restore() {
                    this.isMinimized = false;
                    this.isOpen = true;
                    this.saveState();
                    this.$nextTick(() => this.scrollToBottom());
                },

                toggleMaximize() {
                    this.isMaximized = !this.isMaximized;
                    this.saveState();
                },

                startDrag(e) {
                    if (this.isMaximized) return;
                    this.isDragging = true;
                    this.dragOffsetX = e.clientX - this.posX;
                    this.dragOffsetY = e.clientY - this.posY;
                },

                onDrag(e) {
                    if (!this.isDragging || this.isMaximized) return;
                    this.posX = Math.min(window.innerWidth - 120, Math.max(10, e.clientX - this.dragOffsetX));
                    this.posY = Math.min(window.innerHeight - 60, Math.max(10, e.clientY - this.dragOffsetY));
                },

                startTouchDrag(e) {
                    if (this.isMaximized || !e.touches[0]) return;
                    this.isDragging = true;
                    this.dragOffsetX = e.touches[0].clientX - this.posX;
                    this.dragOffsetY = e.touches[0].clientY - this.posY;
                },

                onTouchDrag(e) {
                    if (!this.isDragging || this.isMaximized || !e.touches[0]) return;
                    this.posX = Math.min(window.innerWidth - 120, Math.max(10, e.touches[0].clientX - this.dragOffsetX));
                    this.posY = Math.min(window.innerHeight - 60, Math.max(10, e.touches[0].clientY - this.dragOffsetY));
                },

                stopDrag() {
                    if (this.isDragging) {
                        this.isDragging = false;
                        this.saveState();
                    }
                },

                startResize(e) {
                    if (this.isMaximized) return;
                    this.isResizing = true;
                    this.resizeStartX = e.clientX;
                    this.resizeStartY = e.clientY;
                    this.initialWidth = this.width;
                    this.initialHeight = this.height;
                },

                onResize(e) {
                    if (!this.isResizing || this.isMaximized) return;
                    const deltaX = e.clientX - this.resizeStartX;
                    const deltaY = e.clientY - this.resizeStartY;
                    this.width = Math.min(window.innerWidth - this.posX - 20, Math.max(380, this.initialWidth + deltaX));
                    this.height = Math.min(window.innerHeight - this.posY - 120, Math.max(220, this.initialHeight + deltaY));
                },

                startTouchResize(e) {
                    if (this.isMaximized || !e.touches[0]) return;
                    this.isResizing = true;
                    this.resizeStartX = e.touches[0].clientX;
                    this.resizeStartY = e.touches[0].clientY;
                    this.initialWidth = this.width;
                    this.initialHeight = this.height;
                },

                onTouchResize(e) {
                    if (!this.isResizing || this.isMaximized || !e.touches[0]) return;
                    const deltaX = e.touches[0].clientX - this.resizeStartX;
                    const deltaY = e.touches[0].clientY - this.resizeStartY;
                    this.width = Math.min(window.innerWidth - this.posX - 20, Math.max(380, this.initialWidth + deltaX));
                    this.height = Math.min(window.innerHeight - this.posY - 120, Math.max(220, this.initialHeight + deltaY));
                },

                stopResize() {
                    if (this.isResizing) {
                        this.isResizing = false;
                        this.saveState();
                    }
                },

                async fetchLogs() {
                    try {
                        const res = await fetch('/admin/api/terminal-logs', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.logs = data.logs || [];
                            this.endpoint = data.endpoint || this.endpoint;
                            if (this.autoScroll) {
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        }
                    } catch(e) {}
                },

                clearLogs() {
                    this.logs = [{
                        timestamp: new Date().toISOString(),
                        level: 'info',
                        component: 'system',
                        message: 'Terminal log buffer cleared.',
                        correlationId: 'clr_01',
                    }];
                },

                scrollToBottom() {
                    const el = this.$refs.logContainer;
                    if (el) el.scrollTop = el.scrollHeight;
                },

                copyEntry(entry, index) {
                    try {
                        navigator.clipboard.writeText(JSON.stringify(entry, null, 2));
                        this.copiedIndex = index;
                        setTimeout(() => this.copiedIndex = null, 1500);
                    } catch(e) {}
                },

                formatTime(ts) {
                    try {
                        const d = new Date(ts);
                        return d.toTimeString().split(' ')[0] + '.' + String(d.getMilliseconds()).padStart(3, '0');
                    } catch(e) {
                        return ts;
                    }
                },

                getLevelClasses(level) {
                    const l = (level || 'info').toLowerCase();
                    switch(l) {
                        case 'error':
                        case 'fatal':
                            return 'text-red-400 bg-red-500/10 border-red-500/20';
                        case 'warn':
                            return 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20';
                        case 'route':
                            return 'text-fuchsia-400 bg-fuchsia-500/10 border-fuchsia-500/20';
                        case 'debug':
                        case 'trace':
                            return 'text-gray-400 bg-gray-500/10 border-gray-500/20';
                        default:
                            return 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20';
                    }
                },

                get filteredLogs() {
                    return this.logs.filter(entry => {
                        const level = (entry.level || 'info').toLowerCase();
                        if (this.levelFilter === 'debug' && !['debug', 'info', 'warn', 'error', 'fatal'].includes(level)) return false;
                        if (this.levelFilter === 'info' && !['info', 'warn', 'error', 'fatal'].includes(level)) return false;
                        if (this.levelFilter === 'warn' && !['warn', 'error', 'fatal'].includes(level)) return false;
                        if (this.levelFilter === 'error' && !['error', 'fatal'].includes(level)) return false;

                        if (this.search) {
                            const q = this.search.toLowerCase();
                            const msg = (entry.message || '').toLowerCase();
                            const comp = (entry.component || '').toLowerCase();
                            const cid = (entry.correlationId || '').toLowerCase();
                            return msg.includes(q) || comp.includes(q) || cid.includes(q);
                        }
                        return true;
                    });
                }
            };
        }
    </script>
</x-layouts.app>
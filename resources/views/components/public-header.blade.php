{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Public Navigation Header Component
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/
--}}

@props([
    'active' => null,
])

<!-- Navigation Header with Smooth Auto Hide/Show on Scroll -->
<header 
    x-data="{ 
        mobileMenuOpen: false, 
        showHeader: true, 
        lastScrollY: 0,
        isScrolled: false,
        init() {
            this.lastScrollY = window.scrollY;
        },
        handleScroll() {
            const currentScrollY = window.scrollY;
            this.isScrolled = currentScrollY > 20;
            
            if (this.mobileMenuOpen) {
                this.showHeader = true;
                return;
            }

            if (currentScrollY <= 60) {
                this.showHeader = true;
                this.lastScrollY = currentScrollY;
                return;
            }

            const scrollDelta = currentScrollY - this.lastScrollY;
            if (Math.abs(scrollDelta) > 6) {
                if (scrollDelta > 0) {
                    this.showHeader = false;
                } else {
                    this.showHeader = true;
                }
                this.lastScrollY = currentScrollY;
            }
        }
    }"
    @scroll.window.passive="handleScroll()"
    class="fixed top-0 inset-x-0 z-50 w-full border-b transition-all duration-300 transform select-none"
    :class="{
        'translate-y-0': showHeader,
        '-translate-y-full': !showHeader,
        'bg-slate-950/85 backdrop-blur-2xl border-white/10 shadow-2xl shadow-black/50': isScrolled,
        'bg-slate-950/40 backdrop-blur-md border-white/5': !isScrolled
    }"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
        <!-- Brand Logo -->
        <a href="/" class="flex items-center gap-3 group shrink-0">
            <x-glass.logo size="md" text="HOA" />
            <div class="hidden sm:block">
                <span class="text-sm sm:text-base font-bold text-white tracking-tight group-hover:text-indigo-300 transition-colors">HelpOfAi Studio</span>
                <p class="text-[11px] text-slate-400 leading-none">Universal Multi-Editor Platform</p>
            </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-300">
            <a href="{{ request()->is('/') ? '#demo' : url('/#demo') }}" class="hover:text-white transition-colors text-indigo-300 font-semibold flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span> Live Multi-Editor Demo
            </a>
            <a href="{{ request()->is('/') ? '#engines' : url('/#engines') }}" class="hover:text-white transition-colors">8 Engines</a>
            <a href="{{ request()->is('/') ? '#features' : url('/#features') }}" class="hover:text-white transition-colors">Architecture</a>
            <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'text-violet-400 font-bold border-b-2 border-violet-500 pb-0.5' : 'hover:text-white transition-colors text-violet-300 font-semibold' }}">Blog</a>
            <a href="{{ request()->is('/') ? '#glass-system' : url('/#glass-system') }}" class="hover:text-white transition-colors">Design System</a>
        </nav>

        <!-- Navigation Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex">
                        <x-glass.button variant="glass" size="sm" class="border-violet-500/40 text-violet-300 hover:bg-violet-600/20">
                            🛡️ Admin Panel
                        </x-glass.button>
                    </a>
                @endif

                <a href="{{ route('editor') }}">
                    <x-glass.button variant="primary" size="sm" class="shadow-indigo-500/30 whitespace-nowrap">
                        ✍️ <span class="hidden sm:inline">Launch</span> Editor
                    </x-glass.button>
                </a>

                <!-- User Profile & Quick Navigation Glass Dropdown -->
                <div class="relative shrink-0" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false" @keydown.escape.window="userMenuOpen = false">
                    <button 
                        type="button" 
                        @click="userMenuOpen = !userMenuOpen" 
                        class="flex items-center gap-2 p-1 pr-2 rounded-xl glass-subtle hover:border-indigo-500/40 transition-all border border-white/5 cursor-pointer focus:outline-none select-none group" 
                        title="User Profile & Settings"
                        :aria-expanded="userMenuOpen"
                    >
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-xs text-white shadow-sm group-hover:scale-105 transition-transform">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="hidden lg:inline text-xs font-semibold text-slate-200 group-hover:text-white transition-colors max-w-[90px] truncate">
                            {{ auth()->user()->name ?? 'User' }}
                        </span>
                        <span class="text-[10px] text-slate-400 group-hover:text-slate-200 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''">▾</span>
                    </button>

                    <!-- Transparent Glassmorphic Backdrop-Blur Dropdown Panel -->
                    <div 
                        x-show="userMenuOpen" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                        class="absolute right-0 top-full mt-2 w-64 rounded-2xl hoa-glass-dropdown py-2 z-50 overflow-hidden text-left border border-white/15 shadow-2xl shadow-black/80"
                        style="display: none; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(28px) saturate(190%); -webkit-backdrop-filter: blur(28px) saturate(190%);"
                    >
                        <!-- User Information Header -->
                        <div class="px-4 py-3 border-b border-white/10 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-sm text-white shadow-md shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name ?? 'User' }}</div>
                                <div class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email ?? 'user@helpofai.com' }}</div>
                                <div class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.2 rounded-full text-[9px] font-mono font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    @if(auth()->user()?->isAdmin())
                                        ● Administrator
                                    @else
                                        ● Studio Creator
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Workspace Quick Navigation -->
                        <div class="py-1.5 border-b border-white/10 text-xs">
                            <div class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Workspace</div>
                            
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                                <span class="text-indigo-400 text-sm">📊</span>
                                <span>Dashboard Overview</span>
                            </a>

                            <a href="{{ route('documents.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                                <span class="text-purple-400 text-sm">📁</span>
                                <span>My Documents</span>
                            </a>

                            <a href="{{ route('editor') }}" class="flex items-center gap-2.5 px-3 py-2 text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                                <span class="text-cyan-400 text-sm">✍️</span>
                                <span>Universal Editor</span>
                            </a>

                            <a href="{{ route('dashboard.blog') }}" class="flex items-center gap-2.5 px-3 py-2 text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                                <span class="text-violet-400 text-sm">📰</span>
                                <span>Blog Articles</span>
                            </a>
                        </div>

                        <!-- Account & Settings Links -->
                        <div class="py-1.5 border-b border-white/10 text-xs">
                            <div class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Account & Controls</div>

                            @if(auth()->user()?->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-violet-300 hover:text-white hover:bg-violet-600/20 transition-colors">
                                    <span class="text-sm">🛡️</span>
                                    <span>Admin Control Center</span>
                                </a>
                            @endif

                            <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-3 py-2 text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                                <span class="text-sm">⚙️</span>
                                <span>Settings & AI Keys</span>
                            </a>

                            <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-3 py-2 text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                                <span class="text-sm">👤</span>
                                <span>User Profile</span>
                            </a>
                        </div>

                        <!-- Sign Out -->
                        <div class="p-1.5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-colors text-left cursor-pointer">
                                    <span class="text-sm">🚪</span>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="relative group">
                    <x-glass.button variant="secondary" size="sm" class="whitespace-nowrap group-hover:border-indigo-500/50 transition-all">
                        Sign In
                    </x-glass.button>
                </a>

                <a href="{{ route('register') }}" class="relative group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl blur opacity-30 group-hover:opacity-75 transition duration-500"></div>
                    <x-glass.button variant="primary" size="sm" shimmer="true" class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-indigo-500/30 whitespace-nowrap !border-0">
                        Get Started Free
                    </x-glass.button>
                </a>
            @endauth

            <!-- Mobile Menu Hamburger Button -->
            <button 
                type="button" 
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 rounded-xl bg-slate-900 border border-white/10 text-slate-300 hover:text-white transition-colors cursor-pointer"
                aria-label="Toggle navigation menu"
            >
                <span x-show="!mobileMenuOpen" class="text-sm">☰</span>
                <span x-show="mobileMenuOpen" class="text-sm">✕</span>
            </button>
        </div>
    </div>

    <!-- Mobile Teleported Slide-Over Drawer -->
    <template x-teleport="body">
        <div>
            <!-- Backdrop Overlay -->
            <div 
                x-show="mobileMenuOpen" 
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="mobileMenuOpen = false"
                class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9998] md:hidden"
                style="display: none;"
            ></div>

            <!-- Slide-Over Drawer (Right Edge) -->
            <div 
                x-show="mobileMenuOpen" 
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 max-w-xs w-full bg-slate-950/98 backdrop-blur-3xl border-l border-white/15 z-[9999] p-6 flex flex-col justify-between shadow-2xl md:hidden overflow-y-auto custom-scrollbar"
                style="display: none;"
            >
                <div class="space-y-6">
                    <!-- Drawer Top Bar -->
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div class="flex items-center gap-2.5">
                            <x-glass.logo size="sm" text="HOA" />
                            <div>
                                <span class="text-sm font-bold text-white tracking-tight">HelpOfAi Studio</span>
                                <p class="text-[10px] text-slate-400 leading-none">Universal Workspace</p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            @click="mobileMenuOpen = false"
                            class="p-2 rounded-xl bg-slate-900 border border-white/10 text-slate-400 hover:text-white transition-colors cursor-pointer"
                            aria-label="Close navigation menu"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Navigation Items -->
                    <div class="space-y-2 font-medium text-sm">
                        <a 
                            href="{{ request()->is('/') ? '#demo' : url('/#demo') }}" 
                            @click="mobileMenuOpen = false" 
                            class="flex items-center justify-between p-3 rounded-2xl bg-gradient-to-r from-indigo-600/20 via-violet-600/15 to-transparent border border-indigo-500/30 text-white font-semibold shadow-inner group hover:border-indigo-500/50 transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-sm shadow-inner group-hover:scale-105 transition-transform">
                                    ⚡
                                </div>
                                <div>
                                    <div class="text-white text-xs font-bold">Live Editor Demo</div>
                                    <div class="text-[10px] text-indigo-300 font-normal">Test 8 writing engines</div>
                                </div>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </a>

                        <a 
                            href="{{ request()->is('/') ? '#engines' : url('/#engines') }}" 
                            @click="mobileMenuOpen = false" 
                            class="flex items-center gap-3 p-3 rounded-2xl bg-slate-900/50 hover:bg-white/5 border border-white/5 hover:border-white/15 text-slate-300 hover:text-white transition-all group"
                        >
                            <div class="w-8 h-8 rounded-xl bg-slate-800 border border-white/10 flex items-center justify-center text-sm group-hover:scale-105 transition-transform">
                                ❖
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white">8 Dedicated Engines</div>
                                <div class="text-[10px] text-slate-400">Tiptap, Notion & Markdown</div>
                            </div>
                        </a>

                        <a 
                            href="{{ request()->is('/') ? '#features' : url('/#features') }}" 
                            @click="mobileMenuOpen = false" 
                            class="flex items-center gap-3 p-3 rounded-2xl bg-slate-900/50 hover:bg-white/5 border border-white/5 hover:border-white/15 text-slate-300 hover:text-white transition-all group"
                        >
                            <div class="w-8 h-8 rounded-xl bg-slate-800 border border-white/10 flex items-center justify-center text-sm group-hover:scale-105 transition-transform">
                                🎯
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white">Architecture</div>
                                <div class="text-[10px] text-slate-400">OmniRoute AI & Real-Time SEO</div>
                            </div>
                        </a>

                        <a 
                            href="{{ route('blog.index') }}" 
                            @click="mobileMenuOpen = false" 
                            class="flex items-center gap-3 p-3 rounded-2xl {{ request()->routeIs('blog.*') ? 'bg-violet-600/20 border border-violet-500/40 text-violet-200' : 'bg-slate-900/50 hover:bg-white/5 border border-white/5 hover:border-white/15 text-slate-300 hover:text-white' }} transition-all group"
                        >
                            <div class="w-8 h-8 rounded-xl bg-slate-800 border border-white/10 flex items-center justify-center text-sm group-hover:scale-105 transition-transform">
                                📰
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white">Blog & Articles</div>
                                <div class="text-[10px] text-violet-300">Insights & Tutorials</div>
                            </div>
                        </a>

                        <a 
                            href="{{ request()->is('/') ? '#glass-system' : url('/#glass-system') }}" 
                            @click="mobileMenuOpen = false" 
                            class="flex items-center gap-3 p-3 rounded-2xl bg-slate-900/50 hover:bg-white/5 border border-white/5 hover:border-white/15 text-slate-300 hover:text-white transition-all group"
                        >
                            <div class="w-8 h-8 rounded-xl bg-slate-800 border border-white/10 flex items-center justify-center text-sm group-hover:scale-105 transition-transform">
                                💎
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white">Design System</div>
                                <div class="text-[10px] text-slate-400">4-Tier Glassmorphic Tokens</div>
                            </div>
                        </a>

                        @auth
                            @if(auth()->user()->isAdmin())
                                <a 
                                    href="{{ route('admin.dashboard') }}" 
                                    @click="mobileMenuOpen = false" 
                                    class="flex items-center gap-3 p-3 rounded-2xl bg-violet-950/40 border border-violet-500/30 text-violet-200 hover:bg-violet-900/40 transition-all group"
                                >
                                    <div class="w-8 h-8 rounded-xl bg-violet-600/30 border border-violet-500/40 flex items-center justify-center text-sm">
                                        🛡️
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-violet-200">Admin Control Center</div>
                                        <div class="text-[10px] text-violet-400">Updates & Diagnostics</div>
                                    </div>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Drawer Bottom Actions -->
                <div class="pt-6 border-t border-white/10 space-y-3">
                    @auth
                        <a href="{{ route('editor') }}" class="block">
                            <x-glass.button variant="primary" size="md" class="w-full justify-center shadow-lg shadow-indigo-600/30">
                                ✍️ Launch Editor
                            </x-glass.button>
                        </a>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="block w-full">
                                <x-glass.button variant="secondary" size="md" class="w-full justify-center text-xs hover:border-indigo-500/50 transition-all">
                                    Sign In
                                </x-glass.button>
                            </a>
                            <a href="{{ route('register') }}" class="block">
                                <x-glass.button variant="primary" size="md" class="w-full justify-center text-xs shadow-lg shadow-indigo-600/30">
                                    Register Free
                                </x-glass.button>
                            </a>
                        </div>
                    @endauth

                    <p class="text-[10px] font-mono text-slate-500 text-center">
                        HelpOfAi Studio &bull; Universal AI Workspace
                    </p>
                </div>
            </div>
        </div>
    </template>
</header>

{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Public Blog Index Blade View
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

<div 
    class="hoa-blog-archive min-h-screen flex flex-col bg-slate-950 text-slate-100 selection:bg-indigo-500/30 selection:text-indigo-200"
    x-data="{ showMobileFilters: false }"
>
    <!-- Ambient Background Lighting matching welcome page -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] bg-purple-600/20 rounded-full blur-[140px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[34rem] h-[34rem] bg-indigo-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-2/3 -left-20 w-[30rem] h-[30rem] bg-cyan-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[40rem] h-[40rem] bg-purple-900/20 rounded-full blur-[160px]"></div>
    </div>

    <!-- Public Navigation Bar matching welcome.blade.php -->
    <x-public-header active="blog" />

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 space-y-10">
        <!-- Hero Section with Ambient Hero Gradient -->
        <section class="relative pt-12 pb-10 text-center overflow-hidden hoa-welcome-hero-gradient rounded-3xl border border-white/5 shadow-2xl">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <!-- Glowing Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-subtle text-xs text-indigo-300 mb-6 border border-indigo-500/20 shadow-inner">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-semibold text-white">The HelpOfAi Studio Journal</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-cyan-300">Dynamic Knowledge Archive & Articles</span>
                </div>

                <!-- Main High-Impact Headline -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.1] max-w-4xl mx-auto mb-5">
                    Explore Articles, Strategies & <br class="hidden sm:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">
                        Content Engineering.
                    </span>
                </h1>

                <!-- Crystal-Clear Subtitle -->
                <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto mb-8 leading-relaxed font-normal">
                    Search through technical tutorials, AI writing frameworks, SEO playbooks, and architectural deep dives published directly from HelpOfAi Studio.
                </p>

                <!-- 4 Core Dynamic Metric Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto">
                    <x-glass.card variant="subtle" class="text-center p-3 hover:border-indigo-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-indigo-400">{{ $totalPublished }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Published Articles</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-3 hover:border-cyan-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-cyan-400">{{ count($categories) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Core Categories</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-3 hover:border-purple-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-purple-400">{{ count($tagCloud) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Indexed Tags</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-3 hover:border-emerald-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black {{ $hasFilters ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $filterCount > 0 ? $filterCount.' Active' : 'All Stories' }}
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Filter Status</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- Search & Control Center -->
        <div class="space-y-4 max-w-5xl mx-auto w-full">
            <!-- Glow Search Box -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-cyan-500/20 rounded-2xl blur opacity-40 group-focus-within:opacity-100 transition duration-300"></div>
                <div class="relative flex items-center glass-standard rounded-2xl border border-white/10 group-focus-within:border-indigo-500/50 shadow-2xl transition-all">
                    <div class="pl-4 text-slate-400 text-base">🔍</div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search articles by title, topic, tag, keyword, or excerpt..." 
                        class="w-full bg-transparent px-3.5 py-3.5 text-xs sm:text-sm text-white placeholder-slate-400 focus:outline-none"
                    />
                    @if(!empty($search))
                        <button 
                            type="button" 
                            wire:click="removeSearch" 
                            class="pr-4 text-slate-400 hover:text-white text-xs cursor-pointer transition-colors"
                            title="Clear search query"
                        >
                            ✕
                        </button>
                    @endif
                </div>
            </div>

            <!-- Toolbar: Sort Dropdown, Read Time Pills, View Switcher & Mobile Filter Toggle -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1 text-xs">
                <!-- Left: Quick Reading Time Filters -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-slate-400 text-[11px] font-mono hidden sm:inline mr-1">Read Time:</span>
                    <button 
                        type="button" 
                        wire:click="filterReadTime('all')"
                        class="px-3 py-1.5 rounded-xl font-medium transition-all cursor-pointer {{ $readTime === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-400 hover:text-white hover:border-white/20' }}"
                    >
                        All
                    </button>
                    <button 
                        type="button" 
                        wire:click="filterReadTime('quick')"
                        class="px-3 py-1.5 rounded-xl font-medium transition-all cursor-pointer {{ $readTime === 'quick' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-400 hover:text-white hover:border-white/20' }}"
                    >
                        ⚡ &lt; 5m Quick
                    </button>
                    <button 
                        type="button" 
                        wire:click="filterReadTime('deep')"
                        class="px-3 py-1.5 rounded-xl font-medium transition-all cursor-pointer {{ $readTime === 'deep' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-400 hover:text-white hover:border-white/20' }}"
                    >
                        📚 5m+ Deep Dive
                    </button>
                </div>

                <!-- Right: Sort Dropdown & Layout View Toggles -->
                <div class="flex items-center gap-2.5 ml-auto">
                    <!-- Sort Select Dropdown -->
                    <div class="flex items-center gap-1.5 glass-subtle rounded-xl px-2.5 py-1 border border-white/10">
                        <span class="text-slate-400 text-[11px]">Sort:</span>
                        <select 
                            wire:model.live="sort" 
                            class="bg-transparent text-white text-xs font-semibold focus:outline-none cursor-pointer pr-1"
                        >
                            <option value="latest" class="bg-slate-900 text-white">Newest First</option>
                            <option value="popular" class="bg-slate-900 text-white">Most Popular (Views)</option>
                            <option value="oldest" class="bg-slate-900 text-white">Oldest First</option>
                            <option value="read_time_asc" class="bg-slate-900 text-white">Shortest Read</option>
                            <option value="read_time_desc" class="bg-slate-900 text-white">Longest Read</option>
                            <option value="alpha" class="bg-slate-900 text-white">Alphabetical (A - Z)</option>
                        </select>
                    </div>

                    <!-- View Switcher (Grid vs List) -->
                    <div class="flex items-center bg-slate-900/80 p-0.5 rounded-xl border border-white/10">
                        <button 
                            type="button" 
                            wire:click="setView('grid')" 
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $view === 'grid' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}"
                            title="Grid View"
                        >
                            ▦
                        </button>
                        <button 
                            type="button" 
                            wire:click="setView('list')" 
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $view === 'list' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}"
                            title="List View"
                        >
                            ☰
                        </button>
                    </div>

                    <!-- Mobile Filter Toggle Button -->
                    <button 
                        type="button" 
                        x-on:click="showMobileFilters = !showMobileFilters" 
                        class="lg:hidden px-3 py-1.5 rounded-xl glass-subtle border border-white/10 text-slate-300 hover:text-white flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <span>🎛️</span>
                        <span>Filters</span>
                        @if($filterCount > 0)
                            <span class="w-4 h-4 rounded-full bg-indigo-500 text-[10px] text-white flex items-center justify-center font-bold">{{ $filterCount }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Active Filter Chips Bar (Visible when any filter is active) -->
            @if($hasFilters)
                <div class="flex items-center gap-2 flex-wrap pt-2 pb-1 border-t border-white/5">
                    <span class="text-xs text-slate-400 font-mono">Active Filters:</span>

                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-200 border border-indigo-500/40 text-xs">
                            <span>🔍 "{{ $search }}"</span>
                            <button type="button" wire:click="removeSearch" class="hover:text-white text-slate-400 cursor-pointer">✕</button>
                        </span>
                    @endif

                    @if($category !== 'all')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-500/20 text-purple-200 border border-purple-500/40 text-xs">
                            <span>📂 {{ $category }}</span>
                            <button type="button" wire:click="removeCategory" class="hover:text-white text-slate-400 cursor-pointer">✕</button>
                        </span>
                    @endif

                    @if($tag !== 'all')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-200 border border-cyan-500/40 text-xs">
                            <span>🏷️ #{{ $tag }}</span>
                            <button type="button" wire:click="removeTag" class="hover:text-white text-slate-400 cursor-pointer">✕</button>
                        </span>
                    @endif

                    @if($archive !== 'all')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-500/40 text-xs">
                            <span>📅 {{ $archive }}</span>
                            <button type="button" wire:click="removeArchive" class="hover:text-white text-slate-400 cursor-pointer">✕</button>
                        </span>
                    @endif

                    @if($readTime !== 'all')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-200 border border-amber-500/40 text-xs">
                            <span>⏱️ {{ $readTime === 'quick' ? '< 5m Quick' : '5m+ Deep Dive' }}</span>
                            <button type="button" wire:click="removeReadTime" class="hover:text-white text-slate-400 cursor-pointer">✕</button>
                        </span>
                    @endif

                    @if($sort !== 'latest')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-slate-300 border border-white/10 text-xs">
                            <span>⇅ {{ ucfirst(str_replace('_', ' ', $sort)) }}</span>
                            <button type="button" wire:click="setSort('latest')" class="hover:text-white text-slate-400 cursor-pointer">✕</button>
                        </span>
                    @endif

                    <button 
                        type="button" 
                        wire:click="clearFilters" 
                        class="text-xs text-rose-400 hover:text-rose-300 underline underline-offset-2 ml-1 cursor-pointer transition-colors font-medium"
                    >
                        Reset All ({{ $filterCount }})
                    </button>
                </div>
            @endif

            <!-- Glass Category Filter Pills Strip -->
            <div class="flex items-center justify-center flex-wrap gap-2 pt-1">
                <button 
                    type="button" 
                    wire:click="filterCategory('all')" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $category === 'all' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-300 hover:text-white hover:border-white/20' }}"
                >
                    All Categories ({{ $totalPublished }})
                </button>

                @foreach($categories as $cat)
                    <button 
                        wire:key="blog-cat-{{ Str::slug($cat->category) }}"
                        type="button" 
                        wire:click="filterCategory('{{ $cat->category }}')" 
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $category === $cat->category ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-300 hover:text-white hover:border-white/20' }}"
                    >
                        {{ $cat->category }} ({{ $cat->total }})
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Featured Hero Post Spotlight (Only when on page 1 & no active filters) -->
        @if($featuredPost)
            <x-glass.card 
                variant="premium" 
                glow="indigo" 
                class="p-6 sm:p-8 relative overflow-hidden hoa-welcome-glow-border hoa-editor-shadow group"
                x-data="hoaCardReadingProgress('{{ $featuredPost->slug }}', {{ (int) ($featuredPost->reading_time_minutes ?? 1) }})"
            >
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    @if(!empty($featuredPost->featured_image))
                        <div class="lg:col-span-6 rounded-2xl overflow-hidden aspect-video bg-slate-950 border border-white/10 shadow-lg relative">
                            <img 
                                src="{{ $featuredPost->featured_image }}" 
                                alt="{{ $featuredPost->title }}" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                            />

                            <!-- Reading Status Floating Badge on Spotlight Thumbnail -->
                            <div class="absolute top-3 right-3" x-show="progress > 0" x-cloak>
                                <template x-if="completed">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/90 backdrop-blur-md text-white font-bold text-[10px] shadow-lg">
                                        <span>✓</span>
                                        <span>Read</span>
                                    </span>
                                </template>
                                <template x-if="!completed">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-900/90 border border-indigo-500/50 backdrop-blur-md text-indigo-300 font-mono font-bold text-[10px] shadow-lg">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                        <span x-text="progress + '%'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    @endif

                    <div class="{{ !empty($featuredPost->featured_image) ? 'lg:col-span-6' : 'lg:col-span-12' }} space-y-4">
                        <div class="flex items-center gap-2.5 flex-wrap text-xs">
                            <x-glass.badge variant="violet">
                                ⭐ Featured Spotlight
                            </x-glass.badge>
                            <x-glass.badge variant="indigo">
                                {{ $featuredPost->category }}
                            </x-glass.badge>
                            <span class="text-slate-500">•</span>
                            <span class="text-slate-400 font-mono text-[11px]">⏱️ {{ $featuredPost->reading_time_minutes }} min read</span>
                            <span class="text-slate-500">•</span>
                            <span class="text-slate-400 font-mono text-[11px]">👁️ {{ number_format($featuredPost->views_count) }} views</span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight group-hover:text-indigo-300 transition-colors leading-snug">
                            <a href="{{ route('blog.show', $featuredPost->slug) }}">
                                {{ $featuredPost->title }}
                            </a>
                        </h2>

                        @if(!empty($featuredPost->excerpt))
                            <p class="text-sm text-slate-300 leading-relaxed line-clamp-3">
                                {{ $featuredPost->excerpt }}
                            </p>
                        @endif

                        <!-- Tags Preview on Hero -->
                        @if(!empty($featuredPost->tags) && count($featuredPost->tags) > 0)
                            <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                @foreach(array_slice($featuredPost->tags, 0, 4) as $t)
                                    <button 
                                        type="button" 
                                        wire:click="filterTag('{{ $t }}')" 
                                        class="px-2 py-0.5 rounded-md text-[10px] font-mono bg-white/5 hover:bg-indigo-600/30 border border-white/10 hover:border-indigo-400/40 text-slate-300 hover:text-white transition-colors cursor-pointer"
                                    >
                                        #{{ $t }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Reading Progress Track (Featured Hero) -->
                        <div x-show="progress > 0" x-cloak class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-[11px] font-mono">
                                <div class="flex items-center gap-1.5">
                                    <template x-if="completed">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">
                                            <span>✓</span>
                                            <span>100% Read</span>
                                        </span>
                                    </template>
                                    <template x-if="!completed">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                            <span x-text="progress + '% read'"></span>
                                        </span>
                                    </template>
                                </div>
                                <span class="text-slate-400 text-[10px]" x-show="!completed" x-text="timeLeft"></span>
                                <span class="text-emerald-400/80 text-[10px] font-semibold" x-show="completed">Completed</span>
                            </div>

                            <div class="w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden border border-white/5 p-[1px]">
                                <div 
                                    class="h-full rounded-full transition-all duration-500 ease-out"
                                    :class="completed ? 'bg-gradient-to-r from-emerald-500 to-teal-400 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400 shadow-[0_0_8px_rgba(99,102,241,0.5)]'"
                                    :style="`width: ${progress}%`"
                                ></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-white/10">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-xs text-white shadow-md">
                                    {{ strtoupper(substr($featuredPost->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="text-xs">
                                    <span class="font-bold text-white block">{{ $featuredPost->user->name ?? 'HelpOfAi Staff' }}</span>
                                    <span class="text-slate-500 text-[10px]">
                                        Published {{ $featuredPost->published_at?->format('M d, Y') }}
                                        @if($featuredPost->updated_at && $featuredPost->updated_at->format('Y-m-d') > ($featuredPost->published_at?->format('Y-m-d') ?? ''))
                                            • Updated {{ $featuredPost->updated_at->format('M d, Y') }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <a 
                                href="{{ route('blog.show', $featuredPost->slug) }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl font-bold text-xs transition-all duration-200 shrink-0 group/btn border shadow-md cursor-pointer"
                                :class="completed 
                                    ? 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-200 hover:text-white border-emerald-500/40 hover:border-emerald-400/70 shadow-emerald-950/30' 
                                    : (progress > 0 
                                        ? 'bg-indigo-600 hover:bg-indigo-500 text-white border-indigo-400/50 shadow-indigo-600/40 shadow-lg' 
                                        : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white border-white/10 shadow-indigo-600/30')"
                            >
                                <template x-if="completed">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span>Read Again</span>
                                        <span class="text-emerald-300 group-hover/btn:rotate-180 transition-transform duration-300">↺</span>
                                    </span>
                                </template>
                                <template x-if="!completed && progress > 0">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span>Resume</span>
                                        <span class="font-mono text-[10px] px-1 py-0.2 rounded bg-white/20 text-white" x-text="progress + '%'"></span>
                                        <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                    </span>
                                </template>
                                <template x-if="progress === 0">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span>Read Article</span>
                                        <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                    </span>
                                </template>
                            </a>
                        </div>
                    </div>
                </div>
            </x-glass.card>
        @endif

        <!-- 2-Column Archive Layout (Articles Feed + Sticky Intelligence Rail) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Main Column: Articles Deck -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Feed Header: Results Counter & Search Context -->
                <div class="flex items-center justify-between gap-4 pb-2 border-b border-white/5 text-xs text-slate-400">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-white text-sm">
                            {{ $posts->total() }} {{ Str::plural('Article', $posts->total()) }}
                        </span>
                        @if($category !== 'all')
                            <span>in <strong class="text-indigo-300 font-semibold">{{ $category }}</strong></span>
                        @endif
                        @if($tag !== 'all')
                            <span>tagged <strong class="text-cyan-300 font-semibold">#{{ $tag }}</strong></span>
                        @endif
                        @if(!empty($search))
                            <span>matching <strong class="text-white font-semibold">"{{ $search }}"</strong></span>
                        @endif
                        @if($archive !== 'all')
                            <span>from <strong class="text-emerald-300 font-semibold">{{ $archive }}</strong></span>
                        @endif
                    </div>

                    @if($hasFilters)
                        <button 
                            type="button" 
                            wire:click="clearFilters" 
                            class="text-slate-400 hover:text-white transition-colors cursor-pointer text-xs shrink-0"
                        >
                            Reset
                        </button>
                    @endif
                </div>

                <!-- Articles Presentation (Grid or List View) -->
                @if($posts->count() > 0)
                    @if($view === 'grid')
                        <!-- Grid View (Cards) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-6">
                            @foreach($posts as $post)
                                <article 
                                    wire:key="blog-post-grid-{{ $post->id }}" 
                                    class="glass-standard rounded-2xl border border-white/10 hover:border-indigo-500/40 transition-all duration-300 hoa-card-glow-shadow flex flex-col justify-between overflow-hidden group"
                                    x-data="hoaCardReadingProgress('{{ $post->slug }}', {{ (int) ($post->reading_time_minutes ?? 1) }})"
                                >
                                    <div>
                                        <!-- Article Thumbnail Banner -->
                                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] bg-slate-950 overflow-hidden relative border-b border-white/5">
                                            @if(!empty($post->featured_image))
                                                <img 
                                                    src="{{ $post->featured_image }}" 
                                                    alt="{{ $post->title }}" 
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                                    onerror="this.style.display='none'"
                                                />
                                            @else
                                                <div class="w-full h-full bg-gradient-to-tr from-indigo-950/60 via-slate-900 to-purple-950/60 flex items-center justify-center text-3xl">
                                                    <span>📰</span>
                                                </div>
                                            @endif

                                            <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                                <x-glass.badge variant="indigo" class="backdrop-blur-md">
                                                    {{ $post->category }}
                                                </x-glass.badge>
                                                @if($post->is_featured)
                                                    <x-glass.badge variant="violet" class="backdrop-blur-md">
                                                        ⭐
                                                    </x-glass.badge>
                                                @endif
                                            </div>

                                            <!-- Dynamic Reading Status Badge on Thumbnail -->
                                            <div class="absolute top-3 right-3" x-show="progress > 0" x-cloak>
                                                <template x-if="completed">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/90 backdrop-blur-md text-white font-bold text-[10px] shadow-lg">
                                                        <span>✓</span>
                                                        <span>Read</span>
                                                    </span>
                                                </template>
                                                <template x-if="!completed">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-900/90 border border-indigo-500/50 backdrop-blur-md text-indigo-300 font-mono font-bold text-[10px] shadow-lg">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                                        <span x-text="progress + '%'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </a>

                                        <!-- Article Details -->
                                        <div class="p-5 space-y-2.5">
                                            <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono">
                                                <span>📅 {{ $post->published_at?->format('M d, Y') }}</span>
                                                <span>•</span>
                                                <span>⏱️ {{ $post->reading_time_minutes }} min read</span>
                                                <span>•</span>
                                                <span>👁️ {{ number_format($post->views_count) }}</span>
                                            </div>

                                            <h3 class="text-base sm:text-lg font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
                                                <a href="{{ route('blog.show', $post->slug) }}">
                                                    {{ $post->title }}
                                                </a>
                                            </h3>

                                            @if(!empty($post->excerpt))
                                                <p class="text-xs text-slate-400 line-clamp-3 leading-relaxed">
                                                    {{ $post->excerpt }}
                                                </p>
                                            @endif

                                            <!-- Tags Cloud Preview (Clicking sets tag filter) -->
                                            @if(!empty($post->tags) && count($post->tags) > 0)
                                                <div class="flex items-center gap-1 flex-wrap pt-1.5">
                                                    @foreach(array_slice($post->tags, 0, 3) as $t)
                                                        <button 
                                                            type="button" 
                                                            wire:click="filterTag('{{ $t }}')" 
                                                            class="px-2 py-0.5 rounded text-[10px] font-mono transition-all cursor-pointer {{ $tag === $t ? 'bg-indigo-600 text-white font-bold' : 'bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/5' }}"
                                                        >
                                                            #{{ $t }}
                                                        </button>
                                                    @endforeach
                                                    @if(count($post->tags) > 3)
                                                        <span class="text-[10px] text-slate-500 font-mono">+{{ count($post->tags) - 3 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Footer Author & Link with Reading Progress Tracker -->
                                    <div class="p-5 pt-3 border-t border-white/5 space-y-2.5 text-xs">
                                        <!-- Reading Progress Bar & Status (Revealed when reading has started) -->
                                        <div x-show="progress > 0" x-cloak class="space-y-1.5 pb-0.5">
                                            <div class="flex items-center justify-between text-[11px] font-mono">
                                                <div class="flex items-center gap-1.5">
                                                    <template x-if="completed">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">
                                                            <span>✓</span>
                                                            <span>100% Read</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!completed">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 text-[10px] font-bold">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                                            <span x-text="progress + '% read'"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                                <span class="text-slate-400 text-[10px]" x-show="!completed" x-text="timeLeft"></span>
                                                <span class="text-emerald-400/80 text-[10px] font-semibold" x-show="completed">Completed</span>
                                            </div>

                                            <div class="w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden border border-white/5 p-[1px]">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-500 ease-out"
                                                    :class="completed ? 'bg-gradient-to-r from-emerald-500 to-teal-400 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400 shadow-[0_0_8px_rgba(99,102,241,0.5)]'"
                                                    :style="`width: ${progress}%`"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Author Row & Upgraded Glassmorphic Action Button -->
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-[10px] text-white shrink-0 shadow-sm">
                                                    {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-slate-300 text-xs truncate">{{ $post->user->name ?? 'Author' }}</span>
                                            </div>

                                            <!-- Ultra-Sleek Glassmorphic Action Button with Dynamic Read/Resume/Completed State -->
                                            <a 
                                                href="{{ route('blog.show', $post->slug) }}" 
                                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all duration-200 shrink-0 ml-2 group/btn border shadow-sm cursor-pointer"
                                                :class="completed 
                                                    ? 'bg-emerald-500/10 hover:bg-emerald-500/25 text-emerald-300 hover:text-white border-emerald-500/30 hover:border-emerald-400/60 shadow-emerald-950/20' 
                                                    : (progress > 0 
                                                        ? 'bg-indigo-500/20 hover:bg-indigo-600 text-indigo-200 hover:text-white border-indigo-500/40 hover:border-indigo-400 shadow-[0_0_12px_rgba(99,102,241,0.25)]' 
                                                        : 'bg-white/5 hover:bg-indigo-600/25 text-slate-300 hover:text-white border-white/10 hover:border-indigo-500/40 group-hover:border-indigo-500/30')"
                                            >
                                                <!-- Dynamic State 1: Completed -->
                                                <template x-if="completed">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <span>Read Again</span>
                                                        <span class="text-emerald-400 group-hover/btn:rotate-180 transition-transform duration-300">↺</span>
                                                    </span>
                                                </template>

                                                <!-- Dynamic State 2: In Progress / Resume -->
                                                <template x-if="!completed && progress > 0">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <span>Resume</span>
                                                        <span class="font-mono text-[10px] px-1 py-0.2 rounded bg-indigo-400/20 text-indigo-300" x-text="progress + '%'"></span>
                                                        <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                                    </span>
                                                </template>

                                                <!-- Dynamic State 3: Unread -->
                                                <template x-if="progress === 0">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <span>Read</span>
                                                        <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                                    </span>
                                                </template>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <!-- List View (Horizontal Editorial Cards) -->
                        <div class="space-y-4">
                            @foreach($posts as $post)
                                <article 
                                    wire:key="blog-post-list-{{ $post->id }}" 
                                    class="glass-standard rounded-2xl border border-white/10 hover:border-indigo-500/40 transition-all duration-300 hoa-card-glow-shadow overflow-hidden group p-4 sm:p-5 flex flex-col sm:flex-row gap-5 items-center"
                                    x-data="hoaCardReadingProgress('{{ $post->slug }}', {{ (int) ($post->reading_time_minutes ?? 1) }})"
                                >
                                    <!-- Thumbnail -->
                                    <a href="{{ route('blog.show', $post->slug) }}" class="w-full sm:w-56 aspect-[16/10] sm:aspect-video rounded-xl bg-slate-950 overflow-hidden relative shrink-0 border border-white/5 block">
                                        @if(!empty($post->featured_image))
                                            <img 
                                                src="{{ $post->featured_image }}" 
                                                alt="{{ $post->title }}" 
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                                onerror="this.style.display='none'"
                                            />
                                        @else
                                            <div class="w-full h-full bg-gradient-to-tr from-indigo-950/60 via-slate-900 to-purple-950/60 flex items-center justify-center text-3xl">
                                                <span>📰</span>
                                            </div>
                                        @endif

                                        <div class="absolute top-2 left-2">
                                            <x-glass.badge variant="indigo" class="backdrop-blur-md text-[10px]">
                                                {{ $post->category }}
                                            </x-glass.badge>
                                        </div>

                                        <!-- Dynamic Reading Status Badge on Thumbnail -->
                                        <div class="absolute top-2 right-2" x-show="progress > 0" x-cloak>
                                            <template x-if="completed">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/90 backdrop-blur-md text-white font-bold text-[10px] shadow-lg">
                                                    <span>✓</span>
                                                    <span>Read</span>
                                                </span>
                                            </template>
                                            <template x-if="!completed">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-900/90 border border-indigo-500/50 backdrop-blur-md text-indigo-300 font-mono font-bold text-[10px] shadow-lg">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                                    <span x-text="progress + '%'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </a>

                                    <!-- Content Details -->
                                    <div class="flex-1 min-w-0 space-y-2 w-full">
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono flex-wrap">
                                            <span>📅 {{ $post->published_at?->format('M d, Y') }}</span>
                                            <span>•</span>
                                            <span>⏱️ {{ $post->reading_time_minutes }} min</span>
                                            <span>•</span>
                                            <span>👁️ {{ number_format($post->views_count) }} views</span>
                                        </div>

                                        <h3 class="text-base sm:text-lg font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
                                            <a href="{{ route('blog.show', $post->slug) }}">
                                                {{ $post->title }}
                                            </a>
                                        </h3>

                                        @if(!empty($post->excerpt))
                                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                                {{ $post->excerpt }}
                                            </p>
                                        @endif

                                        <!-- Reading Progress Track (List View) -->
                                        <div x-show="progress > 0" x-cloak class="space-y-1 pt-1">
                                            <div class="flex items-center justify-between text-[10px] font-mono">
                                                <div class="flex items-center gap-1.5">
                                                    <template x-if="completed">
                                                        <span class="text-emerald-400 font-bold flex items-center gap-1">
                                                            <span>✓</span> <span>Completed</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!completed">
                                                        <span class="text-indigo-300 font-bold flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                                            <span x-text="progress + '% read'"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                                <span class="text-slate-400 text-[10px]" x-show="!completed" x-text="timeLeft"></span>
                                            </div>
                                            <div class="w-full h-1.5 bg-slate-800/80 rounded-full overflow-hidden border border-white/5 p-[1px]">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-500 ease-out"
                                                    :class="completed ? 'bg-gradient-to-r from-emerald-500 to-teal-400' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400'"
                                                    :style="`width: ${progress}%`"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Tags & Author Row -->
                                        <div class="flex items-center justify-between gap-3 pt-2 border-t border-white/5 flex-wrap">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @if(!empty($post->tags) && count($post->tags) > 0)
                                                    @foreach(array_slice($post->tags, 0, 3) as $t)
                                                        <button 
                                                            type="button" 
                                                            wire:click="filterTag('{{ $t }}')" 
                                                            class="px-2 py-0.5 rounded text-[10px] font-mono transition-all cursor-pointer {{ $tag === $t ? 'bg-indigo-600 text-white font-bold' : 'bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/5' }}"
                                                        >
                                                            #{{ $t }}
                                                        </button>
                                                    @endforeach
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <span class="text-xs text-slate-400 font-medium">By {{ $post->user->name ?? 'Author' }}</span>
                                                <a 
                                                    href="{{ route('blog.show', $post->slug) }}" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold text-xs transition-all duration-200 shrink-0 group/btn border shadow-sm cursor-pointer"
                                                    :class="completed 
                                                        ? 'bg-emerald-500/10 hover:bg-emerald-500/25 text-emerald-300 hover:text-white border-emerald-500/30 hover:border-emerald-400/60' 
                                                        : (progress > 0 
                                                            ? 'bg-indigo-500/20 hover:bg-indigo-600 text-indigo-200 hover:text-white border-indigo-500/40 hover:border-indigo-400 shadow-[0_0_12px_rgba(99,102,241,0.25)]' 
                                                            : 'bg-white/5 hover:bg-indigo-600/25 text-slate-300 hover:text-white border-white/10 hover:border-indigo-500/40 group-hover:border-indigo-500/30')"
                                                >
                                                    <template x-if="completed">
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <span>Read Again</span>
                                                            <span class="text-emerald-400 group-hover/btn:rotate-180 transition-transform duration-300">↺</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!completed && progress > 0">
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <span>Resume</span>
                                                            <span class="font-mono text-[10px] px-1 py-0.2 rounded bg-indigo-400/20 text-indigo-300" x-text="progress + '%'"></span>
                                                            <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                                        </span>
                                                    </template>
                                                    <template x-if="progress === 0">
                                                        <span class="inline-flex items-center gap-1.5">
                                                            <span>Read</span>
                                                            <span class="group-hover/btn:translate-x-0.5 transition-transform">&rarr;</span>
                                                        </span>
                                                    </template>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if($posts->hasPages())
                        <div class="pt-6">
                            {{ $posts->links() }}
                        </div>
                    @endif
                @else
                    <!-- Dynamic Empty State -->
                    <x-glass.card variant="glass" class="p-12 text-center space-y-4 max-w-lg mx-auto">
                        <div class="text-5xl">🔍</div>
                        <h3 class="text-xl font-bold text-white">No Matching Articles Found</h3>
                        <p class="text-xs text-slate-400 leading-relaxed max-w-sm mx-auto">
                            No published articles match your current search and filter combination. Try clearing some filters or searching for alternative topics.
                        </p>

                        <!-- Suggestions -->
                        @if(count($categories) > 0)
                            <div class="pt-2">
                                <span class="text-[11px] text-slate-500 block mb-2 font-mono">Suggested Categories:</span>
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    @foreach($categories->take(4) as $cat)
                                        <button 
                                            type="button" 
                                            wire:click="filterCategory('{{ $cat->category }}')" 
                                            class="px-2.5 py-1 rounded-lg text-xs bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 cursor-pointer"
                                        >
                                            {{ $cat->category }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-3">
                            <x-glass.button variant="primary" size="sm" wire:click="clearFilters">
                                ⟲ View All Articles
                            </x-glass.button>
                        </div>
                    </x-glass.card>
                @endif
            </div>

            <!-- Right Column: Sticky Archive Exploration Sidebar -->
            <aside 
                class="lg:col-span-4 space-y-6"
                :class="{ 'block': showMobileFilters, 'hidden lg:block': !showMobileFilters }"
            >
                <!-- Dynamic Tag Cloud Deck -->
                <x-glass.card variant="subtle" class="p-5 space-y-4 hoa-card-glow-shadow">
                    <div class="flex items-center justify-between pb-2 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">🏷️</span>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Topic & Tag Cloud</h3>
                        </div>
                        @if($tag !== 'all')
                            <button 
                                type="button" 
                                wire:click="removeTag" 
                                class="text-[10px] text-cyan-400 hover:text-cyan-300 cursor-pointer"
                            >
                                Clear Tag
                            </button>
                        @endif
                    </div>

                    @if(count($tagCloud) > 0)
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($tagCloud as $tagName => $count)
                                <button 
                                    wire:key="archive-tag-{{ Str::slug($tagName) }}"
                                    type="button" 
                                    wire:click="filterTag('{{ $tagName }}')" 
                                    class="px-2.5 py-1 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer {{ $tag === $tagName ? 'bg-gradient-to-r from-cyan-600 to-indigo-600 text-white font-bold shadow-md shadow-cyan-600/30 border border-cyan-400/50' : 'glass-subtle border border-white/10 text-slate-300 hover:text-white hover:border-white/25' }}"
                                >
                                    <span>#{{ $tagName }}</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $tag === $tagName ? 'bg-white/25 text-white' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $count }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500 italic py-2 text-center">No tags indexed yet.</p>
                    @endif
                </x-glass.card>

                <!-- Categories Directory Deck -->
                <x-glass.card variant="subtle" class="p-5 space-y-3.5 hoa-card-glow-shadow">
                    <div class="flex items-center justify-between pb-2 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">📂</span>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Categories Directory</h3>
                        </div>
                        @if($category !== 'all')
                            <button 
                                type="button" 
                                wire:click="removeCategory" 
                                class="text-[10px] text-purple-400 hover:text-purple-300 cursor-pointer"
                            >
                                Clear
                            </button>
                        @endif
                    </div>

                    <div class="space-y-1.5">
                        <button 
                            type="button" 
                            wire:click="filterCategory('all')" 
                            class="w-full flex items-center justify-between p-2 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $category === 'all' ? 'bg-indigo-600/30 text-indigo-300 border border-indigo-500/40' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                        >
                            <span class="flex items-center gap-2">
                                <span>🌐</span>
                                <span>All Categories</span>
                            </span>
                            <span class="font-mono text-[11px] text-slate-400">{{ $totalPublished }}</span>
                        </button>

                        @foreach($categories as $cat)
                            <button 
                                wire:key="dir-cat-{{ Str::slug($cat->category) }}"
                                type="button" 
                                wire:click="filterCategory('{{ $cat->category }}')" 
                                class="w-full flex items-center justify-between p-2 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $category === $cat->category ? 'bg-indigo-600/30 text-indigo-300 border border-indigo-500/40' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                            >
                                <span class="flex items-center gap-2 truncate">
                                    <span>📁</span>
                                    <span class="truncate">{{ $cat->category }}</span>
                                </span>
                                <span class="font-mono text-[11px] text-slate-400 shrink-0">{{ $cat->total }}</span>
                            </button>
                        @endforeach
                    </div>
                </x-glass.card>

                <!-- Date Archive Timeline Deck -->
                @if(count($archiveTimeline) > 0)
                    <x-glass.card variant="subtle" class="p-5 space-y-3.5 hoa-card-glow-shadow">
                        <div class="flex items-center justify-between pb-2 border-b border-white/10">
                            <div class="flex items-center gap-2">
                                <span class="text-sm">📅</span>
                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Archive Timeline</h3>
                            </div>
                            @if($archive !== 'all')
                                <button 
                                    type="button" 
                                    wire:click="removeArchive" 
                                    class="text-[10px] text-emerald-400 hover:text-emerald-300 cursor-pointer"
                                >
                                    Clear
                                </button>
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            @foreach($archiveTimeline as $item)
                                <button 
                                    wire:key="archive-time-{{ $item['key'] }}"
                                    type="button" 
                                    wire:click="filterArchive('{{ $item['key'] }}')" 
                                    class="w-full flex items-center justify-between p-2 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $archive === $item['key'] ? 'bg-emerald-600/30 text-emerald-300 border border-emerald-500/40' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                >
                                    <span class="flex items-center gap-2">
                                        <span>🗓️</span>
                                        <span>{{ $item['label'] }}</span>
                                    </span>
                                    <span class="font-mono text-[11px] text-slate-400">{{ $item['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </x-glass.card>
                @endif

                <!-- Studio Authoring Spotlight Deck -->
                <x-glass.card variant="glass" class="p-5 space-y-3 hoa-welcome-glow-border text-center">
                    <div class="text-2xl">✍️</div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Publish with AI Studio</h4>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Produce rich SEO-optimized content with TipTap 3.30 and 8 universal writing engines.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('editor') }}" class="block">
                            <x-glass.button variant="primary" size="sm" class="w-full justify-center text-xs">
                                Open Universal Editor &rarr;
                            </x-glass.button>
                        </a>
                    </div>
                </x-glass.card>
            </aside>
        </div>

        <!-- Creator Call to Action Banner matching welcome page -->
        <x-glass.card variant="premium" glow="indigo" class="p-8 sm:p-12 relative overflow-hidden hoa-welcome-glow-border hoa-editor-shadow text-center">
            <h3 class="text-2xl sm:text-4xl font-black text-white tracking-tight mb-4">
                Ready to Publish with Enterprise AI?
            </h3>
            <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto mb-8 leading-relaxed">
                Write, edit, and optimize articles with 8 universal writing engines and real-time SEO intelligence. Post directly to the HelpOfAi Studio blog in 1-click.
            </p>
            <div class="flex items-center justify-center">
                <a href="{{ route('editor') }}" class="relative group inline-block">
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl blur opacity-40 group-hover:opacity-100 transition duration-500"></div>
                    <x-glass.button variant="primary" size="lg" shimmer="true" class="relative px-8 py-3.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-2xl shadow-indigo-600/40 text-white font-bold !border-0 text-base">
                        🚀 Launch Studio Editor &rarr;
                    </x-glass.button>
                </a>
            </div>
        </x-glass.card>
    </main>

    <!-- Public Footer matching welcome.blade.php -->
    <x-public-footer />

    <!-- Reading Progress Sync Engine for Blog Archive Cards -->
    <script>
        function hoaCardReadingProgress(slug, readTimeMinutes) {
            return {
                slug: slug || '',
                readTimeMinutes: parseInt(readTimeMinutes, 10) || 1,
                progress: 0,
                completed: false,
                timeLeft: '',

                init() {
                    this.sync();
                    // Sync when localStorage updates across tabs or popstate
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'hoa_read_progress_' + this.slug) {
                            this.sync();
                        }
                    });
                    // Refresh when navigating back from an article via browser cache
                    window.addEventListener('pageshow', () => this.sync());
                    window.addEventListener('focus', () => this.sync());
                },

                sync() {
                    if (!this.slug) return;
                    try {
                        const raw = localStorage.getItem('hoa_read_progress_' + this.slug);
                        if (raw) {
                            const data = JSON.parse(raw);
                            const p = parseInt(data.progress, 10) || 0;
                            this.completed = !!data.completed || p >= 88;
                            this.progress = this.completed ? 100 : Math.min(100, Math.max(0, p));
                            const remainingRatio = Math.max(0, 1 - (this.progress / 100));
                            const remMin = Math.max(1, Math.round(this.readTimeMinutes * remainingRatio));
                            this.timeLeft = remMin + 'm left';
                        }
                    } catch (e) {}
                }
            };
        }
    </script>
</div>

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

<div class="hoa-blog-index min-h-screen flex flex-col bg-slate-950 text-slate-100 selection:bg-indigo-500/30 selection:text-indigo-200">
    <!-- Ambient Background Lighting matching welcome page -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] bg-purple-600/20 rounded-full blur-[140px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[34rem] h-[34rem] bg-indigo-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-2/3 -left-20 w-[30rem] h-[30rem] bg-cyan-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[40rem] h-[40rem] bg-purple-900/20 rounded-full blur-[160px]"></div>
    </div>

    <!-- Public Navigation Bar matching welcome.blade.php -->
    <x-public-header />

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 space-y-12">
        <!-- Hero Section with Ambient Hero Gradient -->
        <section class="relative pt-12 pb-10 text-center overflow-hidden hoa-welcome-hero-gradient rounded-3xl">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <!-- Glowing Pill Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-subtle text-xs text-indigo-300 mb-6 border border-indigo-500/20 shadow-inner">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="font-semibold text-white">The HelpOfAi Studio Journal</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-cyan-300">AI Intelligence & Content Engineering</span>
                </div>

                <!-- Main High-Impact Headline -->
                <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-[1.1] max-w-4xl mx-auto mb-6">
                    Insights, AI Strategies & <br class="hidden sm:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-cyan-400">
                        Content Engineering.
                    </span>
                </h1>

                <!-- Crystal-Clear Subtitle -->
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
                    Explore deep dives, research papers, writing frameworks, and tutorials published directly from the HelpOfAi Studio multi-engine editor.
                </p>

                <!-- 4 Core Metric Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto">
                    <x-glass.card variant="subtle" class="text-center p-3.5 hover:border-indigo-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-indigo-400">{{ $totalPublished }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Published Articles</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-3.5 hover:border-cyan-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-cyan-400">8 Engines</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Universal Writing</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-3.5 hover:border-purple-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-purple-400">Real-Time</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Live SEO Auditing</div>
                    </x-glass.card>
                    <x-glass.card variant="subtle" class="text-center p-3.5 hover:border-emerald-500/40 transition-all hoa-card-glow-shadow">
                        <div class="text-xl sm:text-2xl font-black text-emerald-400">Direct</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Studio Publishing</div>
                    </x-glass.card>
                </div>
            </div>
        </section>

        <!-- Search & Filter Controls -->
        <div class="space-y-5 max-w-3xl mx-auto w-full">
            <!-- Glow Search Box -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-cyan-500/20 rounded-2xl blur opacity-40 group-focus-within:opacity-100 transition duration-300"></div>
                <div class="relative flex items-center glass-standard rounded-2xl border border-white/10 group-focus-within:border-indigo-500/50 shadow-2xl transition-all">
                    <div class="pl-4 text-slate-400 text-sm">🔍</div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search articles by title, topic, keyword, or technology..." 
                        class="w-full bg-transparent px-3.5 py-3.5 text-xs sm:text-sm text-white placeholder-slate-400 focus:outline-none"
                    />
                    @if(!empty($search))
                        <button 
                            type="button" 
                            wire:click="$set('search', '')" 
                            class="pr-4 text-slate-400 hover:text-white text-xs cursor-pointer transition-colors"
                        >
                            ✕
                        </button>
                    @endif
                </div>
            </div>

            <!-- Glass Category Filter Pills -->
            <div class="flex items-center justify-center flex-wrap gap-2">
                <button 
                    type="button" 
                    wire:click="filterCategory('all')" 
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $category === 'all' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-300 hover:text-white hover:border-white/20' }}"
                >
                    All Articles ({{ $totalPublished }})
                </button>

                @foreach($categories as $cat)
                    <button 
                        type="button" 
                        wire:click="filterCategory('{{ $cat->category }}')" 
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $category === $cat->category ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/40' : 'glass-subtle border border-white/10 text-slate-300 hover:text-white hover:border-white/20' }}"
                    >
                        {{ $cat->category }} ({{ $cat->total }})
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Featured Hero Post Spotlight (If available & not searching) -->
        @if($featuredPost)
            <x-glass.card variant="premium" glow="indigo" class="p-6 sm:p-8 relative overflow-hidden hoa-welcome-glow-border hoa-editor-shadow group">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    @if(!empty($featuredPost->featured_image))
                        <div class="lg:col-span-6 rounded-2xl overflow-hidden aspect-video bg-slate-950 border border-white/10 shadow-lg relative">
                            <img 
                                src="{{ $featuredPost->featured_image }}" 
                                alt="{{ $featuredPost->title }}" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                            />
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

                        <div class="flex items-center justify-between pt-4 border-t border-white/10">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-xs text-white shadow-md">
                                    {{ strtoupper(substr($featuredPost->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="text-xs">
                                    <span class="font-bold text-white block">{{ $featuredPost->user->name ?? 'HelpOfAi Staff' }}</span>
                                    <span class="text-slate-500 text-[10px]">{{ $featuredPost->published_at?->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('blog.show', $featuredPost->slug) }}">
                                <x-glass.button variant="primary" size="sm" class="shadow-lg shadow-indigo-600/30">
                                    Read Article &rarr;
                                </x-glass.button>
                            </a>
                        </div>
                    </div>
                </div>
            </x-glass.card>
        @endif

        <!-- Blog Articles Grid -->
        <div class="space-y-8">
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($posts as $post)
                        <article class="glass-standard rounded-2xl border border-white/10 hover:border-indigo-500/40 transition-all duration-300 hoa-card-glow-shadow flex flex-col justify-between overflow-hidden group">
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
                                        <!-- Sleek Procedural Geometric Cover Fallback -->
                                        <div class="w-full h-full bg-gradient-to-tr from-indigo-950/60 via-slate-900 to-purple-950/60 flex items-center justify-center text-3xl">
                                            <span>📰</span>
                                        </div>
                                    @endif
                                    <div class="absolute top-3 left-3">
                                        <x-glass.badge variant="indigo" class="backdrop-blur-md">
                                            {{ $post->category }}
                                        </x-glass.badge>
                                    </div>
                                </a>

                                <!-- Article Details -->
                                <div class="p-5 space-y-2.5">
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono">
                                        <span>📅 {{ $post->published_at?->format('M d, Y') }}</span>
                                        <span>•</span>
                                        <span>⏱️ {{ $post->reading_time_minutes }} min read</span>
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
                                </div>
                            </div>

                            <!-- Footer Author & Link -->
                            <div class="p-5 pt-3 border-t border-white/5 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-[10px] text-white">
                                        {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-slate-300 text-xs truncate max-w-[110px]">{{ $post->user->name ?? 'Author' }}</span>
                                </div>

                                <a 
                                    href="{{ route('blog.show', $post->slug) }}" 
                                    class="text-xs font-bold text-indigo-400 group-hover:text-indigo-300 flex items-center gap-1 transition-colors"
                                >
                                    <span>Read Article</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="pt-6">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <x-glass.card variant="glass" class="p-12 text-center space-y-4 max-w-md mx-auto">
                    <div class="text-4xl">🔍</div>
                    <h3 class="text-lg font-bold text-white">No Blog Articles Found</h3>
                    <p class="text-xs text-slate-400">
                        We couldn't find any articles matching your search or category filter.
                    </p>
                    <x-glass.button variant="primary" size="sm" wire:click="clearFilters">
                        View All Articles
                    </x-glass.button>
                </x-glass.card>
            @endif
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
</div>

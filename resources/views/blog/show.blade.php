{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Public Blog Post Show Blade View
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
    class="hoa-blog-post-page min-h-screen flex flex-col bg-slate-950 text-slate-100 selection:bg-indigo-500/30 selection:text-indigo-200" 
    x-data="hoaBlogPostReader('{{ $post->slug }}')"
>
    <!-- Top Reading Progress Indicator (0% to 100%) -->
    <div class="fixed top-0 left-0 right-0 z-50 h-[3px] bg-transparent pointer-events-none">
        <div 
            class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400 transition-[width] duration-150 ease-out shadow-[0_0_12px_rgba(99,102,241,0.8)]"
            :style="`width: ${scrollProgress}%`"
        ></div>
    </div>

    <!-- Sticky Floating Mini-Header (Floats down when scrolled past hero) -->
    <header 
        x-show="showFloatingHeader" 
        x-cloak
        x-transition:enter="transition ease-out duration-300 transform" 
        x-transition:enter-start="-translate-y-full opacity-0" 
        x-transition:enter-end="translate-y-0 opacity-100" 
        x-transition:leave="transition ease-in duration-200 transform" 
        x-transition:leave-start="translate-y-0 opacity-100" 
        x-transition:leave-end="-translate-y-full opacity-0" 
        class="fixed top-0 left-0 right-0 z-40 bg-slate-950/90 backdrop-blur-xl border-b border-white/10 shadow-2xl py-3 px-4 sm:px-8"
    >
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('blog.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1 shrink-0 transition-colors">
                    <span>&larr;</span>
                    <span class="hidden sm:inline">Journal</span>
                </a>
                <span class="text-slate-700 hidden sm:inline">/</span>
                <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-mono uppercase font-semibold shrink-0">
                    {{ $post->category }}
                </span>
                <span class="text-xs sm:text-sm font-bold text-white truncate min-w-0" title="{{ $post->title }}">
                    {{ $post->title }}
                </span>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-400 font-mono">
                    <span x-text="scrollProgress + '%'"></span>
                    <span>read</span>
                </div>

                <!-- Quick Copy Link -->
                <button 
                    type="button" 
                    x-on:click="navigator.clipboard.writeText(window.location.href); copySuccess = true; setTimeout(() => copySuccess = false, 2500);" 
                    class="px-3 py-1 rounded-xl glass-subtle hover:border-white/25 text-xs text-slate-300 hover:text-white transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
                >
                    <span x-show="!copySuccess">🔗 <span class="hidden sm:inline">Copy Link</span></span>
                    <span x-show="copySuccess" class="text-emerald-400 font-bold">✓ Copied</span>
                </button>

                @if($canEdit && $post->edit_url)
                    <a href="{{ $post->edit_url }}" class="hidden sm:inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-indigo-600/30 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-200 hover:text-white text-xs font-bold transition-all">
                        ✏️ Edit
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Ambient Background Lighting -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] bg-purple-600/20 rounded-full blur-[140px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[34rem] h-[34rem] bg-indigo-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-2/3 -left-20 w-[30rem] h-[30rem] bg-cyan-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[40rem] h-[40rem] bg-purple-900/20 rounded-full blur-[160px]"></div>
    </div>

    <!-- Public Navigation Bar -->
    <x-public-header />

    <!-- Editorial Hero Masthead -->
    <section class="pt-28 pb-8 px-4 sm:px-6 lg:px-8 border-b border-white/5">
        <div class="max-w-5xl mx-auto space-y-7">
            <!-- Breadcrumbs & Quick Return Bar -->
            <div class="flex items-center justify-between gap-4 flex-wrap text-xs text-slate-400">
                <nav class="flex items-center gap-2 flex-wrap">
                    <a href="/" class="hover:text-white transition-colors">Home</a>
                    <span class="text-slate-600">/</span>
                    <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Journal</a>
                    <span class="text-slate-600">/</span>
                    <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="text-indigo-400 hover:underline font-medium">{{ $post->category }}</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('blog.index') }}" class="hover:text-white flex items-center gap-1.5 transition-colors">
                        <span>&larr;</span>
                        <span>All Articles</span>
                    </a>
                    @if($canEdit && $post->edit_url)
                        <a href="{{ $post->edit_url }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600/25 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-200 hover:text-white text-xs font-bold transition-all shadow-sm">
                            <span>✏️</span>
                            <span>Edit in Studio</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Category Kicker & Metadata Pill -->
            <div class="flex items-center gap-3 flex-wrap text-xs">
                <x-glass.badge variant="violet">
                    {{ $post->category }}
                </x-glass.badge>

                @if($post->status === 'draft')
                    <x-glass.badge variant="amber">
                        ⚠️ Private Draft Preview
                    </x-glass.badge>
                @endif

                <span class="text-slate-500">•</span>
                <span class="text-slate-400 font-mono text-[11px]">⏱️ {{ $post->reading_time_minutes }} min read</span>
                <span class="text-slate-500">•</span>
                <span class="text-slate-400 font-mono text-[11px]">👁️ {{ number_format($post->views_count) }} views</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.14]">
                {{ $post->title }}
            </h1>

            <!-- Subtitle / Excerpt Dek -->
            @if(!empty($post->excerpt))
                <p class="text-base sm:text-xl text-slate-300 leading-relaxed font-normal max-w-4xl">
                    {{ $post->excerpt }}
                </p>
            @endif

            <!-- Clean Editorial Byline Strip -->
            <div class="pt-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-500 border border-white/20 flex items-center justify-center font-bold text-sm text-white shadow-md shrink-0">
                        {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white flex items-center gap-2">
                            <span>{{ $post->user->name ?? 'HelpOfAi Staff' }}</span>
                            <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 text-[9px] uppercase font-mono font-bold border border-indigo-500/30">Author</span>
                        </div>
                        <div class="text-xs text-slate-400 flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5" aria-label="Published on {{ $post->published_at?->format('F d, Y') ?? 'Recently' }} and update on {{ $post->updated_at?->format('F d, Y') }}">
                            <span>Published on {{ $post->published_at?->format('F d, Y') ?? 'Recently' }}</span>
                            @if($post->updated_at && (!$post->published_at || $post->updated_at->format('Y-m-d') > $post->published_at->format('Y-m-d')))
                                <span class="text-slate-600">•</span>
                                <span class="text-slate-300 font-medium">Updated on {{ $post->updated_at->format('F d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Inline Share Actions -->
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        x-on:click="navigator.clipboard.writeText(window.location.href); copySuccess = true; setTimeout(() => copySuccess = false, 2500);" 
                        class="px-3.5 py-1.5 rounded-xl glass-subtle hover:border-white/25 text-xs text-slate-300 hover:text-white transition-all flex items-center gap-1.5 cursor-pointer shadow-sm" 
                        title="Copy link to clipboard"
                    >
                        <span x-show="!copySuccess">🔗 Copy Link</span>
                        <span x-show="copySuccess" class="text-emerald-400 font-bold">✓ Copied!</span>
                    </button>

                    <a 
                        href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(request()->url()) }}" 
                        target="_blank" 
                        class="p-2 px-3 rounded-xl glass-subtle hover:border-sky-500/40 text-slate-400 hover:text-sky-400 text-xs transition-colors"
                        title="Share on X / Twitter"
                    >
                        𝕏
                    </a>

                    <a 
                        href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                        target="_blank" 
                        class="p-2 px-3 rounded-xl glass-subtle hover:border-blue-500/40 text-slate-400 hover:text-blue-400 text-xs transition-colors"
                        title="Share on LinkedIn"
                    >
                        in
                    </a>
                </div>
            </div>

            <!-- Featured Cover Image Banner (If available) -->
            @if(!empty($post->featured_image))
                <div class="pt-2">
                    <div class="rounded-3xl overflow-hidden border border-white/10 bg-slate-900 hoa-editor-shadow aspect-[21/9] relative shadow-2xl">
                        <img 
                            src="{{ $post->featured_image }}" 
                            alt="{{ $post->title }}" 
                            class="w-full h-full object-cover" 
                            onerror="this.style.display='none'" 
                        />
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Main Editorial Body (2-Column Magazine Grid) -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 xl:gap-16 items-start">
            
            <!-- Left / Center: Reading Column -->
            <div class="lg:col-span-8 max-w-[740px] mx-auto lg:mx-0 w-full space-y-12">
                <!-- Mobile Table of Contents Accordion (visible only on mobile/tablet when toc has items) -->
                <div x-show="toc.length > 0" class="lg:hidden glass-standard rounded-2xl p-4 border border-white/10 space-y-3">
                    <button 
                        type="button" 
                        @click="mobileTocOpen = !mobileTocOpen" 
                        class="w-full flex items-center justify-between text-xs font-bold text-slate-200 tracking-wide uppercase cursor-pointer"
                    >
                        <span class="flex items-center gap-2">
                            <span>📑</span>
                            <span>Table of Contents</span>
                            <span class="text-indigo-400 font-mono" x-text="'(' + toc.length + ')'"></span>
                        </span>
                        <span class="text-slate-400 transform transition-transform" :class="{ 'rotate-180': mobileTocOpen }">▾</span>
                    </button>
                    <div x-show="mobileTocOpen" class="pt-2 border-t border-white/10 space-y-1.5 max-h-64 overflow-y-auto">
                        <template x-for="item in toc" :key="item.id">
                            <a 
                                :href="'#' + item.id" 
                                @click.prevent="scrollToHeading(item.id)" 
                                class="block text-xs py-1 transition-colors"
                                :class="{
                                    'pl-2 font-medium': item.level === 'h2',
                                    'pl-6 text-slate-400 text-[11px]': item.level === 'h3',
                                    'text-indigo-400 font-bold': activeHeading === item.id,
                                    'text-slate-300 hover:text-white': activeHeading !== item.id
                                }"
                                x-text="item.text"
                            ></a>
                        </template>
                    </div>
                </div>

                <!-- Article Content (Enterprise Markdown & Typography Rendering) -->
                <article class="hoa-article-content markdown-body max-w-none">
                    {!! $post->content_html !!}
                </article>

                <!-- Tags Strip -->
                @if(!empty($post->tags) && count($post->tags) > 0)
                    <div class="pt-6 border-t border-white/10 flex items-center gap-2 flex-wrap text-xs">
                        <span class="text-slate-400 font-semibold">Filed under:</span>
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.index', ['tag' => $tag]) }}">
                                <x-glass.badge variant="violet" class="hover:border-indigo-400/60 transition-colors cursor-pointer">
                                    #{{ $tag }}
                                </x-glass.badge>
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Author Bio Box -->
                <x-glass.card variant="elevated" class="p-6 sm:p-8 rounded-3xl flex flex-col sm:flex-row items-start sm:items-center gap-5 border border-white/10">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-500 border border-white/20 flex items-center justify-center font-black text-2xl text-white shadow-xl shrink-0">
                        {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="space-y-2 flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <h4 class="text-base font-bold text-white">Written by {{ $post->user->name ?? 'HelpOfAi Staff' }}</h4>
                            <span class="text-[10px] uppercase font-mono tracking-wider font-bold px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Verified Author</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Published and curated on HelpOfAi Studio — the professional AI production platform combining multi-agent LLMs, TipTap editing, and real-time SEO intelligence.
                        </p>
                        <div class="pt-1 flex items-center gap-3 text-xs text-indigo-400 font-medium">
                            <a href="{{ route('blog.index', ['search' => $post->user->name ?? '']) }}" class="hover:underline flex items-center gap-1">
                                <span>View all articles by {{ $post->user->name ?? 'Author' }}</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </x-glass.card>

                <!-- Previous & Next Article Circulation Cards -->
                @if(isset($previousPost) || isset($nextPost))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-white/10">
                        @if(isset($previousPost) && $previousPost)
                            <a href="{{ route('blog.show', $previousPost->slug) }}" class="group glass-standard rounded-2xl p-5 border border-white/10 hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between space-y-3">
                                <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 flex items-center gap-1.5 group-hover:text-indigo-400 transition-colors">
                                    <span>&larr;</span>
                                    <span>Previous Article</span>
                                </span>
                                <h5 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2">
                                    {{ $previousPost->title }}
                                </h5>
                                <span class="text-[10px] font-mono text-slate-500">⏱️ {{ $previousPost->reading_time_minutes }} min read</span>
                            </a>
                        @else
                            <div class="hidden sm:block"></div>
                        @endif

                        @if(isset($nextPost) && $nextPost)
                            <a href="{{ route('blog.show', $nextPost->slug) }}" class="group glass-standard rounded-2xl p-5 border border-white/10 hover:border-indigo-500/40 transition-all duration-300 flex flex-col justify-between space-y-3 sm:text-right">
                                <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 flex items-center justify-end gap-1.5 group-hover:text-indigo-400 transition-colors">
                                    <span>Next Article</span>
                                    <span>&rarr;</span>
                                </span>
                                <h5 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2">
                                    {{ $nextPost->title }}
                                </h5>
                                <span class="text-[10px] font-mono text-slate-500">⏱️ {{ $nextPost->reading_time_minutes }} min read</span>
                            </a>
                        @endif
                    </div>
                @endif

                <!-- Related Articles Grid -->
                @if($relatedPosts->count() > 0)
                    <div class="space-y-5 pt-8 border-t border-white/10">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <span>More from</span>
                                <span class="text-indigo-400">{{ $post->category }}</span>
                            </h3>
                            <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                                Explore Category &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach($relatedPosts as $related)
                                <a href="{{ route('blog.show', $related->slug) }}" class="glass-standard rounded-2xl border border-white/10 hover:border-indigo-500/40 transition-all duration-300 hoa-card-glow-shadow p-4 flex flex-col justify-between group">
                                    <div class="space-y-2">
                                        <span class="text-[10px] font-mono text-slate-400">⏱️ {{ $related->reading_time_minutes }} min read</span>
                                        <h5 class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2">
                                            {{ $related->title }}
                                        </h5>
                                    </div>
                                    <span class="text-[11px] text-indigo-400 group-hover:text-indigo-300 mt-4 font-semibold flex items-center gap-1">&rarr; Read article</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Bottom CTA -->
                <x-glass.card variant="premium" glow="violet" class="p-8 sm:p-10 relative overflow-hidden hoa-welcome-glow-border hoa-editor-shadow text-center space-y-3">
                    <h3 class="text-xl sm:text-2xl font-black text-white">Loved this article? Create your own with AI</h3>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-md mx-auto">
                        Generate, edit, and publish your own high-retention content with HelpOfAi Studio.
                    </p>
                    <div class="pt-3">
                        <a href="{{ route('editor') }}">
                            <x-glass.button variant="primary" size="md" shimmer="true" class="shadow-xl shadow-indigo-600/30">
                                ✍️ Launch Studio Editor &rarr;
                            </x-glass.button>
                        </a>
                    </div>
                </x-glass.card>
            </div>

            <!-- Right: Sticky Editorial Rail (Desktop) -->
            <aside class="hidden lg:block lg:col-span-4 sticky top-28 space-y-6">
                <!-- Dynamic Table of Contents -->
                <div x-show="toc.length > 0" class="glass-standard rounded-3xl p-5 border border-white/10 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-2">
                            <span>📑</span>
                            <span>Contents</span>
                        </h4>
                        <span class="text-[10px] font-mono text-slate-400" x-text="toc.length + ' sections'"></span>
                    </div>

                    <nav class="space-y-1 max-h-56 overflow-y-auto pr-1 hoa-custom-scrollbar">
                        <template x-for="item in toc" :key="item.id">
                            <a 
                                :href="'#' + item.id" 
                                @click.prevent="scrollToHeading(item.id)" 
                                class="block text-xs py-1.5 transition-all duration-200 leading-snug rounded-lg"
                                :class="{
                                    'font-semibold text-indigo-300 bg-indigo-500/15 border-l-2 border-indigo-400 pl-2.5': activeHeading === item.id,
                                    'text-slate-400 hover:text-slate-200 hover:bg-white/5 pl-2': activeHeading !== item.id,
                                    'ml-3 text-[11px]': item.level === 'h3'
                                }"
                                x-text="item.text"
                            ></a>
                        </template>
                    </nav>
                </div>

                <!-- Tabbed Discovery Hub: Similar Posts, Author Articles & Trending -->
                <div x-data="{ sidebarTab: 'similar' }" class="glass-standard rounded-3xl p-5 border border-white/10 space-y-4 shadow-xl">
                    <!-- Tab Controls Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-white/10">
                        <div class="flex items-center p-1 rounded-xl bg-slate-900/80 border border-white/10 w-full gap-1">
                            <button 
                                type="button" 
                                @click="sidebarTab = 'similar'" 
                                class="flex-1 py-1.5 px-2 rounded-lg text-[11px] font-medium transition-all text-center cursor-pointer select-none"
                                :class="sidebarTab === 'similar' ? 'bg-indigo-600/35 text-indigo-200 font-bold border border-indigo-500/40 shadow-sm' : 'text-slate-400 hover:text-white'"
                            >
                                Similar
                            </button>
                            <button 
                                type="button" 
                                @click="sidebarTab = 'author'" 
                                class="flex-1 py-1.5 px-2 rounded-lg text-[11px] font-medium transition-all text-center cursor-pointer select-none"
                                :class="sidebarTab === 'author' ? 'bg-indigo-600/35 text-indigo-200 font-bold border border-indigo-500/40 shadow-sm' : 'text-slate-400 hover:text-white'"
                            >
                                By Author
                            </button>
                            <button 
                                type="button" 
                                @click="sidebarTab = 'trending'" 
                                class="flex-1 py-1.5 px-2 rounded-lg text-[11px] font-medium transition-all text-center cursor-pointer select-none"
                                :class="sidebarTab === 'trending' ? 'bg-indigo-600/35 text-indigo-200 font-bold border border-indigo-500/40 shadow-sm' : 'text-slate-400 hover:text-white'"
                            >
                                Trending
                            </button>
                        </div>
                    </div>

                    <!-- Tab 1: Similar / Category Related Posts -->
                    <div x-show="sidebarTab === 'similar'" class="space-y-3">
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span class="font-medium text-slate-300">In {{ $post->category }}</span>
                            <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="hover:text-indigo-300 transition-colors">See all &rarr;</a>
                        </div>
                        @if(isset($similarPosts) && $similarPosts->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1 hoa-custom-scrollbar">
                                @foreach($similarPosts as $similar)
                                    <a href="{{ route('blog.show', $similar->slug) }}" class="group block p-2.5 rounded-xl glass-subtle hover:border-indigo-500/40 transition-all">
                                        <div class="flex items-start gap-2.5">
                                            @if(!empty($similar->featured_image))
                                                <img src="{{ $similar->featured_image }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-white/10 shrink-0" onerror="this.style.display='none'">
                                            @else
                                                <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-indigo-500/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-xs text-indigo-300 shrink-0 font-bold">
                                                    {{ strtoupper(substr($similar->title, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1 space-y-1">
                                                <h5 class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
                                                    {{ $similar->title }}
                                                </h5>
                                                <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400">
                                                    <span>⏱️ {{ $similar->reading_time_minutes }}m</span>
                                                    <span>•</span>
                                                    <span>👁️ {{ number_format($similar->views_count) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 py-3 text-center">No other articles in this category yet.</p>
                        @endif
                    </div>

                    <!-- Tab 2: Articles by This Author -->
                    <div x-show="sidebarTab === 'author'" class="space-y-3" style="display: none;">
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span class="font-medium text-slate-300">By {{ $post->user->name ?? 'This Author' }}</span>
                            <a href="{{ route('blog.index', ['search' => $post->user->name ?? '']) }}" class="hover:text-indigo-300 transition-colors">Author archive &rarr;</a>
                        </div>
                        @if(isset($authorPosts) && $authorPosts->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1 hoa-custom-scrollbar">
                                @foreach($authorPosts as $authorArticle)
                                    <a href="{{ route('blog.show', $authorArticle->slug) }}" class="group block p-2.5 rounded-xl glass-subtle hover:border-indigo-500/40 transition-all">
                                        <div class="flex items-start gap-2.5">
                                            @if(!empty($authorArticle->featured_image))
                                                <img src="{{ $authorArticle->featured_image }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-white/10 shrink-0" onerror="this.style.display='none'">
                                            @else
                                                <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-purple-500/20 to-cyan-500/20 border border-white/10 flex items-center justify-center text-xs text-purple-300 shrink-0 font-bold">
                                                    ✍️
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1 space-y-1">
                                                <h5 class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
                                                    {{ $authorArticle->title }}
                                                </h5>
                                                <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400">
                                                    <span class="text-indigo-400">{{ $authorArticle->category }}</span>
                                                    <span>•</span>
                                                    <span>⏱️ {{ $authorArticle->reading_time_minutes }}m</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 py-3 text-center">First published article by this author.</p>
                        @endif
                    </div>

                    <!-- Tab 3: Trending Across Studio -->
                    <div x-show="sidebarTab === 'trending'" class="space-y-3" style="display: none;">
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span class="font-medium text-slate-300">Most Read in Journal</span>
                            <span class="text-[10px] font-mono text-cyan-400">🔥 Top Stories</span>
                        </div>
                        @if(isset($trendingPosts) && $trendingPosts->count() > 0)
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1 hoa-custom-scrollbar">
                                @foreach($trendingPosts as $index => $trending)
                                    <a href="{{ route('blog.show', $trending->slug) }}" class="group block p-2.5 rounded-xl glass-subtle hover:border-indigo-500/40 transition-all">
                                        <div class="flex items-start gap-3">
                                            <span class="text-xs font-black font-mono text-slate-500 group-hover:text-indigo-400 transition-colors w-4 text-center shrink-0">
                                                0{{ $index + 1 }}
                                            </span>
                                            <div class="min-w-0 flex-1 space-y-1">
                                                <h5 class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-2 leading-snug">
                                                    {{ $trending->title }}
                                                </h5>
                                                <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400">
                                                    <span class="text-indigo-400">{{ $trending->category }}</span>
                                                    <span>•</span>
                                                    <span>👁️ {{ number_format($trending->views_count) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 py-3 text-center">Trending articles will appear here as views grow.</p>
                        @endif
                    </div>
                </div>

                <!-- Floating Share Card -->
                <div class="glass-subtle rounded-2xl p-4 border border-white/10 space-y-3 shadow-lg">
                    <span class="text-[10px] uppercase font-mono tracking-wider text-slate-400 font-bold block">Share this story</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            x-on:click="navigator.clipboard.writeText(window.location.href); copySuccess = true; setTimeout(() => copySuccess = false, 2500);" 
                            class="p-2.5 rounded-xl glass-subtle hover:border-white/25 text-xs text-slate-300 hover:text-white transition-all flex flex-col items-center gap-1 cursor-pointer"
                            title="Copy Link"
                        >
                            <span x-show="!copySuccess">🔗</span>
                            <span x-show="copySuccess" class="text-emerald-400 font-bold">✓</span>
                            <span class="text-[10px]" x-text="copySuccess ? 'Copied' : 'Copy'"></span>
                        </button>

                        <a 
                            href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(request()->url()) }}" 
                            target="_blank" 
                            class="p-2.5 rounded-xl glass-subtle hover:border-sky-500/40 text-slate-400 hover:text-sky-400 text-xs transition-all flex flex-col items-center gap-1"
                            title="Share on X / Twitter"
                        >
                            <span>𝕏</span>
                            <span class="text-[10px]">Post</span>
                        </a>

                        <a 
                            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                            target="_blank" 
                            class="p-2.5 rounded-xl glass-subtle hover:border-blue-500/40 text-slate-400 hover:text-blue-400 text-xs transition-all flex flex-col items-center gap-1"
                            title="Share on LinkedIn"
                        >
                            <span>in</span>
                            <span class="text-[10px]">Share</span>
                        </a>
                    </div>
                </div>

                <!-- Editorial Meta Card -->
                <div class="glass-subtle rounded-2xl p-4 border border-white/10 flex items-center gap-3.5 shadow-lg">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-500 border border-white/20 flex items-center justify-center font-bold text-sm text-white shadow-md shrink-0">
                        {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-slate-400 block font-medium">Published by</span>
                        <span class="text-xs font-bold text-white truncate block">{{ $post->user->name ?? 'HelpOfAi Staff' }}</span>
                        <span class="text-[10px] text-indigo-400 font-mono">{{ $post->reading_time_minutes }} min read • {{ number_format($post->views_count) }} views</span>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Floating Resume Reading Toast Banner -->
    <div 
        x-show="showResumeBanner" 
        x-cloak
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-8 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-8 opacity-0"
        class="fixed bottom-6 right-6 z-40 max-w-sm glass-standard p-3.5 rounded-2xl border border-indigo-500/40 shadow-2xl flex items-center gap-3 backdrop-blur-xl hoa-welcome-glow-border"
    >
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-sm font-bold text-white shadow-md shrink-0">
            <span>🔖</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-white">Pick up where you left off</p>
            <p class="text-[10px] text-slate-400">Jump straight back to your saved reading point</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button 
                type="button" 
                @click="jumpToSavedPosition()" 
                class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition-all cursor-pointer hover:scale-105"
            >
                Jump &rarr;
            </button>
            <button 
                type="button" 
                @click="dismissResumeBanner()" 
                class="p-1 text-slate-400 hover:text-white text-xs cursor-pointer transition-colors"
                title="Dismiss"
            >
                ✕
            </button>
        </div>
    </div>

    <!-- Public Footer matching welcome.blade.php -->
    <x-public-footer />

    <!-- Reader Script for Dynamic Table of Contents, Scroll Tracking & Code Snippet Copy -->
    <script>
        function hoaBlogPostReader(slug) {
            return {
                slug: slug || '',
                scrollProgress: 0,
                showFloatingHeader: false,
                copySuccess: false,
                activeHeading: '',
                toc: [],
                mobileTocOpen: false,
                saveTimer: null,
                showResumeBanner: false,
                savedScrollY: 0,

                init() {
                    // Check previous reading progress
                    try {
                        const saved = localStorage.getItem('hoa_read_progress_' + this.slug);
                        if (saved) {
                            const data = JSON.parse(saved);
                            if (data) {
                                if (typeof data.progress === 'number') {
                                    this.scrollProgress = data.progress;
                                }
                                if (data.scrollY > 350 && !data.completed && !window.location.hash) {
                                    this.savedScrollY = data.scrollY;
                                    this.showResumeBanner = true;
                                }
                            }
                        }
                    } catch (e) {}

                    this.updateScroll();
                    window.addEventListener('scroll', () => this.updateScroll(), { passive: true });
                    window.addEventListener('hoa:blog-content-enhanced', () => {
                        this.buildTableOfContents();
                        this.setupCodeBlocks();
                    });
                    this.buildTableOfContents();
                    this.setupCodeBlocks();
                    if (window.initBlogVisualEnhancer) {
                        window.initBlogVisualEnhancer();
                    }
                },

                jumpToSavedPosition() {
                    if (this.savedScrollY > 0) {
                        window.scrollTo({ top: this.savedScrollY, behavior: 'smooth' });
                    }
                    this.showResumeBanner = false;
                },

                dismissResumeBanner() {
                    this.showResumeBanner = false;
                },

                updateScroll() {
                    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                    if (docHeight > 0) {
                        this.scrollProgress = Math.min(100, Math.max(0, Math.round((window.scrollY / docHeight) * 100)));
                    }
                    this.showFloatingHeader = window.scrollY > 420;

                    // If user manually scrolls near saved position, dismiss banner
                    if (this.showResumeBanner && Math.abs(window.scrollY - this.savedScrollY) < 120) {
                        this.showResumeBanner = false;
                    }

                    // Debounced write to localStorage
                    if (this.slug) {
                        clearTimeout(this.saveTimer);
                        this.saveTimer = setTimeout(() => {
                            try {
                                const existingRaw = localStorage.getItem('hoa_read_progress_' + this.slug);
                                let wasCompleted = false;
                                if (existingRaw) {
                                    const parsed = JSON.parse(existingRaw);
                                    wasCompleted = !!parsed.completed;
                                }
                                const isCompleted = wasCompleted || this.scrollProgress >= 88;
                                const finalProgress = isCompleted ? 100 : this.scrollProgress;

                                localStorage.setItem('hoa_read_progress_' + this.slug, JSON.stringify({
                                    progress: finalProgress,
                                    completed: isCompleted,
                                    scrollY: window.scrollY,
                                    updated_at: Date.now()
                                }));
                            } catch (e) {}
                        }, 100);
                    }

                    if (this.toc.length > 0) {
                        const scrollPos = window.scrollY + 160;
                        for (let i = this.toc.length - 1; i >= 0; i--) {
                            const el = document.getElementById(this.toc[i].id);
                            if (el && el.offsetTop <= scrollPos) {
                                this.activeHeading = this.toc[i].id;
                                break;
                            }
                        }
                    }
                },

                buildTableOfContents() {
                    this.$nextTick(() => {
                        const article = document.querySelector('.hoa-article-content');
                        if (!article) return;
                        const headings = article.querySelectorAll('h2, h3');
                        const items = [];
                        headings.forEach((el, index) => {
                            let id = el.id;
                            if (!id) {
                                id = 'section-' + (el.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || (index + 1));
                                el.id = id;
                            }
                            items.push({
                                id: id,
                                text: el.textContent.trim(),
                                level: el.tagName.toLowerCase()
                            });
                        });
                        this.toc = items;
                        if (items.length > 0 && !this.activeHeading) {
                            this.activeHeading = items[0].id;
                        }
                    });
                },

                setupCodeBlocks() {
                    this.$nextTick(() => {
                        const pres = document.querySelectorAll('.hoa-article-content pre');
                        pres.forEach((pre) => {
                            if (pre.querySelector('.code-copy-btn')) return;
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'code-copy-btn absolute top-3 right-3 px-2.5 py-1 rounded-lg text-[11px] font-mono font-medium transition-all backdrop-blur-md opacity-70 hover:opacity-100 focus:opacity-100 bg-white/10 hover:bg-white/20 border border-white/15 text-slate-300 hover:text-white shadow-sm flex items-center gap-1 cursor-pointer select-none';
                            btn.innerHTML = '<span>📋</span> <span>Copy</span>';
                            btn.onclick = () => {
                                const code = pre.querySelector('code') ? pre.querySelector('code').innerText : pre.innerText;
                                navigator.clipboard.writeText(code);
                                btn.innerHTML = '<span class="text-emerald-400">✓</span> <span class="text-emerald-300 font-bold">Copied!</span>';
                                setTimeout(() => {
                                    btn.innerHTML = '<span>📋</span> <span>Copy</span>';
                                }, 2000);
                            };
                            pre.appendChild(btn);
                        });
                    });
                },

                scrollToHeading(id) {
                    const el = document.getElementById(id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth' });
                        this.activeHeading = id;
                        this.mobileTocOpen = false;
                    }
                }
            };
        }
    </script>
</div>

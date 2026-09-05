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

<div class="hoa-blog-post-page min-h-screen flex flex-col bg-slate-950 text-slate-100 selection:bg-indigo-500/30 selection:text-indigo-200" x-data="{ copySuccess: false }">
    <!-- Ambient Background Lighting matching welcome page -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[36rem] h-[36rem] bg-purple-600/20 rounded-full blur-[140px] animate-pulse"></div>
        <div class="absolute top-1/4 -right-40 w-[34rem] h-[34rem] bg-indigo-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-2/3 -left-20 w-[30rem] h-[30rem] bg-cyan-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 right-1/4 w-[40rem] h-[40rem] bg-purple-900/20 rounded-full blur-[160px]"></div>
    </div>

    <!-- Public Navigation Bar matching welcome.blade.php -->
    <x-public-header />

    <!-- Main Article Body -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 space-y-10">
        <!-- Breadcrumbs & Quick Action Bar -->
        <div class="glass-subtle rounded-2xl px-4 py-3 border border-white/10 flex items-center justify-between gap-4 flex-wrap">
            <nav class="flex items-center gap-2 text-xs text-slate-400 flex-wrap">
                <a href="/" class="hover:text-white transition-colors">Home</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('blog.index', ['category' => $post->category]) }}" class="text-indigo-400 hover:underline font-medium">{{ $post->category }}</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('blog.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
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

        <!-- Article Header -->
        <header class="space-y-5">
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

            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.12]">
                {{ $post->title }}
            </h1>

            @if(!empty($post->excerpt))
                <p class="text-base sm:text-xl text-slate-300 leading-relaxed font-normal">
                    {{ $post->excerpt }}
                </p>
            @endif

            <!-- Author Info & Share Bar -->
            <div class="glass-standard rounded-2xl p-4 sm:p-5 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 border border-white/20 flex items-center justify-center font-bold text-sm text-white shadow-md">
                        {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white flex items-center gap-2">
                            <span>{{ $post->user->name ?? 'HelpOfAi Staff' }}</span>
                            <span class="px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300 text-[9px] uppercase font-mono font-bold">Author</span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Published on {{ $post->published_at?->format('F d, Y') ?? 'Recently' }}
                        </div>
                    </div>
                </div>

                <!-- Share Buttons -->
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
                        class="p-2 rounded-xl glass-subtle hover:border-sky-500/40 text-slate-400 hover:text-sky-400 text-xs transition-colors"
                        title="Share on X / Twitter"
                    >
                        𝕏
                    </a>

                    <a 
                        href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                        target="_blank" 
                        class="p-2 rounded-xl glass-subtle hover:border-blue-500/40 text-slate-400 hover:text-blue-400 text-xs transition-colors"
                        title="Share on LinkedIn"
                    >
                        in
                    </a>
                </div>
            </div>
        </header>

        <!-- Featured Cover Image Banner (If available) -->
        @if(!empty($post->featured_image))
            <div class="rounded-3xl overflow-hidden border border-white/10 bg-slate-900 hoa-editor-shadow aspect-[21/9] relative">
                <img 
                    src="{{ $post->featured_image }}" 
                    alt="{{ $post->title }}" 
                    class="w-full h-full object-cover" 
                    onerror="this.style.display='none'" 
                />
            </div>
        @endif

        <!-- Article Content (Enterprise Prose Rendering) -->
        <article class="prose prose-invert prose-lg max-w-none prose-indigo prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-white prose-p:text-slate-300 prose-p:leading-relaxed prose-a:text-indigo-400 hover:prose-a:text-indigo-300 prose-img:rounded-2xl prose-img:border prose-img:border-white/10 prose-pre:bg-slate-900/90 prose-pre:border prose-pre:border-white/10 prose-table:border prose-table:border-white/10 py-6">
            {!! $post->content_html !!}
        </article>

        <!-- Tags List -->
        @if(!empty($post->tags) && count($post->tags) > 0)
            <div class="pt-6 border-t border-white/10 flex items-center gap-2 flex-wrap text-xs">
                <span class="text-slate-400 font-semibold">Tags:</span>
                @foreach($post->tags as $tag)
                    <a href="{{ route('blog.index', ['search' => $tag]) }}">
                        <x-glass.badge variant="indigo">
                            #{{ $tag }}
                        </x-glass.badge>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Author Bio Box -->
        <x-glass.card variant="glass" class="p-6 rounded-3xl flex flex-col sm:flex-row items-start sm:items-center gap-4 border border-white/10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 border border-white/20 flex items-center justify-center font-bold text-xl text-white shadow-lg shrink-0">
                {{ strtoupper(substr($post->user->name ?? 'A', 0, 1)) }}
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-white">Written by {{ $post->user->name ?? 'HelpOfAi Studio Author' }}</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Published and curated on HelpOfAi Studio — the professional AI production platform combining multi-agent LLMs, TipTap editing, and real-time SEO intelligence.
                </p>
            </div>
        </x-glass.card>

        <!-- Related Articles -->
        @if($relatedPosts->count() > 0)
            <div class="space-y-5 pt-8 border-t border-white/10">
                <h3 class="text-lg font-bold text-white">More Articles in {{ $post->category }}</h3>
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
        <x-glass.card variant="premium" glow="indigo" class="p-8 sm:p-10 relative overflow-hidden hoa-welcome-glow-border hoa-editor-shadow text-center space-y-3">
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
    </main>

    <!-- Public Footer matching welcome.blade.php -->
    <x-public-footer />
</div>

{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Dashboard Blog Manager Blade View
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

<div class="hoa-blog-manager space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>Blog Articles Manager</span>
                <x-glass.badge variant="violet">Blog System</x-glass.badge>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Manage your published articles, monitor real-time reader views, and publish new content to the HelpOfAi Studio blog journal.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('blog.index') }}" 
                target="_blank"
                class="px-3.5 py-2 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
            >
                <span>🌐</span>
                <span>Visit Public Blog ↗</span>
            </a>

            <button 
                type="button" 
                wire:click="createNewBlogArticle"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg shadow-violet-500/25 transition-all flex items-center gap-2 cursor-pointer"
            >
                <span>➕</span>
                <span>Create Blog Article</span>
            </button>
        </div>
    </div>

    <!-- Alert / Feedback Banner -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/30 text-xs sm:text-sm text-emerald-300 flex items-center justify-between gap-3 animate-fade-in shadow-lg shadow-emerald-950/40">
            <div class="flex items-center gap-2.5">
                <span class="text-base">✅</span>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <!-- Top KPI Cards Grid (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Articles -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Articles</span>
                <span class="text-xl">📰</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-white tracking-tight">{{ number_format($stats['total']) }}</span>
                <span class="text-[11px] text-slate-400 font-mono">articles created</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Created in AI Document Studio
            </div>
        </x-glass.card>

        <!-- Card 2: Published Live -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Published Live</span>
                <span class="text-xl">🚀</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-400 tracking-tight">{{ number_format($stats['published']) }}</span>
                <span class="text-[11px] text-emerald-500/80 font-mono">active posts</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Visible on public blog journal
            </div>
        </x-glass.card>

        <!-- Card 3: Drafts -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Draft Articles</span>
                <span class="text-xl">📝</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-amber-300 tracking-tight">{{ number_format($stats['drafts']) }}</span>
                <span class="text-[11px] text-slate-400 font-mono">in progress</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Unpublished private drafts
            </div>
        </x-glass.card>

        <!-- Card 4: Reader Views -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Cumulative Views</span>
                <span class="text-xl">👁️</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-cyan-300 tracking-tight">{{ number_format($stats['views']) }}</span>
                <span class="text-[11px] text-slate-400 font-mono">total reads</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400">
                Unique reader engagement
            </div>
        </x-glass.card>
    </div>

    <!-- Filter & Search Toolbar -->
    <x-glass.card variant="subtle" class="p-4 flex flex-col sm:flex-row items-center justify-between gap-3 border border-white/10">
        <!-- Search Input -->
        <div class="w-full sm:flex-1 max-w-md">
            <x-glass.input wire:model.live.debounce.300ms="search" placeholder="Search by title, excerpt, or category..." />
        </div>

        <!-- Status Filter Buttons -->
        <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto">
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'all')"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-violet-600 text-white shadow-md' : 'bg-slate-900 border border-white/10 text-slate-300 hover:text-white' }}"
            >
                All ({{ $stats['total'] }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'published')"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $statusFilter === 'published' ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-900 border border-white/10 text-slate-300 hover:text-white' }}"
            >
                Published ({{ $stats['published'] }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'draft')"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer {{ $statusFilter === 'draft' ? 'bg-amber-600 text-white shadow-md' : 'bg-slate-900 border border-white/10 text-slate-300 hover:text-white' }}"
            >
                Drafts ({{ $stats['drafts'] }})
            </button>
        </div>
    </x-glass.card>

    <!-- Articles Table -->
    <x-glass.card variant="standard" class="p-0 overflow-hidden border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/90 text-slate-400 border-b border-white/10 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="p-4">Article</th>
                        <th class="p-4">Category & Tags</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Readers</th>
                        <th class="p-4">Published Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-200">
                    @forelse($posts as $post)
                        <tr class="hover:bg-white/[0.03] transition-colors">
                            <!-- Article Title & Slug -->
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if(!empty($post->featured_image))
                                        <img src="{{ $post->featured_image }}" alt="" class="w-12 h-12 rounded-xl object-cover border border-white/10 shrink-0 bg-slate-950" />
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-violet-950/60 border border-white/10 flex items-center justify-center text-lg text-violet-300 shrink-0">
                                            📰
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-sm hover:text-violet-300 transition-colors truncate max-w-sm">
                                            @if($post->document_id)
                                                <a href="{{ route('documents.editor', $post->document_id) }}">
                                                    {{ $post->title }}
                                                </a>
                                            @else
                                                {{ $post->title }}
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono truncate max-w-sm">
                                            /blog/{{ $post->slug }}
                                        </div>
                                        @if($post->is_featured)
                                            <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 text-[9px] font-bold uppercase">⭐ Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Category & Tags -->
                            <td class="p-4">
                                <div>
                                    <x-glass.badge variant="violet">
                                        {{ $post->category }}
                                    </x-glass.badge>
                                    @if(!empty($post->tags) && count($post->tags) > 0)
                                        <div class="text-[10px] text-slate-400 mt-1 truncate max-w-xs font-mono">
                                            {{ implode(', ', array_slice($post->tags, 0, 3)) }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Button -->
                            <td class="p-4">
                                <button 
                                    type="button" 
                                    wire:click="togglePostStatus({{ $post->id }})"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase transition-all cursor-pointer {{ $post->status === 'published' ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-amber-500/10 text-amber-300 border border-amber-500/30 hover:bg-amber-500/20' }}"
                                    title="Click to toggle between Published and Draft"
                                >
                                    {{ $post->status === 'published' ? '● Published' : '○ Draft' }}
                                </button>
                            </td>

                            <!-- Reader Views -->
                            <td class="p-4 font-mono text-xs">
                                <span class="text-cyan-300 font-bold">👁️ {{ number_format($post->views_count) }}</span>
                                <span class="text-[10px] text-slate-500 block">⏱️ {{ $post->reading_time_minutes }}m read</span>
                            </td>

                            <!-- Published Date -->
                            <td class="p-4 font-mono text-xs text-slate-400">
                                <div>{{ $post->published_at?->format('M d, Y') ?? 'Not Published' }}</div>
                                <div class="text-[10px] text-slate-500">Updated {{ $post->updated_at->diffForHumans() }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit in Studio -->
                                    @if($post->document_id)
                                        <a 
                                            href="{{ route('documents.editor', $post->document_id) }}" 
                                            class="px-3 py-1.5 rounded-xl bg-violet-600/20 border border-violet-500/30 hover:bg-violet-600 hover:text-white text-violet-300 text-xs font-semibold transition-all cursor-pointer"
                                            title="Edit article in AI Document Studio"
                                        >
                                            Edit
                                        </a>
                                    @endif

                                    <!-- View Live Post -->
                                    <a 
                                        href="{{ route('blog.show', $post->slug) }}" 
                                        target="_blank" 
                                        class="px-2.5 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-white/25 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer"
                                        title="View live post on public blog"
                                    >
                                        ↗ View
                                    </a>

                                    <!-- Delete Post -->
                                    <button 
                                        type="button" 
                                        wire:click="deletePost({{ $post->id }})" 
                                        wire:confirm="Are you sure you want to delete this blog post?" 
                                        class="p-1.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 hover:bg-rose-900 hover:text-white text-xs transition-all cursor-pointer"
                                        title="Delete blog post"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                <div class="text-4xl mb-3">📰</div>
                                <h3 class="text-base font-bold text-white">No Blog Articles Yet</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    You haven't published any articles to the blog yet. Open any document in the AI Document Studio and click "Post to Blog".
                                </p>
                                <button 
                                    type="button" 
                                    wire:click="createNewBlogArticle" 
                                    class="mt-4 px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg shadow-violet-500/25 transition-all cursor-pointer"
                                >
                                    ✍️ Write Your First Article
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="p-4 border-t border-white/5">
                {{ $posts->links() }}
            </div>
        @endif
    </x-glass.card>
</div>

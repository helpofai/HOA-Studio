{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Modals Partial
|--------------------------------------------------------------------------
*/
--}}

<!-- Public & Protected Sharing Modal -->
@if($showShareModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-3xl glass-elevated border border-white/15 p-6 sm:p-8 space-y-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between pb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-lg text-indigo-300">🔗</div>
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight">Share & Publish Document</h3>
                        <p class="text-xs text-slate-400">Create an encrypted public view link with custom access controls.</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-white p-2">✕</button>
            </div>

            @if(session('share_status'))
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 font-medium">
                    {{ session('share_status') }}
                </div>
            @endif

            @if($isShareActive)
                <div class="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-500/30 space-y-3">
                    <label class="text-xs font-bold text-indigo-300 block">Active Public Share Link</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $shareUrl }}" class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white font-mono select-all focus:outline-none" />
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); alert('Link copied to clipboard!');" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs">Copy</button>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold">Close</button>
                <button type="button" wire:click="createOrUpdateShare" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30">
                    {{ $isShareActive ? 'Update Share Settings' : 'Generate Public Link' }}
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Blog Publishing Modal -->
@if($showBlogModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md">
        <div class="w-full max-w-xl rounded-3xl glass-elevated border border-violet-500/30 p-6 sm:p-8 space-y-5 shadow-2xl animate-in fade-in zoom-in-95 duration-200 relative max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 border border-white/20 flex items-center justify-center text-lg text-white shadow-lg shadow-violet-500/25">
                        📰
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight">Publish Article to Blog</h3>
                        <p class="text-xs text-slate-400">Post this article directly to the public HelpOfAi Studio blog.</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showBlogModal', false)" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/5 transition-all">✕</button>
            </div>

            <!-- Alerts -->
            @if(session('blog_status'))
                <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 font-medium flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('blog_status') }}</span>
                </div>
            @endif

            @error('blogTitle') <div class="p-2 rounded-xl bg-rose-500/10 text-rose-400 text-xs">{{ $message }}</div> @enderror
            @error('blogCategory') <div class="p-2 rounded-xl bg-rose-500/10 text-rose-400 text-xs">{{ $message }}</div> @enderror

            <!-- Live Status Banner -->
            @if($isPublishedToBlog && $blogPublishedUrl)
                <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-500/40 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-mono font-bold flex items-center gap-1.5 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            ARTICLE IS LIVE ON BLOG
                        </span>
                        <span class="text-xs text-slate-400 font-mono">👁️ {{ number_format($blogViewsCount) }} views</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ $blogPublishedUrl }}" class="flex-1 bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 font-mono select-all focus:outline-none" />
                        <button 
                            type="button" 
                            onclick="navigator.clipboard.writeText('{{ $blogPublishedUrl }}'); alert('Blog article link copied!');" 
                            class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors cursor-pointer"
                        >
                            Copy
                        </button>
                        <a 
                            href="{{ $blogPublishedUrl }}" 
                            target="_blank" 
                            class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all flex items-center gap-1 cursor-pointer shadow-lg shadow-emerald-600/20"
                        >
                            <span>View Post</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Post Meta Fields -->
            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label class="text-xs font-semibold text-slate-300 block mb-1.5">Article Headline</label>
                    <input 
                        type="text" 
                        wire:model="blogTitle" 
                        placeholder="Article Headline..." 
                        class="w-full bg-slate-900 border border-white/10 focus:border-violet-500 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none transition-colors"
                        required
                    />
                </div>

                <!-- Slug -->
                <div>
                    <label class="text-xs font-semibold text-slate-300 block mb-1.5">URL Permalink Slug</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 rounded-l-xl bg-slate-950/80 border border-r-0 border-white/10 text-xs text-slate-500 font-mono">/blog/</span>
                        <input 
                            type="text" 
                            wire:model="blogSlug" 
                            placeholder="my-awesome-article" 
                            class="flex-1 bg-slate-900 border border-white/10 focus:border-violet-500 rounded-r-xl px-3 py-2.5 text-xs text-white font-mono focus:outline-none transition-colors"
                        />
                    </div>
                </div>

                <!-- Category & Status Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-300 block mb-1.5">Primary Category</label>
                        <select wire:model="blogCategory" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500 cursor-pointer">
                            @foreach(\App\Features\Blog\Models\BlogPost::defaultCategories() as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-300 block mb-1.5">Publish Status</label>
                        <select wire:model="blogStatus" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500 cursor-pointer">
                            <option value="published">🚀 Published (Live to Public)</option>
                            <option value="draft">📝 Draft (Private)</option>
                        </select>
                    </div>
                </div>

                <!-- Tags -->
                <div>
                    <label class="text-xs font-semibold text-slate-300 block mb-1.5">Tags (Comma Separated)</label>
                    <input 
                        type="text" 
                        wire:model="blogTags" 
                        placeholder="AI, Writing, Marketing, TipTap, Strategy" 
                        class="w-full bg-slate-900 border border-white/10 focus:border-violet-500 rounded-xl px-3 py-2 text-xs text-white focus:outline-none transition-colors"
                    />
                </div>

                <!-- Featured Image URL -->
                <div>
                    <label class="text-xs font-semibold text-slate-300 block mb-1.5">Featured Cover Image URL (Optional)</label>
                    <input 
                        type="url" 
                        wire:model.live.debounce.400ms="blogFeaturedImage" 
                        placeholder="https://images.unsplash.com/photo-..." 
                        class="w-full bg-slate-900 border border-white/10 focus:border-violet-500 rounded-xl px-3 py-2 text-xs text-white focus:outline-none transition-colors"
                    />
                    @if(!empty($blogFeaturedImage))
                        <div class="mt-2 rounded-xl overflow-hidden border border-white/10 max-h-36 bg-slate-950 flex items-center justify-center">
                            <img src="{{ $blogFeaturedImage }}" alt="Cover preview" class="w-full h-36 object-cover" onerror="this.style.display='none'" />
                        </div>
                    @endif
                </div>

                <!-- Excerpt -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">Article Summary / Excerpt</label>
                        <button 
                            type="button" 
                            wire:click="generateBlogExcerpt" 
                            class="text-[11px] text-violet-400 hover:text-violet-300 font-semibold cursor-pointer transition-colors"
                        >
                            ✨ Auto-Generate from Content
                        </button>
                    </div>
                    <textarea 
                        wire:model="blogExcerpt" 
                        rows="3" 
                        placeholder="Brief 1-2 sentence teaser to hook readers in the blog feed..." 
                        class="w-full bg-slate-900 border border-white/10 focus:border-violet-500 rounded-xl p-3 text-xs text-slate-200 focus:outline-none transition-colors resize-none"
                    ></textarea>
                </div>

                <!-- Featured Spotlight Checkbox -->
                <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900/60 border border-white/5 cursor-pointer hover:bg-slate-900 transition-colors">
                    <input type="checkbox" wire:model="blogIsFeatured" class="rounded bg-slate-950 border-white/20 text-violet-600 focus:ring-violet-500/30">
                    <div class="text-xs">
                        <span class="font-bold text-white block">⭐ Featured Spotlight Article</span>
                        <span class="text-[11px] text-slate-400">Display this article prominently at the top of the blog journal feed.</span>
                    </div>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-white/10">
                <div>
                    @if($isPublishedToBlog)
                        <button 
                            type="button" 
                            wire:click="unpublishFromBlog" 
                            wire:confirm="Are you sure you want to unpublish this article from the public blog? It will be switched to draft."
                            class="px-3.5 py-2 rounded-xl bg-rose-950/60 hover:bg-rose-900 border border-rose-500/30 text-rose-300 hover:text-white text-xs font-semibold transition-all cursor-pointer"
                        >
                            Unpublish Post
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" wire:click="$set('showBlogModal', false)" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold cursor-pointer">
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="publishToBlog" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="publishToBlog">
                            {{ $isPublishedToBlog ? 'Update Published Post' : 'Publish Article Now' }}
                        </span>
                        <span wire:loading wire:target="publishToBlog">Publishing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Lossy Engine Switch Warning Modal -->
<div x-show="showLossyWarning" x-cloak class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true">
    <div x-show="showLossyWarning" class="w-full max-w-md mx-4 rounded-2xl glass-elevated border border-amber-500/30 shadow-2xl p-6 space-y-5">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-xl">⚠️</div>
            <div>
                <h2 class="text-base font-bold text-white">Lossy Engine Switch</h2>
                <p class="text-xs text-slate-400 mt-0.5">Switching may permanently strip rich-text formatting.</p>
            </div>
        </div>
        <div class="rounded-xl bg-amber-900/10 border border-amber-500/20 p-4 text-xs text-slate-300 space-y-2">
            <div class="flex items-center gap-2"><span class="text-amber-400">✦</span><span><strong class="text-white">Headings, bold, italic, lists</strong> become plain text.</span></div>
            <div class="flex items-center gap-2"><span class="text-amber-400">✦</span><span>Images and tables <strong class="text-white">will be removed</strong>.</span></div>
            <div class="flex items-center gap-2"><span class="text-amber-400">✦</span><span>Action <strong class="text-white">cannot be undone</strong> without a snapshot.</span></div>
        </div>
        <p class="text-xs text-slate-500">Tip: use <strong class="text-slate-300">Save Snapshot</strong> first to preserve formatting.</p>
        <div class="flex items-center justify-end gap-3 pt-1">
            <button type="button" x-on:click="cancelLossySwitch()" class="px-4 py-2 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-xs font-semibold transition-all">Cancel</button>
            <button type="button" x-on:click="confirmLossySwitch()" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-lg transition-all">Continue Anyway</button>
        </div>
    </div>
</div>

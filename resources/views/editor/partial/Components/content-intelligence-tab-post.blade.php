{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Post & WordPress Publishing Sidebar Tab
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

<!-- ─── TAB 1: WORDPRESS-STYLE POST SETTINGS & PUBLISHING (SECTION 1) ──── -->
<div 
    x-show="rightTab === 'post'" 
    class="space-y-4" 
    style="display: none;" 
    x-data="{ 
        newCategoryInput: '',
        newTagInput: '',
        showNewCategory: false,
        activeSection: 'summary',
        copiedUrl: false,
        toggleSection(sec) {
            this.activeSection = (this.activeSection === sec ? null : sec);
        }
    }"
>
    <!-- Flash Status Notification -->
    @if(session()->has('blog_status'))
        <div class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between gap-2 shadow-lg animate-fade-in">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span class="font-medium leading-tight">{{ session('blog_status') }}</span>
            </div>
            @if($blogPublishedUrl)
                <a href="{{ $blogPublishedUrl }}" target="_blank" class="px-2 py-0.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] shrink-0 transition-colors">
                    View ↗
                </a>
            @endif
        </div>
    @endif

    <!-- 1. SUMMARY / STATUS & VISIBILITY ACCORDION -->
    <div class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden">
        <button 
            type="button" 
            @click="toggleSection('summary')" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">📌</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Status & Visibility</span>
            </div>
            <div class="flex items-center gap-2">
                @if($isPublishedToBlog)
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[9px] font-bold uppercase font-mono">
                        ● Published
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[9px] font-bold uppercase font-mono">
                        Draft
                    </span>
                @endif
                <span class="text-slate-400 text-xs" x-text="activeSection === 'summary' ? '▾' : '▸'"></span>
            </div>
        </button>

        <div x-show="activeSection === 'summary'" x-collapse class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs text-slate-300">
            <!-- Visibility & Status Selection -->
            <div class="grid grid-cols-2 gap-2 pt-2">
                <div>
                    <label class="text-[10px] text-slate-400 font-mono block mb-1">Status</label>
                    <select 
                        wire:model="blogStatus" 
                        class="w-full bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                    >
                        <option value="published">Published</option>
                        <option value="draft">Draft (Private)</option>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] text-slate-400 font-mono block mb-1">Visibility</label>
                    <div class="w-full bg-slate-950/80 border border-white/10 rounded-xl px-2.5 py-1.5 text-xs text-slate-200 font-mono flex items-center justify-between">
                        <span>Public</span>
                        <span class="text-[10px] text-emerald-400">🌐</span>
                    </div>
                </div>
            </div>

            <!-- Author & Stats -->
            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-white/5 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center font-bold text-[10px] text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="text-white font-medium">{{ auth()->user()->name ?? 'Author' }}</span>
                </div>
                <div class="flex items-center gap-2 font-mono text-slate-400">
                    <span>{{ $wordCount }} words</span>
                    <span>&bull;</span>
                    <span>{{ $readingTimeMinutes }} min read</span>
                </div>
            </div>

            <!-- Sticky to top (Featured Spotlight) Toggle -->
            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60 border border-white/5 cursor-pointer hover:border-indigo-500/30 transition-all">
                <div class="flex items-center gap-2">
                    <span class="text-sm">⭐</span>
                    <div>
                        <div class="text-xs font-semibold text-white">Stick to top of Blog</div>
                        <div class="text-[10px] text-slate-400">Display as Featured Spotlight Hero</div>
                    </div>
                </div>
                <input 
                    type="checkbox" 
                    wire:model="blogIsFeatured" 
                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-white/20 cursor-pointer"
                />
            </label>

            <!-- URL Slug / Permalink -->
            <div class="space-y-1.5 pt-1">
                <label class="text-[10px] text-slate-400 font-mono block">Permalink / Slug</label>
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-slate-500 font-mono select-none">/blog/</span>
                    <input 
                        type="text" 
                        wire:model="blogSlug" 
                        placeholder="article-slug" 
                        class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                    />
                </div>
                @if($blogPublishedUrl)
                    <div class="flex items-center justify-between pt-1">
                        <button 
                            type="button" 
                            @click="navigator.clipboard.writeText('{{ $blogPublishedUrl }}'); copiedUrl = true; setTimeout(() => copiedUrl = false, 2000)" 
                            class="text-[10px] text-indigo-400 hover:text-indigo-300 cursor-pointer flex items-center gap-1 font-mono transition-colors"
                        >
                            <span x-show="!copiedUrl">📋 Copy Live URL</span>
                            <span x-show="copiedUrl" class="text-emerald-400">✓ Link Copied!</span>
                        </button>
                        <a href="{{ $blogPublishedUrl }}" target="_blank" class="text-[10px] text-violet-400 hover:underline flex items-center gap-0.5">
                            <span>Open Article</span>
                            <span>↗</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. FEATURED IMAGE ACCORDION ("Set featured image") -->
    <div class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden">
        <button 
            type="button" 
            @click="toggleSection('image')" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">🖼️</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Featured Image</span>
            </div>
            <div class="flex items-center gap-2">
                @if(!empty($blogFeaturedImage))
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                @else
                    <span class="text-[10px] text-slate-400 font-mono">None</span>
                @endif
                <span class="text-slate-400 text-xs" x-text="activeSection === 'image' ? '▾' : '▸'"></span>
            </div>
        </button>

        <div x-show="activeSection === 'image'" x-collapse class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            @if(!empty($blogFeaturedImage))
                <!-- Image Preview Box -->
                <div class="space-y-2 pt-2">
                    <div class="aspect-video rounded-xl overflow-hidden border border-white/10 relative group bg-slate-950 shadow-md">
                        <img 
                            src="{{ $blogFeaturedImage }}" 
                            alt="Featured image preview" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            onerror="this.src='https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&auto=format&fit=crop&q=80'"
                        />
                        <button 
                            type="button" 
                            wire:click="removeFeaturedImage" 
                            class="absolute top-2 right-2 p-1.5 rounded-lg bg-red-600/80 hover:bg-red-600 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg cursor-pointer"
                            title="Remove featured image"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                        <button 
                            type="button" 
                            wire:click="removeFeaturedImage" 
                            class="text-red-400 hover:text-red-300 cursor-pointer transition-colors"
                        >
                            Remove featured image
                        </button>
                    </div>
                </div>
            @else
                <!-- Set Featured Image Dropzone Box -->
                <div class="pt-2">
                    <div class="p-5 border-2 border-dashed border-white/15 hover:border-indigo-500/50 rounded-2xl flex flex-col items-center justify-center text-center gap-2 bg-slate-950/40 transition-colors">
                        <span class="text-2xl">🖼️</span>
                        <div class="text-xs font-bold text-white">Set featured image</div>
                        <p class="text-[10px] text-slate-400 max-w-[200px] leading-tight">
                            Paste an image URL below to display on article cards and social previews.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Image URL Input -->
            <div class="space-y-1">
                <label class="text-[10px] text-slate-400 font-mono block">Image URL</label>
                <input 
                    type="url" 
                    wire:model.live.debounce.300ms="blogFeaturedImage" 
                    placeholder="https://images.unsplash.com/photo-..." 
                    class="w-full bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                />
            </div>

            <!-- Quick Preset Covers -->
            <div class="space-y-1.5 pt-1">
                <span class="text-[10px] text-slate-400 font-mono block">Quick Presets:</span>
                <div class="grid grid-cols-3 gap-1.5">
                    <button 
                        type="button" 
                        wire:click="$set('blogFeaturedImage', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&auto=format&fit=crop&q=80')" 
                        class="p-1 rounded-lg border border-white/10 hover:border-indigo-400/50 text-[10px] text-slate-300 hover:text-white bg-slate-950 transition-colors truncate"
                    >
                        🔮 Gradient
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('blogFeaturedImage', 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=1200&auto=format&fit=crop&q=80')" 
                        class="p-1 rounded-lg border border-white/10 hover:border-indigo-400/50 text-[10px] text-slate-300 hover:text-white bg-slate-950 transition-colors truncate"
                    >
                        🤖 AI Circuit
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('blogFeaturedImage', 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=1200&auto=format&fit=crop&q=80')" 
                        class="p-1 rounded-lg border border-white/10 hover:border-indigo-400/50 text-[10px] text-slate-300 hover:text-white bg-slate-950 transition-colors truncate"
                    >
                        📈 Growth
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. CATEGORIES ACCORDION (WordPress-style Category Checklist) -->
    <div class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden">
        <button 
            type="button" 
            @click="toggleSection('categories')" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">📁</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Categories</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-[10px] font-mono font-bold truncate max-w-[120px]">
                    {{ $blogCategory ?: 'Select' }}
                </span>
                <span class="text-slate-400 text-xs" x-text="activeSection === 'categories' ? '▾' : '▸'"></span>
            </div>
        </button>

        <div x-show="activeSection === 'categories'" x-collapse class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <div class="space-y-1.5 pt-2 max-h-48 overflow-y-auto custom-scrollbar pr-1">
                @php
                    $availableCats = $blogCategories ?? \App\Features\Blog\Models\BlogPost::defaultCategories();
                @endphp
                @foreach($availableCats as $cat)
                    <label 
                        wire:click="setBlogCategory('{{ $cat }}')"
                        class="flex items-center justify-between p-2 rounded-xl transition-all cursor-pointer select-none {{ $blogCategory === $cat ? 'bg-indigo-600/20 border border-indigo-500/40 text-white font-semibold' : 'hover:bg-white/5 text-slate-300 border border-transparent' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <input 
                                type="radio" 
                                name="blogCategoryRadio" 
                                value="{{ $cat }}" 
                                wire:model="blogCategory" 
                                class="text-indigo-600 focus:ring-indigo-500 bg-slate-950 border-white/20 cursor-pointer"
                            />
                            <span>{{ $cat }}</span>
                        </div>
                        @if($blogCategory === $cat)
                            <span class="text-indigo-400 text-xs font-bold">✓ Primary</span>
                        @endif
                    </label>
                @endforeach
            </div>

            <!-- Add New Category Toggle & Input -->
            <div class="pt-2 border-t border-white/5">
                <div x-show="!showNewCategory">
                    <button 
                        type="button" 
                        @click="showNewCategory = true" 
                        class="text-[11px] text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1 cursor-pointer transition-colors"
                    >
                        <span>+ Add New Category</span>
                    </button>
                </div>
                <div x-show="showNewCategory" class="space-y-2" style="display: none;">
                    <div class="flex items-center gap-1.5">
                        <input 
                            type="text" 
                            x-model="newCategoryInput" 
                            @keydown.enter.prevent="if (newCategoryInput.trim()) { $wire.setBlogCategory(newCategoryInput.trim()); newCategoryInput = ''; showNewCategory = false; }"
                            placeholder="New category name..." 
                            class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                        />
                        <button 
                            type="button" 
                            @click="if (newCategoryInput.trim()) { $wire.setBlogCategory(newCategoryInput.trim()); newCategoryInput = ''; showNewCategory = false; }"
                            class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer shadow-md transition-all"
                        >
                            Add
                        </button>
                    </div>
                    <button 
                        type="button" 
                        @click="showNewCategory = false" 
                        class="text-[10px] text-slate-400 hover:text-slate-300 cursor-pointer"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. TAGS ACCORDION (WordPress-style Tag Chips) -->
    <div class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden">
        <button 
            type="button" 
            @click="toggleSection('tags')" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">🏷️</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Tags</span>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $parsedTags = array_filter(array_map('trim', explode(',', $blogTags ?? '')));
                @endphp
                <span class="text-[10px] text-slate-400 font-mono">
                    {{ count($parsedTags) }} {{ \Illuminate\Support\Str::plural('tag', count($parsedTags)) }}
                </span>
                <span class="text-slate-400 text-xs" x-text="activeSection === 'tags' ? '▾' : '▸'"></span>
            </div>
        </button>

        <div x-show="activeSection === 'tags'" x-collapse class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <!-- Add Tag Input -->
            <div class="flex items-center gap-1.5 pt-2">
                <input 
                    type="text" 
                    x-model="newTagInput" 
                    @keydown.enter.prevent="if (newTagInput.trim()) { $wire.addBlogTag(newTagInput.trim()); newTagInput = ''; }"
                    placeholder="Add new tag (press Enter)..." 
                    class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                />
                <button 
                    type="button" 
                    @click="if (newTagInput.trim()) { $wire.addBlogTag(newTagInput.trim()); newTagInput = ''; }"
                    class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer shadow-md transition-all"
                >
                    + Add
                </button>
            </div>

            <!-- Active Tags List Chips -->
            @if(count($parsedTags) > 0)
                <div class="flex items-center flex-wrap gap-1.5 pt-1">
                    @foreach($parsedTags as $tag)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-950 border border-white/15 text-[11px] text-slate-200 shadow-sm group">
                            <span>#{{ $tag }}</span>
                            <button 
                                type="button" 
                                wire:click="removeBlogTag('{{ $tag }}')" 
                                class="text-slate-400 hover:text-red-400 font-bold cursor-pointer text-xs transition-colors"
                                title="Remove tag"
                            >
                                &times;
                            </button>
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-[10px] text-slate-500 italic pt-1">No tags added yet. Tags help visitors discover related articles.</p>
            @endif

            <!-- Suggested / Popular Tags -->
            <div class="space-y-1.5 pt-2 border-t border-white/5">
                <span class="text-[10px] text-slate-400 font-mono block">Suggested Tags:</span>
                <div class="flex items-center flex-wrap gap-1">
                    @foreach(['AI Writing', 'SEO Strategy', 'TipTap', 'Gutenberg', 'Automation', 'Tutorial', 'DeepSeek'] as $popularTag)
                        <button 
                            type="button" 
                            wire:click="addBlogTag('{{ $popularTag }}')" 
                            class="px-2 py-0.5 rounded-md bg-slate-950/80 hover:bg-indigo-600/30 border border-white/10 hover:border-indigo-500/40 text-[10px] text-slate-300 hover:text-indigo-200 transition-all cursor-pointer"
                        >
                            + {{ $popularTag }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 5. EXCERPT ACCORDION (WordPress-style Excerpt) -->
    <div class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden">
        <button 
            type="button" 
            @click="toggleSection('excerpt')" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">📄</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Excerpt</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-slate-400 font-mono">
                    {{ strlen($blogExcerpt ?? '') }} chars
                </span>
                <span class="text-slate-400 text-xs" x-text="activeSection === 'excerpt' ? '▾' : '▸'"></span>
            </div>
        </button>

        <div x-show="activeSection === 'excerpt'" x-collapse class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <div class="space-y-1.5 pt-2">
                <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                    <span>Summary description</span>
                    <button 
                        type="button" 
                        wire:click="generateBlogExcerpt" 
                        class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer flex items-center gap-1 transition-colors"
                    >
                        <span>✨ AI Generate</span>
                    </button>
                </div>
                <textarea 
                    wire:model="blogExcerpt" 
                    rows="3" 
                    placeholder="Write an excerpt (optional summary for search and article cards)..." 
                    class="w-full bg-slate-950 border border-white/15 rounded-xl p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 shadow-inner resize-none custom-scrollbar"
                ></textarea>
                <p class="text-[10px] text-slate-500 leading-tight">
                    Displays on the blog directory cards, RSS feeds, and social graph cards.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. PRIMARY PUBLISH / UPDATE ACTIONS -->
    <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner space-y-2.5">
        <button 
            type="button" 
            wire:click="publishToBlog" 
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer"
        >
            <span wire:loading.remove wire:target="publishToBlog">
                @if($isPublishedToBlog)
                    🔄 Update Published Post
                @else
                    🚀 Publish to Blog
                @endif
            </span>
            <span wire:loading wire:target="publishToBlog" class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                <span>Publishing article...</span>
            </span>
        </button>

        @if($isPublishedToBlog)
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    wire:click="unpublishFromBlog" 
                    class="flex-1 py-1.5 px-3 rounded-xl bg-slate-950 hover:bg-red-500/10 border border-white/10 hover:border-red-500/30 text-slate-400 hover:text-red-300 text-xs font-semibold transition-colors cursor-pointer text-center"
                >
                    Switch to Draft
                </button>
                @if($blogPublishedUrl)
                    <a 
                        href="{{ $blogPublishedUrl }}" 
                        target="_blank" 
                        class="flex-1 py-1.5 px-3 rounded-xl bg-slate-950 hover:bg-white/10 border border-white/10 text-indigo-300 hover:text-white text-xs font-semibold transition-colors text-center"
                    >
                        View Post ↗
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

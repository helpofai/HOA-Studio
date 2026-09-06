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
        currentStatus: $wire.entangle('blogStatus'),
        currentCategory: $wire.entangle('blogCategory'),
        currentImage: $wire.entangle('blogFeaturedImage'),
        currentExcerpt: $wire.entangle('blogExcerpt'),
        newCategoryInput: '',
        showNewCategory: false,
        copiedUrl: false
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
    <div 
        wire:key="post-section-summary"
        x-data="{ isOpen: true }"
        class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden transition-colors"
    >
        <button 
            type="button" 
            @click="isOpen = !isOpen" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">📌</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Status & Visibility</span>
            </div>
            <div class="flex items-center gap-2">
                <span x-show="currentStatus === 'published'" class="px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[9px] font-bold uppercase font-mono">
                    ● Published
                </span>
                <span x-show="currentStatus !== 'published'" class="px-2 py-0.5 rounded-full bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[9px] font-bold uppercase font-mono">
                    Draft
                </span>
                <span class="text-slate-400 text-xs transition-transform duration-200" :class="isOpen ? 'rotate-90 inline-block' : 'inline-block'">▸</span>
            </div>
        </button>

        <div x-show="isOpen" x-cloak class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs text-slate-300">
            <!-- Visibility & Status Selection -->
            <div class="grid grid-cols-2 gap-2 pt-2">
                <div>
                    <label class="text-[10px] text-slate-400 font-mono block mb-1">Status</label>
                    <select 
                        x-model="currentStatus"
                        @change="$wire.blogStatus = currentStatus"
                        class="w-full bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono cursor-pointer"
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
                    <span x-text="wordCount + ' words'">{{ $wordCount }} words</span>
                    <span>&bull;</span>
                    <span x-text="(readingTime || Math.max(1, Math.ceil(wordCount / 200))) + ' min read'">{{ $readingTimeMinutes }} min read</span>
                </div>
            </div>

            <!-- Sticky to top (Featured Spotlight) Toggle -->
            <label class="flex items-center justify-between p-2.5 rounded-xl bg-slate-950/60 border border-white/5 cursor-pointer hover:border-indigo-500/30 transition-all select-none">
                <div class="flex items-center gap-2">
                    <span class="text-sm">⭐</span>
                    <div>
                        <div class="text-xs font-semibold text-white">Stick to top of Blog</div>
                        <div class="text-[10px] text-slate-400">Display as Featured Spotlight Hero</div>
                    </div>
                </div>
                <input 
                    type="checkbox" 
                    wire:model.live="blogIsFeatured" 
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
                        wire:model.blur="blogSlug" 
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
    <div 
        wire:key="post-section-image"
        x-data="{ isOpen: {{ !empty($blogFeaturedImage) ? 'true' : 'false' }}, isDropping: false }"
        class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden transition-colors"
    >
        <button 
            type="button" 
            @click="isOpen = !isOpen" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">🖼️</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Featured Image</span>
            </div>
            <div class="flex items-center gap-2">
                <span x-show="currentImage" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span x-show="!currentImage" class="text-[10px] text-slate-400 font-mono">None</span>
                <span class="text-slate-400 text-xs transition-transform duration-200" :class="isOpen ? 'rotate-90 inline-block' : 'inline-block'">▸</span>
            </div>
        </button>

        <div x-show="isOpen" x-cloak class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <!-- Unified Drag-and-Drop Dropzone & Preview Container with Progress Bar -->
            <div 
                class="pt-2"
                x-data="{ 
                    isDropping: false, 
                    isUploading: false, 
                    progress: 0 
                }"
                x-on:livewire-upload-start="isUploading = true; progress = 0"
                x-on:livewire-upload-finish="isUploading = false"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
                <div 
                    class="relative rounded-2xl border-2 border-dashed border-white/20 hover:border-indigo-500/50 bg-slate-950/50 hover:bg-slate-950/80 transition-all text-center group flex flex-col items-center justify-center cursor-pointer overflow-hidden shadow-inner aspect-video"
                    x-on:dragover.prevent="isDropping = true"
                    x-on:dragleave.prevent="isDropping = false"
                    x-on:drop="isDropping = false"
                    :class="{ 'border-indigo-500 bg-indigo-950/30 ring-2 ring-indigo-500/20': isDropping }"
                >
                    <!-- Transparent File Input covering entire dropzone -->
                    <input 
                        type="file" 
                        wire:model="featuredImageUpload" 
                        accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,image/svg+xml,image/avif,image/bmp,image/x-icon,image/tiff"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                        :class="{ 'pointer-events-none': isUploading }"
                        title="Click or drop image to upload or replace"
                    />

                    <!-- 1. ACTIVE PROGRESS BAR OVERLAY -->
                    <div 
                        x-show="isUploading" 
                        x-cloak 
                        class="absolute inset-0 z-30 bg-slate-950/95 backdrop-blur-md flex flex-col items-center justify-center gap-2.5 p-4"
                    >
                        <div class="flex items-center justify-between w-full max-w-[220px] text-[11px]">
                            <span class="text-white font-semibold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                                Uploading Image...
                            </span>
                            <span class="text-indigo-400 font-mono font-bold" x-text="`${progress}%`"></span>
                        </div>
                        
                        <!-- Smooth Visual Progress Bar -->
                        <div class="w-full max-w-[220px] bg-slate-800/80 rounded-full h-2.5 p-0.5 border border-white/10 overflow-hidden shadow-inner">
                            <div 
                                class="bg-gradient-to-r from-indigo-500 via-violet-500 to-pink-500 h-full rounded-full transition-all duration-150 shadow-sm"
                                :style="`width: ${Math.max(progress, 5)}%`"
                            ></div>
                        </div>

                        <span class="text-[10px] text-slate-400 font-mono" x-text="progress < 100 ? `${progress}% completed` : 'Optimizing and saving...'"></span>
                    </div>

                    <!-- 2. SERVER PROCESSING INDICATOR (Livewire final storage step) -->
                    <div 
                        wire:loading 
                        wire:target="featuredImageUpload" 
                        x-show="!isUploading" 
                        class="absolute inset-0 z-30 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center gap-2"
                    >
                        <div class="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <div class="text-xs font-bold text-white">Saving Featured Image...</div>
                        <div class="text-[10px] text-slate-400 font-mono">Updating database records...</div>
                    </div>

                    <!-- 3. PREVIEW DISPLAY (Shown after upload is complete) -->
                    <div x-show="currentImage && !isUploading" class="absolute inset-0 w-full h-full z-10 overflow-hidden group/imgpreview">
                        <img 
                            :src="currentImage" 
                            alt="Featured image preview" 
                            class="w-full h-full object-cover group-hover/imgpreview:scale-105 transition-transform duration-300"
                            x-on:error="$el.src='https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&auto=format&fit=crop&q=80'"
                        />
                        <!-- Hover replacement overlay -->
                        <div class="absolute inset-0 bg-slate-950/65 opacity-0 group-hover/imgpreview:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 p-3 pointer-events-none">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-600/90 text-white text-[11px] font-bold shadow-lg flex items-center gap-1">
                                <span>🔄</span>
                                <span>Click or Drop to Replace</span>
                            </span>
                            <span class="text-[9px] text-slate-300 font-mono">PNG, JPG, WEBP, GIF, SVG, AVIF</span>
                        </div>
                    </div>

                    <!-- 4. EMPTY PLACEHOLDER (Shown when no image and not uploading) -->
                    <div x-show="!currentImage && !isUploading" class="flex flex-col items-center justify-center gap-2 p-4 pointer-events-none">
                        <!-- Visual Icon -->
                        <div class="w-11 h-11 rounded-xl bg-indigo-600/15 border border-indigo-500/30 flex items-center justify-center text-xl text-indigo-400 group-hover:scale-110 group-hover:border-indigo-400/50 transition-all shadow-sm">
                            🖼️
                        </div>

                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors">
                                Drop your image here, or <span class="text-indigo-400 underline decoration-indigo-400/50 underline-offset-2">browse</span>
                            </div>
                            <p class="text-[10px] text-slate-400">
                                Upload PNG, JPG, WebP, GIF, SVG, AVIF, BMP (Max 15MB)
                            </p>
                        </div>

                        <!-- Multi-Format Badges -->
                        <div class="flex flex-wrap items-center justify-center gap-1 pt-0.5 pointer-events-none">
                            <span class="px-1.5 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300 text-[9px] font-mono font-semibold">PNG</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300 text-[9px] font-mono font-semibold">JPG</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300 text-[9px] font-mono font-semibold">WEBP</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300 text-[9px] font-mono font-semibold">GIF</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300 text-[9px] font-mono font-semibold">SVG</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white/5 border border-white/10 text-slate-300 text-[9px] font-mono font-semibold">AVIF</span>
                        </div>
                    </div>
                </div>

                <!-- Preview Actions (Under the Box) -->
                <div x-show="currentImage" class="flex items-center justify-between text-[11px] text-slate-400 pt-1.5 px-0.5">
                    <button 
                        type="button" 
                        @click="currentImage = ''; $wire.removeFeaturedImage()" 
                        class="text-red-400 hover:text-red-300 cursor-pointer transition-colors flex items-center gap-1 font-medium"
                    >
                        <span>🗑️ Remove featured image</span>
                    </button>
                    <span class="text-[10px] text-emerald-400 flex items-center gap-1 font-mono font-medium">
                        <span>✓ Active</span>
                    </span>
                </div>
            </div>

            <!-- Upload Error Alert -->
            @error('featuredImageUpload')
                <div class="p-2.5 rounded-xl bg-red-500/15 border border-red-500/30 text-red-300 text-[11px] flex items-center gap-1.5 shadow-sm">
                    <span class="text-sm">⚠️</span>
                    <span class="leading-tight">{{ $message }}</span>
                </div>
            @enderror

            <!-- Divider: Alternative Options -->
            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-white/10"></div>
                <span class="flex-shrink mx-2 text-[10px] text-slate-500 uppercase tracking-widest font-mono">Or paste URL</span>
                <div class="flex-grow border-t border-white/10"></div>
            </div>

            <!-- Image URL Input -->
            <div class="space-y-1">
                <label class="text-[10px] text-slate-400 font-mono block">Image URL</label>
                <input 
                    type="url" 
                    x-model="currentImage"
                    @input.debounce.300ms="$wire.set('blogFeaturedImage', currentImage)"
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
                        @click="currentImage = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&auto=format&fit=crop&q=80'; $wire.set('blogFeaturedImage', currentImage)" 
                        class="p-1 rounded-lg border border-white/10 hover:border-indigo-400/50 text-[10px] text-slate-300 hover:text-white bg-slate-950 transition-colors truncate cursor-pointer"
                    >
                        🔮 Gradient
                    </button>
                    <button 
                        type="button" 
                        @click="currentImage = 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=1200&auto=format&fit=crop&q=80'; $wire.set('blogFeaturedImage', currentImage)" 
                        class="p-1 rounded-lg border border-white/10 hover:border-indigo-400/50 text-[10px] text-slate-300 hover:text-white bg-slate-950 transition-colors truncate cursor-pointer"
                    >
                        🤖 AI Circuit
                    </button>
                    <button 
                        type="button" 
                        @click="currentImage = 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=1200&auto=format&fit=crop&q=80'; $wire.set('blogFeaturedImage', currentImage)" 
                        class="p-1 rounded-lg border border-white/10 hover:border-indigo-400/50 text-[10px] text-slate-300 hover:text-white bg-slate-950 transition-colors truncate cursor-pointer"
                    >
                        📈 Growth
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. CATEGORIES ACCORDION (WordPress-style Category Checklist) -->
    <div 
        wire:key="post-section-categories"
        x-data="{ isOpen: true }"
        class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden transition-colors"
    >
        <button 
            type="button" 
            @click="isOpen = !isOpen" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">📁</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Categories</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-[10px] font-mono font-bold truncate max-w-[120px]" x-text="currentCategory || 'Select'">
                    {{ $blogCategory ?: 'Select' }}
                </span>
                <span class="text-slate-400 text-xs transition-transform duration-200" :class="isOpen ? 'rotate-90 inline-block' : 'inline-block'">▸</span>
            </div>
        </button>

        <div x-show="isOpen" x-cloak class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <div class="space-y-1.5 pt-2 max-h-48 overflow-y-auto custom-scrollbar pr-1">
                @php
                    $availableCats = !empty($blogCategories) ? $blogCategories : \App\Features\Blog\Models\BlogPost::defaultCategories();
                @endphp
                @foreach($availableCats as $cat)
                    <label 
                        wire:key="post-cat-{{ \Illuminate\Support\Str::slug($cat) }}"
                        @click="currentCategory = '{{ addslashes($cat) }}'; $wire.setBlogCategory('{{ addslashes($cat) }}')"
                        :class="currentCategory === '{{ addslashes($cat) }}' ? 'bg-indigo-600/20 border border-indigo-500/40 text-white font-semibold' : 'hover:bg-white/5 text-slate-300 border border-transparent'"
                        class="flex items-center justify-between p-2 rounded-xl transition-all cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2.5">
                            <input 
                                type="radio" 
                                name="blogCategoryRadio" 
                                value="{{ $cat }}" 
                                :checked="currentCategory === '{{ addslashes($cat) }}'"
                                class="text-indigo-600 focus:ring-indigo-500 bg-slate-950 border-white/20 cursor-pointer pointer-events-none"
                            />
                            <span>{{ $cat }}</span>
                        </div>
                        <span x-show="currentCategory === '{{ addslashes($cat) }}'" class="text-indigo-400 text-xs font-bold">✓ Primary</span>
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
                            @keydown.enter.prevent="if (newCategoryInput.trim()) { currentCategory = newCategoryInput.trim(); $wire.setBlogCategory(newCategoryInput.trim()); newCategoryInput = ''; showNewCategory = false; }"
                            placeholder="New category name..." 
                            class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                        />
                        <button 
                            type="button" 
                            @click="if (newCategoryInput.trim()) { currentCategory = newCategoryInput.trim(); $wire.setBlogCategory(newCategoryInput.trim()); newCategoryInput = ''; showNewCategory = false; }"
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
    <div 
        wire:key="post-section-tags"
        x-data="{ 
            isOpen: true,
            newTagInput: '',
            tagsList: @js(array_values(array_filter(array_map('trim', explode(',', $blogTags ?? ''))))),
            init() {
                this.$watch('$wire.blogTags', (val) => {
                    if (typeof val === 'string') {
                        this.tagsList = val.split(',').map(s => s.trim()).filter(Boolean);
                    }
                });
            },
            addTag(tag) {
                tag = tag.trim();
                if (!tag) return;
                if (!this.tagsList.some(t => t.toLowerCase() === tag.toLowerCase())) {
                    this.tagsList.push(tag);
                    this.sync();
                }
                this.newTagInput = '';
            },
            removeTag(tag) {
                this.tagsList = this.tagsList.filter(t => t.toLowerCase() !== tag.toLowerCase());
                this.sync();
            },
            hasTag(tag) {
                return this.tagsList.some(t => t.toLowerCase() === tag.toLowerCase());
            },
            sync() {
                $wire.blogTags = this.tagsList.join(', ');
            }
        }"
        class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden transition-colors"
    >
        <button 
            type="button" 
            @click="isOpen = !isOpen" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">🏷️</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Tags</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-slate-400 font-mono" x-text="tagsList.length + ' ' + (tagsList.length === 1 ? 'tag' : 'tags')">
                    {{ count(array_filter(array_map('trim', explode(',', $blogTags ?? '')))) }} tags
                </span>
                <span class="text-slate-400 text-xs transition-transform duration-200" :class="isOpen ? 'rotate-90 inline-block' : 'inline-block'">▸</span>
            </div>
        </button>

        <div x-show="isOpen" x-cloak class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <!-- Add Tag Input -->
            <div class="flex items-center gap-1.5 pt-2">
                <input 
                    type="text" 
                    x-model="newTagInput" 
                    @keydown.enter.prevent="addTag(newTagInput)"
                    placeholder="Add new tag (press Enter)..." 
                    class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono shadow-inner"
                />
                <button 
                    type="button" 
                    @click="addTag(newTagInput)"
                    class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer shadow-md transition-all"
                >
                    + Add
                </button>
            </div>

            <!-- Active Tags List Chips (0ms instant Alpine reactivity) -->
            <div x-show="tagsList.length > 0" class="flex items-center flex-wrap gap-1.5 pt-1">
                <template x-for="tag in tagsList" :key="tag">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-950 border border-white/15 text-[11px] text-slate-200 shadow-sm group">
                        <span x-text="'#' + tag"></span>
                        <button 
                            type="button" 
                            @click="removeTag(tag)" 
                            class="text-slate-400 hover:text-red-400 font-bold cursor-pointer text-xs transition-colors"
                            title="Remove tag"
                        >
                            &times;
                        </button>
                    </span>
                </template>
            </div>
            <p x-show="tagsList.length === 0" class="text-[10px] text-slate-500 italic pt-1">No tags added yet. Tags help visitors discover related articles.</p>

            <!-- Suggested / Popular Tags (0ms instant toggle & highlight) -->
            <div class="space-y-1.5 pt-2 border-t border-white/5">
                <span class="text-[10px] text-slate-400 font-mono block">Suggested Tags:</span>
                <div class="flex items-center flex-wrap gap-1">
                    @foreach(['AI Writing', 'SEO Strategy', 'TipTap', 'Gutenberg', 'Automation', 'Tutorial', 'DeepSeek'] as $popularTag)
                        <button 
                            type="button" 
                            wire:key="post-popular-tag-{{ \Illuminate\Support\Str::slug($popularTag) }}"
                            @click="addTag('{{ addslashes($popularTag) }}')" 
                            :class="hasTag('{{ addslashes($popularTag) }}') ? 'bg-indigo-600/25 border-indigo-500/50 text-indigo-300 opacity-70 cursor-default' : 'bg-slate-950/80 hover:bg-indigo-600/30 border-white/10 hover:border-indigo-500/40 text-slate-300 hover:text-white cursor-pointer'"
                            class="px-2 py-0.5 rounded-md border text-[10px] transition-all flex items-center gap-1 select-none"
                        >
                            <span x-text="hasTag('{{ addslashes($popularTag) }}') ? '✓' : '+'"></span>
                            <span>{{ $popularTag }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 5. EXCERPT ACCORDION (WordPress-style Excerpt) -->
    <div 
        wire:key="post-section-excerpt"
        x-data="{ isOpen: {{ !empty($blogExcerpt) ? 'true' : 'false' }} }"
        class="rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner overflow-hidden transition-colors"
    >
        <button 
            type="button" 
            @click="isOpen = !isOpen" 
            class="w-full p-3.5 flex items-center justify-between text-left hover:bg-white/5 transition-colors cursor-pointer select-none"
        >
            <div class="flex items-center gap-2">
                <span class="text-sm">📄</span>
                <span class="text-xs font-bold text-white uppercase tracking-wider">Excerpt</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-slate-400 font-mono" x-text="(currentExcerpt ? currentExcerpt.length : 0) + ' chars'">
                    {{ strlen($blogExcerpt ?? '') }} chars
                </span>
                <span class="text-slate-400 text-xs transition-transform duration-200" :class="isOpen ? 'rotate-90 inline-block' : 'inline-block'">▸</span>
            </div>
        </button>

        <div x-show="isOpen" x-cloak class="p-3.5 pt-0 space-y-3 border-t border-white/5 text-xs">
            <div class="space-y-1.5 pt-2">
                <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                    <span>Summary description</span>
                    <button 
                        type="button" 
                        wire:click="generateBlogExcerpt" 
                        wire:loading.attr="disabled"
                        wire:target="generateBlogExcerpt"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer flex items-center gap-1 transition-colors disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="generateBlogExcerpt">✨ AI Generate</span>
                        <span wire:loading wire:target="generateBlogExcerpt" class="inline-block animate-pulse text-[9px]">Generating...</span>
                    </button>
                </div>
                <textarea 
                    x-model="currentExcerpt"
                    wire:model.blur="blogExcerpt" 
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
    <div wire:key="post-section-actions" class="p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner space-y-2.5">
        <button 
            type="button" 
            wire:click="publishToBlog" 
            wire:loading.attr="disabled"
            wire:target="publishToBlog"
            class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50"
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
                    wire:loading.attr="disabled"
                    wire:target="unpublishFromBlog"
                    class="flex-1 py-1.5 px-3 rounded-xl bg-slate-950 hover:bg-red-500/10 border border-white/10 hover:border-red-500/30 text-slate-400 hover:text-red-300 text-xs font-semibold transition-colors cursor-pointer text-center disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="unpublishFromBlog">Switch to Draft</span>
                    <span wire:loading wire:target="unpublishFromBlog">Switching...</span>
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

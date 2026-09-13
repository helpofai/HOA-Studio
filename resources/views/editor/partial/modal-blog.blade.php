<!-- Blog Publishing Modal -->
<div x-show="showBlogModalLocal || $wire.showBlogModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md">
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
            <button type="button" @click="closeBlogModalInstant()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/5 transition-all cursor-pointer">✕</button>
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

                <!-- Featured Image Upload & Cover -->
                <div
                    x-data="{
                        isUploadingModal: false,
                        progressModal: 0,
                        isDroppingModal: false
                    }"
                    x-on:livewire-upload-start="isUploadingModal = true; progressModal = 0"
                    x-on:livewire-upload-finish="isUploadingModal = false"
                    x-on:livewire-upload-error="isUploadingModal = false"
                    x-on:livewire-upload-progress="progressModal = $event.detail.progress"
                    class="space-y-2"
                >
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-300">Featured Cover Image</label>
                        @if(!empty($blogFeaturedImage))
                            <button
                                type="button"
                                wire:click="removeFeaturedImage"
                                class="text-[11px] text-rose-400 hover:text-rose-300 font-medium transition-colors cursor-pointer flex items-center gap-1"
                            >
                                <span>🗑️ Remove</span>
                            </button>
                        @endif
                    </div>

                    <!-- Upload & Preview Dropzone Container -->
                    <div
                        class="relative rounded-2xl border-2 border-dashed border-white/20 hover:border-violet-500/50 bg-slate-900/60 hover:bg-slate-900/90 transition-all text-center flex flex-col items-center justify-center cursor-pointer overflow-hidden shadow-inner h-36 group"
                        x-on:dragover.prevent="isDroppingModal = true"
                        x-on:dragleave.prevent="isDroppingModal = false"
                        x-on:drop="isDroppingModal = false"
                        :class="{ 'border-violet-500 bg-violet-950/30': isDroppingModal }"
                    >
                        <input
                            type="file"
                            wire:model="featuredImageUpload"
                            accept="image/png,image/jpeg,image/jpg,image/webp,image/gif,image/svg+xml,image/avif,image/bmp,image/x-icon,image/tiff"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                            :class="{ 'pointer-events-none': isUploadingModal }"
                            title="Click or drop image to upload"
                        />

                        <!-- Progress Bar Overlay (Modal) -->
                        <div
                            x-show="isUploadingModal"
                            x-cloak
                            class="absolute inset-0 z-30 bg-slate-950/95 backdrop-blur-sm flex flex-col items-center justify-center gap-2 px-6"
                        >
                            <div class="flex items-center justify-between w-full max-w-[240px] text-[11px]">
                                <span class="text-white font-semibold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-violet-400 animate-ping"></span>
                                    Uploading Cover...
                                </span>
                                <span class="text-violet-400 font-mono font-bold" x-text="`${progressModal}%`"></span>
                            </div>
                            <div class="w-full max-w-[240px] bg-slate-800 rounded-full h-2 overflow-hidden border border-white/10 shadow-inner">
                                <div
                                    class="bg-gradient-to-r from-violet-500 via-indigo-500 to-pink-500 h-full rounded-full transition-all duration-150"
                                    :style="`width: ${Math.max(progressModal, 5)}%`"
                                ></div>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono" x-text="progressModal < 100 ? `${progressModal}% completed` : 'Saving featured image...'"></span>
                        </div>

                        <!-- Processing State Indicator -->
                        <div
                            wire:loading
                            wire:target="featuredImageUpload"
                            x-show="!isUploadingModal"
                            class="absolute inset-0 z-30 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center gap-2"
                        >
                            <div class="w-6 h-6 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs text-white font-medium">Processing & saving image...</span>
                        </div>

                        <!-- Image Preview (Shown after upload is complete) -->
                        @if(!empty($blogFeaturedImage))
                            <div class="absolute inset-0 w-full h-full z-10 overflow-hidden">
                                <img src="{{ $blogFeaturedImage }}" alt="Cover preview" class="w-full h-36 object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.style.display='none'" />
                                <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 pointer-events-none">
                                    <span class="px-2.5 py-1 rounded-lg bg-violet-600/90 text-white text-[11px] font-bold shadow-lg flex items-center gap-1">
                                        <span>🔄</span>
                                        <span>Click or Drop to Replace</span>
                                    </span>
                                </div>
                            </div>
                        @else
                            <!-- Placeholder -->
                            <div class="flex flex-col items-center justify-center gap-1 text-center py-2 pointer-events-none">
                                <span class="text-xl">🖼️</span>
                                <div class="text-xs font-semibold text-white">
                                    Drop cover here, or <span class="text-violet-400 underline decoration-violet-400/50">browse</span>
                                </div>
                                <p class="text-[10px] text-slate-400">
                                    PNG, JPG, WebP, GIF, SVG, AVIF (Max 15MB)
                                </p>
                            </div>
                        @endif
                    </div>

                    @error('featuredImageUpload')
                        <div class="p-2 rounded-xl bg-rose-500/10 text-rose-400 text-xs flex items-center gap-1.5">
                            <span>⚠️</span>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <!-- Image URL Input -->
                    <div class="space-y-1 pt-1">
                        <label class="text-[10px] text-slate-400 font-mono block">Featured Cover Image URL (Optional)</label>
                        <input
                            type="url"
                            wire:model.live.debounce.400ms="blogFeaturedImage"
                            placeholder="https://images.unsplash.com/photo-..."
                            class="w-full bg-slate-900 border border-white/10 focus:border-violet-500 rounded-xl px-3 py-2 text-xs text-white focus:outline-none transition-colors font-mono"
                        />
                    </div>
                </div>

                <!-- Excerpt -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-slate-300">Article Summary / Excerpt</label>
                        <button
                            type="button"
                            wire:click="generateBlogExcerpt"
                            wire:loading.attr="disabled"
                            wire:target="generateBlogExcerpt"
                            class="text-[11px] text-violet-400 hover:text-violet-300 font-semibold cursor-pointer transition-colors disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="generateBlogExcerpt">✨ Auto-Generate from Content</span>
                            <span wire:loading wire:target="generateBlogExcerpt" class="inline-block animate-pulse text-[10px]">Generating...</span>
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
                            wire:loading.attr="disabled"
                            wire:target="unpublishFromBlog"
                            wire:confirm="Are you sure you want to unpublish this article from the public blog? It will be switched to draft."
                            class="px-3.5 py-2 rounded-xl bg-rose-950/60 hover:bg-rose-900 border border-rose-500/30 text-rose-300 hover:text-white text-xs font-semibold transition-all cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="unpublishFromBlog">Unpublish Post</span>
                            <span wire:loading wire:target="unpublishFromBlog">Unpublishing...</span>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" @click="closeBlogModalInstant()" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold cursor-pointer">
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

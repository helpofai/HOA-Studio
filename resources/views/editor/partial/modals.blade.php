{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Modals Partial
|--------------------------------------------------------------------------
*/
--}}

<!-- Public & Protected Sharing Modal -->
<div x-show="showShareModalLocal || $wire.showShareModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-3xl glass-elevated border border-white/15 p-6 sm:p-8 space-y-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-lg text-indigo-300">🔗</div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Share & Publish Document</h3>
                    <p class="text-xs text-slate-400">Create an encrypted public view link with custom access controls.</p>
                </div>
            </div>
            <button type="button" @click="closeShareModalInstant()" class="text-slate-400 hover:text-white p-2 cursor-pointer">✕</button>
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
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); alert('Link copied to clipboard!');" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs cursor-pointer">Copy</button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
            <button type="button" @click="closeShareModalInstant()" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold cursor-pointer">Close</button>
            <button
                type="button"
                wire:click="createOrUpdateShare"
                wire:loading.attr="disabled"
                wire:target="createOrUpdateShare"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 cursor-pointer disabled:opacity-50 flex items-center gap-2"
            >
                {{ $isShareActive ? 'Update Share Settings' : 'Generate Public Link' }}
            </button>
        </div>
    </div>
</div>

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

<!-- Universal Document Import Studio Modal -->
<div
    x-show="showImportModalLocal || $wire.showImportModal"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/85 backdrop-blur-md"
    role="dialog"
    aria-modal="true"
>
    <div x-data="{ currentMode: $wire.entangle('importInsertMode') }" class="w-full max-w-5xl max-h-[92vh] rounded-3xl glass-elevated border border-indigo-500/30 p-5 sm:p-7 flex flex-col shadow-2xl animate-in fade-in zoom-in-95 duration-200 relative overflow-hidden">

        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-500 to-violet-600 border border-white/20 flex items-center justify-center text-lg text-white shadow-lg shadow-indigo-500/25">
                    📥
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-white tracking-tight">Universal Document Import Studio</h3>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-[10px] font-mono font-bold uppercase">
                            Multi-Format AI Ready
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">Extract formatted text, headings, tables & deep content intelligence from multi-format files.</p>
                </div>
            </div>
            <button
                type="button"
                @click="closeImportModalInstant()"
                class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/5 transition-all cursor-pointer"
                title="Close"
            >
                ✕
            </button>
        </div>

            <!-- Notification Messages -->
            @if($importErrorMessage)
                <div class="mt-4 p-3.5 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-xs text-rose-300 flex items-start justify-between gap-3 animate-in">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚠️</span>
                        <span>{{ $importErrorMessage }}</span>
                    </div>
                    <button type="button" wire:click="$set('importErrorMessage', '')" class="text-rose-400 hover:text-rose-200">✕</button>
                </div>
            @endif

            @if($importSuccessMessage && !$extractedDocument)
                <div class="mt-4 p-3.5 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-xs text-emerald-300 flex items-center gap-2 animate-in">
                    <span>✓</span>
                    <span>{{ $importSuccessMessage }}</span>
                </div>
            @endif

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto mt-4 space-y-4 pr-1">

                @if(!$extractedDocument)
                    <!-- DROPZONE / UPLOAD VIEW -->
                    <div class="space-y-4">
                        <div
                            class="relative rounded-3xl border-2 border-dashed border-white/20 hover:border-indigo-500/50 bg-slate-900/40 hover:bg-slate-900/70 transition-all p-8 sm:p-12 text-center group flex flex-col items-center justify-center gap-4 cursor-pointer overflow-hidden"
                            x-data="{ isDropping: false }"
                            x-on:dragover.prevent="isDropping = true"
                            x-on:dragleave.prevent="isDropping = false"
                            x-on:drop="isDropping = false"
                            :class="{ 'border-indigo-500 bg-indigo-950/30': isDropping }"
                        >
                            <input
                                type="file"
                                wire:model="importFile"
                                accept=".docx,.pdf,.md,.markdown,.html,.htm,.txt,.csv,.json"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                            />

                            <!-- Loading State -->
                            <div wire:loading wire:target="importFile" class="absolute inset-0 z-30 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center gap-3">
                                <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                                <div class="text-sm font-bold text-white">Extracting & Analyzing Document...</div>
                                <div class="text-xs text-slate-400">Parsing syntax, formatting structures, and computing readability metrics...</div>
                            </div>

                            <!-- Visual Placeholder -->
                            <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-3xl text-indigo-400 group-hover:scale-110 group-hover:border-indigo-400/60 transition-all">
                                📄
                            </div>

                            <div>
                                <div class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">
                                    Drop your document here, or <span class="text-indigo-400 underline decoration-indigo-400/50 underline-offset-4">browse your computer</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    Files up to 20MB are processed instantly via our high-speed native extraction engine.
                                </p>
                            </div>

                            <!-- Supported File Badges -->
                            <div class="flex flex-wrap items-center justify-center gap-1.5 pt-2">
                                <span class="px-2.5 py-1 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-300 text-[11px] font-mono font-bold">.DOCX (Word)</span>
                                <span class="px-2.5 py-1 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-[11px] font-mono font-bold">.PDF (Native)</span>
                                <span class="px-2.5 py-1 rounded-xl bg-violet-500/10 border border-violet-500/30 text-violet-300 text-[11px] font-mono font-bold">.MD / Markdown</span>
                                <span class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-[11px] font-mono font-bold">.HTML / Web</span>
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-[11px] font-mono font-bold">.CSV (Spreadsheets)</span>
                                <span class="px-2.5 py-1 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 text-[11px] font-mono font-bold">.JSON (TipTap AST)</span>
                                <span class="px-2.5 py-1 rounded-xl bg-slate-500/10 border border-slate-500/30 text-slate-300 text-[11px] font-mono font-bold">.TXT (Plain Text)</span>
                            </div>
                        </div>

                        <!-- Extraction Options Pre-Toggles -->
                        <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-3">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pre-Extraction Options</div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer hover:text-white transition-colors">
                                    <input type="checkbox" wire:model="importFormatOptions.clean_whitespace" class="rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500/30" />
                                    <span>Clean redundant whitespace</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer hover:text-white transition-colors">
                                    <input type="checkbox" wire:model="importFormatOptions.preserve_headings" class="rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500/30" />
                                    <span>Preserve heading hierarchy</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer hover:text-white transition-colors">
                                    <input type="checkbox" wire:model="importFormatOptions.smart_typography" class="rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500/30" />
                                    <span>Smart typography (quotes/dashes)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- EXTRACTED DOCUMENT STUDIO WORKSPACE -->
                    <div class="space-y-4">

                        <!-- Extraction Header Banner -->
                        <div class="p-4 rounded-2xl bg-slate-900/90 border border-indigo-500/30 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="px-2.5 py-1 rounded-xl bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 font-mono font-extrabold text-xs uppercase">
                                    {{ $extractedDocument['format'] ?? 'DOC' }}
                                </span>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-white truncate max-w-md">
                                        {{ $extractedDocument['title'] ?: 'Untitled Extracted Document' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono mt-0.5">
                                        <span>{{ number_format($extractedDocument['metrics']['word_count'] ?? 0) }} words</span>
                                        <span>•</span>
                                        <span>{{ number_format($extractedDocument['metrics']['paragraph_count'] ?? 0) }} paragraphs</span>
                                        <span>•</span>
                                        <span class="text-indigo-400">~{{ $extractedDocument['metrics']['reading_time_minutes'] ?? 1 }} min read</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="resetImportState"
                                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-white/10 transition-all cursor-pointer"
                                >
                                    🔄 Import Different File
                                </button>
                            </div>
                        </div>

                        <!-- Main Two-Column Studio Layout -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                            <!-- LEFT COLUMN: Insertion Mode & Quick Intelligence (4 Cols) -->
                            <div class="lg:col-span-4 space-y-4">

                                <!-- Insertion Mode Selector -->
                                <div class="p-4 rounded-2xl bg-slate-900/70 border border-white/10 space-y-3">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                        Canvas Insertion Mode
                                    </div>

                                    <div class="space-y-2">
                                        <!-- Mode 1: Replace Canvas -->
                                        <div
                                            @click="currentMode = 'replace'; $wire.importInsertMode = 'replace'"
                                            class="p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3"
                                            :class="currentMode === 'replace' ? 'bg-indigo-950/40 border-indigo-500/60 shadow-md shadow-indigo-500/10' : 'bg-slate-950/40 border-white/5 hover:border-white/15'"
                                        >
                                            <div class="text-base" :class="currentMode === 'replace' ? 'text-indigo-400' : 'text-slate-500'">🔄</div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold" :class="currentMode === 'replace' ? 'text-white' : 'text-slate-300'">
                                                    Replace Canvas
                                                </div>
                                                <div class="text-[10px] text-slate-400 leading-tight mt-0.5">
                                                    Overwrites current draft. Saves an auto-snapshot before replacement.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mode 2: Append to Bottom -->
                                        <div
                                            @click="currentMode = 'append'; $wire.importInsertMode = 'append'"
                                            class="p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3"
                                            :class="currentMode === 'append' ? 'bg-indigo-950/40 border-indigo-500/60 shadow-md shadow-indigo-500/10' : 'bg-slate-950/40 border-white/5 hover:border-white/15'"
                                        >
                                            <div class="text-base" :class="currentMode === 'append' ? 'text-indigo-400' : 'text-slate-500'">⬇️</div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold" :class="currentMode === 'append' ? 'text-white' : 'text-slate-300'">
                                                    Append to Bottom
                                                </div>
                                                <div class="text-[10px] text-slate-400 leading-tight mt-0.5">
                                                    Adds imported content below existing draft with section divider.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mode 3: Insert at Caret -->
                                        <div
                                            @click="currentMode = 'cursor'; $wire.importInsertMode = 'cursor'"
                                            class="p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3"
                                            :class="currentMode === 'cursor' ? 'bg-indigo-950/40 border-indigo-500/60 shadow-md shadow-indigo-500/10' : 'bg-slate-950/40 border-white/5 hover:border-white/15'"
                                        >
                                            <div class="text-base" :class="currentMode === 'cursor' ? 'text-indigo-400' : 'text-slate-500'">📍</div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold" :class="currentMode === 'cursor' ? 'text-white' : 'text-slate-300'">
                                                    Insert at Caret
                                                </div>
                                                <div class="text-[10px] text-slate-400 leading-tight mt-0.5">
                                                    Injects directly where your cursor was last located.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mode 4: Create as New Document -->
                                        <div
                                            @click="currentMode = 'new_doc'; $wire.importInsertMode = 'new_doc'"
                                            class="p-3 rounded-xl border transition-all cursor-pointer flex items-start gap-3"
                                            :class="currentMode === 'new_doc' ? 'bg-indigo-950/40 border-indigo-500/60 shadow-md shadow-indigo-500/10' : 'bg-slate-950/40 border-white/5 hover:border-white/15'"
                                        >
                                            <div class="text-base" :class="currentMode === 'new_doc' ? 'text-indigo-400' : 'text-slate-500'">📄</div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-bold" :class="currentMode === 'new_doc' ? 'text-white' : 'text-slate-300'">
                                                    Create as New Document
                                                </div>
                                                <div class="text-[10px] text-slate-400 leading-tight mt-0.5">
                                                    Creates a new document in this project and opens it.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Readability & Tone Card -->
                                <div class="p-4 rounded-2xl bg-slate-900/70 border border-white/10 space-y-3">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                        Intelligence Snapshot
                                    </div>

                                    <div class="space-y-2 text-xs">
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/50 border border-white/5">
                                            <span class="text-slate-400">Readability</span>
                                            <span class="font-bold text-indigo-300">
                                                {{ $extractedDocument['readability']['flesch_reading_ease'] ?? 0 }}/100
                                                <span class="text-[10px] text-slate-400">({{ $extractedDocument['readability']['reading_level_label'] ?? 'Standard' }})</span>
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/50 border border-white/5">
                                            <span class="text-slate-400">Reading Level</span>
                                            <span class="font-bold text-slate-200">
                                                Grade {{ $extractedDocument['readability']['flesch_kincaid_grade'] ?? 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/50 border border-white/5">
                                            <span class="text-slate-400">Stylistic Tone</span>
                                            <span class="font-bold text-violet-300">
                                                {{ $extractedDocument['tone']['primary_tone'] ?? 'Informative' }}
                                                <span class="text-[10px] text-slate-400">({{ $extractedDocument['tone']['primary_confidence'] ?? 0 }}%)</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reprocess Options -->
                                <div class="p-3.5 rounded-2xl bg-slate-900/50 border border-white/5 space-y-2">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Format Tuning</div>
                                    <div class="space-y-1.5 text-xs">
                                        <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                            <input type="checkbox" wire:model="importFormatOptions.clean_whitespace" wire:change="reprocessImport" class="rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500/30" />
                                            <span class="text-[11px]">Clean redundant whitespace</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                            <input type="checkbox" wire:model="importFormatOptions.preserve_headings" wire:change="reprocessImport" class="rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500/30" />
                                            <span class="text-[11px]">Preserve heading hierarchy</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                            <input type="checkbox" wire:model="importFormatOptions.smart_typography" wire:change="reprocessImport" class="rounded bg-slate-950 border-white/20 text-indigo-600 focus:ring-indigo-500/30" />
                                            <span class="text-[11px]">Smart typography</span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT COLUMN: Tabbed Content (8 Cols) -->
                            <div class="lg:col-span-8 flex flex-col space-y-3" x-data="{ activeImportTab: 'preview' }">

                                <!-- Tab Navigation -->
                                <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-900/90 border border-white/10 shrink-0">
                                    <button
                                        type="button"
                                        x-on:click="activeImportTab = 'preview'"
                                        :class="activeImportTab === 'preview' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white'"
                                        class="flex-1 py-1.5 px-3 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <span>👁️</span>
                                        <span>Canvas Preview</span>
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="activeImportTab = 'analysis'"
                                        :class="activeImportTab === 'analysis' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white'"
                                        class="flex-1 py-1.5 px-3 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <span>📊</span>
                                        <span>Content Intelligence</span>
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="activeImportTab = 'raw'"
                                        :class="activeImportTab === 'raw' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white'"
                                        class="flex-1 py-1.5 px-3 rounded-xl text-xs font-semibold transition-all cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <span>📝</span>
                                        <span>Clean Raw Text</span>
                                    </button>
                                </div>

                                <!-- TAB 1: FORMATTED PREVIEW -->
                                <div x-show="activeImportTab === 'preview'" class="flex-1 flex flex-col min-h-0 space-y-2">
                                    <div class="p-5 rounded-2xl bg-slate-950/80 border border-white/10 max-h-[380px] overflow-y-auto leading-relaxed text-slate-200 text-xs font-normal">
                                        <div class="prose prose-invert prose-indigo max-w-none text-xs leading-relaxed space-y-3">
                                            {!! $extractedDocument['html'] !!}
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-slate-500 flex items-center justify-between px-1">
                                        <span>✦ Formatting preserves headers, paragraphs, lists & tables seamlessly.</span>
                                        <span>{{ strlen($extractedDocument['html']) }} HTML bytes</span>
                                    </div>
                                </div>

                                <!-- TAB 2: CONTENT INTELLIGENCE -->
                                <div x-show="activeImportTab === 'analysis'" class="flex-1 max-h-[390px] overflow-y-auto space-y-4 pr-1" style="display: none;">

                                    <!-- Executive Extractive Summary -->
                                    @if(!empty($extractedDocument['summary']))
                                        <div class="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-500/30 space-y-2">
                                            <div class="text-[11px] font-bold text-indigo-300 flex items-center gap-1.5">
                                                <span>💡</span>
                                                <span>Executive Extractive Summary</span>
                                            </div>
                                            <div class="space-y-1.5 text-xs text-slate-200 leading-relaxed">
                                                @php
                                                    $summarySentences = is_array($extractedDocument['summary'])
                                                        ? $extractedDocument['summary']
                                                        : array_filter(array_map('trim', preg_split('/(?<=[.?!])\s+/u', (string) $extractedDocument['summary'])));
                                                    if (empty($summarySentences) && !empty($extractedDocument['summary'])) {
                                                        $summarySentences = [trim((string) $extractedDocument['summary'])];
                                                    }
                                                @endphp
                                                @foreach($summarySentences as $sentence)
                                                    <div wire:key="summary-sent-{{ $loop->index }}" class="flex items-start gap-2">
                                                        <span class="text-indigo-400 shrink-0 mt-0.5">•</span>
                                                        <span>{{ $sentence }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Metrics Grid (8 tiles) -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Total Words</div>
                                            <div class="text-base font-extrabold text-white font-mono mt-0.5">
                                                {{ number_format($extractedDocument['metrics']['word_count'] ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Characters</div>
                                            <div class="text-base font-extrabold text-white font-mono mt-0.5">
                                                {{ number_format($extractedDocument['metrics']['char_count_with_spaces'] ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Sentences</div>
                                            <div class="text-base font-extrabold text-white font-mono mt-0.5">
                                                {{ number_format($extractedDocument['metrics']['sentence_count'] ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Paragraphs</div>
                                            <div class="text-base font-extrabold text-white font-mono mt-0.5">
                                                {{ number_format($extractedDocument['metrics']['paragraph_count'] ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Headings</div>
                                            <div class="text-base font-extrabold text-white font-mono mt-0.5">
                                                {{ number_format($extractedDocument['metrics']['heading_count'] ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Tables Found</div>
                                            <div class="text-base font-extrabold text-white font-mono mt-0.5">
                                                {{ number_format($extractedDocument['metrics']['table_count'] ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Reading Time</div>
                                            <div class="text-base font-extrabold text-indigo-400 font-mono mt-0.5">
                                                {{ $extractedDocument['metrics']['reading_time_minutes'] ?? 1 }} min
                                            </div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-950/60 border border-white/5">
                                            <div class="text-[10px] text-slate-400">Speaking Time</div>
                                            <div class="text-base font-extrabold text-indigo-400 font-mono mt-0.5">
                                                {{ $extractedDocument['metrics']['speaking_time_minutes'] ?? 1 }} min
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stylistic Tone Breakdown -->
                                    @if(!empty($extractedDocument['tone']['tones']))
                                        <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-2.5">
                                            <div class="text-[11px] font-bold text-slate-300">Stylistic Register & Tone Breakdown</div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                @foreach($extractedDocument['tone']['tones'] as $toneName => $toneScore)
                                                    <div wire:key="tone-{{ $toneName }}" class="space-y-1">
                                                        <div class="flex items-center justify-between text-[11px]">
                                                            <span class="text-slate-400">{{ $toneName }}</span>
                                                            <span class="text-slate-200 font-mono font-bold">{{ $toneScore }}%</span>
                                                        </div>
                                                        <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $toneScore }}%"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Top Keyword Entities -->
                                    @if(!empty($extractedDocument['keywords']) && is_array($extractedDocument['keywords']))
                                        <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-2">
                                            <div class="text-[11px] font-bold text-slate-300">Top Key Entities & Frequent Terms</div>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($extractedDocument['keywords'] as $item)
                                                    <span wire:key="imp-kw-{{ $loop->index }}" class="px-2.5 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-[11px] font-mono flex items-center gap-1.5">
                                                        <span>{{ $item['term'] ?? $item['word'] ?? (is_string($item) ? $item : '') }}</span>
                                                        @if(isset($item['count']))
                                                            <span class="text-[9px] px-1 rounded bg-indigo-500/20 text-indigo-200">{{ $item['count'] }}</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <!-- TAB 3: RAW CLEAN TEXT -->
                                <div x-show="activeImportTab === 'raw'" class="flex-1 flex flex-col min-h-0 space-y-2" style="display: none;">
                                    <textarea
                                        readonly
                                        rows="12"
                                        class="w-full flex-1 p-4 rounded-2xl bg-slate-950/80 border border-white/10 font-mono text-[11px] text-slate-300 leading-relaxed focus:outline-none resize-none select-all"
                                    >{{ $extractedDocument['plain_text'] }}</textarea>
                                    <div class="flex items-center justify-between text-[11px] text-slate-400 px-1">
                                        <span>{{ number_format(strlen($extractedDocument['plain_text'])) }} characters</span>
                                        <button
                                            type="button"
                                            onclick="navigator.clipboard.writeText({{ json_encode($extractedDocument['plain_text']) }}); alert('Raw text copied to clipboard!');"
                                            class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer"
                                        >
                                            📋 Copy Clean Text
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between gap-3 pt-4 mt-3 border-t border-white/10 shrink-0">
                <button
                    type="button"
                    @click="closeImportModalInstant()"
                    class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold cursor-pointer transition-colors"
                >
                    Cancel
                </button>

                <div class="flex items-center gap-2.5">
                    @if($extractedDocument)
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText({{ json_encode($extractedDocument['html']) }}); alert('HTML copied to clipboard!');"
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-white/10 transition-all cursor-pointer"
                        >
                            📋 Copy HTML
                        </button>

                        <button
                            type="button"
                            wire:click="confirmImport"
                            wire:loading.attr="disabled"
                            wire:target="confirmImport"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all cursor-pointer flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="confirmImport">
                                @if($importInsertMode === 'replace')
                                    🔄 Replace Canvas
                                @elseif($importInsertMode === 'append')
                                    ⬇️ Append to Canvas
                                @elseif($importInsertMode === 'cursor')
                                    📍 Insert at Caret
                                @elseif($importInsertMode === 'new_doc')
                                    📄 Create New Document
                                @endif
                            </span>
                            <span wire:loading wire:target="confirmImport">
                                Importing...
                            </span>
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>

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

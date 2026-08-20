{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/
--}}

<div 
    class="min-h-screen bg-slate-950 text-slate-100 selection:bg-indigo-500/30 selection:text-indigo-200 py-10 px-4 sm:px-6"
    x-data="{ 
        copied: false,
        theme: 'dark',
        copyContent() {
            const el = document.getElementById('shared-doc-body');
            if (el) {
                navigator.clipboard.writeText(el.innerText);
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            }
        }
    }"
    :class="theme === 'light' ? 'bg-slate-100 text-slate-900' : 'bg-slate-950 text-slate-100'"
>
    <!-- Password Unlock Modal / Gate -->
    @if(!$isUnlocked)
        <div class="max-w-md mx-auto py-20">
            <x-glass.card variant="elevated" class="p-8 space-y-6 text-center border-indigo-500/30 shadow-2xl">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-2xl mx-auto shadow-inner">
                    🔒
                </div>

                <div class="space-y-1">
                    <h2 class="text-xl font-black tracking-tight" :class="theme === 'light' ? 'text-slate-900' : 'text-white'">
                        Password Protected Document
                    </h2>
                    <p class="text-xs" :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'">
                        This document has been protected by its author. Please enter the password to view.
                    </p>
                </div>

                @if($errorMessage)
                    <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-semibold">
                        {{ $errorMessage }}
                    </div>
                @endif

                <form wire:submit="unlock" class="space-y-4">
                    <input 
                        type="password" 
                        wire:model="passwordInput" 
                        placeholder="Enter password..."
                        class="w-full bg-slate-900 border border-white/15 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 text-center tracking-widest"
                        required
                    />

                    <button 
                        type="submit" 
                        class="w-full py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all cursor-pointer"
                    >
                        Unlock & View Document
                    </button>
                </form>
            </x-glass.card>
        </div>
    @else
        <!-- Shared Document Reader View -->
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Reader Navigation Top Bar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-2xl border transition-all" :class="theme === 'light' ? 'bg-white border-slate-200 shadow-sm' : 'bg-slate-900/80 border-white/10 shadow-xl backdrop-blur-md'">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-cyan-400 p-[1px] shadow-md shrink-0">
                        <div class="w-full h-full bg-slate-950 rounded-[11px] flex items-center justify-center font-black text-[10px] text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300">
                            HOA
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold" :class="theme === 'light' ? 'text-slate-900' : 'text-white'">HelpOfAi Studio</div>
                        <div class="text-[10px] text-indigo-400 font-mono">Shared Document</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Theme Toggle -->
                    <button 
                        type="button" 
                        @click="theme = (theme === 'dark' ? 'light' : 'dark')"
                        class="p-2 rounded-xl border text-xs transition-all cursor-pointer"
                        :class="theme === 'light' ? 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200' : 'bg-slate-900 border-white/10 text-slate-300 hover:text-white'"
                        title="Toggle Reading Theme"
                    >
                        <span x-text="theme === 'dark' ? '☀️ Light' : '🌙 Dark'"></span>
                    </button>

                    <!-- Copy Content Button -->
                    @if($share->allow_copy)
                        <button 
                            type="button" 
                            @click="copyContent()"
                            class="px-3 py-2 rounded-xl border text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5"
                            :class="theme === 'light' ? 'bg-slate-100 border-slate-300 text-slate-800 hover:bg-slate-200' : 'bg-slate-900 border-white/10 text-slate-300 hover:text-white'"
                        >
                            <span x-show="!copied">📋 Copy</span>
                            <span x-show="copied" class="text-emerald-400 font-bold">✓ Copied!</span>
                        </button>
                    @endif

                    <!-- Download Export Formats -->
                    @if($share->allow_download)
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                type="button" 
                                @click="open = !open"
                                @click.outside="open = false"
                                class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer flex items-center gap-1.5"
                            >
                                <span>⬇ Download</span>
                                <span class="text-[10px]">▼</span>
                            </button>

                            <div 
                                x-show="open" 
                                x-transition
                                class="absolute right-0 mt-2 w-44 rounded-xl bg-slate-900 border border-white/15 shadow-2xl p-1.5 z-50 text-xs space-y-1 font-mono text-slate-300"
                                style="display: none;"
                            >
                                <a href="{{ route('public.export', ['token' => $share->share_token, 'format' => 'md']) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors">
                                    <span>📝 Markdown (.md)</span>
                                </a>
                                <a href="{{ route('public.export', ['token' => $share->share_token, 'format' => 'html']) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors">
                                    <span>🌐 HTML (.html)</span>
                                </a>
                                <a href="{{ route('public.export', ['token' => $share->share_token, 'format' => 'txt']) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors">
                                    <span>📄 Plain Text (.txt)</span>
                                </a>
                                <a href="{{ route('public.export', ['token' => $share->share_token, 'format' => 'docx']) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors">
                                    <span>📘 Word (.doc)</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document Content Paper Card -->
            <div 
                class="p-8 sm:p-12 rounded-3xl border transition-all duration-300 shadow-2xl space-y-8"
                :class="theme === 'light' ? 'bg-white border-slate-200 text-slate-800' : 'bg-slate-900/60 border-white/10 text-slate-200 backdrop-blur-xl'"
            >
                <!-- Document Header & Meta -->
                <div class="space-y-3 pb-6 border-b" :class="theme === 'light' ? 'border-slate-200' : 'border-white/10'">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight" :class="theme === 'light' ? 'text-slate-950' : 'text-white'">
                        {{ $document->title }}
                    </h1>

                    <div class="flex items-center gap-3 text-xs font-mono" :class="theme === 'light' ? 'text-slate-500' : 'text-slate-400'">
                        <span>{{ number_format($document->word_count) }} words</span>
                        <span>•</span>
                        <span>{{ $document->reading_time_minutes }} min read</span>
                        <span>•</span>
                        <span>Updated {{ $document->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Document Body HTML -->
                <div 
                    id="shared-doc-body" 
                    class="prose prose-invert max-w-none leading-relaxed text-sm sm:text-base space-y-4"
                    :class="theme === 'light' ? 'prose-slate text-slate-800' : 'text-slate-200'"
                >
                    {!! $document->content->content_html ?? '<p class=\"italic text-slate-500\">No content in this document yet.</p>' !!}
                </div>
            </div>

            <!-- Public Footer -->
            <div class="text-center py-6 text-xs text-slate-500 font-mono">
                Powered by <strong class="text-indigo-400">HelpOfAi Studio</strong> • Multi-Model AI Engine
            </div>
        </div>
    @endif
</div>
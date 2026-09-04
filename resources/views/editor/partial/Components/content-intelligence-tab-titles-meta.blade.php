{{--
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Titles & Meta Intelligence Tab
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
--}}

<!-- ─── TAB 2: AI VIRAL TITLES & META DESCRIPTIONS ───────────────────── -->
<div x-show="rightTab === 'titles_meta'" class="space-y-3.5" style="display: none;">

            <!-- 🌐 MULTI-PLATFORM SERP & SOCIAL PREVIEW SIMULATOR -->
            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner" x-data="{ serpPlatform: 'desktop' }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs">🌐</span>
                        <span class="text-xs font-bold text-white">Search & Social Simulator</span>
                    </div>
                    <span class="text-[9.5px] font-mono px-2 py-0.5 rounded-full bg-indigo-950 text-indigo-300 border border-indigo-500/30 font-bold">
                        Live Preview
                    </span>
                </div>

                <!-- Platform Switcher Tabs -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-white/5 text-[10px] font-mono overflow-x-auto hoa-custom-scrollbar">
                    <button type="button" x-on:click="serpPlatform = 'desktop'" :class="serpPlatform === 'desktop' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-all flex items-center gap-1 shrink-0 cursor-pointer">
                        <span>💻 Desktop</span>
                    </button>
                    <button type="button" x-on:click="serpPlatform = 'mobile'" :class="serpPlatform === 'mobile' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-all flex items-center gap-1 shrink-0 cursor-pointer">
                        <span>📱 Mobile</span>
                    </button>
                    <button type="button" x-on:click="serpPlatform = 'ai_overview'" :class="serpPlatform === 'ai_overview' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-all flex items-center gap-1 shrink-0 cursor-pointer">
                        <span>🤖 AI Overview</span>
                    </button>
                    <button type="button" x-on:click="serpPlatform = 'twitter'" :class="serpPlatform === 'twitter' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-all flex items-center gap-1 shrink-0 cursor-pointer">
                        <span>🐦 X / Card</span>
                    </button>
                    <button type="button" x-on:click="serpPlatform = 'social'" :class="serpPlatform === 'social' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-1 rounded-lg transition-all flex items-center gap-1 shrink-0 cursor-pointer">
                        <span>💼 Social / OG</span>
                    </button>
                </div>

                <!-- 1. GOOGLE DESKTOP SERP -->
                <div x-show="serpPlatform === 'desktop'" class="p-3 rounded-xl bg-white text-slate-900 shadow-md space-y-1 text-left font-sans select-none">
                    <div class="flex items-center gap-2 text-xs text-slate-600 truncate font-mono">
                        <span class="w-4 h-4 rounded-full bg-indigo-600 text-white text-[9px] flex items-center justify-center font-bold">H</span>
                        <span class="truncate">{{ config('app.url') }} &rsaquo; {{ !empty($targetKeyword) ? \Illuminate\Support\Str::slug($targetKeyword) : 'guide' }}</span>
                    </div>
                    <h4 class="text-[#1a0dab] hover:underline text-sm font-medium leading-tight line-clamp-1 cursor-pointer">
                        {{ !empty($title) ? $title : 'Document Title - Complete Guide in 2026' }}
                    </h4>
                    <p class="text-xs text-[#4d5156] leading-snug line-clamp-2">
                        {{ !empty($metaDescription) ? $metaDescription : 'Comprehensive overview detailing key methodologies, metrics, and actionable steps to achieve optimal results.' }}
                    </p>
                </div>

                <!-- 2. GOOGLE MOBILE SERP -->
                <div x-show="serpPlatform === 'mobile'" style="display: none;" class="p-3 rounded-2xl bg-white text-slate-900 shadow-md space-y-1.5 text-left font-sans select-none max-w-[320px] mx-auto border border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] flex items-center justify-center font-bold shrink-0">H</span>
                        <div class="leading-none truncate text-[11px]">
                            <div class="font-bold text-slate-800 truncate">{{ config('app.name', 'HelpOfAi') }}</div>
                            <div class="text-[9.5px] text-slate-500 truncate font-mono">{{ config('app.url') }}</div>
                        </div>
                    </div>
                    <h4 class="text-[#1a0dab] text-xs font-semibold leading-tight line-clamp-2">
                        {{ !empty($title) ? $title : 'Document Title - Complete Guide in 2026' }}
                    </h4>
                    <p class="text-[11px] text-[#4d5156] leading-snug line-clamp-2">
                        {{ !empty($metaDescription) ? $metaDescription : 'Comprehensive overview detailing key methodologies, metrics, and actionable steps to achieve optimal results.' }}
                    </p>
                </div>

                <!-- 3. GOOGLE AI OVERVIEW CARD -->
                <div x-show="serpPlatform === 'ai_overview'" style="display: none;" class="p-3 rounded-xl bg-gradient-to-br from-purple-950/40 via-slate-900 to-indigo-950/40 border border-purple-500/40 shadow-md space-y-2 text-left font-sans">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                            <span class="text-[10.5px] font-bold text-purple-300 font-mono tracking-wide uppercase">Google AI Overview (Gemini)</span>
                        </div>
                        <span class="text-[9px] font-mono px-1.5 py-0.2 rounded bg-purple-900/60 text-purple-200 border border-purple-400/30">Position Zero</span>
                    </div>
                    <p class="text-xs text-slate-200 leading-relaxed">
                        {{ !empty($metaDescription) ? $metaDescription : 'An AI Overview synthesizes this document directly into concise bullet points, highlighting key definitions and actionable takeaways.' }}
                    </p>
                    <div class="p-2 rounded-lg bg-black/40 border border-white/10 flex items-center justify-between text-[10px] font-mono text-slate-400">
                        <span class="truncate">Cited Source: <strong class="text-white">{{ config('app.name') }}</strong></span>
                        <span class="text-emerald-400 font-bold">High Authority</span>
                    </div>
                </div>

                <!-- 4. TWITTER / X SUMMARY CARD -->
                <div x-show="serpPlatform === 'twitter'" style="display: none;" class="rounded-xl overflow-hidden bg-black border border-neutral-800 text-left font-sans select-none">
                    <div class="h-28 bg-gradient-to-br from-slate-800 via-indigo-950 to-slate-900 flex items-center justify-center relative">
                        <span class="text-2xl font-black text-white/30 tracking-widest font-mono">HOA STUDIO</span>
                        <div class="absolute bottom-2 left-2 px-2 py-0.5 rounded bg-black/70 backdrop-blur text-[9px] font-mono text-white">
                            {{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'helpofai.com' }}
                        </div>
                    </div>
                    <div class="p-2.5 space-y-1">
                        <div class="text-[10px] font-mono text-neutral-500 truncate">{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'helpofai.com' }}</div>
                        <h4 class="text-white text-xs font-bold leading-tight line-clamp-1">
                            {{ !empty($title) ? $title : 'Document Title Tag' }}
                        </h4>
                        <p class="text-neutral-400 text-[11px] leading-snug line-clamp-2">
                            {{ !empty($metaDescription) ? $metaDescription : 'Engaging summary description optimized for viral social click-through rates.' }}
                        </p>
                    </div>
                </div>

                <!-- 5. SOCIAL / OPENGRAPH PREVIEW -->
                <div x-show="serpPlatform === 'social'" style="display: none;" class="rounded-xl overflow-hidden bg-slate-950 border border-white/10 text-left font-sans select-none">
                    <div class="h-28 bg-gradient-to-r from-blue-900/60 to-indigo-950 flex items-center justify-center">
                        <span class="text-xs font-mono text-indigo-300 font-bold">OpenGraph Social Preview</span>
                    </div>
                    <div class="p-2.5 space-y-1">
                        <div class="text-[9.5px] font-mono uppercase tracking-wider text-slate-500">{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'helpofai.com' }}</div>
                        <h4 class="text-white text-xs font-bold leading-tight line-clamp-1">
                            {{ !empty($title) ? $title : 'Document Title Tag' }}
                        </h4>
                        <p class="text-slate-400 text-[11px] leading-snug line-clamp-2">
                            {{ !empty($metaDescription) ? $metaDescription : 'Compelling social preview card with rich metadata.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Viral Titles Generator -->

            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">

                <div class="flex items-center justify-between">

                    <span class="text-xs font-bold text-white flex items-center gap-1.5">

                        <span class="text-violet-400">✓</span>

                        <span>AI Viral Title Generator</span>

                    </span>

                    <button 

                        type="button" 

                        wire:click="generateSeoTitles" 

                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"

                    >

                        <span wire:loading.remove wire:target="generateSeoTitles">⚡ Generate Titles</span>

                        <span wire:loading wire:target="generateSeoTitles" class="animate-pulse">Generating...</span>

                    </button>

                </div>



                <!-- Current Title Input -->
                <div class="space-y-1">
                    @php $tLen = strlen($title ?? ''); @endphp
                    <div class="flex items-center justify-between text-[10px] font-mono">
                        <span class="uppercase font-bold text-slate-400">Active Title Tag:</span>
                        <span class="{{ $tLen >= 50 && $tLen <= 65 ? 'text-emerald-400 font-bold' : ($tLen > 65 ? 'text-rose-400 font-bold' : 'text-amber-400') }}">
                            {{ $tLen }}/60 chars {{ $tLen >= 50 && $tLen <= 65 ? '✓ Ideal' : '• Target: 50-65' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <input 
                            type="text" 
                            wire:model.lazy="title" 
                            placeholder="Enter primary document title..."
                            class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-sans"
                        />

                        <button type="button" wire:click="saveActiveTitle" wire:loading.attr="disabled" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/30 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <span class="dual w-3 h-3 border-2" wire:loading wire:target="saveActiveTitle,applyTitle"></span>
                            <span wire:loading.remove wire:target="saveActiveTitle,applyTitle">Save</span>
                            <span wire:loading wire:target="saveActiveTitle,applyTitle">Saving...</span>
                        </button>
                    </div>
                </div>

                <!-- Generated Suggestions -->
                <div class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">
                    @if(!empty($aiTitles))
                        @foreach($aiTitles as $t)
                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9.5px] font-mono text-emerald-400 bg-emerald-950/80 px-1.5 py-0.2 rounded border border-emerald-500/30">🎯 High CTR</span>
                                    <span class="text-[9.5px] font-mono text-slate-500">{{ strlen($t) }} chars</span>
                                </div>
                                <div class="text-slate-200 text-xs leading-snug font-medium">{{ $t }}</div>
                                <button type="button" wire:click="applyTitle(@js($t))" class="w-full py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10.5px] font-bold transition-colors cursor-pointer">
                                    ✓ Apply Title to Document
                                </button>
                            </div>
                        @endforeach

                    @else

                        <p class="text-slate-500 text-[11px] italic py-1">Click "Generate Titles" to generate search-optimized headline angles.</p>

                    @endif

                </div>

            </div>



            <!-- Meta Descriptions Generator -->

            <div class="space-y-2.5 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 shadow-inner">

                <div class="flex items-center justify-between">

                    <span class="text-xs font-bold text-white flex items-center gap-1.5">

                        <span class="text-emerald-400">📝</span>

                        <span>AI Meta Description</span>

                    </span>

                    <button 

                        type="button" 

                        wire:click="generateMetaDescriptions" 

                        class="px-3 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-mono text-[10.5px] font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer"

                    >

                        <span wire:loading.remove wire:target="generateMetaDescriptions">⚡ Generate Meta</span>

                        <span wire:loading wire:target="generateMetaDescriptions" class="animate-pulse">Generating...</span>

                    </button>

                </div>



                <!-- Active Meta Textarea -->

                <div class="space-y-1">

                    <div class="flex items-center justify-between text-[10px] font-mono">

                        <span class="uppercase font-bold text-slate-400">Active Meta Description:</span>

                        @php $metaLen = strlen($metaDescription ?? ''); @endphp

                        <span class="{{ $metaLen >= 140 && $metaLen <= 160 ? 'text-emerald-400 font-bold' : ($metaLen > 160 ? 'text-red-400' : 'text-amber-400') }}">

                            {{ $metaLen }}/160 chars

                        </span>

                    </div>

                    <textarea 

                        wire:model.lazy="metaDescription" 

                        rows="2" 

                        placeholder="Enter compelling meta description..." 

                        class="w-full bg-slate-950 border border-white/15 rounded-xl p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none font-sans leading-relaxed shadow-inner"

                    ></textarea>

                    <button type="button" wire:click="applyMetaDescription" wire:loading.attr="disabled" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/30 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <span class="dual w-3 h-3 border-2" wire:loading wire:target="applyMetaDescription"></span>
                        <span wire:loading.remove wire:target="applyMetaDescription">Save Meta Description</span>
                        <span wire:loading wire:target="applyMetaDescription">Saving...</span>
                    </button>
                </div>



                <!-- Generated Meta List -->

                <div class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">

                    @if(!empty($aiMetaDescriptions))

                        @foreach($aiMetaDescriptions as $meta)

                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5">

                                <p class="text-slate-300 text-xs leading-relaxed">{{ $meta }}</p>

                                <div class="flex items-center justify-between pt-1 text-[10px] font-mono">

                                    <span class="text-slate-500">{{ strlen($meta) }} chars</span>

                                    <button type="button" wire:click="applyMetaDescription(@js($meta))" class="px-2.5 py-1 rounded-lg bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white font-bold transition-colors">

                                        Use Meta

                                    </button>

                                </div>

                            </div>

                        @endforeach

                    @else

                        <p class="text-slate-500 text-[11px] italic py-1">Click "Generate Meta" for click-magnet snippet copy.</p>

                    @endif

                </div>

            </div>

        </div>




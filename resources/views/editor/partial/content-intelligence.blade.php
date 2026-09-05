{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Intelligence Sidebar
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
|
| Architecture: Slim orchestrator - each tab is a dedicated sub-partial in
|   resources/views/editor/partial/Components/tab-{name}.blade.php
|
| Tabs:
|   1. tab-seo.blade.php          - Rank Math Enterprise 4-Pillar SEO Engine
|   2. tab-titles-meta.blade.php  - AI Viral Titles & Meta Descriptions
|   3. tab-ai-ideas.blade.php     - Content Gaps, FAQ Schema, Content Ideas
|   4. tab-keywords.blade.php     - Secondary & LSI Keywords, Density Matrix
|   5. tab-quality.blade.php      - 10-Point E-E-A-T Quality Audit
|   6. tab-outline.blade.php      - Interactive Document Outline Tree
|   7. tab-versions.blade.php     - Snapshot Versions Timeline & Restore
|
*/
--}}

<div class="space-y-4 h-full flex flex-col">
    <div class="editor-column hoa-custom-scrollbar">
        <!-- Main Header -->
        <div class="flex items-center justify-between pb-2 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h2 class="text-xs uppercase font-extrabold text-white tracking-wider">Content Intelligence</h2>
            </div>
            <span class="text-[10px] font-mono text-emerald-400 font-bold px-2 py-0.5 rounded-full bg-emerald-600/15 border border-emerald-500/30" x-text="'Goal: ' + Math.min(100, Math.round((wordCount/targetWordGoal)*100)) + '%'"></span>
        </div>

        <!-- Responsive Multi-Row Tab Navigation Grid -->
        <div class="grid grid-cols-4 gap-1 p-1.5 rounded-2xl bg-slate-950/90 border border-white/10 text-xs font-mono select-none shadow-inner backdrop-blur-md">
            <button type="button" x-on:click="rightTab = 'post'" :class="rightTab === 'post' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Post Settings (Featured Image, Categories, Tags)">
                <span class="text-sm">📝</span><span class="text-[10px] font-bold truncate w-full">Post</span>
            </button>
            <button type="button" x-on:click="rightTab = 'seo'" :class="rightTab === 'seo' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Rank Math 100/100 SEO Audit">
                <span class="text-sm">🎯</span><span class="text-[10px] font-bold truncate w-full">SEO</span>
            </button>
            <button type="button" x-on:click="rightTab = 'titles_meta'" :class="rightTab === 'titles_meta' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Viral Titles & Meta Descriptions">
                <span class="text-sm">✨</span><span class="text-[10px] font-bold truncate w-full">Titles</span>
            </button>
            <button type="button" x-on:click="rightTab = 'ai_ideas'" :class="rightTab === 'ai_ideas' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Content Gaps & FAQ Schema">
                <span class="text-sm">💡</span><span class="text-[10px] font-bold truncate w-full">Gaps</span>
            </button>
            <button type="button" x-on:click="rightTab = 'keywords'" :class="rightTab === 'keywords' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Secondary & LSI Keywords">
                <span class="text-sm">🏷️</span><span class="text-[10px] font-bold truncate w-full">Keywords</span>
            </button>
            <button type="button" x-on:click="rightTab = 'quality'" :class="rightTab === 'quality' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="E-E-A-T Quality Audit">
                <span class="text-sm">🏆</span><span class="text-[10px] font-bold truncate w-full">Audit</span>
            </button>
            <button type="button" x-on:click="rightTab = 'outline'; updateOutline()" :class="rightTab === 'outline' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Interactive Document Outline">
                <span class="text-sm">📑</span><span class="text-[10px] font-bold truncate w-full">Outline</span>
            </button>
            <button type="button" x-on:click="rightTab = 'versions'" :class="rightTab === 'versions' ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-600/30 border border-indigo-400/50' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent'" class="py-2 px-1 rounded-xl text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-0.5" title="Version Snapshots Timeline">
                <span class="text-sm">🕒</span><span class="text-[10px] font-bold truncate w-full">History</span>
            </button>
        </div>

        {{-- Tab Content Panels (each tab is a dedicated sub-partial for clean maintainability) --}}
        @include('editor.partial.Components.content-intelligence-tab-post')
        @include('editor.partial.Components.content-intelligence-tab-seo')
        @include('editor.partial.Components.content-intelligence-tab-titles-meta')
        @include('editor.partial.Components.content-intelligence-tab-ai-ideas')
        @include('editor.partial.Components.content-intelligence-tab-keywords')
        @include('editor.partial.Components.content-intelligence-tab-quality')
        @include('editor.partial.Components.content-intelligence-tab-outline')
        @include('editor.partial.Components.content-intelligence-tab-versions')

    </div>
</div>

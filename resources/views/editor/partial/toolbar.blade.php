{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Toolbar Partial
|--------------------------------------------------------------------------
*/
--}}

<div class="editor-toolbar">
    <!-- Left Section: Navigation & Inline Editable Document Title -->
    <div class="flex-1 flex flex-wrap sm:flex-nowrap items-center gap-2.5 min-w-0">
        <a 
            href="{{ route('documents.index') }}" 
            class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/5 border border-transparent hover:border-white/10 transition-all flex items-center gap-1.5 text-xs font-semibold shrink-0 group active:scale-95"
            title="Return to Documents"
        >
            <span class="text-slate-500 group-hover:text-indigo-400 transition-colors">&larr;</span>
            <span class="hidden xs:inline">Back</span>
        </a>

        <div class="hidden sm:block h-5 w-[1px] bg-white/10 shrink-0"></div>

        <div class="flex items-center gap-2 min-w-0 flex-1">
            <span class="text-[11px] text-indigo-400/90 font-mono font-semibold hidden md:inline shrink-0 truncate max-w-[140px]">
                {{ $document->project->name ?? 'General' }} /
            </span>
            <input 
                type="text" 
                wire:model.lazy="title" 
                placeholder="Untitled Document..." 
                class="text-sm sm:text-base font-extrabold text-white bg-transparent border-b border-transparent hover:border-white/20 focus:border-indigo-500 focus:outline-none px-1.5 py-0.5 transition-all w-full max-w-sm sm:max-w-md truncate"
            />
        </div>
    </div>

    <!-- Right Section: Actions, Engine Switcher, View Modes & Export -->
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <!-- Multi-Editor Engine Switcher Dropdown -->
        <div class="relative z-50" x-data="{ open: false }">
            <button 
                x-on:click="open = !open" 
                type="button" 
                class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-900/90 border border-white/10 hover:border-indigo-500/40 text-xs text-slate-200 hover:text-white flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-sm transition-all active:scale-95"
            >
                <span class="text-indigo-400 font-bold">✦ Engine:</span>
                <span class="font-bold text-white">{{ $availableEditors[$editorType]['name'] ?? 'Tiptap' }}</span>
                <span class="text-[9px] text-slate-400">▼</span>
            </button>

            <div 
                x-show="open" 
                x-on:click.outside="open = false" 
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="absolute right-0 mt-2 w-72 rounded-2xl bg-slate-900/98 border border-white/20 p-2 shadow-2xl z-[100] space-y-1 backdrop-blur-2xl"
                style="display: none;"
            >
                <div class="px-2 py-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Universal Multi-Editor Platform</div>
                @foreach($availableEditors as $key => $editor)
                    <button 
                        type="button" 
                        x-on:click="requestEngineSwitch('{{ $key }}'); open = false"
                        class="w-full text-left p-2.5 rounded-xl text-xs flex flex-col transition-colors {{ $editorType === $key ? 'bg-indigo-600/25 text-indigo-300 border border-indigo-500/40' : 'text-slate-300 hover:bg-white/10' }}"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-white">{{ $editor['name'] }}</span>
                            @if($editorType === $key)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500 text-white font-mono font-bold">Active</span>
                            @endif
                        </div>
                        <span class="text-[10px] text-slate-400 mt-0.5 leading-snug">{{ $editor['description'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Save Status Live Indicator Badge -->
        <div class="hidden xs:flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-900/70 border border-white/8 text-[11px] text-slate-300 font-mono select-none">
            <span class="w-2 h-2 rounded-full {{ $isSaving ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' }}"></span>
            <span>{{ $saveStatusText }}</span>
        </div>

        <!-- Focus Mode & Panel Toggles Group -->
        <div class="flex items-center rounded-xl bg-slate-900/90 border border-white/10 p-0.5 shadow-inner">
            <button 
                type="button" 
                x-on:click="showLeftPanel = !showLeftPanel" 
                :class="showLeftPanel ? 'bg-indigo-600/30 text-indigo-300 font-bold border border-indigo-500/30' : 'text-slate-400 hover:text-white border border-transparent'"
                class="px-2 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer" 
                title="Toggle AI Command Center (Ctrl+K)"
            >
                ◧ AI
            </button>
            <button 
                type="button" 
                x-on:click="toggleFocusMode()" 
                :class="(!showLeftPanel && !showRightPanel) ? 'bg-purple-600/40 text-purple-300 font-bold border border-purple-500/30' : 'text-slate-400 hover:text-white border border-transparent'"
                class="px-2 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer" 
                title="Zen Focus Mode (Ctrl+Shift+F)"
            >
                ⛶ Zen
            </button>
            <button 
                type="button" 
                x-on:click="showRightPanel = !showRightPanel" 
                :class="showRightPanel ? 'bg-indigo-600/30 text-indigo-300 font-bold border border-indigo-500/30' : 'text-slate-400 hover:text-white border border-transparent'"
                class="px-2 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer" 
                title="Toggle Content Intelligence"
            >
                ◨ Intel
            </button>
        </div>

        <!-- Share & Public Link Button -->
        <button 
            type="button" 
            wire:click="openShareModal" 
            class="px-2.5 sm:px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 active:scale-95 {{ $isShareActive ? 'bg-indigo-600/20 border-indigo-500/50 text-indigo-300 shadow-md shadow-indigo-500/10' : 'bg-slate-900/90 border-white/10 hover:border-indigo-500/40 text-slate-300 hover:text-white' }}"
            title="Share document publicly"
        >
            <span>🔗</span>
            <span class="hidden sm:inline">Share</span>
            @if($isShareActive)
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            @endif
        </button>

        <!-- Export Menu Dropdown -->
        <div class="relative z-50" x-data="{ open: false }">
            <button 
                x-on:click="open = !open" 
                type="button" 
                class="px-3 sm:px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 flex items-center gap-1.5 sm:gap-2 cursor-pointer transition-all active:scale-95"
            >
                <span>Export</span>
                <span class="text-[9px]">▼</span>
            </button>

            <div 
                x-show="open" 
                x-on:click.outside="open = false" 
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="absolute right-0 mt-2 w-52 rounded-2xl bg-slate-900/98 border border-white/20 p-2 shadow-2xl z-[100] space-y-1 backdrop-blur-2xl"
                style="display: none;"
            >
                <div class="px-2 py-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Export Document As</div>
                <a href="{{ route('documents.export', ['id' => $document->id, 'format' => 'html']) }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs text-slate-200 hover:bg-white/10 transition-colors">
                    <span>🌐</span> HTML Webpage
                </a>
                <a href="{{ route('documents.export', ['id' => $document->id, 'format' => 'md']) }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs text-slate-200 hover:bg-white/10 transition-colors">
                    <span>📝</span> Markdown (.md)
                </a>
                <a href="{{ route('documents.export', ['id' => $document->id, 'format' => 'txt']) }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs text-slate-200 hover:bg-white/10 transition-colors">
                    <span>📄</span> Plain Text (.txt)
                </a>
                <a href="{{ route('documents.export', ['id' => $document->id, 'format' => 'json']) }}" class="flex items-center gap-2.5 p-2 rounded-xl text-xs text-slate-200 hover:bg-white/10 transition-colors">
                    <span>📦</span> JSON AST
                </a>
            </div>
        </div>
    </div>
</div>

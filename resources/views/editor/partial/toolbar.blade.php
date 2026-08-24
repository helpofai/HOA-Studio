{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Toolbar Partial
|--------------------------------------------------------------------------
*/
--}}

<div class="editor-toolbar">
    <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <a href="{{ route('documents.index') }}" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/5 transition-colors flex items-center gap-1.5 text-xs font-semibold">
            &larr; Back to Documents
        </a>

        <div class="hidden sm:block h-5 w-[1px] bg-white/10"></div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-indigo-400 font-mono font-medium">{{ $document->project->name ?? 'General Project' }} /</span>
            <input 
                type="text" 
                wire:model.lazy="title" 
                placeholder="Untitled Document..." 
                class="text-base sm:text-lg font-bold text-white bg-transparent border-b border-transparent hover:border-white/20 focus:border-indigo-500 focus:outline-none px-1 py-0.5 transition-all min-w-[200px]"
            />
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Multi-Editor Engine Switcher Dropdown -->
        <div class="relative z-50" x-data="{ open: false }">
            <button 
                x-on:click="open = !open" 
                type="button" 
                class="px-3 py-1.5 rounded-xl bg-slate-900/90 border border-white/10 hover:border-indigo-500/40 text-xs text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer shadow-sm transition-all"
            >
                <span class="text-indigo-400 font-bold">✦ Engine:</span>
                <span class="font-bold text-white">{{ $availableEditors[$editorType]['name'] ?? 'Tiptap' }}</span>
                <span class="text-[10px] text-slate-400">▼</span>
            </button>

            <div 
                x-show="open" 
                x-on:click.outside="open = false" 
                class="absolute right-0 mt-2 w-72 rounded-2xl bg-slate-900/95 border border-white/20 p-2 shadow-2xl z-[100] space-y-1 backdrop-blur-2xl"
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

        <!-- Save Status Badge -->
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/60 border border-white/5 text-xs text-slate-400 font-mono">
            <span class="w-2 h-2 rounded-full {{ $isSaving ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' }}"></span>
            <span>{{ $saveStatusText }}</span>
        </div>

        <!-- Focus Mode & Panel Toggles -->
        <div class="flex items-center rounded-xl bg-slate-900 border border-white/10 p-0.5">
            <button 
                type="button" 
                x-on:click="showLeftPanel = !showLeftPanel" 
                :class="showLeftPanel ? 'bg-indigo-600/30 text-indigo-300' : 'text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg text-xs font-mono transition-colors" 
                title="Toggle AI Command Center (Ctrl+K)"
            >
                ◧ AI Center
            </button>
            <button 
                type="button" 
                x-on:click="toggleFocusMode()" 
                :class="(!showLeftPanel && !showRightPanel) ? 'bg-purple-600/40 text-purple-300' : 'text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg text-xs font-mono transition-colors" 
                title="Zen Focus Mode (Ctrl+Shift+F)"
            >
                ⛶ Zen Focus
            </button>
            <button 
                type="button" 
                x-on:click="showRightPanel = !showRightPanel" 
                :class="showRightPanel ? 'bg-indigo-600/30 text-indigo-300' : 'text-slate-400 hover:text-white'"
                class="p-1.5 rounded-lg text-xs font-mono transition-colors" 
                title="Toggle Content Intelligence"
            >
                ◨ Intel
            </button>
        </div>

        <!-- Share & Public Link Button -->
        <button 
            type="button" 
            wire:click="openShareModal" 
            class="px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 {{ $isShareActive ? 'bg-indigo-600/20 border-indigo-500/50 text-indigo-300 shadow-md shadow-indigo-500/10' : 'bg-slate-900 border-white/10 hover:border-indigo-500/40 text-slate-300 hover:text-white' }}"
            title="Share document publicly"
        >
            <span>🔗 Share</span>
            @if($isShareActive)
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
            @endif
        </button>

        <!-- Export Menu Dropdown -->
        <div class="relative z-50" x-data="{ open: false }">
            <button 
                x-on:click="open = !open" 
                type="button" 
                class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 flex items-center gap-2 cursor-pointer transition-all"
            >
                <span>Export</span>
                <span class="text-[10px]">▼</span>
            </button>

            <div 
                x-show="open" 
                x-on:click.outside="open = false" 
                class="absolute right-0 mt-2 w-52 rounded-2xl bg-slate-900/95 border border-white/20 p-2 shadow-2xl z-[100] space-y-1 backdrop-blur-2xl"
                style="display: none;"
            >
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

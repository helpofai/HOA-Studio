{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas & Overlays Partial
|--------------------------------------------------------------------------
|
| Features:
| 1. High-Performance Prose Canvas Container (wire:ignore Protected)
| 2. Direct Token Streaming Inside TipTap ProseMirror Canvas
| 3. In-Canvas AI Floating Prompt Bar (Cmd+K / Slash / / Command)
| 4. Local Draft Auto-Recovery Ambient Banner
| 5. Direct In-Canvas AI Generation Active Telemetry Stream Bar
| 6. Floating Selection AI Assistant Bar
| 7. Custom Right-Click Context Menu
| 8. Interactive Slash Commands Palette
|
*/
--}}

<div 
    class="glass-elevated rounded-2xl text-slate-100 transition-all duration-200 p-6 sm:p-10 min-h-[650px] border border-white/15 shadow-2xl relative"
    @contextmenu.prevent="openContextMenu($event)"
>
    <!-- Collapsible In-Canvas Master Formatting Ribbon -->
    @include('editor.partial.formatting-ribbon')

    <!-- In-Canvas Floating AI Prompt Bar (Cmd+K / / / Slash command) -->
    <div 
        x-show="showInlineAiPrompt"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="mb-6 p-4 rounded-3xl bg-slate-900/95 border-2 border-indigo-500/60 shadow-2xl backdrop-blur-2xl space-y-3 animate-in"
        style="display: none;"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse"></span>
                <span class="text-xs font-bold text-white flex items-center gap-1">
                    <span>✦ In-Canvas AI Assistant</span>
                    <span class="text-[10px] text-indigo-300 font-mono" x-text="'(' + aiModel + ')'"></span>
                </span>
            </div>
            <button type="button" x-on:click="showInlineAiPrompt = false" class="text-slate-400 hover:text-white text-xs cursor-pointer">✕ Esc</button>
        </div>

        <div class="flex items-center gap-2">
            <input 
                id="inline-ai-input"
                type="text" 
                x-model="inlineAiPrompt" 
                x-on:keydown.enter="submitInlineAiPrompt()"
                placeholder="Instruct AI: e.g. Write an in-depth review with technical specs, pros/cons, and benchmarks..."
                class="flex-1 bg-slate-950/90 border border-white/15 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-sans shadow-inner"
            />
            <button 
                type="button" 
                x-on:click="submitInlineAiPrompt()" 
                :disabled="isTransforming || !inlineAiPrompt.trim()"
                class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-1.5 transition-all disabled:opacity-50 cursor-pointer"
            >
                <span x-show="!isTransforming">✦ Generate</span>
                <span x-show="isTransforming" class="animate-spin text-xs">⟳</span>
                <span x-show="isTransforming">Writing...</span>
            </button>
        </div>

        <!-- Quick Prompt Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-[10.5px]">
            <span class="text-slate-400 font-mono text-[10px]">Shortcuts:</span>
            <button type="button" x-on:click="inlineAiPrompt = 'Write a comprehensive introductory section with a quick answer box'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">✨ Intro + Quick Answer</button>
            <button type="button" x-on:click="inlineAiPrompt = 'Create a high-impact technical comparison table with pros and cons'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">📊 Comparison Table</button>
            <button type="button" x-on:click="inlineAiPrompt = 'Generate 4 high-value schema FAQ questions and authoritative answers'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">❓ FAQ Block</button>
            <button type="button" x-on:click="inlineAiPrompt = 'Write an E-E-A-T testing methodology block with author credibility'; submitInlineAiPrompt();" class="px-2 py-0.5 rounded-lg bg-white/5 hover:bg-indigo-600/30 text-slate-300 hover:text-indigo-200 border border-white/5 transition-colors cursor-pointer">🏆 E-E-A-T Trust Box</button>
        </div>
    </div>

    <!-- Local Draft Auto-Recovery Ambient Banner -->
    <div 
        x-show="showRestoredDraftBanner" 
        x-cloak
        x-transition
        class="mb-4 p-3 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 shadow-xl backdrop-blur-xl flex flex-wrap items-center justify-between gap-3 text-xs font-sans animate-in"
        style="display: none;"
    >
        <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-white font-medium">
                <strong class="text-emerald-300">✦ Unsaved Draft Auto-Restored:</strong>
                <span class="text-slate-300" x-text="'Recovered ' + restoredWordCount + ' words from local backup (' + restoredDraftTime + ')'"></span>
            </span>
        </div>

        <div class="flex items-center gap-2 font-mono text-[11px]">
            <button type="button" x-on:click="dismissRestoredBanner()" class="px-2.5 py-1 rounded-xl bg-emerald-600/30 hover:bg-emerald-600 text-emerald-200 hover:text-white font-bold transition-colors cursor-pointer">
                ✓ Keep & Sync
            </button>
            <button type="button" x-on:click="revertToServerBackup()" class="px-2.5 py-1 rounded-xl bg-slate-900 border border-white/10 text-slate-400 hover:text-red-400 transition-colors cursor-pointer">
                Revert to Server
            </button>
        </div>
    </div>

    <!-- Direct In-Canvas AI Generation Active Telemetry Stream Bar -->
    <div 
        x-show="isTransforming"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="mb-4 px-4 py-2 rounded-2xl bg-indigo-950/80 border border-indigo-500/50 shadow-xl backdrop-blur-xl flex items-center justify-between gap-3 text-xs animate-in"
        style="display: none;"
    >
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
            </span>
            <span class="font-bold text-white flex items-center gap-1.5">
                <span>✦ AI Typing Live in Editor Canvas...</span>
                <span class="text-[10px] font-mono text-indigo-300 px-2 py-0.5 rounded-md bg-indigo-950/80 border border-indigo-500/30" x-text="routedModel"></span>
            </span>
        </div>

        <div class="flex items-center gap-3 font-mono text-xs">
            <span class="text-indigo-300 font-bold" x-text="streamSpeedTokSec + ' tok/s'"></span>
            <span class="text-slate-500">&bull;</span>
            <span class="text-slate-300" x-text="receivedTokens + ' tok'"></span>
            <button 
                type="button" 
                x-on:click="abortAiTransform()" 
                class="px-2.5 py-1 rounded-xl bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white font-bold text-xs transition-colors flex items-center gap-1 cursor-pointer"
            >
                <span>■</span> <span>Stop (Esc)</span>
            </button>
        </div>
    </div>

    <!-- Advanced TipTap Floating Selection Bubble Toolbar (Tippy Portal z-[999999]) -->
    <div 
        id="tiptap-bubble-menu" 
        x-on:mousedown.prevent
        class="bg-slate-950/98 border border-indigo-500/60 rounded-2xl shadow-[0_25px_60px_rgba(0,0,0,0.9)] backdrop-blur-2xl p-1.5 flex flex-wrap items-center gap-1 text-xs z-[999999]"
        style="display: none;"
    >
        <!-- AI Actions Dropdown Menu -->
        <div class="relative" x-data="{ bubbleAiOpen: false }">
            <button 
                type="button" 
                x-on:mousedown.prevent
                x-on:click="bubbleAiOpen = !bubbleAiOpen" 
                class="px-2.5 py-1 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/30 cursor-pointer"
            >
                <span>✦ Ask AI</span>
                <span class="text-[9px]">▼</span>
            </button>
            <div 
                x-show="bubbleAiOpen" 
                x-on:click.outside="bubbleAiOpen = false" 
                x-on:mousedown.prevent
                class="absolute left-0 mt-2 w-56 rounded-2xl bg-slate-900/98 border border-white/20 p-1.5 shadow-2xl z-50 space-y-0.5 backdrop-blur-2xl text-xs"
                style="display: none;"
            >
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('polish'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-cyan-400">✧</span> <span>Polish & Refine</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('expand'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-violet-400">+</span> <span>Expand with Depth</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('shorten'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-amber-400">−</span> <span>Shorten & Condense</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('rewrite'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-emerald-400">↻</span> <span>Rewrite Phrasing</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('simplify'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-pink-400">⚡</span> <span>Simplify (8th-Grade)</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('generate_faq'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-indigo-400">❓</span> <span>Generate FAQ on this</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('key_takeaways'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                    <span class="text-teal-400">💡</span> <span>Extract Key Takeaways</span>
                </button>
            </div>
        </div>

        <span class="w-[1px] h-4 bg-white/10 mx-0.5"></span>

        <!-- Inline Formatting Marks (Bold, Italic, Underline, Strike, Highlight, Code) -->
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('bold')" :class="activeFormats.bold ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg font-bold cursor-pointer" title="Bold (Ctrl+B)">B</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('italic')" :class="activeFormats.italic ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg italic font-serif cursor-pointer" title="Italic (Ctrl+I)">I</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('underline')" :class="activeFormats.underline ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg underline cursor-pointer" title="Underline (Ctrl+U)">U</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('strike')" :class="activeFormats.strike ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg line-through cursor-pointer" title="Strike">S</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('highlight')" :class="activeFormats.highlight ? 'bg-amber-500/80 text-black font-bold shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg cursor-pointer" title="Highlight">⬚</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('codeBlock')" :class="activeFormats.codeBlock ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg font-mono text-[11px] cursor-pointer" title="Code Block">&lt;/&gt;</button>

        <span class="w-[1px] h-4 bg-white/10 mx-0.5"></span>

        <!-- Headings Hierarchy -->
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 1)" :class="activeFormats.heading1 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg font-mono font-bold cursor-pointer" title="Heading 1">H1</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 2)" :class="activeFormats.heading2 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg font-mono font-bold cursor-pointer" title="Heading 2">H2</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 3)" :class="activeFormats.heading3 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg font-mono font-bold cursor-pointer" title="Heading 3">H3</button>

        <span class="w-[1px] h-4 bg-white/10 mx-0.5"></span>

        <!-- Lists, Tasks, Tables & Quotes -->
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('bulletList')" :class="activeFormats.bulletList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg cursor-pointer" title="Bullet List">●</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('orderedList')" :class="activeFormats.orderedList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg cursor-pointer" title="Numbered List">1.</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('taskList')" :class="activeFormats.taskList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg cursor-pointer" title="Task Checklist">✓</button>
        <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('blockquote')" :class="activeFormats.blockquote ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="px-2 py-1 rounded-lg font-serif font-bold cursor-pointer" title="Blockquote">"</button>
        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertTable?.({ rows: 3, cols: 3, withHeaderRow: true })" class="px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer" title="Insert Table">▦</button>

        <span class="w-[1px] h-4 bg-white/10 mx-0.5"></span>

        <!-- Text Alignments -->
        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('left')" class="px-1.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer" title="Align Left">⇤</button>
        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('center')" class="px-1.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer" title="Align Center">↔</button>
        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('right')" class="px-1.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer" title="Align Right">⇥</button>
    </div>

    <!-- Custom Right-Click Context Menu -->
    <div 
        x-show="showContextMenu"
        x-cloak
        x-transition
        :style="`position: fixed; left: ${contextMenuX}px; top: ${contextMenuY}px; z-index: 99999;`"
        class="z-50 bg-slate-900/98 border border-indigo-500/40 rounded-2xl shadow-2xl backdrop-blur-2xl p-1.5 min-w-[220px] text-xs font-sans space-y-0.5 animate-in zoom-in-95"
        style="display: none;"
        x-on:click.outside="closeContextMenu()"
    >
        <div class="px-2.5 py-1.5 text-[10px] uppercase font-bold text-indigo-400 tracking-wider flex items-center justify-between border-b border-white/5 mb-1 select-none">
            <span>✦ AI Context Menu</span>
            <span class="text-slate-500 text-[9px]">Right-Click</span>
        </div>

        <button type="button" x-on:click="openInlineAiPrompt()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-indigo-300 font-bold flex items-center gap-2 cursor-pointer">
            <span>✦</span> <span>Ask AI Inline...</span>
        </button>
        <button type="button" x-on:click="triggerAiTransform('rewrite')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer">
            <span class="text-cyan-400">↻</span> <span>Rewrite & Polish</span>
        </button>
        <button type="button" x-on:click="triggerAiTransform('expand')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer">
            <span class="text-violet-400">+</span> <span>Expand this Section</span>
        </button>
        <button type="button" x-on:click="triggerAiTransform('shorten')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer">
            <span class="text-amber-400">−</span> <span>Shorten & Condense</span>
        </button>
        <button type="button" x-on:click="triggerAiTransform('generate_faq')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer">
            <span class="text-indigo-400">❓</span> <span>Generate FAQ on this</span>
        </button>
        <button type="button" x-on:click="triggerAiTransform('key_takeaways')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer">
            <span class="text-pink-400">💡</span> <span>Extract Key Takeaways</span>
        </button>
        <button type="button" x-on:click="triggerAiTransform('seo_optimize')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer">
            <span class="text-emerald-400">⌁</span> <span>SEO Optimize Text</span>
        </button>

        <!-- Change Tone Submenu Trigger -->
        <div class="relative" x-data="{ toneSubOpen: false }">
            <button type="button" x-on:click="toneSubOpen = !toneSubOpen" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center justify-between cursor-pointer">
                <span class="flex items-center gap-2"><span>🎨</span> <span>Change Tone</span></span>
                <span class="text-[10px] text-slate-400">▶</span>
            </button>
            <div x-show="toneSubOpen" class="mt-1 p-1 bg-slate-950 rounded-xl border border-white/10 space-y-0.5 text-xs">
                <button type="button" x-on:click="triggerAiTransform('tone:professional'); toneSubOpen = false" class="w-full text-left px-2 py-1 rounded hover:bg-white/10 text-slate-300 cursor-pointer">Executive & Professional</button>
                <button type="button" x-on:click="triggerAiTransform('tone:casual'); toneSubOpen = false" class="w-full text-left px-2 py-1 rounded hover:bg-white/10 text-slate-300 cursor-pointer">Warm & Conversational</button>
                <button type="button" x-on:click="triggerAiTransform('tone:persuasive'); toneSubOpen = false" class="w-full text-left px-2 py-1 rounded hover:bg-white/10 text-slate-300 cursor-pointer">High-Impact Persuasive</button>
            </div>
        </div>

        <div class="border-t border-white/5 my-1"></div>
        <button type="button" x-on:click="closeContextMenu()" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/5 text-slate-400 hover:text-slate-200 text-[11px] cursor-pointer">
            ✕ Close Menu
        </button>
    </div>

    <!-- Interactive Floating Slash Commands Palette (Triggered on '/') -->
    <div 
        x-show="showSlashMenu"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        :style="`position: fixed; left: ${slashMenuX}px; top: ${slashMenuY}px; z-index: 99999;`"
        class="fixed bg-slate-900/98 border border-indigo-500/50 rounded-2xl shadow-[0_25px_60px_rgba(0,0,0,0.85)] backdrop-blur-2xl p-2 min-w-[280px] max-h-[380px] overflow-y-auto text-xs font-sans space-y-1 scrollbar-thin scrollbar-thumb-white/10"
        x-on:click.outside="showSlashMenu = false"
        style="display: none;"
    >
        <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-indigo-400 tracking-wider flex items-center justify-between border-b border-white/10 mb-1 select-none">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                <span>✦ Slash AI Commands</span>
            </span>
            <span class="text-slate-500 text-[9px] font-mono">Type / to filter</span>
        </div>

        <!-- AI Actions Group -->
        <div class="space-y-0.5">
            <button type="button" x-on:click="executeSlashAction('ask_ai')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-indigo-300 hover:text-white font-bold flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-600/30 flex items-center justify-center text-xs">✦</span>
                <div>
                    <div>Ask AI Anything</div>
                    <div class="text-[10px] text-slate-400 font-normal">Generate custom text or prompt directly</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('continue_writing')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-violet-400">✍️</span>
                <div>
                    <div>Continue Writing</div>
                    <div class="text-[10px] text-slate-400">AI continues drafting the next thoughts</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('generate_outline')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-cyan-400">📑</span>
                <div>
                    <div>Article Outline</div>
                    <div class="text-[10px] text-slate-400">Generate structured headings tree</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('quick_answer')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-amber-400">⚡</span>
                <div>
                    <div>Quick Answer Box</div>
                    <div class="text-[10px] text-slate-400">Insert TL;DR search-intent snippet</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('faq')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-emerald-400">❓</span>
                <div>
                    <div>FAQ Schema Block</div>
                    <div class="text-[10px] text-slate-400">Generate high-intent Q&A section</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('comparison_table')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-indigo-400">📊</span>
                <div>
                    <div>Comparison Table</div>
                    <div class="text-[10px] text-slate-400">Generate structured pros/cons table</div>
                </div>
            </button>
        </div>

        <!-- Advanced Blog Components Group -->
        <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-violet-400 tracking-wider border-t border-white/10 mt-1 select-none">Editorial & Trust Blocks</div>
        <div class="space-y-0.5">
            <button type="button" x-on:click="executeSlashAction('tip')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-emerald-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>💡</span> <span>Pro-Tip Callout Box</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('warning')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-amber-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>⚠️</span> <span>Warning Precaution Box</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('proscons')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>⚖️</span> <span>Dual Pros & Cons Grid</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('faq_accordion')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>❓</span> <span>Interactive FAQ Accordion</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('trust_box')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>🏆</span> <span>E-E-A-T Testing Trust Box</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('step_timeline')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                <span>🔢</span> <span>Step-by-Step Timeline</span>
            </button>
        </div>

        <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider border-t border-white/10 mt-1 select-none">Structure & Blocks</div>

        <!-- Standard Formatting Group -->
        <div class="space-y-0.5">
            <button type="button" x-on:click="executeSlashAction('h1')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H1</span> <span>Heading 1</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('h2')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H2</span> <span>Heading 2</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('h3')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H3</span> <span>Heading 3</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('h4')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="font-bold text-indigo-400 font-mono">H4</span> <span>Heading 4</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('bullet')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">●</span> <span>Bullet List</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('number')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400 font-mono">1.</span> <span>Numbered List</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('task')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">✓</span> <span>Task / Checklist</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('quote')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">"</span> <span>Blockquote</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('table')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400 font-mono">▦</span> <span>3x3 Data Table</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('code')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400 font-mono">&lt;/&gt;</span> <span>Code Block</span>
            </button>
            <button type="button" x-on:click="executeSlashAction('divider')" class="w-full text-left px-2.5 py-1 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer">
                <span class="text-slate-400">—</span> <span>Horizontal Divider</span>
            </button>
        </div>
    </div>

    <!-- Active Editor Engine Canvas Mount Target (wire:ignore for zero-latency, error-free typing & high-capacity 10,000+ words scrollbar) -->
    <div id="tiptap-content-target" class="min-h-[550px] max-h-[820px] overflow-y-auto hoa-custom-scrollbar pr-3 scroll-smooth" wire:ignore></div>
</div>

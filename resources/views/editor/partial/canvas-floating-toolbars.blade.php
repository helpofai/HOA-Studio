{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas Floating Toolbars
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

    <!-- Advanced TipTap Floating Selection Bubble Toolbar (Teleported to body to avoid backdrop-filter coordinate displacement) -->
    <template x-teleport="body">
        <div
            id="tiptap-bubble-menu"
            x-ref="bubbleMenu"
            x-on:mousedown.prevent
            class="max-w-[calc(100vw-24px)] rounded-2xl bg-slate-950/98 border border-white/20 shadow-[0_20px_50px_rgba(0,0,0,0.95)] backdrop-blur-2xl p-1.5 flex flex-wrap items-center gap-1.5 text-xs select-none transition-all duration-150"
            style="display: none; z-index: var(--z-index-floating);"
        >
            <!-- 1. AI Actions Group -->
            <div class="flex items-center gap-1 bg-white/[0.04] p-0.5 rounded-xl border border-white/5" x-data="{ bubbleAiOpen: false }">
                <button
                    type="button"
                    x-on:mousedown.prevent
                    x-on:click="bubbleAiOpen = !bubbleAiOpen"
                    class="px-2.5 py-1 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold flex items-center gap-1.5 shadow-md shadow-indigo-600/30 cursor-pointer text-xs transition-transform active:scale-95"
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
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('recreate'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl bg-purple-950/80 hover:bg-purple-900/80 text-purple-200 hover:text-white flex items-center justify-between cursor-pointer font-bold border border-purple-500/40">
                        <span class="flex items-center gap-2"><span class="text-purple-400">🤖</span> <span>Recreate Paragraph</span></span>
                        <span class="text-[9px] font-mono px-1 rounded bg-purple-900 text-purple-300">AI</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('rewrite'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-cyan-400">↻</span> <span>Rewrite & Polish</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('expand'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-violet-400">+</span> <span>Expand with Depth</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('shorten'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-amber-400">−</span> <span>Shorten & Condense</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('simplify'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-pink-400">⚡</span> <span>Simplify (8th-Grade)</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('generate_faq'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-indigo-400">❓</span> <span>Generate FAQ on this</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerAiTransform('key_takeaways'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-teal-400">💡</span> <span>Extract Key Takeaways</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('inject_data_points'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-emerald-400">📊</span> <span>Inject Data & Benchmarks</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('add_code_snippet'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-indigo-400">💻</span> <span>Generate Code Snippet</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('inject_counter_arguments'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-amber-400">⚖️</span> <span>Add Trade-offs & Nuance</span>
                    </button>
                    <button type="button" x-on:mousedown.prevent x-on:click="triggerSubContentSubAgent('surgical_micro_repair'); bubbleAiOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-white flex items-center gap-2 cursor-pointer">
                        <span class="text-cyan-400">🔬</span> <span>Surgical Micro-Repair</span>
                    </button>
                </div>
            </div>

            <!-- 2. Inline Typography Group (Bold, Italic, Underline, Strike, Highlight, Code) -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('bold')" :class="activeFormats.bold ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-bold cursor-pointer transition-colors" title="Bold (Ctrl+B)">B</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('italic')" :class="activeFormats.italic ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg italic font-serif cursor-pointer transition-colors" title="Italic (Ctrl+I)">I</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('underline')" :class="activeFormats.underline ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg underline cursor-pointer transition-colors" title="Underline (Ctrl+U)">U</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('strike')" :class="activeFormats.strike ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg line-through cursor-pointer transition-colors" title="Strikethrough">S</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('highlight')" :class="activeFormats.highlight ? 'bg-amber-500/80 text-black font-bold shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors" title="Highlight">⬚</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('codeBlock')" :class="activeFormats.codeBlock ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono text-[11px] cursor-pointer transition-colors" title="Code Block">&lt;/&gt;</button>
            </div>

            <!-- 3. Headings & Blockquote Group -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 1)" :class="activeFormats.heading1 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono font-bold cursor-pointer transition-colors" title="Heading 1">H1</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 2)" :class="activeFormats.heading2 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono font-bold cursor-pointer transition-colors" title="Heading 2">H2</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('heading', 3)" :class="activeFormats.heading3 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-mono font-bold cursor-pointer transition-colors" title="Heading 3">H3</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('blockquote')" :class="activeFormats.blockquote ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg font-serif font-bold cursor-pointer transition-colors" title="Blockquote">"</button>
            </div>

            <!-- 4. Lists & Table Group -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('bulletList')" :class="activeFormats.bulletList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors" title="Bullet List">●</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('orderedList')" :class="activeFormats.orderedList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors text-[11px] font-bold" title="Numbered List">1.</button>
                <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('taskList')" :class="activeFormats.taskList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" class="w-7 h-7 flex items-center justify-center rounded-lg cursor-pointer transition-colors text-[11px]" title="Task Checklist">✓</button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertTable?.({ rows: 3, cols: 3, withHeaderRow: true })" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer transition-colors" title="Insert Table">▦</button>
            </div>

            <!-- 5. Alignment Group -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('left')" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer transition-colors" title="Align Left">⇤</button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('center')" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer transition-colors" title="Align Center">↔</button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('right')" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:bg-white/10 cursor-pointer transition-colors" title="Align Right">⇥</button>
            </div>
        </div>
    </template>

    <!-- Floating Table Operations Toolbar (Teleported to body, reactive to activeFormats.table) -->
    <template x-teleport="body">
        <div
            id="tiptap-table-toolbar"
            x-show="activeFormats.table"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="hoa-table-floating-bar fixed z-[9990] flex items-center gap-1 p-1 bg-slate-950/98 border border-indigo-500/30 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.9)] backdrop-blur-2xl text-xs select-none"
            style="display: none;"
        >
            <!-- Table Indicator Badge -->
            <div class="flex items-center gap-1 px-2 py-1 bg-indigo-600/20 text-indigo-300 font-mono font-bold text-[10px] rounded-xl border border-indigo-500/30 select-none">
                <span>▦</span>
                <span>Table</span>
            </div>

            <!-- Row Operations -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.addRowBefore?.()" class="px-2 py-1 flex items-center gap-1 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white cursor-pointer transition-colors text-[11px]" title="Insert Row Above">
                    <span>↑</span> <span>Row</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.addRowAfter?.()" class="px-2 py-1 flex items-center gap-1 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white cursor-pointer transition-colors text-[11px]" title="Insert Row Below">
                    <span>↓</span> <span>Row</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.deleteRow?.()" class="px-1.5 py-1 flex items-center gap-0.5 rounded-lg text-rose-400 hover:bg-rose-500/20 hover:text-rose-300 cursor-pointer transition-colors text-[11px]" title="Delete Current Row">
                    <span>✕</span> <span>Row</span>
                </button>
            </div>

            <!-- Column Operations -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.addColumnBefore?.()" class="px-2 py-1 flex items-center gap-1 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white cursor-pointer transition-colors text-[11px]" title="Insert Column Left">
                    <span>←</span> <span>Col</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.addColumnAfter?.()" class="px-2 py-1 flex items-center gap-1 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white cursor-pointer transition-colors text-[11px]" title="Insert Column Right">
                    <span>→</span> <span>Col</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.deleteColumn?.()" class="px-1.5 py-1 flex items-center gap-0.5 rounded-lg text-rose-400 hover:bg-rose-500/20 hover:text-rose-300 cursor-pointer transition-colors text-[11px]" title="Delete Current Column">
                    <span>✕</span> <span>Col</span>
                </button>
            </div>

            <!-- Cell / Header Operations -->
            <div class="flex items-center gap-0.5 bg-white/[0.04] p-0.5 rounded-xl border border-white/5">
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.toggleHeaderRow?.()" class="px-2 py-1 flex items-center gap-1 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white cursor-pointer transition-colors text-[11px]" title="Toggle Header Row">
                    <span>🔲</span> <span>Header</span>
                </button>
                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.mergeOrSplit?.()" class="px-2 py-1 flex items-center gap-1 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white cursor-pointer transition-colors text-[11px]" title="Merge or Split Selected Cells">
                    <span>🔗</span> <span>Merge/Split</span>
                </button>
            </div>

            <!-- Delete Entire Table -->
            <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.deleteTable?.()" class="px-2 py-1 flex items-center gap-1 rounded-xl text-rose-400 hover:bg-rose-500/20 hover:text-rose-300 cursor-pointer transition-colors text-[11px] border border-rose-500/20" title="Delete Table">
                <span>🗑️</span>
            </button>
        </div>
    </template>


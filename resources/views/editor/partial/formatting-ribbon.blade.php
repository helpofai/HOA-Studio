{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Collapsible Formatting Ribbon
|--------------------------------------------------------------------------
|
| Features:
| 1. Seamlessly Integrated Inside Editor Canvas Card (.glass-elevated)
| 2. Expand & Collapse feature with smooth transitions (saves vertical canvas space)
| 3. Complete TipTap Element & Formatting Suite:
|    - Headings: H1, H2, H3, H4
|    - Marks: Bold, Italic, Underline, Strike, Highlight, Subscript, Superscript
|    - Lists: Bullet List, Numbered List, Task Checklist
|    - Tables: 3x3 Data & Comparison Tables with cell manipulation
|    - Quotes & Callouts: Blockquote, Tip (💡), Warning (⚠️), Info (ℹ️), TL;DR (⚡)
|    - Interactive Blocks: Pros/Cons (⚖️), FAQ Accordions (❓), Trust Box (🏆), Step Timeline (🔢)
|    - Code & Media: Code Block, Image URL, Horizontal Divider
|    - Controls: Align (Left, Center, Right, Justify), Clear Formatting (🧹), Undo / Redo
| 4. Selection-Preserved MouseDown Prevention (Never loses editor selection on click)
|
*/
--}}

<div 
    x-data="{ isRibbonExpanded: true }"
    class="mb-4 rounded-2xl bg-slate-950/90 border border-white/12 shadow-xl backdrop-blur-2xl transition-all duration-200 sticky top-0 z-30 ring-1 ring-white/5"
    x-on:mousedown.prevent
>
    <!-- Collapsible Header / Minimal Bar -->
    <div class="p-2 sm:p-2.5 flex flex-wrap sm:flex-nowrap items-center justify-between gap-2">
        <div class="flex items-center gap-1.5 flex-wrap min-w-0">
            <!-- Inline AI Trigger Button -->
            <button 
                type="button" 
                x-on:mousedown.prevent
                x-on:click="openInlineAiPrompt()" 
                class="shrink-0 px-2.5 py-1 rounded-xl bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold flex items-center gap-1 border border-indigo-500/30 transition-all shadow-sm cursor-pointer text-xs active:scale-95"
                title="Open in-canvas AI Prompt Bar (Ctrl+K or /)"
            >
                <span class="text-xs">✦</span>
                <span>Ask AI</span>
                <span class="text-[9px] font-mono text-indigo-400 bg-indigo-950/80 px-1 py-0.2 rounded">/</span>
            </button>

            <!-- Quick Indicator when Collapsed -->
            <div x-show="!isRibbonExpanded" x-cloak class="flex items-center gap-2 text-xs text-slate-400 ml-1">
                <span class="text-slate-500 text-[11px] font-medium hidden xs:inline">Formatting Collapsed</span>
            </div>
        </div>

        <!-- Right Side: Metrics & Expand/Collapse Toggle Button -->
        <div class="flex items-center gap-2 shrink-0 ml-auto sm:ml-0">
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-mono select-none mr-1">
                <span><strong class="text-white font-bold" x-text="wordCount">0</strong> <span class="hidden xs:inline">words</span></span>
                <span>&bull;</span>
                <span><strong class="text-indigo-300 font-bold" x-text="readingTime + 'm'">1m</strong> <span class="hidden xs:inline">read</span></span>
            </div>

            <!-- Expand / Collapse Toggle Button -->
            <button 
                type="button" 
                x-on:mousedown.prevent
                x-on:click="isRibbonExpanded = !isRibbonExpanded" 
                class="px-2.5 py-1 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 text-xs font-mono flex items-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95"
                :title="isRibbonExpanded ? 'Collapse Formatting Toolbar' : 'Expand Formatting Toolbar'"
            >
                <span class="text-[10px]" x-text="isRibbonExpanded ? '▲' : '▼'">▲</span>
                <span class="hidden sm:inline" x-text="isRibbonExpanded ? 'Collapse' : 'Format'">Collapse</span>
            </button>
        </div>
    </div>

    <!-- Expanded Rich Formatting Suite Controls -->
    <div 
        x-show="isRibbonExpanded"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-white/5 p-2 sm:p-2.5 pt-2 flex flex-wrap items-center justify-between gap-2"
    >
        <!-- Formatting Buttons Flexible Multi-Group Wrap Container -->
        <div class="w-full max-w-full flex flex-wrap items-center gap-1 sm:gap-1.5 text-xs overflow-hidden">
            <!-- Rich-text controls -->
            <template x-if="caps.richText">
                <div class="w-full max-w-full flex flex-wrap items-center gap-1">
                    <!-- Group 1: Headings (H1, H2, H3, H4) -->
                    <div class="flex flex-wrap items-center gap-0.5 p-0.5 rounded-lg bg-slate-900/80 border border-white/5 shrink-0">
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('heading', 1)" 
                            :class="activeFormats.heading1 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-1.5 sm:px-2 py-1 rounded-md font-bold transition-all cursor-pointer font-mono text-[11px] sm:text-xs" 
                            title="Heading 1"
                        >H1</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('heading', 2)" 
                            :class="activeFormats.heading2 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-1.5 sm:px-2 py-1 rounded-md font-bold transition-all cursor-pointer font-mono text-[11px] sm:text-xs" 
                            title="Heading 2"
                        >H2</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('heading', 3)" 
                            :class="activeFormats.heading3 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-1.5 sm:px-2 py-1 rounded-md font-bold transition-all cursor-pointer font-mono text-[11px] sm:text-xs" 
                            title="Heading 3"
                        >H3</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('heading', 4)" 
                            :class="activeFormats.heading4 ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-1.5 sm:px-2 py-1 rounded-md font-bold transition-all cursor-pointer font-mono text-[10px] sm:text-[11px]" 
                            title="Heading 4"
                        >H4</button>
                    </div>

                    <!-- Group 2: Marks (Bold, Italic, Underline, Strike, Sub, Sup, Highlight) -->
                    <div class="flex items-center gap-0.5 p-0.5 rounded-lg bg-slate-900/80 border border-white/5">
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('bold')" 
                            :class="activeFormats.bold ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md font-bold transition-all cursor-pointer" 
                            title="Bold (Ctrl+B)"
                        >B</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('italic')" 
                            :class="activeFormats.italic ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md italic transition-all cursor-pointer font-serif" 
                            title="Italic (Ctrl+I)"
                        >I</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('underline')" 
                            :class="activeFormats.underline ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md underline transition-all cursor-pointer" 
                            title="Underline (Ctrl+U)"
                        >U</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('strike')" 
                            :class="activeFormats.strike ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md line-through transition-all cursor-pointer" 
                            title="Strikethrough"
                        >S</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('subscript')" 
                            :class="activeFormats.subscript ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-1.5 py-1 rounded-md text-xs font-mono transition-all cursor-pointer" 
                            title="Subscript (X₂)"
                        >X₂</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('superscript')" 
                            :class="activeFormats.superscript ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-1.5 py-1 rounded-md text-xs font-mono transition-all cursor-pointer" 
                            title="Superscript (X²)"
                        >X²</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('highlight')" 
                            :class="activeFormats.highlight ? 'bg-amber-500/80 text-black font-bold shadow-md shadow-amber-500/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md transition-all cursor-pointer" 
                            title="Highlight Text"
                        >⬚</button>
                    </div>

                    <!-- Group 3: Lists & Alignments -->
                    <div class="flex items-center gap-0.5 p-0.5 rounded-lg bg-slate-900/80 border border-white/5">
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('bulletList')" 
                            :class="activeFormats.bulletList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md transition-all cursor-pointer" 
                            title="Bullet List"
                        >● List</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('orderedList')" 
                            :class="activeFormats.orderedList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md transition-all cursor-pointer" 
                            title="Numbered List"
                        >1. List</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('taskList')" 
                            :class="activeFormats.taskList ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md transition-all cursor-pointer" 
                            title="Interactive Task Checklist"
                        >✓ Task</button>

                        <span class="w-[1px] h-3.5 bg-white/10 mx-0.5"></span>

                        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('left')" class="px-1.5 py-1 rounded-md text-slate-300 hover:bg-white/10 cursor-pointer" title="Align Left">⇤</button>
                        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('center')" class="px-1.5 py-1 rounded-md text-slate-300 hover:bg-white/10 cursor-pointer" title="Align Center">↔</button>
                        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('right')" class="px-1.5 py-1 rounded-md text-slate-300 hover:bg-white/10 cursor-pointer" title="Align Right">⇥</button>
                        <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.setTextAlign?.('justify')" class="px-1.5 py-1 rounded-md text-slate-300 hover:bg-white/10 cursor-pointer" title="Justify">⇿</button>
                    </div>

                    <!-- Group 4: Advanced Elements (Table, Quote, Code, Callouts, Blocks) -->
                    <div class="flex items-center gap-0.5 p-0.5 rounded-lg bg-slate-900/80 border border-white/5">
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="editorInstance?.insertTable?.({ rows: 3, cols: 3, withHeaderRow: true })" 
                            :class="activeFormats.table ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md transition-all cursor-pointer" 
                            title="Insert 3x3 Table"
                        >▦ Table</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('blockquote')" 
                            :class="activeFormats.blockquote ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md font-serif transition-all cursor-pointer font-bold" 
                            title="Blockquote"
                        >"</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('codeBlock')" 
                            :class="activeFormats.codeBlock ? 'bg-indigo-600 text-white font-bold shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-white/10'" 
                            class="px-2 py-1 rounded-md font-mono text-[11px] transition-all cursor-pointer" 
                            title="Syntax Code Block"
                        >&lt;/&gt;</button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="applyFormat('hr')" 
                            class="px-2 py-1 rounded-md text-slate-300 hover:bg-white/10 text-xs transition-all cursor-pointer font-mono" 
                            title="Horizontal Divider"
                        >—</button>

                        <span class="w-[1px] h-3.5 bg-white/10 mx-0.5"></span>

                        <!-- Callouts Dropdown -->
                        <div class="relative" x-data="{ calloutOpen: false }">
                            <button 
                                type="button" 
                                x-on:mousedown.prevent
                                x-on:click="calloutOpen = !calloutOpen" 
                                class="px-2 py-1 rounded-md bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs flex items-center gap-1 cursor-pointer transition-colors"
                                title="Insert Callout Boxes"
                            >
                                <span>💡 Callouts</span>
                                <span class="text-[9px]">▼</span>
                            </button>
                            <div 
                                x-show="calloutOpen" 
                                x-on:click.outside="calloutOpen = false" 
                                x-on:mousedown.prevent
                                class="absolute left-0 mt-2 w-48 rounded-2xl bg-slate-900/98 border border-white/20 p-1.5 shadow-2xl z-50 space-y-1 backdrop-blur-2xl text-xs"
                                style="display: none;"
                            >
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertCallout?.('tip'); calloutOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-emerald-300 flex items-center gap-2 cursor-pointer">
                                    <span>💡</span> <span>Pro Tip Box</span>
                                </button>
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertCallout?.('warning'); calloutOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-amber-300 flex items-center gap-2 cursor-pointer">
                                    <span>⚠️</span> <span>Warning Box</span>
                                </button>
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertCallout?.('info'); calloutOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-cyan-300 flex items-center gap-2 cursor-pointer">
                                    <span>ℹ️</span> <span>Info Note Box</span>
                                </button>
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertCallout?.('tldr'); calloutOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-purple-300 flex items-center gap-2 cursor-pointer">
                                    <span>⚡</span> <span>Key Takeaways (TL;DR)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Interactive Blocks Dropdown -->
                        <div class="relative" x-data="{ blockOpen: false }">
                            <button 
                                type="button" 
                                x-on:mousedown.prevent
                                x-on:click="blockOpen = !blockOpen" 
                                class="px-2 py-1 rounded-md bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs flex items-center gap-1 cursor-pointer transition-colors"
                                title="Insert Interactive Editorial Blocks"
                            >
                                <span>🧩 Blocks</span>
                                <span class="text-[9px]">▼</span>
                            </button>
                            <div 
                                x-show="blockOpen" 
                                x-on:click.outside="blockOpen = false" 
                                x-on:mousedown.prevent
                                class="absolute left-0 mt-2 w-52 rounded-2xl bg-slate-900/98 border border-white/20 p-1.5 shadow-2xl z-50 space-y-1 backdrop-blur-2xl text-xs"
                                style="display: none;"
                            >
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertProsCons?.(); blockOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-slate-200 flex items-center gap-2 cursor-pointer">
                                    <span>⚖️</span> <span>Dual Pros & Cons</span>
                                </button>
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertFaqAccordion?.(); blockOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-slate-200 flex items-center gap-2 cursor-pointer">
                                    <span>❓</span> <span>FAQ Accordion</span>
                                </button>
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertTrustBox?.(); blockOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-slate-200 flex items-center gap-2 cursor-pointer">
                                    <span>🏆</span> <span>E-E-A-T Trust Box</span>
                                </button>
                                <button type="button" x-on:mousedown.prevent x-on:click="editorInstance?.insertStepTimeline?.(); blockOpen = false" class="w-full text-left p-2 rounded-xl hover:bg-white/10 text-slate-200 flex items-center gap-2 cursor-pointer">
                                    <span>🔢</span> <span>Step Timeline</span>
                                </button>
                            </div>
                        </div>

                        <!-- Image & Clear Formatting -->
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="insertImageFromUrl()" 
                            class="px-2 py-1 rounded-md text-slate-300 hover:bg-white/10 text-xs transition-all cursor-pointer flex items-center gap-1" 
                            title="Insert Image by URL"
                        >
                            <span>🖼</span>
                        </button>
                        <button 
                            type="button" 
                            x-on:mousedown.prevent
                            x-on:click="editorInstance?.clearFormatting?.()" 
                            class="px-2 py-1 rounded-md text-slate-400 hover:text-red-300 hover:bg-red-600/10 text-xs transition-all cursor-pointer" 
                            title="Clear Formatting (Tx)"
                        >
                            Tx
                        </button>
                    </div>

                    <!-- Undo / Redo -->
                    <template x-if="caps.undoRedo">
                        <div class="flex items-center gap-0.5 p-0.5 rounded-lg bg-slate-900/80 border border-white/5 ml-auto">
                            <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('undo')" class="px-2 py-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 cursor-pointer" title="Undo (Ctrl+Z)">↶</button>
                            <button type="button" x-on:mousedown.prevent x-on:click="applyFormat('redo')" class="px-2 py-1 rounded-md text-slate-400 hover:text-white hover:bg-white/10 cursor-pointer" title="Redo (Ctrl+Y)">↷</button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

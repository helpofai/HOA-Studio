{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Canvas Context Menus & Slash Palette
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

    <!-- Custom Right-Click Context Menu (Teleported to body to avoid backdrop-filter coordinate displacement) -->
    <template x-teleport="body">
        <div
            id="hoa-editor-context-menu"
            x-show="showContextMenu"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            :style="showContextMenu ? `position: fixed; left: ${contextMenuX}px; top: ${contextMenuY}px; z-index: var(--z-index-overlay);` : 'display: none !important;'"
            class="editor-floating-panel w-64 p-1.5 rounded-2xl bg-slate-950/98 border border-white/20 shadow-[0_25px_60px_rgba(0,0,0,0.95)] backdrop-blur-2xl text-xs select-none space-y-1"
            x-on:click.outside="closeContextMenu()"
            x-on:contextmenu.prevent
            style="display: none;"
        >
            <!-- Menu Header -->
            <div class="px-2.5 py-1 text-[10px] uppercase font-bold text-indigo-400 tracking-wider flex items-center justify-between border-b border-white/10 mb-1 select-none">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                    <span>✦ AI & Editor Context</span>
                </span>
                <span class="text-slate-500 text-[9px] font-mono">Menu</span>
            </div>

            <!-- SECTION 0: TABLE CONTROLS (Only visible when right-clicking inside a table cell) -->
            <template x-if="isTableContext">
                <div class="space-y-0.5 pb-1 border-b border-white/10 mb-1">
                    <div class="px-2 py-0.5 text-[9px] font-mono text-cyan-400 font-bold flex items-center justify-between">
                        <span>▦ TABLE CONTROLS</span>
                        <span class="text-[8px] text-slate-500">GRID</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1 px-1">
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.addRowBefore?.()" class="text-left px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-200 text-[11px] flex items-center gap-1.5 cursor-pointer">
                            <span>↑</span> <span>Row Above</span>
                        </button>
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.addRowAfter?.()" class="text-left px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-200 text-[11px] flex items-center gap-1.5 cursor-pointer">
                            <span>↓</span> <span>Row Below</span>
                        </button>
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.addColumnBefore?.()" class="text-left px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-200 text-[11px] flex items-center gap-1.5 cursor-pointer">
                            <span>←</span> <span>Col Left</span>
                        </button>
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.addColumnAfter?.()" class="text-left px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-200 text-[11px] flex items-center gap-1.5 cursor-pointer">
                            <span>→</span> <span>Col Right</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-1 px-1 pt-0.5">
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.deleteRow?.()" class="text-left px-2 py-1 rounded-lg hover:bg-red-500/20 text-red-300 text-[11px] flex items-center gap-1 cursor-pointer">
                            <span>✕</span> <span>Delete Row</span>
                        </button>
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.deleteColumn?.()" class="text-left px-2 py-1 rounded-lg hover:bg-red-500/20 text-red-300 text-[11px] flex items-center gap-1 cursor-pointer">
                            <span>✕</span> <span>Delete Col</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-1 px-1 pt-0.5">
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.toggleHeaderRow?.()" class="flex-1 text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 text-[11px] flex items-center gap-1 cursor-pointer">
                            <span>🔲</span> <span>Toggle Header</span>
                        </button>
                        <button type="button" x-on:click="closeContextMenu(); editorInstance?.mergeOrSplit?.()" class="flex-1 text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 text-[11px] flex items-center gap-1 cursor-pointer">
                            <span>🔗</span> <span>Merge/Split</span>
                        </button>
                    </div>
                    <button type="button" x-on:click="closeContextMenu(); editorInstance?.deleteTable?.()" class="w-full text-left px-2.5 py-1 rounded-lg hover:bg-red-500/20 text-red-400 hover:text-red-300 text-[11px] flex items-center gap-2 cursor-pointer mt-0.5">
                        <span>🗑️</span> <span>Delete Table</span>
                    </button>
                </div>
            </template>

            <!-- SECTION 1: CLIPBOARD & SELECTION ACTIONS -->
            <div class="space-y-0.5">
                <button type="button" x-on:click="closeContextMenu(); cutSelection()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>✂️</span> <span>Cut</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+X</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); copySelection()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>📋</span> <span>Copy</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+C</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); pasteClipboard()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>📄</span> <span>Paste</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+V</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); selectAllCanvas()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>🔲</span> <span>Select All</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Ctrl+A</span>
                </button>
            </div>

            <div class="border-t border-white/10 my-1"></div>

            <!-- SECTION 2: AI REASONING & WRITING INTELLIGENCE (sub-content-sub-agent) -->
            <div id="sub-content-sub-agent" class="sub-content-sub-agent space-y-0.5">
                <div class="px-2 py-0.5 text-[9px] font-mono text-purple-400 font-bold flex items-center justify-between">
                    <span>sub-content-sub-agent</span>
                    <span class="text-[8px] text-slate-500">PARAGRAPH AI</span>
                </div>
                <button type="button" x-on:click="closeContextMenu(); openInlineAiPrompt()" class="w-full text-left px-2.5 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 font-bold flex items-center justify-between cursor-pointer transition-colors border border-indigo-500/30">
                    <span class="flex items-center gap-2"><span>✦</span> <span>Ask AI Inline...</span></span>
                    <span class="text-[10px] text-indigo-400 font-mono">Ctrl+K</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('recreate')" class="w-full text-left px-2.5 py-1.5 rounded-xl bg-purple-950/60 hover:bg-purple-900/60 text-purple-200 hover:text-white flex items-center justify-between cursor-pointer transition-colors border border-purple-500/40 font-bold">
                    <span class="flex items-center gap-2"><span>🔄</span> <span>Recreate Paragraph (sub-agent)</span></span>
                    <span class="text-[9px] font-mono px-1 rounded bg-purple-900/80 text-purple-300">AI</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('rewrite')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-cyan-400">↻</span> <span>Rewrite & Polish</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('expand')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-violet-400">+</span> <span>Expand with Depth</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('shorten')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-amber-400">−</span> <span>Shorten & Condense</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('simplify')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-pink-400">⚡</span> <span>Simplify (8th-Grade)</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('generate_faq')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-indigo-400">❓</span> <span>Generate FAQ Block</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('key_takeaways')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-teal-400">💡</span> <span>Extract Key Takeaways</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('seo_optimize')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-emerald-400">⌁</span> <span>SEO Optimize Text</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('inject_data_points')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-emerald-400">📊</span> <span>Inject Data & Benchmarks</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('add_code_snippet')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-indigo-400">💻</span> <span>Generate Code Snippet</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('inject_counter_arguments')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-amber-400">⚖️</span> <span>Add Trade-offs & Nuance</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('surgical_micro_repair')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-emerald-600/25 text-emerald-300 hover:text-white flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-emerald-400">🔬</span> <span>Surgical Micro-Repair</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); triggerSubContentSubAgent('verify_lineage')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-cyan-600/25 text-cyan-300 hover:text-white flex items-center gap-2 cursor-pointer transition-colors">
                    <span class="text-cyan-400">🛡️</span> <span>Verify Lineage & Grounding</span>
                </button>
            </div>

            <!-- SECTION 3: TONE SHIFTER SUBMENU -->
            <div class="relative" x-data="{ toneSubOpen: false }">
                <button type="button" x-on:click.stop="toneSubOpen = !toneSubOpen" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/25 text-slate-200 hover:text-indigo-200 flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>🎨</span> <span>Tone Shifter</span></span>
                    <span class="text-[10px] text-slate-400" x-text="toneSubOpen ? '▼' : '▶'"></span>
                </button>
                <div x-show="toneSubOpen" class="mt-1 p-1.5 bg-slate-950 rounded-xl border border-white/10 space-y-1 text-xs shadow-xl">
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:professional')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">👔 Executive & Professional</button>
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:casual')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">☕ Warm & Conversational</button>
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:persuasive')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">🎯 High-Impact Persuasive</button>
                    <button type="button" x-on:click="closeContextMenu(); triggerAiTransform('tone:academic')" class="w-full text-left px-2 py-1 rounded-lg hover:bg-white/10 text-slate-300 hover:text-white cursor-pointer">📚 Academic & Analytical</button>
                </div>
            </div>

            <div class="border-t border-white/10 my-1"></div>

            <!-- SECTION 4: EDITORIAL QUICK INSERTERS -->
            <div class="space-y-0.5">
                <button type="button" x-on:click="closeContextMenu(); insertCurrentDate()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer transition-colors">
                    <span>📅</span> <span>Insert Today's Date</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); applyFormat('hr')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer transition-colors">
                    <span>—</span> <span>Insert Divider</span>
                </button>
                <button type="button" x-on:click="closeContextMenu(); deleteSelection()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-red-600/20 text-slate-400 hover:text-red-300 flex items-center justify-between cursor-pointer transition-colors">
                    <span class="flex items-center gap-2"><span>🗑️</span> <span>Delete Selection</span></span>
                    <span class="text-[10px] text-slate-500 font-mono">Del</span>
                </button>
            </div>

            <div class="border-t border-white/10 my-1"></div>

            <button type="button" x-on:click="closeContextMenu()" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-white/5 text-slate-400 hover:text-slate-200 text-[11px] cursor-pointer">
                ✕ Close Menu (Esc)
            </button>
        </div>
    </template>

    <!-- Interactive Floating Slash Commands Palette (Triggered on '/') -->
    <div
        x-show="showSlashMenu"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            :style="`position: fixed; left: ${slashMenuX}px; top: ${slashMenuY}px; z-index: var(--z-index-floating);`"
            class="editor-floating-panel max-h-[380px] overflow-y-auto scrollbar-thin scrollbar-thumb-white/10"
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
            <button type="button" x-on:click="executeSlashAction('surgical_micro_repair')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-emerald-400">🔬</span>
                <div>
                    <div>Surgical Micro-Repair</div>
                    <div class="text-[10px] text-slate-400">Precision sentence repair & fact grounding</div>
                </div>
            </button>
            <button type="button" x-on:click="executeSlashAction('verify_lineage')" class="w-full text-left px-2.5 py-1.5 rounded-xl hover:bg-indigo-600/30 text-slate-200 hover:text-white flex items-center gap-2.5 transition-colors cursor-pointer">
                <span class="w-5 h-5 rounded-lg bg-indigo-950 flex items-center justify-center text-xs text-cyan-400">🛡️</span>
                <div>
                    <div>Verify Lineage & Sources</div>
                    <div class="text-[10px] text-slate-400">Trace facts, claims, and citation roots</div>
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


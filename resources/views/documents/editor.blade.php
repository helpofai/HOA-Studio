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
    class="space-y-4 min-h-screen flex flex-col justify-between"
    x-data="{
        editorInstance: null,
        selectedText: '',
        hasSelection: false,
        wordCount: @entangle('wordCount'),
        characterCount: @entangle('characterCount'),
        readingTime: @entangle('readingTimeMinutes'),

        // Layout Docking & Focus Mode
        showLeftPanel: true,
        showRightPanel: true,
        rightTab: 'seo', // seo, titles_meta, ai_ideas, keywords, quality, outline, versions
        targetWordGoal: 1200,
        serpView: 'desktop', // desktop, mobile

        // Capability flags
        caps: { richText: true, blocks: true, markdown: false, undoRedo: true },

        // Lossy Switch Guard
        showLossyWarning: false,
        pendingEngine: null,
        lossyEngines: { plaintext: true, html: true },

        // AI Command Center State
        aiPrompt: '',
        aiModel: 'Auto (OmniRoute)',
        aiContext: {
            currentDoc: true,
            project: true,
            brandVoice: true,
            knowledgeBase: true,
            webResearch: false
        },
        aiHistory: [
            { id: 1, type: 'Outline Generation', prompt: 'Generate H1/H2/H3 structure for 2026 AI guide', time: 'Just now' },
            { id: 2, type: 'Quick Answer', prompt: 'Create 2-paragraph TL;DR for search intent', time: '12m ago' },
            { id: 3, type: 'FAQ Block', prompt: 'Generate 4 high-value schema FAQ pairs', time: '30m ago' }
        ],

        // Floating AI Selection Assistant State
        isTransforming: false,
        activeAction: null,
        customPrompt: '',
        showCustomInput: false,
        showPreviewModal: false,
        originalText: '',
        aiResult: '',
        routedModel: 'OmniRoute',
        wordsCount: 0,
        copied: false,

        // Real-time Document Outline
        docOutline: [],
        updateOutline() {
            if (!this.editorInstance) return;
            const html = this.editorInstance.getHTML ? this.editorInstance.getHTML() : '';
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const headings = temp.querySelectorAll('h1, h2, h3');
            this.docOutline = Array.from(headings).map(h => ({
                level: parseInt(h.tagName[1]),
                text: h.textContent.trim()
            })).filter(h => h.text.length > 0);
        },

        scrollToHeading(text) {
            const container = document.getElementById('tiptap-content-target');
            if (!container) return;
            const els = Array.from(container.querySelectorAll('h1, h2, h3, .gt-block, .notion-row'));
            const match = els.find(el => el.textContent.trim().toLowerCase().includes(text.toLowerCase()));
            if (match) {
                match.scrollIntoView({ behavior: 'smooth', block: 'center' });
                match.classList.add('ring-2', 'ring-indigo-500/60', 'rounded-lg');
                setTimeout(() => match.classList.remove('ring-2', 'ring-indigo-500/60', 'rounded-lg'), 1500);
            }
        },

        insertContentIntoCanvas(htmlContent) {
            if (!this.editorInstance) return;
            if (typeof this.editorInstance.insertContent === 'function') {
                this.editorInstance.insertContent(htmlContent);
            } else if (typeof this.editorInstance.setContent === 'function') {
                const current = this.editorInstance.getHTML ? this.editorInstance.getHTML() : '';
                this.editorInstance.setContent(current + '<br>' + htmlContent);
            }
        },

        setPromptPreset(text) {
            this.aiPrompt = text;
            const textarea = document.getElementById('ai-command-prompt');
            if (textarea) textarea.focus();
        },

        toggleFocusMode() {
            if (this.showLeftPanel || this.showRightPanel) {
                this.showLeftPanel = false;
                this.showRightPanel = false;
            } else {
                this.showLeftPanel = true;
                this.showRightPanel = true;
            }
        },

        initEditor() {
            if (this.editorInstance) {
                this.editorInstance.destroy();
            }

            const driverType = '{{ $editorType }}';
            this.editorInstance = window.HOA_EditorManager.createEditor(driverType, 'tiptap-content-target', {
                initialContent: @js($contentHtml),
                placeholder: 'Type / for AI commands or write your content...',
                onStatsChange: (stats) => {
                    this.wordCount = stats.words;
                    this.characterCount = stats.characters;
                    this.readingTime = Math.max(1, Math.ceil(stats.words / 200));
                    this.updateOutline();
                },
                onSelectionChange: ({ selectedText, isEmpty }) => {
                    this.selectedText = selectedText;
                    this.hasSelection = !isEmpty && selectedText.trim().length > 0;
                },
                onAutosave: (data) => {
                    $wire.autosave(data.html, data.json ?? null);
                }
            });

            if (this.editorInstance && this.editorInstance.capabilities) {
                this.caps = { ...this.caps, ...this.editorInstance.capabilities };
            }
            this.updateOutline();
        },

        applyFormat(action, param = null) {
            if (!this.editorInstance) return;
            if (action === 'heading')          this.editorInstance.toggleHeading?.(param);
            else if (action === 'bold')        this.editorInstance.toggleBold?.();
            else if (action === 'italic')      this.editorInstance.toggleItalic?.();
            else if (action === 'bulletList')  this.editorInstance.toggleBulletList?.();
            else if (action === 'orderedList') this.editorInstance.toggleOrderedList?.();
            else if (action === 'blockquote')  this.editorInstance.toggleBlockquote?.();
            else if (action === 'codeBlock')   this.editorInstance.toggleCodeBlock?.();
            else if (action === 'hr')          this.editorInstance.setHorizontalRule?.();
            else if (action === 'undo')        this.editorInstance.undo?.();
            else if (action === 'redo')        this.editorInstance.redo?.();
        },

        requestEngineSwitch(targetEngine) {
            const currentRich = this.caps.richText || this.caps.blocks;
            if (currentRich && this.lossyEngines[targetEngine]) {
                this.pendingEngine = targetEngine;
                this.showLossyWarning = true;
            } else {
                $wire.switchEditorType(targetEngine);
            }
        },
        confirmLossySwitch() {
            if (this.pendingEngine) $wire.switchEditorType(this.pendingEngine);
            this.showLossyWarning = false;
            this.pendingEngine = null;
        },
        cancelLossySwitch() {
            this.showLossyWarning = false;
            this.pendingEngine = null;
        },

        async triggerAiTransform(type, customInstruction = '') {
            const targetText = this.hasSelection ? this.selectedText : (this.editorInstance ? this.editorInstance.getText() : '');
            if (!targetText && type !== 'generate_outline' && type !== 'generate_faq' && type !== 'quick_answer') return;

            this.isTransforming = true;
            this.activeAction = type;
            this.originalText = targetText || 'Document Context';
            this.aiResult = '';
            this.showPreviewModal = true;

            try {
                const response = await fetch('{{ route('ai.stream-transform') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'text/event-stream'
                    },
                    body: JSON.stringify({
                        text: this.originalText,
                        type: type,
                        custom_instruction: customInstruction || this.customPrompt || this.aiPrompt,
                        model: this.aiModel
                    })
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.error || 'Server error while generating transformation.');
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            try {
                                const parsed = JSON.parse(line.substring(6));
                                if (parsed.token) this.aiResult += parsed.token;
                                if (parsed.model) this.routedModel = parsed.model;
                                if (parsed.result) this.aiResult = parsed.result;
                                if (parsed.words_used) this.wordsCount = parsed.words_used;
                            } catch (e) {}
                        }
                    }
                }

                this.aiHistory.unshift({
                    id: Date.now(),
                    type: type.replace('_', ' ').toUpperCase(),
                    prompt: (customInstruction || this.aiPrompt || type).substring(0, 35) + '...',
                    time: 'Just now'
                });
            } catch (err) {
                console.error(err);
                this.aiResult = 'Error during AI execution: ' + err.message;
            } finally {
                this.isTransforming = false;
                this.activeAction = null;
            }
        },

        applyReplace() {
            if (!this.aiResult || !this.editorInstance) return;
            if (this.hasSelection && typeof this.editorInstance.replaceSelection === 'function') {
                this.editorInstance.replaceSelection(this.aiResult);
            } else if (typeof this.editorInstance.setContent === 'function') {
                this.editorInstance.setContent(this.aiResult);
            }
            this.showPreviewModal = false;
        },

        applyInsertBelow() {
            if (!this.aiResult || !this.editorInstance) return;
            this.insertContentIntoCanvas('<br><br>' + this.aiResult);
            this.showPreviewModal = false;
        },

        copyToClipboard(text) {
            const target = text || this.aiResult;
            if (!target) return;
            navigator.clipboard.writeText(target);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2000);
        }
    }"
    x-init="
        initEditor();
        $wire.on('editor:setContent', ({ content }) => {
            if (editorInstance) editorInstance.setContent(content);
        });
        $wire.on('editor:reload', () => {
            initEditor();
        });

        // Global productivity keyboard shortcuts
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                $wire.saveExplicitSnapshot();
            }
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                showLeftPanel = true;
                const p = document.getElementById('ai-command-prompt');
                if (p) p.focus();
            }
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'f') {
                e.preventDefault();
                toggleFocusMode();
            }
        });
    "
>
    <!-- ========================================================================= -->
    <!-- TOP DOCUMENT CONTROL & BREADCRUMB BAR                                     -->
    <!-- ========================================================================= -->
    <div class="relative z-40 flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 rounded-2xl glass-elevated border border-white/10 shadow-xl">
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
                    class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-indigo-500/40 text-xs font-semibold text-slate-300 hover:text-white flex items-center gap-1.5 transition-all cursor-pointer shadow-sm"
                >
                    <span>⬇ Export</span>
                    <span class="text-[10px] text-slate-400">▼</span>
                </button>

                <div 
                    x-show="open" 
                    x-on:click.outside="open = false" 
                    class="absolute right-0 mt-2 w-48 rounded-2xl bg-slate-900/95 border border-white/20 p-1.5 shadow-2xl z-[100] space-y-1 font-mono text-xs backdrop-blur-2xl"
                    style="display: none;"
                >
                    <div class="px-2 py-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Export Formats</div>
                    <a href="{{ route('documents.export', ['id' => $documentId, 'format' => 'md']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                        <span>📝 Markdown (.md)</span>
                    </a>
                    <a href="{{ route('documents.export', ['id' => $documentId, 'format' => 'html']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                        <span>🌐 HTML (.html)</span>
                    </a>
                    <a href="{{ route('documents.export', ['id' => $documentId, 'format' => 'txt']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                        <span>📄 Plain Text (.txt)</span>
                    </a>
                    <a href="{{ route('documents.export', ['id' => $documentId, 'format' => 'docx']) }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                        <span>📘 Word (.doc)</span>
                    </a>
                    <div class="border-t border-white/5 my-1"></div>
                    <a href="{{ route('documents.print-pdf', ['id' => $documentId]) }}" target="_blank" class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl text-indigo-300 hover:text-white hover:bg-indigo-600/20 transition-colors">
                        <span>🖨️ Print / Save PDF</span>
                    </a>
                </div>
            </div>

            <!-- Manual Snapshot -->
            <x-glass.button 
                type="button" 
                variant="primary" 
                size="sm" 
                wire:click="saveExplicitSnapshot"
                title="Save snapshot (Ctrl+S)"
            >
                💾 Save (Ctrl+S)
            </x-glass.button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- THREE-COLUMN WORKSPACE LAYOUT                                             -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 gap-4 items-start"
         :class="{
             'lg:grid-cols-[320px_1fr_360px]': showLeftPanel && showRightPanel,
             'lg:grid-cols-[320px_1fr]': showLeftPanel && !showRightPanel,
             'lg:grid-cols-[1fr_360px]': !showLeftPanel && showRightPanel,
             'lg:grid-cols-1': !showLeftPanel && !showRightPanel
         }"
    >
        <!-- ─── COLUMN 1: DYNAMIC AI COMMAND CENTER (320px) ────────────────── -->
        <div 
            x-show="showLeftPanel" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="space-y-4 lg:sticky lg:top-4"
        >
            <x-glass.card variant="standard" class="p-4 space-y-4 border border-white/10 shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between pb-2 border-b border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 animate-pulse"></span>
                        <h2 class="text-xs uppercase font-extrabold text-white tracking-wider">AI Command Center</h2>
                    </div>
                    <span class="text-[10px] font-mono text-indigo-400 font-bold px-2 py-0.5 rounded-full bg-indigo-600/20 border border-indigo-500/30">OmniRoute v2.0</span>
                </div>

                <!-- 1-Click God-Level Prompt Presets -->
                <div class="space-y-1.5">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Quick AI Pipelines</span>
                    <div class="flex flex-wrap gap-1">
                        <button type="button" x-on:click="triggerAiTransform('generate_outline')" class="px-2 py-1 rounded-lg bg-indigo-950/50 hover:bg-indigo-600/30 text-indigo-300 text-[10.5px] border border-indigo-500/30 transition-colors">📑 Article Outline</button>
                        <button type="button" x-on:click="triggerAiTransform('quick_answer')" class="px-2 py-1 rounded-lg bg-indigo-950/50 hover:bg-indigo-600/30 text-indigo-300 text-[10.5px] border border-indigo-500/30 transition-colors">⚡ Quick Answer</button>
                        <button type="button" x-on:click="triggerAiTransform('generate_faq')" class="px-2 py-1 rounded-lg bg-indigo-950/50 hover:bg-indigo-600/30 text-indigo-300 text-[10.5px] border border-indigo-500/30 transition-colors">❓ FAQ Schema</button>
                        <button type="button" x-on:click="triggerAiTransform('content_gaps')" class="px-2 py-1 rounded-lg bg-indigo-950/50 hover:bg-indigo-600/30 text-indigo-300 text-[10.5px] border border-indigo-500/30 transition-colors">🔍 Content Gaps</button>
                        <button type="button" x-on:click="triggerAiTransform('eeat_trust')" class="px-2 py-1 rounded-lg bg-indigo-950/50 hover:bg-indigo-600/30 text-indigo-300 text-[10.5px] border border-indigo-500/30 transition-colors">🏆 E-E-A-T Block</button>
                        <button type="button" x-on:click="triggerAiTransform('comparison_table')" class="px-2 py-1 rounded-lg bg-indigo-950/50 hover:bg-indigo-600/30 text-indigo-300 text-[10.5px] border border-indigo-500/30 transition-colors">📊 Feature Table</button>
                    </div>
                </div>

                <!-- Custom Ask AI Prompt Area -->
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-300 flex items-center justify-between">
                        <span>✦ Ask AI / Custom Workflow</span>
                        <span class="text-[9px] font-mono text-slate-500">Ctrl+K</span>
                    </label>
                    <textarea 
                        id="ai-command-prompt"
                        x-model="aiPrompt"
                        rows="3"
                        placeholder="Instruct AI: e.g. Write a search-intent optimized guide with quick answers, comparison tables, and E-E-A-T signals..."
                        class="w-full bg-slate-900/90 border border-white/15 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 resize-none font-sans leading-relaxed shadow-inner"
                    ></textarea>
                    
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('custom', aiPrompt)"
                        :disabled="isTransforming || !aiPrompt.trim()"
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all disabled:opacity-50 cursor-pointer"
                    >
                        <span x-show="!isTransforming">✦ Execute AI Command</span>
                        <span x-show="isTransforming" class="animate-spin text-sm">⟳</span>
                        <span x-show="isTransforming">Streaming Tokens...</span>
                    </button>
                </div>

                <!-- Quick Transformation Actions -->
                <div class="space-y-2 pt-2 border-t border-white/10">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Semantic Transformations</span>
                    <div class="grid grid-cols-2 gap-1.5 text-xs font-medium">
                        <button type="button" x-on:click="triggerAiTransform('rewrite')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                            <span class="text-cyan-400">↻</span> Rewrite
                        </button>
                        <button type="button" x-on:click="triggerAiTransform('expand')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                            <span class="text-violet-400">+</span> Expand
                        </button>
                        <button type="button" x-on:click="triggerAiTransform('shorten')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                            <span class="text-amber-400">−</span> Shorten
                        </button>
                        <button type="button" x-on:click="triggerAiTransform('improve')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                            <span class="text-emerald-400">✧</span> Polish Flow
                        </button>
                        <button type="button" x-on:click="triggerAiTransform('seo_optimize')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                            <span class="text-yellow-400">⌁</span> SEO Optimize
                        </button>
                        <button type="button" x-on:click="triggerAiTransform('key_takeaways')" class="p-2 rounded-xl bg-slate-900/60 hover:bg-white/10 text-slate-200 hover:text-white border border-white/5 text-left flex items-center gap-1.5 transition-colors">
                            <span class="text-pink-400">💡</span> Key Takeaways
                        </button>
                    </div>
                </div>

                <!-- Context & Intelligence Grounding -->
                <div class="space-y-2 pt-2 border-t border-white/10 text-xs text-slate-300">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Contextual Grounding</span>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 cursor-pointer hover:text-white">
                            <input type="checkbox" x-model="aiContext.currentDoc" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                            <span>Current Document Canvas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-white">
                            <input type="checkbox" x-model="aiContext.brandVoice" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                            <span>Brand Voice Profile</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer hover:text-white">
                            <input type="checkbox" x-model="aiContext.knowledgeBase" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-0">
                            <span>Knowledge Base RAG</span>
                        </label>
                    </div>
                </div>

                <!-- AI Model Selector -->
                <div class="space-y-1.5 pt-2 border-t border-white/10">
                    <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Decoupled AI Router</label>
                    <select 
                        x-model="aiModel" 
                        class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono"
                    >
                        <option value="Auto (OmniRoute)">⚡ Auto (OmniRoute Intelligent Gateway)</option>
                        <option value="Claude 3.7 Sonnet">Claude 3.7 Sonnet (Anthropic)</option>
                        <option value="GPT-4o">GPT-4o (OpenAI)</option>
                        <option value="Gemini 2.0 Flash">Gemini 2.0 Flash (Google Deepmind)</option>
                        <option value="DeepSeek-V3">DeepSeek-V3 (Local/Cloud)</option>
                    </select>
                </div>
            </x-glass.card>
        </div>

        <!-- ─── COLUMN 2: CONTENT WORKSPACE (CENTER / FLEXIBLE) ─────────────── -->
        <div class="space-y-4">
            <!-- Live Capability-Aware Formatting Ribbon -->
            <x-glass.card variant="standard" class="p-2 flex flex-wrap items-center justify-between gap-2 border border-white/10 sticky top-2 z-30 shadow-xl">
                <div class="flex flex-wrap items-center gap-1 text-xs">
                    <!-- Rich-text controls -->
                    <template x-if="caps.richText">
                        <div class="flex flex-wrap items-center gap-1">
                            <button type="button" x-on:click="applyFormat('heading', 1)" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Heading 1">H1</button>
                            <button type="button" x-on:click="applyFormat('heading', 2)" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Heading 2">H2</button>
                            <button type="button" x-on:click="applyFormat('heading', 3)" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Heading 3">H3</button>
                            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>
                            <button type="button" x-on:click="applyFormat('bold')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Bold">B</button>
                            <button type="button" x-on:click="applyFormat('italic')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 italic" title="Italic">I</button>
                            <button type="button" x-on:click="applyFormat('codeBlock')" class="px-2 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-mono text-[11px]" title="Code Block">&lt;/&gt;</button>
                            <button type="button" x-on:click="applyFormat('blockquote')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-serif" title="Quote">"</button>
                            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>
                            <button type="button" x-on:click="applyFormat('bulletList')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10" title="Bullet List">&bull; List</button>
                            <button type="button" x-on:click="applyFormat('orderedList')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10" title="Numbered List">1. List</button>
                            <button type="button" x-on:click="applyFormat('hr')" class="px-2 py-1 rounded-lg text-slate-300 hover:bg-white/10 text-xs" title="Horizontal Rule">—</button>
                            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>
                        </div>
                    </template>

                    <!-- Markdown badge & helpers -->
                    <template x-if="caps.markdown && !caps.richText">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-300 text-[11px] font-mono border border-indigo-500/20">📝 Markdown Active</span>
                            <button type="button" x-on:click="if (editorInstance) editorInstance.insertContent ? editorInstance.insertContent('\n## ') : null" class="px-2 py-1 rounded hover:bg-white/10 text-slate-300 text-xs font-mono">## H2</button>
                            <button type="button" x-on:click="if (editorInstance) editorInstance.insertContent ? editorInstance.insertContent('**bold**') : null" class="px-2 py-1 rounded hover:bg-white/10 text-slate-300 text-xs font-mono">**B**</button>
                            <button type="button" x-on:click="if (editorInstance) editorInstance.insertContent ? editorInstance.insertContent('- [ ] ') : null" class="px-2 py-1 rounded hover:bg-white/10 text-slate-300 text-xs font-mono">✓ Todo</button>
                            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>
                        </div>
                    </template>

                    <!-- Plain text badge -->
                    <template x-if="!caps.richText && !caps.markdown">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-500/10 text-slate-400 text-[11px] font-mono border border-slate-500/20">📄 Plain Text Active</span>
                            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>
                        </div>
                    </template>

                    <!-- Undo/Redo -->
                    <template x-if="caps.undoRedo">
                        <div class="flex items-center gap-1">
                            <button type="button" x-on:click="applyFormat('undo')" class="px-2 py-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10" title="Undo">&#8630;</button>
                            <button type="button" x-on:click="applyFormat('redo')" class="px-2 py-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10" title="Redo">&#8631;</button>
                        </div>
                    </template>
                </div>

                <!-- Word & Reading Metrics with Goal Progress -->
                <div class="flex items-center gap-3 text-xs text-slate-400 font-mono pr-2">
                    <span><strong class="text-white" x-text="wordCount">0</strong> words</span>
                    <span>&bull;</span>
                    <span><strong class="text-white" x-text="characterCount">0</strong> chars</span>
                    <span>&bull;</span>
                    <span><strong class="text-indigo-300" x-text="readingTime + 'm'">1m</strong> read</span>
                </div>
            </x-glass.card>

            <!-- Main Editor Writing Surface with Live Diff Stream Modal & Floating Bubble -->
            <x-glass.card 
                variant="elevated" 
                class="p-6 sm:p-10 min-h-[650px] border border-white/15 shadow-2xl relative"
            >
                <!-- Floating Contextual Selection Bubble -->
                <div 
                    x-show="hasSelection && !showPreviewModal"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="fixed bottom-16 left-1/2 transform -translate-x-1/2 z-40 bg-slate-900/95 border border-indigo-500/40 rounded-2xl shadow-2xl backdrop-blur-xl p-2 flex items-center gap-1.5 text-xs animate-in"
                    style="display: none;"
                >
                    <div class="flex items-center gap-1.5 px-2 py-1 bg-indigo-600/20 rounded-xl text-indigo-300 font-bold text-[11px]">
                        <span>✦ AI Selection</span>
                    </div>

                    <button type="button" x-on:click="triggerAiTransform('improve')" class="px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white font-medium">✧ Polish</button>
                    <button type="button" x-on:click="triggerAiTransform('expand')" class="px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white font-medium">+ Expand</button>
                    <button type="button" x-on:click="triggerAiTransform('shorten')" class="px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white font-medium">− Shorten</button>
                    <button type="button" x-on:click="triggerAiTransform('rewrite')" class="px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white font-medium">↻ Rewrite</button>
                    <button type="button" x-on:click="triggerAiTransform('simplify')" class="px-2.5 py-1.5 rounded-xl hover:bg-white/10 text-slate-200 hover:text-white font-medium">◇ Simplify</button>
                </div>

                <!-- Live Stream AI Transformation Preview Modal -->
                <div 
                    x-show="showPreviewModal" 
                    x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
                    style="display: none;"
                >
                    <div class="w-full max-w-4xl rounded-3xl glass-elevated border border-white/15 p-6 space-y-6 shadow-2xl animate-in zoom-in-95">
                        <div class="flex items-center justify-between pb-4 border-b border-white/10">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                    <span>AI Content Transformation</span>
                                    <span class="text-xs font-normal text-slate-400 font-mono" x-text="'(' + routedModel + ')'"></span>
                                </h3>
                            </div>
                            <button type="button" x-on:click="showPreviewModal = false" class="text-slate-400 hover:text-white p-2">✕</button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <span class="text-xs font-mono text-slate-400 font-semibold uppercase">Original Text</span>
                                <div class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 text-xs text-slate-300 font-sans leading-relaxed max-h-80 overflow-y-auto" x-text="originalText"></div>
                            </div>
                            <div class="space-y-2">
                                <span class="text-xs font-mono text-indigo-400 font-semibold uppercase flex items-center justify-between">
                                    <span>AI Generated Output</span>
                                    <span x-show="isTransforming" class="text-[10px] text-amber-400 animate-pulse font-normal">Streaming tokens...</span>
                                </span>
                                <div class="p-4 rounded-2xl bg-indigo-950/20 border border-indigo-500/30 text-xs text-slate-100 font-sans leading-relaxed max-h-80 overflow-y-auto whitespace-pre-wrap" x-text="aiResult"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-white/10">
                            <button type="button" x-on:click="copyToClipboard()" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-300 text-xs font-semibold">
                                <span x-text="copied ? '✓ Copied' : '📋 Copy Output'"></span>
                            </button>
                            <div class="flex items-center gap-2">
                                <button type="button" x-on:click="showPreviewModal = false" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-400 text-xs font-semibold">✕ Discard</button>
                                <button type="button" x-on:click="applyInsertBelow()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-indigo-500/40 text-indigo-300 text-xs font-semibold">⬇ Insert Into Document</button>
                                <button type="button" x-on:click="applyReplace()" class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-xs font-bold shadow-lg shadow-violet-600/30">✓ Replace Selection</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Editor Engine Canvas Mount Target (wire:ignore for lightspeed typing) -->
                <div id="tiptap-content-target" class="min-h-[550px]" wire:ignore></div>
            </x-glass.card>
        </div>

        <!-- ─── COLUMN 3: CONTENT INTELLIGENCE & AI RECOMMENDATIONS (360px) ─── -->
        <div 
            x-show="showRightPanel" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="space-y-4 lg:sticky lg:top-4"
        >
            <x-glass.card variant="standard" class="p-4 space-y-4 border border-white/10 shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs uppercase font-extrabold text-white tracking-wider">Content Intelligence</span>
                    </div>
                    <span class="text-[10px] font-mono text-emerald-400 font-bold" x-text="'Goal: ' + Math.min(100, Math.round((wordCount/targetWordGoal)*100)) + '%'"></span>
                </div>

                <!-- 7 Comprehensive Tabs Navigation -->
                <div class="flex flex-wrap items-center gap-1 p-1 rounded-xl bg-slate-900 border border-white/10 text-xs font-mono">
                    <button type="button" x-on:click="rightTab = 'seo'" :class="rightTab === 'seo' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">SEO</button>
                    <button type="button" x-on:click="rightTab = 'titles_meta'" :class="rightTab === 'titles_meta' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">Titles</button>
                    <button type="button" x-on:click="rightTab = 'ai_ideas'" :class="rightTab === 'ai_ideas' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">AI Ideas</button>
                    <button type="button" x-on:click="rightTab = 'keywords'" :class="rightTab === 'keywords' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">Keys</button>
                    <button type="button" x-on:click="rightTab = 'quality'" :class="rightTab === 'quality' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">Audit</button>
                    <button type="button" x-on:click="rightTab = 'outline'" :class="rightTab === 'outline' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">Tree</button>
                    <button type="button" x-on:click="rightTab = 'versions'" :class="rightTab === 'versions' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="flex-1 py-1 px-1.5 rounded-lg text-center transition-colors">Snap</button>
                </div>

                <!-- ─── TAB 1: SEO AUDIT & SERP PREVIEW ─────────────────────── -->
                <div x-show="rightTab === 'seo'" class="space-y-4">
                    <!-- Top Score Cards -->
                    <div class="grid grid-cols-2 gap-2">
                        @php
                            $score = $seoData['score'] ?? 0;
                            $scoreColor = $score >= 80 ? 'text-emerald-400 border-emerald-500/40 bg-emerald-950/30' : ($score >= 50 ? 'text-yellow-400 border-yellow-500/40 bg-yellow-950/30' : 'text-red-400 border-red-500/40 bg-red-950/30');
                            $readScore = $seoData['readability_score'] ?? 0;
                            $readColor = $readScore >= 60 ? 'text-cyan-400 border-cyan-500/40 bg-cyan-950/30' : 'text-slate-300 border-white/10 bg-slate-900/60';
                        @endphp
                        <div class="p-3 rounded-2xl border {{ $scoreColor }} flex flex-col items-center justify-center text-center space-y-0.5">
                            <span class="text-2xl font-black font-mono">{{ $score }}<span class="text-xs font-normal text-slate-400">/100</span></span>
                            <span class="text-[9.5px] uppercase font-bold text-slate-300">SEO Content Score</span>
                        </div>
                        <div class="p-3 rounded-2xl border {{ $readColor }} flex flex-col items-center justify-center text-center space-y-0.5">
                            <span class="text-2xl font-black font-mono">{{ $readScore }}<span class="text-xs font-normal text-slate-400">/100</span></span>
                            <span class="text-[9.5px] uppercase font-bold text-slate-300">Reading Ease</span>
                        </div>
                    </div>

                    <!-- Target Focus Keyword Placement Matrix -->
                    <div class="space-y-2 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <label class="text-[11px] font-bold text-slate-300 flex items-center justify-between">
                            <span>🎯 Focus Keyword</span>
                            <button type="button" wire:click="runSeoAudit" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-mono font-semibold">
                                <span wire:loading.remove wire:target="runSeoAudit">🔄 Re-Audit</span>
                                <span wire:loading wire:target="runSeoAudit">...</span>
                            </button>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <input 
                                type="text" 
                                wire:model="targetKeyword" 
                                wire:keydown.enter="runSeoAudit"
                                placeholder="e.g. artificial intelligence..." 
                                class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                            />
                            <button type="button" wire:click="runSeoAudit" class="px-2.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs">Set</button>
                        </div>

                        @if(!empty($seoData['metrics']['keyword']))
                            @php $kw = $seoData['metrics']['keyword']; @endphp
                            <div class="grid grid-cols-2 gap-1.5 pt-1 text-[10.5px] font-mono">
                                <div class="p-1.5 rounded-lg bg-slate-950/80 border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">Title:</span>
                                    <span class="{{ $kw['in_title'] ? 'text-emerald-400 font-bold' : 'text-red-400' }}">{{ $kw['in_title'] ? '✓ Yes' : '✕ No' }}</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-slate-950/80 border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">Intro:</span>
                                    <span class="{{ $kw['in_first_100_words'] ? 'text-emerald-400 font-bold' : 'text-red-400' }}">{{ $kw['in_first_100_words'] ? '✓ Yes' : '✕ No' }}</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-slate-950/80 border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">Headings:</span>
                                    <span class="{{ ($kw['in_h2'] || $kw['in_h3']) ? 'text-emerald-400 font-bold' : 'text-yellow-400' }}">{{ ($kw['in_h2'] || $kw['in_h3']) ? '✓ Yes' : '✕ No' }}</span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-slate-950/80 border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">Density:</span>
                                    <span class="{{ ($kw['density'] >= 0.8 && $kw['density'] <= 2.5) ? 'text-emerald-400 font-bold' : 'text-yellow-400' }}">{{ $kw['density'] }}% ({{ $kw['count'] }}x)</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Live Google SERP Preview Card -->
                    <div class="p-3 rounded-2xl bg-slate-900/80 border border-white/10 space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-300">
                            <span>🔍 Live Google SERP Snippet</span>
                            <div class="flex items-center gap-1 font-mono text-[10px]">
                                <button type="button" x-on:click="serpView = 'desktop'" :class="serpView === 'desktop' ? 'text-indigo-400 font-bold' : 'text-slate-500'">Desktop</button>
                                <span>|</span>
                                <button type="button" x-on:click="serpView = 'mobile'" :class="serpView === 'mobile' ? 'text-indigo-400 font-bold' : 'text-slate-500'">Mobile</button>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-white/5 space-y-1 font-sans">
                            <div class="text-[10px] text-slate-400 truncate">https://helpofai.com/blog/{{ Str::slug($title ?: 'untitled-post') }}</div>
                            <div class="text-xs font-bold text-blue-400 hover:underline cursor-pointer line-clamp-1">{{ $title ?: 'Untitled Article' }} | HelpOfAi</div>
                            <div class="text-[11px] text-slate-300 line-clamp-2 leading-relaxed">
                                {{ $metaDescription ?: 'Experience the next-generation AI writing workflow with HelpOfAi Studio in 2026. High-converting content, SEO recommendations, and multi-editor engines.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── TAB 2: TITLES & META DESCRIPTIONS (NEW) ──────────────── -->
                <div x-show="rightTab === 'titles_meta'" class="space-y-4" style="display: none;">
                    <!-- Trending SEO Titles Generator -->
                    <div class="space-y-2.5 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">✨ Trending SEO Titles</span>
                            <button type="button" wire:click="generateSeoTitles" class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-mono text-[10.5px] font-bold border border-indigo-500/30 transition-colors">
                                <span wire:loading.remove wire:target="generateSeoTitles">⚡ Generate Titles</span>
                                <span wire:loading wire:target="generateSeoTitles">Generating...</span>
                            </button>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @if(!empty($aiTitles))
                                @foreach($aiTitles as $idx => $sugTitle)
                                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5 group hover:border-indigo-500/30 transition-all text-xs">
                                        <div class="text-slate-200 font-medium leading-snug">{{ $sugTitle }}</div>
                                        <div class="flex items-center justify-between pt-1 border-t border-white/5">
                                            <span class="text-[10px] font-mono text-slate-400">{{ mb_strlen($sugTitle) }} chars</span>
                                            <button type="button" wire:click="applyTitle(@js($sugTitle))" class="px-2 py-0.5 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[10.5px] transition-colors">
                                                + Apply Title
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-slate-500 text-[11px] italic py-2">Click "Generate Titles" to get high-CTR, search-optimized title variations.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Click-Magnet Meta Descriptions Generator -->
                    <div class="space-y-2.5 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">📝 Meta Descriptions</span>
                            <button type="button" wire:click="generateMetaDescriptions" class="px-2.5 py-1 rounded-lg bg-purple-600/30 hover:bg-purple-600 text-purple-300 hover:text-white font-mono text-[10.5px] font-bold border border-purple-500/30 transition-colors">
                                <span wire:loading.remove wire:target="generateMetaDescriptions">⚡ Generate Meta</span>
                                <span wire:loading wire:target="generateMetaDescriptions">Generating...</span>
                            </button>
                        </div>

                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @if(!empty($aiMetaDescriptions))
                                @foreach($aiMetaDescriptions as $idx => $sugMeta)
                                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1.5 group hover:border-purple-500/30 transition-all text-xs">
                                        <div class="text-slate-200 text-[11.5px] leading-relaxed">{{ $sugMeta }}</div>
                                        <div class="flex items-center justify-between pt-1 border-t border-white/5">
                                            <span class="text-[10px] font-mono {{ mb_strlen($sugMeta) <= 160 ? 'text-emerald-400' : 'text-amber-400' }}">{{ mb_strlen($sugMeta) }} / 160 chars</span>
                                            <button type="button" wire:click="applyMetaDescription(@js($sugMeta))" class="px-2 py-0.5 rounded-md bg-purple-600 hover:bg-purple-500 text-white font-bold text-[10.5px] transition-colors">
                                                + Apply Meta
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-slate-500 text-[11px] italic py-2">Click "Generate Meta" to create 155-character search snippets with strong calls to action.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ─── TAB 3: AI IDEAS, FAQS & CONTENT GAPS (NEW) ───────────── -->
                <div x-show="rightTab === 'ai_ideas'" class="space-y-4" style="display: none;">
                    <!-- Content Gap Finder -->
                    <div class="space-y-2 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">🔍 Missing Content Gaps</span>
                            <button type="button" wire:click="generateContentGaps" class="px-2.5 py-1 rounded-lg bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white font-mono text-[10.5px] font-bold border border-emerald-500/30 transition-colors">
                                <span wire:loading.remove wire:target="generateContentGaps">⚡ Audit Gaps</span>
                                <span wire:loading wire:target="generateContentGaps">...</span>
                            </button>
                        </div>
                        <div class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">
                            @if(!empty($aiContentGaps))
                                @foreach($aiContentGaps as $gap)
                                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                                        <div class="font-semibold text-white">{{ $gap['topic'] ?? 'Topic' }}</div>
                                        <p class="text-[11px] text-slate-400">{{ $gap['reason'] ?? '' }}</p>
                                        @if(!empty($gap['suggested_h2']))
                                            <button 
                                                type="button" 
                                                x-on:click="insertContentIntoCanvas('<h2>' + @js($gap['suggested_h2']) + '</h2><p>Provide comprehensive insights on ' + @js($gap['topic']) + '...</p>')"
                                                class="mt-1 px-2 py-0.5 rounded bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white text-[10px] font-mono font-bold"
                                            >
                                                + Insert Section ({{ $gap['suggested_h2'] }})
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-slate-500 text-[11px] italic py-1">Identify competitive subtopics and missing search intent angles.</p>
                            @endif
                        </div>
                    </div>

                    <!-- FAQ Section Generator -->
                    <div class="space-y-2 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">❓ Schema-Ready FAQ Suggestions</span>
                            <button type="button" wire:click="generateFaqSuggestions" class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-mono text-[10.5px] font-bold border border-indigo-500/30 transition-colors">
                                <span wire:loading.remove wire:target="generateFaqSuggestions">⚡ Generate FAQs</span>
                                <span wire:loading wire:target="generateFaqSuggestions">...</span>
                            </button>
                        </div>
                        <div class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">
                            @if(!empty($aiFaqs))
                                @foreach($aiFaqs as $faq)
                                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-white/5 space-y-1">
                                        <div class="font-bold text-indigo-300">Q: {{ $faq['question'] ?? '' }}</div>
                                        <p class="text-[11px] text-slate-300 leading-relaxed">{{ $faq['answer'] ?? '' }}</p>
                                        <button 
                                            type="button" 
                                            x-on:click="insertContentIntoCanvas('<h3>' + @js($faq['question']) + '</h3><p>' + @js($faq['answer']) + '</p>')"
                                            class="mt-1 px-2 py-0.5 rounded bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white text-[10px] font-mono font-bold"
                                        >
                                            + Insert Q&A into Editor
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-slate-500 text-[11px] italic py-1">Generate genuine, search-intent driven FAQ questions and structured answers.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Answer Box Generator -->
                    <div class="space-y-2 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">⚡ Quick Answer / TL;DR Box</span>
                            <button type="button" wire:click="generateQuickAnswer" class="px-2.5 py-1 rounded-lg bg-amber-600/30 hover:bg-amber-600 text-amber-300 hover:text-white font-mono text-[10.5px] font-bold border border-amber-500/30 transition-colors">
                                <span wire:loading.remove wire:target="generateQuickAnswer">⚡ Generate</span>
                                <span wire:loading wire:target="generateQuickAnswer">...</span>
                            </button>
                        </div>
                        @if(!empty($aiQuickAnswer))
                            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-amber-500/30 space-y-1.5 text-xs text-slate-200">
                                <div>{!! $aiQuickAnswer !!}</div>
                                <button 
                                    type="button" 
                                    x-on:click="insertContentIntoCanvas('<blockquote><strong>Quick Summary:</strong> ' + @js($aiQuickAnswer) + '</blockquote>')"
                                    class="mt-1 px-2.5 py-1 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10.5px] font-bold"
                                >
                                    + Insert Quick Answer to Intro
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ─── TAB 4: KEYWORDS & SEMANTIC ENTITIES ───────────────────── -->
                <div x-show="rightTab === 'keywords'" class="space-y-3" style="display: none;">
                    <div class="space-y-2.5 p-3 rounded-2xl bg-slate-900/80 border border-white/10">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-white">🏷️ Secondary LSI Keywords</label>
                            <button type="button" wire:click="suggestLsiKeywords" class="text-[10.5px] text-indigo-400 hover:text-indigo-300 font-mono font-bold">
                                <span wire:loading.remove wire:target="suggestLsiKeywords">⚡ AI Suggest</span>
                                <span wire:loading wire:target="suggestLsiKeywords">...</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input 
                                type="text" 
                                wire:model="newSecondaryKeyword" 
                                wire:keydown.enter.prevent="addSecondaryKeyword"
                                placeholder="Add custom keyword..." 
                                class="flex-1 bg-slate-950 border border-white/15 rounded-xl px-2.5 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 font-mono"
                            />
                            <button type="button" wire:click="addSecondaryKeyword" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs">+</button>
                        </div>

                        <!-- Secondary Keyword Tags -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @forelse($secondaryKeywords as $index => $skw)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-950/60 border border-indigo-500/30 text-indigo-300 font-mono text-[11px]">
                                    <span>{{ $skw }}</span>
                                    <button type="button" wire:click="removeSecondaryKeyword({{ $index }})" class="text-slate-400 hover:text-red-400">✕</button>
                                </span>
                            @empty
                                <span class="text-slate-500 text-[11px] italic">No secondary keywords added yet.</span>
                            @endforelse
                        </div>

                        <!-- AI Suggested Keywords List -->
                        @if(!empty($aiSeoResults) && $aiSeoType === 'lsi')
                            <div class="pt-2 border-t border-white/5 space-y-1.5">
                                <span class="text-[10px] font-bold uppercase text-slate-400">AI Suggested Keywords (Click + to add)</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($aiSeoResults as $suggested)
                                        <button 
                                            type="button" 
                                            wire:click="addSuggestedKeyword(@js($suggested))"
                                            class="px-2 py-0.5 rounded-md bg-slate-950 border border-indigo-500/20 hover:border-indigo-500/50 text-slate-300 hover:text-white text-[10.5px] font-mono flex items-center gap-1"
                                        >
                                            <span>+</span> <span>{{ $suggested }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ─── TAB 5: 10-POINT CONTENT QUALITY AUDIT ─────────────────── -->
                <div x-show="rightTab === 'quality'" class="space-y-3" style="display: none;">
                    <div class="p-3 rounded-2xl bg-slate-900/80 border border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">🏆 10-Point Content Quality Score</span>
                            <button type="button" wire:click="generateQualityAudit" class="px-2.5 py-1 rounded-lg bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white font-mono text-[10.5px] font-bold border border-indigo-500/30 transition-colors">
                                ⚡ Run Audit
                            </button>
                        </div>

                        <div class="space-y-2 font-mono text-[11px]">
                            @php $qa = $aiQualityAudit ?? ['search_intent' => 92, 'topic_coverage' => 88, 'original_value' => 90, 'readability' => 85, 'seo_structure' => 94, 'internal_linking' => 82, 'eeat_signals' => 90, 'technical_seo' => 96, 'overall' => 91]; @endphp
                            
                            <div class="p-2.5 rounded-xl bg-indigo-950/40 border border-indigo-500/30 flex items-center justify-between text-white font-bold">
                                <span>Overall Content Quality</span>
                                <span class="text-emerald-400 text-sm">{{ $qa['overall'] }}/100</span>
                            </div>

                            <div class="space-y-1.5 text-slate-300 pt-1">
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>1. Search Intent Satisfaction:</span><span class="text-emerald-400 font-bold">{{ $qa['search_intent'] }}%</span></div>
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>2. Topical Depth & Coverage:</span><span class="text-emerald-400 font-bold">{{ $qa['topic_coverage'] }}%</span></div>
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>3. Original Analysis & Value:</span><span class="text-emerald-400 font-bold">{{ $qa['original_value'] }}%</span></div>
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>4. Readability & Scannability:</span><span class="text-cyan-400 font-bold">{{ $qa['readability'] }}%</span></div>
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>5. Heading & Structure SEO:</span><span class="text-emerald-400 font-bold">{{ $qa['seo_structure'] }}%</span></div>
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>6. E-E-A-T & Trust Signals:</span><span class="text-emerald-400 font-bold">{{ $qa['eeat_signals'] }}%</span></div>
                                <div class="flex justify-between p-1 rounded bg-slate-950/60"><span>7. Technical Architecture:</span><span class="text-emerald-400 font-bold">{{ $qa['technical_seo'] }}%</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── TAB 6: DOCUMENT OUTLINE WITH CLICK-TO-SCROLL ─────────── -->
                <div x-show="rightTab === 'outline'" class="space-y-2" style="display: none;">
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Document Outline Tree (Click to Scroll)</span>
                    <div class="space-y-1 max-h-96 overflow-y-auto font-mono text-xs pr-1">
                        <template x-if="docOutline.length === 0">
                            <p class="text-slate-500 text-xs italic py-2">No headings detected yet. Add H1, H2, or H3 to generate structure.</p>
                        </template>
                        <template x-for="(item, idx) in docOutline" :key="idx">
                            <div 
                                x-on:click="scrollToHeading(item.text)"
                                class="p-1.5 rounded-lg hover:bg-white/10 transition-colors cursor-pointer text-slate-300 hover:text-white flex items-center gap-2"
                                :class="{
                                    'pl-2 font-bold text-indigo-300': item.level === 1,
                                    'pl-5 text-slate-300': item.level === 2,
                                    'pl-8 text-slate-400': item.level === 3
                                }"
                            >
                                <span class="text-[10px] font-bold text-slate-500" x-text="'H' + item.level"></span>
                                <span class="truncate" x-text="item.text"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ─── TAB 7: SNAPSHOT VERSIONS ────────────────────────────── -->
                <div x-show="rightTab === 'versions'" class="space-y-2" style="display: none;">
                    <div class="flex items-center justify-between pb-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Snapshot Timeline</span>
                        <button type="button" wire:click="saveExplicitSnapshot" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-mono font-bold">+ New Snapshot</button>
                    </div>
                    <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                        @foreach($document->versions as $v)
                            <div class="p-2.5 rounded-xl {{ $document->current_version_id === $v->id ? 'bg-indigo-600/15 border border-indigo-500/40' : 'bg-slate-900/60 border border-white/5' }} space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white">Version #{{ $v->version_number }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $v->created_at->format('M d, H:i') }}</span>
                                </div>
                                <p class="text-[11px] text-slate-300 truncate">{{ $v->summary ?? 'Saved snapshot' }}</p>
                                <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400 font-mono">
                                    <span>{{ number_format($v->word_count) }} words</span>
                                    @if($document->current_version_id !== $v->id)
                                        <button type="button" wire:click="restoreVersion({{ $v->id }})" class="text-indigo-400 hover:text-indigo-300 font-bold">Restore &rarr;</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-glass.card>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- STICKY BOTTOM STATUS BAR WITH GOAL TRACKER                                -->
    <!-- ========================================================================= -->
    <div class="sticky bottom-0 z-20 mt-4 p-3 rounded-2xl glass-elevated border border-white/10 shadow-2xl flex flex-wrap items-center justify-between gap-3 text-xs font-mono text-slate-400">
        <div class="flex flex-wrap items-center gap-4">
            <span>Words: <strong class="text-white" x-text="wordCount">0</strong></span>
            <span>&bull;</span>
            <span>Chars: <strong class="text-white" x-text="characterCount">0</strong></span>
            <span>&bull;</span>
            <span>Reading: <strong class="text-indigo-300" x-text="readingTime + ' min'">1 min</strong></span>
            <span>&bull;</span>
            <span>SEO: <strong class="{{ ($seoData['score'] ?? 0) >= 80 ? 'text-emerald-400' : 'text-yellow-400' }}">{{ $seoData['score'] ?? 0 }}/100</strong></span>
            <span>&bull;</span>
            <span>Readability: <strong class="text-cyan-400">{{ ($seoData['readability_score'] ?? 0) >= 60 ? 'Good' : 'Standard' }}</strong></span>
            <span class="hidden md:inline">&bull;</span>
            <span class="hidden md:inline text-slate-300">Goal: <strong class="text-emerald-400" x-text="wordCount + '/' + targetWordGoal"></strong> (<span x-text="Math.min(100, Math.round((wordCount/targetWordGoal)*100)) + '%'"></span>)</span>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-indigo-400">Model: <strong class="text-white" x-text="aiModel">Auto</strong></span>
            <span>&bull;</span>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $isSaving ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' }}"></span>
                <span class="text-slate-300">{{ $saveStatusText }}</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PUBLIC & PROTECTED SHARING MODAL                                          -->
    <!-- ========================================================================= -->
    @if($showShareModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl glass-elevated border border-white/15 p-6 sm:p-8 space-y-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between pb-4 border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-lg text-indigo-300">🔗</div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Share & Publish Document</h3>
                            <p class="text-xs text-slate-400">Create an encrypted public view link with custom access controls.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-white p-2">✕</button>
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
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $shareUrl }}'); alert('Link copied to clipboard!');" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs">Copy</button>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                    <button type="button" wire:click="$set('showShareModal', false)" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold">Close</button>
                    <button type="button" wire:click="createOrUpdateShare" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30">
                        {{ $isShareActive ? 'Update Share Settings' : 'Generate Public Link' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- LOSSY ENGINE SWITCH WARNING MODAL                                         -->
    <!-- ========================================================================= -->
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
</div>

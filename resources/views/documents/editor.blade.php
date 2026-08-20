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
    class="space-y-6"
    x-data="{
        editorInstance: null,
        selectedText: '',
        hasSelection: false,
        wordCount: @entangle('wordCount'),
        characterCount: @entangle('characterCount'),
        readingTime: @entangle('readingTimeMinutes'),
        initEditor() {
            if (this.editorInstance) {
                this.editorInstance.destroy();
            }

            const driverType = '{{ $editorType }}';
            this.editorInstance = window.HOA_EditorManager.createEditor(driverType, 'tiptap-content-target', {
                initialContent: @js($contentHtml),
                placeholder: 'Type / for AI prompts or write your thoughts...',
                onStatsChange: (stats) => {
                    this.wordCount = stats.words;
                    this.characterCount = stats.characters;
                    this.readingTime = Math.max(1, Math.ceil(stats.words / 200));
                },
                onSelectionChange: ({ selectedText, isEmpty }) => {
                    this.selectedText = selectedText;
                    this.hasSelection = !isEmpty && selectedText.trim().length > 0;
                },
                onAutosave: (data) => {
                    $wire.autosave(data.html, data.json);
                }
            });
        },
        applyFormat(action, param = null) {
            if (!this.editorInstance) return;
            if (action === 'heading') this.editorInstance.toggleHeading(param);
            else if (action === 'bold') this.editorInstance.toggleBold();
            else if (action === 'italic') this.editorInstance.toggleItalic();
            else if (action === 'bulletList') this.editorInstance.toggleBulletList();
            else if (action === 'orderedList') this.editorInstance.toggleOrderedList();
            else if (action === 'blockquote') this.editorInstance.toggleBlockquote();
            else if (action === 'codeBlock') this.editorInstance.toggleCodeBlock();
            else if (action === 'hr') this.editorInstance.setHorizontalRule();
            else if (action === 'undo') this.editorInstance.undo();
            else if (action === 'redo') this.editorInstance.redo();
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
    "
>
    <!-- Top Document Control Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-white/5">
        <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <a href="{{ route('documents.index') }}" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/5 transition-colors">
                &larr; Back
            </a>

            <!-- Document Title Input -->
            <input 
                type="text" 
                wire:model.live.debounce.500ms="title" 
                class="text-xl sm:text-2xl font-black text-white bg-transparent border-b border-transparent hover:border-white/10 focus:border-indigo-500 focus:outline-none px-1 py-0.5 w-full sm:max-w-md tracking-tight transition-colors"
                placeholder="Untitled Document"
            />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Multi-Editor Engine Switcher -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    x-on:click="open = !open" 
                    type="button" 
                    class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 text-xs text-slate-300 hover:text-white flex items-center gap-2 cursor-pointer"
                >
                    <span class="text-indigo-400">⚡ Engine:</span>
                    <span class="font-semibold">{{ $availableEditors[$editorType]['name'] ?? 'Tiptap' }}</span>
                    <span>▾</span>
                </button>

                <div 
                    x-show="open" 
                    x-on:click.outside="open = false" 
                    class="absolute right-0 mt-2 w-64 rounded-2xl glass-elevated border border-white/15 p-2 shadow-2xl z-50 space-y-1"
                    style="display: none;"
                >
                    <div class="px-2 py-1 text-[10px] uppercase font-bold text-slate-500 tracking-wider">Multi-Editor Architecture</div>
                    @foreach($availableEditors as $key => $editor)
                        <button 
                            type="button" 
                            wire:click="switchEditorType('{{ $key }}')" 
                            x-on:click="open = false"
                            class="w-full text-left p-2 rounded-xl text-xs flex flex-col transition-colors {{ $editorType === $key ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-300 hover:bg-white/5' }}"
                        >
                            <span class="font-semibold text-white">{{ $editor['name'] }}</span>
                            <span class="text-[10px] text-slate-400">{{ $editor['description'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Save Status Badge -->
            <div class="flex items-center gap-2 text-xs text-slate-400 font-mono">
                <span class="w-2 h-2 rounded-full {{ $isSaving ? 'bg-amber-400 animate-ping' : 'bg-emerald-400' }}"></span>
                <span>{{ $saveStatusText }}</span>
            </div>

            <!-- Export Menu Dropdown -->
            <div class="relative" x-data="{ open: false }">
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
                    class="absolute right-0 mt-2 w-48 rounded-2xl glass-elevated border border-white/15 p-1.5 shadow-2xl z-50 space-y-1 font-mono text-xs"
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

            <!-- Share & Public Link Button -->
            <button 
                type="button" 
                wire:click="openShareModal" 
                class="px-3 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5 {{ $isShareActive ? 'bg-indigo-600/20 border-indigo-500/50 text-indigo-300 shadow-md shadow-indigo-500/10' : 'bg-slate-900 border-white/10 hover:border-indigo-500/40 text-slate-300 hover:text-white' }}"
                title="Share document publicly with optional password"
            >
                <span>🔗 Share</span>
                @if($isShareActive)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                @endif
            </button>

            <!-- SEO Score Button -->
            <button 
                type="button" 
                wire:click="$toggle('showSeoDrawer')" 
                class="px-3 py-1.5 rounded-xl border text-xs font-bold font-mono transition-all cursor-pointer flex items-center gap-1.5 {{ ($seoData['score'] ?? 0) >= 80 ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-400' : (($seoData['score'] ?? 0) >= 50 ? 'bg-yellow-950/80 border-yellow-500/40 text-yellow-400' : 'bg-red-950/80 border-red-500/40 text-red-400') }}"
                title="Open Real-time SEO Intelligence & Score"
            >
                <span>⚡ SEO:</span>
                <span>{{ $seoData['score'] ?? 0 }}/100</span>
            </button>

            <!-- Version History Button -->
            <x-glass.button 
                type="button" 
                variant="secondary" 
                size="sm" 
                wire:click="$toggle('showVersionHistory')"
            >
                🕒 History ({{ $document->versions->count() }})
            </x-glass.button>

            <!-- Manual Snapshot -->
            <x-glass.button 
                type="button" 
                variant="primary" 
                size="sm" 
                wire:click="saveExplicitSnapshot"
            >
                💾 Save Snapshot
            </x-glass.button>
        </div>
    </div>

    <!-- Live Formatting Ribbon (Tiptap Driver) -->
    <x-glass.card variant="standard" class="p-2 flex flex-wrap items-center justify-between gap-2 border border-white/10 sticky top-2 z-30 shadow-xl">
        <div class="flex flex-wrap items-center gap-1 text-xs">
            <button type="button" x-on:click="applyFormat('heading', 1)" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Heading 1">H1</button>
            <button type="button" x-on:click="applyFormat('heading', 2)" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Heading 2">H2</button>
            <button type="button" x-on:click="applyFormat('heading', 3)" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Heading 3">H3</button>

            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>

            <button type="button" x-on:click="applyFormat('bold')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-bold" title="Bold">B</button>
            <button type="button" x-on:click="applyFormat('italic')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 italic" title="Italic">I</button>
            <button type="button" x-on:click="applyFormat('codeBlock')" class="px-2 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-mono text-[11px]" title="Code Block">&lt;/&gt;</button>
            <button type="button" x-on:click="applyFormat('blockquote')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10 font-serif" title="Quote">“</button>

            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>

            <button type="button" x-on:click="applyFormat('bulletList')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10" title="Bullet List">&bull; List</button>
            <button type="button" x-on:click="applyFormat('orderedList')" class="px-2.5 py-1 rounded-lg text-slate-300 hover:bg-white/10" title="Numbered List">1. List</button>
            <button type="button" x-on:click="applyFormat('hr')" class="px-2 py-1 rounded-lg text-slate-300 hover:bg-white/10 text-xs" title="Horizontal Rule">—</button>

            <span class="w-[1px] h-4 bg-white/10 mx-1"></span>

            <button type="button" x-on:click="applyFormat('undo')" class="px-2 py-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10" title="Undo">↶</button>
            <button type="button" x-on:click="applyFormat('redo')" class="px-2 py-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10" title="Redo">↷</button>
        </div>

        <!-- Metrics Badges -->
        <div class="flex items-center gap-3 text-xs text-slate-400 font-mono pr-2">
            <span><strong class="text-white" x-text="wordCount">0</strong> words</span>
            <span>&bull;</span>
            <span><strong class="text-white" x-text="characterCount">0</strong> chars</span>
            <span>&bull;</span>
            <span><strong class="text-indigo-300" x-text="readingTime + 'm'">1m</strong> read</span>
        </div>
    </x-glass.card>

    <!-- Main Editor Writing Surface with Contextual AI Selection Assistant & Live Stream Diff Modal -->
    <x-glass.card 
        variant="elevated" 
        class="p-6 sm:p-12 min-h-[650px] border border-white/15 shadow-2xl relative"
        x-data="{
            isTransforming: false,
            activeAction: null,
            customPrompt: '',
            showCustomInput: false,
            openToneMenu: false,
            openSummarizeMenu: false,
            showPreviewModal: false,
            originalText: '',
            aiResult: '',
            routedModel: 'OmniRoute',
            wordsCount: 0,
            copied: false,

            async triggerAiTransform(type, customInstruction = '') {
                if (!selectedText || this.isTransforming) return;
                this.isTransforming = true;
                this.activeAction = type;
                this.originalText = selectedText;
                this.aiResult = '';
                this.showPreviewModal = true;
                this.openToneMenu = false;
                this.openSummarizeMenu = false;

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
                            custom_instruction: customInstruction || this.customPrompt
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
                                    if (parsed.token) {
                                        this.aiResult += parsed.token;
                                    }
                                    if (parsed.model) {
                                        this.routedModel = parsed.model;
                                    }
                                    if (parsed.result) {
                                        this.aiResult = parsed.result;
                                    }
                                    if (parsed.words_used) {
                                        this.wordsCount = parsed.words_used;
                                    }
                                } catch (e) {}
                            }
                        }
                    }
                } catch (err) {
                    console.error(err);
                    this.aiResult = 'Error during transformation: ' + err.message;
                } finally {
                    this.isTransforming = false;
                    this.activeAction = null;
                }
            },

            applyReplace() {
                if (!this.aiResult || !editorInstance) return;
                editorInstance.replaceSelection(this.aiResult);
                this.showPreviewModal = false;
            },

            applyInsertBelow() {
                if (!this.aiResult || !editorInstance) return;
                editorInstance.insertBelowSelection(this.aiResult);
                this.showPreviewModal = false;
            },

            copyToClipboard() {
                if (!this.aiResult) return;
                navigator.clipboard.writeText(this.aiResult);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        }"
    >
        <!-- Floating Contextual AI Selection Assistant Bubble Menu -->
        <div 
            x-show="hasSelection && !showPreviewModal" 
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="sticky top-16 z-30 mb-6 p-2 rounded-2xl bg-slate-950/95 border border-indigo-500/40 shadow-[0_15px_40px_rgba(0,0,0,0.8)] backdrop-blur-2xl flex flex-col gap-2"
            style="display: none;"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <!-- OmniRoute AI Brand Tag -->
                <div class="flex items-center gap-2 text-xs font-bold text-indigo-300 pl-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>⚡ OmniRoute AI Assist:</span>
                </div>

                <!-- Primary Contextual Actions Toolbar -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <!-- 1. Polish -->
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('polish')" 
                        :disabled="isTransforming"
                        class="px-2.5 py-1 rounded-xl bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-200 border border-indigo-500/30 transition-all flex items-center gap-1 cursor-pointer"
                        title="Polish grammar, vocabulary, and flow while keeping original intent"
                    >
                        <span>✨ Polish</span>
                    </button>

                    <!-- 2. Rewrite -->
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('rewrite')" 
                        :disabled="isTransforming"
                        class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        title="Rephrase with fresh alternative wording"
                    >
                        <span>🔄 Rewrite</span>
                    </button>

                    <!-- 3. Expand -->
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('expand')" 
                        :disabled="isTransforming"
                        class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        title="Elaborate with deeper explanations, arguments, and context"
                    >
                        <span>📈 Expand</span>
                    </button>

                    <!-- 4. Shorten -->
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('shorten')" 
                        :disabled="isTransforming"
                        class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        title="Make concise, punchy, and remove filler"
                    >
                        <span>📉 Shorten</span>
                    </button>

                    <!-- 5. TL;DR -->
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('tldr')" 
                        :disabled="isTransforming"
                        class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        title="Generate 2-3 key takeaway bullet points"
                    >
                        <span>⚡ TL;DR</span>
                    </button>

                    <!-- 6. Continue Writing -->
                    <button 
                        type="button" 
                        x-on:click="triggerAiTransform('continue')" 
                        :disabled="isTransforming"
                        class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        title="Continue writing naturally in the same style"
                    >
                        <span>➡️ Continue</span>
                    </button>

                    <!-- 7. Tone Modifier Dropdown -->
                    <div class="relative">
                        <button 
                            type="button" 
                            x-on:click="openToneMenu = !openToneMenu; openSummarizeMenu = false;" 
                            class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        >
                            <span>🎭 Tone ▾</span>
                        </button>

                        <div 
                            x-show="openToneMenu" 
                            x-on:click.outside="openToneMenu = false" 
                            class="absolute right-0 mt-1.5 w-48 rounded-xl bg-[#0d1117] border border-[#30363d] p-1.5 shadow-2xl z-40 space-y-1 font-mono text-xs"
                            style="display: none;"
                        >
                            <div class="px-2 py-1 text-[10px] uppercase font-bold text-slate-500">Tone Adjuster</div>
                            <button type="button" x-on:click="triggerAiTransform('tone:professional')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>👔 Professional</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('tone:casual')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>☕ Casual & Warm</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('tone:persuasive')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>🎯 Persuasive & Punchy</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('tone:friendly')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>😊 Friendly & Empathetic</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('tone:academic')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>🎓 Academic & Analytical</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('tone:direct')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>⚡ Direct & No-Fluff</span>
                            </button>
                        </div>
                    </div>

                    <!-- 8. Summarize & Structure Dropdown -->
                    <div class="relative">
                        <button 
                            type="button" 
                            x-on:click="openSummarizeMenu = !openSummarizeMenu; openToneMenu = false;" 
                            class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-white/10 hover:border-violet-500/40 transition-all flex items-center gap-1 cursor-pointer"
                        >
                            <span>📝 Summarize ▾</span>
                        </button>

                        <div 
                            x-show="openSummarizeMenu" 
                            x-on:click.outside="openSummarizeMenu = false" 
                            class="absolute right-0 mt-1.5 w-52 rounded-xl bg-[#0d1117] border border-[#30363d] p-1.5 shadow-2xl z-40 space-y-1 font-mono text-xs"
                            style="display: none;"
                        >
                            <div class="px-2 py-1 text-[10px] uppercase font-bold text-slate-500">Synthesis & Utilities</div>
                            <button type="button" x-on:click="triggerAiTransform('summarize')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>📝 Executive Summary</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('action_items')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>☑️ Action Items Checklist</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('simplify')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>💡 Simplify (8th Grade)</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('fix_grammar')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>🔤 Fix Grammar & Typos</span>
                            </button>
                            <button type="button" x-on:click="triggerAiTransform('seo_optimize')" class="w-full text-left px-2.5 py-1.5 rounded-lg text-slate-200 hover:bg-white/10 hover:text-white flex items-center gap-2 cursor-pointer">
                                <span>🚀 SEO Optimize</span>
                            </button>
                        </div>
                    </div>

                    <!-- 9. Toggle Custom Instruction Box -->
                    <button 
                        type="button" 
                        x-on:click="showCustomInput = !showCustomInput" 
                        :class="showCustomInput ? 'bg-violet-600 text-white font-bold' : 'bg-slate-900 text-slate-300 border-white/10 hover:border-violet-500/40'"
                        class="px-2.5 py-1 rounded-xl border text-xs transition-all flex items-center gap-1 cursor-pointer"
                        title="Enter custom prompt instruction for selection"
                    >
                        <span>💬 Custom...</span>
                    </button>
                </div>
            </div>

            <!-- Expandable Custom Instruction Input Bar -->
            <div x-show="showCustomInput" class="pt-1 flex items-center gap-2" style="display: none;">
                <input 
                    type="text" 
                    x-model="customPrompt" 
                    x-on:keydown.enter.prevent="triggerAiTransform('custom')"
                    placeholder="Ask AI (e.g. 'Turn into a FAQ', 'Add bullet points', 'Make funny')..." 
                    class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-3 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500 font-mono"
                />
                <button 
                    type="button" 
                    x-on:click="triggerAiTransform('custom')"
                    class="px-3 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-semibold text-xs transition-all cursor-pointer"
                >
                    ⚡ Transform
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- LIVE SIDE-BY-SIDE STREAMING AI TRANSFORMATION PREVIEW MODAL                -->
        <!-- ========================================================================= -->
        <div 
            x-show="showPreviewModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/85 backdrop-blur-md"
            style="display: none;"
            x-cloak
        >
            <div class="w-full max-w-4xl bg-[#0d1117] border border-[#30363d] rounded-2xl shadow-[0_25px_80px_rgba(0,0,0,0.95)] ring-1 ring-white/10 overflow-hidden flex flex-col max-h-[85vh] font-sans">
                <!-- Modal macOS Header -->
                <div class="h-11 px-4 bg-[#161b22] border-b border-[#30363d] flex items-center justify-between shrink-0 select-none">
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            x-on:click="showPreviewModal = false" 
                            class="w-3 h-3 rounded-full bg-[#FF5F56] border border-[#E0443E] flex items-center justify-center text-[8px] font-bold text-black/70 hover:opacity-100 opacity-90 transition-all cursor-pointer group"
                            title="Close / Discard (X)"
                        >
                            <span class="opacity-0 group-hover:opacity-100 font-sans leading-none">✕</span>
                        </button>
                        <span class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-[#DEA123] opacity-80"></span>
                        <span class="w-3 h-3 rounded-full bg-[#27C93F] border border-[#1AAB29] opacity-80"></span>

                        <span class="ml-3 text-slate-200 text-xs font-bold font-mono">
                            ⚡ AI Contextual Transform Preview
                        </span>
                    </div>

                    <!-- Status & Engine Badge -->
                    <div class="flex items-center gap-2 text-xs font-mono">
                        <template x-if="isTransforming">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-950/80 border border-emerald-500/40 text-emerald-400 text-[10px] font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                STREAMING
                            </span>
                        </template>
                        <template x-if="!isTransforming">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-cyan-950/80 border border-cyan-500/40 text-cyan-400 text-[10px] font-bold">
                                ✓ READY
                            </span>
                        </template>
                        <span class="text-slate-500">•</span>
                        <span class="text-indigo-300 text-[11px]" x-text="routedModel"></span>
                    </div>
                </div>

                <!-- Side-by-Side Diff Comparison Surface -->
                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-[#30363d] overflow-y-auto flex-1 p-4 gap-4 bg-[#0d1117]">
                    <!-- Left: Original Selected Text -->
                    <div class="space-y-2 flex flex-col">
                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 pb-1 border-b border-white/5">
                            <span class="font-bold text-slate-300">Original Selection</span>
                            <span x-text="originalText.trim().split(/\s+/).length + ' words'"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-[#161b22]/70 border border-white/5 text-slate-300 text-sm leading-relaxed overflow-y-auto flex-1 select-text">
                            <p x-text="originalText"></p>
                        </div>
                    </div>

                    <!-- Right: Live AI Suggested Stream -->
                    <div class="space-y-2 flex flex-col">
                        <div class="flex items-center justify-between text-[11px] font-mono text-indigo-300 pb-1 border-b border-white/5">
                            <span class="font-bold text-emerald-400 flex items-center gap-1.5">
                                <span>✨ AI Transformed Output</span>
                            </span>
                            <span x-text="wordsCount > 0 ? wordsCount + ' words' : ''"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-gradient-to-b from-indigo-950/20 to-[#161b22]/90 border border-indigo-500/30 text-white text-sm leading-relaxed overflow-y-auto flex-1 select-text relative">
                            <p x-text="aiResult" class="whitespace-pre-wrap"></p>
                            
                            <template x-if="isTransforming">
                                <span class="inline-block w-2 h-4 bg-emerald-400 animate-pulse ml-0.5 align-middle"></span>
                            </template>
                            
                            <template x-if="!isTransforming && !aiResult">
                                <p class="text-slate-500 italic">Waiting for token stream...</p>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Action Bar -->
                <div class="p-3.5 bg-[#161b22] border-t border-[#30363d] flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            x-on:click="copyToClipboard()" 
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <span x-text="copied ? '✓ Copied' : '📋 Copy Output'"></span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            x-on:click="showPreviewModal = false" 
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-400 hover:text-white text-xs font-semibold transition-all cursor-pointer"
                        >
                            ✕ Discard
                        </button>

                        <button 
                            type="button" 
                            x-on:click="applyInsertBelow()" 
                            :disabled="isTransforming || !aiResult"
                            class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-indigo-500/40 text-indigo-300 hover:text-white text-xs font-semibold transition-all cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <span>⬇ Insert Below</span>
                        </button>

                        <button 
                            type="button" 
                            x-on:click="applyReplace()" 
                            :disabled="isTransforming || !aiResult"
                            class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold shadow-lg shadow-violet-600/30 transition-all cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
                        >
                            <span>✓ Replace Selection</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="tiptap-content-target"></div>
    </x-glass.card>

    <!-- Version History Slide-Out Drawer -->
    <div 
        x-show="$wire.showVersionHistory" 
        class="fixed inset-0 z-50 overflow-hidden"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showVersionHistory', false)"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md glass-elevated border-l border-white/15 p-6 flex flex-col justify-between shadow-2xl">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">Version History</h3>
                            <p class="text-xs text-slate-400">Restore snapshots without losing recent edits.</p>
                        </div>
                        <button wire:click="$set('showVersionHistory', false)" class="text-slate-400 hover:text-white p-2 text-base">✕</button>
                    </div>

                    <div class="space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto pr-1">
                        @foreach($document->versions as $v)
                            <div class="p-4 rounded-2xl {{ $document->current_version_id === $v->id ? 'bg-indigo-600/15 border border-indigo-500/40' : 'bg-slate-900/60 border border-white/5' }} space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white">Version #{{ $v->version_number }}</span>
                                        @if($document->current_version_id === $v->id)
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500 text-white font-semibold">Active</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $v->created_at->format('M d, H:i') }}</span>
                                </div>

                                <p class="text-xs text-slate-300">{{ $v->summary ?? 'Saved snapshot' }}</p>

                                <div class="flex items-center justify-between pt-2 border-t border-white/5 text-xs text-slate-400">
                                    <span>{{ number_format($v->word_count) }} words</span>
                                    @if($document->current_version_id !== $v->id)
                                        <button 
                                            type="button" 
                                            wire:click="restoreVersion({{ $v->id }})" 
                                            class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer"
                                        >
                                            Restore Snapshot &rarr;
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-white/10">
                    <x-glass.button type="button" variant="secondary" size="sm" class="w-full" wire:click="$set('showVersionHistory', false)">
                        Close History
                    </x-glass.button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- REAL-TIME SEO & CONTENT INTELLIGENCE SLIDE-OUT DRAWER                     -->
    <!-- ========================================================================= -->
    <div 
        x-show="$wire.showSeoDrawer" 
        class="fixed inset-0 z-50 overflow-hidden"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showSeoDrawer', false)"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-lg bg-[#0d1117] border-l border-[#30363d] p-6 flex flex-col justify-between shadow-2xl overflow-y-auto">
                <div class="space-y-6">
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-[#30363d]">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span>⚡ SEO & Content Intelligence</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Real-time keyword optimization, readability, and ranking factors.</p>
                        </div>
                        <button wire:click="$set('showSeoDrawer', false)" class="text-slate-400 hover:text-white p-2 text-base cursor-pointer">✕</button>
                    </div>

                    <!-- SEO & Readability Top Score Cards -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- SEO Overall Score -->
                        @php
                            $score = $seoData['score'] ?? 0;
                            $scoreColor = $score >= 80 ? 'text-emerald-400 border-emerald-500/40 bg-emerald-950/30' : ($score >= 50 ? 'text-yellow-400 border-yellow-500/40 bg-yellow-950/30' : 'text-red-400 border-red-500/40 bg-red-950/30');
                        @endphp
                        <div class="p-4 rounded-2xl border {{ $scoreColor }} flex flex-col items-center justify-center text-center space-y-1">
                            <span class="text-2xl sm:text-3xl font-black font-mono">{{ $score }}<span class="text-xs font-normal text-slate-400">/100</span></span>
                            <span class="text-[10px] uppercase font-bold text-slate-300">SEO Content Score</span>
                        </div>

                        <!-- Readability Ease Score -->
                        @php
                            $readScore = $seoData['readability_score'] ?? 0;
                            $readColor = $readScore >= 60 ? 'text-cyan-400 border-cyan-500/40 bg-cyan-950/30' : 'text-slate-300 border-white/10 bg-slate-900/60';
                        @endphp
                        <div class="p-4 rounded-2xl border {{ $readColor }} flex flex-col items-center justify-center text-center space-y-1">
                            <span class="text-2xl sm:text-3xl font-black font-mono">{{ $readScore }}<span class="text-xs font-normal text-slate-400">/100</span></span>
                            <span class="text-[10px] uppercase font-bold text-slate-300">Reading Ease</span>
                        </div>
                    </div>

                    <!-- Target Focus Keyword Input -->
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 space-y-3">
                        <label class="text-xs font-bold text-slate-300 flex items-center justify-between">
                            <span>🎯 Primary Focus Keyword</span>
                            <button 
                                type="button" 
                                wire:click="runSeoAudit" 
                                wire:loading.attr="disabled"
                                class="text-[10px] text-violet-400 hover:text-violet-300 font-mono font-semibold cursor-pointer disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="runSeoAudit">🔄 Re-Audit</span>
                                <span wire:loading wire:target="runSeoAudit">Analyzing...</span>
                            </button>
                        </label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                wire:model="targetKeyword" 
                                wire:keydown.enter="runSeoAudit"
                                placeholder="e.g. ai agents, b2b saas, content workflows..." 
                                class="flex-1 bg-[#0d1117] border border-white/15 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono"
                            />
                            <button 
                                type="button" 
                                wire:click="runSeoAudit" 
                                class="px-3 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs cursor-pointer"
                            >
                                Set
                            </button>
                        </div>

                        <!-- Target Keyword Placement Matrix -->
                        @if(!empty($seoData['metrics']['keyword']))
                            @php $kw = $seoData['metrics']['keyword']; @endphp
                            <div class="grid grid-cols-2 gap-2 pt-2 text-[11px] font-mono">
                                <div class="p-2 rounded-lg bg-[#0d1117] border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">In Title:</span>
                                    <span class="{{ $kw['in_title'] ? 'text-emerald-400 font-bold' : 'text-red-400' }}">{{ $kw['in_title'] ? '✓ Yes' : '✕ No' }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-[#0d1117] border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">In Intro (100w):</span>
                                    <span class="{{ $kw['in_first_100_words'] ? 'text-emerald-400 font-bold' : 'text-red-400' }}">{{ $kw['in_first_100_words'] ? '✓ Yes' : '✕ No' }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-[#0d1117] border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">In Subheadings:</span>
                                    <span class="{{ ($kw['in_h2'] || $kw['in_h3']) ? 'text-emerald-400 font-bold' : 'text-yellow-400' }}">{{ ($kw['in_h2'] || $kw['in_h3']) ? '✓ Yes' : '✕ No' }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-[#0d1117] border border-white/5 flex items-center justify-between">
                                    <span class="text-slate-400">Density:</span>
                                    <span class="{{ ($kw['density'] >= 0.8 && $kw['density'] <= 2.5) ? 'text-emerald-400 font-bold' : 'text-yellow-400' }}">{{ $kw['density'] }}% ({{ $kw['count'] }}x)</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Secondary Keywords Tag Manager -->
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-white/10 space-y-3">
                        <label class="text-xs font-bold text-slate-300 block">🏷️ Secondary & Semantic Keywords</label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                wire:model="newSecondaryKeyword" 
                                wire:keydown.enter.prevent="addSecondaryKeyword"
                                placeholder="Add secondary keyword..." 
                                class="flex-1 bg-[#0d1117] border border-white/15 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-violet-500 font-mono"
                            />
                            <button 
                                type="button" 
                                wire:click="addSecondaryKeyword" 
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer"
                            >
                                + Add
                            </button>
                        </div>

                        <!-- Active Secondary Keyword Badges -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @forelse($secondaryKeywords as $idx => $sk)
                                <span class="px-2.5 py-1 rounded-lg bg-violet-950/60 border border-violet-500/30 text-violet-300 text-xs flex items-center gap-1.5 font-mono">
                                    <span>{{ $sk }}</span>
                                    <button type="button" wire:click="removeSecondaryKeyword({{ $idx }})" class="hover:text-red-400 text-slate-400 text-[10px]">✕</button>
                                </span>
                            @empty
                                <span class="text-[11px] text-slate-500 italic">No secondary keywords added. Use AI suggestions below.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- AI SEO Generator Tools -->
                    <div class="p-4 rounded-2xl bg-gradient-to-b from-indigo-950/30 to-[#0d1117] border border-indigo-500/30 space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold text-indigo-300">
                            <span>⚡ AI SEO Generative Tools</span>
                            @if($isGeneratingSeo)
                                <span class="text-emerald-400 font-mono animate-pulse text-[10px]">Generating...</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button" 
                                wire:click="generateSeoTitles" 
                                :disabled="$wire.isGeneratingSeo"
                                class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-[10px] font-semibold text-center transition-all cursor-pointer disabled:opacity-50"
                            >
                                🎯 SEO Titles
                            </button>
                            <button 
                                type="button" 
                                wire:click="generateMetaDescriptions" 
                                :disabled="$wire.isGeneratingSeo"
                                class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-[10px] font-semibold text-center transition-all cursor-pointer disabled:opacity-50"
                            >
                                📝 Meta Tags
                            </button>
                            <button 
                                type="button" 
                                wire:click="suggestLsiKeywords" 
                                :disabled="$wire.isGeneratingSeo"
                                class="p-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-[10px] font-semibold text-center transition-all cursor-pointer disabled:opacity-50"
                            >
                                💡 LSI Keywords
                            </button>
                        </div>

                        <!-- AI SEO Results Output Display -->
                        @if(!empty($aiSeoResults))
                            <div class="pt-2 space-y-2 border-t border-white/5 text-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block font-mono">Suggestions (Click to apply):</span>
                                
                                @if($aiSeoType === 'titles')
                                    @foreach($aiSeoResults as $t)
                                        <div 
                                            wire:click="applyTitle('{{ addslashes($t) }}')"
                                            class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 hover:border-emerald-500/50 hover:bg-emerald-950/20 text-slate-200 hover:text-emerald-300 cursor-pointer transition-all flex items-center justify-between gap-2"
                                            title="Click to apply as document title"
                                        >
                                            <span class="font-medium truncate">{{ $t }}</span>
                                            <span class="text-[10px] text-emerald-400 shrink-0">Apply &rarr;</span>
                                        </div>
                                    @endforeach
                                @elseif($aiSeoType === 'metas')
                                    @foreach($aiSeoResults as $m)
                                        <div 
                                            onclick="navigator.clipboard.writeText({{ json_encode($m) }}); alert('Meta description copied to clipboard!');"
                                            class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 hover:border-cyan-500/50 text-slate-200 text-[11px] leading-relaxed cursor-pointer transition-all"
                                            title="Click to copy to clipboard"
                                        >
                                            <p>{{ $m }}</p>
                                            <span class="text-[9px] text-cyan-400 font-mono block mt-1">📋 Click to copy ({{ mb_strlen($m) }} chars)</span>
                                        </div>
                                    @endforeach
                                @elseif($aiSeoType === 'lsi')
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($aiSeoResults as $lsi)
                                            <button 
                                                type="button" 
                                                wire:click="addSuggestedKeyword('{{ addslashes($lsi) }}')"
                                                class="px-2 py-1 rounded-lg bg-indigo-950/80 hover:bg-indigo-900 border border-indigo-500/30 text-indigo-300 text-[10px] font-mono flex items-center gap-1 cursor-pointer"
                                                title="Click to track keyword"
                                            >
                                                <span>+</span>
                                                <span>{{ $lsi }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Actionable SEO Recommendations Checklist -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-300 font-mono uppercase tracking-wider">
                            Actionable Checklist ({{ count($seoData['recommendations'] ?? []) }})
                        </h4>

                        <div class="space-y-2">
                            @forelse($seoData['recommendations'] ?? [] as $rec)
                                @php
                                    $badge = match($rec['type']) {
                                        'good' => '🟢 Passed',
                                        'warning' => '🟡 Warning',
                                        default => '🔴 Critical',
                                    };
                                    $border = match($rec['type']) {
                                        'good' => 'border-emerald-500/20 bg-emerald-950/10 text-slate-300',
                                        'warning' => 'border-yellow-500/20 bg-yellow-950/10 text-slate-300',
                                        default => 'border-red-500/20 bg-red-950/10 text-slate-200',
                                    };
                                @endphp
                                <div class="p-3 rounded-xl border {{ $border }} text-xs space-y-1 leading-relaxed">
                                    <div class="flex items-center justify-between text-[10px] font-mono">
                                        <span class="font-bold text-slate-400 uppercase">{{ $rec['category'] }}</span>
                                        <span>{{ $badge }}</span>
                                    </div>
                                    <p>{{ $rec['text'] }}</p>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-slate-500">
                                    Write some content to see live SEO recommendations.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer -->
                <div class="pt-4 mt-6 border-t border-[#30363d]">
                    <button 
                        type="button" 
                        wire:click="$set('showSeoDrawer', false)" 
                        class="w-full py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-white/10 text-slate-300 hover:text-white text-xs font-semibold transition-all cursor-pointer"
                    >
                        Close SEO Panel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Public & Protected Sharing Modal -->
    @if($showShareModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl glass-elevated border border-white/15 p-6 sm:p-8 space-y-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-lg text-indigo-300 shadow-inner">
                            🔗
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Share & Publish Document</h3>
                            <p class="text-[11px] text-slate-400">Generate a live public reader link with optional password security.</p>
                        </div>
                    </div>
                    <button wire:click="$set('showShareModal', false)" class="text-slate-400 hover:text-white p-1 cursor-pointer">✕</button>
                </div>

                @if(session('share_status'))
                    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold">
                        {{ session('share_status') }}
                    </div>
                @endif

                <!-- Active Share Link Presentation -->
                @if($isShareActive)
                    <div class="space-y-3 p-4 rounded-2xl bg-indigo-950/40 border border-indigo-500/30" x-data="{ copied: false }">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-indigo-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Live Link Active
                            </span>
                            <span class="text-slate-400 font-mono text-[10px]">Views: <strong>{{ $shareViewCount }}</strong></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                readonly 
                                value="{{ $shareUrl }}" 
                                class="flex-1 bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs font-mono text-slate-300 focus:outline-none select-all"
                            />
                            <button 
                                type="button" 
                                @click="navigator.clipboard.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all cursor-pointer shrink-0"
                            >
                                <span x-show="!copied">📋 Copy</span>
                                <span x-show="copied" class="text-emerald-300">✓ Copied!</span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <a href="{{ $shareUrl }}" target="_blank" class="text-[11px] text-indigo-400 hover:text-indigo-300 hover:underline">
                                ↗ Open in Reader Mode
                            </a>
                            <button 
                                type="button" 
                                wire:click="revokeShare" 
                                class="text-[11px] text-red-400 hover:text-red-300 font-medium hover:underline cursor-pointer"
                            >
                                ✕ Revoke Share Link
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Share Settings Form -->
                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-medium mb-1">Password Protection (Optional)</label>
                        <input 
                            type="password" 
                            wire:model="sharePassword" 
                            placeholder="Leave blank for open access..."
                            class="w-full bg-slate-900 border border-white/10 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-medium mb-1">Expiration</label>
                            <select 
                                wire:model="shareExpiryDays" 
                                class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500"
                            >
                                <option value="">Never Expires</option>
                                <option value="1">1 Day</option>
                                <option value="7">7 Days</option>
                                <option value="30">30 Days</option>
                            </select>
                        </div>

                        <div class="space-y-2 pt-2">
                            <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                <input type="checkbox" wire:model="shareAllowCopy" class="rounded bg-slate-900 border-white/10 text-indigo-600 focus:ring-0">
                                <span>Allow Readers to Copy</span>
                            </label>
                            <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                <input type="checkbox" wire:model="shareAllowDownload" class="rounded bg-slate-900 border-white/10 text-indigo-600 focus:ring-0">
                                <span>Allow Formats Download</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                    <button 
                        type="button" 
                        wire:click="$set('showShareModal', false)" 
                        class="px-4 py-2 rounded-xl text-slate-400 hover:text-white text-xs font-semibold cursor-pointer"
                    >
                        Close
                    </button>
                    <button 
                        type="button" 
                        wire:click="createOrUpdateShare" 
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all cursor-pointer"
                    >
                        {{ $isShareActive ? 'Update Share Settings' : 'Generate Public Link' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
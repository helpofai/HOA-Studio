{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Alpine JS Engine Partial
|--------------------------------------------------------------------------
*/
--}}

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('documentEditorComponent', (config) => ({
        editorInstance: null,
        isDirty: false,
        autosaveTimeout: null,
        leftPanelLoaded: false,
        rightPanelLoaded: false,
        
        debouncedAutosave() {
            if (this.autosaveTimeout) clearTimeout(this.autosaveTimeout);
            this.autosaveTimeout = setTimeout(() => {
                this.performAutosave();
            }, 2000); // 2 second debounce
        },

        performAutosave() {
            if (!this.isDirty) return;
            
            const html = this.editorInstance ? this.editorInstance.getHTML() : '';
            Livewire.dispatch('autosave', { html: html, json: null });
            this.saveLocalDraft(html);
            this.hasUnsavedChanges = false;
            this.isDirty = false;
        },
        selectedText: '',
hasSelection: false,
        wordCount: 0,
        characterCount: 0,
        readingTime: 1,

        showLeftPanel: true,
        showRightPanel: true,
        rightTab: 'seo',
        targetWordGoal: 1200,
        serpView: 'desktop',

        caps: { richText: true, blocks: true, markdown: false, undoRedo: true },
        showLossyWarning: false,
        pendingEngine: null,
        lossyEngines: { plaintext: true, html: true },

        aiPrompt: '',
        inlineAiPrompt: '',
        inlineAiPlacement: 'replace', // 'replace' or 'insert_below'
        showInlineAiPrompt: false,
        aiModel: 'Auto (OmniRoute)',
        aiContext: {
            currentDoc: true,
            project: true,
            brandVoice: true,
            knowledgeBase: true,
            webResearch: false
        },
        aiHistory: [],

        // Real-Time Token & Latency Telemetry
        sendTokens: 0,
        receivedTokens: 0,
        totalTokens: 0,
        streamLatencyMs: 12,
        streamSpeedTokSec: 0,

        // 15-Stage Enterprise Production Pipeline Matrix
        pipelineStages: {
            search_intent: { icon: '🔍', label: 'Search Intent Analysis', category: 'Analysis', enabled: true },
            keyword_research: { icon: '🏷️', label: 'Keyword & Entity Research', category: 'Research', enabled: true },
            serp_competitor: { icon: '🌐', label: 'SERP / Competitor Analysis', category: 'Intelligence', enabled: true },
            content_gaps: { icon: '🎯', label: 'Content Gap Analysis', category: 'Strategy', enabled: true },
            article_outline: { icon: '📑', label: 'Article Outline Architecture', category: 'Structure', enabled: true },
            section_generation: { icon: '✍️', label: 'Section-by-Section Generation', category: 'Drafting', enabled: true },
            fact_verification: { icon: '🛡️', label: 'Fact & Source Verification', category: 'Accuracy', enabled: true },
            originality_check: { icon: '✨', label: 'Originality & Novelty Check', category: 'Uniqueness', enabled: true },
            seo_optimization: { icon: '⌁', label: 'SEO Deep Optimization', category: 'Optimization', enabled: true },
            readability_opt: { icon: '📖', label: 'Readability & Flow Optimization', category: 'Refinement', enabled: true },
            internal_links: { icon: '🔗', label: 'Internal Link Suggestions', category: 'Linking', enabled: true },
            media_suggestions: { icon: '🖼️', label: 'Media & Asset Suggestions', category: 'Assets', enabled: true },
            schema_generation: { icon: '📋', label: 'Schema JSON-LD Generation', category: 'Schema', enabled: true },
            quality_audit: { icon: '🏆', label: 'Final 10-Point Quality Audit', category: 'Audit', enabled: true },
            publish_assembly: { icon: '🚀', label: 'Publish-Ready Assembly', category: 'Publish', enabled: true },
        },

        getSelectedStagesCount() {
            return Object.values(this.pipelineStages).filter(s => s.enabled).length;
        },

        setPipelinePreset(preset) {
            const allKeys = Object.keys(this.pipelineStages);
            if (preset === 'all') {
                allKeys.forEach(k => this.pipelineStages[k].enabled = true);
                this.addLog('AI', 'Selected all 15 pipeline stages.');
            } else if (preset === 'seo') {
                allKeys.forEach(k => {
                    this.pipelineStages[k].enabled = ['search_intent', 'keyword_research', 'content_gaps', 'article_outline', 'section_generation', 'seo_optimization', 'schema_generation', 'quality_audit'].includes(k);
                });
                this.addLog('AI', 'Applied SEO Authority pipeline preset (8 stages).');
            } else if (preset === 'quick') {
                allKeys.forEach(k => {
                    this.pipelineStages[k].enabled = ['article_outline', 'section_generation', 'readability_opt'].includes(k);
                });
                this.addLog('AI', 'Applied Quick Draft pipeline preset (3 stages).');
            } else if (preset === 'clear') {
                allKeys.forEach(k => this.pipelineStages[k].enabled = false);
                this.addLog('AI', 'Cleared all pipeline stages.');
            }
        },

        isTransforming: false,
        activeAction: null,
        routedModel: 'OmniRoute',
        liveAiStreamText: '',
        showAiStreamBanner: false,
        abortController: null,
        streamDecorationId: null,

        showContextMenu: false,
        contextMenuX: 0,
        contextMenuY: 0,
        showSlashMenu: false,
        slashMenuX: 0,
        slashMenuY: 0,
        docOutline: [],

        // Terminal UI AI Telemetry Logs
        aiLogs: [],
        logFilter: 'ALL',

        get filteredLogs() {
            if (this.logFilter === 'ALL') return this.aiLogs;
            if (this.logFilter === 'AI') return this.aiLogs.filter(l => l.level === 'AI' || l.level === 'STREAM' || l.level === 'GENERATE');
            if (this.logFilter === 'SEO') return this.aiLogs.filter(l => l.level === 'SEO');
            if (this.logFilter === 'ERR') return this.aiLogs.filter(l => l.level === 'ERROR' || l.level === 'ERR' || l.level === 'WARN');
            return this.aiLogs;
        },

        addLog(level, msg) {
            const now = new Date();
            const timeStr = now.toTimeString().split(' ')[0];
            this.aiLogs.unshift({ time: timeStr, level: level.toUpperCase(), msg: msg });
            if (this.aiLogs.length > 50) this.aiLogs.pop();
            this.$nextTick(() => {
                const screen = document.getElementById('terminal-logs-screen');
                if (screen) screen.scrollTop = 0;
            });
        },

        clearLogs() {
            this.aiLogs = [];
            this.addLog('SYSTEM', 'Log buffer cleared.');
        },

        showRestoredDraftBanner: false,
        restoredDraftTime: '',
        restoredWordCount: 0,
        hasUnsavedChanges: false,
        serverBackupContent: config.initialContent || '',

        init() {
            this.addLog('SYSTEM', 'OmniRoute Gateway v2.0 kernel initialized.');
            this.addLog('ENGINE', 'Editor driver mounted: ' + (config.editorType || 'tiptap').toUpperCase());
            this.addLog('SEO', 'Real-time semantic SEO analyzer active.');

            // Check for Local Draft Recovery
            const localDraftKey = 'hoa_doc_draft_' + config.documentId;
            const savedDraft = localStorage.getItem(localDraftKey);
            let contentToLoad = config.initialContent || '<p></p>';

            if (savedDraft) {
                try {
                    const parsed = JSON.parse(savedDraft);
                    const serverLength = (config.initialContent || '').replace(/<[^>]*>/g, '').trim().length;
                    const draftLength = (parsed.html || '').replace(/<[^>]*>/g, '').trim().length;

                    if (draftLength > serverLength && draftLength > 50) {
                        contentToLoad = parsed.html;
                        this.showRestoredDraftBanner = true;
                        this.restoredDraftTime = new Date(parsed.timestamp).toLocaleTimeString();
                        this.restoredWordCount = (parsed.html.replace(/<[^>]*>/g, '').trim().split(/\s+/).filter(Boolean)).length;
                        this.addLog('SYSTEM', '✦ Auto-recovered unsaved local draft (' + this.restoredWordCount + ' words)');
                        
                        setTimeout(() => {
                            Livewire.dispatch('autosave', { html: parsed.html, json: null });
                        }, 1000);
                    }
                } catch (e) {}
            }

            this.initEditor(contentToLoad);

            // Dynamic Engine Switching Listener
            Livewire.on('editor:reload', (event) => {
                const newEngine = (event && event.editorType) ? event.editorType : (event && event[0] && event[0].editorType ? event[0].editorType : null);
                if (newEngine) {
                    config.editorType = newEngine;
                }
                const currentHtml = this.editorInstance ? this.editorInstance.getHTML() : config.initialContent;
                this.initEditor(currentHtml);
                this.addLog('ENGINE', 'Mounted active driver: ' + (config.editorType || 'tiptap').toUpperCase());
            });

            // Browser Accidental Close / Reload Protection Guard
            window.addEventListener('beforeunload', (e) => {
                if (this.isTransforming || this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes or active AI generation. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });

            Livewire.on('editor:setContent', ({ content }) => {
                if (this.editorInstance) this.editorInstance.setContent(content);
                this.addLog('INFO', 'Canvas content reset via server action.');
            });

            window.addEventListener('editor:contextmenu', (e) => {
                const detail = e.detail || {};
                this.openContextMenu(detail);
            });

            window.addEventListener('click', (e) => {
                if (e.target && e.target.closest('[x-show="showContextMenu"]')) return;
                this.closeContextMenu();
            });

            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    Livewire.dispatch('saveExplicitSnapshot');
                    this.addLog('INFO', 'Manual snapshot triggered via keyboard shortcut.');
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    this.openInlineAiPrompt();
                }
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'f') {
                    e.preventDefault();
                    this.toggleFocusMode();
                }
                if (e.key === 'Escape') {
                    this.closeContextMenu();
                    this.showInlineAiPrompt = false;
                    this.showSlashMenu = false;
                }
            });

            window.addEventListener('tiptap:slash', (e) => {
                this.slashMenuX = Math.min(e.detail.x || (window.innerWidth / 2 - 140), window.innerWidth - 300);
                this.slashMenuY = Math.min(e.detail.y || 200, window.innerHeight - 400);
                this.showSlashMenu = true;
                this.addLog('AI', 'Slash commands palette triggered at cursor.');
            });
        },

        executeSlashAction(action) {
            this.showSlashMenu = false;
            const ed = this.getEditor();
            if (action === 'ask_ai') {
                this.openInlineAiPrompt();
            } else if (action === 'continue_writing') {
                this.triggerAiTransform('continue_writing');
            } else if (action === 'generate_outline') {
                this.triggerAiTransform('generate_outline');
            } else if (action === 'quick_answer') {
                this.triggerAiTransform('quick_answer');
            } else if (action === 'faq') {
                this.triggerAiTransform('generate_faq');
            } else if (action === 'comparison_table') {
                this.triggerAiTransform('comparison_table');
            } else if (action === 'tip') {
                ed?.insertCallout?.('tip');
            } else if (action === 'warning') {
                ed?.insertCallout?.('warning');
            } else if (action === 'proscons') {
                ed?.insertProsCons?.();
            } else if (action === 'faq_accordion') {
                ed?.insertFaqAccordion?.();
            } else if (action === 'trust_box') {
                ed?.insertTrustBox?.();
            } else if (action === 'step_timeline') {
                ed?.insertStepTimeline?.();
            } else if (action === 'h1') {
                this.applyFormat('heading', 1);
            } else if (action === 'h2') {
                this.applyFormat('heading', 2);
            } else if (action === 'h3') {
                this.applyFormat('heading', 3);
            } else if (action === 'h4') {
                this.applyFormat('heading', 4);
            } else if (action === 'bullet') {
                this.applyFormat('bulletList');
            } else if (action === 'number') {
                this.applyFormat('orderedList');
            } else if (action === 'task') {
                ed?.toggleTaskList?.();
            } else if (action === 'table') {
                ed?.insertTable?.({ rows: 3, cols: 3, withHeaderRow: true });
            } else if (action === 'quote') {
                this.applyFormat('blockquote');
            } else if (action === 'code') {
                this.applyFormat('codeBlock');
            } else if (action === 'divider') {
                this.applyFormat('hr');
            }
            this.addLog('EDITOR', 'Executed command: ' + action);
        },

        getEditor() {
            if (!window.hoaEditorInstance) return null;
            return typeof Alpine !== 'undefined' && Alpine.raw 
                ? Alpine.raw(window.hoaEditorInstance) 
                : window.hoaEditorInstance;
        },

        // Active Formatting Status Map for Toolbar Highlighting
        activeFormats: {
            heading1: false,
            heading2: false,
            heading3: false,
            heading4: false,
            bold: false,
            italic: false,
            underline: false,
            strike: false,
            subscript: false,
            superscript: false,
            highlight: false,
            bulletList: false,
            orderedList: false,
            taskList: false,
            table: false,
            blockquote: false,
            codeBlock: false
        },

        updateActiveFormats() {
            const ed = this.getEditor();
            if (!ed) return;
            this.activeFormats = {
                heading1: ed.isActive?.('heading', { level: 1 }) ?? false,
                heading2: ed.isActive?.('heading', { level: 2 }) ?? false,
                heading3: ed.isActive?.('heading', { level: 3 }) ?? false,
                heading4: ed.isActive?.('heading', { level: 4 }) ?? false,
                bold: ed.isActive?.('bold') ?? false,
                italic: ed.isActive?.('italic') ?? false,
                underline: ed.isActive?.('underline') ?? false,
                strike: ed.isActive?.('strike') ?? false,
                subscript: ed.isActive?.('subscript') ?? false,
                superscript: ed.isActive?.('superscript') ?? false,
                highlight: ed.isActive?.('highlight') ?? false,
                bulletList: ed.isActive?.('bulletList') ?? false,
                orderedList: ed.isActive?.('orderedList') ?? false,
                taskList: ed.isActive?.('taskList') ?? false,
                table: ed.isActive?.('table') ?? false,
                blockquote: ed.isActive?.('blockquote') ?? false,
                codeBlock: ed.isActive?.('codeBlock') ?? false
            };
        },

        initEditor(customInitial = null) {
            const currentEd = this.getEditor();
            if (currentEd) {
                try {
                    currentEd.destroy();
                } catch (e) {}
            }

            const driverType = config.editorType || 'tiptap';
            if (!window.HOA_EditorManager) {
                console.warn('[initEditor] HOA_EditorManager not ready, retrying in 50ms...');
                setTimeout(() => this.initEditor(customInitial), 50);
                return;
            }

            // Create and store on window.hoaEditorInstance outside Alpine reactive proxy
            window.hoaEditorInstance = window.HOA_EditorManager.createEditor(driverType, 'tiptap-content-target', {
                initialContent: customInitial || config.initialContent || '<p></p>',
                placeholder: 'Type / for AI commands or press Ctrl+K to ask AI...',
                onStatsChange: (stats) => {
                    this.wordCount = stats.words;
                    this.characterCount = stats.characters;
                    this.readingTime = Math.max(1, Math.ceil(stats.words / 200));
                    this.updateOutline();
                    
                    // Trigger dirty state and debounced autosave
                    this.isDirty = true;
                    this.debouncedAutosave();
                },
                onSelectionChange: ({ selectedText, isEmpty }) => {
                    this.selectedText = selectedText;
                    this.hasSelection = !isEmpty && selectedText.trim().length > 0;
                    this.updateActiveFormats();
                },
                onFormatChange: () => {
                    this.updateActiveFormats();
                },
                onAutosave: (data) => {
                    // Prevent duplicate autosaves if our new debounced mechanism is active
                    if (this.isDirty) return; 
                    
                    Livewire.dispatch('autosave', { html: data.html, json: data.json ?? null });
                    this.saveLocalDraft(data.html);
                    this.hasUnsavedChanges = false;
                    this.addLog('INFO', 'Autosaved (' + data.words + ' words, ' + data.chars + ' chars)');
                }
            });

            const ed = this.getEditor();
            if (ed) {
                const initialText = ed.getText() || '';
                this.wordCount = initialText.trim().split(/\s+/).filter(Boolean).length;
                this.characterCount = initialText.length;
                this.readingTime = Math.max(1, Math.ceil(this.wordCount / 200));

                if (ed.capabilities) {
                    this.caps = { ...this.caps, ...ed.capabilities };
                }
            }
            this.updateOutline();
            this.updateActiveFormats();
        },

        saveLocalDraft(html) {
            if (!config.documentId) return;
            const localDraftKey = 'hoa_doc_draft_' + config.documentId;
            localStorage.setItem(localDraftKey, JSON.stringify({
                html: html,
                timestamp: Date.now()
            }));
            this.hasUnsavedChanges = true;
        },

        dismissRestoredBanner() {
            this.showRestoredDraftBanner = false;
            const html = this.editorInstance ? this.editorInstance.getHTML() : '';
            Livewire.dispatch('autosave', { html: html, json: null });
            this.addLog('INFO', 'Confirmed & synced restored draft to cloud database.');
        },

        revertToServerBackup() {
            if (this.editorInstance && this.serverBackupContent) {
                this.editorInstance.setContent(this.serverBackupContent);
                const localDraftKey = 'hoa_doc_draft_' + config.documentId;
                localStorage.removeItem(localDraftKey);
                this.showRestoredDraftBanner = false;
                this.addLog('WARN', 'Reverted to cloud database version.');
            }
        },

        openInlineAiPrompt() {
            this.showInlineAiPrompt = true;
            this.addLog('AI', 'In-canvas AI prompt bar opened.');
            this.$nextTick(() => {
                const el = document.getElementById('inline-ai-input');
                if (el) el.focus();
            });
        },

        submitInlineAiPrompt() {
            if (!this.inlineAiPrompt.trim()) return;
            const prompt = this.inlineAiPrompt;
            const placement = (this.hasSelection && this.selectedText) ? this.inlineAiPlacement : 'auto';
            this.inlineAiPrompt = '';
            this.showInlineAiPrompt = false;
            this.triggerAiTransform('custom', prompt, placement);
        },

        openContextMenu(event) {
            if (!event) return;
            const clientX = (event.clientX !== undefined) ? event.clientX : (event.x !== undefined ? event.x : window.innerWidth / 2);
            const clientY = (event.clientY !== undefined) ? event.clientY : (event.y !== undefined ? event.y : window.innerHeight / 2);
            
            const menuWidth = 240;
            const menuHeight = 340;
            
            this.contextMenuX = Math.max(10, Math.min(clientX, window.innerWidth - menuWidth - 10));
            this.contextMenuY = Math.max(10, Math.min(clientY, window.innerHeight - menuHeight - 10));
            this.showContextMenu = true;
            this.addLog('INFO', 'AI Context menu opened at (' + this.contextMenuX + ', ' + this.contextMenuY + ')');
        },

        closeContextMenu() {
            this.showContextMenu = false;
        },

        updateOutline() {
            const ed = this.getEditor();
            if (!ed) return;
            const html = ed.getHTML ? ed.getHTML() : '';
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
                this.addLog('INFO', 'Navigated to heading: "' + text + '"');
                setTimeout(() => match.classList.remove('ring-2', 'ring-indigo-500/60', 'rounded-lg'), 1500);
            }
        },

        insertContentIntoCanvas(htmlContent, withHighlight = true) {
            const ed = this.getEditor();
            if (!ed) return;
            try {
                if (typeof ed.insertContent === 'function') {
                    ed.insertContent(htmlContent);
                } else if (typeof ed.setContent === 'function') {
                    const current = ed.getHTML ? ed.getHTML() : '';
                    ed.setContent(current + '<p></p>' + htmlContent);
                }

                const finalHtml = ed.getHTML ? ed.getHTML() : htmlContent;
                Livewire.dispatch('autosave', { html: finalHtml, json: null });
                this.saveLocalDraft(finalHtml);
                this.updateOutline();
                this.updateActiveFormats();

                const canvas = document.getElementById('tiptap-content-target');
                if (canvas) {
                    canvas.classList.add('ring-2', 'ring-indigo-500/50', 'transition-all', 'duration-500');
                    setTimeout(() => canvas.classList.remove('ring-2', 'ring-indigo-500/50'), 2500);
                }

                this.addLog('GENERATE', 'Content inserted into active canvas (' + this.routedModel + ')');
            } catch (err) {
                console.error('[insertContentIntoCanvas] Safe fallback:', err);
                const current = ed.getHTML ? ed.getHTML() : '';
                ed.setContent(current + '<p></p>' + htmlContent);
                this.addLog('WARN', 'Inserted via resilient content reset fallback.');
            }
        },

        applyLiveStreamNow() {
            if (!this.liveAiStreamText || this.liveAiStreamText.trim().length === 0) return;
            const textToInsert = this.liveAiStreamText;
            const ed = this.getEditor();
            
            if (ed) {
                if (this.hasSelection && typeof ed.replaceSelection === 'function') {
                    ed.replaceSelection(textToInsert);
                } else {
                    const currentHtml = ed.getHTML ? ed.getHTML() : '';
                    const isDocEmpty = !currentHtml || currentHtml === '<p></p>' || currentHtml === '<p><br></p>' || currentHtml.trim().length === 0;
                    if (isDocEmpty) {
                        ed.setContent(textToInsert);
                    } else {
                        this.insertContentIntoCanvas(textToInsert, true);
                    }
                }
                const finalHtml = ed.getHTML ? ed.getHTML() : textToInsert;
                Livewire.dispatch('autosave', { html: finalHtml, json: null });
                this.saveLocalDraft(finalHtml);
                this.updateOutline();
                this.updateActiveFormats();
            }
            
            this.addLog('AI', 'Applied live stream tokens into canvas (' + textToInsert.length + ' chars)');
            this.liveAiStreamText = '';
        },

        abortAiTransform() {
            if (this.abortController) {
                this.abortController.abort();
                this.abortController = null;
            }
            this.isTransforming = false;
            // Removed automatic apply to allow user to decide
            this.addLog('WARN', 'AI transformation stopped by user.');
        },

        applyFormat(action, param = null) {
            const instance = this.getEditor();
            if (!instance) {
                console.warn('[applyFormat] No editor instance available!');
                return;
            }
            if (action === 'heading')          instance.toggleHeading?.(param);
            else if (action === 'bold')        instance.toggleBold?.();
            else if (action === 'italic')      instance.toggleItalic?.();
            else if (action === 'underline')   instance.toggleUnderline?.();
            else if (action === 'strike')      instance.toggleStrike?.();
            else if (action === 'subscript')   instance.toggleSubscript?.();
            else if (action === 'superscript') instance.toggleSuperscript?.();
            else if (action === 'highlight')   instance.toggleHighlight?.();
            else if (action === 'bulletList')  instance.toggleBulletList?.();
            else if (action === 'orderedList') instance.toggleOrderedList?.();
            else if (action === 'taskList')    instance.toggleTaskList?.();
            else if (action === 'blockquote')  instance.toggleBlockquote?.();
            else if (action === 'codeBlock')   instance.toggleCodeBlock?.();
            else if (action === 'hr')          instance.setHorizontalRule?.();
            else if (action === 'undo')        instance.undo?.();
            else if (action === 'redo')        instance.redo?.();
            this.updateActiveFormats();
            this.updateOutline();
        },

        insertMarkdownHeading() { const ed = this.getEditor(); if (ed && ed.insertContent) ed.insertContent('\n## '); },
        insertMarkdownBold() { const ed = this.getEditor(); if (ed && ed.insertContent) ed.insertContent('**bold**'); },
        insertMarkdownTodo() { const ed = this.getEditor(); if (ed && ed.insertContent) ed.insertContent('- [ ] '); },

        insertImageFromUrl() {
            const ed = this.getEditor();
            if (!ed) return;
            const url = prompt('Enter image URL:', 'https://');
            if (url && url !== 'https://') {
                ed.setImage?.({ src: url, alt: 'Inserted image' });
                this.addLog('MEDIA', 'Image inserted: ' + url);
            }
        },

        toggleFocusMode() {
            if (this.showLeftPanel || this.showRightPanel) {
                this.showLeftPanel = false;
                this.showRightPanel = false;
                this.addLog('INFO', 'Zen Focus Mode enabled.');
            } else {
                this.showLeftPanel = true;
                this.showRightPanel = true;
                this.addLog('INFO', 'Zen Focus Mode exited.');
            }
        },

        requestEngineSwitch(targetEngine) {
            const currentRich = this.caps.richText || this.caps.blocks;
            if (currentRich && this.lossyEngines[targetEngine]) {
                this.pendingEngine = targetEngine;
                this.showLossyWarning = true;
                this.addLog('WARN', 'Lossy engine switch warning prompted for: ' + targetEngine);
            } else {
                Livewire.dispatch('switchEditorType', { type: targetEngine });
                this.addLog('ENGINE', 'Switching engine to: ' + targetEngine);
            }
        },
        confirmLossySwitch() {
            if (this.pendingEngine) {
                Livewire.dispatch('switchEditorType', { type: this.pendingEngine });
                this.addLog('ENGINE', 'Confirmed lossy switch to: ' + this.pendingEngine);
            }
            this.showLossyWarning = false;
            this.pendingEngine = null;
        },
        cancelLossySwitch() {
            this.showLossyWarning = false;
            this.pendingEngine = null;
            this.addLog('INFO', 'Cancelled lossy switch.');
        },

        aiErrorMessage: '',
        activeSwarmAgent: null,
        swarmStepIndex: 0,
        swarmTotalSteps: 5,
        swarmStatusMessage: '',

        async runMultiAgentPipeline(userTopic = '') {
            const prompt = userTopic || this.aiPrompt;
            if (!prompt || !prompt.trim()) {
                this.addLog('WARN', 'Please provide a topic or prompt for the Multi-Agent Swarm.');
                return;
            }

            const ed = this.getEditor();
            if (!ed) return;

            this.isTransforming = true;
            this.showAiStreamBanner = true;
            this.activeAction = 'multi_agent_swarm';
            this.swarmTotalSteps = 5;

            try {
                // STEP 1: RESEARCHER & STRATEGIST (Grounding & SEO Target)
                this.swarmStepIndex = 1;
                this.activeSwarmAgent = 'researcher';
                this.swarmStatusMessage = 'Agent 2 & 3 (Researcher & SEO Strategist) analyzing search intent & vector cache...';
                this.addLog('AGENT', '🎯 Swarm Step 1: Agent [Researcher & SEO Strategist] querying Vector Knowledge Base.');
                
                let targetKw = this.targetKeyword;
                if (!targetKw) {
                    const kwMatch = prompt.match(/(?:for|on|about|review|guide to)\s+([^,.;\n]+)/i);
                    targetKw = kwMatch ? kwMatch[1].trim() : prompt.split(/\s+/).slice(0, 4).join(' ');
                    this.targetKeyword = targetKw;
                    Livewire.dispatch('applyTargetKeyword', { keyword: targetKw });
                }

                // STEP 2: OUTLINER & ORCHESTRATOR (Title & Heading Tree)
                this.swarmStepIndex = 2;
                this.activeSwarmAgent = 'outliner';
                this.swarmStatusMessage = 'Agent 4 (Outline Architect) creating H1/H2/H3 hierarchy & optimizing Title...';
                this.addLog('AGENT', '📑 Swarm Step 2: Agent [Outline Architect] structuring headings tree.');

                const titleResp = await fetch(config.transformRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                    body: JSON.stringify({
                        text: prompt,
                        type: 'seo_fix_title',
                        custom_instruction: "Generate a high-CTR, SEO-optimized title for: '" + prompt + "' frontloading '" + targetKw + "'",
                        model: this.aiModel
                    })
                });
                const titleData = await titleResp.json();
                if (titleData.success && titleData.result) {
                    const cleanTitle = titleData.result.replace(/^["'#\s]+|["'\s]+$/g, '').trim();
                    this.title = cleanTitle;
                    Livewire.dispatch('applyTitle', { title: cleanTitle });
                    this.addLog('SEO', 'Agent [Outline Architect] applied optimized Title: "' + cleanTitle + '"');
                }

                // STEP 3: DRAFTSMAN (Full Technical Deep-Dive Synthesis)
                this.swarmStepIndex = 3;
                this.activeSwarmAgent = 'draftsman';
                this.swarmStatusMessage = 'Agent 5 (Deep Section Draftsman) drafting comprehensive technical sections...';
                this.addLog('AGENT', '✍️ Swarm Step 3: Agent [Draftsman] synthesizing comprehensive sections...');

                await this.triggerAiTransform('custom', "Write an authoritative, technical long-form masterclass guide on: '" + prompt + "'. Include introduction with quick answer box, structured H2 and H3 headings, technical architecture, and real-world implementation details.", 'document');

                // STEP 4: RICH MEDIA & DATA ENGINEER (Comparison Table & FAQ Block)
                this.swarmStepIndex = 4;
                this.activeSwarmAgent = 'rich_media';
                this.swarmStatusMessage = 'Agent 6 (Rich Media Engineer) generating technical comparison table & FAQ schema...';
                this.addLog('AGENT', '▦ Swarm Step 4: Agent [Rich Media & Data Engineer] building comparison table & schema FAQ.');

                const tableResp = await fetch(config.transformRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                    body: JSON.stringify({
                        text: ed.getText().substring(0, 1500),
                        type: 'comparison_table',
                        custom_instruction: "Create a feature comparison matrix table with technical specs, pros/cons, and metrics for '" + targetKw + "'",
                        model: this.aiModel
                    })
                });
                const tableData = await tableResp.json();
                if (tableData.success && tableData.result) {
                    this.insertContentIntoCanvas('<p></p>' + tableData.result, true);
                    this.addLog('ASSETS', 'Agent [Rich Media Engineer] inserted interactive comparison table.');
                }

                // STEP 5: RANK MATH 100/100 & META OPTIMIZER (Meta Description & Final Assembly)
                this.swarmStepIndex = 5;
                this.activeSwarmAgent = 'rankmath_optimizer';
                this.swarmStatusMessage = 'Agent 8 & 10 (Rank Math Optimizer & Assembler) finalizing 100/100 SEO & Meta...';
                this.addLog('AGENT', '⌁ Swarm Step 5: Agent [Rank Math Optimizer] generating meta description & verifying SEO score.');

                const metaResp = await fetch(config.transformRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                    body: JSON.stringify({
                        text: ed.getText().substring(0, 1200),
                        type: 'seo_fix_meta',
                        custom_instruction: "Generate a punchy 155-character meta description with focus keyword '" + targetKw + "'",
                        model: this.aiModel
                    })
                });
                const metaData = await metaResp.json();
                if (metaData.success && metaData.result) {
                    const cleanMeta = metaData.result.replace(/^["'\s]+|["'\s]+$/g, '').trim();
                    this.metaDescription = cleanMeta;
                    Livewire.dispatch('applyMetaDescription', { metaDescription: cleanMeta });
                    this.addLog('SEO', 'Agent [Rank Math Optimizer] generated Meta Description (' + cleanMeta.length + ' chars).');
                }

                const finalHtml = ed.getHTML();
                Livewire.dispatch('autosave', { html: finalHtml, json: null });
                this.saveLocalDraft(finalHtml);
                this.updateOutline();
                this.updateActiveFormats();
                this.addLog('SYSTEM', '🚀 Multi-Agent Swarm successfully published complete human-grade article!');
            } catch (e) {
                this.aiErrorMessage = e.message;
                this.addLog('ERROR', 'Swarm execution interrupted: ' + e.message);
            } finally {
                this.isTransforming = false;
                this.activeSwarmAgent = null;
                this.swarmStatusMessage = '';
            }
        },

        async applyTargetedIntelligenceFix(checkId, title, aiPrompt, targetType = 'insert') {
            this.closeContextMenu();
            this.showInlineAiPrompt = false;
            this.aiErrorMessage = '';
            const ed = this.getEditor();
            if (!ed) return;

            this.isTransforming = true;
            this.activeAction = checkId;
            this.showAiStreamBanner = true;
            this.liveAiStreamText = '';
            this.abortController = new AbortController();

            const currentFullHtml = ed.getHTML ? ed.getHTML() : '';
            const currentText = ed.getText ? ed.getText() : '';

            // 1. SURGICAL TARGET: DOCUMENT TITLE
            if (targetType === 'title' || checkId === 'kw_in_title' || checkId === 'kw_at_beginning_of_title' || checkId === 'title_has_number' || checkId === 'title_has_power_word') {
                this.addLog('SEO', 'Surgically optimizing Title for focus keyword: ' + this.targetKeyword);
                try {
                    const resp = await fetch(config.transformRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken
                        },
                        body: JSON.stringify({
                            text: this.title || currentText.substring(0, 300) || 'Untitled Document',
                            type: 'seo_fix_title',
                            custom_instruction: aiPrompt || ("Rewrite the title to naturally front-load '" + this.targetKeyword + "'"),
                            model: this.aiModel
                        })
                    });
                    const data = await resp.json();
                    if (data.success && data.result) {
                        const cleanTitle = data.result.replace(/^["'#\s]+|["'\s]+$/g, '').trim();
                        this.title = cleanTitle;
                        if (window.Livewire) {
                            Livewire.dispatch('applyTitle', { title: cleanTitle });
                        }
                        this.addLog('SEO', 'Updated Title to: ' + cleanTitle);
                    }
                } catch (e) {
                    this.aiErrorMessage = e.message;
                } finally {
                    this.isTransforming = false;
                    this.showAiStreamBanner = false;
                }
                return;
            }

            // 2. SURGICAL TARGET: META DESCRIPTION
            if (targetType === 'meta' || checkId === 'kw_in_meta') {
                this.addLog('SEO', 'Surgically generating Meta Description...');
                try {
                    const resp = await fetch(config.transformRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken
                        },
                        body: JSON.stringify({
                            text: currentText.substring(0, 1000) || this.title || 'Document Context',
                            type: 'seo_fix_meta',
                            custom_instruction: aiPrompt || ("Generate a 155-character meta description featuring '" + this.targetKeyword + "'"),
                            model: this.aiModel
                        })
                    });
                    const data = await resp.json();
                    if (data.success && data.result) {
                        const cleanMeta = data.result.replace(/^["'\s]+|["'\s]+$/g, '').trim();
                        this.metaDescription = cleanMeta;
                        if (window.Livewire) {
                            Livewire.dispatch('applyMetaDescription', { metaDescription: cleanMeta });
                        }
                        this.addLog('SEO', 'Updated Meta Description (' + cleanMeta.length + ' chars)');
                    }
                } catch (e) {
                    this.aiErrorMessage = e.message;
                } finally {
                    this.isTransforming = false;
                    this.showAiStreamBanner = false;
                }
                return;
            }

            // 3. SURGICAL TARGET: INTRODUCTION PARAGRAPHS ONLY (Preserves rest of canvas)
            if (targetType === 'intro' || checkId === 'kw_in_intro') {
                this.addLog('SEO', 'Surgically rewriting opening introduction paragraphs only...');
                
                const parser = new DOMParser();
                const docDom = parser.parseFromString(currentFullHtml || '<p></p>', 'text/html');
                const paragraphs = docDom.querySelectorAll('p');
                let openingHtml = '';
                let count = 0;
                paragraphs.forEach(p => {
                    if (count < 2) {
                        openingHtml += p.outerHTML;
                        count++;
                    }
                });
                if (!openingHtml) {
                    openingHtml = '<p>' + currentText.substring(0, 300) + '</p>';
                }

                try {
                    const resp = await fetch(config.transformRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken
                        },
                        body: JSON.stringify({
                            text: openingHtml,
                            type: 'seo_fix_intro',
                            custom_instruction: aiPrompt || ("Rewrite only these opening paragraphs to front-load '" + this.targetKeyword + "'"),
                            model: this.aiModel
                        })
                    });
                    const data = await resp.json();
                    if (data.success && data.result) {
                        const newIntro = data.result.trim();
                        
                        let remainingHtml = '';
                        let skipped = 0;
                        docDom.body.childNodes.forEach(node => {
                            if (node.nodeName.toLowerCase() === 'p' && skipped < 2) {
                                skipped++;
                            } else {
                                remainingHtml += (node.outerHTML || node.textContent || '');
                            }
                        });

                        const finalCleanHtml = newIntro + (remainingHtml ? '<p></p>' + remainingHtml : '');
                        ed.setContent(finalCleanHtml, true);
                        Livewire.dispatch('autosave', { html: finalCleanHtml, json: null });
                        this.saveLocalDraft(finalCleanHtml);
                        this.updateOutline();
                        this.addLog('SEO', 'Surgically updated introduction. Rest of document 100% preserved.');
                    }
                } catch (e) {
                    this.aiErrorMessage = e.message;
                } finally {
                    this.isTransforming = false;
                    this.showAiStreamBanner = false;
                }
                return;
            }

            // 4. SURGICAL TARGET: INSERT COMPONENT / BLOCK ONLY (Tables, FAQ, Citations, Callouts)
            this.addLog('SEO', 'Generating targeted section element [' + checkId + '] without rewriting document...');
            try {
                let promptType = 'custom';
                if (checkId === 'comparison_table' || checkId === 'rich_media') promptType = 'comparison_table';
                else if (checkId === 'generate_faq' || checkId === 'faq') promptType = 'generate_faq';
                else if (checkId === 'external_links') promptType = 'seo_fix_citations';
                else if (checkId === 'kw_in_subheadings' || checkId === 'headings_toc') promptType = 'seo_fix_subheadings';
                else if (checkId === 'quick_answer') promptType = 'quick_answer';

                const resp = await fetch(config.transformRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        text: this.hasSelection ? this.selectedText : currentText.substring(0, 1500),
                        type: promptType,
                        custom_instruction: aiPrompt || 'Generate only the requested component in clean HTML',
                        model: this.aiModel
                    })
                });

                const data = await resp.json();
                if (data.success && data.result) {
                    const blockToInsert = data.result.trim();
                    
                    if (this.hasSelection && typeof ed.replaceSelection === 'function') {
                        ed.replaceSelection(blockToInsert);
                        this.addLog('SEO', 'Surgically replaced selected paragraph with AI optimization.');
                    } else {
                        this.insertContentIntoCanvas(blockToInsert, true);
                        this.addLog('SEO', 'Surgically inserted ' + title + ' into document.');
                    }

                    const finalHtml = ed.getHTML ? ed.getHTML() : '';
                    Livewire.dispatch('autosave', { html: finalHtml, json: null });
                    this.saveLocalDraft(finalHtml);
                    this.updateOutline();
                }
            } catch (e) {
                this.aiErrorMessage = e.message;
            } finally {
                this.isTransforming = false;
                this.showAiStreamBanner = false;
            }
        },

        async triggerAiTransform(type, customInstruction = '', placementMode = 'auto') {
            this.closeContextMenu();
            this.showInlineAiPrompt = false;
            this.aiErrorMessage = '';
            const ed = this.editorInstance || window.hoaEditorInstance;
            const hadSelection = !!(this.hasSelection && this.selectedText && this.selectedText.trim().length > 0);
            const targetText = hadSelection ? this.selectedText : (ed ? ed.getText() : '');

            // Determine explicit surgical placement
            let effectivePlacement = placementMode;
            if (effectivePlacement === 'auto') {
                if (hadSelection) {
                    if (['continue', 'generate_faq', 'comparison_table', 'key_takeaways', 'quick_answer', 'action_items', 'insert_below'].includes(type)) {
                        effectivePlacement = 'insert_below';
                    } else {
                        effectivePlacement = 'replace_selection';
                    }
                } else {
                    effectivePlacement = 'document';
                }
            }
            
            this.isTransforming = true;
            this.activeAction = type;
            this.liveAiStreamText = '';
            this.showAiStreamBanner = true;
            this.abortController = new AbortController();
            const signal = this.abortController.signal;

            let promptToSend = customInstruction || this.aiPrompt;
            if (!promptToSend || !promptToSend.trim()) {
                promptToSend = type === 'custom' 
                    ? 'Write a comprehensive, in-depth technical deep-dive article with benchmarks, architecture, code, and FAQs.' 
                    : type;
            }

            this.sendTokens = Math.max(1, Math.round(promptToSend.length / 3.8));
            this.receivedTokens = 0;
            this.totalTokens = this.sendTokens;
            this.streamSpeedTokSec = 0;
            const startTime = performance.now();
            let firstTokenReceived = false;

            const selectedPipelineStages = Object.keys(this.pipelineStages).filter(k => this.pipelineStages[k].enabled);
            this.addLog('AI', 'Dispatched pipeline [' + type + '] (' + effectivePlacement + ') with ' + selectedPipelineStages.length + ' active stages to OmniRoute.');

            try {
                const response = await fetch(config.streamRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'text/event-stream'
                    },
                    body: JSON.stringify({
                        text: targetText || 'Document Context',
                        type: type,
                        custom_instruction: promptToSend,
                        model: this.aiModel,
                        pipeline_stages: selectedPipelineStages,
                        context: this.aiContext
                    }),
                    signal: this.abortController.signal
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.error || 'Server error while generating transformation.');
                }

                this.addLog('STREAM', 'SSE stream connected. Receiving real-time tokens...');
                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
                let buffer = '';
                let fullResult = '';

                const existingDocContent = (ed ? ed.getHTML() : '').trim();
                const isDocEmpty = !existingDocContent || 
                                   existingDocContent === '<p></p>' || 
                                   existingDocContent === '<p><br></p>' || 
                                   existingDocContent === '<p>Start building your block content...</p>';

                let lastCanvasUpdate = 0;

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    if (!firstTokenReceived) {
                        firstTokenReceived = true;
                        this.streamLatencyMs = Math.round(performance.now() - startTime);
                    }

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            try {
                                const parsed = JSON.parse(line.substring(6));
                                if (parsed.token) {
                                    fullResult += parsed.token;
                                }
                                if (parsed.model) this.routedModel = parsed.model;
                                if (parsed.result) {
                                    fullResult = parsed.result;
                                }
                                if (parsed.message) {
                                    this.aiErrorMessage = parsed.message;
                                }
                            } catch (e) {}
                        }
                    }

                    this.liveAiStreamText = fullResult;
                    this.receivedTokens = Math.max(1, Math.round(fullResult.length / 3.8));
                    this.totalTokens = this.sendTokens + this.receivedTokens;
                    const elapsedSec = (performance.now() - startTime) / 1000;
                    if (elapsedSec > 0.1) {
                        this.streamSpeedTokSec = Math.round(this.receivedTokens / elapsedSec);
                    }

                    // STREAM DIRECTLY INTO TIPTAP PROSEMIRROR CANVAS (Throttled for 60fps smoothness)
                    // Only stream into full canvas if not currently replacing a localized selection
                    const now = performance.now();
                    if (now - lastCanvasUpdate > 60 && ed && fullResult.length > 0 && effectivePlacement === 'document') {
                        lastCanvasUpdate = now;
                        if (isDocEmpty) {
                            ed.setContent(fullResult, false);
                        } else {
                            ed.setContent(existingDocContent + '<p></p>' + fullResult, false);
                        }

                        const targetEl = document.getElementById('tiptap-content-target');
                        if (targetEl && targetEl.scrollHeight - targetEl.scrollTop < 1200) {
                            targetEl.scrollTop = targetEl.scrollHeight;
                        }
                    }
                }

                this.isTransforming = false;
                this.liveAiStreamText = '';

                // FINAL PERSISTENCE & SURGICAL PLACEMENT
                if (fullResult.trim().length > 0 && ed) {
                    if (hadSelection && effectivePlacement === 'replace_selection') {
                        // 1. SURGICALLY REPLACE ONLY THE SELECTED TEXT (Rest of document 100% untouched)
                        if (typeof ed.replaceSelection === 'function') {
                            ed.replaceSelection(fullResult);
                        } else if (typeof ed.insertContent === 'function') {
                            ed.insertContent(fullResult);
                        }
                        this.addLog('AI', 'Surgically replaced selected text with AI transformation.');
                    } else if (hadSelection && effectivePlacement === 'insert_below') {
                        // 2. INSERT IMMEDIATELY BELOW SELECTION
                        this.insertContentIntoCanvas('<p></p>' + fullResult, true);
                        this.addLog('AI', 'Inserted AI section immediately below selected text.');
                    } else {
                        // 3. FULL CANVAS LEVEL GENERATION
                        const finalHtml = isDocEmpty 
                            ? fullResult 
                            : existingDocContent + '<p></p>' + fullResult;

                        ed.setContent(finalHtml, true);
                        this.addLog('GENERATE', 'Completed generation in canvas (' + fullResult.length + ' chars)');

                        // Inferred Document Title Auto-Update only if document was initially empty
                        if (isDocEmpty) {
                            const h1Match = fullResult.match(/<h1>(.*?)<\/h1>/i) || fullResult.match(/^#\s+(.*?)$/m);
                            if (h1Match && h1Match[1]) {
                                const extractedTitle = h1Match[1].replace(/<[^>]*>/g, '').trim();
                                if (extractedTitle) {
                                    Livewire.dispatch('updateTitle', { newTitle: extractedTitle });
                                    this.addLog('SEO', 'Auto-applied document title: "' + extractedTitle.substring(0, 30) + '..."');
                                }
                            }
                        }
                    }

                    const savedHtml = ed.getHTML ? ed.getHTML() : '';
                    Livewire.dispatch('autosave', { html: savedHtml, json: null });
                    this.saveLocalDraft(savedHtml);
                    this.updateOutline();
                    this.updateActiveFormats();
                }

                this.aiHistory.unshift({
                    id: Date.now(),
                    type: type.replace('_', ' ').toUpperCase(),
                    prompt: promptToSend.substring(0, 35) + '...',
                    tokens: this.totalTokens,
                    time: 'Just now'
                });

                if (type === 'custom') {
                    this.aiPrompt = '';
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error(err);
                    this.aiErrorMessage = err.message || 'AI Generation notice: Unable to complete request.';
                    this.addLog('ERROR', 'AI Execution notice: ' + err.message);
                    setTimeout(() => { this.aiErrorMessage = ''; }, 8000);
                }
            } finally {
                this.isTransforming = false;
                this.activeAction = null;
                this.abortController = null;
                setTimeout(() => {
                    if (!this.isTransforming) {
                        this.showAiStreamBanner = false;
                    }
                }, 3000);
            }
        }
    }));
});
</script>

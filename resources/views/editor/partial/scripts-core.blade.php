{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Core & Lifecycle
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

editorInstance: null,
isDirty: false,
autosaveTimeout: null,
leftPanelLoaded: false,
rightPanelLoaded: false,

// AI Provider/Model Selection
selectedProvider: '',
availableProviders: config.providers || [],
availableModels: config.initialModels || [],

async fetchModelsForProvider(providerSlug) {
    console.log('[AI Command Center] Fetching models for provider:', providerSlug);
    if (!providerSlug) {
        this.availableModels = config.initialModels || [];
        return;
    }
    try {
        const url = (config.modelsUrl || '/dashboard/api/ai/providers/models') + `?provider=${providerSlug}`;
        const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': config.csrfToken || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        const respText = await response.text();
        let data = {};
        try {
            data = JSON.parse(respText);
        } catch (e) {
            console.warn('[AI Command Center] Server returned non-JSON response for models:', respText.substring(0, 80));
            data = { success: false };
        }
        if (data.success && data.models && data.models.length > 0) {
            this.availableModels = data.models;
            if (!this.availableModels.some(m => m.id === this.aiModel)) {
                this.aiModel = this.availableModels[0].id;
            }
        } else {
            this.availableModels = (config.initialModels || []).filter(m => m.provider_slug === providerSlug);
        }
    } catch (err) {
        console.error('[AI Command Center] Failed to fetch models:', err);
        this.availableModels = (config.initialModels || []).filter(m => m.provider_slug === providerSlug);
    }
},

debouncedAutosave() {
    if (this.autosaveTimeout) clearTimeout(this.autosaveTimeout);
    this.autosaveTimeout = setTimeout(() => {
        this.performAutosave();
    }, 2000);
},

performAutosave() {
    if (!this.isDirty) return;
    
    const ed = this.getEditor();
    const html = ed && ed.getHTML ? ed.getHTML() : '';
    if (html) {
        Livewire.dispatch('autosave', { html: html, json: null });
        this.saveLocalDraft(html);
        this.hasUnsavedChanges = false;
        this.isDirty = false;
    }
},

selectedText: '',
hasSelection: false,
wordCount: 0,
characterCount: 0,
readingTime: 1,

showLeftPanel: localStorage.getItem('hoa_editor_show_left_panel') !== null ? (localStorage.getItem('hoa_editor_show_left_panel') === 'true') : true,
showRightPanel: localStorage.getItem('hoa_editor_show_right_panel') !== null ? (localStorage.getItem('hoa_editor_show_right_panel') === 'true') : true,
rightTab: 'seo',
targetWordGoal: 1200,
serpView: 'desktop',

toggleLeftPanel() {
    this.showLeftPanel = !this.showLeftPanel;
    localStorage.setItem('hoa_editor_show_left_panel', this.showLeftPanel);
},

toggleRightPanel() {
    this.showRightPanel = !this.showRightPanel;
    localStorage.setItem('hoa_editor_show_right_panel', this.showRightPanel);
},

caps: { richText: true, blocks: true, markdown: false, undoRedo: true },
showLossyWarning: false,
pendingEngine: null,
lossyEngines: { plaintext: true, html: true },

showRestoredDraftBanner: false,
restoredDraftTime: '',
restoredWordCount: 0,
hasUnsavedChanges: false,
serverBackupContent: config.initialContent || '',

init() {
    this.addLog('SYSTEM', 'OmniRoute Gateway v2.0 kernel initialized.');
    this.addLog('ENGINE', 'Editor driver mounted: ' + (config.editorType || 'tiptap').toUpperCase());
    this.addLog('SEO', 'Real-time semantic SEO analyzer active.');

    // Initialize default AI provider
    if (this.availableProviders && this.availableProviders.length > 0) {
        if (!this.selectedProvider) {
            this.selectedProvider = this.availableProviders[0].slug;
            this.fetchModelsForProvider(this.selectedProvider);
        }
    }
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
            const ed = this.getEditor();
            const currentHtml = ed && ed.getHTML ? ed.getHTML() : '';
            if (currentHtml) {
                Livewire.dispatch('autosave', { html: currentHtml, json: null });
                this.saveLocalDraft(currentHtml);
            }
            Livewire.dispatch('saveExplicitSnapshot');
            this.addLog('INFO', 'Saved snapshot via Ctrl+S');
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

getEditor() {
    if (!window.hoaEditorInstance) return null;
    return typeof Alpine !== 'undefined' && Alpine.raw 
        ? Alpine.raw(window.hoaEditorInstance) 
        : window.hoaEditorInstance;
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
    localStorage.setItem('hoa_editor_show_left_panel', this.showLeftPanel);
    localStorage.setItem('hoa_editor_show_right_panel', this.showRightPanel);
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

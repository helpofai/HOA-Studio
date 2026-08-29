{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Canvas & Driver
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
    const targetEl = document.getElementById('tiptap-content-target');
    const savedScrollTop = targetEl ? targetEl.scrollTop : 0;

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
            // Prevent duplicate autosaves if our new debounced mechanism is active or during pending proposals
            if (this.isDirty || this.isTransforming || this.showSubAgentProposal) return; 
            
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

    if (targetEl && savedScrollTop > 0) {
        requestAnimationFrame(() => {
            targetEl.scrollTop = savedScrollTop;
        });
    }
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

openContextMenu(event) {
    if (!event) return;
    const clientX = (event.clientX !== undefined) ? event.clientX : (event.x !== undefined ? event.x : window.innerWidth / 2);
    const clientY = (event.clientY !== undefined) ? event.clientY : (event.y !== undefined ? event.y : window.innerHeight / 2);
    
    const menuWidth = 280;
    const menuHeight = 460;
    
    this.contextMenuX = Math.max(10, Math.min(clientX, window.innerWidth - menuWidth - 10));
    this.contextMenuY = Math.max(10, Math.min(clientY, window.innerHeight - menuHeight - 10));
    this.showContextMenu = true;

    const ed = this.getEditor ? this.getEditor() : (this.editorInstance || window.hoaEditorInstance);
    let captured = '';
    if (ed && typeof ed.getSelectedText === 'function') {
        captured = ed.getSelectedText().trim();
    }
    if (!captured && window.getSelection) {
        captured = window.getSelection().toString().trim();
    }
    if (!captured && event.target) {
        const block = event.target.closest('p, h1, h2, h3, h4, blockquote, li');
        if (block) {
            captured = block.innerText.trim();
        }
    }
    if (captured) {
        this.selectedText = captured;
        this.hasSelection = true;
    }
    this.addLog('INFO', 'AI Context menu opened at (' + this.contextMenuX + ', ' + this.contextMenuY + ')');
},

closeContextMenu() {
    this.showContextMenu = false;
},

async copySelection() {
    const ed = this.getEditor();
    let text = this.selectedText || '';
    if (!text && window.getSelection) {
        text = window.getSelection().toString();
    }
    if (text) {
        await navigator.clipboard.writeText(text);
        this.addLog('CLIPBOARD', 'Copied text to clipboard (' + text.length + ' chars)');
    } else {
        this.addLog('WARN', 'No text selected to copy.');
    }
    this.closeContextMenu();
},

async cutSelection() {
    const ed = this.getEditor();
    let text = this.selectedText || '';
    if (!text && window.getSelection) {
        text = window.getSelection().toString();
    }
    if (text) {
        await navigator.clipboard.writeText(text);
        if (ed && typeof ed.replaceSelection === 'function') {
            ed.replaceSelection('');
        }
        this.addLog('CLIPBOARD', 'Cut text to clipboard (' + text.length + ' chars)');
    }
    this.closeContextMenu();
},

async pasteClipboard() {
    try {
        const text = await navigator.clipboard.readText();
        if (text) {
            const ed = this.getEditor();
            if (ed && typeof ed.insertContent === 'function') {
                ed.insertContent(text);
            } else if (ed && typeof ed.setContent === 'function') {
                const current = ed.getHTML ? ed.getHTML() : '';
                ed.setContent(current + text);
            }
            this.addLog('CLIPBOARD', 'Pasted content from clipboard (' + text.length + ' chars)');
        }
    } catch (err) {
        console.warn('[Clipboard Paste] Permission denied or not supported:', err);
        this.addLog('WARN', 'Use Ctrl+V to paste content.');
    }
    this.closeContextMenu();
},

selectAllCanvas() {
    const ed = this.getEditor();
    if (ed && typeof ed.selectAll === 'function') {
        ed.selectAll();
    } else if (this.editorInstance && this.editorInstance.commands) {
        this.editorInstance.commands.selectAll();
    }
    this.closeContextMenu();
    this.addLog('INFO', 'Selected all canvas content.');
},

deleteSelection() {
    const ed = this.getEditor();
    if (ed && typeof ed.replaceSelection === 'function') {
        ed.replaceSelection('');
    } else if (this.editorInstance && this.editorInstance.commands) {
        this.editorInstance.commands.deleteSelection();
    }
    this.closeContextMenu();
    this.addLog('INFO', 'Deleted selection.');
},

insertCurrentDate() {
    const dateStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    this.insertContentIntoCanvas(`<strong>${dateStr}</strong> `);
    this.closeContextMenu();
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

    insertContentIntoCanvas(htmlContent, withHighlight = false, className = 'ai-new-content', isProposal = false) {
        const ed = this.getEditor();
        if (!ed) return;
        try {
            let finalHtml = htmlContent;
            if (withHighlight && className) {
                finalHtml = `<mark class="${className}">${htmlContent}</mark>`;
            }
            if (typeof ed.insertContent === 'function') {
                ed.insertContent(finalHtml);
            } else if (typeof ed.setContent === 'function') {
                const current = ed.getHTML ? ed.getHTML() : '';
                ed.setContent(current + '<p></p>' + finalHtml);
            }

            const currentHtmlVal = ed.getHTML ? ed.getHTML() : htmlContent;
            const currentJsonVal = ed.getJSON ? ed.getJSON() : null;
            Livewire.dispatch('autosave', { html: currentHtmlVal, json: currentJsonVal });
            this.saveLocalDraft(currentHtmlVal);
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
                this.insertContentIntoCanvas(textToInsert, false);
            }
        }
        const finalHtml = ed.getHTML ? ed.getHTML() : textToInsert;
        const finalJson = ed.getJSON ? ed.getJSON() : null;
        Livewire.dispatch('autosave', { html: finalHtml, json: finalJson });
        this.saveLocalDraft(finalHtml);
        this.updateOutline();
        this.updateActiveFormats();
    }
    
    this.addLog('AI', 'Applied live stream tokens into canvas (' + textToInsert.length + ' chars)');
    this.liveAiStreamText = '';
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
}
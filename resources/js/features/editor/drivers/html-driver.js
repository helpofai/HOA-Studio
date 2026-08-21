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

export class HtmlDriver {
    constructor(elementId, config = {}) {
        this.elementId = elementId;
        this.config = config;
        this.saveTimeout = null;
        this.debounceMs = config.debounceMs || 1500;
        this.container = document.getElementById(elementId);

        this.capabilities = {
            richText: false,
            blocks: false,
            markdown: false,
            undoRedo: true
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="h-[650px] border border-white/10 rounded-2xl overflow-hidden bg-slate-950/80 shadow-2xl flex flex-col font-mono">
                <div class="p-3 bg-slate-900 border-b border-white/10 text-xs text-slate-400 font-bold flex items-center justify-between">
                    <span class="text-amber-400">⚡ Raw HTML Source Editor</span>
                    <span class="text-[10px] text-slate-500 font-normal">HTML5 / Sanitized</span>
                </div>
                <textarea id="raw-html-textarea" class="w-full flex-1 bg-transparent p-5 text-amber-200/90 font-mono text-sm leading-relaxed focus:outline-none resize-none" placeholder="<div>Enter HTML here...</div>"></textarea>
            </div>
        `;

        this.textarea = document.getElementById('raw-html-textarea');
        this.textarea.value = this.config.initialContent || '';

        this.textarea.addEventListener('input', () => {
            this.triggerUpdate();
        });
    }

    triggerUpdate() {
        const html = this.textarea.value;
        const text = this.getText();
        const words = text.trim().split(/\s+/).filter(Boolean).length;
        const chars = text.length;

        if (typeof this.config.onStatsChange === 'function') {
            this.config.onStatsChange({ words, characters: chars, html, text });
        }

        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => {
            if (typeof this.config.onAutosave === 'function') {
                this.config.onAutosave({ html, text, words, chars });
            }
        }, this.debounceMs);
    }

    getHTML() {
        return this.textarea ? this.textarea.value : '';
    }

    getText() {
        if (!this.textarea) return '';
        const temp = document.createElement('div');
        temp.innerHTML = this.textarea.value;
        return temp.textContent || '';
    }

    
    insertContent(html) {
        if (this.textarea) {
            const start = this.textarea.selectionStart || this.textarea.value.length;
            const end = this.textarea.selectionEnd || this.textarea.value.length;
            this.textarea.value = this.textarea.value.substring(0, start) + '\n' + html + '\n' + this.textarea.value.substring(end);
            this.triggerUpdate();
        }
    }

    replaceSelection(replacement) {
        this.insertContent(replacement);
    }

    setContent(html) {
        if (this.textarea) {
            this.textarea.value = html || '';
            this.triggerUpdate();
        }
    }

    destroy() {
        if (this.container) this.container.innerHTML = '';
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('html', HtmlDriver);
}

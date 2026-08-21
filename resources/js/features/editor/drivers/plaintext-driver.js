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

export class PlainTextDriver {
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
            undoRedo: false
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="h-[600px] border border-white/10 rounded-2xl overflow-hidden bg-slate-950/40 backdrop-blur-md">
                <textarea id="plaintext-textarea" class="w-full h-full bg-slate-900/60 p-6 text-slate-200 font-sans text-base leading-relaxed focus:outline-none resize-none" placeholder="Type plain text here..."></textarea>
            </div>
        `;

        this.textarea = document.getElementById('plaintext-textarea');

        const initial = this.config.initialContent || '';
        this.textarea.value = this.stripHtml(initial);

        this.textarea.addEventListener('input', () => {
            this.triggerUpdate();
        });
    }

    stripHtml(html) {
        let doc = new DOMParser().parseFromString(html, 'text/html');
        return doc.body.textContent || '';
    }

    triggerUpdate() {
        const text = this.textarea.value;
        const html = `<p>${text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>`.replace(/\n/g, '<br>');
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

    
    insertContent(content) {
        if (this.textarea) {
            const clean = this.stripHtml(content);
            const start = this.textarea.selectionStart || this.textarea.value.length;
            const end = this.textarea.selectionEnd || this.textarea.value.length;
            this.textarea.value = this.textarea.value.substring(0, start) + '\n\n' + clean + '\n\n' + this.textarea.value.substring(end);
            this.triggerUpdate();
        }
    }

    replaceSelection(replacement) {
        this.insertContent(replacement);
    }

    setContent(content) {
        if (this.textarea) {
            this.textarea.value = this.stripHtml(content);
        }
    }

    getHTML() {
        return `<p>${this.textarea.value}</p>`.replace(/\n/g, '<br>');
    }

    getText() {
        return this.textarea.value;
    }

    destroy() {
        clearTimeout(this.saveTimeout);
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}

function e(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('plaintext', PlainTextDriver);
}

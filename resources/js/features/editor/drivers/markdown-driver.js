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

export class MarkdownDriver {
    constructor(elementId, config = {}) {
        this.elementId = elementId;
        this.config = config;
        this.saveTimeout = null;
        this.debounceMs = config.debounceMs || 1500;
        this.container = document.getElementById(elementId);

        this.capabilities = {
            richText: false,
            blocks: false,
            markdown: true,
            undoRedo: false
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        // Render split preview editor layout
        this.container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-[600px] border border-white/10 rounded-2xl overflow-hidden bg-slate-950/40 backdrop-blur-md">
                <textarea id="markdown-textarea" class="w-full h-full bg-slate-900/60 p-6 text-slate-200 font-mono text-sm leading-relaxed border-r border-white/5 focus:outline-none resize-none" placeholder="Type Markdown here..."></textarea>
                <div id="markdown-preview" class="w-full h-full p-6 overflow-y-auto prose prose-invert max-w-none text-slate-300 leading-relaxed bg-slate-950/20"></div>
            </div>
        `;

        this.textarea = document.getElementById('markdown-textarea');
        this.preview = document.getElementById('markdown-preview');

        // Set initial content
        const initial = this.config.initialContent || '';
        this.textarea.value = this.htmlToMarkdown(initial);
        this.updatePreview();

        this.textarea.addEventListener('input', () => {
            this.updatePreview();
            this.triggerUpdate();
        });
    }

    updatePreview() {
        if (this.preview) {
            this.preview.innerHTML = this.markdownToHtml(this.textarea.value);
        }
    }

    triggerUpdate() {
        const text = this.textarea.value;
        const html = this.markdownToHtml(text);
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

    htmlToMarkdown(html) {
        let md = html.replace(/<h[1-4]>(.*?)<\/h[1-4]>/gi, '\n# $1\n');
        md = md.replace(/<p>(.*?)<\/p>/gi, '$1\n\n');
        md = md.replace(/<strong>(.*?)<\/strong>/gi, '**$1**');
        md = md.replace(/<em>(.*?)<\/em>/gi, '*$1*');
        md = md.replace(/<li>(.*?)<\/li>/gi, '* $1\n');
        md = md.replace(/<ul[^>]*>(.*?)<\/ul>/gi, '$1\n');
        return md.replace(/<[^>]*>/g, '').trim();
    }

    markdownToHtml(md) {
        let html = md.replace(/^\s*#\s+(.*?)$/gm, '<h1>$1</h1>');
        html = html.replace(/^\s*##\s+(.*?)$/gm, '<h2>$1</h2>');
        html = html.replace(/^\s*\*\s+(.*?)$/gm, '<li>$1</li>');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        html = html.replace(/\n\n/g, '</p><p>');
        return `<p>${html}</p>`.replace(/<p><h/g, '<h').replace(/<\/h1><\/p>/g, '</h1>').replace(/<\/h2><\/p>/g, '</h2>');
    }

    
    insertContent(content) {
        if (this.textarea) {
            const mdContent = content.includes('<') ? this.htmlToMarkdown(content) : content;
            const start = this.textarea.selectionStart || this.textarea.value.length;
            const end = this.textarea.selectionEnd || this.textarea.value.length;
            this.textarea.value = this.textarea.value.substring(0, start) + '\n\n' + mdContent + '\n\n' + this.textarea.value.substring(end);
            this.updatePreview();
            this.triggerUpdate();
        }
    }

    replaceSelection(replacement) {
        this.insertContent(replacement);
    }

    setContent(content) {
        if (this.textarea) {
            this.textarea.value = this.htmlToMarkdown(content);
            this.updatePreview();
        }
    }

    getHTML() {
        return this.markdownToHtml(this.textarea.value);
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

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('markdown', MarkdownDriver);
}

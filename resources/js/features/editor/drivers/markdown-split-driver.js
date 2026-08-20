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

export class MarkdownSplitDriver {
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
            undoRedo: true
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 h-[650px] border border-white/10 rounded-2xl overflow-hidden bg-slate-950/60 shadow-2xl">
                <div class="flex flex-col h-full border-r border-white/10">
                    <div class="p-3 bg-slate-900/90 border-b border-white/10 text-xs font-mono text-slate-400 font-bold flex items-center justify-between">
                        <span>📝 Markdown Source</span>
                        <span class="text-[10px] text-indigo-400 font-normal">Live Sync Active</span>
                    </div>
                    <textarea id="md-split-input" class="w-full flex-1 bg-transparent p-5 text-slate-200 font-mono text-sm leading-relaxed focus:outline-none resize-none" placeholder="# Start writing Markdown..."></textarea>
                </div>
                <div class="flex flex-col h-full bg-slate-950/40">
                    <div class="p-3 bg-slate-900/90 border-b border-white/10 text-xs font-mono text-slate-400 font-bold flex items-center justify-between">
                        <span>👁 Live HTML Preview</span>
                        <span class="text-[10px] text-emerald-400 font-normal">Sanitized</span>
                    </div>
                    <div id="md-split-preview" class="w-full flex-1 p-6 overflow-y-auto prose prose-invert max-w-none text-slate-300 leading-relaxed"></div>
                </div>
            </div>
        `;

        this.textarea = document.getElementById('md-split-input');
        this.preview = document.getElementById('md-split-preview');

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
        return md.replace(/<[^>]*>/g, '').trim();
    }

    markdownToHtml(md) {
        if (!md) return '<p class="text-slate-500 italic">No content yet...</p>';
        let html = md.replace(/\n/g, '<br/>');
        html = html.replace(/^# (.*?)$/gim, '<h1 class="text-2xl font-bold text-white mb-2">$1</h1>');
        html = html.replace(/^## (.*?)$/gim, '<h2 class="text-xl font-bold text-white mb-2">$1</h2>');
        html = html.replace(/^### (.*?)$/gim, '<h3 class="text-lg font-bold text-white mb-1">$1</h3>');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        return html;
    }

    getHTML() {
        return this.markdownToHtml(this.textarea.value);
    }

    getText() {
        return this.textarea.value;
    }

    setContent(html) {
        if (this.textarea) {
            this.textarea.value = this.htmlToMarkdown(html || '');
            this.updatePreview();
            this.triggerUpdate();
        }
    }

    destroy() {
        if (this.container) this.container.innerHTML = '';
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('markdown_split', MarkdownSplitDriver);
}

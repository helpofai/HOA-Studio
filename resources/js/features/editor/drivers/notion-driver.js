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

export class NotionDriver {
    constructor(elementId, config = {}) {
        this.elementId = elementId;
        this.config = config;
        this.saveTimeout = null;
        this.debounceMs = config.debounceMs || 1500;
        this.container = document.getElementById(elementId);
        this.blocks = [];

        this.capabilities = {
            richText: true,
            blocks: true,
            markdown: false,
            undoRedo: true
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        const initial = this.config.initialContent || '<p>Type / for commands...</p>';
        this.parseHtml(initial);
        this.renderCanvas();
    }

    parseHtml(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html || '';
        this.blocks = [];

        Array.from(temp.children).forEach(el => {
            const tag = el.tagName.toLowerCase();
            if (/^h[1-6]$/.test(tag)) {
                this.blocks.push({ id: this.genId(), type: 'heading', level: parseInt(tag[1]), content: el.innerHTML });
            } else if (tag === 'blockquote') {
                this.blocks.push({ id: this.genId(), type: 'callout', content: el.innerHTML });
            } else if (tag === 'pre' || tag === 'code') {
                this.blocks.push({ id: this.genId(), type: 'code', content: el.innerText });
            } else if (tag === 'table') {
                this.blocks.push({ id: this.genId(), type: 'table', content: el.outerHTML });
            } else if (tag === 'div') {
                this.blocks.push({ id: this.genId(), type: 'card', content: el.innerHTML });
            } else if (tag === 'ul' || tag === 'ol') {
                this.blocks.push({ id: this.genId(), type: 'list', ordered: tag === 'ol', content: el.innerHTML });
            } else {
                this.blocks.push({ id: this.genId(), type: 'text', content: el.innerHTML });
            }
        });

        if (this.blocks.length === 0) {
            this.blocks.push({ id: this.genId(), type: 'text', content: '' });
        }
    }

    genId() {
        return 'notion_' + Math.random().toString(36).substr(2, 9);
    }

    renderCanvas() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="notion-canvas max-w-4xl mx-auto space-y-2 font-sans">
                <div class="flex items-center justify-between p-2.5 bg-slate-900/90 border border-white/10 rounded-2xl backdrop-blur-md sticky top-0 z-20 shadow-md">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-600/20 text-indigo-300 font-mono text-xs font-bold border border-indigo-500/30">
                        ✦ Notion Block Canvas
                    </span>
                    <span class="text-xs text-slate-400 font-mono" id="notion-count">${this.blocks.length} blocks</span>
                </div>
                <div id="notion-rows" class="space-y-1.5 min-h-[500px]"></div>
            </div>
        `;

        this.renderBlocks();
        this.bindEvents();
    }

    renderBlocks() {
        const rows = document.getElementById('notion-rows');
        if (!rows) return;

        rows.innerHTML = this.blocks.map((b) => `
            <div class="notion-row group relative flex items-start gap-2 p-1.5 rounded-xl hover:bg-white/5 transition-colors" data-id="${b.id}">
                <div class="pt-1.5 opacity-0 group-hover:opacity-100 text-slate-500 hover:text-white cursor-grab select-none text-xs font-mono">⋮⋮</div>
                <div class="flex-1">
                    ${this.renderBlockInput(b)}
                </div>
            </div>
        `).join('');

        const countEl = document.getElementById('notion-count');
        if (countEl) countEl.innerText = `${this.blocks.length} blocks`;
    }

    renderBlockInput(b) {
        if (b.type === 'heading') {
            const fontClass = b.level === 1 ? 'text-2xl font-black text-white' : (b.level === 2 ? 'text-xl font-bold text-white' : 'text-lg font-semibold text-indigo-200');
            return `<div contenteditable="true" class="notion-content ${fontClass} focus:outline-none" data-id="${b.id}">${b.content}</div>`;
        } else if (b.type === 'callout') {
            return `<blockquote contenteditable="true" class="notion-content p-3.5 rounded-xl bg-indigo-950/30 border-l-4 border-indigo-500 text-slate-200 text-sm italic focus:outline-none" data-id="${b.id}">${b.content}</blockquote>`;
        } else if (b.type === 'code') {
            return `<pre class="bg-slate-950 p-4 rounded-xl border border-white/10 overflow-x-auto"><code contenteditable="true" class="notion-content text-xs font-mono text-emerald-400 focus:outline-none block" data-id="${b.id}">${b.content}</code></pre>`;
        } else if (b.type === 'table') {
            return `<div class="notion-content overflow-x-auto my-2" data-id="${b.id}">${b.content}</div>`;
        } else if (b.type === 'card') {
            return `<div contenteditable="true" class="notion-content p-3.5 rounded-xl bg-slate-950/70 border border-indigo-500/30 text-xs text-slate-200 focus:outline-none" data-id="${b.id}">${b.content}</div>`;
        } else if (b.type === 'list') {
            return `<ul contenteditable="true" class="notion-content list-disc list-inside space-y-1 text-slate-200 focus:outline-none" data-id="${b.id}">${b.content}</ul>`;
        } else {
            return `<div contenteditable="true" class="notion-content text-base leading-relaxed text-slate-200 focus:outline-none min-h-[26px]" data-id="${b.id}">${b.content}</div>`;
        }
    }

    bindEvents() {
        this.container.addEventListener('input', (e) => {
            if (e.target.classList.contains('notion-content')) {
                const id = e.target.getAttribute('data-id');
                const blk = this.blocks.find(b => b.id === id);
                if (blk) {
                    blk.content = e.target.innerHTML;
                    this.triggerUpdate();
                }
            }
        });
    }

    insertContent(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newBlocks = [];
        Array.from(temp.children).forEach(el => {
            const tag = el.tagName.toLowerCase();
            if (/^h[1-6]$/.test(tag)) {
                newBlocks.push({ id: this.genId(), type: 'heading', level: parseInt(tag[1]), content: el.innerHTML });
            } else if (tag === 'blockquote') {
                newBlocks.push({ id: this.genId(), type: 'callout', content: el.innerHTML });
            } else if (tag === 'pre' || tag === 'code') {
                newBlocks.push({ id: this.genId(), type: 'code', content: el.innerText });
            } else if (tag === 'table') {
                newBlocks.push({ id: this.genId(), type: 'table', content: el.outerHTML });
            } else if (tag === 'div') {
                newBlocks.push({ id: this.genId(), type: 'card', content: el.innerHTML });
            } else if (tag === 'ul' || tag === 'ol') {
                newBlocks.push({ id: this.genId(), type: 'list', ordered: tag === 'ol', content: el.innerHTML });
            } else {
                newBlocks.push({ id: this.genId(), type: 'text', content: el.innerHTML });
            }
        });
        if (newBlocks.length === 0 && html.trim()) {
            newBlocks.push({ id: this.genId(), type: 'text', content: html });
        }
        this.blocks = [...this.blocks, ...newBlocks];
        this.renderBlocks();
        this.triggerUpdate();
    }

    replaceSelection(replacement) {
        this.insertContent(replacement);
    }

    setContent(html) {
        this.parseHtml(html || '');
        this.renderBlocks();
        this.triggerUpdate();
    }

    triggerUpdate() {
        const html = this.getHTML();
        const text = this.getText();
        const words = text.trim().split(/\s+/).filter(Boolean).length;
        const chars = text.length;

        if (typeof this.config.onStatsChange === 'function') {
            this.config.onStatsChange({ words, characters: chars, html, text });
        }

        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => {
            if (typeof this.config.onAutosave === 'function') {
                this.config.onAutosave({ html, text, json: { blocks: this.blocks } });
            }
        }, this.debounceMs);
    }

    getHTML() {
        return this.blocks.map(b => {
            if (b.type === 'heading') return `<h${b.level || 2}>${b.content}</h${b.level || 2}>`;
            if (b.type === 'callout') return `<blockquote>${b.content}</blockquote>`;
            if (b.type === 'code') return `<pre><code>${b.content}</code></pre>`;
            if (b.type === 'table') return b.content;
            if (b.type === 'card') return `<div class="p-4 my-4 rounded-2xl bg-slate-900/90 border border-indigo-500/30">${b.content}</div>`;
            if (b.type === 'list') return b.ordered ? `<ol>${b.content}</ol>` : `<ul>${b.content}</ul>`;
            return `<p>${b.content}</p>`;
        }).join('\n');
    }

    getText() {
        const temp = document.createElement('div');
        temp.innerHTML = this.getHTML();
        return temp.textContent || '';
    }

    destroy() {
        clearTimeout(this.saveTimeout);
        if (this.container) this.container.innerHTML = '';
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('block_editor', NotionDriver);
    window.HOA_EditorManager.registerDriver('notion', NotionDriver);
}

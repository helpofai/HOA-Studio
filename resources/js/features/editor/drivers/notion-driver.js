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
            markdown: true,
            undoRedo: true
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        const initialHtml = this.config.initialContent || '<p>Type / for Notion commands or click anywhere to write...</p>';
        this.parseHtml(initialHtml);
        this.renderCanvas();
    }

    parseHtml(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        this.blocks = [];

        Array.from(temp.children).forEach(el => {
            const tag = el.tagName.toLowerCase();
            if (/^h[1-3]$/.test(tag)) {
                this.blocks.push({ id: this.genId(), type: 'heading', level: parseInt(tag[1]), content: el.innerHTML });
            } else if (tag === 'blockquote') {
                this.blocks.push({ id: this.genId(), type: 'callout', content: el.innerHTML });
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
            <div class="notion-workspace max-w-4xl mx-auto space-y-2 font-sans py-4">
                <div class="flex items-center justify-between pb-3 border-b border-white/10 text-xs text-slate-400 font-mono">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-400 animate-pulse"></span>
                        <strong class="text-white">Notion-Style Block Canvas</strong>
                    </span>
                    <span>Press <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 border border-white/10 text-[10px]">/</kbd> for quick menu</span>
                </div>
                <div id="notion-blocks-container" class="space-y-1.5 min-h-[500px]"></div>
            </div>
        `;

        this.renderBlocks();
        this.bindEvents();
    }

    renderBlocks() {
        const c = document.getElementById('notion-blocks-container');
        if (!c) return;

        c.innerHTML = this.blocks.map(b => `
            <div class="notion-row group flex items-start gap-2 py-1 px-2 rounded-xl hover:bg-slate-900/50 transition-colors" data-id="${b.id}">
                <button type="button" class="notion-handle opacity-0 group-hover:opacity-100 text-slate-500 hover:text-white cursor-grab pt-1 text-sm select-none" title="Drag / Options">⠿</button>
                <div class="flex-1">
                    ${this.renderBlock(b)}
                </div>
            </div>
        `).join('');
    }

    renderBlock(b) {
        if (b.type === 'heading') {
            const size = b.level === 1 ? 'text-3xl font-black' : (b.level === 2 ? 'text-2xl font-bold' : 'text-xl font-bold');
            return `<div contenteditable="true" class="notion-content ${size} text-white focus:outline-none py-1" data-id="${b.id}">${b.content}</div>`;
        } else if (b.type === 'callout') {
            return `<div class="p-4 rounded-xl bg-violet-950/30 border border-violet-500/30 flex items-start gap-3"><span class="text-lg">💡</span><div contenteditable="true" class="notion-content flex-1 text-slate-200 focus:outline-none text-sm" data-id="${b.id}">${b.content}</div></div>`;
        } else {
            return `<div contenteditable="true" class="notion-content text-base leading-relaxed text-slate-300 focus:outline-none min-h-[28px] py-1" data-id="${b.id}">${b.content}</div>`;
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
            return `<p>${b.content}</p>`;
        }).join('\n');
    }

    getText() {
        const temp = document.createElement('div');
        temp.innerHTML = this.getHTML();
        return temp.textContent || '';
    }

    setContent(html) {
        this.parseHtml(html || '');
        this.renderBlocks();
        this.triggerUpdate();
    }

    destroy() {
        if (this.container) this.container.innerHTML = '';
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('block_editor', NotionDriver);
    window.HOA_EditorManager.registerDriver('notion', NotionDriver);
}

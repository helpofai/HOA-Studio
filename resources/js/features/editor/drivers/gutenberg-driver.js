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

export class GutenbergDriver {
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

        const initialHtml = this.config.initialContent || '<p>Start building your block content...</p>';
        this.parseHtmlToBlocks(initialHtml);
        this.renderCanvas();
    }

    parseHtmlToBlocks(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        this.blocks = [];

        if (temp.children.length === 0 && temp.textContent.trim()) {
            this.blocks.push({ id: this.genId(), type: 'paragraph', content: temp.textContent.trim() });
            return;
        }

        Array.from(temp.children).forEach(el => {
            const tag = el.tagName.toLowerCase();
            if (/^h[1-6]$/.test(tag)) {
                this.blocks.push({ id: this.genId(), type: 'heading', level: parseInt(tag[1]), content: el.innerHTML });
            } else if (tag === 'blockquote') {
                this.blocks.push({ id: this.genId(), type: 'quote', content: el.innerHTML });
            } else if (tag === 'pre' || tag === 'code') {
                this.blocks.push({ id: this.genId(), type: 'code', content: el.innerText });
            } else if (tag === 'ul' || tag === 'ol') {
                this.blocks.push({ id: this.genId(), type: 'list', ordered: tag === 'ol', content: el.innerHTML });
            } else {
                this.blocks.push({ id: this.genId(), type: 'paragraph', content: el.innerHTML });
            }
        });

        if (this.blocks.length === 0) {
            this.blocks.push({ id: this.genId(), type: 'paragraph', content: '' });
        }
    }

    genId() {
        return 'blk_' + Math.random().toString(36).substr(2, 9);
    }

    renderCanvas() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div class="gutenberg-canvas max-w-4xl mx-auto space-y-3 font-sans">
                <div class="flex items-center justify-between p-3 bg-slate-900/90 border border-white/10 rounded-2xl backdrop-blur-md sticky top-0 z-20 shadow-lg">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-600/20 text-indigo-300 font-mono text-xs font-bold border border-indigo-500/30">
                            ❖ Gutenberg Block Canvas
                        </span>
                        <span class="text-xs text-slate-400 font-mono" id="gt-block-count">${this.blocks.length} blocks</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" data-add="paragraph" class="gt-add-btn px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">+ Paragraph</button>
                        <button type="button" data-add="heading" class="gt-add-btn px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">+ Heading</button>
                        <button type="button" data-add="quote" class="gt-add-btn px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">+ Quote</button>
                        <button type="button" data-add="code" class="gt-add-btn px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">+ Code</button>
                    </div>
                </div>

                <div id="gt-blocks-list" class="space-y-3 min-h-[500px]"></div>
            </div>
        `;

        this.renderBlockList();
        this.bindEvents();
    }

    renderBlockList() {
        const list = document.getElementById('gt-blocks-list');
        if (!list) return;

        list.innerHTML = this.blocks.map((b, idx) => `
            <div class="gt-block group relative p-4 rounded-2xl bg-slate-900/40 hover:bg-slate-900/80 border border-white/5 hover:border-indigo-500/30 transition-all shadow-sm" data-id="${b.id}" data-idx="${idx}">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-white/5 text-[10px] text-slate-400 font-mono">
                    <span class="uppercase tracking-wider font-bold text-slate-500">${b.type} ${b.level ? 'H' + b.level : ''}</span>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button" class="gt-up p-1 hover:text-white" title="Move Up">↑</button>
                        <button type="button" class="gt-down p-1 hover:text-white" title="Move Down">↓</button>
                        <button type="button" class="gt-del p-1 hover:text-red-400" title="Delete Block">✕</button>
                    </div>
                </div>
                ${this.renderBlockEditor(b)}
            </div>
        `).join('');

        const countEl = document.getElementById('gt-block-count');
        if (countEl) countEl.innerText = `${this.blocks.length} blocks`;
    }

    renderBlockEditor(b) {
        if (b.type === 'heading') {
            return `<div contenteditable="true" class="gt-content text-2xl font-black text-white focus:outline-none" data-id="${b.id}">${b.content}</div>`;
        } else if (b.type === 'quote') {
            return `<blockquote contenteditable="true" class="gt-content border-l-4 border-indigo-500 pl-4 py-1 text-slate-300 italic font-serif focus:outline-none" data-id="${b.id}">${b.content}</blockquote>`;
        } else if (b.type === 'code') {
            return `<pre class="bg-slate-950 p-3 rounded-xl"><code contenteditable="true" class="gt-content text-xs font-mono text-emerald-400 focus:outline-none block" data-id="${b.id}">${b.content}</code></pre>`;
        } else {
            return `<div contenteditable="true" class="gt-content text-base leading-relaxed text-slate-200 focus:outline-none min-h-[28px]" data-id="${b.id}">${b.content}</div>`;
        }
    }

    bindEvents() {
        this.container.querySelectorAll('.gt-add-btn').forEach(btn => {
            btn.onclick = () => {
                const type = btn.getAttribute('data-add');
                this.blocks.push({ id: this.genId(), type, level: type === 'heading' ? 2 : undefined, content: '' });
                this.renderBlockList();
                this.triggerUpdate();
            };
        });

        this.container.addEventListener('click', (e) => {
            const blockEl = e.target.closest('.gt-block');
            if (!blockEl) return;
            const idx = parseInt(blockEl.getAttribute('data-idx'));

            if (e.target.classList.contains('gt-up') && idx > 0) {
                const temp = this.blocks[idx];
                this.blocks[idx] = this.blocks[idx - 1];
                this.blocks[idx - 1] = temp;
                this.renderBlockList();
                this.triggerUpdate();
            } else if (e.target.classList.contains('gt-down') && idx < this.blocks.length - 1) {
                const temp = this.blocks[idx];
                this.blocks[idx] = this.blocks[idx + 1];
                this.blocks[idx + 1] = temp;
                this.renderBlockList();
                this.triggerUpdate();
            } else if (e.target.classList.contains('gt-del')) {
                this.blocks.splice(idx, 1);
                if (this.blocks.length === 0) {
                    this.blocks.push({ id: this.genId(), type: 'paragraph', content: '' });
                }
                this.renderBlockList();
                this.triggerUpdate();
            }
        });

        this.container.addEventListener('input', (e) => {
            if (e.target.classList.contains('gt-content')) {
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
            if (b.type === 'quote') return `<blockquote>${b.content}</blockquote>`;
            if (b.type === 'code') return `<pre><code>${b.content}</code></pre>`;
            return `<p>${b.content}</p>`;
        }).join('\n');
    }

    getText() {
        const temp = document.createElement('div');
        temp.innerHTML = this.getHTML();
        return temp.textContent || '';
    }

    setContent(html) {
        this.parseHtmlToBlocks(html || '');
        this.renderBlockList();
        this.triggerUpdate();
    }

    destroy() {
        if (this.container) this.container.innerHTML = '';
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('gutenberg', GutenbergDriver);
}

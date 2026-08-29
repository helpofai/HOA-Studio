/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Enterprise Tiptap Driver v3.0
|--------------------------------------------------------------------------
|
| Enterprise-grade Tiptap editor engine with extended Element & Formatting Suite:
| Reactive Selection Bubble Toolbar, H1-H4, Bold, Italic, Underline, Strike,
| Subscript, Superscript, Highlights, Color, Task Lists, Bullet/Numbered Lists,
| Tables, Callouts, Pros/Cons Cards, FAQ Accordions, E-E-A-T Trust Blocks.
|
*/

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';
import Typography from '@tiptap/extension-typography';
import { BubbleMenu } from '@tiptap/extension-bubble-menu';
import Highlight from '@tiptap/extension-highlight';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import { Table } from '@tiptap/extension-table';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableRow } from '@tiptap/extension-table-row';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';

/**
 * High-Fidelity Markdown & HTML Parser and Normalizer
 */
export function normalizeContentToHtml(content) {
    if (!content || typeof content !== 'string') return '<p></p>';
    
    let text = content.trim();
    if (!text) return '<p></p>';

    // If already well-structured HTML (starts with or contains major HTML block tags)
    const isHtmlBlock = /^<(!DOCTYPE|html|head|body|div|p|h[1-6]|table|blockquote|ul|ol|article|section|details|pre)/i.test(text)
        || /<\/([a-z]+)>/i.test(text);

    if (isHtmlBlock) {
        // Strip out HTML comments that might disrupt ProseMirror DOM parsing
        text = text.replace(/<!--[\s\S]*?-->/g, '');
        // Clean excessive whitespace between HTML tags
        return text.trim();
    }

    // Markdown / Mixed Plain-text Pipeline
    text = text.replace(/\n{3,}/g, '\n\n');

    // 1. Code blocks
    text = text.replace(/```([a-z0-9_-]*)\n([\s\S]*?)```/gim, (match, lang, code) => {
        return `<pre><code class="language-${lang || 'text'}">${code.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>`;
    });

    // 2. Rich Callout Boxes (> [!TIP], > [!WARNING], > [!NOTE], > [!IMPORTANT], > [!CAUTION])
    text = text.replace(/^>\s*\[!TIP\]\s*\n((?:>.*(?:\r?\n|$))*)/gim, (match, body) => {
        const cleanBody = body.replace(/^>\s?/gm, '').trim();
        return `<div class="callout-box callout-tip"><div class="callout-title">💡 <span>Pro-Tip & Actionable Insight</span></div><p>${cleanBody}</p></div>`;
    });
    text = text.replace(/^>\s*\[!WARNING\]\s*\n((?:>.*(?:\r?\n|$))*)/gim, (match, body) => {
        const cleanBody = body.replace(/^>\s?/gm, '').trim();
        return `<div class="callout-box callout-warning"><div class="callout-title">⚠️ <span>Warning & Critical Precaution</span></div><p>${cleanBody}</p></div>`;
    });
    text = text.replace(/^>\s*\[!NOTE\]\s*\n((?:>.*(?:\r?\n|$))*)/gim, (match, body) => {
        const cleanBody = body.replace(/^>\s?/gm, '').trim();
        return `<div class="callout-box callout-info"><div class="callout-title">ℹ️ <span>Important Context & Notes</span></div><p>${cleanBody}</p></div>`;
    });
    text = text.replace(/^>\s*\[!IMPORTANT\]\s*\n((?:>.*(?:\r?\n|$))*)/gim, (match, body) => {
        const cleanBody = body.replace(/^>\s?/gm, '').trim();
        return `<div class="callout-box callout-tldr"><div class="callout-title">⚡ <span>Executive Summary / TL;DR</span></div><p>${cleanBody}</p></div>`;
    });
    text = text.replace(/^>\s*\[!CAUTION\]\s*\n((?:>.*(?:\r?\n|$))*)/gim, (match, body) => {
        const cleanBody = body.replace(/^>\s?/gm, '').trim();
        return `<div class="callout-box callout-caution"><div class="callout-title">🚨 <span>High-Risk Caution</span></div><p>${cleanBody}</p></div>`;
    });

    // 3. Tables
    text = text.replace(/((\|[^\n]+\|\r?\n)((?:\|:?[-]+:?)+\|)(\r?\n(?:\|[^\n]+\|\r?\n?)*))/g, (tableMatch) => {
        const rows = tableMatch.trim().split(/\r?\n/).map(r => r.trim()).filter(Boolean);
        if (rows.length < 2) return tableMatch;
        
        const headerCells = rows[0].split('|').slice(1, -1).map(c => c.trim());
        let tableHtml = '<table class="border-collapse border border-white/20 my-4 w-full text-left"><thead><tr class="bg-white/10">';
        headerCells.forEach(cell => {
            tableHtml += `<th class="border border-white/20 px-3 py-2 font-bold">${cell}</th>`;
        });
        tableHtml += '</tr></thead><tbody>';

        for (let i = 2; i < rows.length; i++) {
            const cells = rows[i].split('|').slice(1, -1).map(c => c.trim());
            tableHtml += '<tr class="hover:bg-white/5">';
            cells.forEach(cell => {
                tableHtml += `<td class="border border-white/20 px-3 py-2">${cell}</td>`;
            });
            tableHtml += '</tr>';
        }
        tableHtml += '</tbody></table>';
        return tableHtml;
    });

    // 4. Headings
    text = text.replace(/^######\s+(.*?)$/gm, '<h6>$1</h6>');
    text = text.replace(/^#####\s+(.*?)$/gm, '<h5>$1</h5>');
    text = text.replace(/^####\s+(.*?)$/gm, '<h4>$1</h4>');
    text = text.replace(/^###\s+(.*?)$/gm, '<h3>$1</h3>');
    text = text.replace(/^##\s+(.*?)$/gm, '<h2>$1</h2>');
    text = text.replace(/^#\s+(.*?)$/gm, '<h1>$1</h1>');

    // 5. Standard Blockquotes
    text = text.replace(/^>\s+(.*?)$/gm, '<blockquote><p>$1</p></blockquote>');

    // 6. Task Checklists (- [ ] or - [x])
    text = text.replace(/(^[*-]\s+\[([ xX])\]\s+.*(?:\r?\n[*-]\s+\[([ xX])\]\s+.*)*)/gm, (taskMatch) => {
        const items = taskMatch.trim().split(/\r?\n/).map(line => {
            const isChecked = /^[*-]\s+\[[xX]\]/.test(line);
            const content = line.replace(/^[*-]\s+\[[ xX]\]\s+/, '');
            return `<li data-type="taskItem" data-checked="${isChecked ? 'true' : 'false'}"><label><input type="checkbox"${isChecked ? ' checked' : ''}><span></span></label><div><p>${content}</p></div></li>`;
        }).join('');
        return `<ul data-type="taskList">${items}</ul>`;
    });

    // 7. Unordered Lists
    text = text.replace(/(^[*-]\s+(?!\[[ xX]\]).*(\r?\n[*-]\s+(?!\[[ xX]\]).*)*)/gm, (listMatch) => {
        const items = listMatch.trim().split(/\r?\n/).map(line => {
            return `<li>${line.replace(/^[*-]\s+/, '')}</li>`;
        }).join('');
        return `<ul>${items}</ul>`;
    });

    // 8. Ordered Lists
    text = text.replace(/(^\d+\.\s+.*(?:\r?\n\d+\.\s+.*)*)/gm, (listMatch) => {
        const items = listMatch.trim().split(/\r?\n/).map(line => {
            return `<li>${line.replace(/^\d+\.\s+/, '')}</li>`;
        }).join('');
        return `<ol>${items}</ol>`;
    });

    // 9. Horizontal Dividers
    text = text.replace(/^(\*{3,}|-{3,}|_{3,})$/gm, '<hr>');

    // 10. Images
    text = text.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="rounded-2xl border border-white/10 my-4 shadow-xl max-w-full" />');

    // 11. Subscript & Superscript & Underline
    text = text.replace(/~([^~]+)~/g, '<sub>$1</sub>');
    text = text.replace(/\^([^^]+)\^/g, '<sup>$1</sup>');
    text = text.replace(/__([^_]+)__/g, '<u>$1</u>');

    // 12. Inline Formatting
    text = text.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    text = text.replace(/==(.*?)==/g, '<mark>$1</mark>');
    text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
    text = text.replace(/~~(.*?)~~/g, '<s>$1</s>');

    // 13. Convert remaining paragraphs
    const blocks = text.split(/\n\n+/);
    const result = [];

    for (let block of blocks) {
        block = block.trim();
        if (!block) continue;

        if (/^<(h[1-6]|ul|ol|li|blockquote|table|thead|tbody|tr|th|td|pre|div|hr|img|details|section|article)/i.test(block)) {
            result.push(block);
        } else {
            result.push(`<p>${block.replace(/\n/g, '<br>')}</p>`);
        }
    }

    return result.join('\n');
}

export class TiptapDriver {
    constructor(elementId, config = {}) {
        this.elementId = elementId;
        this.config = config;
        this.editor = null;
        this.saveTimeout = null;
        this.debounceMs = config.debounceMs || 1500;
        this.streamActive = false;
        this.scrollHandler = null;

        this.init();
        console.log('[TiptapDriver] Enterprise v3.0 Robust Suite on #' + elementId);
    }

    updateBubbleMenu(editor = this.editor) {
        const bubbleMenuEl = document.getElementById('tiptap-bubble-menu');
        if (!bubbleMenuEl || !editor || editor.isDestroyed) return;

        const { state } = editor;
        const { from, to, empty } = state.selection;

        if (empty || from === to || (to - from) <= 0) {
            bubbleMenuEl.style.display = 'none';
            return;
        }

        try {
            const domSelection = window.getSelection();
            if (!domSelection || domSelection.rangeCount === 0) {
                bubbleMenuEl.style.display = 'none';
                return;
            }

            const range = domSelection.getRangeAt(0);
            const rangeRect = range.getBoundingClientRect();

            if (rangeRect.width === 0 && rangeRect.height === 0) {
                bubbleMenuEl.style.display = 'none';
                return;
            }

            // Check if user has custom dragged the bubble menu
            const alpineData = window.Alpine && document.querySelector('[x-data]') ? window.Alpine.$data(document.querySelector('[x-data]')) : null;
            if (alpineData && alpineData.bubblePos && alpineData.bubblePos.x !== null && alpineData.bubblePos.y !== null) {
                bubbleMenuEl.style.display = 'flex';
                bubbleMenuEl.style.position = 'fixed';
                bubbleMenuEl.style.left = `${alpineData.bubblePos.x}px`;
                bubbleMenuEl.style.top = `${alpineData.bubblePos.y}px`;
                bubbleMenuEl.style.zIndex = 'var(--z-index-floating, 9999)';
                return;
            }

            // Display to measure dimensions
            bubbleMenuEl.style.display = 'flex';
            bubbleMenuEl.style.position = 'fixed';
            bubbleMenuEl.style.zIndex = 'var(--z-index-floating, 9999)';

            const menuRect = bubbleMenuEl.getBoundingClientRect();
            
            // Viewport coordinates
            const selectionCenterX = rangeRect.left + (rangeRect.width / 2);
            let fixedLeft = selectionCenterX - (menuRect.width / 2);
            let fixedTop = rangeRect.top - menuRect.height - 10;

            // Viewport boundary protection
            fixedLeft = Math.max(10, Math.min(fixedLeft, window.innerWidth - menuRect.width - 10));

            // If selected text is too close to top of viewport, flip below selection
            if (fixedTop < 10) {
                fixedTop = rangeRect.bottom + 10;
            }

            bubbleMenuEl.style.left = `${Math.round(fixedLeft)}px`;
            bubbleMenuEl.style.top = `${Math.round(fixedTop)}px`;
        } catch (e) {
            bubbleMenuEl.style.display = 'none';
        }
    }

    init() {
        const targetElement = document.getElementById(this.elementId);
        if (!targetElement) {
            console.error(`[TiptapDriver] Element #${this.elementId} not found.`);
            return;
        }

        if (targetElement.editor) {
            try {
                targetElement.editor.destroy();
            } catch (e) {}
            targetElement.editor = null;
        }
        targetElement.innerHTML = '';

        const initialHtml = normalizeContentToHtml(this.config.initialContent || '<p></p>');
        const extensions = [
            StarterKit.configure({
                history: {
                    depth: 100,
                    newGroupDelay: 500,
                },
                heading: {
                    levels: [1, 2, 3, 4],
                },
                codeBlock: true,
            }),
            Placeholder.configure({
                placeholder: this.config.placeholder || 'Type / for AI commands or begin writing...',
                emptyEditorClass: 'is-editor-empty',
            }),
            CharacterCount,
            Typography,
            Underline,
            Subscript,
            Superscript,
            TextStyle,
            Color,
            Highlight.extend({
                addAttributes() {
                    return {
                        ...this.parent?.(),
                        class: {
                            default: null,
                            parseHTML: element => element.getAttribute('class'),
                            renderHTML: attributes => {
                                if (!attributes.class) return {};
                                return { class: attributes.class };
                            },
                        },
                    };
                },
            }).configure({ multicolor: true }),
            TaskList,
            TaskItem.configure({ nested: true }),
            Table.configure({ resizable: true }),
            TableRow,
            TableHeader,
            TableCell,
            Image.configure({ inline: true, allowBase64: true }),
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
        ];

        this.editor = new Editor({
            element: targetElement,
            editorProps: {
                attributes: {
                    class: 'prose prose-invert max-w-none focus:outline-none min-h-[500px] text-slate-200 leading-relaxed text-base font-normal tracking-wide',
                },
                handleDOMEvents: {
                    contextmenu: (view, event) => {
                        event.preventDefault();
                        window.dispatchEvent(new CustomEvent('editor:contextmenu', {
                            detail: {
                                clientX: event.clientX,
                                clientY: event.clientY,
                                x: event.clientX,
                                y: event.clientY,
                                target: event.target
                            }
                        }));
                        return true;
                    }
                },
                handleKeyDown: (view, event) => {
                    if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey) {
                        setTimeout(() => {
                            if (!this.editor || this.editor.isDestroyed) return;
                            try {
                                const { state } = this.editor;
                                const { from } = state.selection;
                                const coords = view.coordsAtPos(from);
                                window.dispatchEvent(new CustomEvent('tiptap:slash', {
                                    detail: {
                                        x: coords.left,
                                        y: coords.bottom + 8,
                                        pos: from
                                    }
                                }));
                            } catch (err) {}
                        }, 10);
                    }
                    return false;
                }
            },
            extensions: extensions,
            content: initialHtml,
            onSelectionUpdate: ({ editor }) => {
                try {
                    const { state } = editor;
                    const { from, to } = state.selection;
                    const selected = (from !== to) ? state.doc.textBetween(from, to, ' ').trim() : '';
                    window.hoaCurrentSelection = selected;
                    window.dispatchEvent(new CustomEvent('editor:selection-change', {
                        detail: { selectedText: selected, from, to }
                    }));
                } catch (err) {}
            },
            onUpdate: ({ editor }) => {
                if (typeof this.config.onUpdate === 'function') {
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => {
                        if (editor && !editor.isDestroyed) {
                            this.config.onUpdate(editor.getHTML());
                        }
                    }, this.debounceMs);
                }
                
                if (typeof this.config.onStatsChange === 'function') {
                    const text = editor.getText();
                    const words = text.trim().split(/\s+/).filter(Boolean).length;
                    const chars = text.length;
                    this.config.onStatsChange({ words, characters: chars, text });
                }

                if (typeof this.config.onFormatChange === 'function') {
                    this.config.onFormatChange();
                }
            },
            onSelectionUpdate: ({ editor }) => {
                this.updateBubbleMenu(editor);

                if (typeof this.config.onSelectionChange === 'function') {
                    const { state } = editor;
                    const { from, to, empty } = state.selection;
                    const selectedText = state.doc.textBetween(from, to, ' ');
                    this.config.onSelectionChange({
                        selectedText: selectedText,
                        isEmpty: empty,
                        from: from,
                        to: to
                    });
                }

                if (typeof this.config.onFormatChange === 'function') {
                    this.config.onFormatChange();
                }
            },
        });

        // Reposition bubble menu smoothly on container scroll or window resize
        this.scrollHandler = () => {
            if (this.editor && !this.editor.isDestroyed && !this.editor.state.selection.empty) {
                this.updateBubbleMenu(this.editor);
            }
        };

        const contentTarget = document.getElementById(this.elementId);
        if (contentTarget) {
            contentTarget.addEventListener('scroll', this.scrollHandler, { passive: true });
        }
        window.addEventListener('scroll', this.scrollHandler, { passive: true });
        window.addEventListener('resize', this.scrollHandler, { passive: true });

        document.addEventListener('selectionchange', () => {
            setTimeout(() => {
                if (this.editor && !this.editor.isDestroyed) {
                    this.updateBubbleMenu(this.editor);
                }
            }, 10);
        });
    }

    execCommand(fn) {
        if (!this.editor || this.editor.isDestroyed) return false;
        try {
            if (!this.editor.view.hasFocus()) {
                this.editor.view.focus();
            }
            const res = fn(this.editor);
            setTimeout(() => this.updateBubbleMenu(this.editor), 20);
            return res;
        } catch (error) {
            console.warn('[TiptapDriver] Command exec warning:', error);
            return false;
        }
    }

    getHTML() { 
        if (this.editor && !this.editor.isDestroyed) {
            return this.editor.getHTML();
        }
        const el = document.getElementById(this.elementId);
        const pm = el ? (el.querySelector('.ProseMirror') || el) : null;
        return pm ? pm.innerHTML : '';
    }

    get state() {
        return this.editor && !this.editor.isDestroyed ? this.editor.state : null;
    }

    getSelectedText() {
        if (!this.editor || this.editor.isDestroyed) return '';
        try {
            const { state } = this.editor;
            const { from, to } = state.selection;
            if (from !== to) {
                return state.doc.textBetween(from, to, ' ').trim();
            }
        } catch (e) {}
        return '';
    }

    getJSON() { return this.editor && !this.editor.isDestroyed ? this.editor.getJSON() : null; }
    getText() { return this.editor && !this.editor.isDestroyed ? this.editor.getText() : ''; }
    
    setContent(content, emitUpdate = true) {
        const cleanHtml = normalizeContentToHtml(content);
        
        if (this.editor && !this.editor.isDestroyed) {
            const targetElement = document.getElementById(this.elementId);
            const prevScroll = targetElement ? targetElement.scrollTop : 0;

            try {
                const res = this.editor.commands.setContent(cleanHtml, Boolean(emitUpdate));
                if (targetElement && prevScroll > 0) {
                    requestAnimationFrame(() => {
                        targetElement.scrollTop = prevScroll;
                    });
                }
                return res;
            } catch (e) {
                console.warn('[TiptapDriver] setContent error, retrying with parsed DOM:', e);
                try {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(cleanHtml, 'text/html');
                    const res = this.editor.commands.setContent(doc.body.innerHTML, Boolean(emitUpdate));
                    if (targetElement && prevScroll > 0) {
                        requestAnimationFrame(() => {
                            targetElement.scrollTop = prevScroll;
                        });
                    }
                    return res;
                } catch (err) {
                    console.error('[TiptapDriver] Failed to set content in active editor:', err);
                }
            }
        }
        return false;
    }

    insertContent(content) {
        const cleanHtml = normalizeContentToHtml(content);
        if (this.editor && !this.editor.isDestroyed) {
            try {
                return this.editor.chain().focus().insertContent(cleanHtml).run();
            } catch (e) {
                console.warn('[TiptapDriver] insertContent error:', e);
            }
        }
        return false;
    }

    replaceSelection(content) {
        const cleanHtml = normalizeContentToHtml(content);
        if (this.editor && !this.editor.isDestroyed) {
            try {
                return this.editor.chain().focus().deleteSelection().insertContent(cleanHtml).run();
            } catch (e) {
                return this.setContent(cleanHtml);
            }
        }
        return false;
    }

    appendContent(content) {
        const cleanHtml = normalizeContentToHtml(content);
        const current = this.getHTML();
        this.setContent(current + cleanHtml);
    }

    isActive(name, attrs = {}) {
        if (!this.editor || this.editor.isDestroyed) return false;
        try {
            return this.editor.isActive(name, attrs);
        } catch (e) {
            return false;
        }
    }

    // --- Core Formatting & Structure Commands with Direct Chained Execution ---
    toggleHeading(level) { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleHeading({ level: parseInt(level) || 2 }).run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleBold() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleBold().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleItalic() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleItalic().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleUnderline() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleUnderline().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleStrike() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleStrike().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleSubscript() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleSubscript().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleSuperscript() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleSuperscript().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleCode() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleCode().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleHighlight(color = null) { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = color 
            ? this.editor.chain().focus().toggleHighlight({ color }).run() 
            : this.editor.chain().focus().toggleHighlight().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleBulletList() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleBulletList().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleOrderedList() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleOrderedList().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleTaskList() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleTaskList().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleBlockquote() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleBlockquote().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    toggleCodeBlock() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().toggleCodeBlock().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    setHorizontalRule() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().setHorizontalRule().run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    setTextAlign(align) { 
        if (!this.editor || this.editor.isDestroyed) return false;
        const res = this.editor.chain().focus().setTextAlign(align).run();
        this.updateBubbleMenu(this.editor);
        return res;
    }
    setImage(attrs) { 
        if (!this.editor || this.editor.isDestroyed) return false;
        return this.editor.chain().focus().setImage(attrs).run(); 
    }
    setLink(attrs) { 
        if (!this.editor || this.editor.isDestroyed) return false;
        return this.editor.chain().focus().setLink(attrs).run(); 
    }
    clearFormatting() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        return this.editor.chain().focus().unsetAllMarks().clearNodes().run(); 
    }
    undo() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        return this.editor.chain().focus().undo().run(); 
    }
    redo() { 
        if (!this.editor || this.editor.isDestroyed) return false;
        return this.editor.chain().focus().redo().run(); 
    }

    // --- Table Management Commands ---
    insertTable(options = { rows: 3, cols: 3, withHeaderRow: true }) {
        return this.execCommand(ed => ed.chain().focus().insertTable(options).run());
    }
    addRowBefore() { return this.execCommand(ed => ed.chain().focus().addRowBefore().run()); }
    addRowAfter() { return this.execCommand(ed => ed.chain().focus().addRowAfter().run()); }
    deleteRow() { return this.execCommand(ed => ed.chain().focus().deleteRow().run()); }
    addColumnBefore() { return this.execCommand(ed => ed.chain().focus().addColumnBefore().run()); }
    addColumnAfter() { return this.execCommand(ed => ed.chain().focus().addColumnAfter().run()); }
    deleteColumn() { return this.execCommand(ed => ed.chain().focus().deleteColumn().run()); }
    deleteTable() { return this.execCommand(ed => ed.chain().focus().deleteTable().run()); }

    // --- Advanced Rich Callout & Blog Block Inserters ---
    insertCallout(type = 'tip', title = 'Pro-Tip', body = 'Your actionable insight here...') {
        const icons = { tip: '💡', warning: '⚠️', info: 'ℹ️', tldr: '⚡', caution: '🚨' };
        const titles = { tip: 'Pro-Tip & Best Practice', warning: 'Warning & Precaution', info: 'Important Context', tldr: 'Executive Summary / TL;DR', caution: 'High-Risk Caution' };
        const icon = icons[type] || '✦';
        const displayTitle = title || titles[type] || 'Key Insight';

        const calloutHtml = `
            <div class="callout-box callout-${type}">
                <div class="callout-title">${icon} <span>${displayTitle}</span></div>
                <p>${body}</p>
            </div>
        `;
        this.insertContent(calloutHtml);
    }

    insertProsCons(pros = ['Rapid inference throughput', 'Clean modular architecture'], cons = ['Requires modern browser support']) {
        const prosItems = pros.map(p => `<li>${p}</li>`).join('');
        const consItems = cons.map(c => `<li>${c}</li>`).join('');
        const html = `
            <div class="pros-cons-grid">
                <div class="pros-box">
                    <div class="pros-title">✓ <span>Key Advantages (Pros)</span></div>
                    <ul>${prosItems}</ul>
                </div>
                <div class="cons-box">
                    <div class="cons-title">✕ <span>Potential Tradeoffs (Cons)</span></div>
                    <ul>${consItems}</ul>
                </div>
            </div>
        `;
        this.insertContent(html);
    }

    insertFaqAccordion(question = 'What is the primary benefit of this system?', answer = 'It delivers instant, zero-latency content rendering with enterprise SEO optimization and high E-E-A-T trust signals.') {
        const html = `
            <details class="hoa-faq" open>
                <summary><span>${question}</span> <span class="text-indigo-400 text-xs">▼</span></summary>
                <p>${answer}</p>
            </details>
        `;
        this.insertContent(html);
    }

    insertTrustBox() {
        const html = `
            <div class="eeat-trust-card">
                <div class="eeat-header">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🏆</span>
                        <div>
                            <div class="font-bold text-white text-sm">Editorial Testing & Trust Standards</div>
                            <div class="text-[11px] text-indigo-300 font-mono">Independent Benchmark & Real-World Evaluation</div>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono text-[10px] border border-emerald-500/30">Verified 2026</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed mb-2">Our technical review team conducted 40+ hours of hands-on testing across enterprise workloads, measuring inference throughput, transaction resilience, and real-world latency.</p>
                <div class="grid grid-cols-3 gap-2 text-center text-xs font-mono pt-2 border-t border-white/10">
                    <div class="p-2 rounded-xl bg-white/5"><div class="text-indigo-400 font-bold">99.8%</div><div class="text-[10px] text-slate-400">Reliability</div></div>
                    <div class="p-2 rounded-xl bg-white/5"><div class="text-emerald-400 font-bold">12ms</div><div class="text-[10px] text-slate-400">Latency (TTFT)</div></div>
                    <div class="p-2 rounded-xl bg-white/5"><div class="text-purple-400 font-bold">4.9 / 5.0</div><div class="text-[10px] text-slate-400">Trust Score</div></div>
                </div>
            </div>
        `;
        this.insertContent(html);
    }

    insertStepTimeline() {
        const html = `
            <div class="step-walkthrough">
                <div class="step-item">
                    <div class="step-badge">1</div>
                    <h4 class="text-base font-bold text-white mb-1">Step 1: Configuration & Setup</h4>
                    <p class="text-xs text-slate-300">Initialize the workspace parameters and configure your target keywords and project goals.</p>
                </div>
                <div class="step-item">
                    <div class="step-badge">2</div>
                    <h4 class="text-base font-bold text-white mb-1">Step 2: AI Execution & Drafting</h4>
                    <p class="text-xs text-slate-300">Trigger contextual generation with multi-stage reasoning to draft exhaustive, structured content.</p>
                </div>
                <div class="step-item">
                    <div class="step-badge">3</div>
                    <h4 class="text-base font-bold text-white mb-1">Step 3: Verification & Publish</h4>
                    <p class="text-xs text-slate-300">Run the 10-point E-E-A-T audit, check Rank Math SEO scores, and export directly to your CMS.</p>
                </div>
            </div>
        `;
        this.insertContent(html);
    }

    destroy() {
        clearTimeout(this.saveTimeout);
        if (this.scrollHandler) {
            const contentTarget = document.getElementById(this.elementId);
            if (contentTarget) contentTarget.removeEventListener('scroll', this.scrollHandler);
            window.removeEventListener('scroll', this.scrollHandler);
            window.removeEventListener('resize', this.scrollHandler);
        }
        if (this.editor) {
            try {
                this.editor.destroy();
            } catch (e) {}
            this.editor = null;
        }
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('tiptap', TiptapDriver);
}
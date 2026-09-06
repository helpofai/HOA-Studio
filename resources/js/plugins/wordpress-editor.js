/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress TipTap Master Suite
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| Features:
| 1. Enterprise TipTap 3.30 Engine (StarterKit, Tables, Tasks, Typography)
| 2. Reactive Selection Bubble Menu with Boundary Clamping & Quick AI
| 3. Interactive Floating Slash Commands Palette ('/')
| 4. Custom Editorial & E-E-A-T Blocks (Callouts, Pros/Cons, FAQ, Trust)
| 5. Native WordPress Media Library Modal Integration (wp.media)
| 6. Direct SSE AI Streaming with Speed Telemetry (tok/s, tokens)
| 7. Distraction-Free Fullscreen Canvas Mode
| 8. Markdown & Rich HTML Normalizer
|
|--------------------------------------------------------------------------
*/

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Highlight from '@tiptap/extension-highlight';
import Typography from '@tiptap/extension-typography';
import TaskItem from '@tiptap/extension-task-item';
import TaskList from '@tiptap/extension-task-list';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';
import { Table } from '@tiptap/extension-table';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableRow } from '@tiptap/extension-table-row';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import Link from '@tiptap/extension-link';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import { common, createLowlight } from 'lowlight';

const lowlight = createLowlight(common);

/**
 * Enterprise Custom Code Block with Dark Terminal Chrome, Language Selection, and 1-Click Copy
 */
export const CustomCodeBlockLowlight = CodeBlockLowlight.extend({
    addNodeView() {
        return ({ node, HTMLAttributes, getPos, editor }) => {
            const dom = document.createElement('div');
            dom.className = 'hoa-code-block-wrapper relative my-5 rounded-2xl border border-white/10 bg-slate-950/95 overflow-hidden shadow-2xl';

            const header = document.createElement('div');
            header.className = 'hoa-code-header flex items-center justify-between px-3.5 py-2 bg-slate-900/90 border-b border-white/10 select-none';
            header.contentEditable = 'false';

            // Left: macOS terminal dots + language select
            const leftSide = document.createElement('div');
            leftSide.className = 'flex items-center gap-2.5';
            leftSide.innerHTML = `
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80 inline-block"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80 inline-block"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80 inline-block"></span>
                </div>
            `;

            const select = document.createElement('select');
            select.className = 'bg-transparent text-[11px] font-mono font-bold text-indigo-400 hover:text-indigo-300 focus:outline-none cursor-pointer uppercase py-0.5 px-1.5 rounded hover:bg-white/5 border border-transparent hover:border-white/10 transition-colors';
            
            const languages = [
                { value: 'javascript', label: 'JavaScript' },
                { value: 'typescript', label: 'TypeScript' },
                { value: 'python', label: 'Python' },
                { value: 'php', label: 'PHP' },
                { value: 'html', label: 'HTML' },
                { value: 'css', label: 'CSS' },
                { value: 'sql', label: 'SQL' },
                { value: 'json', label: 'JSON' },
                { value: 'bash', label: 'Bash' },
                { value: 'markdown', label: 'Markdown' },
                { value: 'yaml', label: 'YAML' },
                { value: 'go', label: 'Go' },
                { value: 'rust', label: 'Rust' },
                { value: 'java', label: 'Java' },
                { value: 'csharp', label: 'C#' },
                { value: 'cpp', label: 'C++' },
                { value: 'c', label: 'C' },
                { value: 'diff', label: 'Diff' },
                { value: 'text', label: 'Plain Text' },
            ];

            const currentLang = (node.attrs.language || 'javascript').toLowerCase();
            let matched = false;

            languages.forEach(l => {
                const opt = document.createElement('option');
                opt.value = l.value;
                opt.textContent = l.label;
                opt.className = 'bg-slate-900 text-slate-200';
                if (l.value === currentLang) {
                    opt.selected = true;
                    matched = true;
                }
                select.appendChild(opt);
            });

            if (!matched && currentLang) {
                const customOpt = document.createElement('option');
                customOpt.value = currentLang;
                customOpt.textContent = currentLang.toUpperCase();
                customOpt.selected = true;
                customOpt.className = 'bg-slate-900 text-slate-200';
                select.appendChild(customOpt);
            }

            select.addEventListener('change', (e) => {
                if (typeof getPos === 'function') {
                    const pos = getPos();
                    editor.chain().focus().command(({ tr }) => {
                        tr.setNodeMarkup(pos, undefined, {
                            ...node.attrs,
                            language: e.target.value
                        });
                        return true;
                    }).run();
                }
            });
            leftSide.appendChild(select);

            // Right: 1-Click Copy button
            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'hoa-copy-code-btn flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[11px] font-medium text-slate-300 hover:text-white transition-all cursor-pointer border border-white/5';
            copyBtn.innerHTML = '<span>📋</span> <span>Copy</span>';

            copyBtn.addEventListener('click', () => {
                const codeText = node.textContent;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(codeText).then(() => {
                        copyBtn.innerHTML = '<span class="text-emerald-400 font-bold">✓</span> <span class="text-emerald-300 font-bold">Copied!</span>';
                        copyBtn.classList.add('bg-emerald-500/10', 'border-emerald-500/30');
                        setTimeout(() => {
                            copyBtn.innerHTML = '<span>📋</span> <span>Copy</span>';
                            copyBtn.classList.remove('bg-emerald-500/10', 'border-emerald-500/30');
                        }, 2000);
                    }).catch(() => {});
                }
            });

            header.appendChild(leftSide);
            header.appendChild(copyBtn);

            const pre = document.createElement('pre');
            pre.className = 'p-4 overflow-x-auto font-mono text-sm leading-relaxed text-slate-200';
            const code = document.createElement('code');
            pre.appendChild(code);

            dom.appendChild(header);
            dom.appendChild(pre);

            return {
                dom,
                contentDOM: code,
                update(updatedNode) {
                    if (updatedNode.type !== node.type) return false;
                    const newLang = (updatedNode.attrs.language || 'javascript').toLowerCase();
                    if (select.value !== newLang) {
                        select.value = newLang;
                    }
                    return true;
                }
            };
        };
    }
});

/**
 * High-Fidelity Markdown & HTML Parser and Normalizer
 */
export function normalizeContentToHtml(content) {
    if (!content || typeof content !== 'string') return '<p></p>';
    
    let text = content.trim();
    if (!text) return '<p></p>';

    const isHtmlBlock = /^<(!DOCTYPE|html|head|body|div|p|h[1-6]|table|blockquote|ul|ol|article|section|details|pre)/i.test(text)
        || /<\/([a-z]+)>/i.test(text);

    if (isHtmlBlock) {
        text = text.replace(/<!--[\s\S]*?-->/g, '');
        return text.trim();
    }

    text = text.replace(/\n{3,}/g, '\n\n');

    // Code blocks
    text = text.replace(/```([a-z0-9_-]*)\n([\s\S]*?)```/gim, (match, lang, code) => {
        return `<pre><code class="language-${lang || 'text'}">${code.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>`;
    });

    // Rich Callout Boxes (> [!TIP], > [!WARNING], > [!NOTE], > [!IMPORTANT], > [!CAUTION])
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

    // Tables
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

    // Headings
    text = text.replace(/^######\s+(.*?)$/gm, '<h6>$1</h6>');
    text = text.replace(/^#####\s+(.*?)$/gm, '<h5>$1</h5>');
    text = text.replace(/^####\s+(.*?)$/gm, '<h4>$1</h4>');
    text = text.replace(/^###\s+(.*?)$/gm, '<h3>$1</h3>');
    text = text.replace(/^##\s+(.*?)$/gm, '<h2>$1</h2>');
    text = text.replace(/^#\s+(.*?)$/gm, '<h1>$1</h1>');

    // Blockquotes
    text = text.replace(/^>\s+(.*?)$/gm, '<blockquote><p>$1</p></blockquote>');

    // Task Checklists
    text = text.replace(/(^[*-]\s+\[([ xX])\]\s+.*(?:\r?\n[*-]\s+\[([ xX])\]\s+.*)*)/gm, (taskMatch) => {
        const items = taskMatch.trim().split(/\r?\n/).map(line => {
            const isChecked = /^[*-]\s+\[[xX]\]/.test(line);
            const content = line.replace(/^[*-]\s+\[[ xX]\]\s+/, '');
            return `<li data-type="taskItem" data-checked="${isChecked ? 'true' : 'false'}"><label><input type="checkbox"${isChecked ? ' checked' : ''}><span></span></label><div><p>${content}</p></div></li>`;
        }).join('');
        return `<ul data-type="taskList">${items}</ul>`;
    });

    // Lists
    text = text.replace(/(^[*-]\s+(?!\[[ xX]\]).*(\r?\n[*-]\s+(?!\[[ xX]\]).*)*)/gm, (listMatch) => {
        const items = listMatch.trim().split(/\r?\n/).map(line => `<li>${line.replace(/^[*-]\s+/, '')}</li>`).join('');
        return `<ul>${items}</ul>`;
    });
    text = text.replace(/(^\d+\.\s+.*(?:\r?\n\d+\.\s+.*)*)/gm, (listMatch) => {
        const items = listMatch.trim().split(/\r?\n/).map(line => `<li>${line.replace(/^\d+\.\s+/, '')}</li>`).join('');
        return `<ol>${items}</ol>`;
    });

    // Dividers
    text = text.replace(/^(\*{3,}|-{3,}|_{3,})$/gm, '<hr>');

    // Inline formatting
    text = text.replace(/~([^~]+)~/g, '<sub>$1</sub>');
    text = text.replace(/\^([^^]+)\^/g, '<sup>$1</sup>');
    text = text.replace(/__([^_]+)__/g, '<u>$1</u>');
    text = text.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    text = text.replace(/==(.*?)==/g, '<mark>$1</mark>');
    text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
    text = text.replace(/~~(.*?)~~/g, '<s>$1</s>');

    // Paragraph blocks
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

(function($) {
    'use strict';

    $(document).ready(function() {
        const $canvas = $('#hoa-wp-tiptap-target');
        const $hiddenInput = $('#hoa_tiptap_html_content');
        const $wordCount = $('#hoa-wp-word-count');
        const $charCount = $('#hoa-wp-char-count');
        const $readingTime = $('#hoa-wp-reading-time');
        const $aiBar = $('#hoa-wp-ai-bar');
        const $streamingStatus = $('#hoa-wp-streaming-indicator');
        const $bubbleMenu = $('#hoa-wp-bubble-menu');
        const $slashMenu = $('#hoa-wp-slash-menu');
        const $wrapper = $('.hoa-wp-editor-wrapper');
        
        if (!$canvas.length) {
            return;
        }

        let activeStreamReader = null;
        let lastSlashPosition = null;

        // Hoisted intelligence & outline callbacks to prevent ReferenceError across scopes
        let updateDynamicOutline = function() {};
        let updateSeoScore = function() {};
        let updateEeatQualityScore = function() {};
        let updateKeywordDensityMatrix = function() {};

        // Initialize Native TipTap Editor
        const rawInitialContent = $hiddenInput.val() || $canvas.html() || '';
        const initialContent = normalizeContentToHtml(rawInitialContent);
        $canvas.empty();

        const editor = new Editor({
            element: $canvas[0],
            extensions: [
                StarterKit.configure({
                    heading: { levels: [1, 2, 3, 4] },
                    codeBlock: false,
                    link: { openOnClick: false, HTMLAttributes: { class: 'hoa-editor-link' } },
                }),
                CustomCodeBlockLowlight.configure({ lowlight }),
                Placeholder.configure({
                    placeholder: 'Type / for AI commands or begin writing with HOA Studio...',
                    emptyEditorClass: 'is-editor-empty',
                }),
                CharacterCount,
                Typography,
                Subscript,
                Superscript,
                TextStyle,
                Color,
                Highlight.configure({ multicolor: true }),
                TaskList,
                TaskItem.configure({ nested: true }),
                Table.configure({ resizable: true }),
                TableRow,
                TableHeader,
                TableCell,
                Image.configure({ inline: true, allowBase64: true }),
                TextAlign.configure({ types: ['heading', 'paragraph'] }),
            ],
            content: initialContent,
            editorProps: {
                attributes: {
                    class: 'hoa-prosemirror-body focus:outline-none min-h-[500px] w-full max-w-none text-slate-200 leading-relaxed text-base',
                },
                handleKeyDown: (view, event) => {
                    // Slash commands trigger
                    if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey) {
                        setTimeout(() => {
                            if (!editor || editor.isDestroyed) return;
                            try {
                                const { state } = editor;
                                const { from } = state.selection;
                                const coords = view.coordsAtPos(from);
                                lastSlashPosition = from;
                                openSlashMenu(coords.left, coords.bottom + 8);
                            } catch (err) {}
                        }, 10);
                    }
                    if (event.key === 'Escape') {
                        closeSlashMenu();
                        closeBubbleMenu();
                        closeContextMenu();
                        closeLinkModal();
                    }
                    return false;
                },
            },
            onUpdate: ({ editor }) => {
                const html = editor.getHTML();
                $hiddenInput.val(html);
                updateStats(editor.getText());
            },
            onSelectionUpdate: ({ editor }) => {
                updateBubbleMenu(editor);
                updateTableControls();
            },
        });

        // Expose editor instance globally for external plugins / diagnostics
        window.hoaWpTipTapEditor = editor;

        // Ensure editor receives immediate focus when canvas is clicked
        $canvas.on('click', function(e) {
            if (editor && !editor.isDestroyed) {
                if (e.target === this || !$(e.target).closest('.ProseMirror').length) {
                    editor.commands.focus('end');
                }
            }
        });

        // Teleport floating toolbars to body as strictly hidden elements to prevent click blocking
        $bubbleMenu.hide().appendTo('body');
        $slashMenu.hide().appendTo('body');
        const $contextMenu = $('#hoa-wp-context-menu').hide().appendTo('body');
        const $linkModal = $('#hoa-wp-link-modal').hide().appendTo('body');
        const $tableControls = $('#hoa-wp-table-controls').hide().appendTo('body');

        function updateStats(text = '') {
            const words = text.trim().split(/\s+/).filter(Boolean).length;
            const chars = text.length;
            $wordCount.text(words.toLocaleString());
            if ($charCount.length) $charCount.text(chars.toLocaleString());
            $readingTime.text(Math.max(1, Math.ceil(words / 200)) + 'm');
        }

        updateStats(editor.getText());

        // ==========================================
        // FLOATING SELECTION BUBBLE TOOLBAR
        // ==========================================
        let isDraggingBubble = false;
        let bubbleDragOffset = { x: 0, y: 0 };

        $(document).on('mousedown', '.hoa-bubble-drag-handle', function(e) {
            isDraggingBubble = true;
            const rect = $bubbleMenu[0].getBoundingClientRect();
            bubbleDragOffset.x = e.clientX - rect.left;
            bubbleDragOffset.y = e.clientY - rect.top;
            e.preventDefault();
        });

        $(document).on('mousemove', function(e) {
            if (!isDraggingBubble) return;
            const newX = Math.max(10, Math.min(window.innerWidth - ($bubbleMenu.outerWidth() || 460), e.clientX - bubbleDragOffset.x));
            const newY = Math.max(10, Math.min(window.innerHeight - 60, e.clientY - bubbleDragOffset.y));
            $bubbleMenu.css({ left: `${Math.round(newX)}px`, top: `${Math.round(newY)}px` });
        });

        $(document).on('mouseup', function() {
            isDraggingBubble = false;
        });

        function updateBubbleMenu(ed = editor) {
            if (!$bubbleMenu.length || !ed || ed.isDestroyed) return;

            const { state } = ed;
            const { from, to, empty } = state.selection;

            if (empty || from === to || (to - from) <= 0) {
                closeBubbleMenu();
                return;
            }

            try {
                const domSelection = window.getSelection();
                if (!domSelection || domSelection.rangeCount === 0) {
                    closeBubbleMenu();
                    return;
                }

                const range = domSelection.getRangeAt(0);
                const rangeRect = range.getBoundingClientRect();

                if (rangeRect.width === 0 && rangeRect.height === 0) {
                    closeBubbleMenu();
                    return;
                }

                $bubbleMenu.show();
                const menuWidth = $bubbleMenu.outerWidth() || 520;
                const menuHeight = $bubbleMenu.outerHeight() || 44;

                const selectionCenterX = rangeRect.left + (rangeRect.width / 2);
                let fixedLeft = selectionCenterX - (menuWidth / 2);
                let fixedTop = rangeRect.top - menuHeight - 12;

                // Viewport Clamping bounds
                const minLeft = 14;
                const maxLeft = window.innerWidth - menuWidth - 14;
                fixedLeft = Math.max(minLeft, Math.min(fixedLeft, Math.max(minLeft, maxLeft)));

                if (fixedTop < 50) {
                    fixedTop = rangeRect.bottom + 12;
                }

                $bubbleMenu.css({
                    position: 'fixed',
                    left: `${Math.round(fixedLeft)}px`,
                    top: `${Math.round(fixedTop)}px`,
                    zIndex: 999999
                });

                // Update active states on bubble buttons
                updateBubbleActiveStates(ed);
            } catch (e) {
                closeBubbleMenu();
            }
        }

        function updateBubbleActiveStates(ed = editor) {
            $bubbleMenu.find('[data-cmd="bold"]').toggleClass('active', ed.isActive('bold'));
            $bubbleMenu.find('[data-cmd="italic"]').toggleClass('active', ed.isActive('italic'));
            $bubbleMenu.find('[data-cmd="underline"]').toggleClass('active', ed.isActive('underline'));
            $bubbleMenu.find('[data-cmd="strike"]').toggleClass('active', ed.isActive('strike'));
            $bubbleMenu.find('[data-cmd="highlight"]').toggleClass('active', ed.isActive('highlight'));
            $bubbleMenu.find('[data-cmd="code"]').toggleClass('active', ed.isActive('code'));
            $bubbleMenu.find('#hoa-bubble-link-btn').toggleClass('active', ed.isActive('link'));
            $bubbleMenu.find('[data-cmd="heading1"]').toggleClass('active', ed.isActive('heading', { level: 1 }));
            $bubbleMenu.find('[data-cmd="heading2"]').toggleClass('active', ed.isActive('heading', { level: 2 }));
            $bubbleMenu.find('[data-cmd="heading3"]').toggleClass('active', ed.isActive('heading', { level: 3 }));
            $bubbleMenu.find('[data-cmd="blockquote"]').toggleClass('active', ed.isActive('blockquote'));
            $bubbleMenu.find('[data-cmd="bulletList"]').toggleClass('active', ed.isActive('bulletList'));
            $bubbleMenu.find('[data-cmd="orderedList"]').toggleClass('active', ed.isActive('orderedList'));
            $bubbleMenu.find('[data-cmd="taskList"]').toggleClass('active', ed.isActive('taskList'));
        }

        function closeBubbleMenu() {
            $bubbleMenu.hide();
            $('#hoa-wp-bubble-ai-dropdown').hide();
        }

        // Reposition bubble menu smoothly on window scroll & resize
        $(window).on('scroll resize', () => {
            if (editor && !editor.isDestroyed && !editor.state.selection.empty) {
                updateBubbleMenu(editor);
            }
        });

        // Bubble AI dropdown toggle
        $('#hoa-wp-bubble-ai-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#hoa-wp-bubble-ai-dropdown').toggle();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#hoa-wp-bubble-menu').length) {
                $('#hoa-wp-bubble-ai-dropdown').hide();
            }
            if (!$(e.target).closest('#hoa-wp-slash-menu').length) {
                closeSlashMenu();
            }
            if (!$(e.target).closest('#hoa-wp-context-menu').length) {
                closeContextMenu();
            }
            if (!$(e.target).closest('#hoa-wp-link-modal, #hoa-bubble-link-btn, [data-cmd="link"]').length) {
                closeLinkModal();
            }
        });

        // Bubble Quick AI Trigger
        $('.hoa-bubble-ai-item').on('click', function(e) {
            e.preventDefault();
            const transformType = $(this).data('ai-action');
            $('#hoa-wp-bubble-ai-dropdown').hide();
            closeBubbleMenu();

            const { state } = editor;
            const { from, to } = state.selection;
            const selectedText = from !== to ? state.doc.textBetween(from, to, ' ') : '';
            
            if (!selectedText) return;

            if (transformType === 'recreate') {
                triggerParagraphRecreation(selectedText, from, to);
            } else {
                triggerAiStream({
                    prompt: '',
                    type: transformType,
                    selectedText: selectedText,
                    placement: 'replace',
                    fromPos: from,
                    toPos: to,
                });
            }
        });

        // ==========================================
        // CUSTOM RIGHT-CLICK CONTEXT MENU
        // ==========================================
        $canvas.on('contextmenu', function(e) {
            e.preventDefault();
            closeBubbleMenu();
            closeSlashMenu();
            closeLinkModal();

            // Detect if right-click occurred inside a table or table cell
            const isTable = editor.isActive('table') || $(e.target).closest('table, td, th').length > 0;
            if (isTable) {
                $('#hoa-wp-context-table-section').show();
            } else {
                $('#hoa-wp-context-table-section').hide();
            }

            $contextMenu.show();
            const menuWidth = $contextMenu.outerWidth() || 260;
            const menuHeight = $contextMenu.outerHeight() || 440;
            let left = Math.min(window.innerWidth - menuWidth - 14, Math.max(14, e.clientX));
            let top = Math.min(window.innerHeight - menuHeight - 14, Math.max(14, e.clientY));

            $contextMenu.css({
                position: 'fixed',
                left: `${Math.round(left)}px`,
                top: `${Math.round(top)}px`,
                zIndex: 999999
            });
        });

        function closeContextMenu() {
            $contextMenu.hide();
        }

        $(document).on('click', '.hoa-context-item', function(e) {
            e.preventDefault();
            const cmd = $(this).data('context-cmd');
            closeContextMenu();

            const { state } = editor;
            const { from, to } = state.selection;
            const selectedText = from !== to ? state.doc.textBetween(from, to, ' ') : '';

            switch (cmd) {
                // Table Operations
                case 'addRowBefore':
                case 'addRowAfter':
                case 'deleteRow':
                case 'addColumnBefore':
                case 'addColumnAfter':
                case 'deleteColumn':
                case 'toggleHeaderRow':
                case 'mergeOrSplit':
                case 'deleteTable':
                    executeAction(cmd);
                    break;

                // Clipboard & Selection
                case 'cut':
                    if (selectedText) {
                        navigator.clipboard.writeText(selectedText).catch(() => {});
                        editor.chain().focus().deleteSelection().run();
                    }
                    break;
                case 'copy':
                    if (selectedText) {
                        navigator.clipboard.writeText(selectedText).catch(() => {});
                    }
                    break;
                case 'paste':
                    if (navigator.clipboard && navigator.clipboard.readText) {
                        navigator.clipboard.readText().then(text => {
                            if (text) editor.chain().focus().insertContent(text).run();
                        }).catch(() => {});
                    }
                    break;
                case 'select_all':
                    editor.commands.selectAll();
                    break;
                case 'ask_ai_inline':
                    $('#hoa-wp-btn-ask-ai').trigger('click');
                    break;
                case 'recreate':
                    if (selectedText) {
                        triggerParagraphRecreation(selectedText, from, to);
                    }
                    break;
                case 'rewrite':
                case 'expand':
                case 'shorten':
                case 'simplify':
                case 'generate_faq':
                case 'key_takeaways':
                    if (selectedText) {
                        triggerAiStream({
                            prompt: '',
                            type: cmd,
                            selectedText: selectedText,
                            placement: 'replace',
                            fromPos: from,
                            toPos: to,
                        });
                    }
                    break;
                case 'seo_optimize':
                    if (selectedText) {
                        triggerAiStream({
                            prompt: 'Optimize this text for maximum SEO search intent, authoritative depth, and natural keyword integration.',
                            type: 'rewrite',
                            selectedText: selectedText,
                            placement: 'replace',
                            fromPos: from,
                            toPos: to,
                        });
                    }
                    break;
                case 'insert_date':
                    const now = new Date();
                    const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    editor.chain().focus().insertContent(`<strong>${dateStr}</strong>`).run();
                    break;
                case 'insert_hr':
                    editor.chain().focus().setHorizontalRule().run();
                    break;
                case 'delete_selection':
                    editor.chain().focus().deleteSelection().run();
                    break;
            }
        });

        // Tone shifter buttons inside context menu
        $('.hoa-tone-btn').on('click', function(e) {
            e.preventDefault();
            const tone = $(this).data('tone');
            closeContextMenu();

            const { state } = editor;
            const { from, to } = state.selection;
            const selectedText = from !== to ? state.doc.textBetween(from, to, ' ') : '';
            if (selectedText) {
                triggerAiStream({
                    prompt: `Rewrite this paragraph in an authoritative, high-impact ${tone} tone.`,
                    type: 'rewrite',
                    selectedText: selectedText,
                    placement: 'replace',
                    fromPos: from,
                    toPos: to,
                });
            }
        });

        // ==========================================
        // FLOATING LINK TOOLTIP / MODAL
        // ==========================================
        $('#hoa-bubble-link-btn, [data-cmd="link"]').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openLinkModal();
        });

        function openLinkModal() {
            const previousUrl = editor.getAttributes('link').href || '';
            $('#hoa-link-url-input').val(previousUrl);

            const domSelection = window.getSelection();
            if (domSelection && domSelection.rangeCount > 0) {
                const rect = domSelection.getRangeAt(0).getBoundingClientRect();
                let left = Math.max(14, Math.min(window.innerWidth - 350, rect.left));
                let top = rect.bottom + 10;
                $linkModal.css({
                    position: 'fixed',
                    left: `${Math.round(left)}px`,
                    top: `${Math.round(top)}px`,
                    zIndex: 999999
                }).show();
                $('#hoa-link-url-input').focus();
            }
        }

        function closeLinkModal() {
            $linkModal.hide();
        }

        $('#hoa-btn-apply-link').on('click', function() {
            const url = $('#hoa-link-url-input').val().trim();
            const blank = $('#hoa-link-blank-check').is(':checked');
            if (url) {
                editor.chain().focus().extendMarkRange('link').setLink({
                    href: url,
                    target: blank ? '_blank' : null
                }).run();
            } else {
                editor.chain().focus().unsetLink().run();
            }
            closeLinkModal();
        });

        $('#hoa-link-url-input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#hoa-btn-apply-link').trigger('click');
            }
        });

        $('#hoa-btn-remove-link').on('click', function() {
            editor.chain().focus().unsetLink().run();
            closeLinkModal();
        });

        // ==========================================
        // FLOATING TABLE CONTEXTUAL CONTROLS
        // ==========================================
        function updateTableControls() {
            if (!editor || editor.isDestroyed || !editor.isActive('table')) {
                $tableControls.hide();
                return;
            }
            try {
                const domSelection = window.getSelection();
                if (!domSelection || domSelection.rangeCount === 0) {
                    $tableControls.hide();
                    return;
                }
                const anchorNode = domSelection.anchorNode;
                const cell = (anchorNode?.nodeType === 1 ? anchorNode : anchorNode?.parentElement)?.closest('td, th');
                const table = cell?.closest('table') || (anchorNode?.nodeType === 1 ? anchorNode : anchorNode?.parentElement)?.closest('table');
                if (!table) {
                    $tableControls.hide();
                    return;
                }
                const tableRect = table.getBoundingClientRect();
                $tableControls.css({ display: 'flex', position: 'fixed', zIndex: 999999 });
                const toolbarWidth = $tableControls.outerWidth() || 460;
                const toolbarHeight = $tableControls.outerHeight() || 38;
                let fixedLeft = tableRect.left + (tableRect.width / 2) - (toolbarWidth / 2);
                fixedLeft = Math.max(14, Math.min(fixedLeft, window.innerWidth - toolbarWidth - 14));
                let fixedTop = tableRect.top - toolbarHeight - 8;
                if (fixedTop < 60) {
                    fixedTop = tableRect.bottom + 8;
                }
                $tableControls.css({ left: `${Math.round(fixedLeft)}px`, top: `${Math.round(fixedTop)}px` });
            } catch (e) {
                $tableControls.hide();
            }
        }

        $('.hoa-table-btn').on('click', function(e) {
            e.preventDefault();
            const cmd = $(this).data('table-cmd');
            if (cmd) executeAction(cmd);
        });

        // ==========================================
        // PARAGRAPH RECREATION / PROPOSAL REVIEW MODE
        // ==========================================
        let lastProposalRange = null;
        let lastProposalText = '';

        function triggerParagraphRecreation(selectedText, fromPos, toPos) {
            lastProposalRange = { from: fromPos, to: toPos };
            lastProposalText = '';

            $('#hoa-wp-ai-proposal-box').show();
            $('#hoa-proposal-body').html('<span class="text-emerald-400">✦ Sub-content-sub-agent is analyzing & drafting...</span>');

            triggerAiStream({
                prompt: 'Recreate and enhance this paragraph with deep technical clarity, active voice, and engaging rhythm.',
                type: 'rewrite',
                selectedText: selectedText,
                placement: 'proposal',
                fromPos: fromPos,
                toPos: toPos,
            });
        }

        $('#hoa-btn-accept-proposal').on('click', function() {
            if (lastProposalRange && lastProposalText) {
                editor.chain().focus().setTextSelection(lastProposalRange).insertContent(lastProposalText).run();
            }
            $('#hoa-wp-ai-proposal-box').slideUp();
        });

        $('#hoa-btn-discard-proposal').on('click', function() {
            $('#hoa-wp-ai-proposal-box').slideUp();
            lastProposalRange = null;
            lastProposalText = '';
        });

        // ==========================================
        // INTERACTIVE SLASH COMMANDS PALETTE ('/')
        // ==========================================
        function openSlashMenu(x, y) {
            if (!$slashMenu.length) return;
            const menuWidth = 280;
            let left = Math.min(window.innerWidth - menuWidth - 20, Math.max(20, x));
            let top = Math.min(window.innerHeight - 380, y);
            $slashMenu.css({ left: `${Math.round(left)}px`, top: `${Math.round(top)}px` }).fadeIn(100);
            $('#hoa-wp-slash-filter').val('').focus();
            filterSlashItems('');
        }

        function closeSlashMenu() {
            if ($slashMenu.is(':visible')) {
                $slashMenu.fadeOut(100);
            }
        }

        $('#hoa-wp-slash-filter').on('input', function() {
            filterSlashItems($(this).val().toLowerCase());
        });

        function filterSlashItems(q) {
            $('.hoa-slash-item').each(function() {
                const text = $(this).text().toLowerCase();
                const cmd = $(this).data('slash-cmd') || '';
                if (!q || text.includes(q) || cmd.includes(q)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Execute Slash Action
        $('.hoa-slash-item').on('click', function(e) {
            e.preventDefault();
            const action = $(this).data('slash-cmd');
            closeSlashMenu();

            // Erase the '/' trigger character
            if (lastSlashPosition !== null) {
                try {
                    editor.chain().focus().setTextSelection({ from: lastSlashPosition - 1, to: lastSlashPosition }).deleteSelection().run();
                } catch(e) {}
                lastSlashPosition = null;
            }

            executeAction(action);
        });

        // ==========================================
        // MASTER TOOLBAR COMMAND DISPATCHER
        // ==========================================
        function executeAction(cmd, extra = {}) {
            editor.chain().focus();

            switch(cmd) {
                // Typography & Marks
                case 'bold': editor.chain().focus().toggleBold().run(); break;
                case 'italic': editor.chain().focus().toggleItalic().run(); break;
                case 'underline': editor.chain().focus().toggleUnderline().run(); break;
                case 'strike': editor.chain().focus().toggleStrike().run(); break;
                case 'subscript': editor.chain().focus().toggleSubscript().run(); break;
                case 'superscript': editor.chain().focus().toggleSuperscript().run(); break;
                case 'code': editor.chain().focus().toggleCode().run(); break;
                case 'codeBlock': editor.chain().focus().toggleCodeBlock().run(); break;
                case 'highlight': editor.chain().focus().toggleHighlight().run(); break;
                case 'link': openLinkModal(); break;
                case 'clearFormatting': editor.chain().focus().unsetAllMarks().clearNodes().run(); break;

                // Headings
                case 'heading1': editor.chain().focus().toggleHeading({ level: 1 }).run(); break;
                case 'heading2': editor.chain().focus().toggleHeading({ level: 2 }).run(); break;
                case 'heading3': editor.chain().focus().toggleHeading({ level: 3 }).run(); break;
                case 'heading4': editor.chain().focus().toggleHeading({ level: 4 }).run(); break;
                case 'paragraph': editor.chain().focus().setParagraph().run(); break;

                // Alignments
                case 'alignLeft': editor.chain().focus().setTextAlign('left').run(); break;
                case 'alignCenter': editor.chain().focus().setTextAlign('center').run(); break;
                case 'alignRight': editor.chain().focus().setTextAlign('right').run(); break;
                case 'alignJustify': editor.chain().focus().setTextAlign('justify').run(); break;

                // Lists & Dividers
                case 'bulletList': editor.chain().focus().toggleBulletList().run(); break;
                case 'orderedList': editor.chain().focus().toggleOrderedList().run(); break;
                case 'taskList': editor.chain().focus().toggleTaskList().run(); break;
                case 'blockquote': editor.chain().focus().toggleBlockquote().run(); break;
                case 'hr': editor.chain().focus().setHorizontalRule().run(); break;

                // Tables
                case 'table':
                case 'insertTable':
                    editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                    break;
                case 'addRowBefore': editor.chain().focus().addRowBefore().run(); break;
                case 'addRowAfter': editor.chain().focus().addRowAfter().run(); break;
                case 'deleteRow': editor.chain().focus().deleteRow().run(); break;
                case 'addColumnBefore': editor.chain().focus().addColumnBefore().run(); break;
                case 'addColumnAfter': editor.chain().focus().addColumnAfter().run(); break;
                case 'deleteColumn': editor.chain().focus().deleteColumn().run(); break;
                case 'toggleHeaderRow': editor.chain().focus().toggleHeaderRow().run(); break;
                case 'mergeOrSplit': editor.chain().focus().mergeOrSplit().run(); break;
                case 'deleteTable': editor.chain().focus().deleteTable().run(); break;

                // Custom Rich Editorial Blocks
                case 'callout-tip':
                    insertCallout('tip', 'Pro-Tip & Actionable Insight', 'Apply this technical recommendation to maximize throughput and search visibility.');
                    break;
                case 'callout-warning':
                    insertCallout('warning', 'Warning & Precaution', 'Always back up critical configuration files and verify compatibility before proceeding.');
                    break;
                case 'callout-info':
                    insertCallout('info', 'Important Context & Notes', 'This feature operates asynchronously and preserves zero-latency responsiveness.');
                    break;
                case 'callout-tldr':
                    insertCallout('tldr', 'Executive Summary / TL;DR', 'Summary: Automated high-performance AI generation tailored for search engines and readers.');
                    break;
                case 'callout-caution':
                    insertCallout('caution', 'High-Risk Caution', 'Irreversible action: ensure staging evaluation prior to production rollout.');
                    break;

                // Interactive Editorial Cards
                case 'block-proscons':
                    insertProsCons(['High-speed inference throughput', 'Integrated E-E-A-T and SEO schema', 'Zero-friction WordPress workflow'], ['Requires active Studio Connect Key']);
                    break;
                case 'block-faq':
                    insertFaqAccordion('What makes this TipTap editor superior to standard WP editors?', 'It incorporates enterprise-grade multi-model AI streaming, live E-E-A-T trust signals, real-time table manipulation, and contextual slash commands.');
                    break;
                case 'block-trust':
                    insertTrustBox();
                    break;
                case 'block-timeline':
                    insertStepTimeline();
                    break;

                // Media & AI
                case 'media':
                    openWpMediaModal();
                    break;
                case 'ask_ai':
                    $('#hoa-wp-btn-ask-ai').trigger('click');
                    break;
                case 'continue_writing':
                    triggerAiStream({ prompt: 'Continue writing the next paragraphs naturally with authoritative depth and clarity.', type: 'generate', placement: 'insert_below' });
                    break;
                case 'generate_outline':
                    triggerAiStream({ prompt: 'Generate a comprehensive, SEO-optimized H2/H3 article outline with key bullet points.', type: 'generate', placement: 'insert_below' });
                    break;
                case 'quick_answer':
                    insertCallout('tldr', 'Quick Answer (Featured Snippet)', 'The definitive solution in 2-3 sentences optimized for direct search-intent satisfaction.');
                    break;

                // History
                case 'undo': editor.chain().focus().undo().run(); break;
                case 'redo': editor.chain().focus().redo().run(); break;
            }

            setTimeout(() => updateBubbleMenu(editor), 30);
        }

        $('.hoa-tool-btn, .hoa-dropdown-item').on('click', function(e) {
            e.preventDefault();
            const cmd = $(this).data('cmd');
            if (cmd) {
                executeAction(cmd);
            }
        });

        // Dropdown menu toggles
        $('.hoa-dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $menu = $(this).siblings('.hoa-dropdown-menu');
            $('.hoa-dropdown-menu').not($menu).hide();
            $menu.toggle();
        });

        $(document).on('click', function() {
            $('.hoa-dropdown-menu').hide();
        });

        // ==========================================
        // CUSTOM EDITORIAL BLOCKS GENERATORS
        // ==========================================
        function insertCallout(type, title, body) {
            const icons = { tip: '💡', warning: '⚠️', info: 'ℹ️', tldr: '⚡', caution: '🚨' };
            const icon = icons[type] || '✦';
            const html = `
                <div class="callout-box callout-${type}">
                    <div class="callout-title">${icon} <span>${title}</span></div>
                    <p>${body}</p>
                </div>
            `;
            editor.chain().focus().insertContent(html).run();
        }

        function insertProsCons(pros, cons) {
            const prosList = pros.map(p => `<li>${p}</li>`).join('');
            const consList = cons.map(c => `<li>${c}</li>`).join('');
            const html = `
                <div class="pros-cons-grid">
                    <div class="pros-box">
                        <div class="pros-title">✓ <span>Key Advantages (Pros)</span></div>
                        <ul>${prosList}</ul>
                    </div>
                    <div class="cons-box">
                        <div class="cons-title">✕ <span>Potential Tradeoffs (Cons)</span></div>
                        <ul>${consList}</ul>
                    </div>
                </div>
            `;
            editor.chain().focus().insertContent(html).run();
        }

        function insertFaqAccordion(question, answer) {
            const html = `
                <details class="hoa-faq" open>
                    <summary><span>${question}</span> <span class="hoa-faq-toggle">▼</span></summary>
                    <p>${answer}</p>
                </details>
            `;
            editor.chain().focus().insertContent(html).run();
        }

        function insertTrustBox() {
            const html = `
                <div class="eeat-trust-card">
                    <div class="eeat-header">
                        <div class="eeat-badge-wrap">
                            <span class="eeat-icon">🏆</span>
                            <div>
                                <div class="eeat-title">Editorial Testing & Trust Standards</div>
                                <div class="eeat-sub">Independent Benchmark & Real-World Evaluation</div>
                            </div>
                        </div>
                        <span class="eeat-pill">Verified 2026</span>
                    </div>
                    <p class="eeat-desc">Our technical review team conducted 40+ hours of hands-on testing across enterprise workloads, measuring inference throughput, transaction resilience, and real-world latency.</p>
                    <div class="eeat-metrics">
                        <div class="eeat-metric"><div class="eeat-num">99.8%</div><div class="eeat-lbl">Reliability</div></div>
                        <div class="eeat-metric"><div class="eeat-num">12ms</div><div class="eeat-lbl">Latency</div></div>
                        <div class="eeat-metric"><div class="eeat-num">4.9/5</div><div class="eeat-lbl">Trust Score</div></div>
                    </div>
                </div>
            `;
            editor.chain().focus().insertContent(html).run();
        }

        function insertStepTimeline() {
            const html = `
                <div class="step-walkthrough">
                    <div class="step-item">
                        <div class="step-badge">1</div>
                        <h4>Step 1: Configuration & Setup</h4>
                        <p>Initialize workspace parameters and configure your target keywords and objectives.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-badge">2</div>
                        <h4>Step 2: AI Execution & Drafting</h4>
                        <p>Trigger contextual generation with multi-stage reasoning to draft exhaustive, structured content.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-badge">3</div>
                        <h4>Step 3: Verification & Publish</h4>
                        <p>Run the 10-point E-E-A-T audit, check SEO scores, and publish directly to WordPress.</p>
                    </div>
                </div>
            `;
            editor.chain().focus().insertContent(html).run();
        }

        // ==========================================
        // WORDPRESS MEDIA LIBRARY MODAL (wp.media)
        // ==========================================
        function openWpMediaModal() {
            if (typeof wp !== 'undefined' && wp.media) {
                const mediaUploader = wp.media({
                    title: 'Select or Upload Image for HOA TipTap',
                    button: { text: 'Insert Image' },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    if (attachment && attachment.url) {
                        editor.chain().focus().setImage({
                            src: attachment.url,
                            alt: attachment.alt || attachment.title || 'Image',
                        }).run();
                    }
                });

                mediaUploader.open();
            } else {
                const url = prompt('Enter Image URL:');
                if (url) {
                    editor.chain().focus().setImage({ src: url }).run();
                }
            }
        }

        // ==========================================
        // DISTRACTION-FREE FULLSCREEN MODE
        // ==========================================
        $('#hoa-wp-btn-fullscreen').on('click', function(e) {
            e.preventDefault();
            $wrapper.toggleClass('hoa-fullscreen-mode');
            $('body').toggleClass('hoa-no-scroll');

            const isFull = $wrapper.hasClass('hoa-fullscreen-mode');
            $(this).html(isFull ? '<span>✕ Exit Fullscreen</span>' : '<span>⛶ Fullscreen</span>');
            editor.commands.focus();
        });

        // Collapsible Formatting Ribbon
        $('#hoa-wp-btn-toggle-ribbon').on('click', function(e) {
            e.preventDefault();
            $('.hoa-wp-toolbar-inner').slideToggle(150);
            $(this).find('.hoa-chevron').toggleClass('rotate-180');
        });

        // ==========================================
        // IN-CANVAS AI COMMAND BAR & SSE STREAMING
        // ==========================================
        $('#hoa-wp-btn-ask-ai').on('click', function(e) {
            e.preventDefault();
            $aiBar.slideToggle(150, function() {
                if ($aiBar.is(':visible')) {
                    $('#hoa-wp-ai-prompt-input').focus();
                }
            });
        });

        $('#hoa-wp-ai-close-btn').on('click', function() {
            $aiBar.slideUp(150);
        });

        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('#hoa-wp-btn-ask-ai').trigger('click');
            }
        });

        // Quick AI Prompt Shortcuts
        $('.hoa-ai-chip').on('click', function(e) {
            e.preventDefault();
            const prompt = $(this).data('prompt');
            const type = $(this).data('type') || 'generate';
            $('#hoa-wp-ai-prompt-input').val(prompt);
            $('#hoa-wp-ai-type-select').val(type);
            $('#hoa-wp-ai-submit-btn').trigger('click');
        });

        // Stream Stop / Cancel button
        $('#hoa-wp-stop-stream-btn').on('click', function(e) {
            e.preventDefault();
            if (activeStreamReader) {
                try {
                    activeStreamReader.cancel();
                } catch(err) {}
                activeStreamReader = null;
            }
            $streamingStatus.hide();
        });

        $('#hoa-wp-ai-submit-btn').on('click', function() {
            const prompt = $('#hoa-wp-ai-prompt-input').val();
            const type = $('#hoa-wp-ai-type-select').val();
            const placement = $('input[name="hoa_wp_ai_placement"]:checked').val() || 'insert_below';

            const { state } = editor;
            const { from, to } = state.selection;
            const selectedText = from !== to ? state.doc.textBetween(from, to, ' ') : '';

            triggerAiStream({
                prompt: prompt,
                type: type,
                selectedText: selectedText,
                placement: placement,
                fromPos: from,
                toPos: to,
            });
        });

        function updateSwarmSteps(progressRatio) {
            const $steps = $('.hoa-swarm-step');
            $steps.removeClass('current');
            // 5 steps: 0: Research, 1: Outline, 2: Draft, 3: Media, 4: Meta
            let activeIdx = 0;
            if (progressRatio >= 0.85) activeIdx = 4;
            else if (progressRatio >= 0.65) activeIdx = 3;
            else if (progressRatio >= 0.35) activeIdx = 2;
            else if (progressRatio >= 0.15) activeIdx = 1;
            else activeIdx = 0;

            $steps.each(function(idx) {
                if (idx <= activeIdx) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
                if (idx === activeIdx) {
                    $(this).addClass('current');
                }
            });
        }

        async function triggerAiStream({ prompt, type, selectedText = '', placement = 'insert_below', fromPos = 0, toPos = 0, model = null }) {
            if (!prompt && !selectedText) {
                alert('Please type an instruction or select text in the editor.');
                return;
            }

            const cfg = window.hoaStudioConfig || {};
            const ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
            const nonce = cfg.nonce || '';

            const chosenModel = model || $('#hoa-ai-model-select').val() || 'auto';
            const postTitle = $('#hoa-post-title-input').val() ? $('#hoa-post-title-input').val().trim() : '';
            const targetKeyword = $('#hoa-target-keyword').val() ? $('#hoa-target-keyword').val().trim() : '';

            $streamingStatus.show();
            $('#hoa-wp-ai-speed-badge').text('0 tok/s');
            $('#hoa-dedicated-speed-badge').text('0 tok/s');
            $('#hoa-tok-received').text('0');

            const $runBtn = $('#hoa-dedicated-ai-run-btn');
            const origBtnText = $runBtn.html();
            $runBtn.prop('disabled', true).html('⚡ Streaming Tokens...');

            updateSwarmSteps(0.05);

            const formData = new URLSearchParams();
            formData.append('action', 'hoa_studio_stream_proxy');
            formData.append('nonce', nonce);
            formData.append('text', selectedText || prompt);
            formData.append('type', type);
            formData.append('model', chosenModel);
            formData.append('custom_instruction', prompt);
            formData.append('context[document_title]', postTitle);
            formData.append('context[target_keyword]', targetKeyword);

            if (placement === 'proposal') {
                lastProposalRange = (fromPos !== toPos) ? { from: fromPos, to: toPos } : null;
                $('#hoa-wp-ai-proposal-box').fadeIn(150);
                $('#hoa-proposal-body').html('<span class="text-emerald-400">✦ Sub-content-sub-agent reasoning & drafting...</span>');
            }

            let totalChars = 0;
            let totalTokens = 0;
            let firstTokenTime = null;
            const startTime = performance.now();
            let accumulatedBuffer = '';
            let streamHadError = false;
            let lastCanvasUpdate = 0;
            const existingTextAtStart = editor.getText().trim();
            const isDocInitiallyEmpty = !existingTextAtStart;
            let streamRemainder = '';
            let rawServerOutput = '';
            let isEventError = false;

            try {
                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                });

                if (!response.ok) {
                    const errText = await response.text();
                    let errMsg = 'HTTP server error ' + response.status;
                    try {
                        const parsedErr = JSON.parse(errText);
                        if (parsedErr.error) errMsg = parsedErr.error;
                        else if (parsedErr.message) errMsg = parsedErr.message;
                        else if (parsedErr.data && parsedErr.data.message) errMsg = parsedErr.data.message;
                    } catch(e) {}
                    throw new Error(errMsg);
                }

                if (!response.body) throw new Error('ReadableStream is not supported by your browser.');
                
                const reader = response.body.getReader();
                activeStreamReader = reader;
                const decoder = new TextDecoder();

                const processSseLine = (line) => {
                    const trimmed = line.trim();
                    if (!trimmed) return;
                    rawServerOutput += trimmed + ' ';

                    if (trimmed.startsWith('event: error')) {
                        isEventError = true;
                        return;
                    }

                    if (trimmed.startsWith('data: ')) {
                        const data = trimmed.slice(6).trim();
                        if (!data || data === '[DONE]') return;
                        
                        try {
                            const parsed = JSON.parse(data);

                            if (isEventError || parsed.error || (parsed.message && !parsed.delta && !parsed.chunk && !parsed.token && !parsed.words)) {
                                const errMsg = parsed.error || parsed.message || 'AI Gateway execution failed';
                                alert('AI Gateway Error: ' + errMsg);
                                streamHadError = true;
                                return;
                            }

                            const tokenDelta = parsed.delta || parsed.chunk || parsed.token || '';
                            
                            if (tokenDelta) {
                                totalChars += tokenDelta.length;
                                totalTokens += Math.max(1, Math.ceil(tokenDelta.length / 4));
                                accumulatedBuffer += tokenDelta;

                                if (placement === 'proposal') {
                                    lastProposalText = accumulatedBuffer;
                                    $('#hoa-proposal-body').html(accumulatedBuffer.replace(/\n/g, '<br>') + '<span class="hoa-streaming-cursor">|</span>');
                                } else if (isDocInitiallyEmpty && placement !== 'replace') {
                                    // Throttled real-time streaming preview directly in canvas
                                    const now = performance.now();
                                    if (now - lastCanvasUpdate > 100) {
                                        lastCanvasUpdate = now;
                                        editor.commands.setContent(normalizeContentToHtml(accumulatedBuffer), false);
                                    }
                                }

                                // Real-time telemetry counters
                                $('#hoa-tok-received').text(totalTokens.toLocaleString());

                                const elapsedSec = (performance.now() - startTime) / 1000;
                                if (elapsedSec > 0.3) {
                                    const tokSec = Math.round(totalTokens / elapsedSec);
                                    $('#hoa-wp-ai-speed-badge').text(`${tokSec} tok/s`);
                                    $('#hoa-dedicated-speed-badge').text(`${tokSec} tok/s`);
                                    $('#hoa-proposal-speed').text(`${tokSec} tok/s`);
                                }

                                const progressEst = Math.min(0.95, totalTokens / 500);
                                updateSwarmSteps(progressEst);
                            }

                            if (parsed.done) {
                                return;
                            }
                        } catch (e) {
                            // Incomplete chunk, skip
                        }
                    } else if (trimmed.startsWith('{')) {
                        try {
                            const parsed = JSON.parse(trimmed);
                            if (parsed.error || parsed.message) {
                                alert('AI Gateway Error: ' + (parsed.error || parsed.message));
                                streamHadError = true;
                            }
                        } catch (e) {}
                    }
                };

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) {
                        if (streamRemainder.trim()) {
                            processSseLine(streamRemainder.trim());
                        }
                        break;
                    }

                    if (!firstTokenTime) {
                        firstTokenTime = performance.now();
                        const latency = Math.round(firstTokenTime - startTime);
                        $('#hoa-tok-latency').text(latency);
                    }
                    
                    const chunk = decoder.decode(value, { stream: true });
                    const fullChunk = streamRemainder + chunk;
                    const lines = fullChunk.split('\n');
                    streamRemainder = lines.pop() || '';
                    
                    for (const line of lines) {
                        processSseLine(line);
                        if (streamHadError) break;
                    }

                    if (streamHadError) break;
                }

                // STREAMING COMPLETE: Normalize Markdown/HTML to rich TipTap ProseMirror Nodes
                if (accumulatedBuffer.trim() && !streamHadError) {
                    const normalizedHtml = normalizeContentToHtml(accumulatedBuffer);

                    if (placement === 'proposal') {
                        lastProposalText = normalizedHtml;
                        $('#hoa-proposal-body').html(normalizedHtml);
                    } else if (placement === 'replace' && fromPos !== toPos) {
                        try {
                            editor.chain().focus().setTextSelection({ from: fromPos, to: toPos }).deleteSelection().insertContent(normalizedHtml).run();
                        } catch (e) {
                            editor.chain().focus().insertContent(normalizedHtml).run();
                        }
                    } else {
                        const existingText = editor.getText().trim();
                        if (!existingText || isDocInitiallyEmpty) {
                            editor.commands.setContent(normalizedHtml, false);
                        } else {
                            editor.chain().focus('end').insertContent('<p></p>' + normalizedHtml).run();
                        }
                    }

                    // Keep hidden input & stats updated
                    $('#hoa_tiptap_html_content').val(editor.getHTML());
                    updateStats(editor.getText());
                } else if (!streamHadError) {
                    const snippet = rawServerOutput.trim().slice(0, 300);
                    let alertMsg = 'No content was generated by the AI model.\n\n';
                    if (snippet) {
                        alertMsg += 'Diagnostic Telemetry received from server:\n' + snippet + '\n\n';
                    }
                    alertMsg += 'Please verify that:\n1. Your HOA Studio Node is running and reachable from this WordPress site.\n2. Your Connect Key is active in HOA Studio Settings.\n3. Your AI model has remaining word quota or a valid API key configured.';
                    alert(alertMsg);
                }

            } catch (err) {
                console.error('[HOA Studio AI] Streaming error:', err);
                alert('AI Streaming Error: ' + (err.message || 'Failed to connect to AI Gateway.'));
            } finally {
                activeStreamReader = null;
                $streamingStatus.hide();
                $aiBar.slideUp();
                $('#hoa-wp-ai-prompt-input').val('');
                $runBtn.prop('disabled', false).html(origBtnText);
                $('.hoa-swarm-step').addClass('active').removeClass('current');

                try { updateDynamicOutline(); } catch(e) {}
                try { updateSeoScore(); } catch(e) {}
                try { updateEeatQualityScore(); } catch(e) {}
                try { updateKeywordDensityMatrix(); } catch(e) {}
            }
        }

        window.triggerAiGeneration = function(prompt, type = 'generate', placement = 'insert_below', model = null) {
            const { state } = editor;
            const { from, to } = state.selection;
            const selectedText = from !== to ? state.doc.textBetween(from, to, ' ') : '';
            return triggerAiStream({
                prompt: prompt,
                type: type,
                selectedText: selectedText,
                placement: placement,
                fromPos: from,
                toPos: to,
                model: model || $('#hoa-ai-model-select').val()
            });
        };

        /* ==================================================================
           DEDICATED HOA STUDIO POST EDITOR SUITE
           ================================================================== */
        const $dedicatedApp = $('#hoa-studio-dedicated-app');
        if ($dedicatedApp.length) {
            // 1. Post Title auto-sync with hidden/state
            $('#hoa-post-title-input').on('input', function() {
                updateSeoScore();
            });

            // 2. Publish & Save Draft Actions
            $('#hoa-btn-publish').on('click', function(e) {
                e.preventDefault();
                saveDedicatedPost('publish');
            });

            $('#hoa-btn-save-draft').on('click', function(e) {
                e.preventDefault();
                saveDedicatedPost('draft');
            });

            // Keyboard shortcut Ctrl+S
            $(window).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveDedicatedPost();
                }
            });

            function saveDedicatedPost(customStatus = null) {
                const $saveStatus = $('#hoa-save-status-text');
                const $statusDot = $('#hoa-save-status .hoa-status-dot');
                $saveStatus.text('Saving to WordPress...');
                $statusDot.removeClass('saved error').addClass('saving');

                const urlParams = new URLSearchParams(window.location.search);
                const postId = urlParams.get('post_id') || 0;
                const title = $('#hoa-post-title-input').val().trim() || 'Untitled Post';
                const content = editor.getHTML();
                const status = customStatus || $('#hoa-post-status-select').val();
                const slug = $('#hoa-post-slug').val().trim();
                const targetKeyword = $('#hoa-target-keyword').val().trim();
                const metaDesc = $('#hoa-meta-description').val().trim();
                const featuredImageId = $('#hoa-featured-image-id').val();
                const categories = [];
                $('input[name="hoa_categories[]"]:checked').each(function() {
                    categories.push($(this).val());
                });
                const tags = $('#hoa-post-tags').val().trim();

                $.ajax({
                    url: hoaStudioConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'hoa_studio_save_full_post',
                        nonce: hoaStudioConfig.nonce,
                        post_id: postId,
                        title: title,
                        content: content,
                        status: status,
                        slug: slug,
                        target_keyword: targetKeyword,
                        meta_description: metaDesc,
                        featured_image_id: featuredImageId,
                        categories: categories,
                        tags: tags
                    },
                    success: function(res) {
                        if (res.success) {
                            $saveStatus.text('Saved at ' + res.data.saved_at);
                            $statusDot.removeClass('saving error').addClass('saved');
                            $('#hoa-post-status-select').val(res.data.status);
                            if (res.data.permalink) {
                                $('#hoa-view-post-link').attr('href', res.data.permalink).removeClass('disabled');
                            }
                            if (!postId && res.data.edit_url) {
                                window.history.replaceState(null, '', res.data.edit_url);
                            }
                        } else {
                            $saveStatus.text('Save failed: ' + (res.data ? res.data.message : 'Unknown error'));
                            $statusDot.removeClass('saving saved').addClass('error');
                        }
                    },
                    error: function() {
                        $saveStatus.text('Network error during save');
                        $statusDot.removeClass('saving saved').addClass('error');
                    }
                });
            }

            // 3. Featured Image Selection via WordPress Media Modal
            let featuredMediaFrame = null;
            $('#hoa-btn-set-featured-image, #hoa-btn-change-image').on('click', function(e) {
                e.preventDefault();
                if (featuredMediaFrame) {
                    featuredMediaFrame.open();
                    return;
                }
                featuredMediaFrame = wp.media({
                    title: 'Select Featured Image for Post',
                    button: { text: 'Set as Featured Image' },
                    multiple: false
                });
                featuredMediaFrame.on('select', function() {
                    const attachment = featuredMediaFrame.state().get('selection').first().toJSON();
                    $('#hoa-featured-image-id').val(attachment.id);
                    $('#hoa-featured-img-preview').attr('src', attachment.url).show();
                    $('#hoa-no-image-ph').hide();
                    $('#hoa-featured-image-box .hoa-img-actions').show();
                    saveDedicatedPost();
                });
                featuredMediaFrame.open();
            });

            $('#hoa-btn-remove-image').on('click', function(e) {
                e.preventDefault();
                $('#hoa-featured-image-id').val('');
                $('#hoa-featured-img-preview').attr('src', '').hide();
                $('#hoa-featured-image-box .hoa-img-actions').hide();
                $('#hoa-no-image-ph').show();
                saveDedicatedPost();
            });

            // 4. Panel View Toggles
            $('#hoa-toggle-ai-panel').on('click', function() {
                $(this).toggleClass('active');
                $('#hoa-ai-panel').toggleClass('hoa-panel-collapsed');
                $dedicatedApp.toggleClass('hoa-ai-hidden', !$(this).hasClass('active'));
            });

            $('#hoa-toggle-intel-panel').on('click', function() {
                $(this).toggleClass('active');
                $('#hoa-intel-panel').toggleClass('hoa-panel-collapsed');
                $dedicatedApp.toggleClass('hoa-intel-hidden', !$(this).hasClass('active'));
            });

            $('#hoa-toggle-zen-mode').on('click', function() {
                $(this).toggleClass('active');
                const isZen = $(this).hasClass('active');
                $dedicatedApp.toggleClass('hoa-zen-active', isZen);
                if (isZen) {
                    $('#hoa-toggle-ai-panel, #hoa-toggle-intel-panel').removeClass('active');
                    $('#hoa-ai-panel, #hoa-intel-panel').addClass('hoa-panel-collapsed');
                } else {
                    $('#hoa-toggle-ai-panel, #hoa-toggle-intel-panel').addClass('active');
                    $('#hoa-ai-panel, #hoa-intel-panel').removeClass('hoa-panel-collapsed');
                }
            });

            // 5. Intelligence Panel Tab Switching
            $('.hoa-intel-tab').on('click', function() {
                $('.hoa-intel-tab').removeClass('active');
                $(this).addClass('active');
                const tab = $(this).data('tab');
                $('.hoa-tab-content').hide();
                $('#hoa-tab-' + tab).show();
            });

            // 6. Dynamic Outline TOC
            updateDynamicOutline = function() {
                const $outline = $('#hoa-dynamic-outline-list');
                if (!$outline.length) return;

                const headings = [];
                editor.state.doc.descendants((node, pos) => {
                    if (node.type.name === 'heading') {
                        headings.push({
                            level: node.attrs.level,
                            text: node.textContent,
                            pos: pos
                        });
                    }
                });

                if (headings.length === 0) {
                    $outline.html('<span class="hoa-empty-note">No headings yet. Use H1-H4 in editor.</span>');
                    return;
                }

                let html = '';
                headings.forEach(h => {
                    const indent = (h.level - 1) * 12;
                    html += `
                        <div class="hoa-outline-item" data-pos="${h.pos}" style="padding-left:${indent}px">
                            <span class="hoa-h-tag">H${h.level}</span>
                            <span class="hoa-h-text">${$('<div>').text(h.text || 'Untitled Section').html()}</span>
                        </div>
                    `;
                });

                $outline.html(html);
            }

            // Click heading in TOC to navigate in canvas
            $(document).on('click', '.hoa-outline-item', function() {
                const pos = parseInt($(this).data('pos'), 10);
                if (!isNaN(pos)) {
                    editor.chain().focus().setTextSelection(pos).scrollIntoView().run();
                }
            });

            // 7. Reusable AI Prompt Runner (Stream-to-Text)
            async function callAiSimplePrompt(prompt, type = 'custom') {
                const cfg = window.hoaStudioConfig || {};
                const ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
                const nonce = cfg.nonce || '';
                const chosenModel = $('#hoa-ai-model-select').val() || 'auto';

                const formData = new URLSearchParams();
                formData.append('action', 'hoa_studio_stream_proxy');
                formData.append('nonce', nonce);
                formData.append('text', prompt);
                formData.append('type', type);
                formData.append('model', chosenModel);
                formData.append('custom_instruction', prompt);

                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                });

                if (!response.ok) {
                    throw new Error('HTTP server error ' + response.status);
                }

                if (!response.body) {
                    throw new Error('ReadableStream is not supported by your browser.');
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let accumulated = '';
                let streamRemainder = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) {
                        if (streamRemainder.trim() && streamRemainder.trim().startsWith('data: ')) {
                            try {
                                const parsed = JSON.parse(streamRemainder.trim().slice(6).trim());
                                const tokenDelta = parsed.delta || parsed.chunk || parsed.token || '';
                                if (tokenDelta) accumulated += tokenDelta;
                            } catch (e) {}
                        }
                        break;
                    }
                    const chunk = decoder.decode(value, { stream: true });
                    const fullChunk = streamRemainder + chunk;
                    const lines = fullChunk.split('\n');
                    streamRemainder = lines.pop() || '';

                    for (const line of lines) {
                        const trimmed = line.trim();
                        if (!trimmed || !trimmed.startsWith('data: ')) continue;
                        const data = trimmed.slice(6).trim();
                        if (!data || data === '[DONE]') continue;
                        try {
                            const parsed = JSON.parse(data);
                            if (parsed.error || (parsed.message && !parsed.delta && !parsed.token && !parsed.chunk)) {
                                throw new Error(parsed.error || parsed.message);
                            }
                            const tokenDelta = parsed.delta || parsed.chunk || parsed.token || '';
                            if (tokenDelta) accumulated += tokenDelta;
                            if (parsed.done) break;
                        } catch (e) {
                            if (e.message && !e.message.includes('JSON')) throw e;
                        }
                    }
                }
                return accumulated.trim();
            }

            // 8. Live SEO Score Audit Calculation & Density
            updateSeoScore = function() {
                const keyword = ($('#hoa-target-keyword').val() || '').trim().toLowerCase();
                const title = ($('#hoa-post-title-input').val() || '').trim().toLowerCase();
                const plainText = editor.getText().toLowerCase();
                const wordCount = editor.storage.characterCount.words();

                let score = 0;

                // Check 1: Focus keyword in title (25 pts)
                const inTitle = keyword && title.includes(keyword);
                $('#hoa-check-kw-title').toggleClass('passed', !!inTitle);
                if (inTitle) score += 25;

                // Check 2: Focus keyword in first 10% of content (20 pts)
                const firstPart = plainText.slice(0, Math.max(200, Math.floor(plainText.length * 0.1)));
                const inFirst = keyword && firstPart.includes(keyword);
                $('#hoa-check-kw-first').toggleClass('passed', !!inFirst);
                if (inFirst) score += 20;

                // Check 3: Word count > 600 (20 pts)
                const wordPass = wordCount >= 600;
                $('#hoa-check-words').toggleClass('passed', wordPass);
                if (wordPass) score += 20;
                else if (wordCount >= 300) score += 10;

                // Check 4: Headings presence (15 pts)
                let hasHeadings = false;
                editor.state.doc.descendants(node => {
                    if (node.type.name === 'heading') hasHeadings = true;
                });
                $('#hoa-check-headings').toggleClass('passed', hasHeadings);
                if (hasHeadings) score += 15;

                // Check 5: Rich blocks (Table or Callout) (20 pts)
                const html = editor.getHTML();
                const hasRich = html.includes('table') || html.includes('callout-box') || html.includes('pros-cons-grid');
                $('#hoa-check-table').toggleClass('passed', hasRich);
                if (hasRich) score += 20;

                // Focus keyword density calculation
                if (keyword && wordCount > 0) {
                    const escaped = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const matches = plainText.match(new RegExp('\\b' + escaped + '\\b', 'gi')) || [];
                    const kwCount = matches.length;
                    const kwWords = keyword.split(/\s+/).length;
                    const density = ((kwCount * kwWords) / wordCount * 100).toFixed(1);
                    const $densityTag = $('#hoa-seo-density-tag');
                    $densityTag.text(`Density: ${density}% (${kwCount}x)`);
                    if (density >= 0.5 && density <= 2.5) {
                        $densityTag.css({ background: 'rgba(16, 185, 129, 0.2)', color: '#34d399', borderColor: '#10b981' });
                    } else if (density > 2.5) {
                        $densityTag.css({ background: 'rgba(245, 158, 11, 0.2)', color: '#fbbf24', borderColor: '#f59e0b' });
                    } else {
                        $densityTag.css({ background: 'rgba(255, 255, 255, 0.05)', color: '#94a3b8', borderColor: 'rgba(255, 255, 255, 0.1)' });
                    }
                } else {
                    $('#hoa-seo-density-tag').text('Density: 0%').css({ background: 'rgba(255, 255, 255, 0.05)', color: '#94a3b8', borderColor: 'rgba(255, 255, 255, 0.1)' });
                }

                // Update badge
                const $badge = $('#hoa-seo-score-badge');
                $badge.text(`${score}/100`);
                if (score >= 80) {
                    $badge.css({ background: 'rgba(16, 185, 129, 0.2)', color: '#34d399', borderColor: '#10b981' });
                } else if (score >= 50) {
                    $badge.css({ background: 'rgba(245, 158, 11, 0.2)', color: '#fbbf24', borderColor: '#f59e0b' });
                } else {
                    $badge.css({ background: 'rgba(244, 63, 94, 0.2)', color: '#fb7185', borderColor: '#f43f5e' });
                }
            }

            $('#hoa-target-keyword').on('input', updateSeoScore);

            // 9. TAB 3: Viral Titles & Meta Description Generators
            $('#hoa-btn-gen-titles').on('click', async function() {
                const $btn = $(this);
                const origText = $btn.html();
                const title = $('#hoa-post-title-input').val().trim();
                const kw = $('#hoa-target-keyword').val().trim();
                const $list = $('#hoa-viral-titles-list');

                $btn.prop('disabled', true).html('⚡ Generating...');
                $list.html('<div class="hoa-loading-pulse">✦ AI is crafting 5 click-worthy SEO headlines...</div>');

                try {
                    const prompt = `Generate 5 click-worthy, viral, SEO-optimized headlines for an article with focus keyword "${kw || title}" and current working title "${title || kw}". Return ONLY the 5 numbered titles, one per line. No conversational introduction.`;
                    const res = await callAiSimplePrompt(prompt, 'seo_fix_title');
                    const lines = res.split('\n').map(l => l.replace(/^\d+[\.\)]\s*/, '').replace(/^["']|["']$/g, '').trim()).filter(Boolean);
                    if (lines.length) {
                        let html = '';
                        lines.slice(0, 5).forEach(t => {
                            const cleanTitle = $('<div>').text(t).html();
                            html += `
                                <div class="hoa-suggestion-card hoa-title-suggestion">
                                    <div class="hoa-suggestion-text">${cleanTitle}</div>
                                    <button type="button" class="hoa-btn-apply-title button button-small">Apply</button>
                                </div>
                            `;
                        });
                        $list.html(html);
                    } else {
                        $list.html('<span class="hoa-empty-note">Could not generate titles. Please verify connection.</span>');
                    }
                } catch (e) {
                    $list.html(`<span class="hoa-empty-note text-danger">Error: ${e.message}</span>`);
                } finally {
                    $btn.prop('disabled', false).html(origText);
                }
            });

            $(document).on('click', '.hoa-btn-apply-title, .hoa-title-suggestion', function(e) {
                const titleText = $(this).hasClass('hoa-btn-apply-title') 
                    ? $(this).siblings('.hoa-suggestion-text').text()
                    : $(this).find('.hoa-suggestion-text').text();

                if (titleText) {
                    $('#hoa-post-title-input').val(titleText);
                    updateSeoScore();
                    const $btn = $(this).hasClass('hoa-btn-apply-title') ? $(this) : $(this).find('.hoa-btn-apply-title');
                    $btn.text('✓ Applied!').css({ background: 'rgba(16, 185, 129, 0.3)', color: '#34d399' });
                    setTimeout(() => $btn.text('Apply').css({ background: '', color: '' }), 2500);
                }
            });

            $('#hoa-btn-gen-desc').on('click', async function() {
                const $btn = $(this);
                const origText = $btn.html();
                const title = $('#hoa-post-title-input').val().trim();
                const kw = $('#hoa-target-keyword').val().trim();
                const text = editor.getText().slice(0, 800);

                $btn.prop('disabled', true).html('⚡ Generating...');
                $('#hoa-generated-desc-box').hide();

                try {
                    const prompt = `Write a compelling, click-optimized 150-160 character SEO meta description for this article. Focus keyword: "${kw}". Title: "${title}". Content snippet: "${text}". Output ONLY the single meta description text with no quotes or preamble.`;
                    const desc = await callAiSimplePrompt(prompt, 'seo_fix_meta');
                    if (desc) {
                        $('#hoa-generated-desc-text').text(desc.replace(/^["']|["']$/g, '').trim());
                        $('#hoa-generated-desc-box').fadeIn(150);
                    }
                } catch (e) {
                    alert('Error generating meta description: ' + e.message);
                } finally {
                    $btn.prop('disabled', false).html(origText);
                }
            });

            $('#hoa-btn-apply-desc').on('click', function() {
                const text = $('#hoa-generated-desc-text').text().trim();
                if (text) {
                    $('#hoa-meta-description').val(text);
                    updateSeoScore();
                    $(this).text('✓ Applied!').css({ background: 'rgba(16, 185, 129, 0.3)', color: '#34d399' });
                    setTimeout(() => $(this).text('Apply to SEO Meta').css({ background: '', color: '' }), 2500);
                }
            });

            // 10. TAB 4: Semantic Content Gaps & FAQs
            $('#hoa-btn-find-gaps').on('click', async function() {
                const $btn = $(this);
                const origText = $btn.html();
                const title = $('#hoa-post-title-input').val().trim();
                const kw = $('#hoa-target-keyword').val().trim();
                const $list = $('#hoa-content-gaps-list');

                $btn.prop('disabled', true).html('⚡ Analyzing Gaps...');
                $list.html('<div class="hoa-loading-pulse">✦ Evaluating search intent and competitive gaps...</div>');

                try {
                    const prompt = `Analyze this article (Title: "${title}", Focus Keyword: "${kw}"). Identify 2 missing competitive subtopics (gaps) and 2 schema FAQ questions that would satisfy search intent. Format each line starting with "[GAP] Subtopic Name: Explanation" or "[FAQ] Question?: Concise answer". No extra conversational text.`;
                    const res = await callAiSimplePrompt(prompt, 'content_gaps');
                    const lines = res.split('\n').map(l => l.trim()).filter(Boolean);
                    if (lines.length) {
                        let html = '';
                        lines.forEach(line => {
                            if (line.startsWith('[GAP]')) {
                                const content = line.replace(/^\[GAP\]\s*/, '');
                                const parts = content.split(':');
                                const gapTitle = parts[0].trim();
                                const gapDesc = parts.slice(1).join(':').trim();
                                html += `
                                    <div class="hoa-suggestion-card">
                                        <div class="hoa-suggestion-title">💡 ${$('<div>').text(gapTitle).html()}</div>
                                        <p class="hoa-suggestion-desc">${$('<div>').text(gapDesc).html()}</p>
                                        <button type="button" class="hoa-btn-insert-gap button button-small" data-type="heading" data-title="${$('<div>').text(gapTitle).html()}" data-desc="${$('<div>').text(gapDesc).html()}">+ Insert Heading & Section</button>
                                    </div>
                                `;
                            } else if (line.startsWith('[FAQ]')) {
                                const content = line.replace(/^\[FAQ\]\s*/, '');
                                const parts = content.split('?');
                                const q = (parts[0] + '?').trim();
                                const a = parts.slice(1).join('?').replace(/^:\s*/, '').trim();
                                html += `
                                    <div class="hoa-suggestion-card">
                                        <div class="hoa-suggestion-title">❓ ${$('<div>').text(q).html()}</div>
                                        <p class="hoa-suggestion-desc">${$('<div>').text(a).html()}</p>
                                        <button type="button" class="hoa-btn-insert-gap button button-small" data-type="faq" data-question="${$('<div>').text(q).html()}" data-answer="${$('<div>').text(a).html()}">+ Insert FAQ Accordion</button>
                                    </div>
                                `;
                            }
                        });
                        $list.html(html || '<span class="hoa-empty-note">No clear gaps identified. Content is comprehensive!</span>');
                    } else {
                        $list.html('<span class="hoa-empty-note">Could not identify gaps.</span>');
                    }
                } catch (e) {
                    $list.html(`<span class="hoa-empty-note text-danger">Error: ${e.message}</span>`);
                } finally {
                    $btn.prop('disabled', false).html(origText);
                }
            });

            $(document).on('click', '.hoa-btn-insert-gap', function() {
                const type = $(this).data('type');
                if (type === 'faq') {
                    const q = $(this).data('question');
                    const a = $(this).data('answer');
                    insertFaqAccordion(q, a);
                } else {
                    const title = $(this).data('title');
                    const desc = $(this).data('desc');
                    editor.chain().focus('end').insertContent(`<h2>${title}</h2><p>${desc}</p>`).run();
                }
                $(this).text('✓ Inserted').prop('disabled', true);
            });

            // 11. TAB 5: Secondary & LSI Keywords Density Matrix
            updateKeywordDensityMatrix = function() {
                const raw = $('#hoa-secondary-keywords').val() || '';
                const keywords = raw.split(',').map(k => k.trim()).filter(Boolean);
                const $matrix = $('#hoa-kw-density-matrix');

                if (!keywords.length) {
                    $matrix.html('<span class="hoa-empty-note">Enter keywords above to monitor their exact density and distribution in the canvas.</span>');
                    return;
                }

                const plainText = editor.getText().toLowerCase();
                const totalWords = editor.storage.characterCount.words() || 1;

                let html = '';
                keywords.forEach(kw => {
                    const escaped = kw.toLowerCase().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const matches = plainText.match(new RegExp('\\b' + escaped + '\\b', 'gi')) || [];
                    const count = matches.length;
                    const kwWords = kw.split(/\s+/).length;
                    const density = ((count * kwWords) / totalWords * 100).toFixed(1);

                    let statusClass = 'neutral';
                    if (count > 0) {
                        if (density >= 0.5 && density <= 2.5) statusClass = 'optimal';
                        else if (density > 2.5) statusClass = 'high';
                        else statusClass = 'low';
                    }

                    html += `
                        <div class="hoa-density-chip ${statusClass}">
                            <span class="hoa-kw-name">${$('<div>').text(kw).html()}</span>
                            <span class="hoa-kw-count">${count}x (${density}%)</span>
                        </div>
                    `;
                });

                $matrix.html(html);
            }

            $('#hoa-secondary-keywords').on('input', updateKeywordDensityMatrix);

            // 12. TAB 6: 10-Point E-E-A-T Quality Audit Live Scorecard
            updateEeatQualityScore = function() {
                const html = editor.getHTML();
                const text = editor.getText().toLowerCase();
                const wordCount = editor.storage.characterCount.words();

                let passed = 0;

                // 1. First-hand Experience & Author Voice
                const expRegex = /\b(i tested|in our testing|we found|in my experience|our team|hands-on|we evaluated|we measured|in benchmark testing)\b/i;
                const hasExp = expRegex.test(text);
                $('#hoa-eeat-exp').toggleClass('passed', hasExp);
                if (hasExp) passed++;

                // 2. Structured Comparison Table
                const hasTable = html.includes('<table');
                $('#hoa-eeat-table').toggleClass('passed', hasTable);
                if (hasTable) passed++;

                // 3. Executive Summary / TL;DR Box
                const hasTldr = html.includes('callout-tldr') || html.includes('callout-box') || text.includes('quick answer:') || text.includes('summary:');
                $('#hoa-eeat-tldr').toggleClass('passed', hasTldr);
                if (hasTldr) passed++;

                // 4. Verified External Reference Links
                const hasLinks = /<a\s[^>]*href=/i.test(html);
                $('#hoa-eeat-links').toggleClass('passed', hasLinks);
                if (hasLinks) passed++;

                // 5. Actionable Pro-Tips & Warning Boxes
                const hasTips = html.includes('callout-tip') || html.includes('callout-warning') || html.includes('callout-caution');
                $('#hoa-eeat-tips').toggleClass('passed', hasTips);
                if (hasTips) passed++;

                // 6. Step-by-Step Implementation Timeline
                const hasTimeline = html.includes('step-walkthrough') || html.includes('step-item') || html.includes('<ol');
                $('#hoa-eeat-timeline').toggleClass('passed', hasTimeline);
                if (hasTimeline) passed++;

                // 7. Schema-Ready FAQ Accordion
                const hasFaq = html.includes('hoa-faq') || html.includes('<details');
                $('#hoa-eeat-faq').toggleClass('passed', hasFaq);
                if (hasFaq) passed++;

                // 8. Balanced Pros & Cons Comparison Grid
                const hasProsCons = html.includes('pros-cons-grid') || html.includes('pros-box');
                $('#hoa-eeat-proscons').toggleClass('passed', hasProsCons);
                if (hasProsCons) passed++;

                // 9. Skimmable Headings Hierarchy (H2, H3)
                const hasH2 = html.includes('<h2');
                const hasH3 = html.includes('<h3');
                const hasHeadings = hasH2 && hasH3;
                $('#hoa-eeat-headings').toggleClass('passed', hasHeadings);
                if (hasHeadings) passed++;

                // 10. Authoritative Depth (> 600 words)
                const hasLength = wordCount >= 600;
                $('#hoa-eeat-length').toggleClass('passed', hasLength);
                if (hasLength) passed++;

                // Update Score Badge
                const $badge = $('#hoa-eeat-score-badge');
                $badge.text(`${passed}/10 Passed`);
                if (passed >= 8) {
                    $badge.css({ background: 'rgba(16, 185, 129, 0.2)', color: '#34d399', borderColor: '#10b981' });
                } else if (passed >= 5) {
                    $badge.css({ background: 'rgba(245, 158, 11, 0.2)', color: '#fbbf24', borderColor: '#f59e0b' });
                } else {
                    $badge.css({ background: 'rgba(244, 63, 94, 0.2)', color: '#fb7185', borderColor: '#f43f5e' });
                }
            }

            // 13. TAB 8: Local Version Snapshots Timeline
            const currentPostId = (new URLSearchParams(window.location.search)).get('post_id') || '0';
            const snapshotsKey = 'hoa_studio_snapshots_' + currentPostId;
            let lastSavedSnapshotContent = '';

            function getLocalSnapshots() {
                try {
                    return JSON.parse(localStorage.getItem(snapshotsKey)) || [];
                } catch (e) {
                    return [];
                }
            }

            function saveSnapshot(isAuto = false) {
                const content = editor.getHTML();
                if (!content || content === '<p></p>' || content === lastSavedSnapshotContent) {
                    return;
                }
                lastSavedSnapshotContent = content;
                const snapshots = getLocalSnapshots();
                const now = new Date();
                const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const dateStr = now.toLocaleDateString([], { month: 'short', day: 'numeric' });
                const words = editor.storage.characterCount.words();

                snapshots.unshift({
                    id: Date.now(),
                    timeStr: `${dateStr} ${timeStr}`,
                    words: words,
                    content: content,
                    isAuto: isAuto
                });

                if (snapshots.length > 20) snapshots.length = 20;

                try {
                    localStorage.setItem(snapshotsKey, JSON.stringify(snapshots));
                } catch (e) {}

                renderSnapshots();
            }

            function renderSnapshots() {
                const $list = $('#hoa-snapshots-list');
                if (!$list.length) return;

                const snapshots = getLocalSnapshots();
                if (!snapshots.length) {
                    $list.html('<span class="hoa-empty-note">Snapshots are automatically recorded every 60 seconds while you write.</span>');
                    return;
                }

                let html = '';
                snapshots.forEach((snap, idx) => {
                    html += `
                        <div class="hoa-snapshot-item">
                            <div class="hoa-snapshot-meta">
                                <strong>${snap.timeStr} ${snap.isAuto ? '<small class="text-slate-500">(auto)</small>' : '<small class="text-indigo-400">(manual)</small>'}</strong>
                                <span>${snap.words.toLocaleString()} words</span>
                            </div>
                            <button type="button" class="hoa-btn-restore-snapshot button button-small" data-idx="${idx}">Restore</button>
                        </div>
                    `;
                });
                $list.html(html);
            }

            $('#hoa-btn-take-snapshot').on('click', function(e) {
                e.preventDefault();
                saveSnapshot(false);
                const $btn = $(this);
                $btn.text('✓ Saved!').css({ background: 'rgba(16, 185, 129, 0.3)', color: '#34d399' });
                setTimeout(() => $btn.text('📸 Save Snapshot').css({ background: '', color: '' }), 2000);
            });

            $(document).on('click', '.hoa-btn-restore-snapshot', function() {
                const idx = parseInt($(this).data('idx'), 10);
                const snapshots = getLocalSnapshots();
                const snap = snapshots[idx];
                if (snap && snap.content) {
                    if (confirm(`Restore snapshot from ${snap.timeStr} (${snap.words} words)? Your current unsaved changes will be replaced.`)) {
                        editor.commands.setContent(snap.content, false);
                        $('#hoa_tiptap_html_content').val(snap.content);
                        updateStats(editor.getText());
                        updateDynamicOutline();
                        updateSeoScore();
                        updateEeatQualityScore();
                        updateKeywordDensityMatrix();
                    }
                }
            });

            // Auto-snapshot every 60 seconds
            setInterval(() => {
                saveSnapshot(true);
            }, 60000);
            renderSnapshots();

            // 14. Local Draft Auto-Recovery Banner
            const autosaveKey = 'hoa_studio_autosave_' + currentPostId;

            function checkDraftAutoRecovery() {
                try {
                    const savedData = JSON.parse(localStorage.getItem(autosaveKey));
                    if (savedData && savedData.content && savedData.content !== '<p></p>') {
                        const serverContent = ($('#hoa_tiptap_html_content').val() || '').trim();
                        if (savedData.content.trim() !== serverContent && savedData.words > 0) {
                            const saveDate = new Date(savedData.timestamp);
                            const timeStr = saveDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            $('#hoa-recovery-text').text(`Recovered ${savedData.words.toLocaleString()} words auto-saved locally at ${timeStr}`);
                            $('#hoa-wp-draft-recovery-banner').slideDown(200);

                            $('#hoa-btn-keep-restored').off('click').on('click', function() {
                                editor.commands.setContent(savedData.content, false);
                                if (savedData.title && !$('#hoa-post-title-input').val()) {
                                    $('#hoa-post-title-input').val(savedData.title);
                                }
                                $('#hoa_tiptap_html_content').val(savedData.content);
                                $('#hoa-wp-draft-recovery-banner').slideUp(150);
                                updateStats(editor.getText());
                                updateDynamicOutline();
                                updateSeoScore();
                                updateEeatQualityScore();
                                saveDedicatedPost();
                            });

                            $('#hoa-btn-discard-restored').off('click').on('click', function() {
                                localStorage.removeItem(autosaveKey);
                                $('#hoa-wp-draft-recovery-banner').slideUp(150);
                            });
                        }
                    }
                } catch (e) {}
            }

            // Autosave to localStorage every 10 seconds
            setInterval(() => {
                if (!editor || editor.isDestroyed) return;
                const content = editor.getHTML();
                const title = $('#hoa-post-title-input').val() || '';
                const words = editor.storage.characterCount.words();
                if (content && content !== '<p></p>') {
                    try {
                        localStorage.setItem(autosaveKey, JSON.stringify({
                            content: content,
                            title: title,
                            words: words,
                            timestamp: Date.now()
                        }));
                    } catch (e) {}
                }
            }, 10000);

            checkDraftAutoRecovery();

            // 15. Zen Fullscreen Mode Shortcut (Ctrl+Shift+F)
            $(window).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'F' || e.key === 'f')) {
                    e.preventDefault();
                    $('#hoa-toggle-zen-mode').trigger('click');
                }
            });

            // 16. Code Block 1-Click Copy Interaction
            $(document).on('click', '.hoa-copy-code-btn', function() {
                const $btn = $(this);
                const codeText = $btn.closest('.hoa-code-block-wrapper').find('code').text() || $btn.siblings('pre').text();
                if (navigator.clipboard && codeText) {
                    navigator.clipboard.writeText(codeText).then(() => {
                        $btn.html('<span>✓</span> <span>Copied!</span>');
                        setTimeout(() => $btn.html('<span>📋</span> <span>Copy</span>'), 2000);
                    });
                }
            });

            // 17. Hook into editor transactions to update Outline, SEO, E-E-A-T, and Density
            editor.on('transaction', () => {
                updateDynamicOutline();
                updateSeoScore();
                updateEeatQualityScore();
                updateKeywordDensityMatrix();

                // Also update speaking time
                const words = editor.storage.characterCount.words();
                const speakingMinutes = Math.max(1, Math.ceil(words / 130));
                $('#hoa-wp-speaking-time').text(`${speakingMinutes}m`);
            });

            // 0. Fetch & Populate Live Models from HOA Studio Node
            function loadAvailableModels() {
                $.post(hoaStudioConfig.ajaxUrl, {
                    action: 'hoa_studio_test_connection',
                    nonce: hoaStudioConfig.nonce,
                    endpoint: hoaStudioConfig.endpoint,
                    key: 'check'
                }, function(res) {
                    if (res.success && res.data && res.data.available_models && res.data.available_models.length) {
                        const $select = $('#hoa-ai-model-select');
                        const currentVal = $select.val() || 'auto';
                        $select.empty();
                        $select.append('<option value="auto">⚡ Auto (OmniRoute Smart Router)</option>');
                        res.data.available_models.forEach(function(m) {
                            const providerName = m.provider || 'OmniRoute';
                            $select.append(`<option value="${m.model_id}">${m.name} (${providerName})</option>`);
                        });
                        if (currentVal && $select.find(`option[value="${currentVal}"]`).length) {
                            $select.val(currentVal);
                        }
                    }
                });
            }
            loadAvailableModels();

            // 9. Left Panel AI Drafting Trigger
            $('.hoa-preset-chip').on('click', function() {
                const task = $(this).data('task');
                const title = $('#hoa-post-title-input').val().trim();
                const kw = $('#hoa-target-keyword').val().trim();
                
                let prompt = '';
                if (task === 'generate_full') {
                    prompt = `Write a comprehensive, authoritative blog post about "${title || 'the topic'}" with in-depth technical analysis, key takeaways, and comparison tables. Focus keyword: ${kw}.`;
                } else if (task === 'outline') {
                    prompt = `Create a structured SEO-optimized article outline with H2 and H3 headings covering search intent for "${title || kw}".`;
                } else if (task === 'faq') {
                    prompt = `Generate 4 high-value FAQ schema questions and answers addressing user intent for "${title || kw}".`;
                } else if (task === 'table') {
                    prompt = `Create a detailed comparison table analyzing features, performance, and trade-offs related to "${title || kw}".`;
                }
                
                $('#hoa-dedicated-ai-prompt').val(prompt);
            });

            $('#hoa-dedicated-ai-run-btn').on('click', function(e) {
                e.preventDefault();
                const prompt = $('#hoa-dedicated-ai-prompt').val().trim();
                if (!prompt) {
                    alert('Please enter a prompt instruction or click one of the Preset chips above first.');
                    $('#hoa-dedicated-ai-prompt').focus();
                    return;
                }

                const model = $('#hoa-ai-model-select').val();
                triggerAiGeneration(prompt, 'generate', 'insert_below', model);
            });

            // Initial audit run
            setTimeout(() => {
                updateDynamicOutline();
                updateSeoScore();
                updateEeatQualityScore();
                updateKeywordDensityMatrix();
            }, 500);
        }
    });
})(jQuery);
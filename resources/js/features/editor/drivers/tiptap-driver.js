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

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';
import Typography from '@tiptap/extension-typography';
import Link from '@tiptap/extension-link';
import Highlight from '@tiptap/extension-highlight';

export class TiptapDriver {
    constructor(elementId, config = {}) {
        this.elementId = elementId;
        this.config = config;
        this.editor = null;
        this.saveTimeout = null;
        this.debounceMs = config.debounceMs || 1500;

        this.init();
    }

    init() {
        const targetElement = document.getElementById(this.elementId);
        if (!targetElement) {
            console.error(`[TiptapDriver] Element #${this.elementId} not found.`);
            return;
        }

        this.editor = new Editor({
            element: targetElement,
            extensions: [
                StarterKit.configure({
                    heading: { levels: [1, 2, 3, 4] },
                    bulletList: { keepMarks: true, keepAttributes: false },
                    orderedList: { keepMarks: true, keepAttributes: false },
                }),
                Placeholder.configure({
                    placeholder: this.config.placeholder || 'Type / for AI commands or begin writing...',
                    emptyEditorClass: 'is-editor-empty',
                }),
                CharacterCount,
                Typography,
                Link.configure({
                    openOnClick: false,
                    HTMLAttributes: { class: 'text-indigo-400 underline underline-offset-4 hover:text-indigo-300' },
                }),
                Highlight.configure({
                    multicolor: true,
                }),
            ],
            content: this.config.initialContent || '<p></p>',
            editorProps: {
                attributes: {
                    class: 'prose prose-invert max-w-none focus:outline-none min-h-[500px] text-slate-200 leading-relaxed text-base font-normal tracking-wide',
                },
            },
            onUpdate: ({ editor }) => {
                const html = editor.getHTML();
                const json = editor.getJSON();
                const text = editor.getText();
                const words = editor.storage.characterCount.words();
                const chars = editor.storage.characterCount.characters();

                if (typeof this.config.onStatsChange === 'function') {
                    this.config.onStatsChange({ words, characters: chars, html, json, text });
                }

                // Debounced Autosave Trigger
                clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    if (typeof this.config.onAutosave === 'function') {
                        this.config.onAutosave({ html, json, text, words, chars });
                    }
                }, this.debounceMs);
            },
            onSelectionUpdate: ({ editor }) => {
                if (typeof this.config.onSelectionChange === 'function') {
                    const { from, to } = editor.state.selection;
                    const selectedText = editor.state.doc.textBetween(from, to, ' ');
                    this.config.onSelectionChange({ selectedText, isEmpty: from === to });
                }
            },
        });
    }

    getHTML() {
        return this.editor ? this.editor.getHTML() : '';
    }

    getJSON() {
        return this.editor ? this.editor.getJSON() : null;
    }

    getText() {
        return this.editor ? this.editor.getText() : '';
    }

    getWordCount() {
        return this.editor && this.editor.storage.characterCount ? this.editor.storage.characterCount.words() : 0;
    }

    setContent(content, emitUpdate = false) {
        if (this.editor) {
            this.editor.commands.setContent(content, emitUpdate);
        }
    }

    insertContent(content) {
        if (this.editor) {
            this.editor.chain().focus().insertContent(content).run();
        }
    }

    replaceSelection(replacement) {
        if (this.editor) {
            this.editor.chain().focus().insertContent(replacement).run();
        }
    }

    insertBelowSelection(content) {
        if (this.editor) {
            const { to } = this.editor.state.selection;
            this.editor.chain().focus().setTextSelection(to).insertContent(`<p>${content}</p>`).run();
        }
    }

    // Formatting Helpers
    toggleBold() { this.editor?.chain().focus().toggleBold().run(); }
    toggleItalic() { this.editor?.chain().focus().toggleItalic().run(); }
    toggleHeading(level) { this.editor?.chain().focus().toggleHeading({ level }).run(); }
    toggleBulletList() { this.editor?.chain().focus().toggleBulletList().run(); }
    toggleOrderedList() { this.editor?.chain().focus().toggleOrderedList().run(); }
    toggleBlockquote() { this.editor?.chain().focus().toggleBlockquote().run(); }
    toggleCodeBlock() { this.editor?.chain().focus().toggleCodeBlock().run(); }
    setHorizontalRule() { this.editor?.chain().focus().setHorizontalRule().run(); }
    undo() { this.editor?.chain().focus().undo().run(); }
    redo() { this.editor?.chain().focus().redo().run(); }

    destroy() {
        clearTimeout(this.saveTimeout);
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
    }
}

if (window.HOA_EditorManager) {
    window.HOA_EditorManager.registerDriver('tiptap', TiptapDriver);
}
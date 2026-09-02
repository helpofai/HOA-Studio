<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Editor Metabox
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="hoa-wp-editor-wrapper">
    <!-- Action Controls Ribbon -->
    <div class="hoa-wp-editor-header">
        <div class="hoa-wp-editor-title-group">
            <span class="hoa-brand-badge">✨ HOA TipTap AI Canvas</span>
            <span class="hoa-stats-badge">
                <span id="hoa-wp-word-count">0</span> words &bull; <span id="hoa-wp-reading-time">1m</span> read
            </span>
        </div>

        <div class="hoa-wp-editor-actions">
            <!-- Ask AI Command Palette Trigger -->
            <button type="button" id="hoa-wp-btn-ask-ai" class="button button-primary hoa-btn-glow">
                <span>✦ Ask AI</span> <span class="hoa-key-pill">Ctrl+K</span>
            </button>
            <button type="button" id="hoa-wp-btn-fullscreen" class="button button-secondary">
                <span>⛶ Fullscreen</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Formatting Toolbar -->
    <div class="hoa-wp-toolbar">
        <div class="hoa-toolbar-group">
            <button type="button" data-cmd="heading1" title="Heading 1" class="hoa-tool-btn">H1</button>
            <button type="button" data-cmd="heading2" title="Heading 2" class="hoa-tool-btn">H2</button>
            <button type="button" data-cmd="heading3" title="Heading 3" class="hoa-tool-btn">H3</button>
        </div>
        <div class="hoa-toolbar-divider"></div>
        <div class="hoa-toolbar-group">
            <button type="button" data-cmd="bold" title="Bold (Ctrl+B)" class="hoa-tool-btn font-bold">B</button>
            <button type="button" data-cmd="italic" title="Italic (Ctrl+I)" class="hoa-tool-btn italic">I</button>
            <button type="button" data-cmd="underline" title="Underline (Ctrl+U)" class="hoa-tool-btn underline">U</button>
            <button type="button" data-cmd="strike" title="Strike" class="hoa-tool-btn line-through">S</button>
            <button type="button" data-cmd="highlight" title="Highlight" class="hoa-tool-btn">🎨</button>
        </div>
        <div class="hoa-toolbar-divider"></div>
        <div class="hoa-toolbar-group">
            <button type="button" data-cmd="bulletList" title="Bullet List" class="hoa-tool-btn">&bull; List</button>
            <button type="button" data-cmd="orderedList" title="Numbered List" class="hoa-tool-btn">1. List</button>
            <button type="button" data-cmd="taskList" title="Task Checklist" class="hoa-tool-btn">☑ Task</button>
            <button type="button" data-cmd="blockquote" title="Quote / Callout" class="hoa-tool-btn">❝ Quote</button>
            <button type="button" data-cmd="table" title="Insert 3x3 Table" class="hoa-tool-btn">⊞ Table</button>
        </div>
        <div class="hoa-toolbar-divider"></div>
        <div class="hoa-toolbar-group">
            <button type="button" data-cmd="undo" title="Undo (Ctrl+Z)" class="hoa-tool-btn">↺</button>
            <button type="button" data-cmd="redo" title="Redo (Ctrl+Y)" class="hoa-tool-btn">↻</button>
        </div>
    </div>

    <!-- AI Prompt Overlay Bar (Ctrl+K or / Command) -->
    <div id="hoa-wp-ai-bar" class="hoa-ai-bar" style="display: none;">
        <div class="hoa-ai-bar-inner">
            <span class="hoa-ai-sparkle">✦</span>
            <input type="text" id="hoa-wp-ai-prompt-input" placeholder="Ask HOA Studio AI to write, outline, expand, or summarize..." />
            <select id="hoa-wp-ai-type-select">
                <option value="generate">Write Article</option>
                <option value="rewrite">Rewrite & Polish</option>
                <option value="expand">Expand Detail</option>
                <option value="summarize">TL;DR Summary</option>
                <option value="simplify">Simplify Tone</option>
            </select>
            <button type="button" id="hoa-wp-ai-submit-btn" class="button button-primary">Generate</button>
            <button type="button" id="hoa-wp-ai-close-btn" class="button">✕</button>
        </div>
        <div id="hoa-wp-streaming-indicator" class="hoa-streaming-status" style="display: none;">
            <span class="hoa-pulse-dot"></span>
            <span>Streaming AI content from HOA Studio...</span>
            <button type="button" id="hoa-wp-stop-stream-btn" class="button button-small button-link-delete">Stop</button>
        </div>
    </div>

    <!-- TipTap DOM Target Container -->
    <div id="hoa-wp-tiptap-target" class="hoa-tiptap-editor-canvas" contenteditable="true">
        <?php echo $content; ?>
    </div>

    <!-- Hidden Input for Form Submission to WordPress -->
    <textarea id="hoa_tiptap_html_content" name="hoa_tiptap_html_content" style="display:none;"><?php echo esc_textarea($content); ?></textarea>
</div>
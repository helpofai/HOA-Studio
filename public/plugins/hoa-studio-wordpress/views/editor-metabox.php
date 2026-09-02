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
    <!-- Action Controls Ribbon Header -->
    <div class="hoa-wp-editor-header">
        <div class="hoa-wp-editor-title-group">
            <span class="hoa-brand-badge">✨ HOA Studio TipTap AI Canvas</span>
            <span class="hoa-stats-badge">
                <strong id="hoa-wp-word-count">0</strong> words &bull; 
                <strong id="hoa-wp-char-count">0</strong> chars &bull; 
                <strong id="hoa-wp-reading-time">1m</strong> read
            </span>
        </div>

        <div class="hoa-wp-editor-actions">
            <!-- Ask AI Command Palette Trigger -->
            <button type="button" id="hoa-wp-btn-ask-ai" class="button button-primary hoa-btn-glow" title="Ask AI (Ctrl+K or /)">
                <span>✦ Ask AI</span> <span class="hoa-key-pill">Ctrl+K</span>
            </button>
            <button type="button" id="hoa-wp-btn-fullscreen" class="button button-secondary" title="Distraction-Free Fullscreen">
                <span>⛶ Fullscreen</span>
            </button>
            <button type="button" id="hoa-wp-btn-toggle-ribbon" class="button button-secondary" title="Collapse / Expand Ribbon">
                <span class="hoa-chevron">▲</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Master Formatting Toolbar -->
    <div class="hoa-wp-toolbar-inner">
        <div class="hoa-wp-toolbar">
            <!-- Group 1: Headings & Paragraph -->
            <div class="hoa-toolbar-group">
                <button type="button" data-cmd="paragraph" title="Paragraph / Normal Text" class="hoa-tool-btn">¶</button>
                <button type="button" data-cmd="heading1" title="Heading 1" class="hoa-tool-btn font-bold">H1</button>
                <button type="button" data-cmd="heading2" title="Heading 2" class="hoa-tool-btn font-bold">H2</button>
                <button type="button" data-cmd="heading3" title="Heading 3" class="hoa-tool-btn font-bold">H3</button>
                <button type="button" data-cmd="heading4" title="Heading 4" class="hoa-tool-btn">H4</button>
            </div>

            <div class="hoa-toolbar-divider"></div>

            <!-- Group 2: Inline Typography Marks -->
            <div class="hoa-toolbar-group">
                <button type="button" data-cmd="bold" title="Bold (Ctrl+B)" class="hoa-tool-btn font-bold">B</button>
                <button type="button" data-cmd="italic" title="Italic (Ctrl+I)" class="hoa-tool-btn italic">I</button>
                <button type="button" data-cmd="underline" title="Underline (Ctrl+U)" class="hoa-tool-btn underline">U</button>
                <button type="button" data-cmd="strike" title="Strikethrough" class="hoa-tool-btn line-through">S</button>
                <button type="button" data-cmd="subscript" title="Subscript (X₂)" class="hoa-tool-btn">X₂</button>
                <button type="button" data-cmd="superscript" title="Superscript (X²)" class="hoa-tool-btn">X²</button>
                <button type="button" data-cmd="highlight" title="Multicolor Highlight" class="hoa-tool-btn">🎨</button>
                <button type="button" data-cmd="code" title="Inline Code" class="hoa-tool-btn font-mono">&lt;&gt;</button>
                <button type="button" data-cmd="clearFormatting" title="Clear Formatting" class="hoa-tool-btn text-rose-400">Tx</button>
            </div>

            <div class="hoa-toolbar-divider"></div>

            <!-- Group 3: Alignments -->
            <div class="hoa-toolbar-group">
                <button type="button" data-cmd="alignLeft" title="Align Left" class="hoa-tool-btn">⇤</button>
                <button type="button" data-cmd="alignCenter" title="Align Center" class="hoa-tool-btn">↔</button>
                <button type="button" data-cmd="alignRight" title="Align Right" class="hoa-tool-btn">⇥</button>
                <button type="button" data-cmd="alignJustify" title="Justify" class="hoa-tool-btn">⇿</button>
            </div>

            <div class="hoa-toolbar-divider"></div>

            <!-- Group 4: Lists & Quotes -->
            <div class="hoa-toolbar-group">
                <button type="button" data-cmd="bulletList" title="Bullet List" class="hoa-tool-btn">&bull; List</button>
                <button type="button" data-cmd="orderedList" title="Numbered List" class="hoa-tool-btn">1. List</button>
                <button type="button" data-cmd="taskList" title="Interactive Task Checklist" class="hoa-tool-btn">☑ Task</button>
                <button type="button" data-cmd="blockquote" title="Blockquote" class="hoa-tool-btn">❝ Quote</button>
                <button type="button" data-cmd="codeBlock" title="Code Block" class="hoa-tool-btn font-mono">&lt;/&gt;</button>
                <button type="button" data-cmd="hr" title="Horizontal Rule Divider" class="hoa-tool-btn">—</button>
            </div>

            <div class="hoa-toolbar-divider"></div>

            <!-- Group 5: Tables Dropdown -->
            <div class="hoa-dropdown-wrapper">
                <button type="button" class="hoa-tool-btn hoa-dropdown-toggle" title="Table Operations">
                    <span>▦ Table</span> <span class="hoa-caret">▼</span>
                </button>
                <div class="hoa-dropdown-menu" style="display: none;">
                    <button type="button" data-cmd="table" class="hoa-dropdown-item">▦ Insert 3x3 Table</button>
                    <div class="hoa-dropdown-divider"></div>
                    <button type="button" data-cmd="addRowBefore" class="hoa-dropdown-item">⬆ Add Row Above</button>
                    <button type="button" data-cmd="addRowAfter" class="hoa-dropdown-item">⬇ Add Row Below</button>
                    <button type="button" data-cmd="deleteRow" class="hoa-dropdown-item text-danger">✖ Delete Current Row</button>
                    <div class="hoa-dropdown-divider"></div>
                    <button type="button" data-cmd="addColumnBefore" class="hoa-dropdown-item">⬅ Add Column Left</button>
                    <button type="button" data-cmd="addColumnAfter" class="hoa-dropdown-item">➡ Add Column Right</button>
                    <button type="button" data-cmd="deleteColumn" class="hoa-dropdown-item text-danger">✖ Delete Current Column</button>
                    <div class="hoa-dropdown-divider"></div>
                    <button type="button" data-cmd="deleteTable" class="hoa-dropdown-item text-danger">🗑️ Delete Entire Table</button>
                </div>
            </div>

            <!-- Group 6: Custom Callout Boxes Dropdown -->
            <div class="hoa-dropdown-wrapper">
                <button type="button" class="hoa-tool-btn hoa-dropdown-toggle" title="Insert Callout Boxes">
                    <span>💡 Callouts</span> <span class="hoa-caret">▼</span>
                </button>
                <div class="hoa-dropdown-menu" style="display: none;">
                    <button type="button" data-cmd="callout-tip" class="hoa-dropdown-item text-emerald">💡 Pro-Tip Box</button>
                    <button type="button" data-cmd="callout-warning" class="hoa-dropdown-item text-amber">⚠️ Warning Box</button>
                    <button type="button" data-cmd="callout-info" class="hoa-dropdown-item text-cyan">ℹ️ Info Note Box</button>
                    <button type="button" data-cmd="callout-tldr" class="hoa-dropdown-item text-purple">⚡ TL;DR Executive Summary</button>
                    <button type="button" data-cmd="callout-caution" class="hoa-dropdown-item text-rose">🚨 High-Risk Caution</button>
                </div>
            </div>

            <!-- Group 7: Editorial & E-E-A-T Blocks Dropdown -->
            <div class="hoa-dropdown-wrapper">
                <button type="button" class="hoa-tool-btn hoa-dropdown-toggle" title="Insert Editorial & Trust Blocks">
                    <span>🧩 Blocks</span> <span class="hoa-caret">▼</span>
                </button>
                <div class="hoa-dropdown-menu" style="display: none;">
                    <button type="button" data-cmd="block-proscons" class="hoa-dropdown-item">⚖️ Dual Pros & Cons Grid</button>
                    <button type="button" data-cmd="block-faq" class="hoa-dropdown-item">❓ Schema FAQ Accordion</button>
                    <button type="button" data-cmd="block-trust" class="hoa-dropdown-item">🏆 E-E-A-T Testing Trust Box</button>
                    <button type="button" data-cmd="block-timeline" class="hoa-dropdown-item">🔢 Step-by-Step Timeline</button>
                </div>
            </div>

            <div class="hoa-toolbar-divider"></div>

            <!-- Group 8: Media Library & History -->
            <div class="hoa-toolbar-group">
                <button type="button" data-cmd="media" title="Insert Image from WordPress Media Library" class="hoa-tool-btn hoa-btn-highlight">🖼️ Media</button>
                <button type="button" data-cmd="undo" title="Undo (Ctrl+Z)" class="hoa-tool-btn">↺</button>
                <button type="button" data-cmd="redo" title="Redo (Ctrl+Y)" class="hoa-tool-btn">↻</button>
            </div>
        </div>
    </div>

    <!-- In-Canvas AI Command Bar (Ctrl+K or / Command) -->
    <div id="hoa-wp-ai-bar" class="hoa-ai-bar" style="display: none;">
        <div class="hoa-ai-bar-inner">
            <span class="hoa-ai-sparkle">✦</span>
            <input type="text" id="hoa-wp-ai-prompt-input" placeholder="Instruct AI: e.g. Write comprehensive guide, polish with authoritative depth, add comparison table..." />
            <select id="hoa-wp-ai-type-select">
                <option value="generate">Write New Content</option>
                <option value="rewrite">Rewrite & Polish</option>
                <option value="expand">Expand with Depth</option>
                <option value="summarize">TL;DR Summary</option>
                <option value="simplify">Simplify (8th Grade)</option>
                <option value="outline">Article Outline</option>
                <option value="generate_faq">Generate FAQ Block</option>
            </select>
            <div class="hoa-placement-radio-group">
                <label><input type="radio" name="hoa_wp_ai_placement" value="replace" /> <span>Replace</span></label>
                <label><input type="radio" name="hoa_wp_ai_placement" value="insert_below" checked /> <span>Insert Below</span></label>
            </div>
            <button type="button" id="hoa-wp-ai-submit-btn" class="button button-primary hoa-btn-glow">✦ Generate</button>
            <button type="button" id="hoa-wp-ai-close-btn" class="button">✕</button>
        </div>

        <!-- Quick AI Shortcut Chips -->
        <div class="hoa-ai-chips-row">
            <span class="hoa-chips-label">Shortcuts:</span>
            <button type="button" class="hoa-ai-chip" data-prompt="Polish and enhance the phrasing with authoritative technical depth" data-type="rewrite">✨ Polish Phrasing</button>
            <button type="button" class="hoa-ai-chip" data-prompt="Expand this section with detailed real-world examples and architecture" data-type="expand">+ Expand Depth</button>
            <button type="button" class="hoa-ai-chip" data-prompt="Create a structured comparison table analyzing pros, cons, and metrics" data-type="generate">📊 Comparison Table</button>
            <button type="button" class="hoa-ai-chip" data-prompt="Generate 4 high-value schema FAQ questions and authoritative answers" data-type="generate_faq">❓ FAQ Block</button>
            <button type="button" class="hoa-ai-chip" data-prompt="Add technical testing benchmarks and authoritative E-E-A-T credibility context" data-type="expand">🏆 E-E-A-T Trust</button>
        </div>

        <!-- Real-Time SSE Streaming Telemetry -->
        <div id="hoa-wp-streaming-indicator" class="hoa-streaming-status" style="display: none;">
            <span class="hoa-pulse-dot"></span>
            <span>Streaming AI live into TipTap editor...</span>
            <span id="hoa-wp-ai-speed-badge" class="hoa-speed-badge">0 tok/s</span>
            <button type="button" id="hoa-wp-stop-stream-btn" class="button button-small button-link-delete">■ Stop (Esc)</button>
        </div>
    </div>

    <!-- TipTap DOM Target Container -->
    <div id="hoa-wp-tiptap-target" class="hoa-tiptap-editor-canvas">
        <?php echo $content; ?>
    </div>

    <!-- Hidden Input for Form Submission to WordPress -->
    <textarea id="hoa_tiptap_html_content" name="hoa_tiptap_html_content" style="display:none;"><?php echo esc_textarea($content); ?></textarea>

    <!-- SUB-CONTENT-SUB-AGENT In-Canvas Paragraph Proposal Inspector -->
    <div id="hoa-wp-ai-proposal-box" class="hoa-ai-proposal-card" style="display: none;">
        <div class="hoa-proposal-header">
            <div class="hoa-proposal-title">
                <span class="hoa-proposal-ping"></span>
                <strong>✦ SUB-CONTENT-SUB-AGENT (PROPOSAL)</strong>
                <span id="hoa-proposal-speed" class="hoa-speed-badge">0 tok/s</span>
                <span class="hoa-proposal-model">OmniRoute Live</span>
            </div>
            <div class="hoa-proposal-actions">
                <button type="button" id="hoa-btn-accept-proposal" class="button button-primary hoa-btn-accept">✓ Accept & Replace</button>
                <button type="button" id="hoa-btn-discard-proposal" class="button hoa-btn-discard">✕ Discard</button>
            </div>
        </div>
        <div id="hoa-proposal-body" class="hoa-proposal-content"></div>
    </div>

    <!-- Floating Reactive Selection Bubble Menu -->
    <div id="hoa-wp-bubble-menu" class="hoa-bubble-menu" style="display: none;">
        <div class="hoa-bubble-drag-handle" title="Drag to reposition">⋮⋮</div>

        <!-- 1. Quick AI Transforms Trigger -->
        <div class="hoa-bubble-group">
            <button type="button" id="hoa-wp-bubble-ai-btn" class="hoa-bubble-btn hoa-bubble-ai-glow">
                <span>✦ Ask AI</span> <span class="hoa-caret">▼</span>
            </button>
            <div id="hoa-wp-bubble-ai-dropdown" class="hoa-dropdown-menu" style="display: none;">
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="recreate">🤖 Recreate Paragraph (Proposal)</button>
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="rewrite">↻ Rewrite & Polish</button>
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="expand">+ Expand with Depth</button>
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="shorten">− Shorten & Condense</button>
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="simplify">⚡ Simplify (8th Grade)</button>
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="generate_faq">❓ Generate FAQ on this</button>
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="key_takeaways">💡 Key Takeaways</button>
            </div>
        </div>

        <div class="hoa-bubble-divider"></div>

        <!-- 2. Inline Marks -->
        <div class="hoa-bubble-group">
            <button type="button" data-cmd="bold" class="hoa-tool-btn font-bold" title="Bold (Ctrl+B)">B</button>
            <button type="button" data-cmd="italic" class="hoa-tool-btn italic" title="Italic (Ctrl+I)">I</button>
            <button type="button" data-cmd="underline" class="hoa-tool-btn underline" title="Underline (Ctrl+U)">U</button>
            <button type="button" data-cmd="strike" class="hoa-tool-btn line-through" title="Strike">S</button>
            <button type="button" data-cmd="highlight" class="hoa-tool-btn" title="Highlight">🎨</button>
            <button type="button" data-cmd="code" class="hoa-tool-btn font-mono" title="Code">&lt;&gt;</button>
            <button type="button" id="hoa-bubble-link-btn" class="hoa-tool-btn" title="Insert / Edit Link (Ctrl+K)">🔗</button>
        </div>

        <div class="hoa-bubble-divider"></div>

        <!-- 3. Headings & Blockquote -->
        <div class="hoa-bubble-group">
            <button type="button" data-cmd="heading1" class="hoa-tool-btn font-bold" title="Heading 1">H1</button>
            <button type="button" data-cmd="heading2" class="hoa-tool-btn font-bold" title="Heading 2">H2</button>
            <button type="button" data-cmd="heading3" class="hoa-tool-btn font-bold" title="Heading 3">H3</button>
            <button type="button" data-cmd="blockquote" class="hoa-tool-btn font-serif" title="Blockquote">"</button>
        </div>

        <div class="hoa-bubble-divider"></div>

        <!-- 4. Lists & Alignments -->
        <div class="hoa-bubble-group">
            <button type="button" data-cmd="bulletList" class="hoa-tool-btn" title="Bullet List">&bull;</button>
            <button type="button" data-cmd="orderedList" class="hoa-tool-btn text-[11px]" title="Numbered List">1.</button>
            <button type="button" data-cmd="taskList" class="hoa-tool-btn" title="Task Checklist">☑</button>
            <button type="button" data-cmd="table" class="hoa-tool-btn" title="Insert 3x3 Table">▦</button>
            <button type="button" data-cmd="alignLeft" class="hoa-tool-btn" title="Align Left">⇤</button>
            <button type="button" data-cmd="alignCenter" class="hoa-tool-btn" title="Align Center">↔</button>
            <button type="button" data-cmd="alignRight" class="hoa-tool-btn" title="Align Right">⇥</button>
        </div>
    </div>

    <!-- CUSTOM RIGHT-CLICK CONTEXT MENU -->
    <div id="hoa-wp-context-menu" class="hoa-context-menu" style="display: none;">
        <div class="hoa-context-header">
            <span>✦ AI & Editor Context</span>
            <small>Menu</small>
        </div>

        <!-- Section 1: Clipboard & Selection -->
        <div class="hoa-context-group">
            <button type="button" class="hoa-context-item" data-context-cmd="cut">
                <span>✂️ Cut</span> <kbd>Ctrl+X</kbd>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="copy">
                <span>📋 Copy</span> <kbd>Ctrl+C</kbd>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="paste">
                <span>📄 Paste</span> <kbd>Ctrl+V</kbd>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="select_all">
                <span>🔲 Select All</span> <kbd>Ctrl+A</kbd>
            </button>
        </div>

        <div class="hoa-context-divider"></div>

        <!-- Section 2: AI Reasoning & Writing Intelligence -->
        <div class="hoa-context-group">
            <div class="hoa-context-sub-label">sub-content-sub-agent</div>
            <button type="button" class="hoa-context-item hoa-context-item-ai" data-context-cmd="ask_ai_inline">
                <span>✦ Ask AI Inline...</span> <kbd>Ctrl+K</kbd>
            </button>
            <button type="button" class="hoa-context-item hoa-context-item-recreate" data-context-cmd="recreate">
                <span>🔄 Recreate Paragraph (Proposal)</span> <span class="hoa-badge-micro">AI</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="rewrite">
                <span>↻ Rewrite & Polish</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="expand">
                <span>+ Expand with Depth</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="shorten">
                <span>− Shorten & Condense</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="simplify">
                <span>⚡ Simplify (8th-Grade)</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="generate_faq">
                <span>❓ Generate FAQ Block</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="key_takeaways">
                <span>💡 Extract Key Takeaways</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="seo_optimize">
                <span>⌁ SEO Optimize Text</span>
            </button>
        </div>

        <div class="hoa-context-divider"></div>

        <!-- Section 3: Tone Shifter Submenu -->
        <div class="hoa-context-group">
            <div class="hoa-context-sub-label">Tone Shifter</div>
            <div class="hoa-tone-chips-grid">
                <button type="button" class="hoa-tone-btn" data-tone="professional">👔 Executive</button>
                <button type="button" class="hoa-tone-btn" data-tone="casual">☕ Friendly</button>
                <button type="button" class="hoa-tone-btn" data-tone="persuasive">🎯 Persuasive</button>
                <button type="button" class="hoa-tone-btn" data-tone="academic">📚 Academic</button>
            </div>
        </div>

        <div class="hoa-context-divider"></div>

        <!-- Section 4: Quick Inserters & Cleanup -->
        <div class="hoa-context-group">
            <button type="button" class="hoa-context-item" data-context-cmd="insert_date">
                <span>📅 Insert Today's Date</span>
            </button>
            <button type="button" class="hoa-context-item" data-context-cmd="insert_hr">
                <span>— Insert Divider Line</span>
            </button>
            <button type="button" class="hoa-context-item text-danger" data-context-cmd="delete_selection">
                <span>🗑️ Delete Selection</span> <kbd>Del</kbd>
            </button>
        </div>
    </div>

    <!-- FLOATING LINK TOOLTIP / MODAL -->
    <div id="hoa-wp-link-modal" class="hoa-link-modal" style="display: none;">
        <div class="hoa-link-row">
            <span class="hoa-link-icon">🔗</span>
            <input type="url" id="hoa-link-url-input" placeholder="https://example.com/article" />
            <button type="button" id="hoa-btn-apply-link" class="button button-primary hoa-btn-sm">Apply</button>
            <button type="button" id="hoa-btn-remove-link" class="button hoa-btn-sm text-danger" title="Remove Link">✕</button>
        </div>
        <div class="hoa-link-options">
            <label><input type="checkbox" id="hoa-link-blank-check" checked /> <span>Open link in new tab (_blank)</span></label>
        </div>
    </div>

    <!-- FLOATING TABLE CONTEXTUAL HELPER -->
    <div id="hoa-wp-table-controls" class="hoa-table-floating-bar" style="display: none;">
        <span class="hoa-table-label">▦ Table:</span>
        <button type="button" class="hoa-table-btn" data-table-cmd="addRowBefore" title="Add Row Above">⬆ +Row</button>
        <button type="button" class="hoa-table-btn" data-table-cmd="addRowAfter" title="Add Row Below">⬇ +Row</button>
        <button type="button" class="hoa-table-btn text-danger" data-table-cmd="deleteRow" title="Delete Row">✖ Row</button>
        <span class="hoa-toolbar-divider"></span>
        <button type="button" class="hoa-table-btn" data-table-cmd="addColumnBefore" title="Add Column Left">⬅ +Col</button>
        <button type="button" class="hoa-table-btn" data-table-cmd="addColumnAfter" title="Add Column Right">➡ +Col</button>
        <button type="button" class="hoa-table-btn text-danger" data-table-cmd="deleteColumn" title="Delete Column">✖ Col</button>
        <span class="hoa-toolbar-divider"></span>
        <button type="button" class="hoa-table-btn text-danger" data-table-cmd="deleteTable" title="Delete Table">🗑️ Delete</button>
    </div>

    <!-- Floating Slash Commands Palette ('/') -->
    <div id="hoa-wp-slash-menu" class="hoa-slash-palette" style="display: none;">
        <div class="hoa-slash-header">
            <span class="hoa-slash-sparkle">✦ Slash AI Commands</span>
            <input type="text" id="hoa-wp-slash-filter" placeholder="Type to filter..." />
        </div>
        <div class="hoa-slash-items-list">
            <!-- AI Category -->
            <div class="hoa-slash-category">AI Actions</div>
            <button type="button" class="hoa-slash-item" data-slash-cmd="ask_ai">
                <span class="hoa-slash-icon">✦</span>
                <div><strong>Ask AI Anything</strong><small>Generate custom text with prompt</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="continue_writing">
                <span class="hoa-slash-icon">✍️</span>
                <div><strong>Continue Writing</strong><small>AI drafts the next thoughts</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="generate_outline">
                <span class="hoa-slash-icon">📑</span>
                <div><strong>Article Outline</strong><small>Structured H2/H3 headings</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="quick_answer">
                <span class="hoa-slash-icon">⚡</span>
                <div><strong>Quick Answer Box</strong><small>TL;DR search-intent snippet</small></div>
            </button>

            <!-- Editorial Blocks Category -->
            <div class="hoa-slash-category">Editorial & E-E-A-T Blocks</div>
            <button type="button" class="hoa-slash-item" data-slash-cmd="callout-tip">
                <span class="hoa-slash-icon">💡</span>
                <div><strong>Pro-Tip Callout Box</strong><small>Best practice & actionable advice</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="callout-warning">
                <span class="hoa-slash-icon">⚠️</span>
                <div><strong>Warning Box</strong><small>Critical safety precaution</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="block-proscons">
                <span class="hoa-slash-icon">⚖️</span>
                <div><strong>Dual Pros & Cons Grid</strong><small>Balanced comparison matrix</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="block-faq">
                <span class="hoa-slash-icon">❓</span>
                <div><strong>Interactive FAQ Accordion</strong><small>Schema-ready expandable Q&A</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="block-trust">
                <span class="hoa-slash-icon">🏆</span>
                <div><strong>E-E-A-T Testing Trust Box</strong><small>Editorial benchmark badge</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="block-timeline">
                <span class="hoa-slash-icon">🔢</span>
                <div><strong>Step-by-Step Timeline</strong><small>Numbered walkthrough cards</small></div>
            </button>

            <!-- Structure Category -->
            <div class="hoa-slash-category">Structure & Formatting</div>
            <button type="button" class="hoa-slash-item" data-slash-cmd="heading1">
                <span class="hoa-slash-icon">H1</span>
                <div><strong>Heading 1</strong><small>Main title section</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="heading2">
                <span class="hoa-slash-icon">H2</span>
                <div><strong>Heading 2</strong><small>Major subsection</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="heading3">
                <span class="hoa-slash-icon">H3</span>
                <div><strong>Heading 3</strong><small>Minor subsection</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="bulletList">
                <span class="hoa-slash-icon">&bull;</span>
                <div><strong>Bullet List</strong><small>Unordered list items</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="taskList">
                <span class="hoa-slash-icon">☑</span>
                <div><strong>Task Checklist</strong><small>Interactive checkboxes</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="table">
                <span class="hoa-slash-icon">▦</span>
                <div><strong>3x3 Data Table</strong><small>Structured comparison table</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="codeBlock">
                <span class="hoa-slash-icon">&lt;/&gt;</span>
                <div><strong>Syntax Code Block</strong><small>Pre-formatted code snippet</small></div>
            </button>
            <button type="button" class="hoa-slash-item" data-slash-cmd="hr">
                <span class="hoa-slash-icon">—</span>
                <div><strong>Horizontal Divider</strong><small>Section separator line</small></div>
            </button>
        </div>
    </div>
</div>
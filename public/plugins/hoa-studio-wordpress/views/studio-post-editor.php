<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Dedicated Studio Post Editor
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
| 1. Flagship HOA-Studio Master Editor 3-Column Interface in WordPress
| 2. Left Panel: AI Command Center (Model Selector, Swarm, Pipelines, Telemetry)
| 3. Center: Master TipTap Canvas, Collapsible Formatting Ribbon, Floating
|    Selection Bubble Menu, Slash Commands Palette, in-canvas AI Prompt Bar
| 4. Right Panel: Content Intelligence & WP Post Meta (SEO Score, Outline TOC,
|    Target Keywords, Featured Image, Categories, Tags, Slug)
| 5. Top Bar: Live Word Count, Save Status, Direct "Publish to WordPress"
|
|--------------------------------------------------------------------------
*/

if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($post_id) ? intval($post_id) : 0;
$title = isset($title) ? $title : '';
$content = isset($content) ? $content : '';
$post_status = isset($post_status) ? $post_status : 'draft';
$target_keyword = isset($target_keyword) ? $target_keyword : '';
$meta_description = isset($meta_description) ? $meta_description : '';
$categories = isset($categories) ? $categories : [];
$selected_categories = isset($selected_categories) ? $selected_categories : [];
$post_tags = isset($post_tags) ? $post_tags : '';
$featured_image_id = isset($featured_image_id) ? $featured_image_id : 0;
$featured_image_url = isset($featured_image_url) ? $featured_image_url : '';
$permalink = isset($permalink) ? $permalink : '';
$slug = isset($slug) ? $slug : '';
?>

<div id="hoa-studio-dedicated-app" class="hoa-studio-dedicated-workspace">
    <!-- TOP MASTER CONTROL BAR -->
    <header class="hoa-studio-topbar">
        <div class="hoa-topbar-left">
            <a href="<?php echo esc_url(admin_url('edit.php')); ?>" class="hoa-back-btn" title="Back to WordPress Posts">
                <span>&larr;</span> <span class="hoa-hide-mobile">Posts</span>
            </a>
            <div class="hoa-topbar-divider"></div>
            <div class="hoa-title-input-wrap">
                <span class="hoa-badge-ai">✨ HOA Studio</span>
                <input 
                    type="text" 
                    id="hoa-post-title-input" 
                    value="<?php echo esc_attr($title); ?>" 
                    placeholder="Enter post title..." 
                    class="hoa-post-title-field"
                />
            </div>
        </div>

        <div class="hoa-topbar-center">
            <!-- Save Status Badge -->
            <div class="hoa-save-status-badge" id="hoa-save-status">
                <span class="hoa-status-dot"></span>
                <span id="hoa-save-status-text"><?php echo $post_id > 0 ? 'Saved' : 'Ready to draft'; ?></span>
            </div>
        </div>

        <div class="hoa-topbar-right">
            <!-- View Mode Toggles -->
            <div class="hoa-panel-toggle-group">
                <button type="button" id="hoa-toggle-ai-panel" class="hoa-panel-btn active" title="Toggle AI Command Center">
                    ◧ AI
                </button>
                <button type="button" id="hoa-toggle-zen-mode" class="hoa-panel-btn" title="Zen Fullscreen Mode (Ctrl+Shift+F)">
                    ⛶ Zen
                </button>
                <button type="button" id="hoa-toggle-intel-panel" class="hoa-panel-btn active" title="Toggle Content Intelligence">
                    ◨ Intel
                </button>
            </div>

            <!-- Post Status Dropdown -->
            <select id="hoa-post-status-select" class="hoa-status-select">
                <option value="draft" <?php selected($post_status, 'draft'); ?>>Draft</option>
                <option value="pending" <?php selected($post_status, 'pending'); ?>>Pending Review</option>
                <option value="publish" <?php selected($post_status, 'publish'); ?>>Publish</option>
                <option value="private" <?php selected($post_status, 'private'); ?>>Private</option>
            </select>

            <!-- Save Draft Button -->
            <button type="button" id="hoa-btn-save-draft" class="button button-secondary hoa-btn-draft">
                Save Draft
            </button>

            <!-- Primary Publish Button -->
            <button type="button" id="hoa-btn-publish" class="button button-primary hoa-btn-publish-glow">
                <span>✦ <?php echo $post_id > 0 ? 'Update Post' : 'Publish'; ?></span>
            </button>

            <!-- Live Preview Link -->
            <a 
                id="hoa-view-post-link" 
                href="<?php echo esc_url($permalink ?: '#'); ?>" 
                target="_blank" 
                class="hoa-view-btn <?php echo empty($permalink) ? 'disabled' : ''; ?>" 
                title="View Live Post"
            >
                <span>↗</span>
            </a>
        </div>
    </header>

    <!-- 3-COLUMN WORKSPACE BODY -->
    <div class="hoa-studio-body" id="hoa-studio-grid">
        <!-- ===================================================================
             COLUMN 1: AI COMMAND CENTER (LEFT PANEL)
             =================================================================== -->
        <aside class="hoa-sidebar-panel hoa-ai-panel" id="hoa-ai-panel">
            <div class="hoa-panel-header">
                <div class="hoa-panel-title">
                    <span class="hoa-dot-pulse"></span>
                    <h3>AI Command Center</h3>
                </div>
                <span class="hoa-badge-online">ONLINE</span>
            </div>

            <div class="hoa-panel-scrollable">
                <!-- Gateway & Model Select -->
                <div class="hoa-card-box">
                    <label class="hoa-box-label">1. AI Model Gateway</label>
                    <select id="hoa-ai-model-select" class="hoa-select-input">
                        <option value="auto">⚡ Auto (OmniRoute Smart Router)</option>
                        <option value="claude-3-5-sonnet">Claude 3.5 Sonnet (High Reasoning)</option>
                        <option value="gpt-4o">GPT-4o Omnimodal</option>
                        <option value="deepseek-chat">DeepSeek V3 (High Speed)</option>
                        <option value="gemini-1-5-pro">Gemini 1.5 Pro (Massive Context)</option>
                    </select>
                </div>

                <!-- Multi-Agent Swarm Progress -->
                <div class="hoa-card-box">
                    <div class="hoa-box-header">
                        <label class="hoa-box-label">2. Multi-Agent Swarm</label>
                        <span class="hoa-tag-pill">full-content-main-agent</span>
                    </div>
                    <div class="hoa-swarm-grid">
                        <div class="hoa-swarm-step active" title="Agent 1: Search Intent & Research">
                            <span>🎯</span> <small>Research</small>
                        </div>
                        <div class="hoa-swarm-step active" title="Agent 2: Title & Outline Architect">
                            <span>📑</span> <small>Outline</small>
                        </div>
                        <div class="hoa-swarm-step active" title="Agent 3: Full Drafting Engine">
                            <span>✍️</span> <small>Draft</small>
                        </div>
                        <div class="hoa-swarm-step active" title="Agent 4: Comparison Tables & Blocks">
                            <span>▦</span> <small>Media</small>
                        </div>
                        <div class="hoa-swarm-step active" title="Agent 5: SEO Meta & Quality Audit">
                            <span>🚀</span> <small>Meta</small>
                        </div>
                    </div>
                </div>

                <!-- AI Tokens & Speed Telemetry -->
                <div class="hoa-card-box">
                    <div class="hoa-box-header">
                        <label class="hoa-box-label">3. Live Telemetry</label>
                        <span id="hoa-dedicated-speed-badge" class="hoa-speed-tag">0 tok/s</span>
                    </div>
                    <div class="hoa-telemetry-two-col">
                        <div class="hoa-telemetry-stat">
                            <span class="hoa-stat-title">COMPLETION</span>
                            <div class="hoa-stat-val"><span id="hoa-tok-received">0</span> <small>tok</small></div>
                        </div>
                        <div class="hoa-telemetry-stat">
                            <span class="hoa-stat-title">LATENCY</span>
                            <div class="hoa-stat-val"><span id="hoa-tok-latency">12</span> <small>ms</small></div>
                        </div>
                    </div>
                </div>

                <!-- In-Depth Ask AI Prompt Box -->
                <div class="hoa-card-box">
                    <label class="hoa-box-label">4. Ask AI (Streams Directly to Canvas)</label>
                    <textarea 
                        id="hoa-dedicated-ai-prompt" 
                        rows="3" 
                        placeholder="e.g. Write an in-depth WordPress tutorial on TipTap with code samples, callouts, and comparisons..." 
                        class="hoa-textarea-input"
                    ></textarea>

                    <div class="hoa-pipeline-chips">
                        <span class="hoa-chips-header">Presets:</span>
                        <button type="button" class="hoa-preset-chip" data-task="generate_full">Complete Post</button>
                        <button type="button" class="hoa-preset-chip" data-task="outline">Outline</button>
                        <button type="button" class="hoa-preset-chip" data-task="faq">FAQ Schema</button>
                        <button type="button" class="hoa-preset-chip" data-task="table">Comparison Table</button>
                    </div>

                    <button type="button" id="hoa-dedicated-ai-run-btn" class="button button-primary hoa-btn-run-ai">
                        ✦ Execute AI Drafting
                    </button>
                </div>
            </div>
        </aside>

        <!-- ===================================================================
             COLUMN 2: MASTER WRITING WORKSPACE (CENTER CANVAS)
             =================================================================== -->
        <main class="hoa-center-canvas-panel">
            <div class="hoa-wp-editor-wrapper hoa-dedicated-editor-wrap">
                <!-- Master Formatting Ribbon -->
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

                        <!-- Group 6: Custom Callouts Dropdown -->
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

                        <!-- Group 7: Editorial Blocks Dropdown -->
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

                        <!-- Group 8: Media & History -->
                        <div class="hoa-toolbar-group">
                            <button type="button" data-cmd="media" title="Insert Image from WordPress Media Library" class="hoa-tool-btn hoa-btn-highlight">🖼️ Media</button>
                            <button type="button" data-cmd="undo" title="Undo (Ctrl+Z)" class="hoa-tool-btn">↺</button>
                            <button type="button" data-cmd="redo" title="Redo (Ctrl+Y)" class="hoa-tool-btn">↻</button>
                        </div>
                    </div>
                </div>

                <!-- In-Canvas AI Floating Prompt Bar (Ctrl+K or /) -->
                <div id="hoa-wp-ai-bar" class="hoa-ai-bar" style="display: none;">
                    <div class="hoa-ai-bar-inner">
                        <span class="hoa-ai-sparkle">✦</span>
                        <input type="text" id="hoa-wp-ai-prompt-input" placeholder="Instruct AI: e.g. Add comparative analysis with benchmarks..." />
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

                    <!-- Shortcuts Row -->
                    <div class="hoa-ai-chips-row">
                        <span class="hoa-chips-label">Shortcuts:</span>
                        <button type="button" class="hoa-ai-chip" data-prompt="Polish phrasing with authoritative technical depth" data-type="rewrite">✨ Polish Phrasing</button>
                        <button type="button" class="hoa-ai-chip" data-prompt="Expand this section with detailed real-world examples and architecture" data-type="expand">+ Expand Depth</button>
                        <button type="button" class="hoa-ai-chip" data-prompt="Create a structured comparison table analyzing pros, cons, and metrics" data-type="generate">📊 Comparison Table</button>
                        <button type="button" class="hoa-ai-chip" data-prompt="Generate 4 high-value schema FAQ questions and authoritative answers" data-type="generate_faq">❓ FAQ Block</button>
                        <button type="button" class="hoa-ai-chip" data-prompt="Add technical testing benchmarks and authoritative E-E-A-T credibility context" data-type="expand">🏆 E-E-A-T Trust</button>
                    </div>

                    <!-- Streaming Telemetry Indicator -->
                    <div id="hoa-wp-streaming-indicator" class="hoa-streaming-status" style="display: none;">
                        <span class="hoa-pulse-dot"></span>
                        <span>Streaming AI live into TipTap editor...</span>
                        <span id="hoa-wp-ai-speed-badge" class="hoa-speed-badge">0 tok/s</span>
                        <button type="button" id="hoa-wp-stop-stream-btn" class="button button-small button-link-delete">■ Stop (Esc)</button>
                    </div>
                </div>

                <!-- TipTap Target Canvas -->
                <div id="hoa-wp-tiptap-target" class="hoa-tiptap-editor-canvas hoa-dedicated-canvas">
                    <?php echo $content; ?>
                </div>

                <!-- Hidden Input for Form Submission -->
                <textarea id="hoa_tiptap_html_content" name="hoa_tiptap_html_content" style="display:none;"><?php echo esc_textarea($content); ?></textarea>
            </div>
        </main>

        <!-- ===================================================================
             COLUMN 3: CONTENT INTELLIGENCE & WP METADATA (RIGHT PANEL)
             =================================================================== -->
        <aside class="hoa-sidebar-panel hoa-intel-panel" id="hoa-intel-panel">
            <div class="hoa-panel-header">
                <div class="hoa-panel-title">
                    <span class="hoa-dot-emerald"></span>
                    <h3>Content Intelligence</h3>
                </div>
                <span class="hoa-badge-seo-score" id="hoa-seo-score-badge">85/100</span>
            </div>

            <!-- Tab Headers -->
            <div class="hoa-intel-tabs">
                <button type="button" class="hoa-intel-tab active" data-tab="meta">⚙️ Post</button>
                <button type="button" class="hoa-intel-tab" data-tab="seo">🎯 SEO</button>
                <button type="button" class="hoa-intel-tab" data-tab="outline">📑 Outline</button>
            </div>

            <div class="hoa-panel-scrollable">
                <!-- TAB 1: WORDPRESS POST SETTINGS & PUBLISHING -->
                <div class="hoa-tab-content active" id="hoa-tab-meta">
                    <!-- Slug / Permalink -->
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Post URL Slug</label>
                        <input type="text" id="hoa-post-slug" value="<?php echo esc_attr($slug); ?>" placeholder="custom-post-slug" class="hoa-text-input" />
                    </div>

                    <!-- Featured Image -->
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Featured Image</label>
                        <div class="hoa-featured-image-container" id="hoa-featured-image-box">
                            <?php if (!empty($featured_image_url)): ?>
                                <img src="<?php echo esc_url($featured_image_url); ?>" alt="Featured Image" id="hoa-featured-img-preview" />
                                <div class="hoa-img-actions">
                                    <button type="button" id="hoa-btn-change-image" class="button button-small">Change</button>
                                    <button type="button" id="hoa-btn-remove-image" class="button button-small button-link-delete">Remove</button>
                                </div>
                            <?php else: ?>
                                <div class="hoa-no-image-placeholder" id="hoa-no-image-ph">
                                    <span>🖼️</span>
                                    <button type="button" id="hoa-btn-set-featured-image" class="button button-secondary">Set Featured Image</button>
                                </div>
                                <img src="" alt="" id="hoa-featured-img-preview" style="display:none;" />
                                <div class="hoa-img-actions" style="display:none;">
                                    <button type="button" id="hoa-btn-change-image" class="button button-small">Change</button>
                                    <button type="button" id="hoa-btn-remove-image" class="button button-small button-link-delete">Remove</button>
                                </div>
                            <?php endif; ?>
                            <input type="hidden" id="hoa-featured-image-id" value="<?php echo esc_attr($featured_image_id); ?>" />
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Categories</label>
                        <div class="hoa-checklist-box">
                            <?php foreach ($categories as $cat): ?>
                                <label class="hoa-checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        name="hoa_categories[]" 
                                        value="<?php echo esc_attr($cat->term_id); ?>" 
                                        <?php checked(in_array($cat->term_id, $selected_categories)); ?>
                                    />
                                    <span><?php echo esc_html($cat->name); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Tags (Comma-separated)</label>
                        <input type="text" id="hoa-post-tags" value="<?php echo esc_attr($post_tags); ?>" placeholder="ai, tiptap, content, wordpress" class="hoa-text-input" />
                    </div>
                </div>

                <!-- TAB 2: REAL-TIME SEO AUDIT & RANK MATH COMPATIBILITY -->
                <div class="hoa-tab-content" id="hoa-tab-seo" style="display: none;">
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Target / Focus Keyword</label>
                        <input type="text" id="hoa-target-keyword" value="<?php echo esc_attr($target_keyword); ?>" placeholder="e.g. tiptap editor wordpress" class="hoa-text-input" />
                    </div>

                    <div class="hoa-card-box">
                        <label class="hoa-box-label">SEO Meta Description</label>
                        <textarea id="hoa-meta-description" rows="3" placeholder="150-160 characters summary for search engine snippets..." class="hoa-textarea-input"><?php echo esc_textarea($meta_description); ?></textarea>
                    </div>

                    <!-- SEO Metrics Scorecard -->
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Audit Checklist</label>
                        <div class="hoa-audit-list">
                            <div class="hoa-audit-item" id="hoa-check-kw-title">
                                <span class="hoa-audit-icon">✓</span>
                                <span>Focus Keyword in Post Title</span>
                            </div>
                            <div class="hoa-audit-item" id="hoa-check-kw-first">
                                <span class="hoa-audit-icon">✓</span>
                                <span>Focus Keyword in First 10% of Content</span>
                            </div>
                            <div class="hoa-audit-item" id="hoa-check-words">
                                <span class="hoa-audit-icon">✓</span>
                                <span>Content Length &gt; 600 words</span>
                            </div>
                            <div class="hoa-audit-item" id="hoa-check-headings">
                                <span class="hoa-audit-icon">✓</span>
                                <span>Subheadings (H2, H3) Included</span>
                            </div>
                            <div class="hoa-audit-item" id="hoa-check-table">
                                <span class="hoa-audit-icon">✓</span>
                                <span>Data Table or Rich Callout Included</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: DYNAMIC HEADING OUTLINE (TOC) -->
                <div class="hoa-tab-content" id="hoa-tab-outline" style="display: none;">
                    <div class="hoa-card-box">
                        <label class="hoa-box-label">Dynamic Headings Tree (H1-H4)</label>
                        <div id="hoa-dynamic-outline-list" class="hoa-outline-tree">
                            <span class="hoa-empty-note">Headings added to the canvas will automatically populate here as a clickable TOC.</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- BOTTOM FIXED STATUS BAR -->
    <footer class="hoa-studio-statusbar">
        <div class="hoa-status-left">
            <span>Words: <strong id="hoa-wp-word-count">0</strong></span>
            <span class="hoa-status-sep">&bull;</span>
            <span>Chars: <strong id="hoa-wp-char-count">0</strong></span>
            <span class="hoa-status-sep">&bull;</span>
            <span>Est. Reading: <strong id="hoa-wp-reading-time">1m</strong></span>
            <span class="hoa-status-sep">&bull;</span>
            <span>Speaking: <strong id="hoa-wp-speaking-time">1m</strong></span>
        </div>

        <div class="hoa-status-right">
            <span class="hoa-badge-engine">Engine: TipTap v3.30 Enterprise</span>
            <span class="hoa-status-sep">&bull;</span>
            <span class="hoa-live-sync-indicator">⚡ Studio Live Sync</span>
        </div>
    </footer>

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

    <!-- FLOATING SELECTION BUBBLE MENU -->
    <div id="hoa-wp-bubble-menu" class="hoa-bubble-menu" style="display: none;">
        <div class="hoa-bubble-drag-handle" title="Drag to reposition">⋮⋮</div>

        <!-- 1. AI Actions Group -->
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
                <button type="button" class="hoa-dropdown-item hoa-bubble-ai-item" data-ai-action="key_takeaways">💡 Extract Key Takeaways</button>
            </div>
        </div>

        <div class="hoa-bubble-divider"></div>

        <!-- 2. Inline Typography -->
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

        <!-- 3. Headings & Blocks -->
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

    <!-- FLOATING SLASH COMMANDS PALETTE ('/') -->
    <div id="hoa-wp-slash-menu" class="hoa-slash-palette" style="display: none;">
        <div class="hoa-slash-header">
            <span class="hoa-slash-sparkle">✦ Slash AI Commands</span>
            <input type="text" id="hoa-wp-slash-filter" placeholder="Type to filter..." />
        </div>
        <div class="hoa-slash-items-list">
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


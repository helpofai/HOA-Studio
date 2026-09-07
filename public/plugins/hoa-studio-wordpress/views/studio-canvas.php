<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Studio Canvas Master Editor View
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

use HOA_Studio\Core\HOA_Settings;

if (! defined('ABSPATH')) {
    exit;
}

$postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$post = $postId > 0 ? get_post($postId) : null;
$title = $post ? $post->post_title : '';
$status = $post ? $post->post_status : 'draft';
$slug = $post ? $post->post_name : '';
$permalink = $post ? get_permalink($post->ID) : '#';
$targetKeyword = $post ? (string) get_post_meta($post->ID, '_hoa_target_keyword', true) : '';
$metaDescription = $post ? (string) get_post_meta($post->ID, '_hoa_meta_description', true) : '';
$syncedDocId = $post ? (int) get_post_meta($post->ID, '_hoa_synced_document_id', true) : null;

$allCategories = get_categories(['hide_empty' => false]);
$selectedCategories = $post ? wp_get_post_categories($post->ID) : [];
$tags = '';
if ($post) {
    $tagList = wp_get_post_tags($post->ID, ['fields' => 'names']);
    if (! empty($tagList)) {
        $tags = implode(', ', $tagList);
    }
}

$featuredImageId = $post ? (int) get_post_thumbnail_id($post->ID) : 0;
$featuredImageUrl = '';
if ($featuredImageId > 0) {
    $src = wp_get_attachment_image_src($featuredImageId, 'large');
    if ($src) {
        $featuredImageUrl = $src[0];
    }
}

$models = HOA_Settings::getAvailableModels();
$brandVoices = HOA_Settings::getBrandVoices();
$defaultModel = HOA_Settings::getDefaultModel();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="hoa-studio-canvas-html">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? esc_html($post->post_title).' - ' : ''; ?><?php esc_html_e('HOA Studio Master Editor', 'hoa-studio'); ?></title>
    <?php wp_print_head_scripts(); ?>
    <?php wp_print_styles(); ?>
</head>
<body class="hoa-studio-canvas-body">

<div class="hoa-studio-workspace" id="hoa-studio-workspace">
    <!-- Top Global App Bar -->
    <header class="hoa-top-navbar">
        <div class="hoa-nav-left">
            <a href="<?php echo esc_url($postId > 0 ? get_edit_post_link($postId, 'raw') : admin_url('edit.php')); ?>" class="hoa-nav-back-btn" title="<?php esc_attr_e('Back to WordPress Admin', 'hoa-studio'); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                <span><?php esc_html_e('Exit Editor', 'hoa-studio'); ?></span>
            </a>
            <div class="hoa-nav-divider"></div>
            <div class="hoa-logo-badge">
                <span class="hoa-logo-icon">✨</span>
                <span class="hoa-logo-title">HOA Studio</span>
                <span class="hoa-version-tag">v<?php echo esc_html(HOA_STUDIO_VERSION); ?></span>
            </div>
        </div>

        <!-- Document Status & Saving Indicator -->
        <div class="hoa-nav-center">
            <div class="hoa-save-status-pill">
                <span class="hoa-status-dot saved" id="hoa-save-status-dot"></span>
                <span id="hoa-save-status-text"><?php esc_html_e('Ready', 'hoa-studio'); ?></span>
            </div>
            <div class="hoa-speed-badge-wrapper" id="hoa-streaming-status" style="display: none;">
                <span class="hoa-pulse-dot hoa-pulse-violet"></span>
                <span id="hoa-wp-ai-speed-badge">0 tok/s</span>
                <span class="hoa-tok-counter">(<span id="hoa-tok-received">0</span> tok)</span>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="hoa-nav-right">
            <!-- View Live Post -->
            <a href="<?php echo esc_url($permalink); ?>" target="_blank" id="hoa-view-post-link" class="hoa-btn-nav-action <?php echo empty($postId) ? 'disabled' : ''; ?>" title="<?php esc_attr_e('View Published Post', 'hoa-studio'); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span><?php esc_html_e('View', 'hoa-studio'); ?></span>
            </a>

            <!-- 2-Way Cloud Sync Button -->
            <button type="button" id="hoa-cloud-sync-btn" class="hoa-btn-nav-action" title="<?php esc_attr_e('Sync to HOA Studio Cloud Document', 'hoa-studio'); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span id="hoa-cloud-sync-status"><?php echo $syncedDocId ? esc_html__('Cloud Synced', 'hoa-studio') : esc_html__('Push to Cloud', 'hoa-studio'); ?></span>
            </button>

            <!-- Status Selector -->
            <select id="hoa-post-status-select" class="hoa-nav-status-select">
                <option value="draft" <?php selected($status, 'draft'); ?>><?php esc_html_e('Draft', 'hoa-studio'); ?></option>
                <option value="pending" <?php selected($status, 'pending'); ?>><?php esc_html_e('Pending Review', 'hoa-studio'); ?></option>
                <option value="publish" <?php selected($status, 'publish'); ?>><?php esc_html_e('Published', 'hoa-studio'); ?></option>
                <option value="private" <?php selected($status, 'private'); ?>><?php esc_html_e('Private', 'hoa-studio'); ?></option>
            </select>

            <!-- Primary Save / Publish Button -->
            <button type="button" id="hoa-save-post-btn" class="hoa-btn hoa-btn-save-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span><?php esc_html_e('Save Post', 'hoa-studio'); ?></span>
            </button>

            <!-- Fullscreen Canvas Toggle -->
            <button type="button" id="hoa-fullscreen-toggle-btn" class="hoa-btn-icon" title="<?php esc_attr_e('Toggle Distraction-Free Fullscreen', 'hoa-studio'); ?>">
                ⛶
            </button>
        </div>
    </header>

    <!-- Main Workspace Layout: 3 Columns (AI Tools, TipTap Canvas, Inspector & SEO) -->
    <main class="hoa-main-layout">
        <!-- Left Sidebar: AI Copilot & Preset Chips -->
        <aside class="hoa-sidebar hoa-sidebar-left" id="hoa-sidebar-ai">
            <div class="hoa-sidebar-header">
                <div class="hoa-sidebar-title">
                    <span class="hoa-icon-sparkle">⚡</span>
                    <h3><?php esc_html_e('AI Content Copilot', 'hoa-studio'); ?></h3>
                </div>
                <div class="hoa-speed-tag" id="hoa-dedicated-speed-badge">0 tok/s</div>
            </div>

            <!-- Model Selector -->
            <div class="hoa-panel-section">
                <label class="hoa-section-label"><?php esc_html_e('Target Model', 'hoa-studio'); ?></label>
                <select id="hoa-ai-model-select" class="hoa-sidebar-select">
                    <option value="auto">⚡ <?php esc_html_e('Auto (OmniRoute Smart Router)', 'hoa-studio'); ?></option>
                    <?php foreach ($models as $m) { ?>
                        <option value="<?php echo esc_attr($m['model_id']); ?>" <?php selected($defaultModel, $m['model_id']); ?>>
                            <?php echo esc_html($m['name'] ?? $m['model_id']); ?> (<?php echo esc_html($m['provider'] ?? 'OmniRoute'); ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- Quick Prompt Presets -->
            <div class="hoa-panel-section">
                <label class="hoa-section-label"><?php esc_html_e('Quick Prompt Presets', 'hoa-studio'); ?></label>
                <div class="hoa-preset-chips-container">
                    <button type="button" class="hoa-preset-chip" data-task="generate_full">
                        ✨ <?php esc_html_e('Full Article', 'hoa-studio'); ?>
                    </button>
                    <button type="button" class="hoa-preset-chip" data-task="outline">
                        📑 <?php esc_html_e('SEO Outline', 'hoa-studio'); ?>
                    </button>
                    <button type="button" class="hoa-preset-chip" data-task="faq">
                        ❓ <?php esc_html_e('FAQ Schema', 'hoa-studio'); ?>
                    </button>
                    <button type="button" class="hoa-preset-chip" data-task="table">
                        📊 <?php esc_html_e('Data Table', 'hoa-studio'); ?>
                    </button>
                </div>
            </div>

            <!-- Dedicated Prompt Input -->
            <div class="hoa-panel-section">
                <label for="hoa-dedicated-ai-prompt" class="hoa-section-label"><?php esc_html_e('Custom Instruction', 'hoa-studio'); ?></label>
                <textarea
                    id="hoa-dedicated-ai-prompt"
                    rows="4"
                    class="hoa-sidebar-textarea"
                    placeholder="<?php esc_attr_e('Instruct AI to write, expand, restructure, or generate custom editorial blocks...', 'hoa-studio'); ?>"
                ></textarea>
                <button type="button" id="hoa-dedicated-ai-run-btn" class="hoa-btn hoa-btn-run-ai hoa-mt-2">
                    ⚡ <?php esc_html_e('Generate & Stream', 'hoa-studio'); ?>
                </button>
            </div>

            <!-- Real-time Document Outline (ToC) -->
            <div class="hoa-panel-section hoa-panel-outline">
                <label class="hoa-section-label"><?php esc_html_e('Dynamic Outline', 'hoa-studio'); ?></label>
                <div id="hoa-outline-list" class="hoa-outline-scroll-container">
                    <p class="hoa-empty-text"><?php esc_html_e('Headings will appear here automatically...', 'hoa-studio'); ?></p>
                </div>
            </div>
        </aside>

        <!-- Center Column: Document Canvas & TipTap 3.30 -->
        <section class="hoa-center-stage">
            <div class="hoa-canvas-paper">
                <!-- Document Title Input -->
                <div class="hoa-title-wrapper">
                    <input
                        type="text"
                        id="hoa-post-title-input"
                        class="hoa-document-title-input"
                        value="<?php echo esc_attr($title); ?>"
                        placeholder="<?php esc_attr_e('Enter article title here...', 'hoa-studio'); ?>"
                    />
                </div>

                <!-- Slug & Permalink Quick Bar -->
                <div class="hoa-slug-bar">
                    <span class="hoa-slug-prefix"><?php echo esc_html(get_home_url()); ?>/</span>
                    <input
                        type="text"
                        id="hoa-post-slug-input"
                        class="hoa-slug-input"
                        value="<?php echo esc_attr($slug); ?>"
                        placeholder="<?php esc_attr_e('post-slug-url', 'hoa-studio'); ?>"
                    />
                </div>

                <!-- TipTap Master Suite Editor Mount Point -->
                <div class="hoa-tiptap-container" id="hoa-studio-tiptap-editor"></div>

                <!-- Floating Selection Bubble Menu (Rendered by TipTap) -->
                <!-- Floating Slash Commands Palette (Rendered by TipTap) -->
            </div>

            <!-- Bottom Floating Telemetry Bar -->
            <div class="hoa-canvas-footer-bar">
                <div class="hoa-footer-telemetry">
                    <span class="hoa-telemetry-item">
                        📝 <span id="hoa-wp-word-count">0</span> <?php esc_html_e('words', 'hoa-studio'); ?>
                    </span>
                    <span class="hoa-telemetry-separator">•</span>
                    <span class="hoa-telemetry-item">
                        🔡 <span id="hoa-wp-char-count">0</span> <?php esc_html_e('chars', 'hoa-studio'); ?>
                    </span>
                    <span class="hoa-telemetry-separator">•</span>
                    <span class="hoa-telemetry-item">
                        ⏱️ <span id="hoa-wp-read-time">1m</span> <?php esc_html_e('read', 'hoa-studio'); ?>
                    </span>
                    <span class="hoa-telemetry-separator">•</span>
                    <span class="hoa-telemetry-item">
                        🎙️ <span id="hoa-wp-speaking-time">1m</span> <?php esc_html_e('speak', 'hoa-studio'); ?>
                    </span>
                </div>
            </div>
        </section>

        <!-- Right Sidebar: SEO Intelligence, E-E-A-T & Publishing Meta -->
        <aside class="hoa-sidebar hoa-sidebar-right" id="hoa-sidebar-inspector">
            <!-- Tab Switcher -->
            <div class="hoa-tabs-header">
                <button type="button" class="hoa-tab-btn active" data-tab="seo"><?php esc_html_e('SEO & E-E-A-T', 'hoa-studio'); ?></button>
                <button type="button" class="hoa-tab-btn" data-tab="post"><?php esc_html_e('Post Settings', 'hoa-studio'); ?></button>
            </div>

            <!-- Tab 1: SEO & E-E-A-T Intelligence -->
            <div class="hoa-tab-content active" id="hoa-tab-seo">
                <!-- SEO Score Card -->
                <div class="hoa-score-matrix-box">
                    <div class="hoa-score-circle" id="hoa-seo-score-gauge">
                        <span id="hoa-seo-score-number">75</span>
                        <small>/100</small>
                    </div>
                    <div class="hoa-score-meta">
                        <h4><?php esc_html_e('Content SEO Score', 'hoa-studio'); ?></h4>
                        <p id="hoa-seo-rating-label"><?php esc_html_e('Optimized & High Potential', 'hoa-studio'); ?></p>
                    </div>
                </div>

                <!-- 1-Click AI SEO Auto-Generator -->
                <div class="hoa-panel-section">
                    <button type="button" id="hoa-generate-seo-btn" class="hoa-btn hoa-btn-secondary hoa-w-full">
                        ✨ <?php esc_html_e('Generate SEO Meta via AI', 'hoa-studio'); ?>
                    </button>
                </div>

                <!-- Target Focus Keyword -->
                <div class="hoa-panel-section">
                    <label for="hoa-target-keyword" class="hoa-section-label">🎯 <?php esc_html_e('Focus Keyword', 'hoa-studio'); ?></label>
                    <input
                        type="text"
                        id="hoa-target-keyword"
                        class="hoa-sidebar-input"
                        value="<?php echo esc_attr($targetKeyword); ?>"
                        placeholder="<?php esc_attr_e('e.g. ai editor tiptap', 'hoa-studio'); ?>"
                    />
                </div>

                <!-- Meta Description -->
                <div class="hoa-panel-section">
                    <div class="hoa-section-header-flex">
                        <label for="hoa-meta-description" class="hoa-section-label">📝 <?php esc_html_e('Meta Description', 'hoa-studio'); ?></label>
                        <span class="hoa-char-limit" id="hoa-meta-desc-counter">0/155</span>
                    </div>
                    <textarea
                        id="hoa-meta-description"
                        rows="3"
                        class="hoa-sidebar-textarea"
                        placeholder="<?php esc_attr_e('Search snippet preview text...', 'hoa-studio'); ?>"
                    ><?php echo esc_textarea($metaDescription); ?></textarea>
                </div>

                <!-- Keyword Density Matrix -->
                <div class="hoa-panel-section">
                    <label class="hoa-section-label"><?php esc_html_e('Keyword Density Matrix', 'hoa-studio'); ?></label>
                    <div id="hoa-keyword-density-table" class="hoa-density-matrix-box">
                        <div class="hoa-density-row">
                            <span><?php esc_html_e('Density:', 'hoa-studio'); ?></span>
                            <strong id="hoa-density-pct">0.0%</strong>
                        </div>
                        <div class="hoa-density-row">
                            <span><?php esc_html_e('Count:', 'hoa-studio'); ?></span>
                            <strong id="hoa-density-count">0 times</strong>
                        </div>
                    </div>
                </div>

                <!-- E-E-A-T Quality Signals -->
                <div class="hoa-panel-section">
                    <label class="hoa-section-label">🏆 <?php esc_html_e('E-E-A-T Quality Signals', 'hoa-studio'); ?></label>
                    <div class="hoa-eeat-checklist" id="hoa-eeat-checklist">
                        <div class="hoa-eeat-item" id="hoa-eeat-cites">
                            <span class="hoa-eeat-check">○</span>
                            <span><?php esc_html_e('Authoritative External Links', 'hoa-studio'); ?></span>
                        </div>
                        <div class="hoa-eeat-item" id="hoa-eeat-headings">
                            <span class="hoa-eeat-check">○</span>
                            <span><?php esc_html_e('Structured H2 & H3 Hierarchy', 'hoa-studio'); ?></span>
                        </div>
                        <div class="hoa-eeat-item" id="hoa-eeat-tables">
                            <span class="hoa-eeat-check">○</span>
                            <span><?php esc_html_e('Comparison Table / Schema', 'hoa-studio'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Post Taxonomy, Categories & Featured Image -->
            <div class="hoa-tab-content" id="hoa-tab-post" style="display: none;">
                <!-- Featured Image Picker -->
                <div class="hoa-panel-section">
                    <label class="hoa-section-label"><?php esc_html_e('Featured Image', 'hoa-studio'); ?></label>
                    <input type="hidden" id="hoa-featured-image-id" value="<?php echo esc_attr($featuredImageId); ?>" />
                    <div class="hoa-featured-image-preview-box" id="hoa-featured-image-wrapper">
                        <?php if ($featuredImageUrl) { ?>
                            <img src="<?php echo esc_url($featuredImageUrl); ?>" id="hoa-featured-image-preview" alt="Featured" />
                        <?php } else { ?>
                            <div class="hoa-no-image-placeholder" id="hoa-no-image-text">
                                <span>🖼️ <?php esc_html_e('No featured image selected', 'hoa-studio'); ?></span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="hoa-featured-image-actions hoa-mt-2">
                        <button type="button" id="hoa-set-featured-image-btn" class="hoa-btn hoa-btn-secondary hoa-btn-sm">
                            <?php esc_html_e('Choose from Media Library', 'hoa-studio'); ?>
                        </button>
                        <button type="button" id="hoa-remove-featured-image-btn" class="hoa-btn hoa-btn-danger hoa-btn-sm <?php echo empty($featuredImageUrl) ? 'hoa-hidden' : ''; ?>">
                            <?php esc_html_e('Remove', 'hoa-studio'); ?>
                        </button>
                    </div>
                </div>

                <!-- Categories -->
                <div class="hoa-panel-section">
                    <label class="hoa-section-label"><?php esc_html_e('Categories', 'hoa-studio'); ?></label>
                    <div class="hoa-categories-checklist" id="hoa-post-categories-list">
                        <?php foreach ($allCategories as $cat) { ?>
                            <label class="hoa-checkbox-label-sm">
                                <input
                                    type="checkbox"
                                    name="hoa_categories[]"
                                    value="<?php echo esc_attr($cat->term_id); ?>"
                                    <?php checked(in_array($cat->term_id, $selectedCategories, true)); ?>
                                />
                                <span><?php echo esc_html($cat->name); ?></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>

                <!-- Tags -->
                <div class="hoa-panel-section">
                    <label for="hoa-post-tags" class="hoa-section-label"><?php esc_html_e('Tags (comma separated)', 'hoa-studio'); ?></label>
                    <input
                        type="text"
                        id="hoa-post-tags"
                        class="hoa-sidebar-input"
                        value="<?php echo esc_attr($tags); ?>"
                        placeholder="<?php esc_attr_e('ai, wordpress, tiptap', 'hoa-studio'); ?>"
                    />
                </div>
            </div>
        </aside>
    </main>
</div>

<?php wp_print_footer_scripts(); ?>
</body>
</html>

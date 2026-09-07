<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Post Sidebar Metabox View
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

if (! defined('ABSPATH')) {
    exit;
}
?>

<div class="hoa-metabox-container">
    <div class="hoa-metabox-launch-block">
        <a href="<?php echo esc_url($editorUrl); ?>" class="hoa-btn hoa-btn-launch-studio" target="_self">
            <span class="hoa-launch-icon">✨</span>
            <span class="hoa-launch-text">
                <strong><?php esc_html_e('Launch Studio TipTap Editor', 'hoa-studio'); ?></strong>
                <small><?php esc_html_e('Fullscreen Distraction-Free Workspace', 'hoa-studio'); ?></small>
            </span>
        </a>
    </div>

    <!-- Quick Status Badges -->
    <div class="hoa-metabox-status-matrix hoa-mt-3">
        <div class="hoa-status-badge <?php echo $isConnected ? 'hoa-badge-online' : 'hoa-badge-offline'; ?>">
            <span class="hoa-status-dot"></span>
            <?php echo $isConnected ? esc_html__('Studio Gateway Connected', 'hoa-studio') : esc_html__('Gateway Offline', 'hoa-studio'); ?>
        </div>
        <?php if (! empty($syncedDocId)) { ?>
            <div class="hoa-status-badge hoa-badge-synced">
                <span>🔄 <?php esc_html_e('Synced Doc #', 'hoa-studio'); ?><?php echo esc_html($syncedDocId); ?></span>
            </div>
        <?php } ?>
    </div>

    <!-- Target Keyword Field -->
    <div class="hoa-form-group hoa-mt-3">
        <label for="hoa_target_keyword" class="hoa-form-label-sm">
            🎯 <?php esc_html_e('Target Focus Keyword', 'hoa-studio'); ?>
        </label>
        <input
            type="text"
            id="hoa_target_keyword"
            name="hoa_target_keyword"
            value="<?php echo esc_attr($targetKeyword); ?>"
            placeholder="<?php esc_attr_e('e.g. artificial intelligence editor', 'hoa-studio'); ?>"
            class="hoa-input-sm"
        />
    </div>

    <!-- Meta Description Field -->
    <div class="hoa-form-group hoa-mt-2">
        <label for="hoa_meta_description" class="hoa-form-label-sm">
            📝 <?php esc_html_e('SEO Meta Description', 'hoa-studio'); ?>
        </label>
        <textarea
            id="hoa_meta_description"
            name="hoa_meta_description"
            rows="2"
            placeholder="<?php esc_attr_e('Summary for Google SERP results...', 'hoa-studio'); ?>"
            class="hoa-input-sm"
        ><?php echo esc_textarea($metaDesc); ?></textarea>
    </div>
</div>

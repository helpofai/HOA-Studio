<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Settings View
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

$enabledPostTypes = HOA_Settings::getEnabledPostTypes();
$isAutoSync = HOA_Settings::isAutoSyncEnabled();
$allPostTypes = get_post_types(['public' => true], 'objects');
?>

<div class="wrap hoa-studio-admin-wrap">
    <div class="hoa-page-header">
        <h1 class="hoa-hero-title">
            <span class="hoa-gradient-text"><?php esc_html_e('Editor & Sync', 'hoa-studio'); ?></span> Settings
        </h1>
        <p class="hoa-hero-subtitle">
            <?php esc_html_e('Choose which content post types feature the TipTap Master Editor and configure cloud auto-synchronization.', 'hoa-studio'); ?>
        </p>
    </div>

    <?php settings_errors('hoa_messages'); ?>

    <div class="hoa-card hoa-max-w-2xl">
        <div class="hoa-card-header">
            <h3 class="hoa-card-title"><?php esc_html_e('Post Type Integration', 'hoa-studio'); ?></h3>
        </div>
        <div class="hoa-card-body">
            <form method="post" action="">
                <?php wp_nonce_field('hoa_save_settings', 'hoa_save_settings_nonce'); ?>
                <input type="hidden" name="hoa_settings_tab" value="editor" />

                <div class="hoa-form-group">
                    <label class="hoa-form-label"><?php esc_html_e('Enable HOA Studio Metabox & TipTap on:', 'hoa-studio'); ?></label>
                    <div class="hoa-checkbox-list">
                        <?php foreach ($allPostTypes as $pt) { ?>
                            <?php if ($pt->name === 'attachment') {
                                continue;
                            } ?>
                            <label class="hoa-checkbox-item">
                                <input 
                                    type="checkbox" 
                                    name="hoa_studio_enabled_post_types[]" 
                                    value="<?php echo esc_attr($pt->name); ?>" 
                                    <?php checked(in_array($pt->name, $enabledPostTypes, true)); ?> 
                                />
                                <span class="hoa-checkbox-label"><?php echo esc_html($pt->label); ?> (<code><?php echo esc_html($pt->name); ?></code>)</span>
                            </label>
                        <?php } ?>
                    </div>
                </div>

                <div class="hoa-form-group hoa-mt-6">
                    <label class="hoa-form-label"><?php esc_html_e('Bidirectional Cloud Auto-Sync', 'hoa-studio'); ?></label>
                    <label class="hoa-toggle-switch">
                        <input 
                            type="checkbox" 
                            name="hoa_studio_auto_sync" 
                            value="yes" 
                            <?php checked($isAutoSync); ?> 
                        />
                        <span class="hoa-toggle-slider"></span>
                        <span class="hoa-toggle-text"><?php esc_html_e('Automatically push articles to HOA Studio Cloud Documents whenever published in WordPress', 'hoa-studio'); ?></span>
                    </label>
                </div>

                <div class="hoa-form-actions hoa-mt-6">
                    <button type="submit" class="hoa-btn hoa-btn-primary">
                        <?php esc_html_e('Save Editor Settings', 'hoa-studio'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

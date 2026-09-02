<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Studio Bridge
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

<div class="wrap hoa-studio-settings-wrap">
    <div class="hoa-header-card">
        <div class="hoa-brand">
            <div class="hoa-logo-pill">✨ HOA</div>
            <div>
                <h1>Editor Engine Control</h1>
                <p>Manage how the HOA TipTap Editor integrates with WordPress Posts, Pages, and Custom Post Types.</p>
            </div>
        </div>
        <div class="hoa-status-pill connected" id="hoa-connection-status">
            <span class="hoa-dot"></span>
            <span id="hoa-status-text">Core Engine Ready</span>
        </div>
    </div>

    <form method="post" action="options.php" class="hoa-form-card">
        <?php settings_fields('hoa_studio_editor_options'); ?>
        <?php do_settings_sections('hoa_studio_editor_options'); ?>

        <div class="hoa-field-row">
            <label for="hoa_studio_default_editor">Global Default Editing Mode</label>
            <select id="hoa_studio_default_editor" name="hoa_studio_default_editor">
                <option value="tiptap" <?php selected($default_editor, 'tiptap'); ?>>✨ TipTap Enterprise Studio Canvas (Recommended Fullscreen AI Canvas)</option>
                <option value="hybrid" <?php selected($default_editor, 'hybrid'); ?>>⚡ Hybrid Mode (Both TipTap Editor & Gutenberg AI Blocks)</option>
                <option value="gutenberg" <?php selected($default_editor, 'gutenberg'); ?>>🧩 Gutenberg AI Blocks Only</option>
            </select>
            <p class="description">Choose how HOA Studio's multi-driver editor suite is presented to authors on your site.</p>
        </div>

        <div class="hoa-action-row">
            <button type="submit" class="button button-primary button-hero">
                Save Editor Preferences
            </button>
        </div>
    </form>
</div>
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
                <h1>Global AI Intelligence Settings</h1>
                <p>Configure model fallbacks, temperature limits, and brand voice routing.</p>
            </div>
        </div>
        <div class="hoa-status-pill connected" id="hoa-connection-status">
            <span class="hoa-dot"></span>
            <span id="hoa-status-text">Cloud Sync Active</span>
        </div>
    </div>

    <form method="post" action="options.php" class="hoa-form-card">
        <?php settings_fields('hoa_studio_ai_options'); ?>
        <?php do_settings_sections('hoa_studio_ai_options'); ?>

        <div class="hoa-field-row">
            <label for="hoa_studio_default_model">OmniRoute Master Model Preference</label>
            <select id="hoa_studio_default_model" name="hoa_studio_default_model">
                <option value="auto" <?php selected($default_model, 'auto'); ?>>⚡ OmniRoute Dynamic Auto-Routing (Recommended)</option>
                <option value="deepseek-v4-pro" <?php selected($default_model, 'deepseek-v4-pro'); ?>>DeepSeek V4 Pro (High Reasoning)</option>
                <option value="gpt-4o" <?php selected($default_model, 'gpt-4o'); ?>>GPT-4o (Fast Web)</option>
                <option value="claude-3-7-sonnet" <?php selected($default_model, 'claude-3-7-sonnet'); ?>>Claude 3.7 Sonnet (Advanced Coding / Writing)</option>
            </select>
            <p class="description">Select the primary model used for generating content across the WordPress dashboard. 'Auto' lets the Laravel backend route based on token size and latency.</p>
        </div>

        <div class="hoa-field-row" style="margin-top: 24px;">
            <label for="hoa_studio_brand_voice">Global Brand Voice Injection</label>
            <textarea 
                id="hoa_studio_brand_voice" 
                name="hoa_studio_brand_voice" 
                rows="5"
                placeholder="e.g. Write in a confident, professional tone. Avoid passive voice. Use short, punchy sentences."
                style="width: 100%; max-width: 600px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.15); background: #1e293b; color: #fff; padding: 10px 14px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);"
            ><?php echo esc_textarea($brand_voice); ?></textarea>
            <p class="description">This instruction is automatically appended to the system prompt of every AI generation request originating from WordPress.</p>
        </div>

        <div class="hoa-action-row">
            <button type="submit" class="button button-primary button-hero">
                Save AI Configuration
            </button>
        </div>
    </form>
</div>
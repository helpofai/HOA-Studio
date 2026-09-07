<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - AI Settings View
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

$defaultModel = HOA_Settings::getDefaultModel();
$defaultTone = HOA_Settings::getDefaultTone();
$models = HOA_Settings::getAvailableModels();
$brandVoices = HOA_Settings::getBrandVoices();
?>

<div class="wrap hoa-studio-admin-wrap">
    <div class="hoa-page-header">
        <h1 class="hoa-hero-title">
            <span class="hoa-gradient-text"><?php esc_html_e('AI Engine', 'hoa-studio'); ?></span> & Brand Voice
        </h1>
        <p class="hoa-hero-subtitle">
            <?php esc_html_e('Manage intelligent model routing preferences, default writing tones, and synchronized brand personas.', 'hoa-studio'); ?>
        </p>
    </div>

    <?php settings_errors('hoa_messages'); ?>

    <div class="hoa-grid-two-columns">
        <div class="hoa-card">
            <div class="hoa-card-header">
                <h3 class="hoa-card-title"><?php esc_html_e('Model & Tone Preferences', 'hoa-studio'); ?></h3>
            </div>
            <div class="hoa-card-body">
                <form method="post" action="">
                    <?php wp_nonce_field('hoa_save_settings', 'hoa_save_settings_nonce'); ?>
                    <input type="hidden" name="hoa_settings_tab" value="ai" />

                    <div class="hoa-form-group">
                        <label for="hoa_studio_default_model" class="hoa-form-label">
                            <?php esc_html_e('Default AI Model', 'hoa-studio'); ?>
                        </label>
                        <select id="hoa_studio_default_model" name="hoa_studio_default_model" class="hoa-select">
                            <option value="auto" <?php selected($defaultModel, 'auto'); ?>>
                                ⚡ <?php esc_html_e('Auto (OmniRoute Smart Gateway Router)', 'hoa-studio'); ?>
                            </option>
                            <?php foreach ($models as $m) { ?>
                                <option value="<?php echo esc_attr($m['model_id']); ?>" <?php selected($defaultModel, $m['model_id']); ?>>
                                    <?php echo esc_html($m['name'] ?? $m['model_id']); ?> (<?php echo esc_html($m['provider'] ?? 'OmniRoute'); ?>)
                                </option>
                            <?php } ?>
                        </select>
                        <p class="hoa-form-help">
                            <?php esc_html_e('Auto routing dynamically selects the best, most cost-effective model based on the prompt complexity.', 'hoa-studio'); ?>
                        </p>
                    </div>

                    <div class="hoa-form-group">
                        <label for="hoa_studio_default_tone" class="hoa-form-label">
                            <?php esc_html_e('Default Editorial Tone', 'hoa-studio'); ?>
                        </label>
                        <select id="hoa_studio_default_tone" name="hoa_studio_default_tone" class="hoa-select">
                            <?php
                            $tones = ['Professional', 'Authoritative', 'Conversational', 'Engaging', 'Journalistic', 'Technical', 'Casual'];
foreach ($tones as $t) {
    ?>
                                <option value="<?php echo esc_attr($t); ?>" <?php selected($defaultTone, $t); ?>>
                                    <?php echo esc_html($t); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="hoa-form-actions">
                        <button type="submit" class="hoa-btn hoa-btn-primary">
                            <?php esc_html_e('Save AI Preferences', 'hoa-studio'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="hoa-card">
            <div class="hoa-card-header">
                <h3 class="hoa-card-title"><?php esc_html_e('Synchronized Brand Personas', 'hoa-studio'); ?></h3>
                <span class="hoa-pill-counter"><?php echo count($brandVoices); ?></span>
            </div>
            <div class="hoa-card-body">
                <?php if (! empty($brandVoices)) { ?>
                    <div class="hoa-voice-list">
                        <?php foreach ($brandVoices as $v) { ?>
                            <div class="hoa-voice-item">
                                <div class="hoa-voice-icon">🎙️</div>
                                <div class="hoa-voice-details">
                                    <h4 class="hoa-voice-name"><?php echo esc_html($v['name']); ?></h4>
                                    <p class="hoa-voice-desc">
                                        <strong>Tone:</strong> <?php echo esc_html($v['tone'] ?? 'Standard'); ?> | 
                                        <strong>Audience:</strong> <?php echo esc_html($v['audience'] ?? 'General'); ?>
                                    </p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="hoa-empty-box">
                        <p><?php esc_html_e('No brand voices synchronized yet. Create custom Brand Voices inside HOA Studio to maintain consistency across all WordPress articles.', 'hoa-studio'); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

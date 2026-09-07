<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Connection Settings View
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

$endpoint = HOA_Settings::getEndpoint();
$apiKey = HOA_Settings::getApiKey();
$isConnected = HOA_Settings::isConnected();
$webhookSecret = HOA_Settings::getWebhookSecret();
$syncRestUrl = get_rest_url(null, 'hoa-studio/v1/sync');
?>

<div class="wrap hoa-studio-admin-wrap">
    <div class="hoa-page-header">
        <h1 class="hoa-hero-title">
            <span class="hoa-gradient-text"><?php esc_html_e('Studio Connection', 'hoa-studio'); ?></span> & Handshake
        </h1>
        <p class="hoa-hero-subtitle">
            <?php esc_html_e('Configure your secure API credentials to bridge WordPress directly with your HOA Studio workspace.', 'hoa-studio'); ?>
        </p>
    </div>

    <?php settings_errors('hoa_messages'); ?>

    <div class="hoa-grid-two-columns">
        <!-- Settings Form -->
        <div class="hoa-card">
            <div class="hoa-card-header">
                <h3 class="hoa-card-title"><?php esc_html_e('Connection Credentials', 'hoa-studio'); ?></h3>
                <div class="hoa-status-indicator <?php echo $isConnected ? 'hoa-status-online' : 'hoa-status-offline'; ?>">
                    <span class="hoa-status-dot"></span>
                    <span id="hoa-connection-status-text">
                        <?php echo $isConnected ? esc_html__('Connected & Authorized', 'hoa-studio') : esc_html__('Disconnected', 'hoa-studio'); ?>
                    </span>
                </div>
            </div>
            <div class="hoa-card-body">
                <form method="post" action="" id="hoa-connection-form">
                    <?php wp_nonce_field('hoa_save_settings', 'hoa_save_settings_nonce'); ?>
                    <input type="hidden" name="hoa_settings_tab" value="connection" />

                    <div class="hoa-form-group">
                        <label for="hoa_studio_endpoint" class="hoa-form-label">
                            <?php esc_html_e('HOA Studio Base URL', 'hoa-studio'); ?>
                            <span class="hoa-required">*</span>
                        </label>
                        <input
                            type="url"
                            id="hoa_studio_endpoint"
                            name="hoa_studio_endpoint"
                            value="<?php echo esc_attr($endpoint); ?>"
                            placeholder="https://your-studio-domain.com"
                            class="hoa-input"
                            required
                        />
                        <p class="hoa-form-help"><?php esc_html_e('The full URL of your deployed HOA Studio application without trailing slash.', 'hoa-studio'); ?></p>
                    </div>

                    <div class="hoa-form-group">
                        <label for="hoa_studio_api_key" class="hoa-form-label">
                            <?php esc_html_e('Studio Connect Token (API Key)', 'hoa-studio'); ?>
                            <span class="hoa-required">*</span>
                        </label>
                        <div class="hoa-input-with-icon">
                            <input
                                type="password"
                                id="hoa_studio_api_key"
                                name="hoa_studio_api_key"
                                value="<?php echo esc_attr($apiKey); ?>"
                                placeholder="hoa_live_..."
                                class="hoa-input"
                                required
                            />
                            <button type="button" class="hoa-btn-icon-toggle" id="hoa-toggle-secret-btn" title="Toggle visibility">
                                👁️
                            </button>
                        </div>
                        <p class="hoa-form-help"><?php esc_html_e('Generate your token in HOA Studio -> Settings -> WordPress Connect.', 'hoa-studio'); ?></p>
                    </div>

                    <div class="hoa-form-actions">
                        <button type="submit" class="hoa-btn hoa-btn-primary">
                            <?php esc_html_e('Save Credentials', 'hoa-studio'); ?>
                        </button>
                        <button type="button" id="hoa-test-connection-btn" class="hoa-btn hoa-btn-secondary">
                            ⚡ <?php esc_html_e('Test Handshake Now', 'hoa-studio'); ?>
                        </button>
                    </div>
                </form>

                <div id="hoa-connection-result" class="hoa-connection-result-box" style="display: none;"></div>
            </div>
        </div>

        <!-- 2-Way Webhook Information -->
        <div class="hoa-card">
            <div class="hoa-card-header">
                <h3 class="hoa-card-title"><?php esc_html_e('Inbound Cloud Sync Webhook', 'hoa-studio'); ?></h3>
            </div>
            <div class="hoa-card-body">
                <p class="hoa-card-desc">
                    <?php esc_html_e('To enable publishing articles directly from HOA Studio web app to WordPress drafts, configure this webhook in your HOA Studio project settings.', 'hoa-studio'); ?>
                </p>

                <div class="hoa-meta-field">
                    <label class="hoa-meta-label"><?php esc_html_e('REST Sync Endpoint URL', 'hoa-studio'); ?></label>
                    <div class="hoa-copy-box">
                        <code><?php echo esc_html($syncRestUrl); ?></code>
                        <button type="button" class="hoa-btn-copy" data-clipboard-text="<?php echo esc_attr($syncRestUrl); ?>">
                            📋 <?php esc_html_e('Copy', 'hoa-studio'); ?>
                        </button>
                    </div>
                </div>

                <div class="hoa-meta-field hoa-mt-4">
                    <label class="hoa-meta-label"><?php esc_html_e('Webhook Secret Key', 'hoa-studio'); ?></label>
                    <div class="hoa-copy-box">
                        <code><?php echo esc_html($webhookSecret); ?></code>
                        <button type="button" class="hoa-btn-copy" data-clipboard-text="<?php echo esc_attr($webhookSecret); ?>">
                            📋 <?php esc_html_e('Copy', 'hoa-studio'); ?>
                        </button>
                    </div>
                </div>

                <div class="hoa-guide-steps hoa-mt-6">
                    <h4><?php esc_html_e('Setup Checklist:', 'hoa-studio'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Log in to your HOA Studio account.', 'hoa-studio'); ?></li>
                        <li><?php esc_html_e('Go to Settings -> WordPress Bridge.', 'hoa-studio'); ?></li>
                        <li><?php esc_html_e('Click "Generate Studio Connect Token" and paste it here.', 'hoa-studio'); ?></li>
                        <li><?php esc_html_e('Click "Test Handshake Now" to verify active communication.', 'hoa-studio'); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

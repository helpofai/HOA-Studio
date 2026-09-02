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
                <h1>Connection Settings</h1>
                <p>Link your WordPress site securely to your HOA Studio Node.</p>
            </div>
        </div>
        <div class="hoa-status-pill" id="hoa-connection-status">
            <span class="hoa-dot"></span>
            <span id="hoa-status-text">Checking Connection...</span>
        </div>
    </div>

    <form method="post" action="options.php" class="hoa-form-card">
        <?php settings_fields('hoa_studio_connection_options'); ?>
        <?php do_settings_sections('hoa_studio_connection_options'); ?>

        <div class="hoa-field-row">
            <label for="hoa_studio_endpoint_url">HOA Studio Instance Endpoint URL</label>
            <input 
                type="url" 
                id="hoa_studio_endpoint_url" 
                name="hoa_studio_endpoint_url" 
                value="<?php echo esc_attr($endpoint); ?>" 
                placeholder="https://studio.yourdomain.com" 
                class="regular-text"
                required
            />
            <p class="description">Your primary HOA Studio installation URL where your AI models and tokens are hosted.</p>
        </div>

        <div class="hoa-field-row">
            <label for="hoa_studio_connect_key">Personal Studio Connect Key (<code>hoa_live_...</code>)</label>
            <input 
                type="password" 
                id="hoa_studio_connect_key" 
                name="hoa_studio_connect_key" 
                value="<?php echo esc_attr($key); ?>" 
                placeholder="hoa_live_..." 
                class="regular-text code"
                required
            />
            <p class="description">
                Generate this in your HOA Studio workspace under <strong>Settings & Controls &rarr; WordPress Connect Keys</strong>.
            </p>
        </div>

        <div class="hoa-action-row">
            <button type="submit" class="button button-primary button-hero">
                Save Connection Settings
            </button>
            <button type="button" id="hoa-btn-test-conn" class="button button-secondary button-hero">
                Test Connection
            </button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    function runTest() {
        const ep = $('#hoa_studio_endpoint_url').val();
        const key = $('#hoa_studio_connect_key').val();
        const statusEl = $('#hoa-connection-status');
        const statusText = $('#hoa-status-text');

        if (!key) {
            statusEl.removeClass('connected').addClass('disconnected');
            statusText.text('Connect Key Missing');
            return;
        }

        statusText.text('Verifying...');

        $.post(hoaStudioConfig.ajaxUrl, {
            action: 'hoa_studio_test_connection',
            nonce: hoaStudioConfig.nonce,
            endpoint: ep,
            key: key
        }, function(res) {
            if (res.success) {
                statusEl.removeClass('disconnected').addClass('connected');
                statusText.text('Securely Connected');
            } else {
                statusEl.removeClass('connected').addClass('disconnected');
                statusText.text('Failed: ' + (res.data ? res.data.message : 'Unknown error'));
            }
        }).fail(function() {
            statusEl.removeClass('connected').addClass('disconnected');
            statusText.text('Connection Error');
        });
    }

    $('#hoa-btn-test-conn').on('click', runTest);
    if ($('#hoa_studio_connect_key').val()) {
        runTest();
    }
});
</script>
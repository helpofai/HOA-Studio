<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap hoa-studio-settings-wrap">
    <div class="hoa-header-card">
        <div class="hoa-brand">
            <div class="hoa-logo-pill">✨ HOA</div>
            <div>
                <h1>HelpOfAi (HOA) Studio — WordPress AI Bridge</h1>
                <p>Seamlessly connect your self-hosted or cloud HOA-Studio workspace into WordPress.</p>
            </div>
        </div>
        <div class="hoa-status-pill" id="hoa-connection-status">
            <span class="hoa-dot"></span>
            <span id="hoa-status-text">Checking Connection...</span>
        </div>
    </div>

    <form method="post" action="options.php" class="hoa-form-card">
        <?php settings_fields('hoa_studio_options'); ?>
        <?php do_settings_sections('hoa_studio_options'); ?>

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

        <div class="hoa-field-row">
            <label for="hoa_studio_default_editor">Default Editing Mode in Posts & Pages</label>
            <select id="hoa_studio_default_editor" name="hoa_studio_default_editor">
                <option value="tiptap" <?php selected($default_editor, 'tiptap'); ?>>✨ TipTap Enterprise Studio Canvas (Recommended Fullscreen AI Canvas)</option>
                <option value="hybrid" <?php selected($default_editor, 'hybrid'); ?>>⚡ Hybrid Mode (Both TipTap Editor & Gutenberg AI Blocks)</option>
                <option value="gutenberg" <?php selected($default_editor, 'gutenberg'); ?>>🧩 Gutenberg AI Blocks Only</option>
            </select>
            <p class="description">Choose how HOA Studio's editor suite is presented to authors on your site.</p>
        </div>

        <div class="hoa-action-row">
            <button type="submit" class="button button-primary button-hero">
                Save Plugin Settings
            </button>
            <button type="button" id="hoa-btn-test-conn" class="button button-secondary button-hero">
                Test Connection & Fetch Quotas
            </button>
        </div>
    </form>

    <!-- Diagnostics and Account Telemetry Preview Card -->
    <div class="hoa-telemetry-card" id="hoa-telemetry-box" style="display: none;">
        <h3>🔗 Connected Studio Account Telemetry</h3>
        <div class="hoa-metrics-grid">
            <div class="hoa-metric-box">
                <div class="hoa-metric-label">Account Owner</div>
                <div class="hoa-metric-val" id="telemetry-user-name">—</div>
            </div>
            <div class="hoa-metric-box">
                <div class="hoa-metric-label">Monthly Word Balance</div>
                <div class="hoa-metric-val" id="telemetry-quota-words">—</div>
            </div>
            <div class="hoa-metric-box">
                <div class="hoa-metric-label">Active Plan Tier</div>
                <div class="hoa-metric-val" id="telemetry-plan-name">—</div>
            </div>
            <div class="hoa-metric-box">
                <div class="hoa-metric-label">Available AI Models</div>
                <div class="hoa-metric-val" id="telemetry-models-count">—</div>
            </div>
        </div>
    </div>
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
                statusText.text('Connected to HOA Studio');
                $('#hoa-telemetry-box').slideDown();
                $('#telemetry-user-name').text(res.data.user.name + ' (' + res.data.user.email + ')');
                $('#telemetry-quota-words').text(Number(res.data.user.quota.remaining_words).toLocaleString() + ' words left');
                $('#telemetry-plan-name').text(res.data.user.plan.toUpperCase());
                $('#telemetry-models-count').text(res.data.available_models.length + ' active models');
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

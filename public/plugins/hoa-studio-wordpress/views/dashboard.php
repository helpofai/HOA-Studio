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
                <h1>HelpOfAi (HOA) Studio Dashboard</h1>
                <p>Welcome to your AI Copilot. Real-time Node Telemetry is displayed below.</p>
            </div>
        </div>
        <div class="hoa-status-pill" id="hoa-connection-status">
            <span class="hoa-dot"></span>
            <span id="hoa-status-text">Checking Node...</span>
        </div>
    </div>

    <!-- Diagnostics and Account Telemetry Preview Card -->
    <div class="hoa-telemetry-card" id="hoa-telemetry-box" style="display: none;">
        <h3>🔗 Connected Studio Account Telemetry</h3>
        <p style="color: #94a3b8; font-size: 13px; margin-top:-10px; margin-bottom: 20px;">
            Node: <code><?php echo esc_html($endpoint); ?></code>
        </p>
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
    
    <div class="hoa-form-card" id="hoa-missing-key-card" style="display: none;">
        <h3>⚠️ Connection Required</h3>
        <p>Please configure your HOA Connect Key in the <a href="?page=hoa-studio-connection" style="color: #a855f7;">Connection Tab</a> to unlock the dashboard and editor capabilities.</p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function loadDashboardTelemetry() {
        $.post(hoaStudioConfig.ajaxUrl, {
            action: 'hoa_studio_test_connection',
            nonce: hoaStudioConfig.nonce,
            endpoint: hoaStudioConfig.endpoint,
            key: hoaStudioConfig.isConnected ? 'check' : '' // We rely on the backend checking if it's set
        }, function(res) {
            if (res.success) {
                $('#hoa-connection-status').removeClass('disconnected').addClass('connected');
                $('#hoa-status-text').text('Node Online');
                
                $('#hoa-missing-key-card').hide();
                $('#hoa-telemetry-box').slideDown();
                
                $('#telemetry-user-name').text(res.data.user.name + ' (' + res.data.user.email + ')');
                $('#telemetry-quota-words').text(Number(res.data.user.quota.remaining_words).toLocaleString() + ' words left');
                $('#telemetry-plan-name').text(res.data.user.plan.toUpperCase());
                $('#telemetry-models-count').text(res.data.available_models.length + ' active models');
            } else {
                $('#hoa-connection-status').removeClass('connected').addClass('disconnected');
                $('#hoa-status-text').text('Node Disconnected');
                $('#hoa-missing-key-card').show();
            }
        }).fail(function() {
            $('#hoa-connection-status').removeClass('connected').addClass('disconnected');
            $('#hoa-status-text').text('Connection Error');
            $('#hoa-missing-key-card').show();
        });
    }

    if (hoaStudioConfig.isConnected) {
        loadDashboardTelemetry();
    } else {
        $('#hoa-connection-status').removeClass('connected').addClass('disconnected');
        $('#hoa-status-text').text('Not Configured');
        $('#hoa-missing-key-card').show();
    }
});
</script>
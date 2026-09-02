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

    <!-- REAL-TIME TELEMETRY & DIAGNOSTIC LOG CONSOLE -->
    <div class="hoa-console-card">
        <div class="hoa-console-header">
            <div class="hoa-console-title">
                <span class="hoa-console-dot"></span>
                <h3>Real-Time Connection Telemetry & Diagnostic Logs</h3>
                <span class="hoa-badge-live">LIVE</span>
            </div>
            <div class="hoa-console-actions">
                <button type="button" id="hoa-btn-clear-logs" class="button button-small">Clear Logs</button>
                <button type="button" id="hoa-btn-copy-logs" class="button button-small">Copy Telemetry</button>
            </div>
        </div>

        <!-- Live Status Matrix -->
        <div id="hoa-telemetry-matrix" class="hoa-telemetry-matrix" style="display: none;">
            <div class="hoa-matrix-item">
                <span class="hoa-matrix-lbl">Handshake Status</span>
                <span id="hoa-matrix-status" class="hoa-matrix-val text-emerald">CONNECTED</span>
            </div>
            <div class="hoa-matrix-item">
                <span class="hoa-matrix-lbl">Account Email</span>
                <span id="hoa-matrix-email" class="hoa-matrix-val">-</span>
            </div>
            <div class="hoa-matrix-item">
                <span class="hoa-matrix-lbl">Word Quota Remaining</span>
                <span id="hoa-matrix-quota" class="hoa-matrix-val text-indigo">-</span>
            </div>
            <div class="hoa-matrix-item">
                <span class="hoa-matrix-lbl">Synced AI Models</span>
                <span id="hoa-matrix-models" class="hoa-matrix-val text-amber">-</span>
            </div>
            <div class="hoa-matrix-item">
                <span class="hoa-matrix-lbl">Network Latency</span>
                <span id="hoa-matrix-latency" class="hoa-matrix-val text-cyan">- ms</span>
            </div>
        </div>

        <!-- Terminal Log Output Area -->
        <div id="hoa-log-terminal" class="hoa-log-terminal">
            <div class="hoa-log-line">
                <span class="hoa-log-time">[System]</span>
                <span class="hoa-log-tag tag-info">READY</span>
                <span class="hoa-log-msg">HOA Studio Bridge Telemetry Monitor initialized. Click "Test Connection" to run diagnostics.</span>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const $terminal = $('#hoa-log-terminal');
    const $matrix = $('#hoa-telemetry-matrix');

    function getTimestamp() {
        const now = new Date();
        return now.toTimeString().split(' ')[0] + '.' + String(now.getMilliseconds()).padStart(3, '0');
    }

    function appendLog(tagClass, tagText, msg, lineClass = '') {
        const time = getTimestamp();
        const lineHtml = `
            <div class="hoa-log-line ${lineClass}">
                <span class="hoa-log-time">[${time}]</span>
                <span class="hoa-log-tag ${tagClass}">${tagText}</span>
                <span class="hoa-log-msg">${msg}</span>
            </div>
        `;
        $terminal.append(lineHtml);
        $terminal.scrollTop($terminal[0].scrollHeight);
    }

    function runTest() {
        const ep = $('#hoa_studio_endpoint_url').val().trim();
        const key = $('#hoa_studio_connect_key').val().trim();
        const statusEl = $('#hoa-connection-status');
        const statusText = $('#hoa-status-text');

        if (!key) {
            statusEl.removeClass('connected').addClass('disconnected');
            statusText.text('Connect Key Missing');
            appendLog('tag-error', 'ERROR', 'Studio Connect Key is missing. Please generate a key in HOA Studio and paste it above.', 'error');
            return;
        }

        const maskedKey = key.length > 14 
            ? key.substring(0, 10) + '••••••••' + key.slice(-4) 
            : '••••••••';

        const targetUrl = ep.replace(/\/+$/, '') + '/api/v1/wordpress/connect';

        statusText.text('Verifying Handshake...');
        appendLog('tag-outgoing', 'OUTGOING', `POST ${targetUrl}`);
        appendLog('tag-info', 'HEADERS', `Authorization: Bearer ${maskedKey} | Accept: application/json`);
        appendLog('tag-info', 'DISPATCH', 'Executing server-side handshake via WordPress HTTP API...');

        $.post(hoaStudioConfig.ajaxUrl, {
            action: 'hoa_studio_test_connection',
            nonce: hoaStudioConfig.nonce,
            endpoint: ep,
            key: key
        }, function(res) {
            if (res.success && res.data) {
                const d = res.data;
                const latency = d.latency_ms || 0;
                statusEl.removeClass('disconnected').addClass('connected');
                statusText.text('Securely Connected');

                // Telemetry Matrix
                $matrix.show();
                $('#hoa-matrix-status').text('CONNECTED').removeClass('text-rose').addClass('text-emerald');
                $('#hoa-matrix-email').text(d.user ? d.user.email : 'Authenticated');
                $('#hoa-matrix-quota').text(d.user && d.user.quota ? d.user.quota.remaining_words.toLocaleString() + ' words' : 'Unlimited');
                $('#hoa-matrix-models').text((d.available_models ? d.available_models.length : 0) + ' Models');
                $('#hoa-matrix-latency').text(latency + ' ms');

                // Terminal Logs
                appendLog('tag-success', 'SUCCESS', `HTTP 200 OK — Handshake established in ${latency}ms`, 'success');
                if (d.user) {
                    appendLog('tag-success', 'IDENTITY', `Authenticated User: ${d.user.name} (${d.user.email}) | Role: ${d.user.role || 'user'} | Plan: ${d.user.plan || 'Active'}`);
                    if (d.user.quota) {
                        const q = d.user.quota;
                        appendLog('tag-info', 'QUOTA', `Remaining Words: ${q.remaining_words.toLocaleString()} / ${(q.monthly_limit || 0).toLocaleString()} (${q.percentage_used || 0}% used)`);
                    }
                }
                if (d.available_models && d.available_models.length) {
                    const sample = d.available_models.slice(0, 4).map(m => m.name).join(', ');
                    appendLog('tag-info', 'MODELS', `Active AI Suite (${d.available_models.length} models ready): ${sample}...`);
                }
                if (d.brand_voices && d.brand_voices.length) {
                    appendLog('tag-info', 'BRAND', `Loaded ${d.brand_voices.length} customized brand voice personas.`);
                }
                appendLog('tag-success', 'READY', `Node verified. Protocol v${d.protocol_version || '2.6.0'} active on ${ep}.`, 'success');
            } else {
                const errData = res.data || {};
                const msg = errData.message || 'Unknown error occurred during authentication';
                const httpCode = errData.http_code || 0;
                const latency = errData.latency_ms || 0;

                statusEl.removeClass('connected').addClass('disconnected');
                statusText.text('Failed: ' + msg);

                $matrix.show();
                $('#hoa-matrix-status').text('FAILED (' + (httpCode || 'ERR') + ')').removeClass('text-emerald').addClass('text-rose');
                $('#hoa-matrix-latency').text(latency + ' ms');

                appendLog('tag-error', 'FAILED', `HTTP ${httpCode || 'NETWORK_ERR'} Handshake Rejected (${latency}ms): ${msg}`, 'error');
                if (errData.raw_snippet) {
                    appendLog('tag-error', 'RAW', `Server snippet: ${errData.raw_snippet}`, 'error');
                }
                if (httpCode === 500) {
                    appendLog('tag-info', 'ADVICE', 'HTTP 500 indicates a backend exception. Check database migrations or error logs on HOA Studio instance.', 'warning');
                } else if (httpCode === 419) {
                    appendLog('tag-info', 'ADVICE', 'HTTP 419 indicates CSRF token rejection. Ensure api/v1/wordpress/* is excluded from CSRF middleware.', 'warning');
                } else if (httpCode === 401) {
                    appendLog('tag-info', 'ADVICE', 'HTTP 401 indicates invalid or expired key. Verify your Connect Key in HOA Studio Settings.', 'warning');
                }
            }
        }).fail(function(xhr, status, error) {
            statusEl.removeClass('connected').addClass('disconnected');
            statusText.text('Local WordPress AJAX Error');
            appendLog('tag-error', 'AJAX_ERR', `WordPress admin-ajax request failed: ${status} - ${error}`, 'error');
        });
    }

    $('#hoa-btn-test-conn').on('click', function(e) {
        e.preventDefault();
        runTest();
    });

    $('#hoa-btn-clear-logs').on('click', function() {
        $terminal.html(`
            <div class="hoa-log-line">
                <span class="hoa-log-time">[${getTimestamp()}]</span>
                <span class="hoa-log-tag tag-info">CLEARED</span>
                <span class="hoa-log-msg">Diagnostic console cleared.</span>
            </div>
        `);
    });

    $('#hoa-btn-copy-logs').on('click', function() {
        const text = $terminal.text().replace(/\s+/g, ' ').trim();
        const fullLogs = [];
        $terminal.find('.hoa-log-line').each(function() {
            fullLogs.push($(this).text().trim());
        });
        const content = fullLogs.join("\n");
        if (navigator.clipboard) {
            navigator.clipboard.writeText(content).then(() => {
                const $btn = $('#hoa-btn-copy-logs');
                $btn.text('✓ Copied!');
                setTimeout(() => $btn.text('Copy Telemetry'), 2000);
            });
        }
    });

    if ($('#hoa_studio_connect_key').val()) {
        runTest();
    }
});
</script>
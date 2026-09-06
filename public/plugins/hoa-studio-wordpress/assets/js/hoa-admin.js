/**
 * HOA-Studio WordPress Plugin - Admin Dashboard & Settings JS
 * Copyright (c) 2026 Rajib Adhikary / HelpOfAi (HOA)
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const config = window.hoaAdminConfig || {};

        // 1. Password Visibility Toggle
        $('#hoa-toggle-secret-btn').on('click', function() {
            const $input = $('#hoa_studio_api_key');
            const currentType = $input.attr('type');
            $input.attr('type', currentType === 'password' ? 'text' : 'password');
        });

        // 2. Clipboard Copy Utility
        $('.hoa-btn-copy').on('click', function() {
            const textToCopy = $(this).data('clipboard-text');
            if (!textToCopy) return;

            const $btn = $(this);
            const originalText = $btn.text();

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(showCopied);
            } else {
                const $temp = $('<input>').val(textToCopy).appendTo('body').select();
                document.execCommand('copy');
                $temp.remove();
                showCopied();
            }

            function showCopied() {
                $btn.text('✓ ' + (config.i18n ? config.i18n.copied : 'Copied!'));
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);
            }
        });

        // 3. Test Connection & Handshake AJAX
        $('#hoa-test-connection-btn').on('click', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const endpoint = $('#hoa_studio_endpoint').val().trim();
            const key = $('#hoa_studio_api_key').val().trim();
            const $resultBox = $('#hoa-connection-result');

            if (!endpoint || !key) {
                alert('Please enter both the HOA Studio Base URL and API Token before testing.');
                return;
            }

            $btn.prop('disabled', true).text(config.i18n ? config.i18n.testing : 'Testing Handshake...');
            $resultBox.hide().removeClass('hoa-alert-success hoa-alert-warning');

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hoa_studio_test_connection',
                    nonce: config.nonce,
                    endpoint: endpoint,
                    key: key
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('⚡ ' + (config.i18n ? config.i18n.connected : 'Test Handshake Now'));
                    if (res.success && res.data) {
                        const user = res.data.user || {};
                        const quota = user.quota || {};
                        const modelsCount = res.data.available_models ? res.data.available_models.length : 0;

                        $('#hoa-connection-status-text').text('Connected & Authorized');
                        $('.hoa-status-indicator').removeClass('hoa-status-offline').addClass('hoa-status-online');

                        $resultBox.html(`
                            <div class="hoa-alert-box hoa-alert-success" style="padding: 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: 8px; margin-top: 14px; color: #f1f5f9;">
                                <strong>✅ Handshake Successful!</strong><br>
                                Connected to: <em>${user.name || 'Admin'} (${user.email || ''})</em><br>
                                Plan: <strong>${user.plan || 'Standard'}</strong> | Available Quota: <strong>${(quota.remaining_words || 0).toLocaleString()} words</strong><br>
                                Active AI Models Discovered: <strong>${modelsCount}</strong>
                            </div>
                        `).fadeIn();
                    } else {
                        showError(res.data ? res.data.message : 'Unknown response from server.');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('⚡ ' + (config.i18n ? config.i18n.failed : 'Test Handshake Now'));
                    let msg = 'Network error during handshake.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        msg = xhr.responseJSON.data.message;
                    }
                    showError(msg);
                }
            });

            function showError(msg) {
                $resultBox.html(`
                    <div class="hoa-alert-box hoa-alert-warning" style="padding: 12px; background: rgba(244, 63, 94, 0.15); border: 1px solid #f43f5e; border-radius: 8px; margin-top: 14px; color: #f1f5f9;">
                        <strong>❌ Connection Error:</strong> ${msg}
                    </div>
                `).fadeIn();
            }
        });
    });
})(jQuery);

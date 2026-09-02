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
    <!-- TOP HERO HUB HEADER -->
    <div class="hoa-dashboard-hero-card">
        <div class="hoa-card-header-row">
            <div class="hoa-brand">
                <div class="hoa-logo-pill">✨ HOA</div>
                <div>
                    <h1 style="color: #fff; margin: 0; font-size: 22px; font-weight: 800;">HelpOfAi (HOA) Studio Dashboard</h1>
                    <p style="color: #94a3b8; margin: 4px 0 0; font-size: 13px;">Enterprise Multi-Model AI Copilot & In-Canvas Editorial Engine.</p>
                </div>
            </div>
            <div class="hoa-status-pill" id="hoa-connection-status">
                <span class="hoa-dot"></span>
                <span id="hoa-status-text">Synchronizing Node...</span>
            </div>
        </div>

        <!-- QUICK ACTION NAVIGATION -->
        <div class="hoa-dashboard-quick-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-post-editor')); ?>" class="hoa-action-btn-primary">
                <span>✦ Launch Studio Post Editor</span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-connection')); ?>" class="hoa-action-btn-secondary">
                <span>⚙ Connection & Keys</span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-ai-settings')); ?>" class="hoa-action-btn-secondary">
                <span>🤖 AI Model Settings</span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-editor-control')); ?>" class="hoa-action-btn-secondary">
                <span>🎛 Editor Switcher</span>
            </a>
            <button type="button" id="hoa-refresh-telemetry-btn" class="hoa-action-btn-secondary" style="margin-left: auto;">
                <span>↻ Refresh Telemetry</span>
            </button>
        </div>
    </div>

    <!-- ONBOARDING BANNER (WHEN CONNECT KEY MISSING) -->
    <div class="hoa-form-card" id="hoa-missing-key-card" style="display: none;">
        <h3 style="color: #fbbf24; margin-top: 0;">⚠️ Studio Connect Key Required</h3>
        <p style="color: #cbd5e1; font-size: 13.5px; line-height: 1.6;">
            Your WordPress site is not yet connected to an active HOA Studio Node. Please configure your Personal Studio Connect Key to unlock real-time multi-model streaming, quota meters, and the TipTap editorial canvas.
        </p>
        <div style="margin-top: 14px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-connection')); ?>" class="button button-primary button-hero">
                Configure Connect Key &rarr;
            </a>
        </div>
    </div>

    <!-- 4-CARD TELEMETRY MATRIX -->
    <div class="hoa-dashboard-grid-4" id="hoa-telemetry-grid" style="display: none;">
        <!-- Card 1: Account Identity -->
        <div class="hoa-card-telemetry-box">
            <div>
                <div class="hoa-card-header-row">
                    <span class="hoa-card-label">Studio Account</span>
                    <div class="hoa-card-icon-wrap">👤</div>
                </div>
                <div class="hoa-card-main-stat" id="telemetry-user-name">—</div>
                <div class="hoa-card-sub-stat" id="telemetry-user-email">—</div>
            </div>
            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.06); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">Plan Tier</span>
                <span id="telemetry-plan-name" class="hoa-badge-ai" style="font-size: 10px; text-transform: uppercase;">PRO</span>
            </div>
        </div>

        <!-- Card 2: Live Word Quota Meter -->
        <div class="hoa-card-telemetry-box">
            <div>
                <div class="hoa-card-header-row">
                    <span class="hoa-card-label">Monthly Word Balance</span>
                    <div class="hoa-card-icon-wrap" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3);">⚡</div>
                </div>
                <div class="hoa-card-main-stat" id="telemetry-quota-words" style="color: #34d399;">—</div>
                <div class="hoa-quota-bar-wrap">
                    <div id="telemetry-quota-bar" class="hoa-quota-bar-fill" style="width: 0%;"></div>
                </div>
                <div class="hoa-card-sub-stat" id="telemetry-quota-sub">—</div>
            </div>
            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.06); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">Monthly Ceiling</span>
                <span id="telemetry-quota-limit" style="font-size: 11px; color: #cbd5e1; font-family: monospace;">—</span>
            </div>
        </div>

        <!-- Card 3: Multi-Model Gateway -->
        <div class="hoa-card-telemetry-box">
            <div>
                <div class="hoa-card-header-row">
                    <span class="hoa-card-label">OmniRoute AI Gateway</span>
                    <div class="hoa-card-icon-wrap" style="background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3);">🤖</div>
                </div>
                <div class="hoa-card-main-stat" id="telemetry-models-count" style="color: #fbbf24;">—</div>
                <div class="hoa-card-sub-stat" id="telemetry-models-sub">DeepSeek, Claude, GPT, Gemini</div>
            </div>
            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.06); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">Default Model</span>
                <span style="font-size: 11px; color: #a5b4fc; font-family: monospace;"><?php echo esc_html($default_model); ?></span>
            </div>
        </div>

        <!-- Card 4: Editorial Engine Status -->
        <div class="hoa-card-telemetry-box">
            <div>
                <div class="hoa-card-header-row">
                    <span class="hoa-card-label">Editorial Engine</span>
                    <div class="hoa-card-icon-wrap" style="background: rgba(168, 85, 247, 0.15); border-color: rgba(168, 85, 247, 0.3);">✍️</div>
                </div>
                <div class="hoa-card-main-stat" style="color: #c084fc;"><?php echo esc_html(strtoupper($default_editor)); ?> SUITE</div>
                <div class="hoa-card-sub-stat">Bubble Menu, Context Menu & E-E-A-T</div>
            </div>
            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.06); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">Network Latency</span>
                <span id="telemetry-latency-badge" style="font-size: 11px; color: #38bdf8; font-family: monospace;">— ms</span>
            </div>
        </div>
    </div>

    <!-- CONTENT CREATION LAUNCHPAD -->
    <div class="hoa-content-launchpad-card" id="hoa-launchpad-section" style="display: none;">
        <div class="hoa-launchpad-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🚀</span>
                <h3 style="margin: 0;">Instant Post Creation & AI Blueprint Launchpad</h3>
            </div>
            <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">HOA Studio Fast-Track</span>
        </div>

        <div class="hoa-launchpad-input-wrap">
            <input 
                type="text" 
                id="hoa-quick-post-title" 
                placeholder="Enter article topic or working title (e.g. Next-Gen Cloud Architecture with Kubernetes)..." 
                class="hoa-launchpad-input"
            />
            <button type="button" id="hoa-btn-launch-post" class="button button-primary button-hero" style="font-size: 13px; height: auto; padding: 10px 20px;">
                ✦ Create Post &rarr;
            </button>
        </div>

        <div class="hoa-template-chips-row">
            <span style="font-size: 11px; color: #94a3b8; font-family: monospace; margin-right: 4px;">Blueprints:</span>
            <button type="button" class="hoa-template-btn" data-title="Comprehensive Technical Architecture & Deep Dive" data-prompt="deep_dive">
                🔬 Deep-Dive Analysis
            </button>
            <button type="button" class="hoa-template-btn" data-title="Comprehensive Product Comparison & Tradeoff Matrix" data-prompt="comparison">
                📊 Comparison & Schema Table
            </button>
            <button type="button" class="hoa-template-btn" data-title="Definitive Guide with Step-by-Step Walkthrough" data-prompt="guide">
                💡 Actionable Step-by-Step Guide
            </button>
            <button type="button" class="hoa-template-btn" data-title="High-Value Schema FAQ & Key Takeaways" data-prompt="faq">
                ❓ FAQ & Featured Snippets
            </button>
            <button type="button" class="hoa-template-btn" data-title="Authoritative Case Study with E-E-A-T Evidence" data-prompt="eeat">
                🏆 E-E-A-T Trust Case Study
            </button>
        </div>
    </div>

    <!-- MULTI-MODEL GATEWAY EXPLORER (26 LIVE MODELS) -->
    <div class="hoa-models-suite-card" id="hoa-models-section" style="display: none;">
        <div class="hoa-models-toolbar">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🤖</span>
                <h3 style="margin: 0;">Connected OmniRoute AI Models (<span id="hoa-models-header-count">0</span> Synced)</h3>
            </div>
            <input 
                type="text" 
                id="hoa-models-filter" 
                placeholder="Filter models (DeepSeek, Claude, GPT)..." 
                class="hoa-search-models-input"
            />
        </div>
        
        <div id="hoa-models-list-container" class="hoa-models-list-grid">
            <!-- Dynamically populated via telemetry -->
        </div>
    </div>

    <!-- BRAND VOICE PERSONAS PREVIEW (IF AVAILABLE) -->
    <div class="hoa-models-suite-card" id="hoa-brand-voices-section" style="display: none;">
        <div class="hoa-models-toolbar">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🎭</span>
                <h3 style="margin: 0;">Synced Brand Voice Personas</h3>
            </div>
        </div>
        <div id="hoa-brand-voices-container" class="hoa-models-list-grid">
            <!-- Dynamically populated -->
        </div>
    </div>

    <!-- NODE ENVIRONMENT & TELEMETRY FOOTER -->
    <div class="hoa-card-telemetry-box" style="background: #060913; border-color: rgba(255, 255, 255, 0.05); margin-top: 16px;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; font-size: 12px; color: #94a3b8; font-family: monospace;">
            <div>
                Node Endpoint: <code style="color: #cbd5e1;"><?php echo esc_html($endpoint); ?></code>
            </div>
            <div>
                Protocol: <span id="telemetry-protocol-ver" style="color: #a5b4fc;">v2.6.0</span>
            </div>
            <div>
                Last Synchronized: <span id="telemetry-last-sync" style="color: #34d399;">Awaiting Check</span>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let globalModels = [];

    function formatNumber(num) {
        return Number(num || 0).toLocaleString();
    }

    function loadDashboardTelemetry() {
        const $statusPill = $('#hoa-connection-status');
        const $statusText = $('#hoa-status-text');

        $statusText.text('Synchronizing Node...');

        $.post(hoaStudioConfig.ajaxUrl, {
            action: 'hoa_studio_test_connection',
            nonce: hoaStudioConfig.nonce,
            endpoint: hoaStudioConfig.endpoint,
            key: 'check'
        }, function(res) {
            if (res.success && res.data) {
                const d = res.data;
                const latency = d.latency_ms || 0;

                $statusPill.removeClass('disconnected').addClass('connected');
                $statusText.text('Node Online (' + latency + 'ms)');

                $('#hoa-missing-key-card').hide();
                $('#hoa-telemetry-grid').fadeIn(200);
                $('#hoa-launchpad-section').fadeIn(250);
                $('#hoa-models-section').fadeIn(300);

                // User Identity
                if (d.user) {
                    $('#telemetry-user-name').text(d.user.name || 'Account Owner');
                    $('#telemetry-user-email').text(d.user.email || '—');
                    $('#telemetry-plan-name').text((d.user.plan || 'PRO').toUpperCase());

                    // Quota Meter
                    if (d.user.quota) {
                        const q = d.user.quota;
                        const remaining = q.remaining_words || 0;
                        const limit = q.monthly_limit || 0;
                        const pct = q.percentage_used || 0;

                        $('#telemetry-quota-words').text(formatNumber(remaining) + ' words left');
                        $('#telemetry-quota-limit').text(formatNumber(limit) + ' words/mo');
                        $('#telemetry-quota-sub').text('Used ' + formatNumber(q.used_words || 0) + ' words (' + pct + '% consumed)');
                        
                        const $bar = $('#telemetry-quota-bar');
                        $bar.css('width', Math.min(100, pct) + '%');
                        if (pct > 90) {
                            $bar.addClass('danger');
                        } else {
                            $bar.removeClass('danger');
                        }
                    }
                }

                // AI Models List
                if (d.available_models && d.available_models.length) {
                    globalModels = d.available_models;
                    $('#telemetry-models-count').text(d.available_models.length + ' AI Models');
                    $('#hoa-models-header-count').text(d.available_models.length);
                    renderModelsList(globalModels);
                }

                // Brand Voices
                if (d.brand_voices && d.brand_voices.length) {
                    $('#hoa-brand-voices-section').show();
                    renderBrandVoices(d.brand_voices);
                }

                // Latency & Protocol
                $('#telemetry-latency-badge').text(latency + ' ms');
                $('#telemetry-protocol-ver').text('v' + (d.protocol_version || '2.6.0'));
                $('#telemetry-last-sync').text(new Date().toLocaleTimeString());
            } else {
                $statusPill.removeClass('connected').addClass('disconnected');
                $statusText.text('Node Disconnected');
                $('#hoa-telemetry-grid').hide();
                $('#hoa-launchpad-section').hide();
                $('#hoa-models-section').hide();
                $('#hoa-missing-key-card').slideDown();
            }
        }).fail(function() {
            $statusPill.removeClass('connected').addClass('disconnected');
            $statusText.text('Connection Error');
            $('#hoa-missing-key-card').slideDown();
        });
    }

    function renderModelsList(models) {
        const $container = $('#hoa-models-list-container');
        $container.empty();

        if (!models.length) {
            $container.html('<div style="color: #64748b; font-size: 12px; padding: 12px;">No matching AI models found.</div>');
            return;
        }

        models.forEach(function(m) {
            const ctxWindow = m.context_window ? (m.context_window >= 1000 ? Math.round(m.context_window / 1000) + 'K' : m.context_window) : '128K';
            const card = `
                <div class="hoa-model-chip-box">
                    <div class="hoa-model-name-row">
                        <span class="hoa-model-name">${m.name}</span>
                        <span class="hoa-model-provider">${m.provider || 'OmniRoute'}</span>
                    </div>
                    <span class="hoa-model-id-txt">${m.model_id}</span>
                    <div class="hoa-model-meta-row">
                        <span>Context: <strong style="color: #c7d2fe;">${ctxWindow} tokens</strong></span>
                        <span style="color: #34d399; font-weight: 700;">● Active</span>
                    </div>
                </div>
            `;
            $container.append(card);
        });
    }

    function renderBrandVoices(voices) {
        const $container = $('#hoa-brand-voices-container');
        $container.empty();

        voices.forEach(function(v) {
            const card = `
                <div class="hoa-model-chip-box">
                    <div class="hoa-model-name-row">
                        <span class="hoa-model-name">${v.name}</span>
                        <span class="hoa-badge-ai" style="font-size: 9.5px;">PERSONA</span>
                    </div>
                    <p style="font-size: 11.5px; color: #cbd5e1; margin: 4px 0;">${v.tone || 'Custom Brand Tone'}</p>
                    <div class="hoa-model-meta-row">
                        <span>Audience: <strong style="color: #c7d2fe;">${v.audience || 'General'}</strong></span>
                    </div>
                </div>
            `;
            $container.append(card);
        });
    }

    // Filter Models
    $('#hoa-models-filter').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        if (!query) {
            renderModelsList(globalModels);
            return;
        }
        const filtered = globalModels.filter(m => 
            (m.name && m.name.toLowerCase().includes(query)) ||
            (m.model_id && m.model_id.toLowerCase().includes(query)) ||
            (m.provider && m.provider.toLowerCase().includes(query))
        );
        renderModelsList(filtered);
    });

    // Launchpad Post Creation
    function launchPostEditor(title = '') {
        const baseUrl = '<?php echo esc_url(admin_url('admin.php?page=hoa-studio-post-editor')); ?>';
        if (title) {
            window.location.href = baseUrl + '&title=' + encodeURIComponent(title);
        } else {
            window.location.href = baseUrl;
        }
    }

    $('#hoa-btn-launch-post').on('click', function() {
        const title = $('#hoa-quick-post-title').val().trim();
        launchPostEditor(title);
    });

    $('#hoa-quick-post-title').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            launchPostEditor($(this).val().trim());
        }
    });

    $('.hoa-template-btn').on('click', function() {
        const title = $(this).data('title');
        launchPostEditor(title);
    });

    // Refresh Telemetry
    $('#hoa-refresh-telemetry-btn').on('click', function(e) {
        e.preventDefault();
        loadDashboardTelemetry();
    });

    // Initial Load
    loadDashboardTelemetry();
});
</script>
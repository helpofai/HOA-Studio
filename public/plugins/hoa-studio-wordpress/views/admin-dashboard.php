<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Dashboard View
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

if (!defined('ABSPATH')) {
    exit;
}

$isConnected = HOA_Settings::isConnected();
$user = HOA_Settings::getUserData();
$quota = $user['quota'] ?? ['monthly_limit' => 0, 'used_words' => 0, 'remaining_words' => 0, 'percentage_used' => 0];
$models = HOA_Settings::getAvailableModels();
$brandVoices = HOA_Settings::getBrandVoices();
?>

<div class="wrap hoa-studio-admin-wrap">
    <div class="hoa-dashboard-hero">
        <div class="hoa-hero-content">
            <div class="hoa-badge-pill">
                <span class="hoa-pulse-dot <?php echo $isConnected ? 'hoa-pulse-green' : 'hoa-pulse-red'; ?>"></span>
                <?php echo $isConnected ? esc_html__('Node Synced & Operational', 'hoa-studio') : esc_html__('Setup Required', 'hoa-studio'); ?>
            </div>
            <h1 class="hoa-hero-title">
                <span class="hoa-gradient-text">HOA Studio</span> <?php esc_html_e('AI & TipTap Master Suite', 'hoa-studio'); ?>
            </h1>
            <p class="hoa-hero-subtitle">
                <?php esc_html_e('Enterprise editorial intelligence gateway connecting your WordPress site with TipTap 3.30, OmniRoute AI, and live SSE streaming.', 'hoa-studio'); ?>
            </p>
        </div>
        <div class="hoa-hero-actions">
            <a href="<?php echo esc_url(admin_url('post-new.php')); ?>" class="hoa-btn hoa-btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <?php esc_html_e('New Article in TipTap', 'hoa-studio'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-connection')); ?>" class="hoa-btn hoa-btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <?php esc_html_e('Connection Settings', 'hoa-studio'); ?>
            </a>
        </div>
    </div>

    <!-- Telemetry & Stats Matrix -->
    <div class="hoa-grid-cards">
        <!-- Connection Card -->
        <div class="hoa-card">
            <div class="hoa-card-header">
                <div class="hoa-card-icon hoa-icon-violet">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
                </div>
                <div>
                    <h3 class="hoa-card-title"><?php esc_html_e('Studio Handshake', 'hoa-studio'); ?></h3>
                    <p class="hoa-card-desc"><?php echo esc_html(HOA_Settings::getEndpoint() ?: __('No endpoint configured', 'hoa-studio')); ?></p>
                </div>
            </div>
            <div class="hoa-card-body">
                <?php if ($isConnected): ?>
                    <div class="hoa-user-pill">
                        <div class="hoa-avatar-letter"><?php echo esc_html(strtoupper(substr($user['name'] ?? 'U', 0, 1))); ?></div>
                        <div class="hoa-user-meta">
                            <span class="hoa-user-name"><?php echo esc_html($user['name'] ?? 'Authorized User'); ?></span>
                            <span class="hoa-user-plan"><?php echo esc_html($user['plan'] ?? 'Pro'); ?> Plan</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hoa-alert-box hoa-alert-warning">
                        <span>⚠️ <?php esc_html_e('Plugin is not connected to your HOA Studio instance. Please configure your token.', 'hoa-studio'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quota Meter Card -->
        <div class="hoa-card">
            <div class="hoa-card-header">
                <div class="hoa-card-icon hoa-icon-emerald">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div>
                    <h3 class="hoa-card-title"><?php esc_html_e('AI Word Quota', 'hoa-studio'); ?></h3>
                    <p class="hoa-card-desc"><?php esc_html_e('Live token consumption balance', 'hoa-studio'); ?></p>
                </div>
            </div>
            <div class="hoa-card-body">
                <div class="hoa-quota-numbers">
                    <span class="hoa-quota-remaining"><?php echo number_format($quota['remaining_words'] ?? 0); ?></span>
                    <span class="hoa-quota-total">/ <?php echo number_format($quota['monthly_limit'] ?? 0); ?> <?php esc_html_e('words left', 'hoa-studio'); ?></span>
                </div>
                <div class="hoa-progress-track">
                    <div class="hoa-progress-bar" style="width: <?php echo min(100, max(0, 100 - ($quota['percentage_used'] ?? 0))); ?>%;"></div>
                </div>
                <div class="hoa-quota-footer">
                    <span><?php echo esc_html($quota['percentage_used'] ?? 0); ?>% <?php esc_html_e('used this month', 'hoa-studio'); ?></span>
                </div>
            </div>
        </div>

        <!-- Available Models & Capabilities -->
        <div class="hoa-card">
            <div class="hoa-card-header">
                <div class="hoa-card-icon hoa-icon-cyan">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                </div>
                <div>
                    <h3 class="hoa-card-title"><?php esc_html_e('AI Engine Matrix', 'hoa-studio'); ?></h3>
                    <p class="hoa-card-desc"><?php echo count($models); ?> <?php esc_html_e('Models Discovered', 'hoa-studio'); ?></p>
                </div>
            </div>
            <div class="hoa-card-body">
                <div class="hoa-badge-grid">
                    <span class="hoa-tag-badge hoa-tag-violet">⚡ OmniRoute Auto</span>
                    <?php foreach (array_slice($models, 0, 4) as $m): ?>
                        <span class="hoa-tag-badge"><?php echo esc_html($m['name'] ?? $m['model_id']); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="hoa-card-action-link">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hoa-studio-ai')); ?>">
                        <?php esc_html_e('Configure AI Models & Brand Voices →', 'hoa-studio'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Features Overview -->
    <div class="hoa-card hoa-mt-6">
        <div class="hoa-card-header">
            <h3 class="hoa-card-title"><?php esc_html_e('Integrated Studio Suite Capabilities', 'hoa-studio'); ?></h3>
        </div>
        <div class="hoa-features-grid">
            <div class="hoa-feature-item">
                <div class="hoa-feature-icon">✨</div>
                <h4><?php esc_html_e('Fullscreen TipTap 3.30', 'hoa-studio'); ?></h4>
                <p><?php esc_html_e('Distraction-free rich editor with floating slash menu, live word/token telemetry, and table formatting.', 'hoa-studio'); ?></p>
            </div>
            <div class="hoa-feature-item">
                <div class="hoa-feature-icon">⚡</div>
                <h4><?php esc_html_e('Real-time SSE Streaming', 'hoa-studio'); ?></h4>
                <p><?php esc_html_e('Instant chunk streaming for generation, rewriting, summarizing, and tone changes with zero buffering.', 'hoa-studio'); ?></p>
            </div>
            <div class="hoa-feature-item">
                <div class="hoa-feature-icon">🎯</div>
                <h4><?php esc_html_e('SEO & E-E-A-T Auditor', 'hoa-studio'); ?></h4>
                <p><?php esc_html_e('Real-time heading structure, keyword density analysis, schema recommendations, and meta generation.', 'hoa-studio'); ?></p>
            </div>
            <div class="hoa-feature-item">
                <div class="hoa-feature-icon">🔄</div>
                <h4><?php esc_html_e('2-Way Cloud Sync', 'hoa-studio'); ?></h4>
                <p><?php esc_html_e('Seamless bidirectional synchronization between WordPress posts and HOA Studio documents.', 'hoa-studio'); ?></p>
            </div>
        </div>
    </div>
</div>

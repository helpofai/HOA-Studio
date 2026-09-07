<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Admin Controller
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

namespace HOA_Studio\Admin;

use HOA_Studio\Core\HOA_Settings;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Admin
{
    private static ?HOA_Admin $instance = null;

    public static function instance(): HOA_Admin
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    public function register_hooks(): void
    {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'handle_save_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('plugin_action_links_'.HOA_STUDIO_BASENAME, [$this, 'add_action_links']);
    }

    public function register_admin_menu(): void
    {
        $iconSvg = 'data:image/svg+xml;base64,'.base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>'
        );

        // Top-level Menu
        add_menu_page(
            __('HOA Studio AI', 'hoa-studio'),
            __('HOA Studio', 'hoa-studio'),
            'manage_options',
            'hoa-studio',
            [$this, 'render_dashboard_page'],
            $iconSvg,
            26
        );

        // Submenus
        add_submenu_page(
            'hoa-studio',
            __('Dashboard & Quota', 'hoa-studio'),
            __('Dashboard', 'hoa-studio'),
            'manage_options',
            'hoa-studio',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'hoa-studio',
            __('Connection & Handshake', 'hoa-studio'),
            __('Connection', 'hoa-studio'),
            'manage_options',
            'hoa-studio-connection',
            [$this, 'render_connection_page']
        );

        add_submenu_page(
            'hoa-studio',
            __('AI & Brand Voice', 'hoa-studio'),
            __('AI & Models', 'hoa-studio'),
            'manage_options',
            'hoa-studio-ai',
            [$this, 'render_ai_settings_page']
        );

        add_submenu_page(
            'hoa-studio',
            __('Editor & Post Types', 'hoa-studio'),
            __('Editor Settings', 'hoa-studio'),
            'manage_options',
            'hoa-studio-editor-settings',
            [$this, 'render_editor_settings_page']
        );
    }

    public function enqueue_admin_assets(string $hook): void
    {
        if (! str_contains($hook, 'hoa-studio')) {
            return;
        }

        wp_enqueue_style(
            'hoa-studio-admin-css',
            HOA_STUDIO_URL.'assets/css/hoa-studio.css',
            [],
            HOA_STUDIO_VERSION
        );

        wp_enqueue_script(
            'hoa-studio-admin-js',
            HOA_STUDIO_URL.'assets/js/hoa-admin.js',
            ['jquery'],
            HOA_STUDIO_VERSION,
            true
        );

        wp_localize_script('hoa-studio-admin-js', 'hoaAdminConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hoa_studio_editor_nonce'),
            'endpoint' => HOA_Settings::getEndpoint(),
            'isConnected' => HOA_Settings::isConnected(),
            'i18n' => [
                'testing' => __('Testing connection...', 'hoa-studio'),
                'connected' => __('Connected successfully!', 'hoa-studio'),
                'failed' => __('Connection failed.', 'hoa-studio'),
                'copied' => __('Copied to clipboard!', 'hoa-studio'),
            ],
        ]);
    }

    public function add_action_links(array $links): array
    {
        $customLinks = [
            '<a href="'.admin_url('admin.php?page=hoa-studio-connection').'">'.__('Settings', 'hoa-studio').'</a>',
            '<a href="'.admin_url('admin.php?page=hoa-studio').'">'.__('Dashboard', 'hoa-studio').'</a>',
        ];

        return array_merge($customLinks, $links);
    }

    public function handle_save_settings(): void
    {
        if (! isset($_POST['hoa_save_settings_nonce']) || ! wp_verify_nonce($_POST['hoa_save_settings_nonce'], 'hoa_save_settings')) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        $tab = sanitize_key($_POST['hoa_settings_tab'] ?? '');

        if ($tab === 'connection') {
            $endpoint = rtrim(sanitize_text_field(wp_unslash($_POST['hoa_studio_endpoint'] ?? '')), '/');
            $apiKey = sanitize_text_field(wp_unslash($_POST['hoa_studio_api_key'] ?? ''));

            update_option(HOA_Settings::OPTION_ENDPOINT, $endpoint);
            update_option(HOA_Settings::OPTION_API_KEY, $apiKey);

            // Re-verify immediately if credentials provided
            if (! empty($endpoint) && ! empty($apiKey)) {
                $connectUrl = $endpoint.'/api/v1/wordpress/connect';
                $resp = wp_remote_post($connectUrl, [
                    'timeout' => 15,
                    'headers' => [
                        'Authorization' => 'Bearer '.$apiKey,
                        'Accept' => 'application/json',
                    ],
                ]);

                if (! is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
                    $data = json_decode(wp_remote_retrieve_body($resp), true);
                    if (! empty($data['success'])) {
                        update_option(HOA_Settings::OPTION_STATUS, 'connected');
                        if (! empty($data['user'])) {
                            update_option(HOA_Settings::OPTION_USER_DATA, $data['user']);
                        }
                        if (! empty($data['available_models'])) {
                            update_option(HOA_Settings::OPTION_MODELS, $data['available_models']);
                        }
                        if (! empty($data['brand_voices'])) {
                            update_option(HOA_Settings::OPTION_BRAND_VOICES, $data['brand_voices']);
                        }
                        add_settings_error('hoa_messages', 'hoa_connected', __('Settings saved and connection verified successfully!', 'hoa-studio'), 'success');

                        return;
                    }
                }
            }

            add_settings_error('hoa_messages', 'hoa_saved', __('Settings saved.', 'hoa-studio'), 'success');
        } elseif ($tab === 'ai') {
            $defaultModel = sanitize_text_field(wp_unslash($_POST['hoa_studio_default_model'] ?? 'auto'));
            $defaultTone = sanitize_text_field(wp_unslash($_POST['hoa_studio_default_tone'] ?? 'Professional'));

            update_option(HOA_Settings::OPTION_DEFAULT_MODEL, $defaultModel);
            update_option(HOA_Settings::OPTION_DEFAULT_TONE, $defaultTone);

            add_settings_error('hoa_messages', 'hoa_saved', __('AI settings updated successfully.', 'hoa-studio'), 'success');
        } elseif ($tab === 'editor') {
            $postTypes = isset($_POST['hoa_studio_enabled_post_types']) && is_array($_POST['hoa_studio_enabled_post_types'])
                ? array_map('sanitize_key', $_POST['hoa_studio_enabled_post_types'])
                : ['post', 'page'];
            $autoSync = isset($_POST['hoa_studio_auto_sync']) ? 'yes' : 'no';

            update_option(HOA_Settings::OPTION_POST_TYPES, $postTypes);
            update_option(HOA_Settings::OPTION_AUTO_SYNC, $autoSync);

            add_settings_error('hoa_messages', 'hoa_saved', __('Editor configuration updated successfully.', 'hoa-studio'), 'success');
        }
    }

    public function render_dashboard_page(): void
    {
        require_once HOA_STUDIO_DIR.'views/admin-dashboard.php';
    }

    public function render_connection_page(): void
    {
        require_once HOA_STUDIO_DIR.'views/admin-connection.php';
    }

    public function render_ai_settings_page(): void
    {
        require_once HOA_STUDIO_DIR.'views/admin-ai-settings.php';
    }

    public function render_editor_settings_page(): void
    {
        require_once HOA_STUDIO_DIR.'views/admin-editor-settings.php';
    }
}

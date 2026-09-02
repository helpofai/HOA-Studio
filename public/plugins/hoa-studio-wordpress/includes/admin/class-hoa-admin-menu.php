<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Admin Menus
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
|--------------------------------------------------------------------------
*/

if (!defined('ABSPATH')) {
    exit;
}

class HoaAdminMenu {
    public function init() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function register_admin_menu() {
        add_menu_page(
            __('HOA Studio', 'hoa-studio'),
            __('HOA Studio', 'hoa-studio'),
            'manage_options',
            'hoa-studio-dashboard',
            [$this, 'render_dashboard_page'],
            'dashicons-superhero',
            30
        );

        add_submenu_page(
            'hoa-studio-dashboard',
            __('Dashboard', 'hoa-studio'),
            __('Dashboard', 'hoa-studio'),
            'manage_options',
            'hoa-studio-dashboard',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'hoa-studio-dashboard',
            __('Connection', 'hoa-studio'),
            __('Connection', 'hoa-studio'),
            'manage_options',
            'hoa-studio-connection',
            [$this, 'render_connection_page']
        );

        add_submenu_page(
            'hoa-studio-dashboard',
            __('AI Settings', 'hoa-studio'),
            __('AI Settings', 'hoa-studio'),
            'manage_options',
            'hoa-studio-ai-settings',
            [$this, 'render_ai_settings_page']
        );

        add_submenu_page(
            'hoa-studio-dashboard',
            __('Editor Control', 'hoa-studio'),
            __('Editor Control', 'hoa-studio'),
            'manage_options',
            'hoa-studio-editor-control',
            [$this, 'render_editor_control_page']
        );
    }

    public function register_settings() {
        register_setting('hoa_studio_connection_options', 'hoa_studio_endpoint_url', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://studio.helpofai.com',
        ]);

        register_setting('hoa_studio_connection_options', 'hoa_studio_connect_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('hoa_studio_editor_options', 'hoa_studio_default_editor', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'tiptap', 
        ]);

        register_setting('hoa_studio_ai_options', 'hoa_studio_default_model', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'auto',
        ]);

        register_setting('hoa_studio_ai_options', 'hoa_studio_brand_voice', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => '',
        ]);
    }

    public function render_dashboard_page() {
        $endpoint = get_option('hoa_studio_endpoint_url', 'http://127.0.0.1:8000');
        include HOA_STUDIO_PLUGIN_DIR . 'views/dashboard.php';
    }

    public function render_connection_page() {
        $endpoint = get_option('hoa_studio_endpoint_url', 'http://127.0.0.1:8000');
        $key = get_option('hoa_studio_connect_key', '');
        include HOA_STUDIO_PLUGIN_DIR . 'views/connection.php';
    }

    public function render_ai_settings_page() {
        $default_model = get_option('hoa_studio_default_model', 'auto');
        $brand_voice = get_option('hoa_studio_brand_voice', '');
        include HOA_STUDIO_PLUGIN_DIR . 'views/ai-settings.php';
    }

    public function render_editor_control_page() {
        $default_editor = get_option('hoa_studio_default_editor', 'tiptap');
        include HOA_STUDIO_PLUGIN_DIR . 'views/editor-control.php';
    }

    public function enqueue_admin_scripts($hook) {
        $allowed_pages = [
            'post.php', 
            'post-new.php', 
            'toplevel_page_hoa-studio-dashboard',
            'hoa-studio_page_hoa-studio-connection',
            'hoa-studio_page_hoa-studio-ai-settings',
            'hoa-studio_page_hoa-studio-editor-control'
        ];
        
        if (!in_array($hook, $allowed_pages)) {
            return;
        }

        wp_enqueue_style(
            'hoa-studio-admin-style',
            HOA_STUDIO_PLUGIN_URL . 'assets/css/hoa-studio.css',
            [],
            HOA_STUDIO_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'hoa-studio-tiptap-bundle',
            HOA_STUDIO_PLUGIN_URL . 'assets/js/hoa-tiptap-bundle.js',
            ['jquery'],
            HOA_STUDIO_PLUGIN_VERSION,
            true
        );

        wp_localize_script('hoa-studio-tiptap-bundle', 'hoaStudioConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hoa_studio_nonce'),
            'endpoint' => get_option('hoa_studio_endpoint_url', 'http://127.0.0.1:8000'),
            'isConnected' => !empty(get_option('hoa_studio_connect_key')),
            'defaultEditor' => get_option('hoa_studio_default_editor', 'tiptap'),
        ]);
    }
}

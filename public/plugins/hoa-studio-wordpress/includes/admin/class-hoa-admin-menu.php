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
            __('✨ Studio Editor', 'hoa-studio'),
            __('✨ Add New Post', 'hoa-studio'),
            'edit_posts',
            'hoa-studio-post-editor',
            [$this, 'render_studio_post_editor_page']
        );

        add_submenu_page(
            'edit.php',
            __('✨ Add with HOA Studio', 'hoa-studio'),
            __('✨ Add with HOA Studio', 'hoa-studio'),
            'edit_posts',
            'hoa-studio-post-editor',
            [$this, 'render_studio_post_editor_page']
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
        $endpoint = get_option('hoa_studio_endpoint_url', 'https://studio.helpofai.com');
        $has_key = !empty(get_option('hoa_studio_connect_key', ''));
        $default_model = get_option('hoa_studio_default_model', 'auto');
        $default_editor = get_option('hoa_studio_default_editor', 'tiptap');
        include HOA_STUDIO_PLUGIN_DIR . 'views/dashboard.php';
    }

    public function render_connection_page() {
        $endpoint = get_option('hoa_studio_endpoint_url', 'https://studio.helpofai.com');
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

    public function render_studio_post_editor_page() {
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $post = $post_id > 0 ? get_post($post_id) : null;
        $title = $post ? $post->post_title : (isset($_GET['title']) ? sanitize_text_field(wp_unslash($_GET['title'])) : '');
        $content = $post ? $post->post_content : '';
        $post_status = $post ? $post->post_status : 'draft';
        $target_keyword = $post ? get_post_meta($post_id, '_hoa_target_keyword', true) : '';
        $meta_description = $post ? get_post_meta($post_id, '_hoa_meta_description', true) : '';
        $categories = get_categories(['hide_empty' => false]);
        $selected_categories = $post ? wp_get_post_categories($post_id) : [];
        $post_tags = $post ? implode(', ', wp_get_post_tags($post_id, ['fields' => 'names'])) : '';
        $featured_image_id = $post ? get_post_thumbnail_id($post_id) : 0;
        $featured_image_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '';
        $permalink = $post ? get_permalink($post_id) : '';
        $slug = $post ? $post->post_name : '';

        include HOA_STUDIO_PLUGIN_DIR . 'views/studio-post-editor.php';
    }

    public function enqueue_admin_scripts($hook) {
        $allowed_pages = [
            'post.php', 
            'post-new.php', 
            'toplevel_page_hoa-studio-dashboard',
            'hoa-studio_page_hoa-studio-connection',
            'hoa-studio_page_hoa-studio-ai-settings',
            'hoa-studio_page_hoa-studio-editor-control',
            'hoa-studio_page_hoa-studio-post-editor',
            'posts_page_hoa-studio-post-editor',
        ];
        
        $is_studio_page = strpos($hook, 'hoa-studio-post-editor') !== false || in_array($hook, $allowed_pages);

        if (!$is_studio_page) {
            return;
        }

        if (in_array($hook, ['post.php', 'post-new.php']) || strpos($hook, 'hoa-studio-post-editor') !== false) {
            wp_enqueue_media();
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
            'endpoint' => !empty(get_option('hoa_studio_endpoint_url')) ? rtrim(get_option('hoa_studio_endpoint_url'), '/') : 'https://studio.helpofai.com',
            'isConnected' => !empty(get_option('hoa_studio_connect_key')),
            'defaultEditor' => get_option('hoa_studio_default_editor', 'tiptap'),
        ]);
    }
}

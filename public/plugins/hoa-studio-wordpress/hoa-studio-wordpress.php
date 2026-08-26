<?php
/**
 * Plugin Name: HOA-Studio AI Editor & Content Suite
 * Plugin URI: https://helpofai.com
 * Description: Supercharge WordPress with HelpOfAi (HOA) Studio's TipTap editor, live streaming AI commands, content intelligence, and Gutenberg AI blocks.
 * Version: 2.6.0
 * Author: Rajib Adhikary / HelpOfAi (HOA)
 * Author URI: https://helpofai.com
 * License: GPLv2 or later
 * Text Domain: hoa-studio
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HOA_STUDIO_PLUGIN_VERSION', '2.6.0');
define('HOA_STUDIO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HOA_STUDIO_PLUGIN_URL', plugin_dir_url(__FILE__));

class HoaStudioWordPressPlugin {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('add_meta_boxes', [$this, 'register_editor_meta_box']);
        add_action('save_post', [$this, 'save_editor_post_content'], 10, 2);
        add_action('wp_ajax_hoa_studio_test_connection', [$this, 'ajax_test_connection']);
        add_action('wp_ajax_hoa_studio_stream_proxy', [$this, 'ajax_stream_proxy']);
        add_action('wp_ajax_hoa_studio_transform', [$this, 'ajax_transform_proxy']);
        add_action('init', [$this, 'register_gutenberg_blocks']);
    }

    public function register_admin_menu() {
        add_menu_page(
            __('HOA Studio AI', 'hoa-studio'),
            __('HOA Studio AI', 'hoa-studio'),
            'manage_options',
            'hoa-studio-settings',
            [$this, 'render_settings_page'],
            'dashicons-superhero',
            30
        );
    }

    public function register_settings() {
        register_setting('hoa_studio_options', 'hoa_studio_endpoint_url', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://studio.helpofai.com',
        ]);

        register_setting('hoa_studio_options', 'hoa_studio_connect_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        register_setting('hoa_studio_options', 'hoa_studio_default_editor', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'tiptap', // tiptap, gutenberg, hybrid
        ]);
    }

    public function render_settings_page() {
        $endpoint = get_option('hoa_studio_endpoint_url', 'http://127.0.0.1:8000');
        $key = get_option('hoa_studio_connect_key', '');
        $default_editor = get_option('hoa_studio_default_editor', 'tiptap');

        include HOA_STUDIO_PLUGIN_DIR . 'views/settings.php';
    }

    public function enqueue_admin_scripts($hook) {
        if (!in_array($hook, ['post.php', 'post-new.php', 'toplevel_page_hoa-studio-settings'])) {
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

    public function register_editor_meta_box() {
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            add_meta_box(
                'hoa_studio_tiptap_editor_box',
                __('✨ HOA Studio Enterprise TipTap AI Editor', 'hoa-studio'),
                [$this, 'render_editor_meta_box'],
                $screen,
                'normal',
                'high'
            );
        }
    }

    public function render_editor_meta_box($post) {
        wp_nonce_field('hoa_studio_save_post', 'hoa_studio_meta_nonce');
        $content = $post->post_content;
        include HOA_STUDIO_PLUGIN_DIR . 'views/editor-metabox.php';
    }

    public function save_editor_post_content($post_id, $post) {
        if (!isset($_POST['hoa_studio_meta_nonce']) || !wp_verify_nonce($_POST['hoa_studio_meta_nonce'], 'hoa_studio_save_post')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['hoa_tiptap_html_content'])) {
            $updated_content = wp_kses_post($_POST['hoa_tiptap_html_content']);
            
            // Remove hook temporarily to prevent recursion
            remove_action('save_post', [$this, 'save_editor_post_content'], 10);
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $updated_content,
            ]);
            add_action('save_post', [$this, 'save_editor_post_content'], 10, 2);
        }
    }

    public function ajax_test_connection() {
        check_ajax_referer('hoa_studio_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $endpoint = rtrim(sanitize_text_field($_POST['endpoint']), '/');
        $key = sanitize_text_field($_POST['key']);

        $response = wp_remote_post($endpoint . '/api/v1/wordpress/connect', [
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Origin' => get_site_url(),
                'Referer' => get_site_url(),
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Connection Failed: ' . $response->get_error_message()]);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($body['success']) && $body['success']) {
            wp_send_json_success($body);
        } else {
            $msg = isset($body['error']) ? $body['error'] : 'Authentication rejected (HTTP ' . $code . ')';
            wp_send_json_error(['message' => $msg]);
        }
    }

    public function ajax_stream_proxy() {
        check_ajax_referer('hoa_studio_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $endpoint = rtrim(get_option('hoa_studio_endpoint_url', 'http://127.0.0.1:8000'), '/');
        $key = get_option('hoa_studio_connect_key', '');

        if (empty($key)) {
            wp_send_json_error(['message' => 'HOA Studio Connect Key missing. Configure in HOA Studio Settings.']);
        }

        $payload = [
            'text' => wp_unslash($_POST['text'] ?? ''),
            'type' => sanitize_text_field($_POST['type'] ?? 'generate'),
            'model' => sanitize_text_field($_POST['model'] ?? ''),
            'custom_instruction' => wp_unslash($_POST['custom_instruction'] ?? ''),
        ];

        // Directly stream from backend to browser
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $ch = curl_init($endpoint . '/api/v1/wordpress/stream');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $key,
            'Content-Type: application/json',
            'Accept: text/event-stream',
        ]);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
            echo $data;
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
            return strlen($data);
        });
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    public function register_gutenberg_blocks() {
        if (!function_exists('register_block_type')) {
            return;
        }

        wp_register_script(
            'hoa-studio-gutenberg-blocks',
            HOA_STUDIO_PLUGIN_URL . 'assets/js/hoa-gutenberg-blocks.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            HOA_STUDIO_PLUGIN_VERSION
        );

        register_block_type('hoa-studio/ai-content-generator', [
            'editor_script' => 'hoa-studio-gutenberg-blocks',
        ]);
    }
}

function hoa_studio_init() {
    return HoaStudioWordPressPlugin::get_instance();
}
add_action('plugins_loaded', 'hoa_studio_init');

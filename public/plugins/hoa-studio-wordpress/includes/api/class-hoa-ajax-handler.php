<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress AJAX Handlers
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
|--------------------------------------------------------------------------
*/

if (!defined('ABSPATH')) {
    exit;
}

class HoaAjaxHandler {
    public function init() {
        add_action('wp_ajax_hoa_studio_test_connection', [$this, 'ajax_test_connection']);
        add_action('wp_ajax_hoa_studio_stream_proxy', [$this, 'ajax_stream_proxy']);
        add_action('wp_ajax_hoa_studio_save_full_post', [$this, 'ajax_save_full_post']);
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

        // Disable PHP output buffering entirely for zero-latency SSE token delivery
        @ob_implicit_flush(true);
        while (ob_get_level() > 0) { ob_end_flush(); }

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
            flush();
            return strlen($data);
        });
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    public function ajax_save_full_post() {
        check_ajax_referer('hoa_studio_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Unauthorized permissions to edit posts']);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = sanitize_text_field($_POST['title'] ?? 'Untitled Studio Post');
        $content = wp_kses_post($_POST['content'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'draft');
        $slug = sanitize_title($_POST['slug'] ?? '');
        $featured_image_id = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;
        $categories = isset($_POST['categories']) ? array_map('intval', (array) $_POST['categories']) : [];
        $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
        $target_keyword = isset($_POST['target_keyword']) ? sanitize_text_field($_POST['target_keyword']) : '';
        $meta_description = isset($_POST['meta_description']) ? sanitize_text_field($_POST['meta_description']) : '';

        $post_data = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => in_array($status, ['publish', 'draft', 'pending', 'private']) ? $status : 'draft',
            'post_type' => 'post',
        ];

        if (!empty($slug)) {
            $post_data['post_name'] = $slug;
        }

        if ($post_id > 0) {
            $post_data['ID'] = $post_id;
            $saved_id = wp_update_post($post_data, true);
        } else {
            $saved_id = wp_insert_post($post_data, true);
        }

        if (is_wp_error($saved_id) || $saved_id === 0) {
            wp_send_json_error(['message' => 'Failed to save post: ' . (is_wp_error($saved_id) ? $saved_id->get_error_message() : 'Unknown error')]);
        }

        // Categories & Tags
        if (!empty($categories)) {
            wp_set_post_categories($saved_id, $categories);
        }

        if (!empty($tags)) {
            wp_set_post_tags($saved_id, $tags);
        }

        // Featured Image
        if ($featured_image_id > 0) {
            set_post_thumbnail($saved_id, $featured_image_id);
        } elseif (isset($_POST['remove_featured_image']) && $_POST['remove_featured_image'] == '1') {
            delete_post_thumbnail($saved_id);
        }

        // SEO & Custom Meta synchronization
        if (!empty($target_keyword)) {
            update_post_meta($saved_id, '_hoa_target_keyword', $target_keyword);
            update_post_meta($saved_id, 'rank_math_focus_keyword', $target_keyword);
            update_post_meta($saved_id, '_yoast_wpseo_focuskw', $target_keyword);
        }

        if (!empty($meta_description)) {
            update_post_meta($saved_id, '_hoa_meta_description', $meta_description);
            update_post_meta($saved_id, 'rank_math_description', $meta_description);
            update_post_meta($saved_id, '_yoast_wpseo_metadesc', $meta_description);
        }

        // Word count & Reading time cache
        $plain = strip_tags($content);
        $word_count = str_word_count($plain);
        update_post_meta($saved_id, '_hoa_word_count', $word_count);

        wp_send_json_success([
            'post_id' => $saved_id,
            'status' => get_post_status($saved_id),
            'permalink' => get_permalink($saved_id),
            'edit_url' => admin_url('admin.php?page=hoa-studio-post-editor&post_id=' . $saved_id),
            'classic_edit_url' => get_edit_post_link($saved_id, 'raw'),
            'saved_at' => current_time('H:i:s'),
        ]);
    }
}

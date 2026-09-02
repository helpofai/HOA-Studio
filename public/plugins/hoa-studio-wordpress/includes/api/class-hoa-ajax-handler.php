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
}

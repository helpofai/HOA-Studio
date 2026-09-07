<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin AJAX Handler
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

namespace HOA_Studio\Api;

use HOA_Studio\Core\HOA_Settings;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Ajax_Handler
{
    private static ?HOA_Ajax_Handler $instance = null;

    public static function instance(): HOA_Ajax_Handler
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    public function register_hooks(): void
    {
        add_action('wp_ajax_hoa_studio_test_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_hoa_studio_stream_proxy', [$this, 'handle_stream_proxy']);
        add_action('wp_ajax_hoa_studio_transform_proxy', [$this, 'handle_transform_proxy']);
        add_action('wp_ajax_hoa_studio_save_full_post', [$this, 'handle_save_full_post']);
        add_action('wp_ajax_hoa_studio_sync_to_cloud', [$this, 'handle_sync_to_cloud']);
        add_action('wp_ajax_hoa_studio_generate_seo', [$this, 'handle_generate_seo']);
    }

    /**
     * Handshake Verification with HOA-Studio
     */
    public function handle_test_connection(): void
    {
        check_ajax_referer('hoa_studio_editor_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Unauthorized permission level.', 'hoa-studio')], 403);
        }

        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field(wp_unslash($_POST['endpoint'])) : HOA_Settings::getEndpoint();
        $key = isset($_POST['key']) && $_POST['key'] !== 'check' ? sanitize_text_field(wp_unslash($_POST['key'])) : HOA_Settings::getApiKey();

        $endpoint = rtrim($endpoint, '/');

        if (empty($endpoint) || empty($key)) {
            wp_send_json_error(['message' => __('Endpoint URL and Studio Connect Token cannot be empty.', 'hoa-studio')], 400);
        }

        $connectUrl = $endpoint.'/api/v1/wordpress/connect';

        $response = wp_remote_post($connectUrl, [
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'Bearer '.$key,
                'Accept' => 'application/json',
                'User-Agent' => 'HOA-Studio-WordPress/'.HOA_STUDIO_VERSION,
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error([
                'message' => sprintf(__('Connection failed: %s', 'hoa-studio'), $response->get_error_message()),
            ], 500);
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code !== 200 || ! is_array($data) || empty($data['success'])) {
            $msg = $data['message'] ?? $data['error'] ?? sprintf(__('Invalid response from HOA Studio (HTTP %d).', 'hoa-studio'), $code);
            wp_send_json_error(['message' => $msg], 400);
        }

        // Cache settings and discovered intelligence
        if (isset($_POST['key']) && $_POST['key'] !== 'check') {
            update_option(HOA_Settings::OPTION_ENDPOINT, $endpoint);
            update_option(HOA_Settings::OPTION_API_KEY, $key);
        }

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

        wp_send_json_success($data);
    }

    /**
     * Real-time Server-Sent Events (SSE) AI Streaming Proxy
     */
    public function handle_stream_proxy(): void
    {
        check_ajax_referer('hoa_studio_editor_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            status_header(403);
            echo 'data: '.json_encode(['error' => __('Unauthorized permission.', 'hoa-studio'), 'done' => true])."\n\n";
            exit;
        }

        $endpoint = HOA_Settings::getEndpoint();
        $apiKey = HOA_Settings::getApiKey();

        if (empty($endpoint) || empty($apiKey)) {
            status_header(400);
            echo 'data: '.json_encode(['error' => __('HOA Studio is not connected. Please connect in Settings.', 'hoa-studio'), 'done' => true])."\n\n";
            exit;
        }

        $text = isset($_POST['text']) ? wp_unslash($_POST['text']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'generate';
        $model = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : HOA_Settings::getDefaultModel();
        $customInstruction = isset($_POST['custom_instruction']) ? wp_unslash($_POST['custom_instruction']) : '';
        $brandVoiceId = isset($_POST['brand_voice_id']) ? intval($_POST['brand_voice_id']) : 0;

        $streamUrl = $endpoint.'/api/v1/wordpress/stream';

        // Prepare raw stream proxy headers
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');

        header('Content-Type: text/event-stream; charset=UTF-8');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();

        $postData = [
            'text' => $text,
            'type' => $type,
            'model' => $model,
            'custom_instruction' => $customInstruction,
        ];
        if ($brandVoiceId > 0) {
            $postData['brand_voice_id'] = $brandVoiceId;
        }

        // Perform streaming cURL call with continuous chunk flushing
        $ch = curl_init($streamUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$apiKey,
            'Accept: text/event-stream',
            'User-Agent: HOA-Studio-WordPress/'.HOA_STUDIO_VERSION,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            echo $data;
            if (ob_get_level() > 0) {
                @ob_flush();
            }
            flush();

            return strlen($data);
        });

        curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo 'data: '.json_encode(['error' => 'Gateway communication error: '.$curlError, 'done' => true])."\n\n";
            flush();
        }

        exit;
    }

    /**
     * Synchronous AI Transformation Proxy
     */
    public function handle_transform_proxy(): void
    {
        check_ajax_referer('hoa_studio_editor_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Unauthorized permission level.', 'hoa-studio')], 403);
        }

        $endpoint = HOA_Settings::getEndpoint();
        $apiKey = HOA_Settings::getApiKey();

        if (empty($endpoint) || empty($apiKey)) {
            wp_send_json_error(['message' => __('HOA Studio is not connected.', 'hoa-studio')], 400);
        }

        $text = isset($_POST['text']) ? wp_unslash($_POST['text']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : 'transform';
        $model = isset($_POST['model']) ? sanitize_text_field(wp_unslash($_POST['model'])) : HOA_Settings::getDefaultModel();
        $customInstruction = isset($_POST['custom_instruction']) ? wp_unslash($_POST['custom_instruction']) : '';

        $transformUrl = $endpoint.'/api/v1/wordpress/transform';

        $response = wp_remote_post($transformUrl, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'HOA-Studio-WordPress/'.HOA_STUDIO_VERSION,
            ],
            'body' => [
                'text' => $text,
                'type' => $type,
                'model' => $model,
                'custom_instruction' => $customInstruction,
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 500);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['success'])) {
            wp_send_json_error(['message' => $data['error'] ?? __('Transformation failed.', 'hoa-studio')], 400);
        }

        wp_send_json_success($data);
    }

    /**
     * Save/Publish Full Post from TipTap 3.30 Studio Editor
     */
    public function handle_save_full_post(): void
    {
        check_ajax_referer('hoa_studio_editor_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Unauthorized permission level.', 'hoa-studio')], 403);
        }

        $postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'draft';
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        $targetKeyword = isset($_POST['target_keyword']) ? sanitize_text_field(wp_unslash($_POST['target_keyword'])) : '';
        $metaDescription = isset($_POST['meta_description']) ? sanitize_text_field(wp_unslash($_POST['meta_description'])) : '';
        $featuredImageId = isset($_POST['featured_image_id']) ? intval($_POST['featured_image_id']) : 0;
        $categories = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
        $tags = isset($_POST['tags']) ? sanitize_text_field(wp_unslash($_POST['tags'])) : '';

        if (empty($title)) {
            $title = __('Untitled Post', 'hoa-studio');
        }

        $allowedStatuses = ['draft', 'publish', 'pending', 'future', 'private'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'draft';
        }

        $postData = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => 'post',
        ];

        if (! empty($slug)) {
            $postData['post_name'] = $slug;
        }

        if ($postId > 0) {
            $postData['ID'] = $postId;
            $updatedId = wp_update_post($postData, true);
        } else {
            $updatedId = wp_insert_post($postData, true);
        }

        if (is_wp_error($updatedId)) {
            wp_send_json_error(['message' => $updatedId->get_error_message()], 500);
        }

        $postId = (int) $updatedId;

        // Categories & Tags
        if (! empty($categories)) {
            wp_set_post_categories($postId, $categories);
        }
        if (! empty($tags)) {
            wp_set_post_tags($postId, $tags);
        }

        // Featured Image
        if ($featuredImageId > 0) {
            set_post_thumbnail($postId, $featuredImageId);
        }

        // Save SEO Meta
        update_post_meta($postId, '_hoa_target_keyword', $targetKeyword);
        update_post_meta($postId, '_hoa_meta_description', $metaDescription);
        update_post_meta($postId, '_hoa_editor_used', 'tiptap');
        update_post_meta($postId, '_hoa_last_saved', current_time('mysql'));

        // RankMath & Yoast SEO Interoperability
        if (! empty($targetKeyword)) {
            update_post_meta($postId, 'rank_math_focus_keyword', $targetKeyword);
            update_post_meta($postId, '_yoast_wpseo_focuskw', $targetKeyword);
        }
        if (! empty($metaDescription)) {
            update_post_meta($postId, 'rank_math_description', $metaDescription);
            update_post_meta($postId, '_yoast_wpseo_metadesc', $metaDescription);
        }

        $permalink = get_permalink($postId);
        $editUrl = admin_url('admin.php?page=hoa-studio-editor&post_id='.$postId);

        wp_send_json_success([
            'post_id' => $postId,
            'status' => $status,
            'permalink' => $permalink,
            'edit_url' => $editUrl,
            'saved_at' => current_time('g:i:s A'),
        ]);
    }

    /**
     * Sync Current Post Directly to HOA-Studio Cloud Documents
     */
    public function handle_sync_to_cloud(): void
    {
        check_ajax_referer('hoa_studio_editor_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Unauthorized permission level.', 'hoa-studio')], 403);
        }

        $endpoint = HOA_Settings::getEndpoint();
        $apiKey = HOA_Settings::getApiKey();

        if (empty($endpoint) || empty($apiKey)) {
            wp_send_json_error(['message' => __('HOA Studio is not connected.', 'hoa-studio')], 400);
        }

        $postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';

        if (empty($title)) {
            $title = __('Untitled Synced Document', 'hoa-studio');
        }

        $syncedDocId = $postId > 0 ? (int) get_post_meta($postId, '_hoa_synced_document_id', true) : null;

        $syncUrl = $endpoint.'/api/v1/wordpress/sync-document';

        $payload = [
            'title' => $title,
            'content_html' => $content,
            'wp_post_id' => $postId > 0 ? $postId : null,
            'document_id' => $syncedDocId > 0 ? $syncedDocId : null,
        ];

        $response = wp_remote_post($syncUrl, [
            'timeout' => 25,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'HOA-Studio-WordPress/'.HOA_STUDIO_VERSION,
            ],
            'body' => json_encode($payload),
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 500);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['success']) || empty($data['document'])) {
            wp_send_json_error(['message' => $data['error'] ?? __('Cloud document sync failed.', 'hoa-studio')], 400);
        }

        $cloudDocId = $data['document']['id'];
        if ($postId > 0 && $cloudDocId > 0) {
            update_post_meta($postId, '_hoa_synced_document_id', $cloudDocId);
            update_post_meta($postId, '_hoa_last_synced_at', current_time('mysql'));
        }

        wp_send_json_success([
            'document' => $data['document'],
            'synced_at' => current_time('g:i:s A'),
        ]);
    }

    /**
     * Generate SEO Metadata (Title, Description, Keyword) via AI
     */
    public function handle_generate_seo(): void
    {
        check_ajax_referer('hoa_studio_editor_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Unauthorized permission level.', 'hoa-studio')], 403);
        }

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        $content = isset($_POST['content']) ? wp_strip_all_tags(wp_unslash($_POST['content'])) : '';

        $prompt = "Analyze the following article title and excerpt, then generate a JSON object with:
1. 'focus_keyword': The single best primary SEO keyword (2-4 words).
2. 'meta_title': An engaging, high-CTR SEO title under 60 characters.
3. 'meta_description': A compelling meta description with call-to-action under 155 characters.
4. 'key_takeaways': An array of 3 bullet takeaways.

Title: {$title}
Excerpt: ".substr($content, 0, 1500).'

Return ONLY valid JSON.';

        $endpoint = HOA_Settings::getEndpoint();
        $apiKey = HOA_Settings::getApiKey();

        $transformUrl = $endpoint.'/api/v1/wordpress/transform';

        $response = wp_remote_post($transformUrl, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'body' => [
                'text' => $prompt,
                'type' => 'custom',
                'custom_instruction' => 'Generate strict JSON only for SEO meta data.',
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 500);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (! empty($data['result'])) {
            // Clean markdown code blocks if wrapped in ```json
            $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($data['result']));
            $parsed = json_decode($cleanJson, true);
            if (is_array($parsed)) {
                wp_send_json_success($parsed);
            }
        }

        wp_send_json_error(['message' => __('Could not parse SEO metadata response.', 'hoa-studio')], 400);
    }
}

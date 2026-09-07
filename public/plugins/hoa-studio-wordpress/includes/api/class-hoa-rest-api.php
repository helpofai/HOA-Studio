<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin REST API
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
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Rest_Api
{
    private static ?HOA_Rest_Api $instance = null;

    public static function instance(): HOA_Rest_Api
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    public function register_routes(): void
    {
        $namespace = 'hoa-studio/v1';

        // 1. Handshake & Health Route
        register_rest_route($namespace, '/handshake', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_handshake_status'],
            'permission_callback' => [$this, 'check_api_permission'],
        ]);

        // 2. Cloud Inbound Sync Route (Publish from HOA Studio to WordPress)
        register_rest_route($namespace, '/sync', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'handle_inbound_sync'],
            'permission_callback' => [$this, 'check_api_permission'],
        ]);

        // 3. Remote Posts Inventory Route
        register_rest_route($namespace, '/posts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_posts_inventory'],
            'permission_callback' => [$this, 'check_api_permission'],
        ]);
    }

    /**
     * Check Bearer Token Authorization against configured Studio API Key or Webhook Secret
     */
    public function check_api_permission(WP_REST_Request $request): bool
    {
        $authHeader = $request->get_header('Authorization');
        if (empty($authHeader) || ! str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $token = trim(substr($authHeader, 7));
        $validKey = HOA_Settings::getApiKey();
        $webhookSecret = HOA_Settings::getWebhookSecret();

        if (empty($token)) {
            return false;
        }

        return hash_equals($validKey, $token) || hash_equals($webhookSecret, $token);
    }

    /**
     * Return Handshake & Node Status
     */
    public function get_handshake_status(): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => true,
            'plugin_version' => HOA_STUDIO_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'site_name' => get_bloginfo('name'),
            'site_url' => get_site_url(),
            'status' => HOA_Settings::isConnected() ? 'connected' : 'disconnected',
            'user' => HOA_Settings::getUserData(),
            'default_model' => HOA_Settings::getDefaultModel(),
            'default_tone' => HOA_Settings::getDefaultTone(),
            'enabled_post_types' => HOA_Settings::getEnabledPostTypes(),
        ], 200);
    }

    /**
     * Inbound Sync: Create or update draft from HOA-Studio
     */
    public function handle_inbound_sync(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();

        $title = sanitize_text_field($params['title'] ?? __('Imported Document from HOA Studio', 'hoa-studio'));
        $content = wp_kses_post($params['content_html'] ?? '');
        $status = sanitize_key($params['status'] ?? 'draft');
        $documentId = isset($params['document_id']) ? (int) $params['document_id'] : 0;
        $wpPostId = isset($params['wp_post_id']) ? (int) $params['wp_post_id'] : 0;

        $postData = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => in_array($status, ['draft', 'publish', 'pending'], true) ? $status : 'draft',
            'post_type' => 'post',
        ];

        if ($wpPostId > 0 && get_post($wpPostId)) {
            $postData['ID'] = $wpPostId;
            $savedId = wp_update_post($postData, true);
        } else {
            $savedId = wp_insert_post($postData, true);
        }

        if (is_wp_error($savedId)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $savedId->get_error_message(),
            ], 500);
        }

        $postId = (int) $savedId;

        if ($documentId > 0) {
            update_post_meta($postId, '_hoa_synced_document_id', $documentId);
        }
        update_post_meta($postId, '_hoa_editor_used', 'tiptap');
        update_post_meta($postId, '_hoa_last_synced_at', current_time('mysql'));

        return new WP_REST_Response([
            'success' => true,
            'post_id' => $postId,
            'permalink' => get_permalink($postId),
            'edit_url' => admin_url('admin.php?page=hoa-studio-editor&post_id='.$postId),
            'status' => get_post_status($postId),
        ], 200);
    }

    /**
     * Posts Inventory Listing
     */
    public function get_posts_inventory(WP_REST_Request $request): WP_REST_Response
    {
        $limit = min(50, max(5, (int) ($request->get_param('limit') ?? 20)));
        $posts = get_posts([
            'numberposts' => $limit,
            'post_status' => ['publish', 'draft', 'pending'],
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);

        $list = [];
        foreach ($posts as $p) {
            $list[] = [
                'id' => $p->ID,
                'title' => $p->post_title,
                'status' => $p->post_status,
                'modified' => $p->post_modified,
                'permalink' => get_permalink($p->ID),
                'synced_doc_id' => get_post_meta($p->ID, '_hoa_synced_document_id', true) ?: null,
            ];
        }

        return new WP_REST_Response([
            'success' => true,
            'posts' => $list,
        ], 200);
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Cloud Sync Engine
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

namespace HOA_Studio\Sync;

use HOA_Studio\Core\HOA_Settings;
use WP_Post;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Cloud_Sync
{
    private static ?HOA_Cloud_Sync $instance = null;

    public static function instance(): HOA_Cloud_Sync
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    public function register_hooks(): void
    {
        if (HOA_Settings::isAutoSyncEnabled()) {
            add_action('transition_post_status', [$this, 'handle_post_transition'], 10, 3);
        }
    }

    public function handle_post_transition(string $newStatus, string $oldStatus, WP_Post $post): void
    {
        if (! HOA_Settings::isPostTypeEnabled($post->post_type)) {
            return;
        }

        if (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID)) {
            return;
        }

        if ($newStatus === 'publish' && $oldStatus !== 'publish') {
            $this->sync_post_to_cloud($post);
        }
    }

    public function sync_post_to_cloud(WP_Post $post): bool
    {
        $endpoint = HOA_Settings::getEndpoint();
        $apiKey = HOA_Settings::getApiKey();

        if (empty($endpoint) || empty($apiKey)) {
            return false;
        }

        $syncedDocId = (int) get_post_meta($post->ID, '_hoa_synced_document_id', true);

        $payload = [
            'title' => $post->post_title,
            'content_html' => $post->post_content,
            'wp_post_id' => $post->ID,
            'document_id' => $syncedDocId > 0 ? $syncedDocId : null,
        ];

        $response = wp_remote_post($endpoint.'/api/v1/wordpress/sync-document', [
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'HOA-Studio-WordPress/'.HOA_STUDIO_VERSION,
            ],
            'body' => json_encode($payload),
        ]);

        if (! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (! empty($data['document']['id'])) {
                update_post_meta($post->ID, '_hoa_synced_document_id', $data['document']['id']);
                update_post_meta($post->ID, '_hoa_last_synced_at', current_time('mysql'));

                return true;
            }
        }

        return false;
    }
}

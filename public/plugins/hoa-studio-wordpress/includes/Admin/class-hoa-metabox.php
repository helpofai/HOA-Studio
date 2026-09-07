<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Editor Metabox
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
use WP_Post;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Metabox
{
    private static ?HOA_Metabox $instance = null;

    public static function instance(): HOA_Metabox
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    public function register_hooks(): void
    {
        add_action('add_meta_boxes', [$this, 'add_editor_metabox']);
        add_action('save_post', [$this, 'save_post_meta'], 10, 2);
    }

    public function add_editor_metabox(): void
    {
        $enabledTypes = HOA_Settings::getEnabledPostTypes();

        foreach ($enabledTypes as $postType) {
            add_meta_box(
                'hoa_studio_editor_metabox',
                __('⚡ HOA Studio AI & TipTap Workspace', 'hoa-studio'),
                [$this, 'render_metabox'],
                $postType,
                'side',
                'high'
            );
        }
    }

    public function render_metabox(WP_Post $post): void
    {
        wp_nonce_field('hoa_save_metabox_nonce', 'hoa_metabox_nonce');

        $editorUrl = admin_url('admin.php?page=hoa-studio-editor&post_id='.$post->ID);
        $isConnected = HOA_Settings::isConnected();
        $targetKeyword = get_post_meta($post->ID, '_hoa_target_keyword', true);
        $metaDesc = get_post_meta($post->ID, '_hoa_meta_description', true);
        $syncedDocId = get_post_meta($post->ID, '_hoa_synced_document_id', true);
        $lastSyncedAt = get_post_meta($post->ID, '_hoa_last_synced_at', true);
        $userData = HOA_Settings::getUserData();

        require HOA_STUDIO_DIR.'views/metabox-post-sidebar.php';
    }

    public function save_post_meta(int $postId, WP_Post $post): void
    {
        if (! isset($_POST['hoa_metabox_nonce']) || ! wp_verify_nonce($_POST['hoa_metabox_nonce'], 'hoa_save_metabox_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        if (isset($_POST['hoa_target_keyword'])) {
            update_post_meta($postId, '_hoa_target_keyword', sanitize_text_field(wp_unslash($_POST['hoa_target_keyword'])));
        }

        if (isset($_POST['hoa_meta_description'])) {
            update_post_meta($postId, '_hoa_meta_description', sanitize_text_field(wp_unslash($_POST['hoa_meta_description'])));
        }
    }
}

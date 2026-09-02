<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Editor Metabox
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
|--------------------------------------------------------------------------
*/

if (!defined('ABSPATH')) {
    exit;
}

class HoaEditorMetabox {
    public function init() {
        add_action('add_meta_boxes', [$this, 'register_editor_meta_box']);
        add_action('save_post', [$this, 'save_editor_post_content'], 10, 2);
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
}

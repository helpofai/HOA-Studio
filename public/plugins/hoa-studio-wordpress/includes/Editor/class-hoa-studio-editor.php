<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Studio Editor
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

namespace HOA_Studio\Editor;

use HOA_Studio\Core\HOA_Settings;

if (!defined('ABSPATH')) {
    exit;
}

class HOA_Studio_Editor
{
    private static ?HOA_Studio_Editor $instance = null;

    public static function instance(): HOA_Studio_Editor
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function register_hooks(): void
    {
        add_action('admin_menu', [$this, 'register_hidden_editor_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
    }

    public function register_hidden_editor_page(): void
    {
        add_submenu_page(
            null, // Hidden from wp-admin sidebar menu
            __('HOA Studio Master Editor', 'hoa-studio'),
            __('HOA Studio Editor', 'hoa-studio'),
            'edit_posts',
            'hoa-studio-editor',
            [$this, 'render_editor_page']
        );
    }

    public function enqueue_editor_assets(string $hook): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'hoa-studio-editor') {
            return;
        }

        // Enable WordPress Media Library popup
        wp_enqueue_media();

        // Enqueue Editor Stylesheets
        wp_enqueue_style(
            'hoa-studio-css',
            HOA_STUDIO_URL . 'assets/css/hoa-studio.css',
            [],
            HOA_STUDIO_VERSION
        );

        wp_enqueue_style(
            'hoa-editor-css',
            HOA_STUDIO_URL . 'assets/css/hoa-editor.css',
            ['hoa-studio-css'],
            HOA_STUDIO_VERSION
        );

        // Enqueue Compiled TipTap Suite Bundle
        wp_enqueue_script(
            'hoa-tiptap-bundle',
            HOA_STUDIO_URL . 'assets/js/hoa-tiptap-bundle.js',
            ['jquery'],
            HOA_STUDIO_VERSION,
            true
        );

        $postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $post = $postId > 0 ? get_post($postId) : null;

        $categories = [];
        if ($post) {
            $catObjects = get_the_category($post->ID);
            foreach ($catObjects as $cat) {
                $categories[] = $cat->term_id;
            }
        }

        $allCategories = get_categories(['hide_empty' => false]);
        $formattedCats = [];
        foreach ($allCategories as $c) {
            $formattedCats[] = [
                'id' => $c->term_id,
                'name' => $c->name,
            ];
        }

        $tags = '';
        if ($post) {
            $tagList = wp_get_post_tags($post->ID, ['fields' => 'names']);
            if (!empty($tagList) && is_array($tagList)) {
                $tags = implode(', ', $tagList);
            }
        }

        $featuredImageUrl = '';
        $featuredImageId = 0;
        if ($post && has_post_thumbnail($post->ID)) {
            $featuredImageId = (int) get_post_thumbnail_id($post->ID);
            $imgSrc = wp_get_attachment_image_src($featuredImageId, 'large');
            if ($imgSrc) {
                $featuredImageUrl = $imgSrc[0];
            }
        }

        $config = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hoa_studio_editor_nonce'),
            'endpoint' => HOA_Settings::getEndpoint(),
            'token' => HOA_Settings::getApiKey(),
            'isConnected' => HOA_Settings::isConnected(),
            'postId' => $post ? $post->ID : 0,
            'postTitle' => $post ? $post->post_title : '',
            'postContent' => $post ? $post->post_content : '',
            'postStatus' => $post ? $post->post_status : 'draft',
            'postSlug' => $post ? $post->post_name : '',
            'targetKeyword' => $post ? (string) get_post_meta($post->ID, '_hoa_target_keyword', true) : '',
            'metaDescription' => $post ? (string) get_post_meta($post->ID, '_hoa_meta_description', true) : '',
            'featuredImageId' => $featuredImageId,
            'featuredImageUrl' => $featuredImageUrl,
            'categories' => $categories,
            'allCategories' => $formattedCats,
            'tags' => $tags,
            'autoSync' => HOA_Settings::isAutoSyncEnabled(),
            'defaultModel' => HOA_Settings::getDefaultModel(),
            'brandVoices' => HOA_Settings::getBrandVoices(),
            'availableModels' => HOA_Settings::getAvailableModels(),
            'cloudDocumentId' => $post ? (int) get_post_meta($post->ID, '_hoa_synced_document_id', true) : null,
            'i18n' => [
                'saving' => __('Saving...', 'hoa-studio'),
                'saved' => __('All changes saved', 'hoa-studio'),
                'error' => __('Error saving post', 'hoa-studio'),
                'streaming' => __('AI Generating...', 'hoa-studio'),
                'syncing' => __('Syncing to HOA Cloud...', 'hoa-studio'),
                'synced' => __('Synced to HOA Cloud', 'hoa-studio'),
            ],
        ];

        wp_localize_script('hoa-tiptap-bundle', 'hoaStudioConfig', $config);
    }

    public function render_editor_page(): void
    {
        $postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        $post = $postId > 0 ? get_post($postId) : null;

        require_once HOA_STUDIO_DIR . 'views/studio-canvas.php';
    }
}

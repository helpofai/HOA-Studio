<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Gutenberg Integration
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

namespace HOA_Studio\Gutenberg;

use HOA_Studio\Core\HOA_Settings;

if (!defined('ABSPATH')) {
    exit;
}

class HOA_Gutenberg_Blocks
{
    private static ?HOA_Gutenberg_Blocks $instance = null;

    public static function instance(): HOA_Gutenberg_Blocks
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
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_assets']);
        add_filter('block_categories_all', [$this, 'register_block_category'], 10, 2);
    }

    public function register_block_category(array $categories, $post): array
    {
        return array_merge($categories, [
            [
                'slug'  => 'hoa-studio',
                'title' => __('HOA Studio AI & Editorial', 'hoa-studio'),
                'icon'  => 'star-filled',
            ],
        ]);
    }

    public function enqueue_block_assets(): void
    {
        wp_enqueue_script(
            'hoa-gutenberg-js',
            HOA_STUDIO_URL . 'assets/js/hoa-gutenberg.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose'],
            HOA_STUDIO_VERSION,
            true
        );

        wp_enqueue_style(
            'hoa-studio-css',
            HOA_STUDIO_URL . 'assets/css/hoa-studio.css',
            [],
            HOA_STUDIO_VERSION
        );

        wp_localize_script('hoa-gutenberg-js', 'hoaGutenbergConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hoa_studio_editor_nonce'),
            'endpoint' => HOA_Settings::getEndpoint(),
            'isConnected' => HOA_Settings::isConnected(),
            'availableModels' => HOA_Settings::getAvailableModels(),
            'defaultModel' => HOA_Settings::getDefaultModel(),
            'i18n' => [
                'panelTitle' => __('⚡ HOA Studio AI Assistant', 'hoa-studio'),
                'generate' => __('Generate Content', 'hoa-studio'),
                'generating' => __('Generating with AI...', 'hoa-studio'),
                'insert' => __('Insert to Canvas', 'hoa-studio'),
            ],
        ]);
    }
}

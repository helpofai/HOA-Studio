<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Gutenberg Blocks
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
|--------------------------------------------------------------------------
*/

if (!defined('ABSPATH')) {
    exit;
}

class HoaGutenbergBlocks {
    public function init() {
        add_action('init', [$this, 'register_gutenberg_blocks']);
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

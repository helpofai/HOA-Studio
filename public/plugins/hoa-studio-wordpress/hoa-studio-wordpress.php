<?php
/**
 * Plugin Name: HOA-Studio AI Editor & Content Suite
 * Plugin URI: https://helpofai.com
 * Description: Supercharge WordPress with HelpOfAi (HOA) Studio's TipTap editor, live streaming AI commands, content intelligence, and Gutenberg AI blocks.
 * Version: 2.6.0
 * Author: Rajib Adhikary / HelpOfAi (HOA)
 * Author URI: https://helpofai.com
 * License: GPLv2 or later
 * Text Domain: hoa-studio
 *
 * |--------------------------------------------------------------------------
 * | HelpOfAi (HOA) Professional Software - WordPress Root Bootstrap
 * |--------------------------------------------------------------------------
 * |
 * | Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
 * |
 * | Author      : Rajib Adhikary
 * | Organization: HelpOfAi (HOA)
 * | Website     : https://helpofai.com
 * | Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
 * |
 * |--------------------------------------------------------------------------
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HOA_STUDIO_PLUGIN_VERSION', '2.6.0');
define('HOA_STUDIO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HOA_STUDIO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require the modular architecture pieces
require_once HOA_STUDIO_PLUGIN_DIR . 'includes/admin/class-hoa-admin-menu.php';
require_once HOA_STUDIO_PLUGIN_DIR . 'includes/admin/class-hoa-editor-metabox.php';
require_once HOA_STUDIO_PLUGIN_DIR . 'includes/api/class-hoa-ajax-handler.php';
require_once HOA_STUDIO_PLUGIN_DIR . 'includes/gutenberg/class-hoa-gutenberg.php';

class HoaStudioWordPressPlugin {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize Admin Menus & Settings
        (new HoaAdminMenu())->init();

        // Initialize Metaboxes
        (new HoaEditorMetabox())->init();

        // Initialize AJAX & Streaming
        (new HoaAjaxHandler())->init();

        // Initialize Gutenberg Integrations
        (new HoaGutenbergBlocks())->init();
    }
}

function hoa_studio_init() {
    return HoaStudioWordPressPlugin::get_instance();
}
add_action('plugins_loaded', 'hoa_studio_init');

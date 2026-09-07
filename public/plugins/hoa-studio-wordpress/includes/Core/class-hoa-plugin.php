<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Core Orchestrator
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

namespace HOA_Studio\Core;

use HOA_Studio\Admin\HOA_Admin;
use HOA_Studio\Admin\HOA_Metabox;
use HOA_Studio\Api\HOA_Ajax_Handler;
use HOA_Studio\Api\HOA_Rest_Api;
use HOA_Studio\Editor\HOA_Studio_Editor;
use HOA_Studio\Gutenberg\HOA_Gutenberg_Blocks;
use HOA_Studio\Sync\HOA_Cloud_Sync;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Plugin
{
    private static ?HOA_Plugin $instance = null;

    public static function instance(): HOA_Plugin
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {}

    public function init(): void
    {
        // 1. Text Domain
        load_plugin_textdomain('hoa-studio', false, dirname(HOA_STUDIO_BASENAME).'/languages');

        // 2. Initialize AJAX Endpoints (Both Admin & Public proxy)
        HOA_Ajax_Handler::instance()->register_hooks();

        // 3. Initialize REST API Routes
        add_action('rest_api_init', [HOA_Rest_Api::instance(), 'register_routes']);

        // 4. Initialize Admin Screens & Menus
        if (is_admin()) {
            HOA_Admin::instance()->register_hooks();
            HOA_Metabox::instance()->register_hooks();
            HOA_Studio_Editor::instance()->register_hooks();
            HOA_Cloud_Sync::instance()->register_hooks();
        }

        // 5. Initialize Gutenberg Block Support
        HOA_Gutenberg_Blocks::instance()->register_hooks();
    }
}

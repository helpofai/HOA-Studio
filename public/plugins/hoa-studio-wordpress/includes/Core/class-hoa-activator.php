<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Activator
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

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Activator
{
    /**
     * Executes activation sequence: default options, capabilities, and rewrite rules.
     */
    public static function activate(): void
    {
        // 1. Initialize Default Plugin Options
        $defaults = [
            'hoa_studio_endpoint' => get_option('hoa_studio_endpoint', ''),
            'hoa_studio_api_key' => get_option('hoa_studio_api_key', ''),
            'hoa_studio_status' => get_option('hoa_studio_status', 'disconnected'),
            'hoa_studio_default_model' => get_option('hoa_studio_default_model', 'auto'),
            'hoa_studio_default_tone' => get_option('hoa_studio_default_tone', 'Professional'),
            'hoa_studio_enabled_post_types' => get_option('hoa_studio_enabled_post_types', ['post', 'page']),
            'hoa_studio_auto_sync' => get_option('hoa_studio_auto_sync', 'no'),
            'hoa_studio_installed_version' => HOA_STUDIO_VERSION,
        ];

        foreach ($defaults as $key => $val) {
            if (get_option($key) === false) {
                update_option($key, $val);
            }
        }

        // 2. Grant capabilities to Administrator
        $adminRole = get_role('administrator');
        if ($adminRole) {
            $adminRole->add_cap('manage_hoa_studio');
            $adminRole->add_cap('use_hoa_studio_editor');
        }

        $editorRole = get_role('editor');
        if ($editorRole) {
            $editorRole->add_cap('use_hoa_studio_editor');
        }

        $authorRole = get_role('author');
        if ($authorRole) {
            $authorRole->add_cap('use_hoa_studio_editor');
        }

        // 3. Flush rewrite rules for REST endpoints
        flush_rewrite_rules();
    }
}

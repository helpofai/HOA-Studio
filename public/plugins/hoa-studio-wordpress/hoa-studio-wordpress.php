<?php

use HOA_Studio\Core\HOA_Activator;
use HOA_Studio\Core\HOA_Deactivator;
use HOA_Studio\Core\HOA_Plugin;

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Main Bootstrap
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| Plugin Name:       HOA-Studio AI Editor & Content Suite
| Plugin URI:        https://helpofai.com
| Description:       Enterprise-grade AI content creation & editorial workspace for WordPress. Powered by TipTap 3.30, OmniRoute AI, live SSE streaming, 2-way cloud sync, and deep SEO intelligence.
| Version:           2.6.0
| Requires at least: 6.0
| Requires PHP:      8.0
| Author:            Rajib Adhikary / HelpOfAi (HOA)
| Author URI:        https://helpofai.com
| License:           Proprietary
| Text Domain:       hoa-studio
| Domain Path:       /languages
|
|--------------------------------------------------------------------------
*/

if (! defined('ABSPATH')) {
    exit;
}

// Plugin Constants
define('HOA_STUDIO_VERSION', '2.6.0');
define('HOA_STUDIO_MIN_PHP', '8.0');
define('HOA_STUDIO_MIN_WP', '6.0');
define('HOA_STUDIO_FILE', __FILE__);
define('HOA_STUDIO_DIR', plugin_dir_path(__FILE__));
define('HOA_STUDIO_URL', plugin_dir_url(__FILE__));
define('HOA_STUDIO_BASENAME', plugin_basename(__FILE__));

/**
 * Autoloader for HOA-Studio Plugin Classes
 */
spl_autoload_register(function ($class) {
    $prefix = 'HOA_Studio\\';
    $baseDir = HOA_STUDIO_DIR.'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $subDir = ! empty($parts) ? implode('/', $parts).'/' : '';

    $fileName = 'class-'.strtolower(str_replace('_', '-', $className)).'.php';
    $filePath = $baseDir.$subDir.$fileName;

    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

/**
 * Check Environment Compatibility
 */
function hoa_studio_check_compatibility(): bool
{
    if (version_compare(PHP_VERSION, HOA_STUDIO_MIN_PHP, '<')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>'.
                sprintf(
                    esc_html__('HOA-Studio requires PHP version %s or higher. Your server is running PHP %s.', 'hoa-studio'),
                    HOA_STUDIO_MIN_PHP,
                    PHP_VERSION
                ).'</p></div>';
        });

        return false;
    }

    global $wp_version;
    if (version_compare($wp_version, HOA_STUDIO_MIN_WP, '<')) {
        add_action('admin_notices', function () use ($wp_version) {
            echo '<div class="notice notice-error"><p>'.
                sprintf(
                    esc_html__('HOA-Studio requires WordPress version %s or higher. Your site is running WordPress %s.', 'hoa-studio'),
                    HOA_STUDIO_MIN_WP,
                    $wp_version
                ).'</p></div>';
        });

        return false;
    }

    return true;
}

/**
 * Activation & Deactivation Hooks
 */
register_activation_hook(__FILE__, function () {
    if (class_exists('HOA_Studio\\Core\\HOA_Activator')) {
        HOA_Activator::activate();
    }
});

register_deactivation_hook(__FILE__, function () {
    if (class_exists('HOA_Studio\\Core\\HOA_Deactivator')) {
        HOA_Deactivator::deactivate();
    }
});

/**
 * Bootstrap Main Plugin Orchestrator
 */
add_action('plugins_loaded', function () {
    if (hoa_studio_check_compatibility()) {
        if (class_exists('HOA_Studio\\Core\\HOA_Plugin')) {
            HOA_Plugin::instance()->init();
        }
    }
});

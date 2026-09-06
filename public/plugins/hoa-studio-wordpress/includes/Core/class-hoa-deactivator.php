<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Deactivator
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

if (!defined('ABSPATH')) {
    exit;
}

class HOA_Deactivator
{
    /**
     * Executes deactivation sequence: cleans transients and rewrite rules.
     */
    public static function deactivate(): void
    {
        delete_transient('hoa_studio_cached_quota');
        delete_transient('hoa_studio_connection_cache');
        flush_rewrite_rules();
    }
}

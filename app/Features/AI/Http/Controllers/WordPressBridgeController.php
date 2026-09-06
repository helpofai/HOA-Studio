<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Bridge API Controller (Alias)
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

namespace App\Features\AI\Http\Controllers;

use App\Features\WordPress\Http\Controllers\WordPressBridgeController as BaseWordPressBridgeController;

/**
 * Backward-compatibility alias for WordPress Bridge Controller.
 * Core implementation now resides in App\Features\WordPress\Http\Controllers\WordPressBridgeController
 */
class WordPressBridgeController extends BaseWordPressBridgeController
{
}

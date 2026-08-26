<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// ─── SUBDIRECTORY ROUTING FIX ──────────────────────────────────────────────────
// When deployed in a subdirectory (e.g. public_html/studio/), Apache may pass
// REQUEST_URI as /studio/login instead of /login. Laravel's router then fails
// to match any routes (returns 404). We fix this by stripping the subfolder
// prefix from the server variables BEFORE Laravel captures the request.
//
// Works automatically — reads APP_URL from .env to detect the subfolder path.
// No manual configuration needed beyond setting APP_URL correctly.
// ──────────────────────────────────────────────────────────────────────────────
(function () {
    // Load APP_URL from .env without booting the full framework
    $envFile = __DIR__ . '/../.env';
    $appUrl  = '';
    if (is_readable($envFile)) {
        foreach (file($envFile) as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'APP_URL=')) {
                $appUrl = trim(substr($line, 8), " \t\n\r\0\x0B\"'");
                break;
            }
        }
    }

    // Derive the subfolder path from APP_URL (e.g. https://helpofai.com/studio → /studio)
    $subPath = rtrim(parse_url($appUrl, PHP_URL_PATH) ?? '', '/');

    // Only act when there is a non-root subfolder (e.g. /studio)
    if ($subPath !== '' && $subPath !== '/') {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $phpSelf    = $_SERVER['PHP_SELF'] ?? '';

        // Strip the subfolder prefix from REQUEST_URI if present
        if (str_starts_with($requestUri, $subPath . '/')) {
            $_SERVER['REQUEST_URI'] = substr($requestUri, strlen($subPath));
        } elseif ($requestUri === $subPath) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        // Also fix SCRIPT_NAME / PHP_SELF so Laravel generates correct URLs
        if (str_starts_with($scriptName, $subPath)) {
            $_SERVER['SCRIPT_NAME'] = substr($scriptName, strlen($subPath)) ?: '/index.php';
        }
        if (str_starts_with($phpSelf, $subPath)) {
            $_SERVER['PHP_SELF'] = substr($phpSelf, strlen($subPath)) ?: '/index.php';
        }
    }
})();
// ──────────────────────────────────────────────────────────────────────────────

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

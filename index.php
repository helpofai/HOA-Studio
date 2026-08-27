<?php
/**
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Advanced Smart Router & Controller
|--------------------------------------------------------------------------
|
| This file handles:
|   1. Subdirectory Routing (e.g. /studio)
|   2. Asset Serving from /public folder with correct MIME types
|   3. Laravel Bootstrapping (Integrated Front Controller)
|   4. HTTPS Enforcement for all generated URLs
|
| Copyright (c) 2026 HelpOfAi (HOA). All Rights Reserved.
|--------------------------------------------------------------------------
*/

declare(strict_types=1);

define('LARAVEL_START', microtime(true));

// ── 1. Environment & Subdirectory Detection ──────────────────────────────────
$subPath = '';
$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile) as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'APP_URL=')) {
            $rawUrl  = trim(substr($line, 8), " \t\n\r\0\x0B\"'");
            $subPath = rtrim((string)(parse_url($rawUrl, PHP_URL_PATH) ?? ''), '/');
            break;
        }
    }
}

// ── 2. Force HTTPS for Production and URL Generation ─────────────────────────
$_SERVER['HTTPS'] = 'on';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['HTTP_X_FORWARDED_SSL'] = 'on';

// ── 3. Path Normalization ───────────────────────────────────────────────────
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri        = strtok($requestUri, '?');
$query      = ltrim(substr($requestUri, strlen($uri)), '?');

if ($subPath !== '' && $subPath !== '/') {
    if (str_starts_with($uri, $subPath . '/')) {
        $uri = substr($uri, strlen($subPath));
        $_SERVER['REQUEST_URI'] = $uri . ($query !== '' ? '?' . $query : '');
    } elseif ($uri === $subPath || $uri === $subPath . '/') {
        $uri = '/';
        $_SERVER['REQUEST_URI'] = '/' . ($query !== '' ? '?' . $query : '');
    }

    foreach (['SCRIPT_NAME', 'PHP_SELF', 'ORIG_SCRIPT_NAME'] as $key) {
        if (isset($_SERVER[$key]) && str_starts_with($_SERVER[$key], $subPath)) {
            $_SERVER[$key] = substr($_SERVER[$key], strlen($subPath)) ?: '/index.php';
        }
    }
}

$uri = $uri ?: '/';

// ── 4. Asset Serving (Public Folder Bridge) ──────────────────────────────────
if ($uri !== '/') {
    $candidate = __DIR__ . '/public' . $uri;

    if (file_exists($candidate) && is_file($candidate)) {
        $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));

        // If it's a PHP file in public/ (like install.php), execute it
        if ($ext === 'php') {
            require $candidate;
            exit;
        }

        // Serve static assets with correct MIME types
        $mimeMap = [
            'css'   => 'text/css; charset=utf-8',
            'js'    => 'application/javascript; charset=utf-8',
            'mjs'   => 'application/javascript; charset=utf-8',
            'json'  => 'application/json; charset=utf-8',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'webp'  => 'image/webp',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'pdf'   => 'application/pdf',
            'txt'   => 'text/plain; charset=utf-8',
            'xml'   => 'application/xml',
        ];

        $mime = $mimeMap[$ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        
        if (ob_get_level()) ob_end_clean();
        readfile($candidate);
        exit;
    }
}

// ── 5. Laravel Bootstrapping (Integrated Front Controller) ──────────────────
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());

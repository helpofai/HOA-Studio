<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Subdirectory Smart Router
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
|
| This file lives at: public_html/helpofai.com/studio/index.php
|
| WHY THIS FILE EXISTS
| ─────────────────────
| The Laravel app is in studio/ but the web root must be studio/public/.
| On shared cPanel hosting we cannot change the web root, so this file
| acts as a one-stop bridge:
|
|   1. Reads APP_URL from .env  →  detects subdirectory prefix (/studio)
|   2. Strips the prefix from REQUEST_URI  →  Laravel sees /login not /studio/login
|   3. If the URI matches a real PHP file in public/  →  includes it directly
|      (handles: /studio/install.php, /studio/hoa-rescue.php, etc.)
|   4. If the URI matches a static asset in public/  →  serves it with caching
|      (handles: /studio/build/assets/app.css, images, fonts, etc.)
|   5. Everything else  →  hands off to public/index.php (Laravel)
|
| HOW IT IS INVOKED
| ──────────────────
| .htaccess (same directory) routes ALL non-file/non-dir requests here.
| The pattern is identical to Laravel's own public/.htaccess — proven,
| simple, and with zero infinite-loop risk.
|
|--------------------------------------------------------------------------
*/

declare(strict_types=1);

// ── 1. Detect subdirectory prefix from APP_URL in .env ──────────────────────
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

// ── 2. Strip subdirectory prefix from all $_SERVER path variables ────────────
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uri        = strtok($requestUri, '?');   // strip query string for file check
$query      = ltrim(substr($requestUri, strlen($uri)), '?');

if ($subPath !== '' && $subPath !== '/') {
    // REQUEST_URI:  /studio/login?foo=bar  →  /login?foo=bar
    if (str_starts_with($uri, $subPath . '/')) {
        $uri = substr($uri, strlen($subPath));
        $_SERVER['REQUEST_URI'] = $uri . ($query !== '' ? '?' . $query : '');
    } elseif ($uri === $subPath || $uri === $subPath . '/') {
        $uri = '/';
        $_SERVER['REQUEST_URI'] = '/' . ($query !== '' ? '?' . $query : '');
    }

    // SCRIPT_NAME / PHP_SELF: keep Laravel URL generation correct
    foreach (['SCRIPT_NAME', 'PHP_SELF', 'ORIG_SCRIPT_NAME'] as $key) {
        if (isset($_SERVER[$key]) && str_starts_with($_SERVER[$key], $subPath)) {
            $_SERVER[$key] = substr($_SERVER[$key], strlen($subPath)) ?: '/index.php';
        }
    }
}

$uri = $uri ?: '/';

// ── 3. Check for a real file inside public/ matching this URI ───────────────
if ($uri !== '/') {
    $candidate = __DIR__ . '/public' . $uri;

    if (file_exists($candidate) && is_file($candidate)) {
        $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));

        // PHP files: execute directly (install.php, hoa-rescue.php, etc.)
        if ($ext === 'php') {
            require $candidate;
            exit;
        }

        // Static assets: serve with proper caching headers
        $mimeMap = [
            'css'   => 'text/css; charset=utf-8',
            'js'    => 'application/javascript; charset=utf-8',
            'mjs'   => 'application/javascript; charset=utf-8',
            'json'  => 'application/json; charset=utf-8',
            'map'   => 'application/json; charset=utf-8',
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
            'eot'   => 'application/vnd.ms-fontobject',
            'pdf'   => 'application/pdf',
            'txt'   => 'text/plain; charset=utf-8',
            'xml'   => 'application/xml',
            'mp4'   => 'video/mp4',
            'webm'  => 'video/webm',
        ];

        $mime     = $mimeMap[$ext] ?? 'application/octet-stream';
        $etag     = '"' . sha1_file($candidate) . '"';
        $modified = (int) filemtime($candidate);
        $size     = (int) filesize($candidate);

        // 304 Not Modified
        $clientEtag    = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        $clientModSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        if ($clientEtag === $etag
            || ($clientModSince && strtotime($clientModSince) >= $modified)) {
            http_response_code(304);
            exit;
        }

        // Versioned assets (/build/) get 1-year immutable cache
        $isVersioned = str_contains($uri, '/build/');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . $size);
        header('ETag: ' . $etag);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT');
        header('Cache-Control: ' . ($isVersioned
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=3600'));
        header('X-Content-Type-Options: nosniff');

        // Disable output buffering for large files
        if (ob_get_level()) ob_end_clean();
        readfile($candidate);
        exit;
    }
}

// ── 4. Everything else → Laravel ────────────────────────────────────────────
require __DIR__ . '/public/index.php';

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - System Info & Documentation Service
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

namespace App\Features\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SystemInfoService
{
    /**
     * Get Server, PHP, Database, and Framework Environment Details.
     */
    public function getSystemDiagnostics(): array
    {
        // 1. PHP Requirements & Extensions Checklist
        $requiredExtensions = [
            'bcmath' => 'BCMath Arbitrary Precision Mathematics',
            'ctype' => 'Character type checking',
            'curl' => 'cURL HTTP client extension',
            'dom' => 'Document Object Model parser',
            'fileinfo' => 'File Information detection',
            'json' => 'JavaScript Object Notation support',
            'mbstring' => 'Multibyte String processing',
            'openssl' => 'OpenSSL Cryptography extension',
            'pcre' => 'Perl-Compatible Regular Expressions',
            'pdo' => 'PHP Data Objects abstraction',
            'tokenizer' => 'PHP Tokenizer engine',
            'xml' => 'XML Parser & generation',
            'zip' => 'ZipArchive compression & decompression',
        ];

        $extensionsStatus = [];
        $allExtensionsMet = true;
        foreach ($requiredExtensions as $ext => $description) {
            $isLoaded = extension_loaded($ext);
            if (!$isLoaded) {
                $allExtensionsMet = false;
            }
            $extensionsStatus[$ext] = [
                'name' => strtoupper($ext),
                'description' => $description,
                'loaded' => $isLoaded,
            ];
        }

        // 2. Database Connection & Engine Statistics
        $dbDriver = 'unknown';
        $dbVersion = 'unknown';
        $dbSizeFormatted = 'N/A';
        $dbTableCount = 0;
        try {
            $conn = DB::connection();
            $dbDriver = strtoupper($conn->getDriverName());

            if ($dbDriver === 'SQLITE') {
                $sqlitePath = config('database.connections.sqlite.database');
                if (file_exists($sqlitePath)) {
                    $dbSizeFormatted = round(filesize($sqlitePath) / (1024 * 1024), 2) . ' MB';
                }
                $dbTableCount = count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"));
                $dbVersion = 'SQLite ' . (DB::select('SELECT sqlite_version() as v')[0]->v ?? '3.x');
            } else {
                $dbVersionResult = DB::select('SELECT VERSION() as v');
                $dbVersion = 'MySQL ' . ($dbVersionResult[0]->v ?? '8.x');
                $tables = DB::select('SHOW TABLES');
                $dbTableCount = count($tables);

                $dbName = $conn->getDatabaseName();
                $sizeResult = DB::select("SELECT table_schema AS 'db', SUM(data_length + index_length) / 1024 / 1024 AS 'size' FROM information_schema.TABLES WHERE table_schema = ? GROUP BY table_schema", [$dbName]);
                if (!empty($sizeResult)) {
                    $dbSizeFormatted = round($sizeResult[0]->size ?? 0, 2) . ' MB';
                }
            }
        } catch (\Throwable $e) {}

        // 3. Storage Directory & Permission Diagnostics
        $storagePaths = [
            'storage/app' => storage_path('app'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $directoryPermissions = [];
        $allPermissionsWritable = true;
        foreach ($storagePaths as $label => $path) {
            $isWritable = is_writable($path);
            if (!$isWritable) {
                $allPermissionsWritable = false;
            }
            $directoryPermissions[$label] = [
                'path' => $path,
                'exists' => file_exists($path),
                'writable' => $isWritable,
                'perms' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A',
            ];
        }

        // 4. Memory & Upload Limits
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time') . 's';
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');

        return [
            'server' => [
                'os' => PHP_OS . ' (' . php_uname('s') . ' ' . php_uname('r') . ')',
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Built-in Server',
                'php_version' => PHP_VERSION,
                'php_sapi' => PHP_SAPI,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug') ? 'Enabled (true)' : 'Disabled (false)',
                'timezone' => config('app.timezone'),
                'url' => config('app.url'),
            ],
            'php_limits' => [
                'memory_limit' => $memoryLimit ?: 'Unlimited',
                'max_execution_time' => $maxExecutionTime,
                'upload_max_filesize' => $uploadMaxFilesize ?: 'N/A',
                'post_max_size' => $postMaxSize ?: 'N/A',
                'max_input_vars' => ini_get('max_input_vars') ?: '1000',
            ],
            'database' => [
                'driver' => $dbDriver,
                'version' => $dbVersion,
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'table_count' => $dbTableCount,
                'size' => $dbSizeFormatted,
            ],
            'extensions' => $extensionsStatus,
            'all_extensions_met' => $allExtensionsMet,
            'permissions' => $directoryPermissions,
            'all_permissions_writable' => $allPermissionsWritable,
        ];
    }

    /**
     * Get and parse markdown documentation files in the project root.
     *
     * @return array<string, array{title: string, filename: string, content_html: string, raw_size_kb: float}>
     */
    public function getDocumentationFiles(): array
    {
        $docKeys = [
            'readme' => ['filename' => 'README.md', 'title' => 'Project Overview & Architecture'],
            'changelog' => ['filename' => 'CHANGELOG.md', 'title' => 'Release Notes & History'],
            'documents' => ['filename' => 'DOCUMENTS.md', 'title' => 'Documentation & Guidelines'],
            'production' => ['filename' => 'PRODUCTION-GUIDE.md', 'title' => 'Production Deployment Guide'],
            'multieditor' => ['filename' => 'ADVANCED MULTI-EDITOR.md', 'title' => 'AI Multi-Candidate Editor Spec'],
            'license' => ['filename' => 'LICENSE.md', 'title' => 'Software License Agreement'],
        ];

        $docs = [];
        foreach ($docKeys as $key => $meta) {
            $filePath = base_path($meta['filename']);
            if (File::exists($filePath)) {
                $rawContent = File::get($filePath);
                $docs[$key] = [
                    'title' => $meta['title'],
                    'filename' => $meta['filename'],
                    'content_html' => Str::markdown($rawContent),
                    'raw_size_kb' => round(File::size($filePath) / 1024, 2),
                ];
            }
        }

        return $docs;
    }
}

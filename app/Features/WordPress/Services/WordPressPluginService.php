<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Service
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

namespace App\Features\WordPress\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class WordPressPluginService
{
    public const PLUGIN_DIR_NAME = 'hoa-studio-wordpress';
    public const ZIP_FILE_NAME = 'hoa-studio-wordpress.zip';
    public const CURRENT_VERSION = '2.6.0';

    public function getPluginDirectoryPath(): string
    {
        return public_path('plugins/' . self::PLUGIN_DIR_NAME);
    }

    public function getZipFilePath(): string
    {
        return public_path('plugins/' . self::ZIP_FILE_NAME);
    }

    public function getPluginInfo(): array
    {
        $dirPath = $this->getPluginDirectoryPath();
        $zipPath = $this->getZipFilePath();

        $exists = is_dir($dirPath);
        $zipExists = file_exists($zipPath);

        $zipSize = $zipExists ? (int) filesize($zipPath) : 0;
        $zipModified = $zipExists ? filemtime($zipPath) : null;

        return [
            'name' => 'HOA-Studio AI Editor & Content Suite',
            'slug' => self::PLUGIN_DIR_NAME,
            'version' => self::CURRENT_VERSION,
            'directory_exists' => $exists,
            'zip_exists' => $zipExists,
            'zip_size_bytes' => $zipSize,
            'zip_size_human' => $this->formatBytes($zipSize),
            'zip_modified_at' => $zipModified ? date('Y-m-d H:i:s', $zipModified) : null,
            'download_url' => route('dashboard.wordpress.download'),
        ];
    }

    /**
     * Ensures the ZIP archive is up to date with the files in the plugin directory.
     * Re-packages if the zip is missing or older than the newest file in the directory.
     */
    public function ensureZipArchive(): string
    {
        $dirPath = $this->getPluginDirectoryPath();
        $zipPath = $this->getZipFilePath();

        if (!is_dir($dirPath)) {
            throw new \RuntimeException("WordPress plugin directory not found at [{$dirPath}].");
        }

        $shouldRebuild = !file_exists($zipPath) || $this->isZipStale($dirPath, $zipPath);

        if ($shouldRebuild) {
            $this->buildZipArchive($dirPath, $zipPath);
        }

        return $zipPath;
    }

    /**
     * Builds a clean ZIP distribution archive for the WordPress plugin.
     */
    public function buildZipArchive(string $sourceDir, string $destinationZip): void
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension is required to package the WordPress plugin.');
        }

        $zipDir = dirname($destinationZip);
        if (!is_dir($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        $tempZip = $destinationZip . '.tmp.' . uniqid();

        $zip = new ZipArchive();
        $res = $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($res !== true) {
            throw new \RuntimeException("Failed to create temporary ZIP archive at [{$tempZip}]. Code: {$res}");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($sourceDir) + 1);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Structure inside ZIP starts with hoa-studio-wordpress/
            $zipPath = self::PLUGIN_DIR_NAME . '/' . $relativePath;
            $zip->addFile($filePath, $zipPath);
        }

        $zip->close();

        if (file_exists($destinationZip)) {
            @unlink($destinationZip);
        }

        rename($tempZip, $destinationZip);
    }

    public function downloadResponse(): BinaryFileResponse
    {
        $zipPath = $this->ensureZipArchive();

        return response()->download($zipPath, self::ZIP_FILE_NAME, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    private function isZipStale(string $sourceDir, string $zipPath): bool
    {
        $zipTime = filemtime($zipPath);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() > $zipTime) {
                return true;
            }
        }

        return false;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}

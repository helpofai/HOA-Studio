<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Core Update Service
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

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class CoreUpdateService
{
    protected string $githubRepo = 'helpofai/HOA-Studio';
    protected string $backupDir;
    protected string $manifestFile;
    protected HealthProberService $healthProber;

    public function __construct(HealthProberService $healthProber)
    {
        $this->healthProber = $healthProber;
        $this->backupDir = storage_path('app/updates/backups');
        $this->manifestFile = storage_path('app/updates/restore-manifests.json');

        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true, true);
        }
    }

    /**
     * Get Current System Version.
     */
    public function getCurrentVersion(): string
    {
        $versionFile = base_path('version.json');
        if (File::exists($versionFile)) {
            $data = json_decode(File::get($versionFile), true);
            return $data['version'] ?? '2.5.0';
        }
        return '2.5.0';
    }

    /**
     * Get Full System Version Metadata Payload.
     */
    public function getVersionMetadata(): array
    {
        $versionFile = base_path('version.json');
        if (File::exists($versionFile)) {
            $data = json_decode(File::get($versionFile), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [
            'name' => 'HelpOfAi Studio (HOA-Studio)',
            'version' => '2.5.0',
            'version_code' => 250,
            'channel' => 'stable',
            'release_date' => date('Y-m-d'),
            'min_php_version' => '8.2.0',
            'min_laravel_version' => '12.0.0',
            'schema_version' => '2026_08_24_000001',
            'signature' => 'HOA-SEC-V250-ENTERPRISE',
        ];
    }

    /**
     * Check GitHub for available updates.
     *
     * @return array{has_update: bool, current_version: string, latest_version: string, release_notes: string, published_at: ?string, download_url: ?string, commit_sha: ?string}
     */
    public function checkForUpdates(): array
    {
        $currentVersion = $this->getCurrentVersion();
        $fallback = [
            'has_update' => false,
            'current_version' => $currentVersion,
            'latest_version' => $currentVersion,
            'release_notes' => 'You are on the latest verified release build of HelpOfAi Studio.',
            'published_at' => date('Y-m-d H:i:s'),
            'download_url' => null,
            'commit_sha' => $this->getCurrentGitSha(),
        ];

        try {
            // Check GitHub API for latest release or latest commits
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'HelpOfAi-Studio-Updater',
                    'Accept' => 'application/vnd.github.v3+json',
                ])
                ->get("https://api.github.com/repos/{$this->githubRepo}/releases/latest");

            if ($response->successful()) {
                $data = $response->json();
                $latestTag = ltrim($data['tag_name'] ?? $currentVersion, 'v');
                $hasUpdate = version_compare($latestTag, ltrim($currentVersion, 'v'), '>');

                return [
                    'has_update' => $hasUpdate,
                    'current_version' => $currentVersion,
                    'latest_version' => $data['tag_name'] ?? "v{$currentVersion}",
                    'release_notes' => $data['body'] ?? 'No release notes provided for this version.',
                    'published_at' => $data['published_at'] ?? null,
                    'download_url' => $data['zipball_url'] ?? "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip",
                    'commit_sha' => $data['target_commitish'] ?? null,
                ];
            }

            // If no release tag, check latest commit on main branch
            $commitResp = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'HelpOfAi-Studio-Updater',
                    'Accept' => 'application/vnd.github.v3+json',
                ])
                ->get("https://api.github.com/repos/{$this->githubRepo}/commits/main");

            if ($commitResp->successful()) {
                $commitData = $commitResp->json();
                $latestSha = substr($commitData['sha'] ?? '', 0, 8);
                $currentSha = $this->getCurrentGitSha();
                $hasUpdate = !empty($latestSha) && !empty($currentSha) && ($latestSha !== $currentSha);

                return [
                    'has_update' => $hasUpdate,
                    'current_version' => $currentVersion,
                    'latest_version' => "main ({$latestSha})",
                    'release_notes' => $commitData['commit']['message'] ?? 'Latest updates from GitHub repository.',
                    'published_at' => $commitData['commit']['committer']['date'] ?? null,
                    'download_url' => "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip",
                    'commit_sha' => $latestSha,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("Update check failed: {$e->getMessage()}");
        }

        return $fallback;
    }

    /**
     * Create an immutable full website snapshot (Codebase, Assets, Config + Database Dump).
     *
     * @return array{id: string, label: string, version: string, timestamp: string, file_backup: string, db_backup: ?string, git_sha: ?string, file_size_mb: float, db_size_kb: float, type: string}
     */
    public function createRestorePoint(string $label = 'Full Snapshot'): array
    {
        $id = 'rp_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6);
        $version = $this->getCurrentVersion();
        $gitSha = $this->getCurrentGitSha();
        $timestamp = date('Y-m-d H:i:s');

        // 1. Create Comprehensive Codebase Zip (app, bootstrap, config, database, public, resources, routes, build assets, root configs)
        $zipFileName = "backup_{$id}.zip";
        $zipFilePath = "{$this->backupDir}/{$zipFileName}";
        
        $this->createCodebaseZip($zipFilePath);

        // 2. Create Database Dump (MySQL schema + records or SQLite file)
        $dbFileName = "db_{$id}.sql";
        $dbFilePath = "{$this->backupDir}/{$dbFileName}";
        $this->dumpDatabase($dbFilePath);

        $dbSizeKb = 0;
        if (File::exists("{$dbFilePath}.sqlite")) {
            $dbSizeKb = round(File::size("{$dbFilePath}.sqlite") / 1024, 2);
        } elseif (File::exists($dbFilePath)) {
            $dbSizeKb = round(File::size($dbFilePath) / 1024, 2);
        }

        $restorePoint = [
            'id' => $id,
            'label' => $label,
            'version' => $version,
            'timestamp' => $timestamp,
            'file_backup' => $zipFilePath,
            'db_backup' => (File::exists("{$dbFilePath}.sqlite") || File::exists($dbFilePath)) ? $dbFilePath : null,
            'git_sha' => $gitSha,
            'file_size_mb' => File::exists($zipFilePath) ? round(File::size($zipFilePath) / (1024 * 1024), 2) : 0,
            'db_size_kb' => $dbSizeKb,
            'type' => 'full_snapshot',
        ];

        $manifests = $this->getRestorePoints();
        array_unshift($manifests, $restorePoint);

        // Keep maximum 20 latest restore points to conserve hosting disk space
        if (count($manifests) > 20) {
            $pruned = array_slice($manifests, 20);
            foreach ($pruned as $oldRp) {
                if (!empty($oldRp['file_backup']) && File::exists($oldRp['file_backup'])) {
                    @unlink($oldRp['file_backup']);
                }
                if (!empty($oldRp['db_backup'])) {
                    if (File::exists($oldRp['db_backup'])) @unlink($oldRp['db_backup']);
                    if (File::exists("{$oldRp['db_backup']}.sqlite")) @unlink("{$oldRp['db_backup']}.sqlite");
                }
            }
            $manifests = array_slice($manifests, 0, 20);
        }

        File::put($this->manifestFile, json_encode($manifests, JSON_PRETTY_PRINT));

        return $restorePoint;
    }

    /**
     * Delete a specific Snapshot Restore Point and its underlying backup files.
     */
    public function deleteRestorePoint(string $restorePointId): bool
    {
        $manifests = $this->getRestorePoints();
        $targetIndex = null;
        $target = null;

        foreach ($manifests as $index => $rp) {
            if ($rp['id'] === $restorePointId) {
                $targetIndex = $index;
                $target = $rp;
                break;
            }
        }

        if ($targetIndex === null || !$target) {
            throw new Exception("Snapshot [{$restorePointId}] not found.");
        }

        // Delete underlying zip file
        if (!empty($target['file_backup']) && File::exists($target['file_backup'])) {
            @unlink($target['file_backup']);
        }

        // Delete underlying database file
        if (!empty($target['db_backup'])) {
            if (File::exists($target['db_backup'])) @unlink($target['db_backup']);
            if (File::exists("{$target['db_backup']}.sqlite")) @unlink("{$target['db_backup']}.sqlite");
        }

        // Remove from manifest and persist
        array_splice($manifests, $targetIndex, 1);
        File::put($this->manifestFile, json_encode($manifests, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Get all available restore points.
     */
    public function getRestorePoints(): array
    {
        if (File::exists($this->manifestFile)) {
            $data = json_decode(File::get($this->manifestFile), true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    /**
     * Execute Core System Update with Automatic Rollback on failure.
     *
     * @return array{success: bool, message: string, rolled_back: bool, health: array}
     */
    public function executeUpdate(): array
    {
        $logMessages = [];
        $restorePoint = null;

        $log = function(string $type, string $message) use (&$logMessages) {
            $time = date('H:i:s');
            $logMessages[] = [
                'time' => $time,
                'type' => $type, // 'info', 'success', 'warning', 'error', 'command'
                'message' => $message,
            ];
        };

        try {
            $log('info', 'Initializing Core Update Sequence...');
            $log('info', 'Target Repository: https://github.com/' . $this->githubRepo);

            // Step 1: Pre-Flight Backup Snapshot
            $log('command', 'Creating pre-flight immutable full website snapshot...');
            $restorePoint = $this->createRestorePoint('Pre-Update Automated Restore Point');
            $log('success', "Pre-flight snapshot generated [{$restorePoint['id']}] (Code: {$restorePoint['file_size_mb']} MB, DB: {$restorePoint['db_size_kb']} KB)");

            // Step 2: Engage Safe Maintenance Mode
            $log('command', 'Engaging application maintenance mode (storage/framework/down)...');
            $this->putSiteInMaintenance();
            $log('success', 'Maintenance mode engaged. Traffic gracefully locked.');

            // Step 3: Fetch & Apply Updates (Git pull or Pure-PHP Zip unpack)
            if ($this->isGitAvailable()) {
                $log('command', 'Git CLI detected. Executing: git pull origin main');
                $this->applyGitUpdate();
                $log('success', 'Pulled latest release delta from GitHub via Git Driver.');
            } else {
                $log('command', 'Shared Hosting Mode active. Fetching release payload via ZipArchive Driver...');
                $this->applyZipUpdate();
                $log('success', 'Extracted and synchronized release payload via Pure-PHP Driver.');
            }

            // Step 4: Run Database Migrations
            $log('command', 'Running database migrations: php artisan migrate --force');
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
            $log('success', !empty($migrateOutput) ? $migrateOutput : 'Database schema verified. All tables up to date.');

            // Step 5: Clear and Recompile Caches
            $log('command', 'Synchronizing application caches: php artisan optimize:clear');
            Artisan::call('optimize:clear');
            $log('success', 'Caches, Blade views, and routes synchronized.');

            // Step 6: Post-Update Synthetic Health Prober
            $log('command', 'Executing post-update synthetic health probe diagnostics...');
            $health = $this->healthProber->probeSystem();
            
            foreach ($health['checks'] as $check) {
                $statusType = $check['status'] === 'pass' ? 'success' : ($check['status'] === 'fail' ? 'error' : 'warning');
                $log($statusType, "[{$check['name']}] {$check['message']} ({$check['duration_ms']}ms)");
            }

            if (!$health['passed']) {
                throw new Exception('Post-update health checks failed. Triggering automatic self-healing rollback.');
            }

            // Step 7: Update Success! Bring Site Back Online
            $this->bringSiteOnline();
            $log('success', '🎉 Update applied & verified successfully. Website is online.');

            return [
                'success' => true,
                'message' => 'Core update applied and verified successfully.',
                'rolled_back' => false,
                'health' => $health,
                'logs' => $logMessages,
            ];
        } catch (\Throwable $e) {
            Log::error("Update execution error: {$e->getMessage()}");
            $log('error', "CRITICAL ERROR: {$e->getMessage()}");

            // Step 8: Auto-Rollback Routine
            $rollbackSuccess = false;
            if ($restorePoint) {
                try {
                    $log('warning', "Initiating emergency self-healing rollback to pre-update snapshot [{$restorePoint['id']}]...");
                    $this->rollbackToPoint($restorePoint['id']);
                    $rollbackSuccess = true;
                    $log('success', "AUTO-ROLLBACK COMPLETE: Codebase and database reverted to previous working state.");
                } catch (\Throwable $rollbackError) {
                    $log('error', "CRITICAL ROLLBACK FAILURE: {$rollbackError->getMessage()}");
                }
            }

            $this->bringSiteOnline();

            return [
                'success' => false,
                'message' => "Update failed: {$e->getMessage()}" . ($rollbackSuccess ? ' (System automatically rolled back to working state).' : ''),
                'rolled_back' => $rollbackSuccess,
                'health' => $this->healthProber->probeSystem(),
                'logs' => $logMessages,
            ];
        }
    }

    /**
     * Rollback the system to a specified restore point.
     */
    public function rollbackToPoint(string $restorePointId): bool
    {
        $restorePoints = $this->getRestorePoints();
        $target = null;
        foreach ($restorePoints as $rp) {
            if ($rp['id'] === $restorePointId) {
                $target = $rp;
                break;
            }
        }

        if (!$target) {
            throw new Exception("Restore point [{$restorePointId}] not found.");
        }

        // 1. Put Site in Maintenance Mode
        $this->putSiteInMaintenance();

        // 2. Restore Codebase Files from Zip
        if (!empty($target['file_backup']) && File::exists($target['file_backup'])) {
            $zip = new ZipArchive();
            if ($zip->open($target['file_backup']) === true) {
                $zip->extractTo(base_path());
                $zip->close();
            }
        } elseif (!empty($target['git_sha']) && $this->isGitAvailable()) {
            @exec("git reset --hard {$target['git_sha']}");
        }

        // 3. Restore Database Dump
        if (!empty($target['db_backup']) && File::exists($target['db_backup'])) {
            $this->restoreDatabaseDump($target['db_backup']);
        }

        // 4. Clear and Recompile Caches
        Artisan::call('optimize:clear');

        // 5. Bring Site Back Online
        $this->bringSiteOnline();

        return true;
    }

    /**
     * Check if Git command-line is available on this environment.
     */
    public function isGitAvailable(): bool
    {
        if (!function_exists('exec') || !function_exists('shell_exec')) {
            return false;
        }

        try {
            $output = [];
            $returnVar = 0;
            @exec('git --version 2>&1', $output, $returnVar);
            return $returnVar === 0 && !empty($output);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get Current Git Commit SHA.
     */
    protected function getCurrentGitSha(): ?string
    {
        if ($this->isGitAvailable()) {
            $sha = @trim(shell_exec('git rev-parse --short HEAD 2>&1'));
            if (!empty($sha) && strlen($sha) >= 4 && strlen($sha) <= 40 && !str_contains($sha, 'fatal')) {
                return $sha;
            }
        }

        $headFile = base_path('.git/HEAD');
        if (File::exists($headFile)) {
            $head = trim(File::get($headFile));
            if (str_starts_with($head, 'ref:')) {
                $refPath = base_path('.git/' . trim(substr($head, 4)));
                if (File::exists($refPath)) {
                    return substr(trim(File::get($refPath)), 0, 8);
                }
            } else {
                return substr($head, 0, 8);
            }
        }

        return null;
    }

    /**
     * Apply update via Git CLI.
     */
    protected function applyGitUpdate(): void
    {
        $output = [];
        $returnVar = 0;
        @exec('git pull origin main 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            throw new Exception("Git pull failed: " . implode("\n", $output));
        }
    }

    /**
     * Apply update via pure-PHP Zip download & extraction.
     */
    protected function applyZipUpdate(): void
    {
        $downloadUrl = "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip";
        $tempZip = storage_path('app/updates/temp_update.zip');
        $tempExtractDir = storage_path('app/updates/temp_extracted');

        // Download Archive
        $response = Http::timeout(60)->sink($tempZip)->get($downloadUrl);
        if (!$response->successful() || !File::exists($tempZip)) {
            throw new Exception("Failed to download update package from GitHub.");
        }

        // Unpack Archive
        $zip = new ZipArchive();
        if ($zip->open($tempZip) !== true) {
            throw new Exception("Failed to open downloaded update zip archive.");
        }

        File::deleteDirectory($tempExtractDir);
        File::makeDirectory($tempExtractDir, 0755, true, true);
        $zip->extractTo($tempExtractDir);
        $zip->close();
        @unlink($tempZip);

        // Find root folder inside zip
        $extractedDirs = File::directories($tempExtractDir);
        $sourceDir = !empty($extractedDirs) ? $extractedDirs[0] : $tempExtractDir;

        // Safely Copy files over base_path, preserving sensitive files
        $this->syncDirectories($sourceDir, base_path(), [
            '.env',
            'storage',
            'public/uploads',
            'public/hoa-rescue.php',
            'version.json',
        ]);

        File::deleteDirectory($tempExtractDir);
    }

    /**
     * Recursive directory sync preserving excluded files.
     */
    protected function syncDirectories(string $source, string $destination, array $excludes = []): void
    {
        $items = File::allFiles($source, true);
        foreach ($items as $item) {
            $relativePath = $item->getRelativePathname();
            
            // Check exclusion rules
            $isExcluded = false;
            foreach ($excludes as $exclude) {
                if (str_starts_with($relativePath, $exclude) || $relativePath === $exclude) {
                    $isExcluded = true;
                    break;
                }
            }

            if ($isExcluded) {
                continue;
            }

            $targetPath = "{$destination}/{$relativePath}";
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
            }

            File::copy($item->getRealPath(), $targetPath);
        }
    }

    /**
     * Create comprehensive full-website zip archive of application codebase and root domain files.
     */
    protected function createCodebaseZip(string $zipFilePath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return;
        }

        $basePath = base_path();
        
        // 1. All Primary & Auxiliary Application Directories
        $directoriesToInclude = [
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'tests',
            'vendor',
            '.helpofai',
            'storage/app/public',
        ];

        foreach ($directoriesToInclude as $dir) {
            $fullDir = "{$basePath}/{$dir}";
            if (File::exists($fullDir)) {
                $files = File::allFiles($fullDir);
                foreach ($files as $file) {
                    $relative = "{$dir}/" . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relative);
                }
            }
        }

        // 2. All Root Files (Documents, Configs, Package manifests, Dotfiles)
        $rootFiles = File::files($basePath, true);
        $excludedZipNames = [basename($zipFilePath)];

        foreach ($rootFiles as $file) {
            $fileName = $file->getFilename();
            // Exclude large temporary dump archives during creation
            if (str_starts_with($fileName, 'backup_rp_') || in_array($fileName, $excludedZipNames)) {
                continue;
            }

            $zip->addFile($file->getRealPath(), $fileName);
        }

        $zip->close();
    }

    /**
     * Export database backup.
     */
    protected function dumpDatabase(string $filePath): void
    {
        try {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                $sqliteDb = config('database.connections.sqlite.database');
                if ($sqliteDb && File::exists($sqliteDb)) {
                    File::copy($sqliteDb, "{$filePath}.sqlite");
                }
            } else {
                // Pure-PHP MySQL simple table backup
                $tables = DB::select('SHOW TABLES');
                $sql = "-- HOA Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
                foreach ($tables as $table) {
                    $tableArray = (array) $table;
                    $tableName = reset($tableArray);
                    
                    $create = DB::select("SHOW CREATE TABLE `{$tableName}`");
                    if (!empty($create)) {
                        $createArr = (array) $create[0];
                        $sql .= "\nDROP TABLE IF EXISTS `{$tableName}`;\n";
                        $sql .= ($createArr['Create Table'] ?? '') . ";\n\n";
                    }

                    $rows = DB::table($tableName)->get();
                    foreach ($rows as $row) {
                        $rowArr = (array) $row;
                        $keys = array_map(fn($k) => "`{$k}`", array_keys($rowArr));
                        $vals = array_map(function($v) {
                            return is_null($v) ? 'NULL' : DB::getPdo()->quote($v);
                        }, array_values($rowArr));
                        
                        if (!empty($keys)) {
                            $sql .= "INSERT INTO `{$tableName}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ");\n";
                        }
                    }
                }
                File::put($filePath, $sql);
            }
        } catch (\Throwable $e) {
            Log::warning("Database dump warning: {$e->getMessage()}");
        }
    }

    /**
     * Restore database from backup.
     */
    protected function restoreDatabaseDump(string $filePath): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite' && File::exists("{$filePath}.sqlite")) {
            $sqliteDb = config('database.connections.sqlite.database');
            if ($sqliteDb) {
                File::copy("{$filePath}.sqlite", $sqliteDb);
            }
        } elseif (File::exists($filePath)) {
            $sql = File::get($filePath);
            if (!empty($sql)) {
                DB::unprepared($sql);
            }
        }
    }

    protected function putSiteInMaintenance(): void
    {
        try {
            Artisan::call('down', ['--render' => 'errors.503']);
        } catch (\Throwable $e) {
            @file_put_contents(storage_path('framework/down'), json_encode([
                'time' => time(),
                'message' => 'HelpOfAi Studio is updating to the latest version.',
                'retry' => 60,
            ]));
        }
    }

    protected function bringSiteOnline(): void
    {
        try {
            Artisan::call('up');
        } catch (\Throwable $e) {
            @unlink(storage_path('framework/down'));
        }
    }
}

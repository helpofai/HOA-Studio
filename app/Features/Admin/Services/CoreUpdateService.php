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
     * Check GitHub for available updates directly from repository main branch.
     *
     * @return array{has_update: bool, current_version: string, latest_version: string, release_notes: string, published_at: ?string, download_url: ?string, commit_sha: ?string, latest_sha: ?string, commits_behind: int, recent_commits: array, changed_files: array, remote_version_meta: array}
     */
    public function checkForUpdates(): array
    {
        $currentVersionMeta = $this->getVersionMetadata();
        $currentVersion = $currentVersionMeta['version'] ?? '2.5.0';
        $currentVersionCode = (int) ($currentVersionMeta['version_code'] ?? 250);
        $currentBuildNumber = (string) ($currentVersionMeta['build_number'] ?? '');
        $currentSha = $this->getCurrentGitSha();

        $fallback = [
            'has_update' => false,
            'current_version' => $currentVersion,
            'latest_version' => $currentVersion,
            'release_notes' => 'You are on the latest verified release build of HelpOfAi Studio.',
            'published_at' => date('Y-m-d H:i:s'),
            'download_url' => "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip",
            'commit_sha' => $currentSha,
            'latest_sha' => $currentSha,
            'commits_behind' => 0,
            'recent_commits' => [],
            'changed_files' => [],
            'remote_version_meta' => $currentVersionMeta,
        ];

        try {
            // 1. Direct Fetch: Remote version.json from GitHub raw content (Zero rate-limit)
            $remoteVersionResp = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'HelpOfAi-Studio-Updater'])
                ->get("https://raw.githubusercontent.com/{$this->githubRepo}/main/version.json");

            $remoteVersionData = [];
            if ($remoteVersionResp->successful()) {
                $remoteVersionData = $remoteVersionResp->json() ?: [];
            }

            $remoteVersion = $remoteVersionData['version'] ?? $currentVersion;
            $remoteVersionCode = (int) ($remoteVersionData['version_code'] ?? $currentVersionCode);
            $remoteBuildNumber = (string) ($remoteVersionData['build_number'] ?? $currentBuildNumber);

            // 2. Fetch Latest Commit Metadata on main branch
            $commitResp = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => 'HelpOfAi-Studio-Updater',
                    'Accept' => 'application/vnd.github.v3+json',
                ])
                ->get("https://api.github.com/repos/{$this->githubRepo}/commits/main");

            $latestSha = null;
            $commitMessage = 'Latest updates from GitHub repository.';
            $commitDate = date('Y-m-d H:i:s');
            $commitAuthor = 'HOA Core Team';

            if ($commitResp->successful()) {
                $commitData = $commitResp->json();
                $latestSha = substr($commitData['sha'] ?? '', 0, 8);
                $commitMessage = $commitData['commit']['message'] ?? $commitMessage;
                $commitDate = $commitData['commit']['committer']['date'] ?? $commitDate;
                $commitAuthor = $commitData['commit']['author']['name'] ?? $commitAuthor;
            }

            // 3. Fetch Comparison Delta if current SHA differs from latest SHA
            $commitsBehind = 0;
            $recentCommits = [];
            $changedFiles = [];

            if (!empty($currentSha) && !empty($latestSha) && $currentSha !== $latestSha) {
                try {
                    $compareResp = Http::timeout(6)
                        ->withHeaders([
                            'User-Agent' => 'HelpOfAi-Studio-Updater',
                            'Accept' => 'application/vnd.github.v3+json',
                        ])
                        ->get("https://api.github.com/repos/{$this->githubRepo}/compare/{$currentSha}...main");

                    if ($compareResp->successful()) {
                        $compareData = $compareResp->json();
                        $commitsBehind = (int) ($compareData['ahead_by'] ?? 0);
                        
                        foreach (($compareData['commits'] ?? []) as $c) {
                            $recentCommits[] = [
                                'sha' => substr($c['sha'] ?? '', 0, 8),
                                'message' => $c['commit']['message'] ?? '',
                                'author' => $c['commit']['author']['name'] ?? '',
                                'date' => $c['commit']['committer']['date'] ?? '',
                            ];
                        }

                        foreach (($compareData['files'] ?? []) as $f) {
                            $changedFiles[] = [
                                'filename' => $f['filename'] ?? '',
                                'status' => $f['status'] ?? 'modified',
                                'additions' => $f['additions'] ?? 0,
                                'deletions' => $f['deletions'] ?? 0,
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    // Non-blocking delta fallback
                }
            }

            // Determine if update is available:
            // - Remote version code > current version code
            // - OR version_compare(remote, current, '>')
            // - OR remote build number !== current build number
            // - OR remote commit SHA differs from local commit SHA
            $hasUpdate = ($remoteVersionCode > $currentVersionCode)
                || (version_compare($remoteVersion, $currentVersion, '>'))
                || (!empty($remoteBuildNumber) && !empty($currentBuildNumber) && $remoteBuildNumber !== $currentBuildNumber)
                || (!empty($latestSha) && !empty($currentSha) && $latestSha !== $currentSha);

            $displayLatestVersion = $remoteVersion;
            if ($displayLatestVersion === $currentVersion && !empty($latestSha) && $latestSha !== $currentSha) {
                $displayLatestVersion = "{$currentVersion} (build {$latestSha})";
            }

            return [
                'has_update' => $hasUpdate,
                'current_version' => $currentVersion,
                'latest_version' => $displayLatestVersion,
                'current_sha' => $currentSha,
                'commit_sha' => $currentSha,
                'latest_sha' => $latestSha ?? $currentSha,
                'commits_behind' => $commitsBehind,
                'recent_commits' => array_slice(array_reverse($recentCommits), 0, 5),
                'changed_files' => array_slice($changedFiles, 0, 15),
                'release_notes' => $commitMessage,
                'published_at' => $commitDate,
                'download_url' => "https://github.com/{$this->githubRepo}/archive/refs/heads/main.zip",
                'remote_version_meta' => !empty($remoteVersionData) ? $remoteVersionData : $currentVersionMeta,
            ];
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

            // Step 4: Synchronize New .env Environment Variables (Preserve Production Keys)
            $log('command', 'Analyzing .env.example for new environment variable specifications...');
            $newEnvKeys = $this->syncEnvVariables();
            if (!empty($newEnvKeys)) {
                $log('success', 'Synchronized new environment variables into .env: ' . implode(', ', $newEnvKeys));
            } else {
                $log('info', 'Environment variables in .env are up to date.');
            }

            // Step 5: Run Database Migrations Safely
            $log('command', 'Executing schema migrations: php artisan migrate --force');
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
            $log('success', !empty($migrateOutput) ? $migrateOutput : 'Database schema verified. All tables up to date.');

            // Step 6: Clear and Recompile Caches
            $log('command', 'Synchronizing application caches: php artisan optimize:clear');
            Artisan::call('optimize:clear');
            $log('success', 'Caches, Blade views, and routes synchronized.');

            // Step 7: Post-Update Synthetic Health Prober
            $log('command', 'Executing post-update synthetic health probe diagnostics...');
            $health = $this->healthProber->probeSystem();
            
            foreach ($health['checks'] as $check) {
                $statusType = $check['status'] === 'pass' ? 'success' : ($check['status'] === 'fail' ? 'error' : 'warning');
                $log($statusType, "[{$check['name']}] {$check['message']} ({$check['duration_ms']}ms)");
            }

            if (!$health['passed']) {
                throw new Exception('Post-update health checks failed. Triggering automatic self-healing rollback.');
            }

            // Step 8: Update Success! Bring Site Back Online
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
     * Apply update via Git CLI with clean origin/main reset.
     */
    protected function applyGitUpdate(): void
    {
        $output = [];
        $returnVar = 0;
        @exec('git fetch origin main 2>&1 && git reset --hard origin/main 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            // Fallback to git pull
            @exec('git pull origin main 2>&1', $output, $returnVar);
            if ($returnVar !== 0) {
                throw new Exception("Git pull/reset failed: " . implode("\n", $output));
            }
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

        // Safely Copy files over base_path, preserving user configuration and runtime data
        $this->syncDirectories($sourceDir, base_path(), [
            '.env',
            'storage',
            'public/uploads',
            'public/hoa-rescue.php',
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
     * Safely merge missing environment keys from .env.example into production .env.
     *
     * @return array<string> List of newly appended environment variable keys
     */
    public function syncEnvVariables(): array
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (!File::exists($envPath) || !File::exists($examplePath)) {
            return [];
        }

        $currentEnv = File::get($envPath);
        $exampleEnv = File::get($examplePath);

        preg_match_all('/^([A-Z0-9_]+)=/m', $exampleEnv, $exampleMatches);
        preg_match_all('/^([A-Z0-9_]+)=/m', $currentEnv, $currentMatches);

        $exampleKeys = $exampleMatches[1] ?? [];
        $currentKeys = $currentMatches[1] ?? [];

        $missingKeys = array_diff($exampleKeys, $currentKeys);
        $appendedKeys = [];

        if (!empty($missingKeys)) {
            $appendContent = "\n# --- Auto-Added by HOA Core Update System (" . date('Y-m-d H:i:s') . ") ---\n";
            foreach ($missingKeys as $key) {
                if (preg_match('/^(' . preg_quote($key, '/') . '=.*)$/m', $exampleEnv, $lineMatch)) {
                    $appendContent .= $lineMatch[1] . "\n";
                    $appendedKeys[] = $key;
                }
            }
            File::append($envPath, $appendContent);
        }

        return $appendedKeys;
    }

    /**
     * Create comprehensive full-website zip archive of application codebase and root domain files.
     */
    protected function createCodebaseZip(string $zipFilePath): void
    {
        $targetDir = dirname($zipFilePath);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

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
            'storage/app/public',
            'storage/framework/views',
            'storage/framework/sessions',
            'storage/logs',
            'tests',
            'vendor',
        ];

        foreach ($directoriesToInclude as $dir) {
            $fullDir = "{$basePath}/{$dir}";
            if (File::exists($fullDir)) {
                $files = File::allFiles($fullDir, true);
                foreach ($files as $file) {
                    $relative = "{$dir}/" . $file->getRelativePathname();
                    $normalizedRelative = str_replace('\\', '/', $relative);

                    $zip->addFile($file->getRealPath(), $normalizedRelative);
                }
            }
        }

        // 2. All Root Files (Dotfiles, Manifests, Configs, Bootstraps, Production Docs)
        $allRootItems = @scandir($basePath) ?: [];
        $excludedRootFiles = [
            '.',
            '..',
            '.git',
            '.helpofai',
            '.phpunit.result.cache',
            'ADVANCED MULTI-EDITOR.md',
            'AGENTS.md',
            'agent.md',
            'ai-content-writer-laravel13-plan.md',
            'blog-post -creation-plan.md',
            basename($zipFilePath),
        ];

        foreach ($allRootItems as $item) {
            if (in_array($item, $excludedRootFiles)) {
                continue;
            }

            $itemPath = "{$basePath}/{$item}";
            if (is_file($itemPath)) {
                // Skip backup zip files in root if any
                if (str_starts_with($item, 'backup_rp_')) {
                    continue;
                }
                $zip->addFile($itemPath, $item);
            }
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

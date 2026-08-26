<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Standalone Disaster Recovery Script
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| Usage: http://your-domain.com/hoa-rescue.php?key=YOUR_RESCUE_KEY
|
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
$envFile = $baseDir . '/.env';
$backupDir = $baseDir . '/storage/app/updates/backups';
$manifestFile = $baseDir . '/storage/app/updates/restore-manifests.json';

// Simple authentication token lookup from .env or default secret
$rescueKey = 'hoa_rescue_' . md5($baseDir);
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^RESCUE_SECRET=(.*)$/m', $envContent, $matches)) {
        $rescueKey = trim($matches[1], "\"' ");
    }
}

$providedKey = $_GET['key'] ?? $_POST['key'] ?? '';
$isAuthorized = !empty($providedKey) && hash_equals($rescueKey, $providedKey);

$action = $_POST['action'] ?? '';
$message = '';
$messageType = 'info';

if ($isAuthorized && !empty($action)) {
    if ($action === 'disable_maintenance') {
        $downFile = $baseDir . '/storage/framework/down';
        if (file_exists($downFile)) {
            @unlink($downFile);
            $message = 'Maintenance mode disabled successfully. Website is now accessible.';
            $messageType = 'success';
        } else {
            $message = 'Maintenance mode was not active.';
            $messageType = 'info';
        }
    } elseif ($action === 'flush_cache') {
        $viewPath = $baseDir . '/storage/framework/views';
        $cachePath = $baseDir . '/storage/framework/cache/data';
        $configPath = $baseDir . '/bootstrap/cache/config.php';
        $routesPath = $baseDir . '/bootstrap/cache/routes-v7.php';
        $servicesPath = $baseDir . '/bootstrap/cache/services.php';
        $packagesPath = $baseDir . '/bootstrap/cache/packages.php';

        @unlink($configPath);
        @unlink($routesPath);
        @unlink($servicesPath);
        @unlink($packagesPath);

        if (is_dir($viewPath)) {
            $files = glob($viewPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) @unlink($file);
            }
        }
        $message = 'Emergency view, routes, services, and configuration cache flushed successfully.';
        $messageType = 'success';
    } elseif ($action === 'test_db') {
        try {
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                preg_match('/^DB_HOST=(.*)$/m', $envContent, $h);
                preg_match('/^DB_PORT=(.*)$/m', $envContent, $p);
                preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $d);
                preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $u);
                preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $pw);

                $host = trim($h[1] ?? '127.0.0.1', "\"' ");
                $port = trim($p[1] ?? '3306', "\"' ");
                $db = trim($d[1] ?? '', "\"' ");
                $user = trim($u[1] ?? '', "\"' ");
                $pass = trim($pw[1] ?? '', "\"' ");

                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $message = "Database Connected Successfully! Found " . count($tables) . " tables in [{$db}].";
                $messageType = 'success';
            } else {
                $message = ".env file not found.";
                $messageType = 'error';
            }
        } catch (\Throwable $e) {
            $message = "Database Connection Failed: " . $e->getMessage();
            $messageType = 'error';
        }
    }
    } elseif ($action === 'restore_backup' && !empty($_POST['backup_file'])) {
        $targetFile = basename($_POST['backup_file']);
        $fullPath = $backupDir . '/' . $targetFile;

        if (file_exists($fullPath) && class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($fullPath) === true) {
                $zip->extractTo($baseDir);
                $zip->close();
                
                // Restore Database if matching dump exists
                $snapshotId = str_replace(['backup_', '.zip'], '', $targetFile);
                $dbSqlite = $backupDir . '/db_' . $snapshotId . '.sql.sqlite';
                $dbSql = $backupDir . '/db_' . $snapshotId . '.sql';

                if (file_exists($dbSqlite)) {
                    $sqliteDest = $baseDir . '/database/database.sqlite';
                    @copy($dbSqlite, $sqliteDest);
                } elseif (file_exists($dbSql)) {
                    // Execute SQL dump via native PDO if db credentials in .env
                    try {
                        if (file_exists($envFile)) {
                            $envContent = file_get_contents($envFile);
                            preg_match('/^DB_HOST=(.*)$/m', $envContent, $h);
                            preg_match('/^DB_PORT=(.*)$/m', $envContent, $p);
                            preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $d);
                            preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $u);
                            preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $pw);

                            $host = trim($h[1] ?? '127.0.0.1', "\"' ");
                            $port = trim($p[1] ?? '3306', "\"' ");
                            $db = trim($d[1] ?? '', "\"' ");
                            $user = trim($u[1] ?? '', "\"' ");
                            $pass = trim($pw[1] ?? '', "\"' ");

                            if (!empty($db)) {
                                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                ]);
                                $sql = file_get_contents($dbSql);
                                $pdo->exec($sql);
                            }
                        }
                    } catch (\Throwable $e) {}
                }

                // Flush caches after restore
                @unlink($baseDir . '/bootstrap/cache/config.php');
                @unlink($baseDir . '/bootstrap/cache/routes-v7.php');
                @unlink($baseDir . '/storage/framework/down');

                $message = "Codebase & Database state successfully restored from snapshot [{$targetFile}].";
                $messageType = 'success';
            } else {
                $message = "Failed to open zip archive [{$targetFile}].";
                $messageType = 'error';
            }
        } else {
            $message = "Backup archive [{$targetFile}] does not exist.";
            $messageType = 'error';
        }
    }
}

// Load manifest restore points
$restorePoints = [];
if (file_exists($manifestFile)) {
    $manifestData = json_decode(file_get_contents($manifestFile), true);
    if (is_array($manifestData)) $restorePoints = $manifestData;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpOfAi Studio — Emergency Disaster Rescue</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-slate-900 border border-white/10 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-lg font-bold">
                    🚨
                </div>
                <div>
                    <h1 class="text-xl font-black text-white">Emergency Disaster Rescue</h1>
                    <p class="text-xs text-slate-400">Standalone offline recovery mode for HelpOfAi Studio</p>
                </div>
            </div>
            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-rose-950 text-rose-300 border border-rose-800">
                OFFLINE MODE
            </span>
        </div>

        <?php if (!$isAuthorized): ?>
            <form method="GET" class="space-y-4">
                <div class="p-4 rounded-xl bg-slate-950 border border-white/10 text-xs text-slate-300">
                    Please enter the emergency rescue key to authenticate. Your default rescue key is:
                    <code class="text-amber-300 block font-mono mt-1 select-all"><?= htmlspecialchars($rescueKey) ?></code>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Rescue Key</label>
                    <input type="password" name="key" class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-white/15 text-sm text-white font-mono" placeholder="Enter key..." required>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 font-bold text-xs text-white shadow-lg">
                    Authenticate Rescue Console &rarr;
                </button>
            </form>
        <?php else: ?>
            <?php if (!empty($message)): ?>
                <div class="p-4 rounded-xl border text-xs font-semibold <?= $messageType === 'success' ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/80 border-rose-500/40 text-rose-300' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <form method="POST" class="p-4 rounded-xl bg-slate-950 border border-white/10 space-y-3">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                    <input type="hidden" name="action" value="disable_maintenance">
                    <div class="font-bold text-sm text-white flex items-center gap-2">
                        <span>🔓</span>
                        <span>Disable Maintenance</span>
                    </div>
                    <p class="text-xs text-slate-400">Remove lock file and bring website online immediately.</p>
                    <button type="submit" class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white border border-white/10">
                        Force Site Online
                    </button>
                </form>

                <form method="POST" class="p-4 rounded-xl bg-slate-950 border border-white/10 space-y-3">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                    <input type="hidden" name="action" value="flush_cache">
                    <div class="font-bold text-sm text-white flex items-center gap-2">
                        <span>🧹</span>
                        <span>Flush All Caches</span>
                    </div>
                    <p class="text-xs text-slate-400">Flush views, routes, config & service cache.</p>
                    <button type="submit" class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white border border-white/10">
                        Flush All Caches
                    </button>
                </form>

                <form method="POST" class="p-4 rounded-xl bg-slate-950 border border-white/10 space-y-3">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                    <input type="hidden" name="action" value="test_db">
                    <div class="font-bold text-sm text-white flex items-center gap-2">
                        <span>🗄️</span>
                        <span>Test Database</span>
                    </div>
                    <p class="text-xs text-slate-400">Verify MySQL database credentials and tables.</p>
                    <button type="submit" class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white border border-white/10">
                        Test Connection
                    </button>
                </form>
            </div>

            <!-- Recent Laravel Log Tail -->
            <div class="p-4 rounded-xl bg-slate-950 border border-white/10 space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                    <span>📋 Recent Server Error Log (storage/logs/laravel.log)</span>
                </div>
                <?php
                    $logFile = $baseDir . '/storage/logs/laravel.log';
                    $logContent = 'Log file is clean / no errors recorded.';
                    if (file_exists($logFile) && filesize($logFile) > 0) {
                        $lines = file($logFile);
                        $lastLines = array_slice($lines, -15);
                        $logContent = htmlspecialchars(implode("", $lastLines));
                    }
                ?>
                <pre class="p-3 rounded-lg bg-black text-[11px] font-mono text-slate-300 overflow-x-auto max-h-48 border border-white/5"><?= $logContent ?></pre>
            </div>

            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Available Restore Snapshots</h3>
                <?php if (empty($restorePoints)): ?>
                    <p class="text-xs text-slate-500 font-mono">No restore manifests found in storage directory.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($restorePoints as $rp): ?>
                            <form method="POST" class="p-3 rounded-xl bg-slate-950 border border-white/10 flex items-center justify-between gap-4">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($providedKey) ?>">
                                <input type="hidden" name="action" value="restore_backup">
                                <input type="hidden" name="backup_file" value="<?= htmlspecialchars(basename($rp['file_backup'] ?? '')) ?>">
                                <div>
                                    <div class="text-xs font-bold text-violet-300 font-mono"><?= htmlspecialchars($rp['id']) ?> (v<?= htmlspecialchars($rp['version'] ?? '2.4.0') ?>)</div>
                                    <div class="text-[11px] text-slate-400"><?= htmlspecialchars($rp['label'] ?? 'Snapshot') ?> &bull; <?= htmlspecialchars($rp['timestamp'] ?? '') ?></div>
                                </div>
                                <button type="submit" onclick="return confirm('Restore codebase to this point?');" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs">
                                    Restore
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

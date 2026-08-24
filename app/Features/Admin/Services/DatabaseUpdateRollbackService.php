<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Database Migration & Rollback Engine
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
use Illuminate\Support\Facades\Log;

class DatabaseUpdateRollbackService
{
    protected string $backupDirectory;

    public function __construct()
    {
        $this->backupDirectory = storage_path('app/updates/db-snapshots');
        if (!File::exists($this->backupDirectory)) {
            File::makeDirectory($this->backupDirectory, 0755, true, true);
        }
    }

    /**
     * Get Database Engine & Connection Details.
     */
    public function getDatabaseDetails(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $databaseName = $connection->getDatabaseName();

        $tableCount = 0;
        try {
            if ($driver === 'sqlite') {
                $tableCount = count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"));
            } else {
                $tableCount = count(DB::select('SHOW TABLES'));
            }
        } catch (\Throwable $e) {}

        return [
            'driver' => strtoupper($driver),
            'database' => $databaseName,
            'table_count' => $tableCount,
            'backup_dir' => $this->backupDirectory,
        ];
    }

    /**
     * Create an Isolated Point-in-Time Database Backup Snapshot.
     *
     * @return array{id: string, path: string, size_kb: float, timestamp: string, driver: string, tables_count: int}
     */
    public function createDatabaseSnapshot(string $label = 'DB Snapshot'): array
    {
        $id = 'dbsnap_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6);
        $timestamp = date('Y-m-d H:i:s');
        $driver = DB::connection()->getDriverName();

        $filePath = "{$this->backupDirectory}/{$id}.sql";
        $tableCount = 0;

        if ($driver === 'sqlite') {
            $sqliteFile = config('database.connections.sqlite.database');
            $targetSqlite = "{$this->backupDirectory}/{$id}.sqlite";
            if ($sqliteFile && File::exists($sqliteFile)) {
                File::copy($sqliteFile, $targetSqlite);
                $filePath = $targetSqlite;
            }
            $tableCount = count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"));
        } else {
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);

            $sql = "-- ========================================================\n";
            $sql .= "-- HelpOfAi Studio Database Snapshot\n";
            $sql .= "-- Snapshot ID: {$id}\n";
            $sql .= "-- Label: {$label}\n";
            $sql .= "-- Generated: {$timestamp}\n";
            $sql .= "-- Engine: {$driver}\n";
            $sql .= "-- ========================================================\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableArray = (array) $table;
                $tableName = reset($tableArray);

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTable)) {
                    $createArr = (array) $createTable[0];
                    $sql .= "\n-- Table structure for `{$tableName}`\n";
                    $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $sql .= ($createArr['Create Table'] ?? '') . ";\n\n";
                }

                $rows = DB::table($tableName)->get();
                if ($rows->isNotEmpty()) {
                    $sql .= "-- Dumping data for `{$tableName}`\n";
                    foreach ($rows as $row) {
                        $rowArr = (array) $row;
                        $keys = array_map(fn($k) => "`{$k}`", array_keys($rowArr));
                        $vals = array_map(function($v) {
                            return is_null($v) ? 'NULL' : DB::getPdo()->quote($v);
                        }, array_values($rowArr));

                        $sql .= "INSERT INTO `{$tableName}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            File::put($filePath, $sql);
        }

        $manifestFile = "{$this->backupDirectory}/snapshots-manifest.json";
        $manifest = [];
        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true) ?: [];
        }

        $snapshotRecord = [
            'id' => $id,
            'label' => $label,
            'path' => $filePath,
            'size_kb' => File::exists($filePath) ? round(File::size($filePath) / 1024, 2) : 0,
            'timestamp' => $timestamp,
            'driver' => $driver,
            'tables_count' => $tableCount,
        ];

        array_unshift($manifest, $snapshotRecord);

        // Keep last 15 DB snapshots
        if (count($manifest) > 15) {
            $pruned = array_slice($manifest, 15);
            foreach ($pruned as $p) {
                if (!empty($p['path']) && File::exists($p['path'])) {
                    @unlink($p['path']);
                }
            }
            $manifest = array_slice($manifest, 0, 15);
        }

        File::put($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT));

        return $snapshotRecord;
    }

    /**
     * Get All Saved Database Snapshots.
     */
    public function getDatabaseSnapshots(): array
    {
        $manifestFile = "{$this->backupDirectory}/snapshots-manifest.json";
        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            return is_array($manifest) ? $manifest : [];
        }
        return [];
    }

    /**
     * Run All Pending Database Migrations.
     *
     * @return array{success: bool, output: string}
     */
    public function runMigrations(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return [
                'success' => true,
                'output' => !empty(trim($output)) ? $output : 'No pending migrations to run. Schema is fully up to date.',
            ];
        } catch (\Throwable $e) {
            Log::error("Database migration error: {$e->getMessage()}");
            return [
                'success' => false,
                'output' => "Migration failed: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Rollback the Last Migration Batch (Artisan migrate:rollback).
     *
     * @return array{success: bool, output: string}
     */
    public function rollbackLastMigrationBatch(int $step = 1): array
    {
        try {
            Artisan::call('migrate:rollback', [
                '--step' => $step,
                '--force' => true,
            ]);
            $output = Artisan::output();

            return [
                'success' => true,
                'output' => !empty(trim($output)) ? $output : "Rolled back {$step} migration step(s).",
            ];
        } catch (\Throwable $e) {
            Log::error("Migration rollback error: {$e->getMessage()}");
            return [
                'success' => false,
                'output' => "Rollback failed: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Delete an isolated Database Snapshot and its underlying file.
     */
    public function deleteSnapshot(string $snapshotId): bool
    {
        $snapshots = $this->getDatabaseSnapshots();
        $targetIndex = null;
        $target = null;

        foreach ($snapshots as $index => $snap) {
            if ($snap['id'] === $snapshotId) {
                $targetIndex = $index;
                $target = $snap;
                break;
            }
        }

        if ($targetIndex === null || !$target) {
            throw new Exception("Database snapshot [{$snapshotId}] not found.");
        }

        if (!empty($target['path']) && File::exists($target['path'])) {
            @unlink($target['path']);
        }

        array_splice($snapshots, $targetIndex, 1);
        $manifestFile = "{$this->backupDirectory}/snapshots-manifest.json";
        File::put($manifestFile, json_encode($snapshots, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Restore Database from a Specific Snapshot Record.
     */
    public function restoreFromSnapshot(string $snapshotId): bool
    {
        $snapshots = $this->getDatabaseSnapshots();
        $target = null;
        foreach ($snapshots as $snap) {
            if ($snap['id'] === $snapshotId) {
                $target = $snap;
                break;
            }
        }

        if (!$target || empty($target['path']) || !File::exists($target['path'])) {
            throw new Exception("Database snapshot [{$snapshotId}] file not found.");
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $sqliteDb = config('database.connections.sqlite.database');
            if ($sqliteDb) {
                File::copy($target['path'], $sqliteDb);
            }
        } else {
            $sql = File::get($target['path']);
            if (!empty($sql)) {
                DB::unprepared($sql);
            }
        }

        return true;
    }
}

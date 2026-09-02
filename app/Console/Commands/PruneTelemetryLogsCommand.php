<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - PruneTelemetryLogsCommand
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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneTelemetryLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan hoa:prune-telemetry
     *        php artisan hoa:prune-telemetry --days=60 --dry-run
     */
    protected $signature = 'hoa:prune-telemetry
                            {--days=30 : Prune records older than this many days}
                            {--dry-run : Preview how many rows would be deleted without deleting}';

    protected $description = 'Prune old OmniRoute telemetry logs and auth security logs to prevent table bloat';

    /**
     * Tables to prune with their respective timestamp column names.
     * Chunk size is tuned for shared hosting (small memory / short max_execution_time).
     */
    protected array $targets = [
        ['table' => 'omniroute_telemetry_logs', 'column' => 'created_at'],
        ['table' => 'auth_security_logs',       'column' => 'created_at'],
        ['table' => 'audit_logs',               'column' => 'created_at'],
    ];

    protected const CHUNK_SIZE = 500;

    public function handle(): int
    {
        $days    = max(7, (int) $this->option('days'));
        $dryRun  = (bool) $this->option('dry-run');
        $cutoff  = now()->subDays($days)->toDateTimeString();

        $this->info("HOA Telemetry Pruner — cutoff: {$cutoff}" . ($dryRun ? ' [DRY RUN]' : ''));
        $this->newLine();

        $totalDeleted = 0;

        foreach ($this->targets as ['table' => $table, 'column' => $col]) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
                $this->line("  <fg=yellow>SKIP</> {$table} — table does not exist");
                continue;
            }

            $count = DB::table($table)->where($col, '<', $cutoff)->count();

            if ($count === 0) {
                $this->line("  <fg=green>CLEAN</> {$table} — no eligible rows");
                continue;
            }

            if ($dryRun) {
                $this->line("  <fg=cyan>PREVIEW</> {$table} — would delete {$count} rows");
                $totalDeleted += $count;
                continue;
            }

            // Delete in chunks to stay within shared hosting execution limits
            $deleted = 0;
            do {
                $batch = DB::table($table)
                    ->where($col, '<', $cutoff)
                    ->limit(self::CHUNK_SIZE)
                    ->delete();
                $deleted += $batch;
            } while ($batch === self::CHUNK_SIZE);

            $this->line("  <fg=green>PRUNED</> {$table} — {$deleted} rows removed");
            Log::info("[HOA Prune] {$table}: {$deleted} rows older than {$days}d removed.");
            $totalDeleted += $deleted;
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete — {$totalDeleted} rows would be removed.");
        } else {
            $this->info("Pruning complete — {$totalDeleted} total rows removed.");
        }

        return self::SUCCESS;
    }
}

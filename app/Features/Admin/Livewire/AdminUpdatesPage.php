<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Updates & Rollback Page
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

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\CoreUpdateService;
use App\Features\Admin\Services\DatabaseUpdateRollbackService;
use App\Features\Admin\Services\HealthProberService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Core Updates & Rollback — HelpOfAi Studio')]
class AdminUpdatesPage extends Component
{
    public array $updateInfo = [];
    public array $versionMeta = [];
    public array $restorePoints = [];
    public array $dbSnapshots = [];
    public array $dbDetails = [];
    public array $migrationsData = [];
    public array $healthReport = [];
    public array $updateLogs = [];
    public bool $isChecking = false;
    public bool $isUpdating = false;
    public bool $isMigrating = false;
    public bool $isRollingBack = false;
    public string $activeTab = 'core'; // 'core', 'database', 'migrations', 'health'
    public ?string $feedbackMessage = null;
    public ?string $feedbackType = null;

    public function mount(
        CoreUpdateService $updateService, 
        HealthProberService $healthProber,
        DatabaseUpdateRollbackService $dbService
    ) {
        $this->refreshData($updateService, $healthProber, $dbService);
    }

    public function refreshData(
        CoreUpdateService $updateService, 
        HealthProberService $healthProber,
        DatabaseUpdateRollbackService $dbService
    ) {
        $this->updateInfo = $updateService->checkForUpdates();
        $this->versionMeta = $updateService->getVersionMetadata();
        $this->restorePoints = $updateService->getRestorePoints();
        $this->dbSnapshots = $dbService->getDatabaseSnapshots();
        $this->dbDetails = $dbService->getDatabaseDetails();
        $this->migrationsData = $dbService->getMigrationsList();
        $this->healthReport = $healthProber->probeSystem();
    }

    public function clearTerminalLogs()
    {
        $this->updateLogs = [];
    }

    public function triggerCheck(CoreUpdateService $updateService)
    {
        $this->isChecking = true;
        $this->updateInfo = $updateService->checkForUpdates();
        $this->isChecking = false;
        
        if (!empty($this->updateInfo['connection_error'])) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = $this->updateInfo['connection_error'];
        } else {
            $this->feedbackType = $this->updateInfo['has_update'] ? 'success' : 'info';
            $this->feedbackMessage = $this->updateInfo['has_update'] 
                ? "New update available: v{$this->updateInfo['latest_version']} directly from GitHub!" 
                : "You are currently running the latest version (v{$this->updateInfo['current_version']}).";
        }
    }

    public function createSnapshot(CoreUpdateService $updateService)
    {
        try {
            $rp = $updateService->createRestorePoint('Manual Admin Snapshot');
            $this->restorePoints = $updateService->getRestorePoints();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Full restore snapshot [{$rp['id']}] created successfully ({$rp['file_size_mb']} MB).";
            $this->updateLogs[] = [
                'time' => date('H:i:s'),
                'type' => 'success',
                'message' => "Manual full snapshot created: [{$rp['id']}] ({$rp['file_size_mb']} MB)",
            ];
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Failed to create snapshot: {$e->getMessage()}";
            $this->updateLogs[] = [
                'time' => date('H:i:s'),
                'type' => 'error',
                'message' => "Failed to create snapshot: {$e->getMessage()}",
            ];
        }
    }

    public function createDbSnapshot(DatabaseUpdateRollbackService $dbService)
    {
        try {
            $snap = $dbService->createDatabaseSnapshot('Manual DB Backup');
            $this->dbSnapshots = $dbService->getDatabaseSnapshots();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Database snapshot [{$snap['id']}] created successfully ({$snap['size_kb']} KB).";
            $this->updateLogs[] = [
                'time' => date('H:i:s'),
                'type' => 'success',
                'message' => "Manual database snapshot created: [{$snap['id']}] ({$snap['size_kb']} KB)",
            ];
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Failed to create DB snapshot: {$e->getMessage()}";
            $this->updateLogs[] = [
                'time' => date('H:i:s'),
                'type' => 'error',
                'message' => "Failed to create DB snapshot: {$e->getMessage()}",
            ];
        }
    }

    public function runDbMigrations(DatabaseUpdateRollbackService $dbService)
    {
        $this->isMigrating = true;
        try {
            $result = $dbService->runMigrations();
            $this->dbDetails = $dbService->getDatabaseDetails();
            $this->migrationsData = $dbService->getMigrationsList();
            $this->updateLogs = array_merge($this->updateLogs, $result['logs'] ?? []);
            $this->feedbackType = $result['success'] ? 'success' : 'error';
            $this->feedbackMessage = $result['output'];
        } finally {
            $this->isMigrating = false;
        }
    }

    public function rollbackMigrationStep(DatabaseUpdateRollbackService $dbService)
    {
        $this->isMigrating = true;
        try {
            $result = $dbService->rollbackLastMigrationBatch(1);
            $this->dbDetails = $dbService->getDatabaseDetails();
            $this->migrationsData = $dbService->getMigrationsList();
            $this->updateLogs = array_merge($this->updateLogs, $result['logs'] ?? []);
            $this->feedbackType = $result['success'] ? 'success' : 'error';
            $this->feedbackMessage = $result['output'];
        } finally {
            $this->isMigrating = false;
        }
    }

    public function downloadRestorePoint(string $restorePointId, CoreUpdateService $updateService)
    {
        $manifests = $updateService->getRestorePoints();
        $target = null;
        foreach ($manifests as $rp) {
            if ($rp['id'] === $restorePointId) {
                $target = $rp;
                break;
            }
        }

        if (!$target || empty($target['file_backup']) || !file_exists($target['file_backup'])) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Snapshot archive file not found on disk.";
            return;
        }

        return response()->download($target['file_backup'], basename($target['file_backup']), [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function deleteRestorePoint(string $restorePointId, CoreUpdateService $updateService)
    {
        try {
            $updateService->deleteRestorePoint($restorePointId);
            $this->restorePoints = $updateService->getRestorePoints();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Snapshot [{$restorePointId}] deleted successfully.";
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Failed to delete snapshot: {$e->getMessage()}";
        }
    }

    public function deleteDbSnapshot(string $snapshotId, DatabaseUpdateRollbackService $dbService)
    {
        try {
            $dbService->deleteSnapshot($snapshotId);
            $this->dbSnapshots = $dbService->getDatabaseSnapshots();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Database snapshot [{$snapshotId}] deleted successfully.";
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Failed to delete DB snapshot: {$e->getMessage()}";
        }
    }

    public function restoreDbSnapshot(string $snapshotId, DatabaseUpdateRollbackService $dbService)
    {
        try {
            $dbService->restoreFromSnapshot($snapshotId);
            $this->dbDetails = $dbService->getDatabaseDetails();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Database successfully restored from snapshot [{$snapshotId}].";
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Database restore failed: {$e->getMessage()}";
        }
    }

    public function applyUpdate(CoreUpdateService $updateService, HealthProberService $healthProber, DatabaseUpdateRollbackService $dbService)
    {
        $this->isUpdating = true;
        $this->updateLogs = [];

        try {
            $result = $updateService->executeUpdate();
            $this->updateLogs = $result['logs'] ?? [];
            $this->healthReport = $result['health'] ?? [];
            $this->restorePoints = $updateService->getRestorePoints();
            $this->dbSnapshots = $dbService->getDatabaseSnapshots();
            $this->dbDetails = $dbService->getDatabaseDetails();
            $this->versionMeta = $updateService->getVersionMetadata();
            $this->updateInfo = $updateService->checkForUpdates();

            if ($result['success']) {
                $this->feedbackType = 'success';
                $this->feedbackMessage = 'Core update applied and verified successfully!';
            } elseif ($result['rolled_back']) {
                $this->feedbackType = 'error';
                $this->feedbackMessage = 'Update failed safety verification! System automatically rolled back to working state.';
            } else {
                $this->feedbackType = 'error';
                $this->feedbackMessage = "Update failed: {$result['message']}";
            }
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Update exception: {$e->getMessage()}";
        } finally {
            $this->isUpdating = false;
        }
    }

    public function rollbackTo(string $restorePointId, CoreUpdateService $updateService, HealthProberService $healthProber, DatabaseUpdateRollbackService $dbService)
    {
        $this->isRollingBack = true;
        try {
            $updateService->rollbackToPoint($restorePointId);
            $this->restorePoints = $updateService->getRestorePoints();
            $this->dbSnapshots = $dbService->getDatabaseSnapshots();
            $this->dbDetails = $dbService->getDatabaseDetails();
            $this->versionMeta = $updateService->getVersionMetadata();
            $this->healthReport = $healthProber->probeSystem();
            $this->updateInfo = $updateService->checkForUpdates();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "System successfully rolled back to restore point [{$restorePointId}].";
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Rollback failed: {$e->getMessage()}";
        } finally {
            $this->isRollingBack = false;
        }
    }

    public function render()
    {
        return view('admin.updates');
    }
}

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
    public array $healthReport = [];
    public array $updateLogs = [];
    public bool $isChecking = false;
    public bool $isUpdating = false;
    public bool $isRollingBack = false;
    public string $activeTab = 'core'; // 'core', 'database', 'health'
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
        $this->healthReport = $healthProber->probeSystem();
    }

    public function triggerCheck(CoreUpdateService $updateService)
    {
        $this->isChecking = true;
        $this->updateInfo = $updateService->checkForUpdates();
        $this->isChecking = false;
        
        $this->feedbackType = 'info';
        $this->feedbackMessage = $this->updateInfo['has_update'] 
            ? "New update available: {$this->updateInfo['latest_version']}" 
            : 'You are currently running the latest version.';
    }

    public function createSnapshot(CoreUpdateService $updateService)
    {
        try {
            $rp = $updateService->createRestorePoint('Manual Admin Snapshot');
            $this->restorePoints = $updateService->getRestorePoints();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Full restore snapshot [{$rp['id']}] created successfully ({$rp['file_size_mb']} MB).";
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Failed to create snapshot: {$e->getMessage()}";
        }
    }

    public function createDbSnapshot(DatabaseUpdateRollbackService $dbService)
    {
        try {
            $snap = $dbService->createDatabaseSnapshot('Manual DB Backup');
            $this->dbSnapshots = $dbService->getDatabaseSnapshots();
            $this->feedbackType = 'success';
            $this->feedbackMessage = "Database snapshot [{$snap['id']}] created successfully ({$snap['size_kb']} KB).";
        } catch (\Throwable $e) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = "Failed to create DB snapshot: {$e->getMessage()}";
        }
    }

    public function runDbMigrations(DatabaseUpdateRollbackService $dbService)
    {
        $result = $dbService->runMigrations();
        $this->dbDetails = $dbService->getDatabaseDetails();
        $this->feedbackType = $result['success'] ? 'success' : 'error';
        $this->feedbackMessage = $result['output'];
    }

    public function rollbackMigrationStep(DatabaseUpdateRollbackService $dbService)
    {
        $result = $dbService->rollbackLastMigrationBatch(1);
        $this->dbDetails = $dbService->getDatabaseDetails();
        $this->feedbackType = $result['success'] ? 'success' : 'error';
        $this->feedbackMessage = $result['output'];
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

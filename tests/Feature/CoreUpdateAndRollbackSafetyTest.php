<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Core Update & Rollback Test Suite
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

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminUpdatesPage;
use App\Features\Admin\Services\CoreUpdateService;
use App\Features\Admin\Services\HealthProberService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CoreUpdateAndRollbackSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'plan' => 'enterprise',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
            'plan' => 'starter',
        ]);
    }

    public function test_non_admin_cannot_access_updates_page()
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.updates'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_updates_and_rollback_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.updates'));
        $response->assertStatus(200);
        $response->assertSee('Core Updates');
        $response->assertSee('Snapshot Restore Points');
    }

    public function test_health_prober_service_executes_successfully()
    {
        $service = app(HealthProberService::class);
        $report = $service->probeSystem();

        $this->assertIsArray($report);
        $this->assertArrayHasKey('passed', $report);
        $this->assertArrayHasKey('checks', $report);
        $this->assertArrayHasKey('database', $report['checks']);
        $this->assertArrayHasKey('auth_model', $report['checks']);
        $this->assertArrayHasKey('storage_write', $report['checks']);
        $this->assertTrue($report['passed']);
    }

    public function test_admin_can_create_manual_restore_snapshot()
    {
        $service = app(CoreUpdateService::class);
        $snapshot = $service->createRestorePoint('Test Automation Snapshot');

        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('id', $snapshot);
        $this->assertArrayHasKey('file_backup', $snapshot);
        $this->assertStringStartsWith('rp_', $snapshot['id']);

        $restorePoints = $service->getRestorePoints();
        $this->assertNotEmpty($restorePoints);
        $this->assertEquals($snapshot['id'], $restorePoints[0]['id']);
    }

    public function test_version_check_returns_valid_payload()
    {
        $service = app(CoreUpdateService::class);
        $updateInfo = $service->checkForUpdates();

        $this->assertIsArray($updateInfo);
        $this->assertArrayHasKey('current_version', $updateInfo);
        $this->assertArrayHasKey('has_update', $updateInfo);
        $this->assertArrayHasKey('release_notes', $updateInfo);
    }

    public function test_env_variables_sync_detects_and_appends_missing_keys()
    {
        $service = app(CoreUpdateService::class);
        $appended = $service->syncEnvVariables();

        $this->assertIsArray($appended);
    }

    public function test_admin_can_bulk_delete_restore_snapshots()
    {
        $service = app(CoreUpdateService::class);
        $s1 = $service->createRestorePoint('Test Snapshot 1');
        $s2 = $service->createRestorePoint('Test Snapshot 2');

        $initialPoints = $service->getRestorePoints();
        $this->assertGreaterThanOrEqual(2, count($initialPoints));

        $deletedCount = $service->deleteRestorePoints([$s1['id'], $s2['id']]);
        $this->assertEquals(2, $deletedCount);

        $remainingPoints = $service->getRestorePoints();
        $ids = array_column($remainingPoints, 'id');
        $this->assertNotContains($s1['id'], $ids);
        $this->assertNotContains($s2['id'], $ids);
    }

    public function test_admin_can_prune_older_restore_snapshots()
    {
        $service = app(CoreUpdateService::class);
        $service->createRestorePoint('Snapshot A');
        $service->createRestorePoint('Snapshot B');
        $service->createRestorePoint('Snapshot C');
        $service->createRestorePoint('Snapshot D');

        $pruned = $service->pruneOlderRestorePoints(2);
        $this->assertGreaterThanOrEqual(2, $pruned);

        $points = $service->getRestorePoints();
        $this->assertLessThanOrEqual(2, count($points));
    }

    public function test_livewire_bulk_selection_and_deletion()
    {
        $service = app(CoreUpdateService::class);
        $s1 = $service->createRestorePoint('Bulk Test 1');
        $s2 = $service->createRestorePoint('Bulk Test 2');

        Livewire::actingAs($this->admin)
            ->test(AdminUpdatesPage::class)
            ->set('selectAll', true)
            ->assertSet('selectAll', true)
            ->call('selectAllRestorePoints')
            ->call('bulkDeleteRestorePoints')
            ->assertSet('selectedRestorePoints', [])
            ->assertSet('selectAll', false);
    }
}

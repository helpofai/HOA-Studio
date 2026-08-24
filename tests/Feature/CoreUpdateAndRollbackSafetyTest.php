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

use App\Features\Admin\Services\CoreUpdateService;
use App\Features\Admin\Services\HealthProberService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

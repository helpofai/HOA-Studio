<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - System Info Test Suite
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

use App\Features\Admin\Services\SystemInfoService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSystemInfoAndDocsTest extends TestCase
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

    public function test_non_admin_cannot_access_system_info_page()
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.system-info'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_system_info_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.system-info'));
        $response->assertStatus(200);
        $response->assertSee('System Info');
        $response->assertSee('Documentation');
        $response->assertSee('Server');
        $response->assertSee('Requirements');
    }

    public function test_system_info_service_diagnostics_payload()
    {
        $service = app(SystemInfoService::class);
        $diagnostics = $service->getSystemDiagnostics();

        $this->assertIsArray($diagnostics);
        $this->assertArrayHasKey('server', $diagnostics);
        $this->assertArrayHasKey('database', $diagnostics);
        $this->assertArrayHasKey('extensions', $diagnostics);
        $this->assertArrayHasKey('permissions', $diagnostics);
        $this->assertArrayHasKey('php_limits', $diagnostics);

        $this->assertArrayHasKey('curl', $diagnostics['extensions']);
        $this->assertArrayHasKey('json', $diagnostics['extensions']);
        $this->assertArrayHasKey('pdo', $diagnostics['extensions']);
    }

    public function test_system_info_service_reads_markdown_documents()
    {
        $service = app(SystemInfoService::class);
        $docs = $service->getDocumentationFiles();

        $this->assertIsArray($docs);
        $this->assertArrayHasKey('readme', $docs);
        $this->assertArrayHasKey('changelog', $docs);
        $this->assertArrayHasKey('documents', $docs);
        $this->assertNotEmpty($docs['readme']['content_html']);
        $this->assertNotEmpty($docs['changelog']['content_html']);
    }
}

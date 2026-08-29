<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Usage Logs Feature Test
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

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;
use App\Features\Admin\Livewire\AdminUsageLogsPage;

class AdminUsageLogsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);
    }

    public function test_regular_user_cannot_access_admin_usage_logs(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.usage'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_usage_logs_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.usage'));
        $response->assertOk();
        $response->assertSee('AI Generation Audit Logs');
    }

    public function test_admin_can_filter_usage_logs_by_model_and_search(): void
    {
        DB::table('generation_usage')->insert([
            [
                'user_id' => $this->admin->id,
                'words_used' => 250,
                'tokens_used' => 1000,
                'model_slug' => 'anthropic/claude-3-7-sonnet',
                'recorded_at' => now(),
            ],
            [
                'user_id' => $this->regularUser->id,
                'words_used' => 500,
                'tokens_used' => 2000,
                'model_slug' => 'openai/gpt-4o',
                'recorded_at' => now()->subHour(),
            ],
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminUsageLogsPage::class)
            ->assertSee('anthropic/claude-3-7-sonnet')
            ->assertSee('openai/gpt-4o')
            ->set('selectedModel', 'openai/gpt-4o')
            ->assertSee('openai/gpt-4o')
            ->set('selectedModel', '')
            ->set('search', $this->admin->email)
            ->assertSee('anthropic/claude-3-7-sonnet');
    }
}

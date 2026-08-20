<?php

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminDashboardPage;
use App\Features\Admin\Livewire\AdminSettingsPage;
use App\Features\Admin\Livewire\AdminUsageLogsPage;
use App\Features\Admin\Livewire\AdminUsersPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_for_admin_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(AdminDashboardPage::class)
            ->assertStatus(200)
            ->assertSee('System Overview')
            ->assertSee('Total Registered Users')
            ->assertSee('AI Words Consumed');
    }

    public function test_admin_routes_block_standard_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $this->get('/admin')->assertStatus(403);
        $this->get('/admin/users')->assertStatus(403);
        $this->get('/admin/usage')->assertStatus(403);
        $this->get('/admin/settings')->assertStatus(403);
    }

    public function test_admin_can_update_user_role_and_word_quota(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'role' => 'user',
            'plan' => 'starter',
            'monthly_word_quota' => 15000,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminUsersPage::class)
            ->call('openEditModal', $targetUser->id)
            ->set('role', 'pro')
            ->set('plan', 'pro')
            ->set('monthly_word_quota', 100000)
            ->call('saveUser');

        $targetUser->refresh();
        $this->assertEquals('pro', $targetUser->role);
        $this->assertEquals('pro', $targetUser->plan);
        $this->assertEquals(100000, $targetUser->monthly_word_quota);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin);

        Livewire::test(AdminUsersPage::class)
            ->call('toggleActive', $targetUser->id);

        $this->assertFalse($targetUser->fresh()->is_active);

        Livewire::test(AdminUsersPage::class)
            ->call('toggleActive', $targetUser->id);

        $this->assertTrue($targetUser->fresh()->is_active);
    }

    public function test_admin_can_save_system_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(AdminSettingsPage::class)
            ->set('site_name', 'HelpOfAi Studio Enterprise')
            ->set('starter_quota', 20000)
            ->set('gateway_url', 'http://127.0.0.1:20128')
            ->call('saveSettings');

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'HelpOfAi Studio Enterprise',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'starter_quota',
            'value' => '20000',
        ]);
    }

    public function test_admin_usage_logs_page_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        DB::table('generation_usage')->insert([
            'user_id' => $admin->id,
            'words_used' => 500,
            'tokens_used' => 650,
            'model_slug' => 'deepseek/deepseek-chat',
            'recorded_at' => now(),
        ]);

        Livewire::test(AdminUsageLogsPage::class)
            ->assertStatus(200)
            ->assertSee('AI Generation Audit Logs')
            ->assertSee('deepseek/deepseek-chat')
            ->assertSee('500 words');
    }
}
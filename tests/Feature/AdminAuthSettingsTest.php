<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Auth & Security Feature Test
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

use App\Features\Admin\Livewire\AdminAuthSettingsPage;
use App\Features\Admin\Models\AuthSecurityLog;
use App\Features\Admin\Models\BlockedIp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_auth_settings_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.auth-settings'));
        $response->assertStatus(200);
        $response->assertSee('hoa-auth-settings-container', false);
    }

    public function test_non_admin_cannot_access_auth_settings_page()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('admin.auth-settings'));
        $response->assertStatus(403);
    }

    public function test_admin_can_block_and_unblock_ip()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(AdminAuthSettingsPage::class)
            ->set('new_block_ip', '198.51.100.25')
            ->set('new_block_reason', 'Known malicious proxy')
            ->set('new_block_duration', '24_hours')
            ->call('blockIp')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('blocked_ips', [
            'ip_address' => '198.51.100.25',
            'reason' => 'Known malicious proxy',
        ]);

        $block = BlockedIp::where('ip_address', '198.51.100.25')->first();

        Livewire::actingAs($admin)
            ->test(AdminAuthSettingsPage::class)
            ->call('unblockIp', $block->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('blocked_ips', [
            'ip_address' => '198.51.100.25',
        ]);
    }

    public function test_admin_can_toggle_user_ban()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $victim = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminAuthSettingsPage::class)
            ->call('toggleUserBan', $victim->id)
            ->assertHasNoErrors();

        $this->assertFalse($victim->fresh()->is_active);

        // Toggle back to active
        Livewire::actingAs($admin)
            ->test(AdminAuthSettingsPage::class)
            ->call('toggleUserBan', $victim->id)
            ->assertHasNoErrors();

        $this->assertTrue($victim->fresh()->is_active);
    }

    public function test_admin_can_update_turnstile_and_honeypot_configuration()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(AdminAuthSettingsPage::class)
            ->set('enableTurnstile', true)
            ->set('turnstileSiteKey', '0x4AAAAAAA_TEST_SITE_KEY')
            ->set('turnstileSecretKey', '0x4AAAAAAA_TEST_SECRET_KEY')
            ->set('enableHoneypot', true)
            ->call('saveSecurityConfig')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'turnstile_site_key',
            'value' => '0x4AAAAAAA_TEST_SITE_KEY',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'turnstile_enabled',
            'value' => '0',
        ]);
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin User Management Test Suite
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

use App\Features\Admin\Livewire\AdminUsersPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Master Admin',
            'email' => 'admin@helpofai.com',
            'role' => 'admin',
            'plan' => 'enterprise',
        ]);

        $this->targetUser = User::factory()->create([
            'name' => 'John Creator',
            'email' => 'john@creator.com',
            'role' => 'user',
            'plan' => 'starter',
            'monthly_word_quota' => 15000,
            'used_word_quota' => 2000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_users_list()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users'));
        $response->assertStatus(200);
        $response->assertSee('User &amp; Quota Management', false);
        $response->assertSee('John Creator');
    }

    public function test_admin_can_create_new_user()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->set('new_user_name', 'Sarah Pro')
            ->set('new_user_email', 'sarah@pro.com')
            ->set('new_user_password', 'password123')
            ->set('new_user_role', 'pro')
            ->set('new_user_plan', 'pro')
            ->set('new_user_quota', 50000)
            ->call('createUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'sarah@pro.com',
            'role' => 'pro',
            'plan' => 'pro',
            'monthly_word_quota' => 50000,
        ]);
    }

    public function test_admin_can_update_user_details_and_quota()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->call('openEditModal', $this->targetUser->id)
            ->set('name', 'John Updated')
            ->set('role', 'editor')
            ->set('plan', 'enterprise')
            ->set('monthly_word_quota', 100000)
            ->set('used_word_quota', 500)
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->targetUser->refresh();
        $this->assertEquals('John Updated', $this->targetUser->name);
        $this->assertEquals('editor', $this->targetUser->role);
        $this->assertEquals('enterprise', $this->targetUser->plan);
        $this->assertEquals(100000, $this->targetUser->monthly_word_quota);
    }

    public function test_admin_can_grant_bonus_quota()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->call('grantBonusQuota', $this->targetUser->id, 10000);

        $this->targetUser->refresh();
        $this->assertEquals(10000, $this->targetUser->bonus_word_quota);
    }

    public function test_admin_can_toggle_user_active_status()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->call('toggleActive', $this->targetUser->id);

        $this->targetUser->refresh();
        $this->assertFalse($this->targetUser->is_active);
    }

    public function test_admin_can_delete_user()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->call('deleteUser', $this->targetUser->id);

        $this->assertDatabaseMissing('users', [
            'id' => $this->targetUser->id,
        ]);
    }
}

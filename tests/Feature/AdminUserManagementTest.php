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
            'email' => 'admin_' . uniqid() . '@helpofai.com',
            'role' => 'admin',
            'plan' => 'enterprise',
        ]);

        $this->targetUser = User::factory()->create([
            'name' => 'John Creator',
            'email' => 'john_' . uniqid() . '@creator.com',
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

    public function test_non_admin_cannot_access_user_management()
    {
        $response = $this->actingAs($this->targetUser)->get(route('admin.users'));
        $response->assertStatus(403);
    }

    public function test_admin_can_use_bulk_selection_and_grant_bonus()
    {
        $user2 = User::factory()->create([
            'role' => 'user',
            'plan' => 'starter',
            'bonus_word_quota' => 0,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->set('selectedUsers', [$this->targetUser->id, $user2->id])
            ->call('bulkGrantBonus', 25000)
            ->assertHasNoErrors();

        $this->targetUser->refresh();
        $user2->refresh();

        $this->assertEquals(25000, $this->targetUser->bonus_word_quota);
        $this->assertEquals(25000, $user2->bonus_word_quota);
    }

    public function test_admin_can_bulk_assign_role_and_plan()
    {
        $user2 = User::factory()->create([
            'role' => 'user',
            'plan' => 'starter',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->set('selectedUsers', [$this->targetUser->id, $user2->id])
            ->call('bulkAssignRole', 'pro')
            ->set('selectedUsers', [$this->targetUser->id, $user2->id])
            ->call('bulkChangePlan', 'pro')
            ->assertHasNoErrors();

        $this->targetUser->refresh();
        $user2->refresh();

        $this->assertEquals('pro', $this->targetUser->role);
        $this->assertEquals('pro', $this->targetUser->plan);
        $this->assertEquals('pro', $user2->role);
        $this->assertEquals('pro', $user2->plan);
    }

    public function test_admin_can_bulk_reset_used_quota()
    {
        $this->targetUser->update(['used_word_quota' => 8500]);

        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->set('selectedUsers', [$this->targetUser->id])
            ->call('bulkResetUsedQuota')
            ->assertHasNoErrors();

        $this->targetUser->refresh();
        $this->assertEquals(0, $this->targetUser->used_word_quota);
    }

    public function test_admin_cannot_delete_self_in_bulk_delete()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->set('selectedUsers', [$this->admin->id, $this->targetUser->id])
            ->call('bulkDeleteUsers')
            ->assertHasNoErrors();

        // Admin must still exist
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        // Target user is deleted
        $this->assertDatabaseMissing('users', ['id' => $this->targetUser->id]);
    }

    public function test_admin_can_view_roles_matrix_tab()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->set('activeTab', 'roles')
            ->assertSee('Roles, Access Limits')
            ->assertSee('Administrator')
            ->assertSee('Editor')
            ->assertSee('Pro')
            ->assertSee('User')
            ->assertSee('Member');
    }

    public function test_admin_can_export_users_csv()
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(AdminUsersPage::class)
            ->call('exportSelectedCsv');

        $this->assertNotNull($component);
    }

    public function test_admin_can_export_users_json()
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(AdminUsersPage::class)
            ->call('exportSelectedJson');

        $this->assertNotNull($component);
    }

    public function test_admin_can_toggle_select_all_and_user_selection()
    {
        $this->actingAs($this->admin);

        Livewire::test(AdminUsersPage::class)
            ->call('toggleUserSelection', $this->targetUser->id)
            ->assertSet('selectedUsers', [$this->targetUser->id])
            ->call('toggleUserSelection', $this->targetUser->id)
            ->assertSet('selectedUsers', [])
            ->call('toggleSelectAll')
            ->assertSet('selectAll', true);
    }
}



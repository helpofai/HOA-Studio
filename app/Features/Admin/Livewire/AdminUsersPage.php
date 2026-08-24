<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Actions\UpdateUserQuotaAndRole;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('User & Quota Management — HelpOfAi Studio')]
class AdminUsersPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedRole = '';
    public string $selectedPlan = '';

    // Modal state
    public bool $showEditModal = false;
    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'user';
    public string $plan = 'starter';
    public int $monthly_word_quota = 15000;
    public int $used_word_quota = 0;
    public bool $is_active = true;
    public string $new_password = '';

    // Create Modal state
    public bool $showCreateModal = false;
    public string $new_user_name = '';
    public string $new_user_email = '';
    public string $new_user_password = '';
    public string $new_user_role = 'user';
    public string $new_user_plan = 'starter';
    public int $new_user_quota = 15000;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->new_user_name = '';
        $this->new_user_email = '';
        $this->new_user_password = '';
        $this->new_user_role = 'user';
        $this->new_user_plan = 'starter';
        $this->new_user_quota = 15000;
        $this->showCreateModal = true;
    }

    public function createUser()
    {
        $this->validate([
            'new_user_name' => 'required|string|min:2|max:100',
            'new_user_email' => 'required|email|max:255|unique:users,email',
            'new_user_password' => 'required|string|min:8',
            'new_user_role' => 'required|string|in:' . implode(',', UserRole::values()),
            'new_user_plan' => 'required|string',
            'new_user_quota' => 'required|integer|min:0',
        ]);

        $user = User::create([
            'name' => $this->new_user_name,
            'email' => $this->new_user_email,
            'password' => $this->new_user_password,
            'role' => $this->new_user_role,
            'plan' => $this->new_user_plan,
            'monthly_word_quota' => $this->new_user_quota,
            'used_word_quota' => 0,
            'bonus_word_quota' => 0,
            'is_active' => true,
        ]);

        $this->showCreateModal = false;
        session()->flash('status', "New user '{$user->name}' created successfully.");
    }

    public function openEditModal(int $userId)
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->plan = $user->plan;
        $this->monthly_word_quota = $user->monthly_word_quota;
        $this->used_word_quota = $user->used_word_quota;
        $this->is_active = $user->is_active;
        $this->new_password = '';
        $this->showEditModal = true;
    }

    public function saveUser(UpdateUserQuotaAndRole $action)
    {
        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $this->editingUserId,
            'role' => 'required|string|in:' . implode(',', UserRole::values()),
            'plan' => 'required|string',
            'monthly_word_quota' => 'required|integer|min:0',
            'used_word_quota' => 'required|integer|min:0',
        ]);

        $user = User::findOrFail($this->editingUserId);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'plan' => $this->plan,
            'monthly_word_quota' => $this->monthly_word_quota,
            'used_word_quota' => $this->used_word_quota,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->new_password)) {
            $data['password'] = $this->new_password;
        }

        $action->execute($user, $data);

        $this->showEditModal = false;
        session()->flash('status', "User '{$user->name}' updated successfully.");
    }

    public function deleteUser(int $userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own logged-in admin account.');
            return;
        }

        $userName = $user->name;
        $user->delete();

        session()->flash('status', "User '{$userName}' has been permanently removed.");
    }

    public function toggleActive(int $userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot deactivate your own admin account.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        session()->flash('status', "User '{$user->name}' status toggled to " . ($user->is_active ? 'Active' : 'Inactive') . ".");
    }

    public function grantBonusQuota(int $userId, int $amount)
    {
        $user = User::findOrFail($userId);
        $user->bonus_word_quota = ($user->bonus_word_quota ?? 0) + $amount;
        $user->save();

        session()->flash('status', "Granted +" . number_format($amount) . " bonus words to '{$user->name}'.");
    }

    public function render()
    {
        $query = User::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedRole)) {
            $query->where('role', $this->selectedRole);
        }

        if (!empty($this->selectedPlan)) {
            $query->where('plan', $this->selectedPlan);
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }
}
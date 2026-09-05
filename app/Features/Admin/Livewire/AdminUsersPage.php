<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin User & Quota Management
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

use App\Features\Admin\Actions\UpdateUserQuotaAndRole;
use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('User & Quota Management — HelpOfAi Studio')]
class AdminUsersPage extends Component
{
    use WithPagination;

    // View Navigation
    public string $activeTab = 'users'; // 'users', 'roles'

    // Filtering, Search & Sorting
    public string $search = '';
    public string $selectedRole = '';
    public string $selectedPlan = '';
    public string $selectedStatus = ''; // '', 'active', 'inactive', 'zero_quota', 'unverified'
    public string $sortBy = 'latest'; // 'latest', 'oldest', 'name_asc', 'quota_high', 'quota_low', 'words_used'
    public int $perPage = 15;

    // Bulk Selection State
    public array $selectedUsers = [];
    public bool $selectAll = false;

    // Edit User Modal State
    public bool $showEditModal = false;
    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'user';
    public string $plan = 'starter';
    public int $monthly_word_quota = 15000;
    public int $used_word_quota = 0;
    public int $bonus_word_quota = 0;
    public bool $is_active = true;
    public bool $email_verified = true;
    public string $new_password = '';
    public ?string $user_created_at = null;
    public string $editActiveTab = 'profile'; // 'profile', 'role', 'quota', 'security'

    // Create User Modal State
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

    public function updatingSelectedRole()
    {
        $this->resetPage();
    }

    public function updatingSelectedPlan()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedRole = '';
        $this->selectedPlan = '';
        $this->selectedStatus = '';
        $this->sortBy = 'latest';
        $this->resetPage();
    }

    public function filterByRole(string $role)
    {
        $this->activeTab = 'users';
        $this->selectedRole = $role;
        $this->resetPage();
    }

    // ==========================================
    // Bulk Selection Suite
    // ==========================================

    public function toggleSelectAll(): void
    {
        $pageIds = $this->getCurrentPageUserIds();
        if (empty($pageIds)) {
            return;
        }

        $allSelected = count(array_intersect($pageIds, $this->selectedUsers)) === count($pageIds);

        if ($allSelected) {
            $this->selectedUsers = array_values(array_diff($this->selectedUsers, $pageIds));
            $this->selectAll = false;
        } else {
            $this->selectedUsers = array_values(array_unique(array_merge($this->selectedUsers, $pageIds)));
            $this->selectAll = true;
        }
    }

    public function toggleUserSelection(int $userId): void
    {
        if (in_array($userId, $this->selectedUsers)) {
            $this->selectedUsers = array_values(array_diff($this->selectedUsers, [$userId]));
        } else {
            $this->selectedUsers[] = $userId;
        }

        $pageIds = $this->getCurrentPageUserIds();
        $this->selectAll = (!empty($pageIds) && count(array_intersect($pageIds, $this->selectedUsers)) === count($pageIds));
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->getCurrentPageUserIds();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $pageIds = $this->getCurrentPageUserIds();
        $this->selectAll = (!empty($pageIds) && count(array_intersect($pageIds, $this->selectedUsers)) === count($pageIds));
    }

    public function selectAllOnPage()
    {
        $this->selectedUsers = array_values(array_unique(array_merge($this->selectedUsers, $this->getCurrentPageUserIds())));
        $this->selectAll = true;
    }

    public function selectAllMatching()
    {
        $this->selectedUsers = $this->buildFilteredQuery()->pluck('id')->map(fn($id) => (int)$id)->all();
        $this->selectAll = true;
    }

    public function selectAdmins()
    {
        $this->selectedUsers = User::where('role', 'admin')->pluck('id')->map(fn($id) => (int)$id)->all();
        $this->selectAll = false;
    }

    public function selectInactive()
    {
        $this->selectedUsers = User::where('is_active', false)->pluck('id')->map(fn($id) => (int)$id)->all();
        $this->selectAll = false;
    }

    public function selectZeroQuota()
    {
        $this->selectedUsers = User::whereRaw('((monthly_word_quota + bonus_word_quota) - used_word_quota) <= 0')->pluck('id')->map(fn($id) => (int)$id)->all();
        $this->selectAll = false;
    }

    public function invertSelection()
    {
        $pageIds = $this->getCurrentPageUserIds();
        $newSelection = array_diff($pageIds, $this->selectedUsers);
        $this->selectedUsers = array_values($newSelection);
        $this->selectAll = (count($this->selectedUsers) === count($pageIds) && count($pageIds) > 0);
    }

    public function clearSelection()
    {
        $this->selectedUsers = [];
        $this->selectAll = false;
    }

    // ==========================================
    // Bulk Actions Operations
    // ==========================================

    public function bulkAssignRole(string $newRole)
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        if (!in_array($newRole, UserRole::values(), true)) {
            session()->flash('error', 'Invalid role specified.');
            return;
        }

        // Safety: Do not downgrade logged-in admin
        $targetIds = array_filter($this->selectedUsers, fn($id) => (int)$id !== (int)Auth::id() || $newRole === 'admin');

        $updated = User::whereIn('id', $targetIds)->update(['role' => $newRole]);
        $this->clearSelection();

        session()->flash('status', "Role updated to '" . ucfirst($newRole) . "' for {$updated} user account(s).");
    }

    public function bulkChangePlan(string $newPlan)
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        $validPlans = ['starter', 'pro', 'enterprise'];
        if (!in_array($newPlan, $validPlans, true)) {
            session()->flash('error', 'Invalid plan specified.');
            return;
        }

        $defaultQuota = match($newPlan) {
            'enterprise' => 500000,
            'pro' => 100000,
            default => 15000,
        };

        $updated = User::whereIn('id', $this->selectedUsers)->update([
            'plan' => $newPlan,
            'monthly_word_quota' => $defaultQuota,
        ]);
        $this->clearSelection();

        session()->flash('status', "Subscription plan updated to '" . strtoupper($newPlan) . "' with " . number_format($defaultQuota) . " words for {$updated} user(s).");
    }

    public function bulkGrantBonus(int $amount)
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        $updated = User::whereIn('id', $this->selectedUsers)->increment('bonus_word_quota', $amount);
        $this->clearSelection();

        session()->flash('status', "Granted +" . number_format($amount) . " bonus words to {$updated} user(s).");
    }

    public function bulkSetQuota(int $quota)
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        $updated = User::whereIn('id', $this->selectedUsers)->update([
            'monthly_word_quota' => max(0, $quota),
        ]);
        $this->clearSelection();

        session()->flash('status', "Monthly word quota updated to " . number_format($quota) . " words for {$updated} user(s).");
    }

    public function bulkResetUsedQuota()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        $updated = User::whereIn('id', $this->selectedUsers)->update(['used_word_quota' => 0]);
        $this->clearSelection();

        session()->flash('status', "Consumed word usage successfully reset to 0 for {$updated} user(s).");
    }

    public function bulkToggleActive(bool $status)
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        // Safety: Do not deactivate logged-in admin account
        $targetIds = array_filter($this->selectedUsers, fn($id) => (int)$id !== (int)Auth::id() || $status === true);

        $updated = User::whereIn('id', $targetIds)->update(['is_active' => $status]);
        $this->clearSelection();

        $label = $status ? 'Activated' : 'Suspended';
        session()->flash('status', "{$updated} user account(s) have been {$label}.");
    }

    public function bulkVerifyEmail()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        $updated = User::whereIn('id', $this->selectedUsers)->whereNull('email_verified_at')->update([
            'email_verified_at' => now(),
        ]);
        $this->clearSelection();

        session()->flash('status', "Marked {$updated} user email(s) as verified.");
    }

    public function bulkDeleteUsers()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        // Safety: Strictly exclude currently authenticated user
        $targetIds = array_filter($this->selectedUsers, fn($id) => (int)$id !== (int)Auth::id());

        if (empty($targetIds)) {
            session()->flash('error', 'You cannot delete your own logged-in admin account.');
            return;
        }

        $deleted = User::whereIn('id', $targetIds)->delete();
        $this->clearSelection();

        session()->flash('status', "Permanently removed {$deleted} user account(s).");
    }

    // ==========================================
    // Export Suite (CSV & JSON)
    // ==========================================

    public function exportSelectedCsv()
    {
        $userIds = !empty($this->selectedUsers) ? $this->selectedUsers : $this->buildFilteredQuery()->pluck('id')->all();
        $users = User::whereIn('id', $userIds)->orderBy('name')->get();

        $fileName = 'users_export_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'User ID',
                'Full Name',
                'Email Address',
                'Role',
                'Plan',
                'Monthly Quota (Words)',
                'Used Quota (Words)',
                'Bonus Quota (Words)',
                'Remaining Quota (Words)',
                'Status',
                'Email Verified',
                'Created At',
            ]);

            foreach ($users as $u) {
                $rem = max(0, ($u->monthly_word_quota + ($u->bonus_word_quota ?? 0)) - $u->used_word_quota);
                fputcsv($out, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->role,
                    $u->plan,
                    $u->monthly_word_quota,
                    $u->used_word_quota,
                    $u->bonus_word_quota ?? 0,
                    $rem,
                    $u->is_active ? 'Active' : 'Banned',
                    $u->email_verified_at ? 'Verified' : 'Unverified',
                    $u->created_at?->toIso8601String(),
                ]);
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function exportSelectedJson()
    {
        $userIds = !empty($this->selectedUsers) ? $this->selectedUsers : $this->buildFilteredQuery()->pluck('id')->all();
        $users = User::whereIn('id', $userIds)->orderBy('name')->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'plan' => $u->plan,
                'monthly_word_quota' => $u->monthly_word_quota,
                'used_word_quota' => $u->used_word_quota,
                'bonus_word_quota' => $u->bonus_word_quota ?? 0,
                'remaining_quota' => max(0, ($u->monthly_word_quota + ($u->bonus_word_quota ?? 0)) - $u->used_word_quota),
                'is_active' => (bool)$u->is_active,
                'email_verified' => (bool)$u->email_verified_at,
                'created_at' => $u->created_at?->toIso8601String(),
            ];
        });

        $fileName = 'users_export_' . date('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($users) {
            echo json_encode($users, JSON_PRETTY_PRINT);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    // ==========================================
    // Upgraded Edit Box Modal
    // ==========================================

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
        $this->bonus_word_quota = $user->bonus_word_quota ?? 0;
        $this->is_active = (bool)$user->is_active;
        $this->email_verified = !empty($user->email_verified_at);
        $this->user_created_at = $user->created_at?->format('M d, Y H:i');
        $this->new_password = '';
        $this->editActiveTab = 'profile';
        $this->showEditModal = true;
    }

    public function modalGrantBonus(int $amount)
    {
        $this->bonus_word_quota += $amount;
    }

    public function modalResetUsed()
    {
        $this->used_word_quota = 0;
    }

    public function modalSetPlanQuota(string $plan)
    {
        $this->plan = $plan;
        $this->monthly_word_quota = match($plan) {
            'enterprise' => 500000,
            'pro' => 100000,
            default => 15000,
        };
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
            'bonus_word_quota' => 'required|integer|min:0',
        ]);

        $user = User::findOrFail($this->editingUserId);

        // Safety: Do not allow deactivating or removing admin role from own logged-in account
        if ($user->id === Auth::id()) {
            $this->is_active = true;
            $this->role = 'admin';
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'plan' => $this->plan,
            'monthly_word_quota' => $this->monthly_word_quota,
            'used_word_quota' => $this->used_word_quota,
            'bonus_word_quota' => $this->bonus_word_quota,
            'is_active' => $this->is_active,
            'email_verified' => $this->email_verified,
        ];

        if (!empty($this->new_password)) {
            $data['password'] = $this->new_password;
        }

        $action->execute($user, $data);

        $this->showEditModal = false;
        session()->flash('status', "User '{$user->name}' updated successfully.");
    }

    // ==========================================
    // Create User Modal
    // ==========================================

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
            'email_verified_at' => now(),
        ]);

        // Send Account Details Email with credentials
        try {
            Mail::to($user->email)->send(
                new \App\Features\Admin\Mail\TemplateMailable('account_details', [
                    '{user_name}' => $user->name,
                    '{user_email}' => $user->email,
                    '{user_role}' => strtoupper($user->role),
                    '{plan_name}' => strtoupper($user->plan),
                    '{monthly_words}' => number_format($user->monthly_word_quota),
                    '{temporary_password}' => $this->new_user_password,
                ])
            );
        } catch (\Throwable $e) {}

        $this->showCreateModal = false;
        session()->flash('status', "New user '{$user->name}' created successfully and welcome credentials dispatched.");
    }

    // ==========================================
    // Single User Quick Row Actions
    // ==========================================

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

        try {
            $template = $user->is_active ? 'account_unbanned' : 'account_banned';
            Mail::to($user->email)->send(
                new \App\Features\Admin\Mail\TemplateMailable($template, [
                    '{user_name}' => $user->name,
                    '{ban_reason}' => 'Administrative review or policy suspension',
                    '{timestamp}' => now()->toDayDateTimeString(),
                ])
            );
        } catch (\Throwable $e) {}

        session()->flash('status', "User '{$user->name}' status toggled to " . ($user->is_active ? 'Active' : 'Inactive') . ".");
    }

    public function grantBonusQuota(int $userId, int $amount)
    {
        $user = User::findOrFail($userId);
        $user->bonus_word_quota = ($user->bonus_word_quota ?? 0) + $amount;
        $user->save();

        try {
            Mail::to($user->email)->send(
                new \App\Features\Admin\Mail\TemplateMailable('plan_upgraded', [
                    '{user_name}' => $user->name,
                    '{new_plan}' => strtoupper($user->plan),
                    '{monthly_words}' => number_format($user->monthly_word_quota),
                    '{bonus_words}' => number_format($amount),
                ])
            );
        } catch (\Throwable $e) {}

        session()->flash('status', "Granted +" . number_format($amount) . " bonus words to '{$user->name}'.");
    }

    public function resetUserQuota(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->used_word_quota = 0;
        $user->save();

        session()->flash('status', "Reset consumed words to 0 for '{$user->name}'.");
    }

    public function markEmailVerified(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->email_verified_at = now();
        $user->save();

        session()->flash('status', "Email verified for '{$user->name}'.");
    }

    public function resendWelcomeEmail(int $userId)
    {
        $user = User::findOrFail($userId);

        try {
            Mail::to($user->email)->send(
                new \App\Features\Admin\Mail\TemplateMailable('account_details', [
                    '{user_name}' => $user->name,
                    '{user_email}' => $user->email,
                    '{user_role}' => strtoupper($user->role),
                    '{plan_name}' => strtoupper($user->plan),
                    '{monthly_words}' => number_format($user->monthly_word_quota),
                    '{temporary_password}' => 'Contact administrator if reset is needed',
                ])
            );
            session()->flash('status', "Welcome and credentials email resent to {$user->email}.");
        } catch (\Throwable $e) {
            session()->flash('error', "Failed to dispatch email: {$e->getMessage()}");
        }
    }

    // ==========================================
    // Internal Query Builders
    // ==========================================

    protected function buildFilteredQuery()
    {
        $query = User::query();

        if (!empty($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('email', 'like', '%' . $term . '%')
                  ->orWhere('id', $term);
            });
        }

        if (!empty($this->selectedRole)) {
            $query->where('role', $this->selectedRole);
        }

        if (!empty($this->selectedPlan)) {
            $query->where('plan', $this->selectedPlan);
        }

        if ($this->selectedStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->selectedStatus === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->selectedStatus === 'zero_quota') {
            $query->whereRaw('((monthly_word_quota + bonus_word_quota) - used_word_quota) <= 0');
        } elseif ($this->selectedStatus === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        match ($this->sortBy) {
            'oldest' => $query->oldest('id'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'quota_high' => $query->orderBy('monthly_word_quota', 'desc'),
            'quota_low' => $query->orderBy('monthly_word_quota', 'asc'),
            'words_used' => $query->orderBy('used_word_quota', 'desc'),
            default => $query->latest('id'),
        };

        return $query;
    }

    protected function getCurrentPageUserIds(): array
    {
        return $this->buildFilteredQuery()
            ->paginate($this->perPage)
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    // ==========================================
    // Render Lifecycle
    // ==========================================

    public function render()
    {
        $users = $this->buildFilteredQuery()->paginate($this->perPage);

        // Global KPI Stats for Top Overview Cards
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'banned_users' => User::where('is_active', false)->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'editor_pro_count' => User::whereIn('role', ['editor', 'pro'])->count(),
            'total_quota_allocated' => (int) User::sum('monthly_word_quota'),
            'total_quota_used' => (int) User::sum('used_word_quota'),
            'unverified_count' => User::whereNull('email_verified_at')->count(),
        ];

        // Role Distribution & Permissions Matrix Data
        $rolesMatrix = collect(UserRole::cases())->map(function ($roleEnum) {
            $roleValue = $roleEnum->value;
            $count = User::where('role', $roleValue)->count();
            $allocatedWords = (int) User::where('role', $roleValue)->sum('monthly_word_quota');
            $usedWords = (int) User::where('role', $roleValue)->sum('used_word_quota');

            $capabilities = match($roleValue) {
                'admin' => [
                    'Universal super-administrator access',
                    'Core System Updates & Automated Rollback',
                    'Global AI Model & OmniRoute Gateway Routing',
                    'User Management & Word Quota Engine',
                    'System Health & Diagnostic Telemetry Probes',
                    'Email SMTP Configuration & Dispatcher',
                ],
                'editor' => [
                    'Publishing & Editorial Management',
                    'Unlimited TipTap Document Production',
                    'Custom AI System Prompts & Writing Intelligence',
                    'Multi-Format Document AST & DOCX Exports',
                    'Public Document Sharing & Team Links',
                ],
                'pro' => [
                    'High-Throughput AI Content Generation',
                    'DeepSeek R1 / OpenAI o1 Reasoning Access',
                    'TipTap Enterprise Extensions Suite',
                    'Custom BYOK Gateway API Key Integration',
                    'Priority Token Generation Routing',
                ],
                'user' => [
                    'Standard AI Document Studio Canvas',
                    'Core LLM Generation & Paragraph Polish',
                    'Monthly Quota Tracking & Word Metering',
                    'Rich Text & Markdown Clipboard Operations',
                ],
                'member' => [
                    'Team Workspace Collaboration Access',
                    'Shared Document Reading & Editing',
                    'Word Quota Pool Allocation',
                ],
            };

            $defaultQuota = match($roleValue) {
                'admin' => 1000000,
                'editor' => 250000,
                'pro' => 100000,
                'user' => 15000,
                'member' => 5000,
            };

            $defaultPlan = match($roleValue) {
                'admin' => 'Enterprise',
                'editor' => 'Enterprise',
                'pro' => 'Pro',
                default => 'Starter',
            };

            return [
                'enum' => $roleEnum,
                'key' => $roleValue,
                'label' => $roleEnum->label(),
                'badgeVariant' => $roleEnum->badgeVariant(),
                'userCount' => $count,
                'allocatedWords' => $allocatedWords,
                'usedWords' => $usedWords,
                'capabilities' => $capabilities,
                'defaultQuota' => $defaultQuota,
                'defaultPlan' => $defaultPlan,
            ];
        });

        return view('admin.users', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'stats' => $stats,
            'rolesMatrix' => $rolesMatrix,
        ]);
    }
}
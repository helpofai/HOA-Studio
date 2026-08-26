<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Auth & Security Control Center
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

use App\Features\Admin\Models\AuthSecurityLog;
use App\Features\Admin\Models\BlockedIp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Auth & Security Settings — HelpOfAi Studio')]
class AdminAuthSettingsPage extends Component
{
    use WithPagination;

    // Filter & Tab state
    public string $activeTab = 'overview'; // 'overview', 'security_logs', 'blocked_ips', 'banned_users', 'online_users', 'config'
    public string $searchLog = '';
    public string $eventFilter = '';
    public string $searchIp = '';
    public string $searchUser = '';

    // Manual IP Block Modal
    public bool $showBlockIpModal = false;
    public string $new_block_ip = '';
    public string $new_block_reason = '';
    public string $new_block_duration = '24_hours'; // '1_hour', '24_hours', '7_days', 'permanent'

    // Auth & Rate Limiting Config settings
    public int $maxLoginAttemptsPerIp = 10;
    public int $maxAccountAttempts = 5;
    public int $lockoutDurationMinutes = 5;
    public int $autoBlockThreshold = 15;
    public int $autoBlockHours = 24;
    public int $maxRegistrationsPerHour = 3;
    public bool $enableHoneypot = true;
    public bool $enableTurnstile = false;
    public string $turnstileSiteKey = '';
    public string $turnstileSecretKey = '';

    public function mount()
    {
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();

        $this->enableTurnstile = isset($settings['turnstile_enabled']) ? (bool) $settings['turnstile_enabled'] : (!empty(config('services.turnstile.site_key')) && !empty(config('services.turnstile.secret_key')));
        $this->turnstileSiteKey = $settings['turnstile_site_key'] ?? config('services.turnstile.site_key', '');
        $this->turnstileSecretKey = $settings['turnstile_secret_key'] ?? config('services.turnstile.secret_key', '');
        $this->enableHoneypot = isset($settings['honeypot_enabled']) ? (bool) $settings['honeypot_enabled'] : true;

        // Rate limit & Throttle settings
        $this->maxLoginAttemptsPerIp = isset($settings['auth_max_ip_attempts']) ? (int) $settings['auth_max_ip_attempts'] : 10;
        $this->maxAccountAttempts = isset($settings['auth_max_account_attempts']) ? (int) $settings['auth_max_account_attempts'] : 5;
        $this->lockoutDurationMinutes = isset($settings['auth_lockout_minutes']) ? (int) $settings['auth_lockout_minutes'] : 5;
        $this->autoBlockThreshold = isset($settings['auth_autoblock_threshold']) ? (int) $settings['auth_autoblock_threshold'] : 15;
        $this->autoBlockHours = isset($settings['auth_autoblock_hours']) ? (int) $settings['auth_autoblock_hours'] : 24;
        $this->maxRegistrationsPerHour = isset($settings['auth_max_reg_per_hour']) ? (int) $settings['auth_max_reg_per_hour'] : 3;
    }

    public function saveSecurityConfig()
    {
        $this->validate([
            'turnstileSiteKey' => 'nullable|string|max:255',
            'turnstileSecretKey' => 'nullable|string|max:255',
            'enableTurnstile' => 'boolean',
            'enableHoneypot' => 'boolean',
            'maxLoginAttemptsPerIp' => 'required|integer|min:1|max:100',
            'maxAccountAttempts' => 'required|integer|min:1|max:50',
            'lockoutDurationMinutes' => 'required|integer|min:1|max:1440',
            'autoBlockThreshold' => 'required|integer|min:3|max:500',
            'autoBlockHours' => 'required|integer|min:1|max:8760',
            'maxRegistrationsPerHour' => 'required|integer|min:1|max:50',
        ]);

        $configs = [
            'turnstile_enabled' => $this->enableTurnstile ? '1' : '0',
            'turnstile_site_key' => trim($this->turnstileSiteKey),
            'turnstile_secret_key' => trim($this->turnstileSecretKey),
            'honeypot_enabled' => $this->enableHoneypot ? '1' : '0',
            'auth_max_ip_attempts' => (string) $this->maxLoginAttemptsPerIp,
            'auth_max_account_attempts' => (string) $this->maxAccountAttempts,
            'auth_lockout_minutes' => (string) $this->lockoutDurationMinutes,
            'auth_autoblock_threshold' => (string) $this->autoBlockThreshold,
            'auth_autoblock_hours' => (string) $this->autoBlockHours,
            'auth_max_reg_per_hour' => (string) $this->maxRegistrationsPerHour,
        ];

        foreach ($configs as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'security',
                    'group' => 'auth',
                    'updated_at' => now(),
                ]
            );
        }

        session()->flash('status', 'Authentication, Rate Limiting, and Turnstile security configurations saved successfully.');
    }

    public function updatingSearchLog()
    {
        $this->resetPage('logsPage');
    }

    public function updatingSearchIp()
    {
        $this->resetPage('ipsPage');
    }

    public function updatingSearchUser()
    {
        $this->resetPage('usersPage');
    }

    public function openBlockIpModal()
    {
        $this->new_block_ip = '';
        $this->new_block_reason = '';
        $this->new_block_duration = '24_hours';
        $this->showBlockIpModal = true;
    }

    public function blockIp()
    {
        $this->validate([
            'new_block_ip' => 'required|ip',
            'new_block_reason' => 'nullable|string|max:255',
        ]);

        $blockedUntil = match ($this->new_block_duration) {
            '1_hour' => now()->addHour(),
            '24_hours' => now()->addHours(24),
            '7_days' => now()->addDays(7),
            default => null, // permanent
        };

        BlockedIp::updateOrCreate(
            ['ip_address' => $this->new_block_ip],
            [
                'reason' => $this->new_block_reason ?: 'Manually blocked by Administrator',
                'blocked_by' => 'admin (' . Auth::user()->name . ')',
                'blocked_until' => $blockedUntil,
            ]
        );

        $this->showBlockIpModal = false;
        session()->flash('status', "IP Address '{$this->new_block_ip}' has been successfully blocked.");
    }

    public function unblockIp(int $id)
    {
        $block = BlockedIp::findOrFail($id);
        $ip = $block->ip_address;
        $block->delete();

        // Clear Rate Limiters for this IP
        RateLimiter::clear('login:ip:' . $ip);
        RateLimiter::clear('register:ip:' . $ip);

        session()->flash('status', "IP Address '{$ip}' unblocked and rate limits flushed.");
    }

    public function toggleUserBan(int $userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot ban your own active admin account.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        // If banning user, terminate all their active sessions
        if (!$user->is_active && SchemaHasSessionsTable()) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $statusName = $user->is_active ? 'Unbanned (Active)' : 'Banned (Suspended)';
        session()->flash('status', "User '{$user->name}' is now {$statusName}.");
    }

    public function flushAllRateLimits()
    {
        // Flush memory or database cache
        DB::table('auth_security_logs')->where('created_at', '<', now()->subDays(30))->delete();
        session()->flash('status', 'Security telemetry archives older than 30 days pruned.');
    }

    public function render()
    {
        // 1. Metric matrix
        $totalUsersCount = User::count();
        $bannedUsersCount = User::where('is_active', false)->count();
        $activeBlockedIpsCount = BlockedIp::count();
        $failedLoginsTodayCount = AuthSecurityLog::where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
        $honeypotTrapsCount = AuthSecurityLog::where('event_type', 'honeypot_triggered')->count();

        // 2. Online users calculation (active within last 5 minutes from sessions table)
        $onlineUsers = collect();
        if (SchemaHasSessionsTable()) {
            $fiveMinutesAgo = now()->subMinutes(5)->timestamp;
            $onlineSessions = DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', $fiveMinutesAgo)
                ->get();

            $onlineUserIds = $onlineSessions->pluck('user_id')->unique()->filter();
            $onlineUsers = User::whereIn('id', $onlineUserIds)->get();
        }

        // 3. Security logs query
        $logsQuery = AuthSecurityLog::latest();
        if (!empty($this->searchLog)) {
            $logsQuery->where(function ($q) {
                $q->where('ip_address', 'like', '%' . $this->searchLog . '%')
                  ->orWhere('email', 'like', '%' . $this->searchLog . '%');
            });
        }
        if (!empty($this->eventFilter)) {
            $logsQuery->where('event_type', $this->eventFilter);
        }
        $securityLogs = $logsQuery->paginate(15, ['*'], 'logsPage');

        // 4. Blocked IPs query
        $blockedIpsQuery = BlockedIp::latest();
        if (!empty($this->searchIp)) {
            $blockedIpsQuery->where('ip_address', 'like', '%' . $this->searchIp . '%')
                            ->orWhere('reason', 'like', '%' . $this->searchIp . '%');
        }
        $blockedIps = $blockedIpsQuery->paginate(10, ['*'], 'ipsPage');

        // 5. Banned users query
        $bannedUsersQuery = User::where('is_active', false);
        if (!empty($this->searchUser)) {
            $bannedUsersQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchUser . '%')
                  ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            });
        }
        $bannedUsers = $bannedUsersQuery->paginate(10, ['*'], 'usersPage');

        return view('admin.auth-settings', [
            'totalUsersCount' => $totalUsersCount,
            'bannedUsersCount' => $bannedUsersCount,
            'activeBlockedIpsCount' => $activeBlockedIpsCount,
            'failedLoginsTodayCount' => $failedLoginsTodayCount,
            'honeypotTrapsCount' => $honeypotTrapsCount,
            'onlineUsers' => $onlineUsers,
            'securityLogs' => $securityLogs,
            'blockedIps' => $blockedIps,
            'bannedUsers' => $bannedUsers,
        ]);
    }
}

/**
 * Helper to safely check if sessions table exists
 */
function SchemaHasSessionsTable(): bool
{
    try {
        return \Illuminate\Support\Facades\Schema::hasTable('sessions');
    } catch (\Throwable $e) {
        return false;
    }
}

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

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function execute(string $email, string $password, bool $remember = false): bool
    {
        $normalizedEmail = Str::lower(trim($email));
        $ip = request()->ip() ?? '127.0.0.1';

        // Load dynamic throttle settings from database (with sensible defaults)
        $settings = \Illuminate\Support\Facades\DB::table('settings')->whereIn('key', [
            'auth_max_ip_attempts',
            'auth_max_account_attempts',
            'auth_lockout_minutes',
            'auth_autoblock_threshold',
            'auth_autoblock_hours',
        ])->pluck('value', 'key');

        $maxIpAttempts = isset($settings['auth_max_ip_attempts']) ? (int) $settings['auth_max_ip_attempts'] : 10;
        $maxAccountAttempts = isset($settings['auth_max_account_attempts']) ? (int) $settings['auth_max_account_attempts'] : 5;
        $lockoutMinutes = isset($settings['auth_lockout_minutes']) ? (int) $settings['auth_lockout_minutes'] : 5;
        $autoBlockThreshold = isset($settings['auth_autoblock_threshold']) ? (int) $settings['auth_autoblock_threshold'] : 15;
        $autoBlockHours = isset($settings['auth_autoblock_hours']) ? (int) $settings['auth_autoblock_hours'] : 24;

        // 1. Dual Throttling Keys:
        // Key A: IP-specific rate limiter
        $ipThrottleKey = 'login:ip:' . $ip;
        // Key B: Specific Account + IP rate limiter
        $accountThrottleKey = 'login:account:' . Str::transliterate($normalizedEmail . '|' . $ip);

        // Check global IP throttle (defense against distributed dictionary attacks from single source)
        if (RateLimiter::tooManyAttempts($ipThrottleKey, $maxIpAttempts)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Check specific user + IP throttle
        if (RateLimiter::tooManyAttempts($accountThrottleKey, $maxAccountAttempts)) {
            $seconds = RateLimiter::availableIn($accountThrottleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Attempt login with timing-safe hash comparison
        if (! Auth::attempt(['email' => $normalizedEmail, 'password' => $password], $remember)) {
            $ipAttempts = RateLimiter::hit($ipThrottleKey, 60);
            $accountAttempts = RateLimiter::hit($accountThrottleKey, $lockoutMinutes * 60); // Lock for configured minutes if limit hit

            // Log security failure
            \App\Features\Admin\Models\AuthSecurityLog::create([
                'ip_address' => $ip,
                'email' => $normalizedEmail,
                'event_type' => 'failed_login',
                'user_agent' => request()->userAgent(),
                'details' => ['attempts' => $ipAttempts],
                'is_blocked' => $ipAttempts >= $maxIpAttempts,
            ]);

            // Auto-block IP if exceeding configured threshold (Auto IP Blocker)
            if ($ipAttempts >= $autoBlockThreshold && !\App\Features\Admin\Models\BlockedIp::where('ip_address', $ip)->exists()) {
                \App\Features\Admin\Models\BlockedIp::create([
                    'ip_address' => $ip,
                    'reason' => 'Automated block: Exceeded maximum failed login attempts (' . $ipAttempts . ' attempts)',
                    'blocked_by' => 'system',
                    'blocked_until' => now()->addHours($autoBlockHours),
                ]);

                // Dispatch notification to admins
                try {
                    $admins = \App\Models\User::where('role', 'admin')->get();
                    $alert = new \App\Features\Admin\Notifications\SecurityAlertNotification(
                        title: "Malicious IP Auto-Blocked",
                        description: "Network IP {$ip} has been automatically blacklisted for {$autoBlockHours} hours after {$ipAttempts} failed login attempts.",
                        severity: "critical",
                        actionUrl: url('/admin/auth-settings'),
                        actionText: "Manage IP Blacklist",
                        metadata: [
                            'ip' => $ip,
                            'timestamp' => now()->toIso8601String(),
                            'target_email' => $normalizedEmail,
                        ]
                    );

                    foreach ($admins as $admin) {
                        $admin->notify($alert);
                    }
                } catch (\Throwable $e) {
                    // Silently log failure to prevent breaking authentication flow
                    \Illuminate\Support\Facades\Log::warning('Failed to dispatch auto-block security notification', ['error' => $e->getMessage()]);
                }
            }

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        // Authentication succeeded: clear failed attempt locks
        RateLimiter::clear($ipThrottleKey);
        RateLimiter::clear($accountThrottleKey);

        \App\Features\Admin\Models\AuthSecurityLog::create([
            'ip_address' => $ip,
            'email' => $normalizedEmail,
            'event_type' => 'successful_login',
            'user_agent' => request()->userAgent(),
            'is_blocked' => false,
        ]);

        // Dispatch Login Detected Security Email (Optional/Configurable)
        try {
            $user = Auth::user();
            if ($user && $user->role === 'admin') {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Features\Admin\Mail\TemplateMailable('login_detected', [
                        '{user_name}' => $user->name,
                        '{user_email}' => $user->email,
                        '{ip_address}' => $ip,
                        '{user_agent}' => request()->userAgent() ?: 'Unknown Device',
                        '{location}' => 'Local Network',
                        '{timestamp}' => now()->toDayDateTimeString(),
                    ])
                );
            }
        } catch (\Throwable $e) {}
        
        // Prevent Session Fixation attacks: regenerate session ID on login
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        return true;
    }
}
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

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterUser
{
    public function execute(array $data): User
    {
        $ip = request()->ip() ?? '127.0.0.1';
        $ipThrottleKey = 'register:ip:' . $ip;

        $maxRegPerHour = (int) (\Illuminate\Support\Facades\DB::table('settings')->where('key', 'auth_max_reg_per_hour')->value('value') ?? 3);

        // Anti-Spam: Maximum registrations per hour per IP address
        if (RateLimiter::tooManyAttempts($ipThrottleKey, $maxRegPerHour)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            throw ValidationException::withMessages([
                'email' => __('Too many accounts created from this network. Please try again in :minutes minutes.', [
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $user = User::create([
            'name' => trim(strip_tags($data['name'])),
            'email' => Str::lower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'plan' => 'starter',
            'monthly_word_quota' => 15000,
            'used_word_quota' => 0,
            'preferences' => [
                'theme' => 'dark',
                'default_model' => 'OmniRoute: DeepSeek-V3',
                'auto_save_interval_seconds' => 30,
            ],
            'is_active' => true,
        ]);

        RateLimiter::hit($ipThrottleKey, 3600); // 1 hour cooldown per registration hit

        // Send Welcome in-app notification to new user
        try {
            $user->notify(new \App\Features\Admin\Notifications\GeneralSystemNotification(
                title: "Welcome to HelpOfAi Studio!",
                description: "Your starter workspace has been initialized with 15,000 monthly AI words. Explore templates, brand voices, and AI transformations!",
                type: "success",
                sendEmail: false,
                actionUrl: url('/dashboard'),
                actionText: "Open Dashboard"
            ));

            // Deliver dynamic Templated Welcome Email
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Features\Admin\Mail\TemplateMailable('welcome_registration', [
                    '{user_name}' => $user->name,
                    '{user_email}' => $user->email,
                    '{plan_name}' => strtoupper($user->plan ?? 'Starter'),
                    '{monthly_words}' => number_format($user->monthly_word_quota ?? 15000),
                ])
            );

            // Notify Admins
            $admins = User::where('role', 'admin')->get();
            $adminAlert = new \App\Features\Admin\Notifications\GeneralSystemNotification(
                title: "New User Registered",
                description: "{$user->name} ({$user->email}) just registered on the platform.",
                type: "info",
                sendEmail: false,
                actionUrl: url('/admin/users') . '?search=' . urlencode($user->email),
                actionText: "Manage User"
            );
            foreach ($admins as $admin) {
                $admin->notify($adminAlert);
            }
        } catch (\Throwable $e) {}

        event(new Registered($user));
        Auth::login($user);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        return $user;
    }
}
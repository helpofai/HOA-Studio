<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Authentication Telemetry & Security Model
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

namespace App\Features\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthSecurityLog extends Model
{
    use HasFactory;

    protected $table = 'auth_security_logs';

    protected $fillable = [
        'ip_address',
        'email',
        'event_type', // 'failed_login', 'blocked_ip', 'honeypot_triggered', 'banned_attempt', 'turnstile_failed', 'successful_login'
        'user_agent',
        'details',
        'is_blocked',
        'blocked_until',
    ];

    protected $casts = [
        'details' => 'array',
        'is_blocked' => 'boolean',
        'blocked_until' => 'datetime',
    ];
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Account Model
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

namespace App\Features\Antigravity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntigravityAccount extends Model
{
    protected $table = 'antigravity_accounts';

    protected $fillable = [
        'user_id',
        'email',
        'google_oauth_id',
        'google_oauth_token',
        'google_oauth_refresh_token',
        'token_expires_at',
        'antigravity_key',
        'is_active',
        'is_quota_exhausted',
        'quota_reset_at',
        'priority_order',
    ];

    protected $casts = [
        'google_oauth_token' => 'encrypted',
        'google_oauth_refresh_token' => 'encrypted',
        'antigravity_key' => 'encrypted',
        'token_expires_at' => 'datetime',
        'quota_reset_at' => 'datetime',
        'is_active' => 'boolean',
        'is_quota_exhausted' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

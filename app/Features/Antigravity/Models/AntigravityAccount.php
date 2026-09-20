<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - AntigravityAccount Model
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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AntigravityAccount extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'google_oauth_id',
        'project_id',
        'tier_id',
        'google_oauth_token',
        'google_oauth_refresh_token',
        'antigravity_key',
        'token_expires_at',
        'is_active',
        'is_quota_exhausted',
        'quota_reset_at',
        'priority_order',
        'total_tokens_used',
        'total_requests_count',
        'daily_token_limit',
        'last_used_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'quota_reset_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'is_quota_exhausted' => 'boolean',
        'total_tokens_used' => 'integer',
        'total_requests_count' => 'integer',
        'daily_token_limit' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

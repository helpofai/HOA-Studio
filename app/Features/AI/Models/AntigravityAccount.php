<?php

namespace App\Features\AI\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntigravityAccount extends Model
{
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
        'priority_order'
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

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

namespace App\Features\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserApiKey extends Model
{
    use HasFactory;

    protected $table = 'user_api_keys';

    protected $fillable = [
        'user_id',
        'provider_slug',
        'api_key',
        'custom_base_url',
        'is_active',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            // Laravel encrypted cast uses AES-256-CBC / AES-256-GCM via APP_KEY
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Return raw unencrypted key safely only when requested by owner
     */
    public function getRawKeyForOwner(User $requestingUser): ?string
    {
        if ($this->user_id !== $requestingUser->id && !$requestingUser->isAdmin()) {
            return null;
        }

        return $this->api_key;
    }

    /**
     * Check if this key points to a user local endpoint (e.g. localhost/127.0.0.1/Ollama)
     */
    public function isLocalEndpoint(): bool
    {
        if (empty($this->custom_base_url)) {
            return false;
        }

        return str_contains($this->custom_base_url, '127.0.0.1') 
            || str_contains($this->custom_base_url, 'localhost')
            || str_contains($this->custom_base_url, '192.168.');
    }
}
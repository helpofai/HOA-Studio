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

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'role',
        'plan',
        'monthly_word_quota',
        'used_word_quota',
        'bonus_word_quota',
        'preferences',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'monthly_word_quota' => 'integer',
            'used_word_quota' => 'integer',
            'bonus_word_quota' => 'integer',
            'preferences' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user has available quota words.
     */
    public function hasQuota(int $requiredWords = 1): bool
    {
        return ($this->monthly_word_quota - $this->used_word_quota) >= $requiredWords;
    }

    /**
     * Consume words from quota.
     */
    public function consumeQuota(int $words): void
    {
        $this->increment('used_word_quota', $words);
    }

    public function projects()
    {
        return $this->hasMany(\App\Features\Projects\Models\Project::class);
    }

    public function documents()
    {
        return $this->hasMany(\App\Features\Documents\Models\Document::class);
    }

    /**
     * Role Authorization Helpers
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;

        // Admin has universal super-privileges across all role checks
        if ($this->role === 'admin') {
            return true;
        }

        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['admin', 'editor'], true);
    }

    public function isPro(): bool
    {
        return in_array($this->role, ['admin', 'pro'], true) || in_array($this->plan, ['pro', 'enterprise'], true);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function apiKeys()
    {
        return $this->hasMany(\App\Features\Auth\Models\UserApiKey::class);
    }

    public function getActiveApiKeyFor(string $providerSlug): ?\App\Features\Auth\Models\UserApiKey
    {
        return $this->apiKeys()->where('provider_slug', $providerSlug)->where('is_active', true)->first();
    }
}


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

namespace App\Features\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DocumentShare extends Model
{
    use HasFactory;

    protected $table = 'document_shares';

    protected $fillable = [
        'document_id',
        'share_token',
        'is_active',
        'password_hash',
        'allow_copy',
        'allow_download',
        'view_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allow_copy' => 'boolean',
            'allow_download' => 'boolean',
            'view_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPasswordProtected(): bool
    {
        return !empty($this->password_hash);
    }

    public function verifyPassword(string $password): bool
    {
        if (!$this->isPasswordProtected()) {
            return true;
        }

        return Hash::check($password, $this->password_hash);
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public static function generateToken(): string
    {
        return Str::random(32);
    }
}
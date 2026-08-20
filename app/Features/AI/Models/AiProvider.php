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

namespace App\Features\AI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    use HasFactory;

    protected $table = 'ai_providers';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'base_url',
        'api_key_encrypted',
        'is_local',
        'is_active',
        'allow_user_key',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_local' => 'boolean',
            'is_active' => 'boolean',
            'allow_user_key' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function models(): HasMany
    {
        return $this->hasMany(AiModel::class, 'ai_provider_id');
    }

    public function liveModelsCount(): int
    {
        return $this->models()->where('is_active', true)->count();
    }

    public function offlineModelsCount(): int
    {
        return $this->models()->where('is_active', false)->count();
    }
}
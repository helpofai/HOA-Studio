<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Style Preference Model
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

namespace App\Features\ContentIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStylePreference extends Model
{
    use HasFactory;

    protected $table = 'user_style_preferences';

    protected $fillable = [
        'user_id',
        'preference_key',
        'observed_diff_count',
        'confidence',
        'rule_description',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'observed_diff_count' => 'integer',
        'confidence' => 'float',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Strategy Memory Model
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

use App\Features\ContentIntelligence\Enums\StrategyCategory;
use App\Features\ContentIntelligence\Enums\StrategyStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyMemory extends Model
{
    use HasFactory;

    protected $table = 'strategy_memories';

    protected $fillable = [
        'user_id',
        'strategy_key',
        'category',
        'evidence_count',
        'confidence',
        'status',
        'learning_payload',
        'adopted_at',
    ];

    protected $casts = [
        'category' => StrategyCategory::class,
        'status' => StrategyStatus::class,
        'evidence_count' => 'integer',
        'confidence' => 'float',
        'learning_payload' => 'array',
        'adopted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

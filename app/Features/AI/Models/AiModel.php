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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends Model
{
    use HasFactory;

    protected $table = 'ai_models';

    protected $fillable = [
        'ai_provider_id',
        'name',
        'model_id',
        'context_window',
        'cost_per_1k_input',
        'cost_per_1k_output',
        'supports_streaming',
        'is_free_tier',
        'is_combo',
        'supports_reasoning',
        'owned_by',
        'metadata',
        'max_output_tokens',
        'supports_vision',
        'supports_tools',
        'supports_json',
        'provider_family',
        'last_tested_at',
        'last_test_status',
        'last_test_latency_ms',
        'last_test_error',
        'total_calls_count',
        'total_tokens_consumed',
        'success_rate_percentage',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'context_window' => 'integer',
            'max_output_tokens' => 'integer',
            'cost_per_1k_input' => 'decimal:4',
            'cost_per_1k_output' => 'decimal:4',
            'supports_streaming' => 'boolean',
            'supports_vision' => 'boolean',
            'supports_tools' => 'boolean',
            'supports_json' => 'boolean',
            'is_free_tier' => 'boolean',
            'is_combo' => 'boolean',
            'supports_reasoning' => 'boolean',
            'metadata' => 'array',
            'last_tested_at' => 'datetime',
            'last_test_latency_ms' => 'integer',
            'total_calls_count' => 'integer',
            'total_tokens_consumed' => 'integer',
            'success_rate_percentage' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
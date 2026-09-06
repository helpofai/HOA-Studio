<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Outline Model
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentOutline extends Model
{
    use HasFactory;

    protected $table = 'content_outlines';

    protected $fillable = [
        'blueprint_id',
        'mission_id',
        'total_sections',
        'target_word_count',
        'section_nodes',
        'status',
    ];

    protected $casts = [
        'total_sections' => 'integer',
        'target_word_count' => 'integer',
        'section_nodes' => 'array',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(ContentBlueprint::class, 'blueprint_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }
}

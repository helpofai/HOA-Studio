<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Research Item Model
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

class ResearchItem extends Model
{
    use HasFactory;

    protected $table = 'research_items';

    protected $fillable = [
        'mission_id',
        'source_id',
        'query',
        'extracted_text',
        'key_facts',
        'confidence_score',
    ];

    protected $casts = [
        'key_facts' => 'array',
        'confidence_score' => 'decimal:4',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceIntelligence::class, 'source_id');
    }
}

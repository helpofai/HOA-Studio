<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Claim Node Model
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

use App\Features\ContentIntelligence\Enums\EpistemicState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimNode extends Model
{
    use HasFactory;

    protected $table = 'claim_nodes';

    protected $fillable = [
        'mission_id',
        'source_id',
        'statement',
        'epistemic_state',
        'evidence_extract',
        'section_target',
        'confidence_score',
        'is_controversial',
        'contradiction_details',
        'resolution_strategy',
    ];

    protected $casts = [
        'epistemic_state' => EpistemicState::class,
        'confidence_score' => 'decimal:4',
        'is_controversial' => 'boolean',
        'contradiction_details' => 'array',
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

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Evidence Snippet Model
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvidenceSnippet extends Model
{
    use HasFactory;

    protected $table = 'evidence_snippets';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'confidence_score' => 'float',
        'epistemic_state' => EpistemicState::class,
        'page_number' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceIntelligence::class, 'source_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function claimLinks(): HasMany
    {
        return $this->hasMany(ClaimEvidenceLink::class, 'evidence_id');
    }

    public function claims(): BelongsToMany
    {
        return $this->belongsToMany(ClaimNode::class, 'claim_evidence_links', 'evidence_id', 'claim_id')
            ->withPivot('relation_type', 'support_weight', 'notes')
            ->withTimestamps();
    }
}

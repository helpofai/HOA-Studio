<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Knowledge Triple Model
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

class KnowledgeTriple extends Model
{
    use HasFactory;

    protected $table = 'knowledge_triples';

    protected $fillable = [
        'mission_id',
        'source_id',
        'subject',
        'predicate',
        'object',
        'confidence',
        'epistemic_state',
    ];

    protected $casts = [
        'confidence' => 'decimal:4',
        'epistemic_state' => EpistemicState::class,
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

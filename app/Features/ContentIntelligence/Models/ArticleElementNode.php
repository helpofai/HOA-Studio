<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Article Element Node Model
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
use App\Features\Documents\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleElementNode extends Model
{
    use HasFactory;

    protected $table = 'article_element_nodes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'section_index' => 'integer',
        'paragraph_index' => 'integer',
        'sentence_index' => 'integer',
        'epistemic_state' => EpistemicState::class,
        'is_stale' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimNode::class, 'claim_id');
    }

    public function memory(): BelongsTo
    {
        return $this->belongsTo(BrainMemory::class, 'memory_id');
    }
}

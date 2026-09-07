<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Claim Evidence Link Model
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

use App\Features\ContentIntelligence\Enums\EvidenceRelationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimEvidenceLink extends Model
{
    use HasFactory;

    protected $table = 'claim_evidence_links';

    protected $guarded = [];

    protected $casts = [
        'support_weight' => 'float',
        'relation_type' => EvidenceRelationType::class,
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimNode::class, 'claim_id');
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(EvidenceSnippet::class, 'evidence_id');
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Source Intelligence Model
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

use App\Features\ContentIntelligence\Enums\SourceReliabilityTier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceIntelligence extends Model
{
    use HasFactory;

    protected $table = 'source_intelligences';

    protected $fillable = [
        'url_hash',
        'url',
        'title',
        'source_type',
        'reliability_score',
        'domain_authority',
        'is_primary',
        'publication_date',
        'last_verified_at',
        'metadata',
    ];

    protected $casts = [
        'source_type' => SourceReliabilityTier::class,
        'reliability_score' => 'integer',
        'domain_authority' => 'integer',
        'is_primary' => 'boolean',
        'publication_date' => 'date',
        'last_verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function researchItems(): HasMany
    {
        return $this->hasMany(ResearchItem::class, 'source_id');
    }

    public function knowledgeTriples(): HasMany
    {
        return $this->hasMany(KnowledgeTriple::class, 'source_id');
    }

    public function claimNodes(): HasMany
    {
        return $this->hasMany(ClaimNode::class, 'source_id');
    }

    public static function findOrCreateByUrl(string $url, string $title, SourceReliabilityTier $tier, array $metadata = []): self
    {
        $hash = hash('sha256', trim($url));

        return static::firstOrCreate(
            ['url_hash' => $hash],
            [
                'url' => $url,
                'title' => $title,
                'source_type' => $tier,
                'reliability_score' => $tier->defaultReliabilityScore(),
                'domain_authority' => $tier->isPrimary() ? 90 : 60,
                'is_primary' => $tier->isPrimary(),
                'last_verified_at' => now(),
                'metadata' => $metadata,
            ]
        );
    }
}

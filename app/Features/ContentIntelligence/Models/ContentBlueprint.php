<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Blueprint Model
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
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContentBlueprint extends Model
{
    use HasFactory;

    protected $table = 'content_blueprints';

    protected $fillable = [
        'mission_id',
        'article_angle',
        'unique_value_proposition',
        'target_transformation',
        'required_sections',
        'optional_sections',
        'required_entities',
        'internal_links',
        'external_sources',
        'faq_requirements',
        'quality_targets',
        'status',
    ];

    protected $casts = [
        'target_transformation' => 'array',
        'required_sections' => 'array',
        'optional_sections' => 'array',
        'required_entities' => 'array',
        'internal_links' => 'array',
        'external_sources' => 'array',
        'faq_requirements' => 'array',
        'quality_targets' => 'array',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function outline(): HasOne
    {
        return $this->hasOne(ContentOutline::class, 'blueprint_id');
    }
}

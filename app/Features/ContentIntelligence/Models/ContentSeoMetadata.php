<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content SEO Metadata Model
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

class ContentSeoMetadata extends Model
{
    use HasFactory;

    protected $table = 'content_seo_metadatas';

    protected $fillable = [
        'workflow_run_id',
        'mission_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'primary_keyword',
        'secondary_keywords',
        'schema_json_ld',
        'seo_score',
        'keyword_density',
        'heading_hierarchy_valid',
    ];

    protected $casts = [
        'secondary_keywords' => 'array',
        'schema_json_ld' => 'array',
        'keyword_density' => 'array',
        'seo_score' => 'integer',
        'heading_hierarchy_valid' => 'boolean',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }
}

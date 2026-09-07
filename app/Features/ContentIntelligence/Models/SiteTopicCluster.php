<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Site Topic Cluster Model
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

use App\Features\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteTopicCluster extends Model
{
    use HasFactory;

    protected $table = 'site_topic_clusters';

    protected $fillable = [
        'user_id',
        'project_id',
        'cluster_name',
        'core_topic',
        'coverage_score',
        'cannibalization_risks',
        'uncovered_subtopics',
        'internal_link_matrix',
        'recommendations',
    ];

    protected $casts = [
        'coverage_score' => 'integer',
        'cannibalization_risks' => 'array',
        'uncovered_subtopics' => 'array',
        'internal_link_matrix' => 'array',
        'recommendations' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

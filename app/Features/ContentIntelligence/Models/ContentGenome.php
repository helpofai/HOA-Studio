<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Genome Model
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

use App\Features\Documents\Models\Document;
use App\Features\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentGenome extends Model
{
    use HasFactory;

    protected $table = 'content_genomes';

    protected $fillable = [
        'user_id',
        'project_id',
        'workflow_run_id',
        'document_id',
        'genome_signature',
        'title',
        'mission_dna',
        'topics_dna',
        'entities_dna',
        'claims_dna',
        'facts_dna',
        'sources_dna',
        'quality_dna',
        'reusable_fragments',
    ];

    protected $casts = [
        'mission_dna' => 'array',
        'topics_dna' => 'array',
        'entities_dna' => 'array',
        'claims_dna' => 'array',
        'facts_dna' => 'array',
        'sources_dna' => 'array',
        'quality_dna' => 'array',
        'reusable_fragments' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}

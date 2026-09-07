<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Lineage Node Model
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

class ContentLineageNode extends Model
{
    use HasFactory;

    protected $table = 'content_lineage_nodes';

    protected $fillable = [
        'user_id',
        'project_id',
        'workflow_run_id',
        'document_id',
        'source_id',
        'evidence_snippet_id',
        'claim_id',
        'section_index',
        'paragraph_index',
        'sentence_index',
        'sentence_text',
        'published_url',
        'is_stale',
        'invalidation_reason',
    ];

    protected $casts = [
        'is_stale' => 'boolean',
        'section_index' => 'integer',
        'paragraph_index' => 'integer',
        'sentence_index' => 'integer',
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

    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceIntelligence::class, 'source_id');
    }

    public function evidenceSnippet(): BelongsTo
    {
        return $this->belongsTo(EvidenceSnippet::class, 'evidence_snippet_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ClaimNode::class, 'claim_id');
    }
}

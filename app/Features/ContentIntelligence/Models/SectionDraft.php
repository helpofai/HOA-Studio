<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Section Draft Model
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

class SectionDraft extends Model
{
    use HasFactory;

    protected $table = 'section_drafts';

    protected $fillable = [
        'workflow_run_id',
        'section_id',
        'heading',
        'content_html',
        'content_markdown',
        'word_count',
        'critic_score',
        'critic_feedback',
        'revision_count',
        'status',
    ];

    protected $casts = [
        'word_count' => 'integer',
        'critic_score' => 'decimal:2',
        'critic_feedback' => 'array',
        'revision_count' => 'integer',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}

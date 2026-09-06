<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Workflow Node Record Model
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

class WorkflowNodeRecord extends Model
{
    use HasFactory;

    protected $table = 'workflow_nodes';

    protected $fillable = [
        'workflow_run_id',
        'node_name',
        'input_payload',
        'output_payload',
        'status',
        'confidence',
        'latency_ms',
        'token_count',
        'error_log',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'output_payload' => 'array',
        'confidence' => 'decimal:4',
        'latency_ms' => 'integer',
        'token_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}

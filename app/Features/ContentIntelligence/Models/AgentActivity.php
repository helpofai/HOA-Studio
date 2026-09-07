<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Agent Activity Model
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

class AgentActivity extends Model
{
    use HasFactory;

    protected $table = 'agent_activities';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'input_payload' => 'array',
        'tokens_used' => 'integer',
        'latency_ms' => 'integer',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}

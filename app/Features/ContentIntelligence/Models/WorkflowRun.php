<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Workflow Run Model
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

use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowRun extends Model
{
    use HasFactory;

    protected $table = 'workflow_runs';

    protected $fillable = [
        'mission_id',
        'user_id',
        'document_id',
        'current_node',
        'status',
        'graph_state',
        'total_tokens',
        'total_cost',
        'overall_confidence',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'status' => ContentWorkflowStatus::class,
        'graph_state' => 'array',
        'total_tokens' => 'integer',
        'total_cost' => 'decimal:4',
        'overall_confidence' => 'decimal:4',
        'completed_at' => 'datetime',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(WorkflowNodeRecord::class, 'workflow_run_id');
    }

    public function getGraphStateValue(string $key, mixed $default = null): mixed
    {
        $state = $this->graph_state ?? [];

        return $state[$key] ?? $default;
    }

    public function setGraphStateValue(string $key, mixed $value): void
    {
        $state = $this->graph_state ?? [];
        $state[$key] = $value;
        $this->graph_state = $state;
        $this->save();
    }
}

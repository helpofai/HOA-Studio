<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Risk Assessment Model
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

use App\Features\ContentIntelligence\Enums\RiskLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    use HasFactory;

    protected $table = 'risk_assessments';

    protected $fillable = [
        'workflow_run_id',
        'risk_level',
        'risk_score',
        'is_ymyl',
        'requires_primary_sources',
        'requires_human_approval',
        'is_approved_by_human',
        'approved_by_user_id',
        'approved_at',
        'risk_factors',
        'mitigation_actions',
    ];

    protected $casts = [
        'risk_level' => RiskLevel::class,
        'risk_score' => 'integer',
        'is_ymyl' => 'boolean',
        'requires_primary_sources' => 'boolean',
        'requires_human_approval' => 'boolean',
        'is_approved_by_human' => 'boolean',
        'approved_at' => 'datetime',
        'risk_factors' => 'array',
        'mitigation_actions' => 'array',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function isGatingPassed(): bool
    {
        if ($this->requires_human_approval && ! $this->is_approved_by_human) {
            return false;
        }

        return true;
    }
}

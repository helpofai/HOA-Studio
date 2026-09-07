<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Quality Health Audit Model
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

class QualityHealthAudit extends Model
{
    use HasFactory;

    protected $table = 'quality_health_audits';

    protected $fillable = [
        'workflow_run_id',
        'overall_score',
        'grade',
        'dimensions',
        'key_strengths',
        'critical_gaps',
        'recommendations',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'dimensions' => 'array',
        'key_strengths' => 'array',
        'critical_gaps' => 'array',
        'recommendations' => 'array',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}

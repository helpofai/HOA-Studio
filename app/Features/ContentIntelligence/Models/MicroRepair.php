<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Micro Repair Model
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

use App\Features\ContentIntelligence\Enums\ProblemCategory;
use App\Features\ContentIntelligence\Enums\RepairStatus;
use App\Features\ContentIntelligence\Enums\RepairUnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MicroRepair extends Model
{
    use HasFactory;

    protected $table = 'micro_repairs';

    protected $fillable = [
        'workflow_run_id',
        'unit_type',
        'unit_pointer',
        'problem_category',
        'root_cause',
        'original_text',
        'repaired_text',
        'diff_summary',
        'escalation_level',
        'iteration',
        'status',
        'diagnostic_notes',
    ];

    protected $casts = [
        'unit_type' => RepairUnitType::class,
        'problem_category' => ProblemCategory::class,
        'status' => RepairStatus::class,
        'escalation_level' => 'integer',
        'iteration' => 'integer',
        'diagnostic_notes' => 'array',
    ];

    public function workflowRun(): BelongsTo
    {
        return $this->belongsTo(WorkflowRun::class, 'workflow_run_id');
    }
}

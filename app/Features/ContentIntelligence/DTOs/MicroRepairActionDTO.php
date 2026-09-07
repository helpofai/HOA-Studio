<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Micro Repair Action DTO
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

namespace App\Features\ContentIntelligence\DTOs;

use App\Features\ContentIntelligence\Enums\ProblemCategory;
use App\Features\ContentIntelligence\Enums\RepairStatus;
use App\Features\ContentIntelligence\Enums\RepairUnitType;

class MicroRepairActionDTO
{
    public function __construct(
        public int $workflowRunId,
        public RepairUnitType $unitType,
        public ?string $unitPointer,
        public ProblemCategory $problemCategory,
        public string $rootCause,
        public string $originalText,
        public string $repairedText,
        public string $diffSummary,
        public int $escalationLevel = 1,
        public int $iteration = 1,
        public RepairStatus $status = RepairStatus::RESOLVED,
        public array $diagnosticNotes = []
    ) {}

    public function toArray(): array
    {
        return [
            'workflow_run_id' => $this->workflowRunId,
            'unit_type' => $this->unitType->value,
            'unit_pointer' => $this->unitPointer,
            'problem_category' => $this->problemCategory->value,
            'root_cause' => $this->rootCause,
            'original_text' => $this->originalText,
            'repaired_text' => $this->repairedText,
            'diff_summary' => $this->diffSummary,
            'escalation_level' => $this->escalationLevel,
            'iteration' => $this->iteration,
            'status' => $this->status->value,
            'diagnostic_notes' => $this->diagnosticNotes,
        ];
    }
}

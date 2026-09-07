<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Risk Assessment DTO
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

use App\Features\ContentIntelligence\Enums\RiskLevel;

class RiskAssessmentDTO
{
    public function __construct(
        public RiskLevel $riskLevel,
        public int $riskScore,
        public bool $isYmyl,
        public bool $requiresPrimarySources,
        public bool $requiresHumanApproval,
        public bool $isApprovedByHuman = false,
        public array $riskFactors = [],
        public array $mitigationActions = []
    ) {}

    public function isGatingPassed(): bool
    {
        if ($this->requiresHumanApproval && ! $this->isApprovedByHuman) {
            return false;
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'risk_level' => $this->riskLevel->value,
            'risk_score' => $this->riskScore,
            'is_ymyl' => $this->isYmyl,
            'requires_primary_sources' => $this->requiresPrimarySources,
            'requires_human_approval' => $this->requiresHumanApproval,
            'is_approved_by_human' => $this->isApprovedByHuman,
            'is_gating_passed' => $this->isGatingPassed(),
            'risk_factors' => $this->riskFactors,
            'mitigation_actions' => $this->mitigationActions,
        ];
    }
}

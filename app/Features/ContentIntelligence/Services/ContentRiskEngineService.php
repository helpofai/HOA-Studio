<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Risk Engine Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\RiskAssessmentDTO;
use App\Features\ContentIntelligence\Enums\RiskLevel;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\RiskAssessment;
use App\Features\ContentIntelligence\Models\WorkflowRun;

class ContentRiskEngineService
{
    /**
     * Assess content risk profile across YMYL, regulatory, factual volatility, and claims criteria.
     */
    public function assessRisk(WorkflowRun $run, array $context = []): RiskAssessment
    {
        $mission = $run->mission ?? ContentMission::find($run->mission_id);
        $missionGraph = $run->getGraphStateValue('mission') ?? [];
        $topic = $mission?->topic ?? ($missionGraph['topic'] ?? '');
        $objective = $mission?->primary_objective ?? ($missionGraph['primary_objective'] ?? '');
        $text = $context['text'] ?? $run->getGraphStateValue('document_content', '');

        $combined = strtolower("{$topic} {$objective} {$text}");

        $riskFactors = [];
        $mitigations = [];
        $riskScore = 15; // baseline low risk

        // 1. Check YMYL Medical / Health
        $isHealth = (bool) preg_match('/\b(medicine|dosage|treatment|therapy|cancer|diagnosis|symptoms|fda approved|clinical trial|cardiovascular)\b/i', $combined);
        if ($isHealth) {
            $riskScore += 50;
            $riskFactors[] = 'YMYL: Health / Medical advice detected. Strict clinical validation necessary.';
            $mitigations[] = 'Mandate peer-reviewed journal sources and medical disclaimer.';
        }

        // 2. Check YMYL Financial / Legal
        $isFinanceLegal = (bool) preg_match('/\b(investing|guaranteed returns|cryptocurrency|roi|tax deductible|legal liability|gdpr compliance|hipaa|lawsuit)\b/i', $combined);
        if ($isFinanceLegal) {
            $riskScore += 50;
            $riskFactors[] = 'YMYL: Financial or Legal compliance implications detected.';
            $mitigations[] = 'Include standard financial / legal disclaimer and cite authoritative statutes.';
        }

        // 3. Check High-Volatility Claims / Statistics
        $hasStatistics = (bool) preg_match('/\b\d+(\.\d+)?%\b/', $combined);
        if ($hasStatistics) {
            $riskScore += 10;
            $riskFactors[] = 'Empirical statistical assertions present.';
            $mitigations[] = 'Corroborate numbers with Deep Evidence Graph citations.';
        }

        // 4. Absolute Claims / Unverifiable Guarantees
        $hasAbsolutes = (bool) preg_match('/\b(guaranteed 100%|risk-free|fail-proof|zero risk)\b/i', $combined);
        if ($hasAbsolutes) {
            $riskScore += 25;
            $riskFactors[] = 'Absolute liability claims detected without qualifications.';
            $mitigations[] = 'Surgically soften absolute statements via Micro-Repair loop.';
        }

        $isYmyl = $isHealth || $isFinanceLegal;
        $riskScore = min(100, max(5, $riskScore));

        $level = match (true) {
            $riskScore >= 80 => RiskLevel::CRITICAL,
            $riskScore >= 60 || $isYmyl => RiskLevel::HIGH,
            $riskScore >= 35 => RiskLevel::MEDIUM,
            default => RiskLevel::LOW,
        };

        $requiresPrimary = $level->requiresPrimarySources() || $isYmyl;
        $requiresApproval = $level->requiresHumanSignoff() || $riskScore >= 60 || $isYmyl;

        if (empty($riskFactors)) {
            $riskFactors[] = 'General informative content with standard enterprise risk profile.';
        }
        if (empty($mitigations)) {
            $mitigations[] = 'Standard automated fact-check gate verification.';
        }

        $dto = new RiskAssessmentDTO(
            riskLevel: $level,
            riskScore: $riskScore,
            isYmyl: $isYmyl,
            requiresPrimarySources: $requiresPrimary,
            requiresHumanApproval: $requiresApproval,
            isApprovedByHuman: false,
            riskFactors: $riskFactors,
            mitigationActions: $mitigations
        );

        return RiskAssessment::updateOrCreate(
            ['workflow_run_id' => $run->id],
            [
                'risk_level' => $dto->riskLevel,
                'risk_score' => $dto->riskScore,
                'is_ymyl' => $dto->isYmyl,
                'requires_primary_sources' => $dto->requiresPrimarySources,
                'requires_human_approval' => $dto->requiresHumanApproval,
                'is_approved_by_human' => false,
                'risk_factors' => $dto->riskFactors,
                'mitigation_actions' => $dto->mitigationActions,
            ]
        );
    }

    /**
     * Authorize and grant human signoff for high-risk missions.
     */
    public function approveByHuman(WorkflowRun $run, int $userId): RiskAssessment
    {
        $assessment = RiskAssessment::where('workflow_run_id', $run->id)->firstOrFail();

        $assessment->update([
            'is_approved_by_human' => true,
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
        ]);

        return $assessment;
    }

    /**
     * Check if the workflow run has cleared all verification gates.
     */
    public function isGatingPassed(WorkflowRun $run): bool
    {
        $assessment = RiskAssessment::where('workflow_run_id', $run->id)->first();

        if (! $assessment) {
            return true;
        }

        return $assessment->isGatingPassed();
    }
}

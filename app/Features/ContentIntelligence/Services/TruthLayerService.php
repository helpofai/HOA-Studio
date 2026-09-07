<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Truth Layer Service
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

use App\Features\ContentIntelligence\Contracts\ClaimVerifierInterface;
use App\Features\ContentIntelligence\DTOs\TruthAuditReportDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\ContentMission;

class TruthLayerService implements ClaimVerifierInterface
{
    public function __construct(
        protected DeepEvidenceGraphService $evidenceGraphService,
        protected WorldModelService $worldModelService
    ) {}

    /**
     * Audit an individual claim against its evidence consensus and world model constraints.
     *
     * @return array<string, mixed>
     */
    public function verifyClaim(ClaimNode $claim): array
    {
        $consensus = $this->evidenceGraphService->calculateConsensus($claim->id);

        $suggestedAction = 'allow_assertion';
        if ($consensus['epistemicState'] === EpistemicState::CONTRADICTED) {
            $suggestedAction = 'resolve_conflict_or_cite_both';
        } elseif ($consensus['epistemicState'] === EpistemicState::PARTIALLY_VERIFIED) {
            $suggestedAction = 'qualify_prose_cautiously';
        } elseif ($consensus['epistemicState'] === EpistemicState::UNVERIFIED) {
            $suggestedAction = 'gather_evidence_or_remove';
        }

        return [
            'claim_id' => $claim->id,
            'statement' => $claim->statement,
            'current_state' => $claim->epistemic_state,
            'audited_state' => $consensus['epistemicState']->value,
            'consensus_score' => $consensus['consensusScore'],
            'support_count' => $consensus['supportCount'],
            'refute_count' => $consensus['refuteCount'],
            'suggested_action' => $suggestedAction,
        ];
    }

    /**
     * Perform a comprehensive truth layer audit across all claims in a mission.
     */
    public function auditMission(ContentMission $mission): TruthAuditReportDTO
    {
        $claims = ClaimNode::where('mission_id', $mission->id)->get();

        if ($claims->isEmpty()) {
            return new TruthAuditReportDTO(
                totalClaims: 0,
                overallTruthScore: 100.0,
                riskRating: 'low',
                recommendedActions: ['Proceed with initial research and claim extraction.']
            );
        }

        $verified = 0;
        $partiallyVerified = 0;
        $unverified = 0;
        $contradicted = 0;
        $outdated = 0;
        $opinion = 0;

        $unresolvedIssues = [];
        $recommendedActions = [];

        foreach ($claims as $claim) {
            $state = $claim->epistemic_state instanceof EpistemicState
                ? $claim->epistemic_state
                : EpistemicState::tryFrom((string) $claim->epistemic_state) ?? EpistemicState::VERIFIED;

            match ($state) {
                EpistemicState::VERIFIED => $verified++,
                EpistemicState::PARTIALLY_VERIFIED => $partiallyVerified++,
                EpistemicState::UNVERIFIED => $unverified++,
                EpistemicState::CONTRADICTED => $contradicted++,
                EpistemicState::OUTDATED => $outdated++,
                EpistemicState::OPINION, EpistemicState::INFERENCE, EpistemicState::ESTIMATE => $opinion++,
                default => $unverified++,
            };

            if ($state === EpistemicState::CONTRADICTED) {
                $unresolvedIssues[] = "Claim #{$claim->id} is contradicted by conflicting evidence: '{$claim->statement}'";
            } elseif ($state === EpistemicState::UNVERIFIED) {
                $unresolvedIssues[] = "Claim #{$claim->id} has no supporting primary evidence: '{$claim->statement}'";
            }
        }

        $total = $claims->count();

        // Truth Score Formula:
        // Verified: 1.0, Opinion: 0.85, Partially: 0.70, Outdated: 0.30, Unverified: 0.20, Contradicted: 0.00
        $points = ($verified * 1.0)
            + ($opinion * 0.85)
            + ($partiallyVerified * 0.70)
            + ($outdated * 0.30)
            + ($unverified * 0.20)
            + ($contradicted * 0.0);

        $truthScore = round(($points / $total) * 100, 1);

        $riskRating = 'low';
        if ($contradicted > 0) {
            $riskRating = 'critical';
            $recommendedActions[] = "Immediately resolve {$contradicted} contradictory claim(s) before publishing.";
        } elseif ($unverified > 0 || $truthScore < 75.0) {
            $riskRating = 'high';
            $recommendedActions[] = "Conduct supplementary research to verify {$unverified} ungrounded claim(s).";
        } elseif ($partiallyVerified > 0 || $truthScore < 90.0) {
            $riskRating = 'medium';
            $recommendedActions[] = 'Ensure partially verified claims are framed with measured, cautious language.';
        } else {
            $recommendedActions[] = 'Truth layer integrity verified. All claims meet enterprise evidentiary thresholds.';
        }

        return new TruthAuditReportDTO(
            totalClaims: $total,
            verifiedCount: $verified,
            partiallyVerifiedCount: $partiallyVerified,
            unverifiedCount: $unverified,
            contradictedCount: $contradicted,
            outdatedCount: $outdated,
            opinionCount: $opinion,
            overallTruthScore: $truthScore,
            riskRating: $riskRating,
            unresolvedIssues: $unresolvedIssues,
            recommendedActions: $recommendedActions
        );
    }
}

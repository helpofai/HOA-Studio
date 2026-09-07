<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Truth Audit Report DTO
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

final class TruthAuditReportDTO
{
    public function __construct(
        public readonly int $totalClaims = 0,
        public readonly int $verifiedCount = 0,
        public readonly int $partiallyVerifiedCount = 0,
        public readonly int $unverifiedCount = 0,
        public readonly int $contradictedCount = 0,
        public readonly int $outdatedCount = 0,
        public readonly int $opinionCount = 0,
        public readonly float $overallTruthScore = 100.0,
        public readonly string $riskRating = 'low', // low, medium, high, critical
        public readonly array $unresolvedIssues = [],
        public readonly array $recommendedActions = []
    ) {}

    public function toArray(): array
    {
        return [
            'total_claims' => $this->totalClaims,
            'verified_count' => $this->verifiedCount,
            'partially_verified_count' => $this->partiallyVerifiedCount,
            'unverified_count' => $this->unverifiedCount,
            'contradicted_count' => $this->contradictedCount,
            'outdated_count' => $this->outdatedCount,
            'opinion_count' => $this->opinionCount,
            'overall_truth_score' => $this->overallTruthScore,
            'risk_rating' => $this->riskRating,
            'unresolved_issues' => $this->unresolvedIssues,
            'recommended_actions' => $this->recommendedActions,
        ];
    }
}

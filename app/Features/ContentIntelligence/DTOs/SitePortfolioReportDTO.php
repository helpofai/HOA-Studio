<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Site Portfolio Report DTO
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

class SitePortfolioReportDTO
{
    /**
     * @param  array<int, array<string, mixed>>  $clusters
     * @param  array<int, array<string, mixed>>  $internalLinkOpportunities
     * @param  array<int, string>  $strategicRecommendations
     */
    public function __construct(
        public int $totalClusters,
        public float $averageCoverageScore,
        public int $cannibalizationAlertsCount,
        public int $uncoveredTopicsCount,
        public array $clusters = [],
        public array $internalLinkOpportunities = [],
        public array $strategicRecommendations = []
    ) {}

    public function toArray(): array
    {
        return [
            'total_clusters' => $this->totalClusters,
            'average_coverage_score' => $this->averageCoverageScore,
            'cannibalization_alerts_count' => $this->cannibalizationAlertsCount,
            'uncovered_topics_count' => $this->uncoveredTopicsCount,
            'clusters' => $this->clusters,
            'internal_link_opportunities' => $this->internalLinkOpportunities,
            'strategic_recommendations' => $this->strategicRecommendations,
        ];
    }
}

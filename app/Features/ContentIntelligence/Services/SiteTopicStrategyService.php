<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Site Topic Strategy Service
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

use App\Features\ContentIntelligence\DTOs\SitePortfolioReportDTO;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\SiteTopicCluster;
use App\Features\Documents\Models\Document;
use Illuminate\Support\Collection;

class SiteTopicStrategyService
{
    /**
     * Analyze user's full content portfolio to generate topic clusters, cannibalization alerts, and linking matrix.
     * Implements brain.md Section 29: Site-Level Intelligence.
     *
     * @return Collection<int, SiteTopicCluster>
     */
    public function analyzeTopicClusters(int $userId, ?int $projectId = null): Collection
    {
        $docs = Document::query()
            ->where('user_id', $userId)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->get();

        $missions = ContentMission::query()
            ->where('user_id', $userId)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->get();

        // 1. Group documents and missions into logical topic seeds
        $topicSeeds = [];
        foreach ($docs as $doc) {
            $words = array_filter(explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i', '', $doc->title))), fn ($w) => strlen($w) > 3);
            $primaryWord = ! empty($words) ? reset($words) : 'General';
            $seedKey = ucfirst($primaryWord);

            $topicSeeds[$seedKey][] = [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => 'document',
            ];
        }

        foreach ($missions as $m) {
            $words = array_filter(explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i', '', $m->topic))), fn ($w) => strlen($w) > 3);
            $primaryWord = ! empty($words) ? reset($words) : 'General';
            $seedKey = ucfirst($primaryWord);

            $topicSeeds[$seedKey][] = [
                'id' => $m->id,
                'title' => $m->topic,
                'type' => 'mission',
            ];
        }

        if (empty($topicSeeds)) {
            $topicSeeds['Content Strategy'] = [
                ['id' => 1, 'title' => 'Getting Started with Intelligent Content', 'type' => 'document'],
            ];
        }

        $clusters = collect();

        foreach ($topicSeeds as $seed => $items) {
            $count = count($items);
            $coverageScore = min(100, (int) ($count * 25));

            // Cannibalization check: multiple titles with similar prefixes
            $cannibalizationRisks = [];
            if ($count > 1) {
                $titles = array_column($items, 'title');
                for ($i = 0; $i < count($titles); $i++) {
                    for ($j = $i + 1; $j < count($titles); $j++) {
                        similar_text(strtolower($titles[$i]), strtolower($titles[$j]), $percent);
                        if ($percent > 65) {
                            $cannibalizationRisks[] = [
                                'item_a' => $titles[$i],
                                'item_b' => $titles[$j],
                                'similarity_percent' => round($percent, 1),
                                'recommendation' => "Merge or differentiate search intent between '{$titles[$i]}' and '{$titles[$j]}'",
                            ];
                        }
                    }
                }
            }

            // Uncovered subtopics recommendations
            $uncovered = [
                "{$seed} Architecture & Design Patterns",
                "{$seed} Production Best Practices & Security",
                "Comparative Benchmarks for {$seed}",
            ];

            // Internal linking matrix
            $linkMatrix = [];
            if ($count > 1) {
                for ($k = 0; $k < min(3, $count - 1); $k++) {
                    $linkMatrix[] = [
                        'source' => $items[$k]['title'],
                        'target' => $items[$k + 1]['title'],
                        'recommended_anchor' => "learn more about {$seed}",
                    ];
                }
            }

            $recommendations = [
                "Expand cluster '{$seed}' with deep technical guides to build topic authority.",
                'Implement cross-linking between sibling cluster articles to pass link equity.',
            ];

            $cluster = SiteTopicCluster::updateOrCreate(
                [
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'cluster_name' => "Cluster: {$seed}",
                ],
                [
                    'core_topic' => $seed,
                    'coverage_score' => $coverageScore,
                    'cannibalization_risks' => $cannibalizationRisks,
                    'uncovered_subtopics' => $uncovered,
                    'internal_link_matrix' => $linkMatrix,
                    'recommendations' => $recommendations,
                ]
            );

            $clusters->push($cluster);
        }

        return $clusters;
    }

    /**
     * Generate an executive Site Portfolio Report DTO for Site Brain dashboard.
     */
    public function generatePortfolioReport(int $userId, ?int $projectId = null): SitePortfolioReportDTO
    {
        $clusters = $this->analyzeTopicClusters($userId, $projectId);

        $totalClusters = $clusters->count();
        $avgCoverage = $totalClusters > 0 ? (float) $clusters->avg('coverage_score') : 0.0;

        $cannibalizationAlerts = 0;
        $uncoveredCount = 0;
        $allLinkOps = [];
        $strategicRecs = [];

        foreach ($clusters as $c) {
            $cannibalizationAlerts += count($c->cannibalization_risks ?? []);
            $uncoveredCount += count($c->uncovered_subtopics ?? []);
            if (! empty($c->internal_link_matrix)) {
                $allLinkOps = array_merge($allLinkOps, $c->internal_link_matrix);
            }
            if (! empty($c->recommendations)) {
                $strategicRecs = array_merge($strategicRecs, $c->recommendations);
            }
        }

        return new SitePortfolioReportDTO(
            totalClusters: $totalClusters,
            averageCoverageScore: round($avgCoverage, 1),
            cannibalizationAlertsCount: $cannibalizationAlerts,
            uncoveredTopicsCount: $uncoveredCount,
            clusters: $clusters->map(fn (SiteTopicCluster $c) => [
                'id' => $c->id,
                'cluster_name' => $c->cluster_name,
                'core_topic' => $c->core_topic,
                'coverage_score' => $c->coverage_score,
                'cannibalization_risks_count' => count($c->cannibalization_risks ?? []),
                'uncovered_subtopics' => $c->uncovered_subtopics ?? [],
            ])->toArray(),
            internalLinkOpportunities: array_slice($allLinkOps, 0, 10),
            strategicRecommendations: array_unique(array_slice($strategicRecs, 0, 8))
        );
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Research Director Service
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

use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\ResearchPlanDTO;
use App\Features\ContentIntelligence\DTOs\ResearchTaskDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\Enums\ResearchBudgetTier;
use App\Features\ContentIntelligence\Enums\SourceReliabilityTier;

class ResearchDirectorService
{
    /**
     * Dynamically formulate an adaptive research plan tailored to the mission,
     * search intelligence, budget tier, and factual trust requirements.
     */
    public function formulatePlan(ContentMissionDTO $mission, SearchIntelligenceDTO $searchIntel): ResearchPlanDTO
    {
        $budgetTier = $mission->researchBudgetTier;
        $maxBudget = $budgetTier->maxQueries();
        $tasks = [];

        // 1. Primary Source & Official Documentation Tasks
        $tasks[] = new ResearchTaskDTO(
            taskId: uniqid('res_official_'),
            query: "{$mission->topic} official documentation requirements specification",
            purpose: 'Discover primary specifications, valid versions, and authoritative constraints',
            sourceTypePriority: SourceReliabilityTier::OFFICIAL_DOCUMENTATION
        );

        // 2. Production Architecture & Configuration Tasks
        $tasks[] = new ResearchTaskDTO(
            taskId: uniqid('res_arch_'),
            query: "{$mission->topic} production architecture configuration best practices",
            purpose: 'Uncover standard deployment templates and operational parameters',
            sourceTypePriority: SourceReliabilityTier::PRIMARY_RESEARCH
        );

        // 3. User Questions & PAA Research Tasks
        foreach (array_slice($searchIntel->queryClusters['paa_questions'], 0, 2) as $paa) {
            if (count($tasks) >= $maxBudget) {
                break;
            }
            $tasks[] = new ResearchTaskDTO(
                taskId: uniqid('res_paa_'),
                query: $paa,
                purpose: 'Extract authoritative answers to frequent real-world practitioner inquiries',
                sourceTypePriority: SourceReliabilityTier::EXPERT_PUBLICATION
            );
        }

        // 4. Content Gap Exploitation Tasks
        foreach (array_slice($searchIntel->contentGaps['missing_topics'], 0, 2) as $gap) {
            if (count($tasks) >= $maxBudget) {
                break;
            }
            $tasks[] = new ResearchTaskDTO(
                taskId: uniqid('res_gap_'),
                query: "{$mission->topic} {$gap}",
                purpose: 'Fill competitor coverage gap with authoritative evidence',
                sourceTypePriority: SourceReliabilityTier::INDUSTRY_PUBLICATION
            );
        }

        // 5. Deep / Expert Secondary Tasks (if budget allows)
        if ($budgetTier === ResearchBudgetTier::DEEP || $budgetTier === ResearchBudgetTier::EXPERT) {
            $tasks[] = new ResearchTaskDTO(
                taskId: uniqid('res_benchmarks_'),
                query: "{$mission->topic} performance benchmarks throughput latency",
                purpose: 'Acquire empirical numerical data and statistical comparisons',
                sourceTypePriority: SourceReliabilityTier::PRIMARY_RESEARCH
            );

            $tasks[] = new ResearchTaskDTO(
                taskId: uniqid('res_security_'),
                query: "{$mission->topic} security hardening vulnerability common errors",
                purpose: 'Audit security boundaries and failure prevention mechanics',
                sourceTypePriority: SourceReliabilityTier::RECOGNIZED_ORGANIZATION
            );
        }

        // Calculate initial research confidence
        $taskCount = count($tasks);
        $minThreshold = $budgetTier->minConfidenceThreshold();
        $confidence = min(1.0, 0.70 + ($taskCount / max(1, $maxBudget)) * 0.28);

        $requiresAdditionalResearch = ($confidence < $minThreshold);
        $additionalReason = $requiresAdditionalResearch
            ? "Research confidence ({$confidence}) is below budget tier threshold ({$minThreshold}). Critical evidence points require deeper exploration."
            : null;

        return new ResearchPlanDTO(
            budgetTier: $budgetTier,
            allocatedBudget: $maxBudget,
            tasks: $tasks,
            rationale: "Adaptive research vectors configured for {$mission->topic} targeting {$taskCount} prioritized inquiries across authoritative sources.",
            researchConfidence: round($confidence, 2),
            requiresAdditionalResearch: $requiresAdditionalResearch,
            additionalResearchReason: $additionalReason
        );
    }
}

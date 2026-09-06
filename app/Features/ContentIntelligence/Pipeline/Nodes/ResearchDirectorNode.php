<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Research Director Workflow Node
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

namespace App\Features\ContentIntelligence\Pipeline\Nodes;

use App\Features\ContentIntelligence\Contracts\WorkflowNodeInterface;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\ResearchDirectorService;

class ResearchDirectorNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?ResearchDirectorService $service = null
    ) {
        $this->service = $service ?? new ResearchDirectorService;
    }

    public function getName(): string
    {
        return 'research_director';
    }

    public function getDescription(): string
    {
        return 'Dynamically determines required research tasks, allocates budget tier, and schedules discovery vectors.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $searchPayload = $context['search_intelligence']['search_intelligence'] ?? null;

        if (! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing mission_intake payload in workflow context.');
        }

        if (! $searchPayload) {
            return WorkflowNodeResultDTO::failed('Missing search_intelligence payload in workflow context.');
        }

        $mission = ContentMissionDTO::fromArray($missionPayload);
        $searchIntel = SearchIntelligenceDTO::fromArray($searchPayload);

        $plan = $this->service->formulatePlan($mission, $searchIntel);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'research_plan' => $plan->toArray(),
                'allocated_budget' => $plan->allocatedBudget,
                'task_count' => count($plan->tasks),
                'confidence' => $plan->researchConfidence,
                'requires_additional_research' => $plan->requiresAdditionalResearch,
                'rationale' => $plan->rationale,
                'planned_at' => now()->toIso8601String(),
            ],
            confidence: $plan->researchConfidence,
            nextSuggestedNode: 'knowledge_fabric'
        );
    }
}

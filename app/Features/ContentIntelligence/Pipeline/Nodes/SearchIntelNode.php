<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Search Intelligence Workflow Node
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
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\SearchIntelligenceService;

class SearchIntelNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?SearchIntelligenceService $service = null
    ) {
        $this->service = $service ?? new SearchIntelligenceService;
    }

    public function getName(): string
    {
        return 'search_intelligence';
    }

    public function getDescription(): string
    {
        return 'Deconstructs search intent, builds topic universe, and detects content coverage gaps.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $missionPayload = $context['mission_intake']['mission'] ?? null;

        if (! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing validated mission_intake payload in workflow context.');
        }

        $mission = ContentMissionDTO::fromArray($missionPayload);
        $searchIntel = $this->service->analyze($mission);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'search_intelligence' => $searchIntel->toArray(),
                'primary_intent' => $searchIntel->primaryIntent,
                'target_entities' => $searchIntel->targetEntities,
                'query_count' => count($searchIntel->queryClusters['primary']) + count($searchIntel->queryClusters['secondary']),
                'content_gaps_count' => count($searchIntel->contentGaps['missing_topics']),
                'analyzed_at' => now()->toIso8601String(),
            ],
            confidence: $searchIntel->intentConfidence,
            nextSuggestedNode: 'research_director'
        );
    }
}

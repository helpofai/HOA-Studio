<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blueprint Workflow Node
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
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\ContentBlueprintService;

class BlueprintNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?ContentBlueprintService $service = null
    ) {
        $this->service = $service ?? new ContentBlueprintService;
    }

    public function getName(): string
    {
        return 'content_blueprint';
    }

    public function getDescription(): string
    {
        return 'Synthesizes mission, search gaps, and verified knowledge into a strategic content blueprint.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $mission = $run->mission;
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $searchPayload = $context['search_intelligence']['search_intelligence'] ?? null;
        $knowledgePayload = $context['knowledge_fabric']['knowledge_fabric'] ?? null;

        if (! $mission || ! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing Content Mission in workflow context.');
        }

        if (! $searchPayload) {
            return WorkflowNodeResultDTO::failed('Missing search_intelligence in workflow context.');
        }

        if (! $knowledgePayload) {
            return WorkflowNodeResultDTO::failed('Missing knowledge_fabric in workflow context.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $searchIntelDTO = SearchIntelligenceDTO::fromArray($searchPayload);
        $knowledgeFabricDTO = KnowledgeFabricDTO::fromArray($knowledgePayload);

        $blueprintDTO = $this->service->generate($mission, $missionDTO, $searchIntelDTO, $knowledgeFabricDTO);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'content_blueprint' => $blueprintDTO->toArray(),
                'required_sections_count' => count($blueprintDTO->requiredSections),
                'article_angle' => $blueprintDTO->articleAngle,
                'uvp' => $blueprintDTO->uniqueValueProposition,
                'generated_at' => now()->toIso8601String(),
            ],
            confidence: 0.98,
            nextSuggestedNode: 'adaptive_outline'
        );
    }
}

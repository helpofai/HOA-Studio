<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Outline Workflow Node
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
use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\AdaptiveOutlineService;

class OutlineNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?AdaptiveOutlineService $service = null
    ) {
        $this->service = $service ?? new AdaptiveOutlineService;
    }

    public function getName(): string
    {
        return 'adaptive_outline';
    }

    public function getDescription(): string
    {
        return 'Generates a dependency-aware hierarchical outline tree grounded by verified claims.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $mission = $run->mission;
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $blueprintPayload = $context['content_blueprint']['content_blueprint'] ?? null;
        $knowledgePayload = $context['knowledge_fabric']['knowledge_fabric'] ?? null;

        if (! $mission || ! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing Content Mission in workflow context.');
        }

        if (! $blueprintPayload) {
            return WorkflowNodeResultDTO::failed('Missing content_blueprint in workflow context.');
        }

        if (! $knowledgePayload) {
            return WorkflowNodeResultDTO::failed('Missing knowledge_fabric in workflow context.');
        }

        $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->latest()->first();

        if (! $blueprintModel) {
            return WorkflowNodeResultDTO::failed('Blueprint record not found in database for mission.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $blueprintDTO = ContentBlueprintDTO::fromArray($blueprintPayload);
        $knowledgeFabricDTO = KnowledgeFabricDTO::fromArray($knowledgePayload);

        $outlineDTO = $this->service->build($mission, $blueprintModel, $blueprintDTO, $knowledgeFabricDTO, $missionDTO);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'adaptive_outline' => $outlineDTO->toArray(),
                'total_sections' => $outlineDTO->totalSections,
                'target_word_count' => $outlineDTO->targetWordCount,
                'outline_generated_at' => now()->toIso8601String(),
            ],
            confidence: $outlineDTO->outlineConfidence,
            nextSuggestedNode: 'section_draftsman'
        );
    }
}

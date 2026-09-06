<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Knowledge Fabric Workflow Node
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
use App\Features\ContentIntelligence\DTOs\ResearchPlanDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\KnowledgeFabricService;

class KnowledgeNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?KnowledgeFabricService $service = null
    ) {
        $this->service = $service ?? new KnowledgeFabricService;
    }

    public function getName(): string
    {
        return 'knowledge_fabric';
    }

    public function getDescription(): string
    {
        return 'Synthesizes authoritative sources, builds entity relationships, and grounds verified claims.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $mission = $run->mission;
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $planPayload = $context['research_director']['research_plan'] ?? null;

        if (! $mission || ! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing Content Mission in workflow execution context.');
        }

        if (! $planPayload) {
            return WorkflowNodeResultDTO::failed('Missing research_plan payload in workflow context.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $planDTO = ResearchPlanDTO::fromArray($planPayload);

        $fabric = $this->service->synthesize($mission, $missionDTO, $planDTO);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'knowledge_fabric' => $fabric->toArray(),
                'source_count' => count($fabric->sources),
                'triple_count' => count($fabric->triples),
                'claim_count' => count($fabric->claims),
                'contradictions_resolved' => $fabric->contradictionsResolved,
                'confidence' => $fabric->knowledgeConfidence,
                'synthesized_at' => now()->toIso8601String(),
            ],
            confidence: $fabric->knowledgeConfidence,
            nextSuggestedNode: 'content_blueprint'
        );
    }
}

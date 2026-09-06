<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Media Enhancer Workflow Node
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
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\MediaEnhancerService;

class MediaEnhancerNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?MediaEnhancerService $service = null
    ) {
        $this->service = $service ?? new MediaEnhancerService;
    }

    public function getName(): string
    {
        return 'media_enhancement';
    }

    public function getDescription(): string
    {
        return 'Injects Mermaid architectural diagrams, comparison parameter tables, and authoritative evidence callouts.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $knowledgePayload = $context['knowledge_fabric']['knowledge_fabric'] ?? null;
        $sectionsPayload = $context['section_draftsman']['sections'] ?? null;

        if (! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing mission_intake payload in workflow context.');
        }

        if (! $knowledgePayload) {
            return WorkflowNodeResultDTO::failed('Missing knowledge_fabric payload in workflow context.');
        }

        if (! $sectionsPayload) {
            return WorkflowNodeResultDTO::failed('Missing section_draftsman payload in workflow context.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $knowledgeDTO = KnowledgeFabricDTO::fromArray($knowledgePayload);

        $drafts = [];
        foreach ($sectionsPayload as $sec) {
            $drafts[] = SectionDraftDTO::fromArray($sec['draft']);
        }

        $assets = $this->service->enhance($run, $missionDTO, $knowledgeDTO, $drafts);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'media_assets' => array_map(fn ($a) => $a->toArray(), $assets),
                'total_assets_created' => count($assets),
                'asset_types' => array_values(array_unique(array_map(fn ($a) => $a->assetType, $assets))),
            ],
            confidence: 0.95,
            nextSuggestedNode: 'master_assembly'
        );
    }
}

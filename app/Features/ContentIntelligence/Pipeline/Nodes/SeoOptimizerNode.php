<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - SEO Optimizer Workflow Node
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
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\SeoOptimizationService;

class SeoOptimizerNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?SeoOptimizationService $service = null
    ) {
        $this->service = $service ?? new SeoOptimizationService;
    }

    public function getName(): string
    {
        return 'seo_optimization';
    }

    public function getDescription(): string
    {
        return 'Optimizes keyword densities, generates high-CTR SERP meta tags, and synthesizes Google JSON-LD schema.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $blueprintPayload = $context['content_blueprint']['content_blueprint'] ?? null;
        $sectionsPayload = $context['section_draftsman']['sections'] ?? null;

        if (! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing mission_intake payload in workflow context.');
        }

        if (! $blueprintPayload) {
            return WorkflowNodeResultDTO::failed('Missing content_blueprint payload in workflow context.');
        }

        if (! $sectionsPayload) {
            return WorkflowNodeResultDTO::failed('Missing section_draftsman payload in workflow context.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $blueprintDTO = ContentBlueprintDTO::fromArray($blueprintPayload);

        $drafts = [];
        foreach ($sectionsPayload as $sec) {
            $drafts[] = SectionDraftDTO::fromArray($sec['draft']);
        }

        $seoDTO = $this->service->optimize($run, $missionDTO, $blueprintDTO, $drafts);

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'seo_metadata' => $seoDTO->toArray(),
                'meta_title' => $seoDTO->metaTitle,
                'meta_description' => $seoDTO->metaDescription,
                'seo_score' => $seoDTO->seoScore,
                'schema_count' => count($seoDTO->schemaJsonLd['@graph'] ?? []),
            ],
            confidence: min(0.98, max(0.70, $seoDTO->seoScore / 100)),
            nextSuggestedNode: 'media_enhancement'
        );
    }
}

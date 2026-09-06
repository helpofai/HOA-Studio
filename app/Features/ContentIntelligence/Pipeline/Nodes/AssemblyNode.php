<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Assembly Workflow Node
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
use App\Features\ContentIntelligence\DTOs\MediaAssetDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\SeoMetadataDTO;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\TipTapDocumentAssembler;

class AssemblyNode implements WorkflowNodeInterface
{
    public function __construct(
        protected ?TipTapDocumentAssembler $assembler = null
    ) {
        $this->assembler = $assembler ?? new TipTapDocumentAssembler;
    }

    public function getName(): string
    {
        return 'master_assembly';
    }

    public function getDescription(): string
    {
        return 'Assembles all drafted sections, media assets, and SEO schemas into canonical TipTap ProseMirror AST and persists the final Document.';
    }

    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO
    {
        $missionPayload = $context['mission_intake']['mission'] ?? null;
        $blueprintPayload = $context['content_blueprint']['content_blueprint'] ?? null;
        $sectionsPayload = $context['section_draftsman']['sections'] ?? null;
        $seoPayload = $context['seo_optimization']['seo_metadata'] ?? null;
        $mediaPayload = $context['media_enhancement']['media_assets'] ?? [];

        if (! $missionPayload) {
            return WorkflowNodeResultDTO::failed('Missing mission_intake payload in workflow context.');
        }

        if (! $blueprintPayload) {
            return WorkflowNodeResultDTO::failed('Missing content_blueprint payload in workflow context.');
        }

        if (! $sectionsPayload) {
            return WorkflowNodeResultDTO::failed('Missing section_draftsman payload in workflow context.');
        }

        if (! $seoPayload) {
            return WorkflowNodeResultDTO::failed('Missing seo_optimization payload in workflow context.');
        }

        $missionDTO = ContentMissionDTO::fromArray($missionPayload);
        $blueprintDTO = ContentBlueprintDTO::fromArray($blueprintPayload);
        $seoDTO = SeoMetadataDTO::fromArray($seoPayload);

        $drafts = [];
        foreach ($sectionsPayload as $sec) {
            $drafts[] = SectionDraftDTO::fromArray($sec['draft']);
        }

        $mediaAssets = [];
        foreach ($mediaPayload as $asset) {
            $mediaAssets[] = MediaAssetDTO::fromArray($asset);
        }

        $masterDoc = $this->assembler->assemble(
            $run,
            $missionDTO,
            $blueprintDTO,
            $drafts,
            $seoDTO,
            $mediaAssets
        );

        return WorkflowNodeResultDTO::success(
            outputPayload: [
                'document_id' => $masterDoc->documentId,
                'title' => $masterDoc->title,
                'slug' => $masterDoc->slug,
                'word_count' => $masterDoc->wordCount,
                'reading_time_minutes' => $masterDoc->readingTimeMinutes,
                'seo_score' => $masterDoc->seoScore,
                'assembled_at' => $masterDoc->assembledAt,
                'is_publish_ready' => true,
            ],
            confidence: 0.98,
            nextSuggestedNode: null // Workflow successfully completed!
        );
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - TipTap Document Assembler
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

use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\MasterDocumentDTO;
use App\Features\ContentIntelligence\DTOs\MediaAssetDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\SeoMetadataDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\Documents\Data\CanonicalDocumentSchema;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use Illuminate\Support\Str;

class TipTapDocumentAssembler
{
    /**
     * Assemble all section drafts, media assets, and SEO metadata into a canonical TipTap ProseMirror document.
     *
     * @param  array<SectionDraftDTO>  $drafts
     * @param  array<MediaAssetDTO>  $mediaAssets
     */
    public function assemble(
        WorkflowRun $run,
        ContentMissionDTO $mission,
        ContentBlueprintDTO $blueprint,
        array $drafts,
        SeoMetadataDTO $seo,
        array $mediaAssets = []
    ): MasterDocumentDTO {
        $title = $seo->metaTitle ?: "{$mission->topic}: Production Guide";
        $slug = Str::slug($mission->topic);

        // Group media assets by target section
        $assetsBySection = [];
        foreach ($mediaAssets as $asset) {
            $assetsBySection[$asset->targetSectionId][] = $asset;
        }

        // Build ProseMirror AST nodes
        $astNodes = [];
        $htmlParts = [];
        $markdownParts = [];

        // 1. Document Title (H1)
        $astNodes[] = CanonicalDocumentSchema::createNode(
            'heading',
            ['level' => 1],
            [CanonicalDocumentSchema::createTextNode($title)]
        );
        $htmlParts[] = '<h1 class="text-3xl font-bold tracking-tight text-white mb-6">'.htmlspecialchars($title).'</h1>';
        $markdownParts[] = "# {$title}\n";

        // 2. Iterate through sections
        foreach ($drafts as $draft) {
            $sectionAssets = $assetsBySection[$draft->sectionId] ?? [];

            // Pre-body assets (e.g. callouts)
            foreach ($sectionAssets as $asset) {
                if ($asset->placement === 'before_body') {
                    $htmlParts[] = $asset->content;
                    $markdownParts[] = '> **'.$asset->title."**\n>\n> ".strip_tags($asset->content)."\n";
                    $astNodes[] = CanonicalDocumentSchema::createNode(
                        'blockquote',
                        [],
                        [CanonicalDocumentSchema::createNode('paragraph', [], [
                            CanonicalDocumentSchema::createTextNode(strip_tags($asset->content)),
                        ])]
                    );
                }
            }

            // Section Heading (H2)
            $astNodes[] = CanonicalDocumentSchema::createNode(
                'heading',
                ['level' => 2],
                [CanonicalDocumentSchema::createTextNode($draft->heading)]
            );
            $htmlParts[] = '<h2 class="text-2xl font-semibold text-violet-300 mt-8 mb-4">'.htmlspecialchars($draft->heading).'</h2>';
            $markdownParts[] = "\n## {$draft->heading}\n";

            // In-body assets (e.g. diagrams)
            foreach ($sectionAssets as $asset) {
                if ($asset->placement === 'in_body') {
                    if ($asset->assetType === 'diagram') {
                        $htmlParts[] = '<div class="mermaid-diagram my-6 p-4 rounded-xl bg-slate-900 border border-violet-500/20">'.
                            '<pre class="language-mermaid text-xs text-violet-200">'.htmlspecialchars($asset->content).'</pre></div>';
                        $markdownParts[] = "\n".$asset->content."\n";
                        $astNodes[] = CanonicalDocumentSchema::createNode(
                            'code_block',
                            ['language' => 'mermaid'],
                            [CanonicalDocumentSchema::createTextNode($asset->content)]
                        );
                    }
                }
            }

            // Section Prose Content
            $htmlParts[] = $draft->contentHtml;
            $markdownParts[] = $draft->contentMarkdown;

            // Split markdown by paragraph to convert to AST paragraphs
            $rawParagraphs = array_filter(explode("\n\n", trim($draft->contentMarkdown)));
            foreach ($rawParagraphs as $para) {
                $trimmed = trim($para);
                if (! empty($trimmed)) {
                    $astNodes[] = CanonicalDocumentSchema::createNode(
                        'paragraph',
                        [],
                        [CanonicalDocumentSchema::createTextNode($trimmed)]
                    );
                }
            }

            // After-body assets (e.g. comparison tables)
            foreach ($sectionAssets as $asset) {
                if ($asset->placement === 'after_body') {
                    $htmlParts[] = $asset->content;
                    $markdownParts[] = "\n".strip_tags($asset->content)."\n";
                    $astNodes[] = CanonicalDocumentSchema::createNode(
                        'paragraph',
                        [],
                        [CanonicalDocumentSchema::createTextNode(strip_tags($asset->content))]
                    );
                }
            }
        }

        // Complete Document Canonical TipTap Schema
        $canonicalDocument = CanonicalDocumentSchema::createDocument($astNodes);
        $fullHtml = implode("\n\n", $htmlParts);
        $fullMarkdown = implode("\n\n", $markdownParts);
        $wordCount = CanonicalDocumentSchema::calculateWordCount($canonicalDocument);
        $readingTime = CanonicalDocumentSchema::estimateReadingTime($canonicalDocument);
        $seoScore = $seo->seoScore;

        // Persist Document and DocumentContent in HOA-Studio
        $document = Document::updateOrCreate(
            [
                'id' => $run->document_id,
            ],
            [
                'user_id' => $run->user_id,
                'title' => $title,
                'slug' => $slug,
                'status' => 'draft',
                'word_count' => $wordCount,
                'character_count' => strlen($fullMarkdown),
                'reading_time_minutes' => $readingTime,
                'seo_score' => $seoScore,
            ]
        );

        DocumentContent::updateOrCreate(
            [
                'document_id' => $document->id,
            ],
            [
                'content_html' => $fullHtml,
                'content_json' => json_encode($canonicalDocument),
                'content_markdown' => $fullMarkdown,
                'content_plain' => CanonicalDocumentSchema::extractPlainText($canonicalDocument),
            ]
        );

        // Associate with workflow run
        $run->document_id = $document->id;
        $run->save();

        return new MasterDocumentDTO(
            documentId: $document->id,
            title: $title,
            slug: $slug,
            tiptapJson: $canonicalDocument,
            html: $fullHtml,
            markdown: $fullMarkdown,
            wordCount: $wordCount,
            readingTimeMinutes: $readingTime,
            seoScore: $seoScore,
            schemaJsonLd: $seo->schemaJsonLd,
            assembledAt: now()->toIso8601String()
        );
    }
}

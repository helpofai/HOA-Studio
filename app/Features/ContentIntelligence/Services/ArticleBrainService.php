<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Level 3 Article Brain Service
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

use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Models\ArticleElementNode;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\Documents\Models\Document;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ArticleBrainService
{
    /**
     * Map structured article text into granular Section -> Paragraph -> Sentence element nodes.
     *
     * @param  array<int, array{title: string, content: string, claim_ids?: array<int, int>}>  $sections
     * @return Collection<int, ArticleElementNode>
     */
    public function indexArticleElements(
        ContentMission $mission,
        ?Document $document,
        array $sections
    ): Collection {
        $createdElements = collect();

        foreach ($sections as $sIndex => $sectionData) {
            $sectionTitle = $sectionData['title'] ?? 'Section '.($sIndex + 1);
            $sectionContent = $sectionData['content'] ?? '';
            $claimIds = $sectionData['claim_ids'] ?? [];

            // 1. Index Section Element
            $secNode = ArticleElementNode::create([
                'id' => 'aen_sec_'.Str::lower(Str::random(10)),
                'document_id' => $document?->id,
                'mission_id' => $mission->id,
                'section_index' => $sIndex,
                'paragraph_index' => 0,
                'sentence_index' => 0,
                'element_type' => 'section',
                'text_content' => $sectionTitle,
                'epistemic_state' => EpistemicState::VERIFIED->value,
                'is_stale' => false,
            ]);
            $createdElements->push($secNode);

            // 2. Index Paragraphs & Sentences
            $paragraphs = preg_split('/\n\s*\n/', trim($sectionContent), -1, PREG_SPLIT_NO_EMPTY) ?: [$sectionContent];

            foreach ($paragraphs as $pIndex => $paragraph) {
                // Break into sentences
                $sentences = preg_split('/(?<=[.?!])\s+(?=[A-Z0-9])/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) ?: [$paragraph];

                foreach ($sentences as $sentIndex => $sentence) {
                    $claimId = ! empty($claimIds) ? ($claimIds[$sentIndex % count($claimIds)] ?? null) : null;

                    $sentNode = ArticleElementNode::create([
                        'id' => 'aen_snt_'.Str::lower(Str::random(10)),
                        'document_id' => $document?->id,
                        'mission_id' => $mission->id,
                        'section_index' => $sIndex,
                        'paragraph_index' => $pIndex,
                        'sentence_index' => $sentIndex,
                        'element_type' => 'sentence',
                        'text_content' => trim($sentence),
                        'claim_id' => $claimId,
                        'epistemic_state' => EpistemicState::VERIFIED->value,
                        'is_stale' => false,
                    ]);
                    $createdElements->push($sentNode);
                }
            }
        }

        return $createdElements;
    }

    /**
     * Surgically invalidate all downstream article elements when a core fact changes or becomes outdated.
     *
     * @return array{affectedSentencesCount: int, impactedDocumentIds: array<int, int>, invalidatedNodes: array<int, array<string, mixed>>}
     */
    public function invalidateFact(string $subject, string $reason): array
    {
        // 1. Find memories matching subject
        $memories = BrainMemory::where('subject', 'LIKE', "%{$subject}%")->pluck('id')->toArray();

        // 2. Find elements pointing to memory or containing subject
        $elements = ArticleElementNode::query()
            ->where(function ($q) use ($memories, $subject) {
                if (! empty($memories)) {
                    $q->whereIn('memory_id', $memories);
                }
                $q->orWhere('text_content', 'LIKE', "%{$subject}%");
            })
            ->where('is_stale', false)
            ->get();

        $invalidated = [];
        $documentIds = [];

        foreach ($elements as $el) {
            $el->update([
                'is_stale' => true,
                'invalidation_reason' => $reason,
                'epistemic_state' => EpistemicState::OUTDATED->value,
            ]);

            if ($el->document_id) {
                $documentIds[] = (int) $el->document_id;
            }

            $invalidated[] = [
                'id' => $el->id,
                'section_index' => $el->section_index,
                'paragraph_index' => $el->paragraph_index,
                'sentence_index' => $el->sentence_index,
                'element_type' => $el->element_type,
                'snippet' => Str::limit($el->text_content, 60),
                'reason' => $reason,
            ];
        }

        return [
            'affectedSentencesCount' => count($invalidated),
            'impactedDocumentIds' => array_values(array_unique($documentIds)),
            'invalidatedNodes' => $invalidated,
        ];
    }

    /**
     * Get all stale or outdated element alerts for a mission or document.
     *
     * @return Collection<int, ArticleElementNode>
     */
    public function getStaleElements(ContentMission $mission, ?Document $document = null): Collection
    {
        $query = ArticleElementNode::query()
            ->where('mission_id', $mission->id)
            ->where('is_stale', true);

        if ($document) {
            $query->where('document_id', $document->id);
        }

        return $query->get();
    }
}

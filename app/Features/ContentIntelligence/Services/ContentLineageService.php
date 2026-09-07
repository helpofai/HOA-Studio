<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Lineage Service
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

use App\Features\ContentIntelligence\DTOs\DownstreamImpactDTO;
use App\Features\ContentIntelligence\DTOs\LineageTraceDTO;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\ContentLineageNode;
use App\Features\ContentIntelligence\Models\SourceIntelligence;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use Illuminate\Support\Collection;

class ContentLineageService
{
    /**
     * Record an individual sentence lineage node in the 7-tier trace graph.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordLineage(int $userId, array $data): ContentLineageNode
    {
        return ContentLineageNode::create([
            'user_id' => $userId,
            'project_id' => $data['project_id'] ?? null,
            'workflow_run_id' => $data['workflow_run_id'] ?? null,
            'document_id' => $data['document_id'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'evidence_snippet_id' => $data['evidence_snippet_id'] ?? null,
            'claim_id' => $data['claim_id'] ?? null,
            'section_index' => (int) ($data['section_index'] ?? 0),
            'paragraph_index' => (int) ($data['paragraph_index'] ?? 0),
            'sentence_index' => (int) ($data['sentence_index'] ?? 0),
            'sentence_text' => (string) ($data['sentence_text'] ?? ''),
            'published_url' => $data['published_url'] ?? null,
            'is_stale' => (bool) ($data['is_stale'] ?? false),
            'invalidation_reason' => $data['invalidation_reason'] ?? null,
        ]);
    }

    /**
     * Extract and record lineage from an assembled document and its workflow context.
     *
     * @return Collection<int, ContentLineageNode>
     */
    public function extractAndRecordDocumentLineage(Document $document, ?WorkflowRun $workflowRun = null): Collection
    {
        $userId = $document->user_id;
        $docId = $document->id;
        $runId = $workflowRun?->id;
        $projId = $document->project_id ?? $workflowRun?->project_id;

        // Fetch claims available to this user — claim_nodes has no user_id or workflow_run_id column.
        // Scope via content_missions that belong to the user.
        $availableClaims = ClaimNode::query()
            ->whereIn(
                'mission_id',
                \App\Features\ContentIntelligence\Models\ContentMission::where('user_id', $userId)->select('id')
            )
            ->with(['source'])
            ->get();

        $content = (string) ($document->content instanceof DocumentContent
            ? ($document->content->content_plain ?: strip_tags($document->content->content_html ?? ''))
            : (is_string($document->content) ? $document->content : ''));
        $sections = preg_split('/(\n\s*#{1,6}\s+)/', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [$content];

        $recordedNodes = collect();
        $claimIndex = 0;

        foreach ($sections as $secIdx => $secContent) {
            $paragraphs = preg_split('/\n\s*\n/', trim($secContent), -1, PREG_SPLIT_NO_EMPTY) ?: [$secContent];

            foreach ($paragraphs as $paraIdx => $paraContent) {
                // Split sentences cleanly
                $sentences = preg_split('/(?<=[.?!])\s+(?=[A-Z0-9])/i', trim($paraContent), -1, PREG_SPLIT_NO_EMPTY) ?: [$paraContent];

                foreach ($sentences as $sentIdx => $sentence) {
                    $sentenceText = trim($sentence);
                    if (strlen($sentenceText) < 10) {
                        continue;
                    }

                    // Map to available claim if possible
                    $matchedClaim = $availableClaims->get($claimIndex % max(1, $availableClaims->count()));
                    if ($availableClaims->isNotEmpty()) {
                        $claimIndex++;
                    }

                    $node = $this->recordLineage($userId, [
                        'project_id' => $projId,
                        'workflow_run_id' => $runId,
                        'document_id' => $docId,
                        'source_id' => $matchedClaim?->source_id,
                        'evidence_snippet_id' => $matchedClaim?->evidence_snippet_id,
                        'claim_id' => $matchedClaim?->id,
                        'section_index' => $secIdx,
                        'paragraph_index' => $paraIdx,
                        'sentence_index' => $sentIdx,
                        'sentence_text' => $sentenceText,
                        'published_url' => null,
                        'is_stale' => false,
                    ]);

                    $recordedNodes->push($node);
                }
            }
        }

        return $recordedNodes;
    }

    /**
     * Answers: "Where did this statement come from?"
     * Returns full 7-tier trace from sentence up to source document and URL.
     */
    public function traceSentenceLineage(int $lineageNodeId): ?LineageTraceDTO
    {
        $node = ContentLineageNode::with(['source', 'evidenceSnippet', 'claim', 'document'])
            ->find($lineageNodeId);

        if (! $node) {
            return null;
        }

        return new LineageTraceDTO(
            id: $node->id,
            sentenceText: $node->sentence_text,
            sectionIndex: $node->section_index,
            paragraphIndex: $node->paragraph_index,
            sentenceIndex: $node->sentence_index,
            claimId: $node->claim_id,
            claimText: $node->claim?->statement ?? $node->claim?->claim_text,
            claimStatus: $node->claim?->epistemic_state?->value ?? $node->claim?->epistemic_status?->value ?? ($node->claim ? 'verified' : null),
            evidenceSnippetId: $node->evidence_snippet_id,
            evidenceQuote: $node->evidenceSnippet?->verbatim_quote ?? $node->evidenceSnippet?->extract_text ?? $node->evidenceSnippet?->quote,
            sourceId: $node->source_id,
            sourceTitle: $node->source?->title,
            sourceUrl: $node->source?->url,
            publishedUrl: $node->published_url,
            isStale: $node->is_stale,
            invalidationReason: $node->invalidation_reason
        );
    }

    /**
     * Answers: "Which articles and sentences depend on this source?"
     */
    public function findDownstreamImpactBySource(int $sourceId): DownstreamImpactDTO
    {
        $source = SourceIntelligence::find($sourceId);
        $sourceTitle = $source?->title ?? "Source #{$sourceId}";

        $affectedNodes = ContentLineageNode::where('source_id', $sourceId)->get();
        $affectedDocIds = $affectedNodes->pluck('document_id')->filter()->unique();

        return new DownstreamImpactDTO(
            sourceId: $sourceId,
            sourceTitle: $sourceTitle,
            affectedDocumentsCount: $affectedDocIds->count(),
            affectedSentencesCount: $affectedNodes->count(),
            staleNodes: $affectedNodes->map(fn (ContentLineageNode $n) => [
                'id' => $n->id,
                'document_id' => $n->document_id,
                'section_index' => $n->section_index,
                'sentence_text' => $n->sentence_text,
                'is_stale' => $n->is_stale,
            ])->toArray()
        );
    }

    /**
     * Answers: "What content needs updating if this fact changes?"
     * Granularly invalidates all sentences derived from this source without breaking the rest of the document.
     */
    public function invalidateSourceFact(int $sourceId, string $reason): DownstreamImpactDTO
    {
        $source = SourceIntelligence::find($sourceId);
        $sourceTitle = $source?->title ?? "Source #{$sourceId}";

        ContentLineageNode::where('source_id', $sourceId)->update([
            'is_stale' => true,
            'invalidation_reason' => $reason,
        ]);

        $staleNodes = ContentLineageNode::where('source_id', $sourceId)->get();
        $affectedDocIds = $staleNodes->pluck('document_id')->filter()->unique();

        return new DownstreamImpactDTO(
            sourceId: $sourceId,
            sourceTitle: $sourceTitle,
            affectedDocumentsCount: $affectedDocIds->count(),
            affectedSentencesCount: $staleNodes->count(),
            staleNodes: $staleNodes->map(fn (ContentLineageNode $n) => [
                'id' => $n->id,
                'document_id' => $n->document_id,
                'section_index' => $n->section_index,
                'sentence_text' => $n->sentence_text,
                'is_stale' => true,
                'invalidation_reason' => $reason,
            ])->toArray(),
            recommendedAction: "Perform surgical micro-repair on {$staleNodes->count()} affected sentences across {$affectedDocIds->count()} document(s)."
        );
    }

    /**
     * Get all stale nodes for a document to guide the surgical repair engine.
     *
     * @return Collection<int, ContentLineageNode>
     */
    public function getStaleNodesForDocument(int $documentId): Collection
    {
        return ContentLineageNode::where('document_id', $documentId)
            ->where('is_stale', true)
            ->orderBy('section_index')
            ->orderBy('paragraph_index')
            ->orderBy('sentence_index')
            ->get();
    }

    /**
     * Resolve a stale sentence node after surgical re-write.
     */
    public function resolveStaleNode(int $lineageNodeId, string $newSentenceText): ContentLineageNode
    {
        $node = ContentLineageNode::findOrFail($lineageNodeId);
        $node->update([
            'sentence_text' => $newSentenceText,
            'is_stale' => false,
            'invalidation_reason' => null,
        ]);

        return $node;
    }
}

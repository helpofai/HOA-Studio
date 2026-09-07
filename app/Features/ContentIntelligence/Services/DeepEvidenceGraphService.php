<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Deep Evidence Graph Service
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

use App\Features\ContentIntelligence\DTOs\EvidenceSnippetDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\EvidenceRelationType;
use App\Features\ContentIntelligence\Models\ArticleElementNode;
use App\Features\ContentIntelligence\Models\ClaimEvidenceLink;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\EvidenceSnippet;
use Illuminate\Support\Str;

class DeepEvidenceGraphService
{
    /**
     * Record a verbatim evidence snippet from an authoritative source.
     */
    public function recordSnippet(EvidenceSnippetDTO $dto): EvidenceSnippet
    {
        return EvidenceSnippet::create([
            'id' => $dto->id ?: 'evd_'.Str::lower(Str::random(10)),
            'source_id' => $dto->sourceId,
            'mission_id' => $dto->missionId,
            'extract_text' => $dto->extractText,
            'verbatim_quote' => $dto->verbatimQuote,
            'section_or_heading' => $dto->sectionOrHeading,
            'page_number' => $dto->pageNumber,
            'confidence_score' => $dto->confidenceScore,
            'epistemic_state' => $dto->epistemicState->value,
            'verified_at' => $dto->verifiedAt ?: now(),
        ]);
    }

    /**
     * Ground a claim to an evidence snippet with an explicit relationship type.
     */
    public function linkClaimToEvidence(
        int $claimId,
        string $evidenceId,
        EvidenceRelationType|string $type = EvidenceRelationType::SUPPORTS,
        float $weight = 1.0,
        ?string $notes = null
    ): ClaimEvidenceLink {
        $relation = $type instanceof EvidenceRelationType ? $type->value : $type;

        return ClaimEvidenceLink::create([
            'claim_id' => $claimId,
            'evidence_id' => $evidenceId,
            'relation_type' => $relation,
            'support_weight' => $weight,
            'notes' => $notes,
        ]);
    }

    /**
     * Calculate consensus and supporting vs refuting evidence ratio for a claim.
     *
     * @return array{supportCount: int, refuteCount: int, consensusScore: float, epistemicState: EpistemicState}
     */
    public function calculateConsensus(int $claimId): array
    {
        $links = ClaimEvidenceLink::with('evidence.source')->where('claim_id', $claimId)->get();

        if ($links->isEmpty()) {
            return [
                'supportCount' => 0,
                'refuteCount' => 0,
                'consensusScore' => 0.50,
                'epistemicState' => EpistemicState::UNVERIFIED,
            ];
        }

        $supportWeight = 0.0;
        $refuteWeight = 0.0;
        $supportCount = 0;
        $refuteCount = 0;

        foreach ($links as $link) {
            $srcWeight = $link->evidence?->source?->reliability_score ? ($link->evidence->source->reliability_score / 100) : 0.80;
            $linkWeight = $link->support_weight * $srcWeight;

            if ($link->relation_type === EvidenceRelationType::SUPPORTS) {
                $supportWeight += $linkWeight;
                $supportCount++;
            } elseif ($link->relation_type === EvidenceRelationType::REFUTES) {
                $refuteWeight += $linkWeight;
                $refuteCount++;
            }
        }

        $totalWeight = $supportWeight + $refuteWeight;
        $consensusScore = $totalWeight > 0 ? round($supportWeight / $totalWeight, 4) : 0.50;

        $state = EpistemicState::VERIFIED;
        if ($refuteCount > 0 && $supportCount > 0) {
            $state = EpistemicState::CONTRADICTED;
        } elseif ($refuteCount > 0 && $supportCount === 0) {
            $state = EpistemicState::CONTRADICTED;
        } elseif ($consensusScore < 0.70) {
            $state = EpistemicState::PARTIALLY_VERIFIED;
        }

        return [
            'supportCount' => $supportCount,
            'refuteCount' => $refuteCount,
            'supporting_count' => $supportCount,
            'refuting_count' => $refuteCount,
            'consensusScore' => $consensusScore,
            'consensus_score' => $consensusScore,
            'is_contradicted' => ($state === EpistemicState::CONTRADICTED),
            'epistemicState' => $state,
            'epistemic_state' => $state,
        ];
    }

    /**
     * Extract the complete deep lineage trace for a claim:
     * SOURCE ➔ EVIDENCE ➔ CLAIM ➔ SENTENCE ➔ DOCUMENT.
     *
     * @return array<string, mixed>
     */
    public function getDeepLineage(int $claimId): array
    {
        $claim = ClaimNode::with('source')->find($claimId);
        if (! $claim) {
            return [];
        }

        $evidenceLinks = ClaimEvidenceLink::with('evidence.source')->where('claim_id', $claimId)->get();
        $articleElements = ArticleElementNode::with('document')->where('claim_id', $claimId)->get();

        $evidenceList = [];
        foreach ($evidenceLinks as $link) {
            $evidenceList[] = [
                'evidence_id' => $link->evidence_id,
                'relation' => $link->relation_type->value,
                'quote' => $link->evidence?->verbatim_quote ?: Str::limit($link->evidence?->extract_text, 100),
                'source_title' => $link->evidence?->source?->title,
                'source_url' => $link->evidence?->source?->url,
                'reliability' => $link->evidence?->source?->reliability_score,
            ];
        }

        $sentencesList = [];
        foreach ($articleElements as $elem) {
            $sentencesList[] = [
                'element_id' => $elem->id,
                'document_id' => $elem->document_id,
                'document_title' => $elem->document?->title,
                'section_index' => $elem->section_index,
                'sentence_index' => $elem->sentence_index,
                'sentence_text' => $elem->text_content,
                'is_stale' => $elem->is_stale,
            ];
        }

        return [
            'claim_id' => $claim->id,
            'statement' => $claim->statement,
            'epistemic_state' => $claim->epistemic_state,
            'confidence' => $claim->confidence_score,
            'primary_source' => [
                'id' => $claim->source?->id,
                'title' => $claim->source?->title,
                'url' => $claim->source?->url,
            ],
            'evidence_nodes' => $evidenceList,
            'article_sentences' => $sentencesList,
            'lineage_chain' => sprintf(
                'Source #%s ➔ %d Evidence Extracts ➔ Claim #%d ➔ %d Sentences in Doc #%s',
                $claim->source_id ?? '?',
                count($evidenceList),
                $claim->id,
                count($sentencesList),
                $articleElements->first()?->document_id ?? 'N/A'
            ),
        ];
    }
}

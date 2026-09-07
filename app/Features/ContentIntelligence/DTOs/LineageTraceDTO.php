<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Lineage Trace DTO
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

namespace App\Features\ContentIntelligence\DTOs;

class LineageTraceDTO
{
    public function __construct(
        public ?int $id,
        public string $sentenceText,
        public int $sectionIndex = 0,
        public int $paragraphIndex = 0,
        public int $sentenceIndex = 0,
        public ?int $claimId = null,
        public ?string $claimText = null,
        public ?string $claimStatus = null,
        public ?int $evidenceSnippetId = null,
        public ?string $evidenceQuote = null,
        public ?int $sourceId = null,
        public ?string $sourceTitle = null,
        public ?string $sourceUrl = null,
        public ?string $publishedUrl = null,
        public bool $isStale = false,
        public ?string $invalidationReason = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sentence_text' => $this->sentenceText,
            'section_index' => $this->sectionIndex,
            'paragraph_index' => $this->paragraphIndex,
            'sentence_index' => $this->sentenceIndex,
            'claim_id' => $this->claimId,
            'claim_text' => $this->claimText,
            'claim_status' => $this->claimStatus,
            'evidence_snippet_id' => $this->evidenceSnippetId,
            'evidence_quote' => $this->evidenceQuote,
            'source_id' => $this->sourceId,
            'source_title' => $this->sourceTitle,
            'source_url' => $this->sourceUrl,
            'published_url' => $this->publishedUrl,
            'is_stale' => $this->isStale,
            'invalidation_reason' => $this->invalidationReason,
        ];
    }
}

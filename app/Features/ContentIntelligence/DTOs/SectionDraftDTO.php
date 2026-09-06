<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Section Draft DTO
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

final class SectionDraftDTO
{
    /**
     * @param  array<string>  $citedClaimIds
     */
    public function __construct(
        public readonly string $sectionId,
        public readonly string $heading,
        public readonly string $contentHtml,
        public readonly string $contentMarkdown = '',
        public readonly int $wordCount = 0,
        public readonly array $citedClaimIds = [],
        public readonly int $revisionIteration = 0
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sectionId: (string) ($data['section_id'] ?? ''),
            heading: (string) ($data['heading'] ?? ''),
            contentHtml: (string) ($data['content_html'] ?? ''),
            contentMarkdown: (string) ($data['content_markdown'] ?? ''),
            wordCount: (int) ($data['word_count'] ?? str_word_count(strip_tags($data['content_html'] ?? ''))),
            citedClaimIds: (array) ($data['cited_claim_ids'] ?? []),
            revisionIteration: (int) ($data['revision_iteration'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'section_id' => $this->sectionId,
            'heading' => $this->heading,
            'content_html' => $this->contentHtml,
            'content_markdown' => $this->contentMarkdown,
            'word_count' => $this->wordCount,
            'cited_claim_ids' => $this->citedClaimIds,
            'revision_iteration' => $this->revisionIteration,
        ];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Evidence Snippet DTO
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

use App\Features\ContentIntelligence\Enums\EpistemicState;
use Illuminate\Support\Str;

final class EvidenceSnippetDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $sourceId,
        public readonly string $extractText,
        public readonly ?int $missionId = null,
        public readonly ?string $verbatimQuote = null,
        public readonly ?string $sectionOrHeading = null,
        public readonly ?int $pageNumber = null,
        public readonly float $confidenceScore = 0.95,
        public readonly EpistemicState $epistemicState = EpistemicState::VERIFIED,
        public readonly ?string $verifiedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        $state = isset($data['epistemic_state']) && $data['epistemic_state'] instanceof EpistemicState
            ? $data['epistemic_state']
            : EpistemicState::tryFrom($data['epistemic_state'] ?? '') ?? EpistemicState::VERIFIED;

        return new self(
            id: (string) ($data['id'] ?? 'evd_'.Str::lower(Str::random(10))),
            sourceId: (int) ($data['source_id'] ?? 1),
            extractText: (string) ($data['extract_text'] ?? ''),
            missionId: isset($data['mission_id']) ? (int) $data['mission_id'] : null,
            verbatimQuote: $data['verbatim_quote'] ?? null,
            sectionOrHeading: $data['section_or_heading'] ?? null,
            pageNumber: isset($data['page_number']) ? (int) $data['page_number'] : null,
            confidenceScore: (float) ($data['confidence_score'] ?? 0.95),
            epistemicState: $state,
            verifiedAt: $data['verified_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source_id' => $this->sourceId,
            'mission_id' => $this->missionId,
            'extract_text' => $this->extractText,
            'verbatim_quote' => $this->verbatimQuote,
            'section_or_heading' => $this->sectionOrHeading,
            'page_number' => $this->pageNumber,
            'confidence_score' => $this->confidenceScore,
            'epistemic_state' => $this->epistemicState->value,
            'verified_at' => $this->verifiedAt,
        ];
    }
}

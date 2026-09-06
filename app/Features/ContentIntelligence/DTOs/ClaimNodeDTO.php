<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Claim Node DTO
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

final class ClaimNodeDTO
{
    public function __construct(
        public readonly string $claimId,
        public readonly string $statement,
        public readonly EpistemicState $epistemicState = EpistemicState::VERIFIED,
        public readonly ?string $evidenceExtract = null,
        public readonly ?string $sourceUrl = null,
        public readonly ?string $sectionTarget = null,
        public readonly float $confidenceScore = 0.95,
        public readonly bool $isControversial = false,
        public readonly array $contradictionDetails = [],
        public readonly ?string $resolutionStrategy = null
    ) {}

    public static function fromArray(array $data): self
    {
        $state = isset($data['epistemic_state']) && $data['epistemic_state'] instanceof EpistemicState
            ? $data['epistemic_state']
            : EpistemicState::tryFrom($data['epistemic_state'] ?? '') ?? EpistemicState::VERIFIED;

        return new self(
            claimId: (string) ($data['claim_id'] ?? uniqid('clm_')),
            statement: (string) ($data['statement'] ?? ''),
            epistemicState: $state,
            evidenceExtract: $data['evidence_extract'] ?? null,
            sourceUrl: $data['source_url'] ?? null,
            sectionTarget: $data['section_target'] ?? null,
            confidenceScore: (float) ($data['confidence_score'] ?? 0.95),
            isControversial: (bool) ($data['is_controversial'] ?? false),
            contradictionDetails: (array) ($data['contradiction_details'] ?? []),
            resolutionStrategy: $data['resolution_strategy'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'claim_id' => $this->claimId,
            'statement' => $this->statement,
            'epistemic_state' => $this->epistemicState->value,
            'evidence_extract' => $this->evidenceExtract,
            'source_url' => $this->sourceUrl,
            'section_target' => $this->sectionTarget,
            'confidence_score' => $this->confidenceScore,
            'is_controversial' => $this->isControversial,
            'contradiction_details' => $this->contradictionDetails,
            'resolution_strategy' => $this->resolutionStrategy,
        ];
    }
}

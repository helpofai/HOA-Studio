<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Knowledge Triple DTO
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

final class KnowledgeTripleDTO
{
    public function __construct(
        public readonly string $subject,
        public readonly string $predicate,
        public readonly string $object,
        public readonly float $confidence = 0.95,
        public readonly EpistemicState $epistemicState = EpistemicState::VERIFIED,
        public readonly ?string $sourceUrl = null
    ) {}

    public static function fromArray(array $data): self
    {
        $state = isset($data['epistemic_state']) && $data['epistemic_state'] instanceof EpistemicState
            ? $data['epistemic_state']
            : EpistemicState::tryFrom($data['epistemic_state'] ?? '') ?? EpistemicState::VERIFIED;

        return new self(
            subject: (string) ($data['subject'] ?? ''),
            predicate: (string) ($data['predicate'] ?? ''),
            object: (string) ($data['object'] ?? ''),
            confidence: (float) ($data['confidence'] ?? 0.95),
            epistemicState: $state,
            sourceUrl: $data['source_url'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'predicate' => $this->predicate,
            'object' => $this->object,
            'confidence' => $this->confidence,
            'epistemic_state' => $this->epistemicState->value,
            'source_url' => $this->sourceUrl,
        ];
    }
}

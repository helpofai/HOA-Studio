<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - World Relationship DTO
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

use Illuminate\Support\Str;

final class WorldRelationshipDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $subjectEntityId,
        public readonly string $predicate,
        public readonly string $objectEntityId,
        public readonly float $confidence = 0.95,
        public readonly float $strength = 1.0,
        public readonly ?string $explanation = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? 'rel_'.Str::lower(Str::random(10))),
            subjectEntityId: (string) ($data['subject_entity_id'] ?? ''),
            predicate: (string) ($data['predicate'] ?? 'relates_to'),
            objectEntityId: (string) ($data['object_entity_id'] ?? ''),
            confidence: (float) ($data['confidence'] ?? 0.95),
            strength: (float) ($data['strength'] ?? 1.0),
            explanation: $data['explanation'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subject_entity_id' => $this->subjectEntityId,
            'predicate' => $this->predicate,
            'object_entity_id' => $this->objectEntityId,
            'confidence' => $this->confidence,
            'strength' => $this->strength,
            'explanation' => $this->explanation,
        ];
    }
}

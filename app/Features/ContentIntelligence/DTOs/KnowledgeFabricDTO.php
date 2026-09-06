<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Knowledge Fabric DTO
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

final class KnowledgeFabricDTO
{
    /**
     * @param  array<SourceIntelligenceDTO>  $sources
     * @param  array<KnowledgeTripleDTO>  $triples
     * @param  array<ClaimNodeDTO>  $claims
     */
    public function __construct(
        public readonly array $sources = [],
        public readonly array $triples = [],
        public readonly array $claims = [],
        public readonly int $contradictionsDetected = 0,
        public readonly int $contradictionsResolved = 0,
        public readonly float $knowledgeConfidence = 0.95
    ) {}

    public static function fromArray(array $data): self
    {
        $sources = [];
        foreach (($data['sources'] ?? []) as $src) {
            $sources[] = $src instanceof SourceIntelligenceDTO ? $src : SourceIntelligenceDTO::fromArray($src);
        }

        $triples = [];
        foreach (($data['triples'] ?? []) as $trp) {
            $triples[] = $trp instanceof KnowledgeTripleDTO ? $trp : KnowledgeTripleDTO::fromArray($trp);
        }

        $claims = [];
        foreach (($data['claims'] ?? []) as $clm) {
            $claims[] = $clm instanceof ClaimNodeDTO ? $clm : ClaimNodeDTO::fromArray($clm);
        }

        return new self(
            sources: $sources,
            triples: $triples,
            claims: $claims,
            contradictionsDetected: (int) ($data['contradictions_detected'] ?? 0),
            contradictionsResolved: (int) ($data['contradictions_resolved'] ?? 0),
            knowledgeConfidence: (float) ($data['knowledge_confidence'] ?? 0.95)
        );
    }

    public function toArray(): array
    {
        return [
            'sources' => array_map(fn (SourceIntelligenceDTO $s) => $s->toArray(), $this->sources),
            'triples' => array_map(fn (KnowledgeTripleDTO $t) => $t->toArray(), $this->triples),
            'claims' => array_map(fn (ClaimNodeDTO $c) => $c->toArray(), $this->claims),
            'contradictions_detected' => $this->contradictionsDetected,
            'contradictions_resolved' => $this->contradictionsResolved,
            'knowledge_confidence' => $this->knowledgeConfidence,
        ];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Genome DTO
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

class ContentGenomeDTO
{
    public function __construct(
        public string $genomeSignature,
        public string $title,
        public array $missionDna = [],
        public array $topicsDna = [],
        public array $entitiesDna = [],
        public array $claimsDna = [],
        public array $factsDna = [],
        public array $sourcesDna = [],
        public array $qualityDna = [],
        public array $reusableFragments = []
    ) {}

    public function toArray(): array
    {
        return [
            'genome_signature' => $this->genomeSignature,
            'title' => $this->title,
            'mission_dna' => $this->missionDna,
            'topics_dna' => $this->topicsDna,
            'entities_dna' => $this->entitiesDna,
            'claims_dna' => $this->claimsDna,
            'facts_dna' => $this->factsDna,
            'sources_dna' => $this->sourcesDna,
            'quality_dna' => $this->qualityDna,
            'reusable_fragments' => $this->reusableFragments,
        ];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Adaptive Outline DTO
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

final class AdaptiveOutlineDTO
{
    /**
     * @param  array<SectionNodeDTO>  $sections
     * @param  array<string, array<string>>  $sectionDependencyMap
     */
    public function __construct(
        public readonly int $totalSections,
        public readonly int $targetWordCount,
        public readonly array $sections = [],
        public readonly array $sectionDependencyMap = [],
        public readonly float $outlineConfidence = 0.95
    ) {}

    public static function fromArray(array $data): self
    {
        $sections = [];
        foreach (($data['sections'] ?? []) as $sec) {
            $sections[] = $sec instanceof SectionNodeDTO ? $sec : SectionNodeDTO::fromArray($sec);
        }

        return new self(
            totalSections: (int) ($data['total_sections'] ?? count($sections)),
            targetWordCount: (int) ($data['target_word_count'] ?? 2500),
            sections: $sections,
            sectionDependencyMap: (array) ($data['section_dependency_map'] ?? []),
            outlineConfidence: (float) ($data['outline_confidence'] ?? 0.95)
        );
    }

    public function toArray(): array
    {
        return [
            'total_sections' => $this->totalSections,
            'target_word_count' => $this->targetWordCount,
            'sections' => array_map(fn (SectionNodeDTO $s) => $s->toArray(), $this->sections),
            'section_dependency_map' => $this->sectionDependencyMap,
            'outline_confidence' => $this->outlineConfidence,
        ];
    }
}

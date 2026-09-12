<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - ContentBlueprint DTO
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

namespace App\Features\AI\Data;

/**
 * Stage 15 - 18: Editorial Strategy & Adaptive Section Contracts Blueprint DTO
 * Defines section contracts binding required claims, evidence, and audience goals to each section.
 */
class ContentBlueprint
{
    public function __construct(
        public string $positioning = 'Authoritative & Practical Guide',
        public string $uniqueValueProposition = 'Empirical benchmarks with zero generic fluff',
        public string $readerJourney = 'Beginner concept -> Practical implementation -> Advanced optimization',
        public int $targetWordCount = 1500,
        public array $sectionContracts = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            positioning: $data['positioning'] ?? 'Authoritative & Practical Guide',
            uniqueValueProposition: $data['unique_value_proposition'] ?? 'Empirical benchmarks with zero generic fluff',
            readerJourney: $data['reader_journey'] ?? 'Beginner concept -> Practical implementation -> Advanced optimization',
            targetWordCount: $data['target_word_count'] ?? 1500,
            sectionContracts: $data['section_contracts'] ?? []
        );
    }

    public function addSectionContract(
        string $sectionId,
        string $heading,
        string $purpose,
        string $readerGoal,
        array $questions = [],
        array $requiredClaimIds = [],
        array $keywords = [],
        bool $requiresExamples = true,
        int $targetWords = 400
    ): void {
        $this->sectionContracts[] = [
            'section_id' => $sectionId,
            'heading' => $heading,
            'purpose' => $purpose,
            'reader_goal' => $readerGoal,
            'questions' => $questions,
            'required_claim_ids' => $requiredClaimIds,
            'keywords' => $keywords,
            'requires_examples' => $requiresExamples,
            'target_words' => $targetWords,
        ];
    }

    public function toArray(): array
    {
        return [
            'positioning' => $this->positioning,
            'unique_value_proposition' => $this->uniqueValueProposition,
            'reader_journey' => $this->readerJourney,
            'target_word_count' => $this->targetWordCount,
            'section_contracts' => $this->sectionContracts,
        ];
    }
}

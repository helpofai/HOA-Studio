<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - AudienceProfile DTO
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
 * Stage 02: Audience Profile DTO
 * Defines who the reader is, what they know, jargon to explain, and common pitfalls.
 */
class AudienceProfile
{
    public function __construct(
        public string $persona = 'General Practitioner',
        public array $priorKnowledge = [],
        public array $knowledgeGaps = [],
        public array $readerGoals = [],
        public array $painPoints = [],
        public array $familiarTerms = [],
        public array $termsNeedingExplanation = [],
        public array $commonBeginnerMistakes = [],
        public array $anticipatedQuestions = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            persona: $data['persona'] ?? 'General Practitioner',
            priorKnowledge: $data['prior_knowledge'] ?? [],
            knowledgeGaps: $data['knowledge_gaps'] ?? [],
            readerGoals: $data['reader_goals'] ?? [],
            painPoints: $data['pain_points'] ?? [],
            familiarTerms: $data['familiar_terms'] ?? [],
            termsNeedingExplanation: $data['terms_needing_explanation'] ?? [],
            commonBeginnerMistakes: $data['common_beginner_mistakes'] ?? [],
            anticipatedQuestions: $data['anticipated_questions'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'persona' => $this->persona,
            'prior_knowledge' => $this->priorKnowledge,
            'knowledge_gaps' => $this->knowledgeGaps,
            'reader_goals' => $this->readerGoals,
            'pain_points' => $this->painPoints,
            'familiar_terms' => $this->familiarTerms,
            'terms_needing_explanation' => $this->termsNeedingExplanation,
            'common_beginner_mistakes' => $this->commonBeginnerMistakes,
            'anticipated_questions' => $this->anticipatedQuestions,
        ];
    }
}

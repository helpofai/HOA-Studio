<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - IntentContract DTO
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
 * Stage 03: Intent Contract DTO
 * Defines search intent, expected answer formats, and mandatory questions to fulfill.
 */
class IntentContract
{
    public function __construct(
        public string $primaryIntent = 'informational',
        public array $secondaryIntents = ['implementation', 'comparison'],
        public string $userGoal = 'Learn how to implement topic effectively',
        public string $expectedAnswerFormat = 'step_by_step_explanation',
        public string $requiredDepth = 'comprehensive',
        public array $mustAnswerQuestions = [],
        public array $shouldAnswerQuestions = [],
        public array $serpFeatureTargets = ['featured_snippet', 'faq_schema', 'table_comparison']
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            primaryIntent: $data['primary_intent'] ?? 'informational',
            secondaryIntents: $data['secondary_intents'] ?? ['implementation', 'comparison'],
            userGoal: $data['user_goal'] ?? 'Learn how to implement topic effectively',
            expectedAnswerFormat: $data['expected_answer_format'] ?? 'step_by_step_explanation',
            requiredDepth: $data['required_depth'] ?? 'comprehensive',
            mustAnswerQuestions: $data['must_answer_questions'] ?? [],
            shouldAnswerQuestions: $data['should_answer_questions'] ?? [],
            serpFeatureTargets: $data['serp_feature_targets'] ?? ['featured_snippet', 'faq_schema', 'table_comparison']
        );
    }

    public function toArray(): array
    {
        return [
            'primary_intent' => $this->primaryIntent,
            'secondary_intents' => $this->secondaryIntents,
            'user_goal' => $this->userGoal,
            'expected_answer_format' => $this->expectedAnswerFormat,
            'required_depth' => $this->requiredDepth,
            'must_answer_questions' => $this->mustAnswerQuestions,
            'should_answer_questions' => $this->shouldAnswerQuestions,
            'serp_feature_targets' => $this->serpFeatureTargets,
        ];
    }
}

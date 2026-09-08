<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Section Node DTO
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

final class SectionNodeDTO
{
    /**
     * @param  string  $sectionId  Unique section identifier (e.g. 'sec_01_intro')
     * @param  string  $level  'H2' | 'H3'
     * @param  string  $intentRole  Educational, Technical Guide, Case Study, Troubleshooting, FAQ
     * @param  array<string>  $mustAnswerQuestions
     * @param  array<string>  $assignedClaimIds
     * @param  array<string>  $requiredKeywords
     * @param  array<string>  $requiredEntities
     * @param  array<string>  $requiredCodeSnippets
     * @param  array<string>  $dependencySections
     */
    public function __construct(
        public readonly string $sectionId,
        public readonly string $heading,
        public readonly string $level = 'H2',
        public readonly string $intentRole = 'Technical Guide',
        public readonly string $purpose = '',
        public readonly int $targetWordCount = 500,
        public readonly array $mustAnswerQuestions = [],
        public readonly array $assignedClaimIds = [],
        public readonly array $requiredKeywords = [],
        public readonly array $requiredEntities = [],
        public readonly array $requiredCodeSnippets = [],
        public readonly array $dependencySections = [],
        public readonly ?string $mediaPlaceholder = null,
        public readonly int $writingPriority = 1
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sectionId: (string) ($data['section_id'] ?? uniqid('sec_')),
            heading: (string) ($data['heading'] ?? 'Untitled Section'),
            level: (string) ($data['level'] ?? 'H2'),
            intentRole: (string) ($data['intent_role'] ?? 'Technical Guide'),
            purpose: (string) ($data['purpose'] ?? ''),
            targetWordCount: (int) ($data['target_word_count'] ?? 500),
            mustAnswerQuestions: (array) ($data['must_answer_questions'] ?? []),
            assignedClaimIds: (array) ($data['assigned_claim_ids'] ?? []),
            requiredKeywords: (array) ($data['required_keywords'] ?? []),
            requiredEntities: (array) ($data['required_entities'] ?? []),
            requiredCodeSnippets: (array) ($data['required_code_snippets'] ?? []),
            dependencySections: (array) ($data['dependency_sections'] ?? []),
            mediaPlaceholder: $data['media_placeholder'] ?? null,
            writingPriority: (int) ($data['writing_priority'] ?? 1)
        );
    }

    public function toArray(): array
    {
        return [
            'section_id' => $this->sectionId,
            'heading' => $this->heading,
            'level' => $this->level,
            'intent_role' => $this->intentRole,
            'purpose' => $this->purpose,
            'target_word_count' => $this->targetWordCount,
            'must_answer_questions' => $this->mustAnswerQuestions,
            'assigned_claim_ids' => $this->assignedClaimIds,
            'required_keywords' => $this->requiredKeywords,
            'required_entities' => $this->requiredEntities,
            'required_code_snippets' => $this->requiredCodeSnippets,
            'dependency_sections' => $this->dependencySections,
            'media_placeholder' => $this->mediaPlaceholder,
            'writing_priority' => $this->writingPriority,
        ];
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'key', 'id' => $this->sectionId,
            'assignedClaims' => $this->assignedClaimIds,
            'dependencies' => $this->dependencySections,
            default => null,
        };
    }

    public function __isset(string $name): bool
    {
        return in_array($name, ['key', 'id', 'assignedClaims', 'dependencies']);
    }
}

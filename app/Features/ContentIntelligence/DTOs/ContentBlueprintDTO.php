<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Blueprint DTO
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

final class ContentBlueprintDTO
{
    /**
     * @param  string  $articleAngle  The core information-gain angle
     * @param  string  $uniqueValueProposition  What sets this content apart from competitor pages
     * @param  array{current_pain_points: array<string>, desired_mastery: string}  $targetTransformation
     * @param  array<string>  $requiredSections
     * @param  array<string>  $optionalSections
     * @param  array<string>  $requiredEntities
     * @param  array<string>  $internalLinks
     * @param  array<string>  $externalSources
     * @param  array<string>  $faqRequirements
     * @param  array{cta_type: string, primary_action: string}  $conversionStrategy
     * @param  array<string, mixed>  $qualityTargets
     */
    public function __construct(
        public readonly string $articleAngle,
        public readonly string $uniqueValueProposition,
        public readonly array $targetTransformation = [
            'current_pain_points' => [],
            'desired_mastery' => '',
        ],
        public readonly array $requiredSections = [],
        public readonly array $optionalSections = [],
        public readonly array $requiredEntities = [],
        public readonly array $internalLinks = [],
        public readonly array $externalSources = [],
        public readonly array $faqRequirements = [],
        public readonly array $conversionStrategy = [
            'cta_type' => 'authoritative_consulting',
            'primary_action' => 'Explore HOA enterprise engineering blueprints',
        ],
        public readonly array $qualityTargets = [
            'min_health_score' => 90,
            'flesch_reading_ease' => 60,
            'evidence_grounding_threshold' => 0.90,
        ]
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            articleAngle: (string) ($data['article_angle'] ?? 'Authoritative production engineering blueprint'),
            uniqueValueProposition: (string) ($data['unique_value_proposition'] ?? 'Zero-fluff, production-tested configuration with verified claims'),
            targetTransformation: (array) ($data['target_transformation'] ?? [
                'current_pain_points' => [],
                'desired_mastery' => 'Complete operational and architectural command',
            ]),
            requiredSections: (array) ($data['required_sections'] ?? []),
            optionalSections: (array) ($data['optional_sections'] ?? []),
            requiredEntities: (array) ($data['required_entities'] ?? []),
            internalLinks: (array) ($data['internal_links'] ?? []),
            externalSources: (array) ($data['external_sources'] ?? []),
            faqRequirements: (array) ($data['faq_requirements'] ?? []),
            conversionStrategy: (array) ($data['conversion_strategy'] ?? [
                'cta_type' => 'authoritative_consulting',
                'primary_action' => 'Explore HOA enterprise engineering blueprints',
            ]),
            qualityTargets: (array) ($data['quality_targets'] ?? [
                'min_health_score' => 90,
                'flesch_reading_ease' => 60,
                'evidence_grounding_threshold' => 0.90,
            ])
        );
    }

    public function toArray(): array
    {
        return [
            'article_angle' => $this->articleAngle,
            'unique_value_proposition' => $this->uniqueValueProposition,
            'target_transformation' => $this->targetTransformation,
            'required_sections' => $this->requiredSections,
            'optional_sections' => $this->optionalSections,
            'required_entities' => $this->requiredEntities,
            'internal_links' => $this->internalLinks,
            'external_sources' => $this->externalSources,
            'faq_requirements' => $this->faqRequirements,
            'conversion_strategy' => $this->conversionStrategy,
            'quality_targets' => $this->qualityTargets,
        ];
    }
}

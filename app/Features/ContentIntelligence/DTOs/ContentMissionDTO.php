<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Mission DTO
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

use App\Features\ContentIntelligence\Enums\ResearchBudgetTier;
use App\Features\ContentIntelligence\Enums\RiskLevel;
use InvalidArgumentException;

final class ContentMissionDTO
{
    /**
     * @param  array<string>  $secondaryObjectives
     * @param  array<string, mixed>  $targetAudience  Persona, expertise level, pain points
     * @param  array{min: int, max: int}  $targetWordCountRange
     * @param  array<string>  $successCriteria
     * @param  array<string, mixed>  $customConstraints
     */
    public function __construct(
        public readonly string $topic,
        public readonly string $primaryObjective,
        public readonly array $secondaryObjectives = [],
        public readonly array $targetAudience = [
            'persona' => 'General Professional',
            'expertise_level' => 'Intermediate',
            'pain_points' => [],
        ],
        public readonly string $marketGeo = 'Global',
        public readonly string $language = 'en',
        public readonly string $contentType = 'comprehensive_guide',
        public readonly string $businessGoal = 'authority_and_engagement',
        public readonly string $searchGoal = 'organic_search_rank_1',
        public readonly ?int $brandProfileId = null,
        public readonly string $freshnessRequirement = 'current_standard',
        public readonly string $trustRequirement = 'authoritative_sources_only',
        public readonly string $evidenceRequirement = 'verified_factual_citations',
        public readonly array $targetWordCountRange = ['min' => 1500, 'max' => 3000],
        public readonly RiskLevel $riskLevel = RiskLevel::MEDIUM,
        public readonly ResearchBudgetTier $researchBudgetTier = ResearchBudgetTier::STANDARD,
        public readonly array $successCriteria = [],
        public readonly array $customConstraints = []
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (trim($this->topic) === '') {
            throw new InvalidArgumentException('Content mission topic cannot be empty.');
        }

        if (trim($this->primaryObjective) === '') {
            throw new InvalidArgumentException('Content mission primary objective cannot be empty.');
        }

        if ($this->targetWordCountRange['min'] > $this->targetWordCountRange['max']) {
            throw new InvalidArgumentException('Target word count minimum cannot exceed maximum.');
        }
    }

    /**
     * Factory from an associative array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            topic: (string) ($data['topic'] ?? ''),
            primaryObjective: (string) ($data['primary_objective'] ?? $data['topic'] ?? ''),
            secondaryObjectives: (array) ($data['secondary_objectives'] ?? []),
            targetAudience: (array) ($data['target_audience'] ?? [
                'persona' => $data['audience'] ?? 'General Professional',
                'expertise_level' => $data['expertise_level'] ?? 'Intermediate',
                'pain_points' => $data['pain_points'] ?? [],
            ]),
            marketGeo: (string) ($data['market_geo'] ?? $data['market'] ?? 'Global'),
            language: (string) ($data['language'] ?? 'en'),
            contentType: (string) ($data['content_type'] ?? 'comprehensive_guide'),
            businessGoal: (string) ($data['business_goal'] ?? 'authority_and_engagement'),
            searchGoal: (string) ($data['search_goal'] ?? 'organic_search_rank_1'),
            brandProfileId: isset($data['brand_profile_id']) ? (int) $data['brand_profile_id'] : null,
            freshnessRequirement: (string) ($data['freshness_requirement'] ?? 'current_standard'),
            trustRequirement: (string) ($data['trust_requirement'] ?? 'authoritative_sources_only'),
            evidenceRequirement: (string) ($data['evidence_requirement'] ?? 'verified_factual_citations'),
            targetWordCountRange: [
                'min' => (int) ($data['target_word_count_range']['min'] ?? $data['min_words'] ?? 1500),
                'max' => (int) ($data['target_word_count_range']['max'] ?? $data['max_words'] ?? 3000),
            ],
            riskLevel: isset($data['risk_level']) && $data['risk_level'] instanceof RiskLevel
                ? $data['risk_level']
                : RiskLevel::tryFrom($data['risk_level'] ?? 'medium') ?? RiskLevel::MEDIUM,
            researchBudgetTier: isset($data['research_budget_tier']) && $data['research_budget_tier'] instanceof ResearchBudgetTier
                ? $data['research_budget_tier']
                : ResearchBudgetTier::tryFrom($data['research_budget_tier'] ?? 'standard') ?? ResearchBudgetTier::STANDARD,
            successCriteria: (array) ($data['success_criteria'] ?? []),
            customConstraints: (array) ($data['custom_constraints'] ?? [])
        );
    }

    /**
     * Serialize to array for database JSON storage.
     */
    public function toArray(): array
    {
        return [
            'topic' => $this->topic,
            'primary_objective' => $this->primaryObjective,
            'secondary_objectives' => $this->secondaryObjectives,
            'target_audience' => $this->targetAudience,
            'market_geo' => $this->marketGeo,
            'language' => $this->language,
            'content_type' => $this->contentType,
            'business_goal' => $this->businessGoal,
            'search_goal' => $this->searchGoal,
            'brand_profile_id' => $this->brandProfileId,
            'freshness_requirement' => $this->freshnessRequirement,
            'trust_requirement' => $this->trustRequirement,
            'evidence_requirement' => $this->evidenceRequirement,
            'target_word_count_range' => $this->targetWordCountRange,
            'risk_level' => $this->riskLevel->value,
            'research_budget_tier' => $this->researchBudgetTier->value,
            'success_criteria' => $this->successCriteria,
            'custom_constraints' => $this->customConstraints,
        ];
    }

    /**
     * Generate machine-readable specification prompt block for AI agents.
     */
    public function toMachinePrompt(): string
    {
        $payload = json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return "=== CONTENT MISSION SPECIFICATION ===\n".$payload."\n=== END MISSION SPECIFICATION ===";
    }
}

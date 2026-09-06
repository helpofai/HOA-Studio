<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Critic Score DTO
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

final class CriticScoreDTO
{
    /**
     * @param  float  $factGrounding  0 - 100
     * @param  float  $completeness  0 - 100
     * @param  float  $searchIntent  0 - 100
     * @param  float  $brandVoice  0 - 100
     * @param  float  $readability  0 - 100
     * @param  float  $seoOptimization  0 - 100
     * @param  float  $overallScore  0 - 100
     * @param  array<string>  $issues
     * @param  array<string>  $revisionDirectives
     */
    public function __construct(
        public readonly float $factGrounding,
        public readonly float $completeness,
        public readonly float $searchIntent,
        public readonly float $brandVoice,
        public readonly float $readability,
        public readonly float $seoOptimization,
        public readonly float $overallScore,
        public readonly bool $passed,
        public readonly array $issues = [],
        public readonly array $revisionDirectives = []
    ) {}

    public static function compute(
        float $factGrounding,
        float $completeness,
        float $searchIntent,
        float $brandVoice,
        float $readability,
        float $seoOptimization,
        array $issues = [],
        array $revisionDirectives = []
    ): self {
        // Weighted composite overall score
        $overall = round(
            ($factGrounding * 0.25) +
            ($completeness * 0.20) +
            ($searchIntent * 0.15) +
            ($brandVoice * 0.15) +
            ($readability * 0.15) +
            ($seoOptimization * 0.10),
            1
        );

        $passed = ($overall >= 80.0) && ($factGrounding >= 70.0) && ($completeness >= 70.0) && empty($issues);

        return new self(
            factGrounding: $factGrounding,
            completeness: $completeness,
            searchIntent: $searchIntent,
            brandVoice: $brandVoice,
            readability: $readability,
            seoOptimization: $seoOptimization,
            overallScore: $overall,
            passed: $passed,
            issues: $issues,
            revisionDirectives: $revisionDirectives
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            factGrounding: (float) ($data['fact_grounding'] ?? 85.0),
            completeness: (float) ($data['completeness'] ?? 85.0),
            searchIntent: (float) ($data['search_intent'] ?? 85.0),
            brandVoice: (float) ($data['brand_voice'] ?? 85.0),
            readability: (float) ($data['readability'] ?? 85.0),
            seoOptimization: (float) ($data['seo_optimization'] ?? 85.0),
            overallScore: (float) ($data['overall_score'] ?? 85.0),
            passed: (bool) ($data['passed'] ?? false),
            issues: (array) ($data['issues'] ?? []),
            revisionDirectives: (array) ($data['revision_directives'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'fact_grounding' => $this->factGrounding,
            'completeness' => $this->completeness,
            'search_intent' => $this->searchIntent,
            'brand_voice' => $this->brandVoice,
            'readability' => $this->readability,
            'seo_optimization' => $this->seoOptimization,
            'overall_score' => $this->overallScore,
            'passed' => $this->passed,
            'issues' => $this->issues,
            'revision_directives' => $this->revisionDirectives,
        ];
    }
}

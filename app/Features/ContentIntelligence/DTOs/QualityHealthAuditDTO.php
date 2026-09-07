<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Quality Health Audit DTO
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

class QualityHealthAuditDTO
{
    /**
     * @param  QualityDimensionScoreDTO[]  $dimensions
     */
    public function __construct(
        public int $overallScore,
        public string $grade,
        public array $dimensions,
        public array $keyStrengths = [],
        public array $criticalGaps = [],
        public array $recommendations = []
    ) {}

    public static function computeGrade(int $score): string
    {
        return match (true) {
            $score >= 95 => 'A+',
            $score >= 88 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    public function toArray(): array
    {
        return [
            'overall_score' => $this->overallScore,
            'grade' => $this->grade,
            'dimensions' => array_map(fn (QualityDimensionScoreDTO $dim) => $dim->toArray(), $this->dimensions),
            'key_strengths' => $this->keyStrengths,
            'critical_gaps' => $this->criticalGaps,
            'recommendations' => $this->recommendations,
        ];
    }
}

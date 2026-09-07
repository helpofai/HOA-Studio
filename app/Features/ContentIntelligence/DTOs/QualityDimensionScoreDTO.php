<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Quality Dimension Score DTO
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

class QualityDimensionScoreDTO
{
    public function __construct(
        public string $key,
        public string $name,
        public float $score,
        public float $weight,
        public string $rating,
        public array $reasons = []
    ) {}

    public static function create(string $key, string $name, float $score, float $weight, array $reasons = []): self
    {
        $rating = match (true) {
            $score >= 90.0 => 'EXCELLENT',
            $score >= 80.0 => 'GOOD',
            $score >= 65.0 => 'NEEDS_IMPROVEMENT',
            default => 'CRITICAL',
        };

        return new self($key, $name, round($score, 1), $weight, $rating, $reasons);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'score' => $this->score,
            'weight' => $this->weight,
            'rating' => $this->rating,
            'reasons' => $this->reasons,
        ];
    }
}

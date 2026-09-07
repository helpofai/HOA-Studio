<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Strategy Candidate DTO
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

class StrategyCandidateDTO
{
    /**
     * @param  array<string, mixed>  $learningPayload
     */
    public function __construct(
        public string $strategyKey,
        public string $category,
        public int $evidenceCount,
        public float $confidence,
        public string $status,
        public array $learningPayload = [],
        public bool $readyForAdoption = false
    ) {}

    public function toArray(): array
    {
        return [
            'strategy_key' => $this->strategyKey,
            'category' => $this->category,
            'evidence_count' => $this->evidenceCount,
            'confidence' => $this->confidence,
            'status' => $this->status,
            'learning_payload' => $this->learningPayload,
            'ready_for_adoption' => $this->readyForAdoption,
        ];
    }
}

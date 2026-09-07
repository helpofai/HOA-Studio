<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Decay Reconciliation DTO
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

final class DecayReconciliationDTO
{
    public function __construct(
        public readonly int $totalChecked = 0,
        public readonly int $activeCount = 0,
        public readonly int $uncertainCount = 0,
        public readonly int $decayedCount = 0,
        public readonly int $supersededCount = 0,
        public readonly array $updatedMemoryIds = [],
        public readonly int $impactedElementCount = 0,
        public readonly array $impactedElements = []
    ) {}

    public function toArray(): array
    {
        return [
            'total_checked' => $this->totalChecked,
            'active_count' => $this->activeCount,
            'uncertain_count' => $this->uncertainCount,
            'decayed_count' => $this->decayedCount,
            'superseded_count' => $this->supersededCount,
            'updated_memory_ids' => $this->updatedMemoryIds,
            'impacted_element_count' => $this->impactedElementCount,
            'impacted_elements' => $this->impactedElements,
        ];
    }
}

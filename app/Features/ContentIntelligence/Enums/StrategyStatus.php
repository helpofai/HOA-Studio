<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Strategy Status Enum
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

namespace App\Features\ContentIntelligence\Enums;

enum StrategyStatus: string
{
    case OBSERVATION = 'observation';
    case CANDIDATE = 'candidate';
    case VALIDATED = 'validated';
    case ADOPTED = 'adopted';
    case REJECTED = 'rejected';

    public function canAdopt(): bool
    {
        return $this === self::VALIDATED || $this === self::ADOPTED;
    }

    public function label(): string
    {
        return match ($this) {
            self::OBSERVATION => 'Initial Observation (Evidence = 1)',
            self::CANDIDATE => 'Candidate Hypothesis (Evidence >= 2)',
            self::VALIDATED => 'Empirically Validated (Evidence >= 4)',
            self::ADOPTED => 'Adopted Core Strategy (Evidence >= 5)',
            self::REJECTED => 'Rejected / Disproven',
        };
    }
}

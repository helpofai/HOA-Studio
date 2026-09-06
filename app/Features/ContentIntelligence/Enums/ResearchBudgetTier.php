<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Research Budget Tier Enum
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

enum ResearchBudgetTier: string
{
    case QUICK = 'quick';
    case STANDARD = 'standard';
    case DEEP = 'deep';
    case EXPERT = 'expert';

    public function maxQueries(): int
    {
        return match ($this) {
            self::QUICK => 5,
            self::STANDARD => 15,
            self::DEEP => 30,
            self::EXPERT => 60,
        };
    }

    public function minConfidenceThreshold(): float
    {
        return match ($this) {
            self::QUICK => 0.70,
            self::STANDARD => 0.85,
            self::DEEP => 0.92,
            self::EXPERT => 0.96,
        };
    }
}

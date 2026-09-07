<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Risk Level Enum
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

enum RiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function requiresPrimarySources(): bool
    {
        return $this === self::HIGH || $this === self::CRITICAL;
    }

    public function requiresHumanSignoff(): bool
    {
        return $this === self::HIGH || $this === self::CRITICAL;
    }
}

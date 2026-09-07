<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Status Enum
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

enum MemoryStatus: string
{
    case ACTIVE = 'active';
    case SUPERSEDED = 'superseded';
    case DECAYED = 'decayed';
    case UNCERTAIN = 'uncertain';
    case CONTRADICTED = 'contradicted';
    case REJECTED = 'rejected';

    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }

    public function requiresRecheck(): bool
    {
        return in_array($this, [self::UNCERTAIN, self::DECAYED, self::CONTRADICTED]);
    }
}

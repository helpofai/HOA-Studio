<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Repair Status Enum
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

enum RepairStatus: string
{
    case DETECTED = 'detected';
    case REPAIRING = 'repairing';
    case RESOLVED = 'resolved';
    case ESCALATED = 'escalated';
    case FAILED = 'failed';

    public function isCompleted(): bool
    {
        return $this === self::RESOLVED || $this === self::FAILED;
    }
}

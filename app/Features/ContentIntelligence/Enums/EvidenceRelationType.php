<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Evidence Relation Type Enum
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

enum EvidenceRelationType: string
{
    case SUPPORTS = 'supports';
    case REFUTES = 'refutes';
    case QUALIFIES = 'qualifies';
    case CONTEXTUALIZES = 'contextualizes';

    public function isAffirmative(): bool
    {
        return $this === self::SUPPORTS;
    }

    public function isContradictory(): bool
    {
        return $this === self::REFUTES;
    }
}

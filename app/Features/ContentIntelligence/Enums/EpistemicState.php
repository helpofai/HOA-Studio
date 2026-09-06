<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Epistemic State Enum
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

enum EpistemicState: string
{
    case VERIFIED = 'verified';
    case PARTIALLY_VERIFIED = 'partially_verified';
    case UNVERIFIED = 'unverified';
    case CONTRADICTED = 'contradicted';
    case OUTDATED = 'outdated';
    case OPINION = 'opinion';
    case INFERENCE = 'inference';
    case ESTIMATE = 'estimate';
    case UNKNOWN = 'unknown';

    public function isAssertable(): bool
    {
        return in_array($this, [self::VERIFIED, self::PARTIALLY_VERIFIED]);
    }

    public function requiresCaution(): bool
    {
        return in_array($this, [self::PARTIALLY_VERIFIED, self::ESTIMATE, self::INFERENCE]);
    }

    public function isBlocked(): bool
    {
        return in_array($this, [self::UNVERIFIED, self::CONTRADICTED, self::OUTDATED]);
    }
}

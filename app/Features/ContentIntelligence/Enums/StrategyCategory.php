<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Strategy Category Enum
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

enum StrategyCategory: string
{
    case STRUCTURE = 'structure';
    case SOURCES = 'sources';
    case TONE = 'tone';
    case WORKFLOW = 'workflow';
    case SEO = 'seo';

    public function label(): string
    {
        return match ($this) {
            self::STRUCTURE => 'Structural Architecture & Outlining',
            self::SOURCES => 'Authoritative Source Selection',
            self::TONE => 'Tone, Cadence & Brand Voice',
            self::WORKFLOW => 'Workflow Execution & Routing',
            self::SEO => 'Search Intent & Entity Density',
        };
    }
}

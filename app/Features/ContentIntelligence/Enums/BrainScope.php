<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Brain Scope Enum
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

enum BrainScope: string
{
    case SITE = 'site';
    case PROJECT = 'project';
    case ARTICLE = 'article';

    public function label(): string
    {
        return match ($this) {
            self::SITE => 'Level 1: Site Brain (Entire Website & Catalog)',
            self::PROJECT => 'Level 2: Project Brain (Mission Lifecycle)',
            self::ARTICLE => 'Level 3: Article Brain (Granular Element Graph)',
        };
    }
}

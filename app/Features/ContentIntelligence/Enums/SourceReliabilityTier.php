<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Source Reliability Tier Enum
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

enum SourceReliabilityTier: string
{
    case OFFICIAL_DOCUMENTATION = 'official_documentation';
    case GOVERNMENT_SOURCE = 'government_source';
    case ACADEMIC_PAPER = 'academic_paper';
    case PRIMARY_RESEARCH = 'primary_research';
    case RECOGNIZED_ORGANIZATION = 'recognized_organization';
    case EXPERT_PUBLICATION = 'expert_publication';
    case INDUSTRY_PUBLICATION = 'industry_publication';
    case GENERAL_WEBSITE = 'general_website';
    case FORUM_SOCIAL = 'forum_social';

    public function defaultReliabilityScore(): int
    {
        return match ($this) {
            self::OFFICIAL_DOCUMENTATION => 98,
            self::GOVERNMENT_SOURCE => 97,
            self::ACADEMIC_PAPER => 95,
            self::PRIMARY_RESEARCH => 94,
            self::RECOGNIZED_ORGANIZATION => 91,
            self::EXPERT_PUBLICATION => 88,
            self::INDUSTRY_PUBLICATION => 83,
            self::GENERAL_WEBSITE => 65,
            self::FORUM_SOCIAL => 40,
        };
    }

    public function isPrimary(): bool
    {
        return in_array($this, [
            self::OFFICIAL_DOCUMENTATION,
            self::GOVERNMENT_SOURCE,
            self::ACADEMIC_PAPER,
            self::PRIMARY_RESEARCH,
        ]);
    }
}

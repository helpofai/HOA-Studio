<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Repair Unit Type Enum
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

enum RepairUnitType: string
{
    case SENTENCE = 'sentence';
    case PARAGRAPH = 'paragraph';
    case SECTION = 'section';
    case ARTICLE = 'article';

    /**
     * Escalation ladder: sentence -> paragraph -> section -> article
     */
    public function escalate(): ?self
    {
        return match ($this) {
            self::SENTENCE => self::PARAGRAPH,
            self::PARAGRAPH => self::SECTION,
            self::SECTION => self::ARTICLE,
            self::ARTICLE => null,
        };
    }

    public function level(): int
    {
        return match ($this) {
            self::SENTENCE => 1,
            self::PARAGRAPH => 2,
            self::SECTION => 3,
            self::ARTICLE => 4,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SENTENCE => 'Sentence (Smallest Unit)',
            self::PARAGRAPH => 'Paragraph (Local Context)',
            self::SECTION => 'Section (Structural Unit)',
            self::ARTICLE => 'Article (Full Document Escalation)',
        };
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Problem Category Enum
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

enum ProblemCategory: string
{
    case FACTUAL_ERROR = 'factual_error';
    case WEAK_EVIDENCE = 'weak_evidence';
    case TONE_MISMATCH = 'tone_mismatch';
    case REPETITION = 'repetition';
    case READABILITY = 'readability';
    case MISSING_ENTITY = 'missing_entity';
    case STRUCTURAL_GAP = 'structural_gap';

    public function label(): string
    {
        return match ($this) {
            self::FACTUAL_ERROR => 'Factual Inaccuracy',
            self::WEAK_EVIDENCE => 'Weak Evidence / Unverified Claim',
            self::TONE_MISMATCH => 'Voice / Tone Inconsistency',
            self::REPETITION => 'Repetitive Phrasing / Redundancy',
            self::READABILITY => 'Poor Readability / High Complexity',
            self::MISSING_ENTITY => 'Missing Essential Entity',
            self::STRUCTURAL_GAP => 'Structural / Transition Gap',
        };
    }

    public function defaultUnitType(): RepairUnitType
    {
        return match ($this) {
            self::FACTUAL_ERROR, self::WEAK_EVIDENCE, self::READABILITY => RepairUnitType::SENTENCE,
            self::TONE_MISMATCH, self::REPETITION => RepairUnitType::PARAGRAPH,
            self::MISSING_ENTITY, self::STRUCTURAL_GAP => RepairUnitType::SECTION,
        };
    }
}

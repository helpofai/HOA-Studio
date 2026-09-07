<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Layer Type Enum
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

enum MemoryLayerType: string
{
    case WORKING = 'working';
    case SEMANTIC = 'semantic';
    case EPISODIC = 'episodic';
    case PROCEDURAL = 'procedural';
    case STRATEGIC = 'strategic';
    case BRAND = 'brand';
    case SITE = 'site';
    case KNOWLEDGE = 'knowledge';

    public function label(): string
    {
        return match ($this) {
            self::WORKING => 'Working Memory (Short-Term Task)',
            self::SEMANTIC => 'Semantic Memory (Concepts & Entities)',
            self::EPISODIC => 'Episodic Memory (Events & Learnings)',
            self::PROCEDURAL => 'Procedural Memory (Execution How-To)',
            self::STRATEGIC => 'Strategic Memory (Heuristics & Playbooks)',
            self::BRAND => 'Brand Voice & Guidelines',
            self::SITE => 'Site Brain (Catalog & Topic Graph)',
            self::KNOWLEDGE => 'Knowledge Base & Claim Triples',
        };
    }

    public function isLongTerm(): bool
    {
        return in_array($this, [self::SEMANTIC, self::PROCEDURAL, self::STRATEGIC, self::BRAND, self::SITE, self::KNOWLEDGE]);
    }

    public function isEphemeral(): bool
    {
        return $this === self::WORKING;
    }
}

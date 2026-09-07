<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Agent Role Enum
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

enum AgentRole: string
{
    case RESEARCHER = 'researcher';
    case ANALYST = 'analyst';
    case WRITER = 'writer';
    case FACT_CHECKER = 'fact_checker';
    case CRITIC = 'critic';
    case SEO = 'seo';
    case EDITOR = 'editor';

    public function displayName(): string
    {
        return match ($this) {
            self::RESEARCHER => 'Research Investigator',
            self::ANALYST => 'Knowledge Graph Analyst',
            self::WRITER => 'Long-Form Prose Writer',
            self::FACT_CHECKER => 'Evidence & Fact Verifier',
            self::CRITIC => 'Multi-Rubric Quality Critic',
            self::SEO => 'Search Intent & SEO Strategist',
            self::EDITOR => 'Cadence & Style Polisher',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::RESEARCHER => '🔍',
            self::ANALYST => '📊',
            self::WRITER => '✍️',
            self::FACT_CHECKER => '🛡️',
            self::CRITIC => '⚖️',
            self::SEO => '🎯',
            self::EDITOR => '✨',
        };
    }
}

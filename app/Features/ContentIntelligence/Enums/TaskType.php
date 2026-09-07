<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Task Type Enum
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

enum TaskType: string
{
    case CLASSIFICATION = 'classification';
    case KEYWORD_ANALYSIS = 'keyword_analysis';
    case RESEARCH_SYNTHESIS = 'research_synthesis';
    case REASONING_ANALYSIS = 'reasoning_analysis';
    case CREATIVE_WRITING = 'creative_writing';
    case PROOFREADING_EDITING = 'proofreading_editing';
    case FACT_CHECKING = 'fact_checking';
    case SEO_OPTIMIZATION = 'seo_optimization';
    case CRITIQUE_EVALUATION = 'critique_evaluation';

    public function defaultQualityTier(): string
    {
        return match ($this) {
            self::CLASSIFICATION, self::KEYWORD_ANALYSIS, self::PROOFREADING_EDITING => 'fast',
            self::RESEARCH_SYNTHESIS, self::SEO_OPTIMIZATION => 'balanced',
            self::REASONING_ANALYSIS, self::FACT_CHECKING => 'reasoning',
            self::CREATIVE_WRITING, self::CRITIQUE_EVALUATION => 'high_accuracy',
        };
    }

    public function defaultPreferredModels(): array
    {
        return match ($this) {
            self::CLASSIFICATION, self::KEYWORD_ANALYSIS => ['gpt-4o-mini', 'claude-3-5-haiku', 'gemini-1.5-flash', 'deepseek-chat'],
            self::RESEARCH_SYNTHESIS, self::SEO_OPTIMIZATION => ['claude-3-5-haiku', 'gpt-4o-mini', 'gemini-1.5-flash'],
            self::REASONING_ANALYSIS, self::FACT_CHECKING => ['o1', 'o3-mini', 'claude-3-7-sonnet', 'deepseek-reasoner'],
            self::CREATIVE_WRITING => ['claude-3-5-sonnet', 'gpt-4o', 'claude-3-7-sonnet'],
            self::CRITIQUE_EVALUATION => ['claude-3-5-sonnet', 'gpt-4o', 'o3-mini'],
            self::PROOFREADING_EDITING => ['gpt-4o-mini', 'claude-3-5-haiku'],
        };
    }
}

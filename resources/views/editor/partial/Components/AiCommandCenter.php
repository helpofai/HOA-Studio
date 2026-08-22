<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace Resources\Views\Editor\Partial\Components;

class AiCommandCenter
{
    public static function get15PipelineStages(): array
    {
        return [
            'search_intent' => ['icon' => '🔍', 'label' => 'Search Intent Analysis', 'category' => 'Analysis', 'enabled' => true],
            'keyword_research' => ['icon' => '🏷️', 'label' => 'Keyword & Entity Research', 'category' => 'Research', 'enabled' => true],
            'serp_competitor' => ['icon' => '🌐', 'label' => 'SERP / Competitor Analysis', 'category' => 'Intelligence', 'enabled' => true],
            'content_gaps' => ['icon' => '🎯', 'label' => 'Content Gap Analysis', 'category' => 'Strategy', 'enabled' => true],
            'article_outline' => ['icon' => '📑', 'label' => 'Article Outline Architecture', 'category' => 'Structure', 'enabled' => true],
            'section_generation' => ['icon' => '✍️', 'label' => 'Section-by-Section Generation', 'category' => 'Drafting', 'enabled' => true],
            'fact_verification' => ['icon' => '🛡️', 'label' => 'Fact & Source Verification', 'category' => 'Accuracy', 'enabled' => true],
            'originality_check' => ['icon' => '✨', 'label' => 'Originality & Novelty Check', 'category' => 'Uniqueness', 'enabled' => true],
            'seo_optimization' => ['icon' => '⌁', 'label' => 'SEO Deep Optimization', 'category' => 'Optimization', 'enabled' => true],
            'readability_opt' => ['icon' => '📖', 'label' => 'Readability & Flow Optimization', 'category' => 'Refinement', 'enabled' => true],
            'internal_links' => ['icon' => '🔗', 'label' => 'Internal Link Suggestions', 'category' => 'Linking', 'enabled' => true],
            'media_suggestions' => ['icon' => '🖼️', 'label' => 'Media & Asset Suggestions', 'category' => 'Assets', 'enabled' => true],
            'schema_generation' => ['icon' => '📋', 'label' => 'Schema JSON-LD Generation', 'category' => 'Schema', 'enabled' => true],
            'quality_audit' => ['icon' => '🏆', 'label' => 'Final 10-Point Quality Audit', 'category' => 'Audit', 'enabled' => true],
            'publish_assembly' => ['icon' => '🚀', 'label' => 'Publish-Ready Assembly', 'category' => 'Publish', 'enabled' => true],
        ];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - KnowledgeConstrainedWriterService Test
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

namespace Tests\Feature;

use App\Features\AI\Data\ContentState;
use App\Features\AI\Services\KnowledgeConstrainedWriterService;

use Tests\TestCase;

class KnowledgeConstrainedWriterServiceTest extends TestCase
{
    public function test_writer_service_builds_blueprint_and_drafts_constrained_article(): void
    {
        $service = app(KnowledgeConstrainedWriterService::class);
        $state = new ContentState('PHP 8.4 Features');

        $state->knowledgeGraph->addSource('src_01', 'https://php.net', 0.99, 'Tier 1');
        $state->knowledgeGraph->addEvidence('ev_01', 'src_01', 'PHP 8.4 adds Property Hooks.', 0.99);
        $state->knowledgeGraph->addApprovedClaim('claim_01', 'ev_01', 'PHP 8.4 introduces Property Hooks for concise getters and setters.');

        $state = $service->generateBlueprint($state);
        $this->assertEquals(18, $state->stage);
        $this->assertCount(3, $state->blueprint->sectionContracts);

        $state = $service->writeArticleSections($state);
        $this->assertEquals(21, $state->stage);
        $this->assertNotEmpty($state->structuredArticle->sections);

        $html = $state->structuredArticle->toHtml();
        $this->assertStringContainsString('The Definitive Guide to PHP 8.4 Features', $html);
        $this->assertStringContainsString('<h2>Understanding PHP 8.4 Features: Core Principles</h2>', $html);
    }
}

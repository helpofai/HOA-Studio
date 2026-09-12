<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - ContentState Engine Unit Test
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

use App\Features\AI\Data\ArticleMission;
use App\Features\AI\Data\AudienceProfile;
use App\Features\AI\Data\ContentBlueprint;
use App\Features\AI\Data\ContentState;
use App\Features\AI\Data\IntentContract;
use App\Features\AI\Data\KeywordUniverse;
use App\Features\AI\Data\KnowledgeGraph;
use App\Features\AI\Data\StructuredArticle;
use Tests\TestCase;

class ContentStateEngineTest extends TestCase
{
    public function test_content_state_initializes_with_defaults(): void
    {
        $state = new ContentState('Laravel 13 Architecture');

        $this->assertEquals(1, $state->stage);
        $this->assertEquals('pending', $state->status);
        $this->assertEquals('Laravel 13 Architecture', $state->mission->topic);
        $this->assertEquals('Laravel 13 Architecture', $state->keywordUniverse->primaryKeyword);
    }

    public function test_knowledge_graph_lineage_and_claims(): void
    {
        $graph = new KnowledgeGraph;
        $graph->addSource('src_01', 'https://laravel.com/docs', 0.98, 'Tier 1');
        $graph->addEvidence('ev_101', 'src_01', 'Laravel 13 requires PHP 8.3+', 0.95);
        $graph->addApprovedClaim('claim_201', 'ev_101', 'Minimum supported PHP version is PHP 8.3');

        $this->assertCount(1, $graph->sources);
        $this->assertCount(1, $graph->evidence);
        $this->assertCount(1, $graph->claims);

        $approved = $graph->getApprovedClaimsForSection(['ev_101']);
        $this->assertCount(1, $approved);
        $this->assertEquals('Minimum supported PHP version is PHP 8.3', $approved[0]['statement']);
    }

    public function test_structured_article_renders_valid_html(): void
    {
        $article = new StructuredArticle(
            metadata: ['title' => 'Guide to Laravel 13'],
            introduction: [
                'quick_answer' => 'Laravel 13 introduces real-time state management.',
                'body' => '<p>Welcome to this guide.</p>',
            ],
            sections: [
                [
                    'heading' => 'What is New in Laravel 13?',
                    'level' => 2,
                    'content' => '<p>New features include high performance routing.</p>',
                ],
            ],
            faq: [
                [
                    'question' => 'Is PHP 8.3 required?',
                    'answer' => 'Yes, PHP 8.3 or higher is required.',
                ],
            ]
        );

        $html = $article->toHtml();

        $this->assertStringContainsString('<h1>Guide to Laravel 13</h1>', $html);
        $this->assertStringContainsString('💡 Quick Summary / Key Takeaway', $html);
        $this->assertStringContainsString('<h2>What is New in Laravel 13?</h2>', $html);
        $this->assertStringContainsString('<h3>Is PHP 8.3 required?</h3>', $html);
    }

    public function test_content_state_serialization_roundtrip(): void
    {
        $state = new ContentState('AI Copywriting Engine');
        $state->mission->articleType = 'ultimate_guide';
        $state->advanceStage(2, ['status' => 'mission_approved']);

        $array = $state->toArray();
        $reconstructed = ContentState::fromArray($array);

        $this->assertEquals(2, $reconstructed->stage);
        $this->assertEquals('in_progress', $reconstructed->status);
        $this->assertEquals('ultimate_guide', $reconstructed->mission->articleType);
        $this->assertCount(1, $reconstructed->stageOutputs);
    }
}

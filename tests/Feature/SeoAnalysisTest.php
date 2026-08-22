<?php

namespace Tests\Feature;

use App\Features\AI\Models\AiProvider;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use App\Features\SEO\Actions\AnalyzeDocumentSeo;
use App\Features\SEO\Services\SeoAnalyzer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
        ]);

        $this->document = Document::create([
            'user_id' => $this->user->id,
            'title' => 'The Complete Guide to AI Agents in 2026',
            'slug' => 'the-complete-guide-to-ai-agents-in-2026',
            'status' => 'draft',
            'word_count' => 800,
            'character_count' => 4500,
            'reading_time_minutes' => 4,
        ]);

        DocumentContent::create([
            'document_id' => $this->document->id,
            'content_html' => '<h1>The Complete Guide to AI Agents in 2026</h1><p>AI agents are transforming how modern software systems coordinate complex tasks across distributed networks.</p><h2>Understanding Autonomous AI Agents</h2><p>Here is an in-depth breakdown of how autonomous multi-agent systems operate efficiently with low latency.</p><h2>Key Architectural Benefits</h2><p>Multi-model routing allows failover resilience and cost optimization across hundreds of LLMs.</p>',
            'content_plain' => 'The Complete Guide to AI Agents in 2026. AI agents are transforming how modern software systems coordinate complex tasks across distributed networks. Understanding Autonomous AI Agents. Here is an in-depth breakdown of how autonomous multi-agent systems operate efficiently with low latency. Key Architectural Benefits. Multi-model routing allows failover resilience and cost optimization across hundreds of LLMs.',
        ]);

        AiProvider::firstOrCreate(
            ['slug' => 'omniroute'],
            [
                'name' => 'OmniRoute Gateway',
                'slug' => 'omniroute',
                'base_url' => 'http://localhost:20128/v1',
                'api_key_encrypted' => 'test-key',
                'is_local' => true,
                'is_active' => true,
            ]
        );
    }

    public function test_seo_analyzer_evaluates_document_metrics_and_scores(): void
    {
        $analyzer = new SeoAnalyzer();
        $html = $this->document->content->content_html;
        $title = $this->document->title;

        $results = $analyzer->analyze($html, $title, 'ai agents', ['latency', 'routing']);

        $this->assertGreaterThan(0, $results['score']);
        $this->assertGreaterThan(0, $results['readability_score']);
        $this->assertTrue($results['metrics']['keyword']['in_title']);
        $this->assertTrue($results['metrics']['keyword']['in_first_100_words']);
        $this->assertTrue($results['metrics']['keyword']['in_h2']);
        $this->assertNotEmpty($results['recommendations']);
    }

    public function test_analyze_document_seo_action_persists_to_database(): void
    {
        $action = app(AnalyzeDocumentSeo::class);
        $analysis = $action->execute($this->document, 'ai agents', ['routing']);

        $this->assertDatabaseHas('seo_analyses', [
            'document_id' => $this->document->id,
            'target_keyword' => 'ai agents',
        ]);

        $this->assertEquals($this->document->id, $analysis->document_id);
        $this->assertIsArray($analysis->metrics);
        $this->assertIsArray($analysis->recommendations);
    }

    public function test_document_editor_can_run_seo_audit_and_generate_titles(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => '["AI Agents Guide: Master Autonomous LLM Workflows in 2026", "The Ultimate AI Agents Blueprint for Modern Developers", "How to Build AI Agents with OmniRoute in 2026"]',
                        ],
                    ],
                ],
                'usage' => ['total_tokens' => 50],
                'model' => 'auto',
            ], 200),
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\Documents\Livewire\DocumentEditor::class, ['id' => $this->document->id])
            ->set('targetKeyword', 'AI Agents')
            ->call('runSeoAudit')
            ->assertHasNoErrors()
            ->call('generateSeoTitles')
            ->assertSet('aiSeoType', 'titles')
            ->assertCount('aiSeoResults', 3)
            ->call('applyTitle', 'AI Agents Guide: Master Autonomous LLM Workflows in 2026');

        $this->assertDatabaseHas('documents', [
            'id' => $this->document->id,
            'title' => 'AI Agents Guide: Master Autonomous LLM Workflows in 2026',
        ]);
    }
}
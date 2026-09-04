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
        $this->assertArrayHasKey('geo_ai_search', $results['rank_math']);
    }

    public function test_seo_analyzer_evaluates_geo_readiness_and_ai_overviews(): void
    {
        $analyzer = new SeoAnalyzer();
        $html = '<h1>Guide to Autonomous AI Agents</h1>' .
                '<h2>What is an Autonomous AI Agent?</h2>' .
                '<p>An autonomous AI agent is an advanced software system powered by large language models that independently plans, reasons, and executes multi-step workflows to accomplish complex objectives with minimal human intervention in real-time production environments.</p>' .
                '<table class="hoa-comparison-table"><tr><th>Agent</th><th>Speed</th></tr><tr><td>OmniRoute</td><td>25ms</td></tr></table>' .
                '<p>According to research from Stanford University, multi-agent frameworks improved efficiency by 48% across 1,200 benchmarks in 2026.</p>';

        $results = $analyzer->analyze($html, 'Guide to Autonomous AI Agents in 2026', 'ai agent');

        $this->assertArrayHasKey('geo_readiness', $results);
        $this->assertTrue($results['geo_readiness']['has_direct_answer']);
        $this->assertTrue($results['geo_readiness']['has_table']);
        $this->assertGreaterThanOrEqual(2, $results['geo_readiness']['data_points']);
        $this->assertArrayHasKey('geo_ai_search', $results['rank_math']);
        $this->assertNotEmpty($results['rank_math']['geo_ai_search']['checks']);
    }

    public function test_schema_generator_detects_faq_howto_and_article_jsonld(): void
    {
        $generator = app(\App\Features\SEO\Services\SchemaGenerator::class);
        $html = '<h1>How to Deploy an AI Agent</h1>' .
                '<p>Deploying an autonomous agent requires distributed architecture and model routing gateways.</p>' .
                '<h2>Step 1: Install OmniRoute Gateway</h2>' .
                '<p>Download the binary or launch the docker daemon container on port 20128.</p>' .
                '<h2>Step 2: Configure Model Fallbacks</h2>' .
                '<p>Specify primary and backup providers in the multi-provider routing matrix.</p>' .
                '<h3>What is the recommended server memory?</h3>' .
                '<p>We recommend at least 8GB of RAM for local model execution and caching.</p>' .
                '<h3>Can I use cloud providers?</h3>' .
                '<p>Yes, OpenAI, Anthropic, and DeepSeek endpoints integrate seamlessly.</p>';

        $results = $generator->generate($html, 'How to Deploy an AI Agent', 'A comprehensive step-by-step deployment guide.');

        $this->assertTrue($results['validation']['is_valid']);
        $this->assertArrayHasKey('article', $results['schemas']);
        $this->assertArrayHasKey('faq', $results['schemas']);
        $this->assertArrayHasKey('howto', $results['schemas']);
        $this->assertGreaterThanOrEqual(2, $results['faq_count']);
        $this->assertGreaterThanOrEqual(2, $results['howto_step_count']);
        $this->assertStringContainsString('application/ld+json', $results['script_tag']);
        $this->assertStringContainsString('FAQPage', $results['json_ld']);
        $this->assertStringContainsString('HowTo', $results['json_ld']);
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

    public function test_document_editor_mounts_with_complete_rank_math_checklist_and_controls(): void
    {
        $component = \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\Documents\Livewire\DocumentEditor::class, ['id' => $this->document->id]);

        $seoData = $component->get('seoData');
        $this->assertIsArray($seoData);
        $this->assertArrayHasKey('rank_math', $seoData);
        $this->assertArrayHasKey('basic_seo', $seoData['rank_math']);
        $this->assertArrayHasKey('additional_seo', $seoData['rank_math']);
        $this->assertArrayHasKey('title_readability', $seoData['rank_math']);

        // Verify key checks required by user are all present in rank_math pillars
        $checkTitles = [];
        foreach ($seoData['rank_math'] as $pillar) {
            foreach ($pillar['checks'] as $check) {
                $checkTitles[] = $check['title'];
            }
        }

        $this->assertContains('Focus Keyword in SEO Title', $checkTitles);
        $this->assertContains('Focus Keyword in Meta Description', $checkTitles);
        $this->assertContains('Focus Keyword in URL Slug', $checkTitles);
        $this->assertContains('Focus Keyword in First 10% (Intro)', $checkTitles);
        $this->assertContains('Focus Keyword in Content Body', $checkTitles);
        $this->assertContains('Content Length Check (600+ words)', $checkTitles);
        $this->assertContains('Focus Keyword in Subheadings (H2, H3)', $checkTitles);
        $this->assertContains('Focus Keyword in Image Alt Attributes', $checkTitles);
        $this->assertContains('Keyword Density (0.8% - 2.5%)', $checkTitles);
        $this->assertContains('External Outbound Citations', $checkTitles);
        $this->assertContains('Internal Cluster Links', $checkTitles);
        $this->assertContains('Focus Keyword at Start of Title', $checkTitles);
        $this->assertContains('Title Contains a Number', $checkTitles);
        $this->assertContains('Title Contains a Power Word', $checkTitles);

        // Verify HTML renders the checklist header and checks
        $component->assertSee('SEO Optimization Checklist')
            ->assertSee('Focus Keyword in SEO Title')
            ->assertSee('Focus Keyword in Meta Description')
            ->assertSee('Focus Keyword in First 10% (Intro)');
    }

    public function test_document_editor_mounts_with_complete_10_point_eeat_quality_audit(): void
    {
        $component = \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\Documents\Livewire\DocumentEditor::class, ['id' => $this->document->id]);

        $qa = $component->get('aiQualityAudit');
        $this->assertIsArray($qa);
        $this->assertArrayHasKey('overall', $qa);
        $this->assertArrayHasKey('grade', $qa);
        $this->assertArrayHasKey('passed_count', $qa);
        $this->assertArrayHasKey('factors', $qa);
        $this->assertCount(10, $qa['factors']);

        // Check each of the 10 dimensions exists with score and status
        $expectedFactors = [
            'search_intent',
            'topic_coverage',
            'original_value',
            'readability',
            'seo_structure',
            'internal_linking',
            'outbound_citations',
            'eeat_signals',
            'geo_readiness',
            'technical_seo',
        ];

        foreach ($expectedFactors as $factorKey) {
            $this->assertArrayHasKey($factorKey, $qa['factors']);
            $this->assertArrayHasKey('score', $qa['factors'][$factorKey]);
            $this->assertArrayHasKey('status', $qa['factors'][$factorKey]);
            $this->assertArrayHasKey('title', $qa['factors'][$factorKey]);
            $this->assertGreaterThanOrEqual(0, $qa['factors'][$factorKey]['score']);
            $this->assertLessThanOrEqual(100, $qa['factors'][$factorKey]['score']);
        }

        // Verify HTML rendering of the Quality Audit tab
        $component->assertSee('10-Point E-E-A-T Quality Audit')
            ->assertSee('Search Intent Satisfaction')
            ->assertSee('Topical Depth & Comprehensiveness')
            ->assertSee('Information Gain & Data Points')
            ->assertSee('Readability & Scannability')
            ->assertSee('Heading Hierarchy & Structure')
            ->assertSee('Internal Topic Cluster Links')
            ->assertSee('Authoritative Outbound Citations')
            ->assertSee('First-Hand Experience & Trust (E-E-A-T)')
            ->assertSee('Google AI Overviews & GEO Readiness')
            ->assertSee('Technical Schema.org & Meta Markup')
            ->assertSee('1-Click E-E-A-T Holistic Auto-Heal');

        // Test running re-audit via Livewire action
        $component->call('generateQualityAudit');
        $recalculated = $component->get('aiQualityAudit');
        $this->assertIsArray($recalculated);
        $this->assertCount(10, $recalculated['factors']);
    }

    public function test_content_intelligence_features_work_flawlessly_with_local_algorithms_when_ai_is_unavailable(): void
    {
        // 1. Simulate user having 0 word quota (AI unavailable)
        $this->user->update([
            'used_word_quota' => 100000,
            'monthly_word_quota' => 100000,
        ]);
        $this->assertFalse($this->user->hasQuota(1));

        /** @var \App\Features\SEO\Actions\GenerateSeoMetadata $generator */
        $generator = app(\App\Features\SEO\Actions\GenerateSeoMetadata::class);
        $docHtml = '<h2>DeepSeek AI Architecture</h2><p>DeepSeek models provide high efficiency and performance for enterprise tasks.</p>';

        // 2. Titles generation without AI (Local Algorithm)
        $titles = $generator->generateTitles($this->user, $docHtml, 'DeepSeek V4');
        $this->assertIsArray($titles);
        $this->assertCount(3, $titles);
        foreach ($titles as $t) {
            $this->assertStringContainsStringIgnoringCase('DeepSeek V4', $t);
            $this->assertLessThanOrEqual(65, mb_strlen($t));
        }

        // 3. Meta Descriptions generation without AI (Local Algorithm)
        $metas = $generator->generateMetaDescriptions($this->user, $docHtml, 'DeepSeek V4');
        $this->assertIsArray($metas);
        $this->assertCount(3, $metas);
        foreach ($metas as $m) {
            $this->assertStringContainsStringIgnoringCase('DeepSeek V4', $m);
            $this->assertLessThanOrEqual(160, mb_strlen($m));
        }

        // 4. LSI Keywords extraction without AI (Local NLP Algorithm)
        $keywords = $generator->suggestKeywords($this->user, $docHtml, 'DeepSeek V4');
        $this->assertIsArray($keywords);
        $this->assertCount(8, $keywords);

        // 5. FAQ generation without AI (Local Algorithm)
        $faqs = $generator->generateFaqs($this->user, $docHtml, 'DeepSeek V4');
        $this->assertIsArray($faqs);
        $this->assertCount(4, $faqs);
        $this->assertArrayHasKey('question', $faqs[0]);
        $this->assertArrayHasKey('answer', $faqs[0]);

        // 6. Content Gaps without AI (Local Structural Taxonomy)
        $gaps = $generator->generateContentGaps($this->user, $docHtml, 'DeepSeek V4');
        $this->assertIsArray($gaps);
        $this->assertNotEmpty($gaps);
        $this->assertArrayHasKey('suggested_h2', $gaps[0]);

        // 7. Quick Answer / GEO without AI (Local Direct-Answer Synthesizer)
        $quickAnswer = $generator->generateQuickAnswer($this->user, $docHtml, 'DeepSeek V4');
        $this->assertIsString($quickAnswer);
        $this->assertStringContainsString('Quick Answer', $quickAnswer);
        $this->assertStringContainsStringIgnoringCase('DeepSeek V4', $quickAnswer);

        // 8. Livewire integration: generating titles without AI seamlessly populates editor state
        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\Documents\Livewire\DocumentEditor::class, ['id' => $this->document->id])
            ->set('targetKeyword', 'DeepSeek V4')
            ->call('generateSeoTitles')
            ->assertSet('seoErrorMessage', '')
            ->assertCount('aiTitles', 3)
            ->assertCount('aiSeoResults', 3);
    }
}
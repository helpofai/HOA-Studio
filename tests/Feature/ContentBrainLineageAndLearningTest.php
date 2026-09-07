<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 6 Test Suite
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

use App\Features\ContentIntelligence\Actions\CreateContentMission;
use App\Features\ContentIntelligence\DTOs\DownstreamImpactDTO;
use App\Features\ContentIntelligence\DTOs\LineageTraceDTO;
use App\Features\ContentIntelligence\DTOs\SitePortfolioReportDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\StrategyCategory;
use App\Features\ContentIntelligence\Enums\StrategyStatus;
use App\Features\ContentIntelligence\Livewire\ContentIntelligencePage;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\ContentLineageNode;
use App\Features\ContentIntelligence\Models\EvidenceSnippet;
use App\Features\ContentIntelligence\Models\QualityHealthAudit;
use App\Features\ContentIntelligence\Models\SourceIntelligence;
use App\Features\ContentIntelligence\Models\StrategyMemory;
use App\Features\ContentIntelligence\Models\UserStylePreference;
use App\Features\ContentIntelligence\Services\AutonomousLearningEngineService;
use App\Features\ContentIntelligence\Services\ContentLineageService;
use App\Features\ContentIntelligence\Services\SiteTopicStrategyService;
use App\Features\ContentIntelligence\Services\UserFeedbackIntelligenceService;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBrainLineageAndLearningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'author_lead_'.uniqid().'@helpofai.com',
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'principal_scientist_'.uniqid().'@helpofai.com',
            'role' => 'admin',
        ]);
    }

    public function test_content_lineage_records_and_traces_sentence_chain(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Redis Caching Pipelines',
            'primary_objective' => 'Explain Redis serialization bottlenecks',
        ]);
        $run = $created['run'];

        $sourceUrl = 'https://redis.io/docs/data-types/strings';
        $source = SourceIntelligence::create([
            'url_hash' => hash('sha256', $sourceUrl),
            'url' => $sourceUrl,
            'title' => 'Official Redis Documentation',
            'reliability_score' => 95,
            'domain_authority' => 95,
        ]);

        $evidence = EvidenceSnippet::create([
            'id' => 'ev_redis_strings',
            'source_id' => $source->id,
            'mission_id' => $created['mission']->id,
            'extract_text' => 'Redis serialization overhead scales linearly with payload complexity.',
            'verbatim_quote' => 'Redis serialization overhead scales linearly with payload complexity.',
            'confidence_score' => 0.96,
            'epistemic_state' => EpistemicState::VERIFIED,
        ]);

        $claim = ClaimNode::create([
            'mission_id' => $created['mission']->id,
            'source_id' => $source->id,
            'statement' => 'Complex payload serialization degrades Redis throughput.',
            'epistemic_state' => EpistemicState::VERIFIED,
            'confidence_score' => 0.95,
        ]);

        $lineageService = app(ContentLineageService::class);

        $node = $lineageService->recordLineage($this->user->id, [
            'workflow_run_id' => $run->id,
            'source_id' => $source->id,
            'claim_id' => $claim->id,
            'section_index' => 1,
            'paragraph_index' => 2,
            'sentence_index' => 0,
            'sentence_text' => 'When payloads grow in complexity, serialization overhead degrades Redis throughput considerably.',
        ]);

        $this->assertInstanceOf(ContentLineageNode::class, $node);
        $this->assertDatabaseHas('content_lineage_nodes', [
            'id' => $node->id,
            'user_id' => $this->user->id,
            'claim_id' => $claim->id,
        ]);

        $trace = $lineageService->traceSentenceLineage($node->id);

        $this->assertInstanceOf(LineageTraceDTO::class, $trace);
        $this->assertEquals($node->sentence_text, $trace->sentenceText);
        $this->assertEquals($claim->statement, $trace->claimText);
        $this->assertEquals($source->title, $trace->sourceTitle);
        $this->assertFalse($trace->isStale);
    }

    public function test_lineage_finds_downstream_impact_and_invalidates_source_facts(): void
    {
        $benchUrl = 'https://benchmark.test/v1';
        $source = SourceIntelligence::create([
            'url_hash' => hash('sha256', $benchUrl),
            'url' => $benchUrl,
            'title' => 'Framework Benchmark v1',
            'reliability_score' => 90,
            'domain_authority' => 85,
        ]);

        $lineageService = app(ContentLineageService::class);

        $node1 = $lineageService->recordLineage($this->user->id, [
            'source_id' => $source->id,
            'sentence_text' => 'Framework v1 achieves 40,000 requests per second under peak load.',
        ]);

        $node2 = $lineageService->recordLineage($this->user->id, [
            'source_id' => $source->id,
            'sentence_text' => 'Due to these 40k req/sec benchmarks, microservices scale linearly.',
        ]);

        $impactBefore = $lineageService->findDownstreamImpactBySource($source->id);
        $this->assertInstanceOf(DownstreamImpactDTO::class, $impactBefore);
        $this->assertEquals(2, $impactBefore->affectedSentencesCount);

        // Invalidate source facts
        $invalidationReason = 'Framework v2 release altered benchmark methodology; v1 metrics obsolete.';
        $impactAfter = $lineageService->invalidateSourceFact($source->id, $invalidationReason);

        $this->assertEquals(2, $impactAfter->affectedSentencesCount);
        $this->assertDatabaseHas('content_lineage_nodes', [
            'id' => $node1->id,
            'is_stale' => true,
            'invalidation_reason' => $invalidationReason,
        ]);

        // Resolve stale node with fresh data
        $freshText = 'Framework v2 benchmarks demonstrate 65,000 requests per second with JIT enabled.';
        $resolved = $lineageService->resolveStaleNode($node1->id, $freshText);

        $this->assertFalse($resolved->is_stale);
        $this->assertNull($resolved->invalidation_reason);
        $this->assertEquals($freshText, $resolved->sentence_text);
    }

    public function test_autonomous_learning_engine_advances_strategy_lifecycle(): void
    {
        $engine = app(AutonomousLearningEngineService::class);

        // 1. Initial Observation
        $strategy = $engine->recordObservation(
            $this->user->id,
            'prefer_faq_tables',
            StrategyCategory::STRUCTURE,
            ['rule' => 'Include comprehensive HTML comparison tables in technical comparisons.'],
            0.40
        );

        $this->assertInstanceOf(StrategyMemory::class, $strategy);
        $this->assertEquals(StrategyStatus::OBSERVATION, $strategy->status);
        $this->assertEquals(1, $strategy->evidence_count);

        // 2. Increment to Candidate (count = 2)
        $engine->incrementEvidence($this->user->id, 'prefer_faq_tables');
        $strategy->refresh();
        $this->assertEquals(StrategyStatus::CANDIDATE, $strategy->status);

        // 3. Increment to Validated (count = 4)
        $engine->incrementEvidence($this->user->id, 'prefer_faq_tables');
        $engine->incrementEvidence($this->user->id, 'prefer_faq_tables');
        $strategy->refresh();
        $this->assertEquals(StrategyStatus::VALIDATED, $strategy->status);

        // 4. Increment to Adopted (count = 6)
        $engine->incrementEvidence($this->user->id, 'prefer_faq_tables');
        $engine->incrementEvidence($this->user->id, 'prefer_faq_tables');
        $strategy->refresh();
        $this->assertEquals(StrategyStatus::ADOPTED, $strategy->status);
        $this->assertNotNull($strategy->adopted_at);

        // 5. Query active adopted strategies
        $adopted = $engine->getActiveAdoptedStrategies($this->user->id, StrategyCategory::STRUCTURE);
        $this->assertTrue($adopted->contains('id', $strategy->id));

        // 6. Manual rejection
        $rejected = $engine->rejectStrategy($strategy->id, 'Deprecated in favor of interactive tabs.');
        $this->assertEquals(StrategyStatus::REJECTED, $rejected->status);
    }

    public function test_learning_engine_harvests_lessons_from_completed_workflow_run(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Production SQS Queues',
            'primary_objective' => 'Optimize Amazon SQS batch polling',
            'target_audience' => ['persona' => 'Cloud Architects'],
        ]);
        $run = $created['run'];

        QualityHealthAudit::create([
            'workflow_run_id' => $run->id,
            'overall_score' => 92,
            'grade' => 'A',
            'dimensions' => [
                'evidence_density' => 88,
                'readability' => 90,
            ],
            'key_strengths' => ['Strong evidence density'],
            'critical_gaps' => [],
            'recommendations' => [],
        ]);

        $engine = app(AutonomousLearningEngineService::class);
        $harvested = $engine->harvestWorkflowRunLessons($run);

        $this->assertNotEmpty($harvested);
        $this->assertDatabaseHas('strategy_memories', [
            'user_id' => $this->user->id,
            'strategy_key' => 'high_depth_outline_strategy',
        ]);
    }

    public function test_user_feedback_intelligence_learns_style_preferences_from_diffs(): void
    {
        $feedbackService = app(UserFeedbackIntelligenceService::class);

        // 1. Sentence brevity learning (reduced words by >= 25%)
        $original = 'In order to properly configure the server environment, it is absolutely essential and crucial for the engineering team to ensure that all environment variables are correctly populated and thoroughly verified before starting the daemon process.';
        $edited = 'Ensure all environment variables are populated and verified before starting the daemon.';

        $pref = $feedbackService->analyzeDiffAndRecordPreference($this->user->id, $original, $edited);

        $this->assertInstanceOf(UserStylePreference::class, $pref);
        $this->assertEquals('prefer_concise_sentences', $pref->preference_key);
        $this->assertEquals(1, $pref->observed_diff_count);
        $this->assertTrue($pref->is_active);

        // 2. Second observation increments count and confidence
        $pref2 = $feedbackService->analyzeDiffAndRecordPreference($this->user->id, $original, $edited);
        $this->assertEquals(2, $pref2->observed_diff_count);
        $this->assertGreaterThan(0.45, $pref2->confidence);

        // 3. Fluff removal learning
        $fluffOriginal = 'In today\'s fast-paced world, developers must continually delve into asynchronous tasks.';
        $fluffEdited = 'Developers must execute tasks asynchronously.';

        $fluffPref = $feedbackService->analyzeDiffAndRecordPreference($this->user->id, $fluffOriginal, $fluffEdited);
        $this->assertEquals('eliminate_fluff_phrases', $fluffPref->preference_key);

        // 4. Toggle preference
        $toggled = $feedbackService->togglePreference($pref->id, false);
        $this->assertFalse($toggled->is_active);
    }

    public function test_site_topic_strategy_clusters_portfolio_and_detects_cannibalization(): void
    {
        Document::create([
            'user_id' => $this->user->id,
            'title' => 'Mastering Laravel Queue Workers',
            'slug' => 'mastering-laravel-queue-workers',
            'content' => 'Deep dive into queue worker configurations.',
        ]);

        Document::create([
            'user_id' => $this->user->id,
            'title' => 'Mastering Laravel Queues for Production',
            'slug' => 'mastering-laravel-queues-for-production',
            'content' => 'Production guide for managing queues.',
        ]);

        Document::create([
            'user_id' => $this->user->id,
            'title' => 'Redis High Availability Architectures',
            'slug' => 'redis-high-availability-architectures',
            'content' => 'Redis Sentinel and clustering setups.',
        ]);

        $strategyService = app(SiteTopicStrategyService::class);
        $clusters = $strategyService->analyzeTopicClusters($this->user->id);

        $this->assertNotEmpty($clusters);
        $this->assertDatabaseHas('site_topic_clusters', [
            'user_id' => $this->user->id,
        ]);

        $report = $strategyService->generatePortfolioReport($this->user->id);
        $this->assertInstanceOf(SitePortfolioReportDTO::class, $report);
        $this->assertGreaterThanOrEqual(1, $report->totalClusters);
        $this->assertGreaterThanOrEqual(1, $report->cannibalizationAlertsCount);
        $this->assertNotEmpty($report->strategicRecommendations);
    }

    public function test_content_intelligence_page_renders_tab_11_for_admin_and_regular_user(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Microservices Architecture',
            'primary_objective' => 'Explain microservices patterns in Laravel',
        ]);
        $run = $created['run'];

        // 1. Regular User Access
        Livewire::actingAs($this->user)
            ->test(ContentIntelligencePage::class)
            ->set('selectedRunId', $run->id)
            ->call('setInspectorTab', 'lineage_learning')
            ->assertStatus(200)
            ->assertSee('7-Tier Content Lineage Graph')
            ->assertSee('Autonomous Learning Engine')
            ->assertSee('Site-Level Topic Strategy');

        $createdAdmin = app(CreateContentMission::class)->execute($this->admin, [
            'topic' => 'Enterprise System Design',
            'primary_objective' => 'Enterprise scalability guidelines',
        ]);
        $adminRun = $createdAdmin['run'];

        // 2. Admin User Access with Interactive Actions
        Livewire::actingAs($this->admin)
            ->test(ContentIntelligencePage::class)
            ->set('selectedRunId', $adminRun->id)
            ->call('setInspectorTab', 'lineage_learning')
            ->set('newStrategyKey', 'benchmark_table_required')
            ->set('newStrategyCategory', 'sources')
            ->set('newStrategyRule', 'Always present benchmark data in standardized comparison matrices.')
            ->call('recordStrategyObservation')
            ->set('testOriginalEdit', 'In order to properly configure the server environment, it is absolutely essential and crucial for the engineering team to ensure that all environment variables are correctly populated.')
            ->set('testManualEdit', 'Ensure all environment variables are populated and verified before starting the daemon.')
            ->call('analyzeUserEditDiff')
            ->assertSee('Learned preference')
            ->call('refreshTopicPortfolio')
            ->assertSee('Site Topic Portfolio')
            ->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_content_intelligence_hub(): void
    {
        $response = $this->get(route('content-intelligence.index'));

        $response->assertRedirect(route('login'));
    }
}

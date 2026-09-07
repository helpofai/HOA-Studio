<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 5 Test Suite
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
use App\Features\ContentIntelligence\DTOs\QualityDimensionScoreDTO;
use App\Features\ContentIntelligence\Enums\ProblemCategory;
use App\Features\ContentIntelligence\Enums\RepairStatus;
use App\Features\ContentIntelligence\Enums\RepairUnitType;
use App\Features\ContentIntelligence\Enums\RiskLevel;
use App\Features\ContentIntelligence\Livewire\ContentIntelligencePage;
use App\Features\ContentIntelligence\Models\ContentGenome;
use App\Features\ContentIntelligence\Models\MicroRepair;
use App\Features\ContentIntelligence\Models\QualityHealthAudit;
use App\Features\ContentIntelligence\Models\RiskAssessment;
use App\Features\ContentIntelligence\Services\ContentGenomeService;
use App\Features\ContentIntelligence\Services\ContentRiskEngineService;
use App\Features\ContentIntelligence\Services\MicroRepairService;
use App\Features\ContentIntelligence\Services\QualityEngineService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBrainSurgicalRepairAndQualityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'editor_lead_'.uniqid().'@helpofai.com',
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'chief_architect_'.uniqid().'@helpofai.com',
            'role' => 'admin',
        ]);
    }

    public function test_micro_repair_detects_localized_problems_accurately(): void
    {
        $repairService = app(MicroRepairService::class);

        $text = 'In today\'s fast-paced world, it is crucial to remember that this new cloud caching framework is guaranteed 100% to outperform traditional memory architectures without any doubt whatsoever. Furthermore, developers across the enterprise domain must continually evaluate and benchmark their distributed systems in order to properly measure throughput, latency, serialization cost, network overhead, and database lock contention under extreme peak traffic.';

        $problems = $repairService->detectProblems($text);

        $this->assertNotEmpty($problems);

        $categories = array_column($problems, 'category');
        $this->assertTrue(
            in_array(ProblemCategory::REPETITION, $categories) ||
            in_array(ProblemCategory::WEAK_EVIDENCE, $categories) ||
            in_array(ProblemCategory::READABILITY, $categories)
        );
    }

    public function test_micro_repair_executes_smallest_unit_sentence_repair(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Distributed Microservices Resiliency',
            'primary_objective' => 'Build high-availability fault tolerance in Laravel',
        ]);
        $run = $created['run'];

        $repairService = app(MicroRepairService::class);

        $fullDoc = 'Introduction. In today\'s fast-paced world, it is crucial to remember that caching is a testament to modern engineering. Next section.';
        $targetSentence = 'In today\'s fast-paced world, it is crucial to remember that caching is a testament to modern engineering.';

        $repair = $repairService->repairSmallestUnit(
            run: $run,
            fullContent: $fullDoc,
            targetUnit: $targetSentence,
            unitType: RepairUnitType::SENTENCE,
            category: ProblemCategory::REPETITION,
            rootCause: 'Stereotypical AI clichés detected in intro sentence.',
            unitPointer: 'sec-0-s-1'
        );

        $this->assertInstanceOf(MicroRepair::class, $repair);
        $this->assertEquals(RepairUnitType::SENTENCE, $repair->unit_type);
        $this->assertEquals(ProblemCategory::REPETITION, $repair->problem_category);
        $this->assertEquals(RepairStatus::RESOLVED, $repair->status);
        $this->assertEquals(1, $repair->escalation_level);
        $this->assertNotEmpty($repair->diff_summary);
        $this->assertDatabaseHas('micro_repairs', [
            'id' => $repair->id,
            'workflow_run_id' => $run->id,
            'unit_type' => 'sentence',
        ]);
    }

    public function test_micro_repair_escalates_to_higher_unit_ladder(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Database Optimization',
            'primary_objective' => 'Postgres indexing strategies',
        ]);
        $run = $created['run'];

        $repairService = app(MicroRepairService::class);

        $targetSentence = 'Query times are guaranteed 100% to drop instantly.';
        $initialRepair = $repairService->repairSmallestUnit(
            run: $run,
            fullContent: $targetSentence,
            targetUnit: $targetSentence,
            unitType: RepairUnitType::SENTENCE,
            category: ProblemCategory::WEAK_EVIDENCE,
            rootCause: 'Absolute claim without citation.',
            unitPointer: 'sec-1-s-1'
        );

        $expandedParagraph = 'Query times are guaranteed 100% to drop instantly. This applies to all table schemas without exception.';
        $escalatedRepair = $repairService->escalateRepair(
            existingRepair: $initialRepair,
            expandedUnit: $expandedParagraph,
            run: $run,
            expandedRootCause: 'Sentence-level edit did not resolve broader paragraph over-generalization.'
        );

        $this->assertEquals(RepairStatus::ESCALATED, $initialRepair->fresh()->status);
        $this->assertEquals(RepairUnitType::PARAGRAPH, $escalatedRepair->unit_type);
        $this->assertEquals(2, $escalatedRepair->escalation_level);
        $this->assertEquals(RepairStatus::RESOLVED, $escalatedRepair->status);
    }

    public function test_quality_engine_evaluates_fifteen_dimensional_content_health(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Modern PHP 8.5 Architecture',
            'primary_objective' => 'Deep dive into asynchronous fibers and performance',
        ]);
        $run = $created['run'];

        $qualityService = app(QualityEngineService::class);

        $content = '<h2>PHP 8.5 Fiber Concurrency</h2><p>Modern PHP architecture is defined as a scalable ecosystem. According to official benchmarks, latency decreased by 34% in 2026. In practice, unlike typical monolithic designs, asynchronous fibers allow 10000 concurrent sockets with minimal memory. Summary and next steps for deployment.</p><ul><li>Step 1: Install runtime</li><li>Step 2: Configure event loop</li></ul>';

        $audit = $qualityService->auditContentHealth($run, ['text' => $content]);

        $this->assertInstanceOf(QualityHealthAudit::class, $audit);
        $this->assertGreaterThanOrEqual(70, $audit->overall_score);
        $this->assertContains($audit->grade, ['A+', 'A', 'B', 'C']);
        $this->assertCount(15, $audit->dimensions);
        $this->assertNotEmpty($audit->key_strengths);
        $this->assertNotEmpty($audit->recommendations);

        $this->assertDatabaseHas('quality_health_audits', [
            'workflow_run_id' => $run->id,
            'overall_score' => $audit->overall_score,
        ]);
    }

    public function test_quality_dimension_dto_calculates_ratings_and_explicit_reasons(): void
    {
        $dim1 = QualityDimensionScoreDTO::create(
            key: 'evidence_strength',
            name: 'Evidence Strength',
            score: 95.0,
            weight: 0.08,
            reasons: ['Peer-reviewed citations included.', 'Clear empirical benchmarks.']
        );

        $dim2 = QualityDimensionScoreDTO::create(
            key: 'factual_reliability',
            name: 'Factual Reliability',
            score: 55.0,
            weight: 0.08,
            reasons: ['Contradicted claims detected.', 'Ungrounded absolutes.']
        );

        $this->assertEquals('EXCELLENT', $dim1->rating);
        $this->assertEquals('CRITICAL', $dim2->rating);
        $this->assertCount(2, $dim1->reasons);
        $this->assertCount(2, $dim2->reasons);
    }

    public function test_risk_engine_detects_ymyl_and_assigns_risk_levels(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Clinical Pharmacology & Dosage Guidelines for Cancer Treatment',
            'primary_objective' => 'Explain treatment dosage and FDA approved clinical procedures',
        ]);
        $run = $created['run'];

        $riskService = app(ContentRiskEngineService::class);
        $assessment = $riskService->assessRisk($run);

        $this->assertInstanceOf(RiskAssessment::class, $assessment);
        $this->assertTrue($assessment->is_ymyl);
        $this->assertTrue($assessment->requires_primary_sources);
        $this->assertTrue($assessment->requires_human_approval);
        $this->assertFalse($assessment->is_approved_by_human);
        $this->assertFalse($assessment->isGatingPassed());
        $this->assertContains($assessment->risk_level, [RiskLevel::HIGH, RiskLevel::CRITICAL]);
    }

    public function test_risk_engine_enforces_human_approval_gate_and_clears_upon_signoff(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Investment Strategies & Guaranteed Returns in Crypto',
            'primary_objective' => 'Financial return calculation and legal liability',
        ]);
        $run = $created['run'];

        $riskService = app(ContentRiskEngineService::class);
        $assessment = $riskService->assessRisk($run);

        $this->assertFalse($assessment->isGatingPassed());

        // Grant human signoff
        $approved = $riskService->approveByHuman($run, $this->admin->id);

        $this->assertTrue($approved->is_approved_by_human);
        $this->assertEquals($this->admin->id, $approved->approved_by_user_id);
        $this->assertNotNull($approved->approved_at);
        $this->assertTrue($approved->isGatingPassed());
    }

    public function test_content_genome_synthesizes_reusable_knowledge_object(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Cloud Native Kubernetes Orchestration',
            'primary_objective' => 'Production deployment best practices and benchmarks',
        ]);
        $run = $created['run'];

        $genomeService = app(ContentGenomeService::class);
        $genome = $genomeService->synthesizeGenome($run);

        $this->assertInstanceOf(ContentGenome::class, $genome);
        $this->assertNotEmpty($genome->genome_signature);
        $this->assertNotEmpty($genome->mission_dna);
        $this->assertEquals('Cloud Native Kubernetes Orchestration', $genome->mission_dna['target_topic']);
        $this->assertNotEmpty($genome->topics_dna);
        $this->assertNotEmpty($genome->reusable_fragments);

        $this->assertDatabaseHas('content_genomes', [
            'id' => $genome->id,
            'user_id' => $this->user->id,
            'genome_signature' => $genome->genome_signature,
        ]);
    }

    public function test_content_genome_inherits_knowledge_for_subsequent_missions(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Event Driven Architecture in PHP',
            'primary_objective' => 'Kafka message queuing',
        ]);
        $run = $created['run'];

        $genomeService = app(ContentGenomeService::class);
        $genomeService->synthesizeGenome($run);

        $inherited = $genomeService->inheritKnowledge($this->user->id, 'Architecture');

        $this->assertIsArray($inherited);
        $this->assertArrayHasKey('inherited_claims', $inherited);
        $this->assertArrayHasKey('inherited_entities', $inherited);
        $this->assertArrayHasKey('inherited_sources', $inherited);
        $this->assertGreaterThanOrEqual(1, $inherited['genomes_consulted']);
    }

    public function test_livewire_content_intelligence_hub_interacts_with_phase5_features(): void
    {
        $created = app(CreateContentMission::class)->execute($this->user, [
            'topic' => 'Real-Time Telemetry with WebSockets',
            'primary_objective' => 'Build high throughput live telemetry pipelines',
        ]);
        $run = $created['run'];

        $lw = Livewire::actingAs($this->user)
            ->test(ContentIntelligencePage::class)
            ->set('selectedRunId', $run->id)
            ->set('inspectorTab', 'health_repairs')
            ->assertSet('inspectorTab', 'health_repairs')
            ->assertSee('15-Dimension Content Health')
            ->assertSee('Surgical Micro-Repair Loop')
            ->assertSee('Content Risk')
            ->assertSee('Content Genome');

        $lw->call('runQualityAudit');
        $this->assertNotEmpty($lw->get('statusMessage'));
    }

    public function test_dual_role_authorization_and_access_matrix_for_phase5(): void
    {
        // 1. Authenticated User Access
        $this->actingAs($this->user)
            ->get(route('content-intelligence.index'))
            ->assertOk()
            ->assertSee('Content Intelligence Hub');

        // 2. Admin User Access
        $this->actingAs($this->admin)
            ->get(route('content-intelligence.index'))
            ->assertOk()
            ->assertSee('Content Intelligence Hub');

        // 3. Guest Access Redirects
        auth()->logout();
        $this->get(route('content-intelligence.index'))
            ->assertRedirect(route('login'));
    }
}

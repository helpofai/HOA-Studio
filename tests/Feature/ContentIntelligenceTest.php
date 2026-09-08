<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Intelligence Test Suite
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
use App\Features\ContentIntelligence\DTOs\AdaptiveOutlineDTO;
use App\Features\ContentIntelligence\DTOs\ClaimNodeDTO;
use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\CriticScoreDTO;
use App\Features\ContentIntelligence\Enums\ArticleArchetype;
use App\Features\ContentIntelligence\DTOs\FactCheckGateDTO;
use App\Features\ContentIntelligence\DTOs\KnowledgeFabricDTO;
use App\Features\ContentIntelligence\DTOs\MasterDocumentDTO;
use App\Features\ContentIntelligence\DTOs\MediaAssetDTO;
use App\Features\ContentIntelligence\DTOs\ResearchPlanDTO;
use App\Features\ContentIntelligence\DTOs\SearchIntelligenceDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\SeoMetadataDTO;
use App\Features\ContentIntelligence\DTOs\SourceIntelligenceDTO;
use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\ResearchBudgetTier;
use App\Features\ContentIntelligence\Enums\RiskLevel;
use App\Features\ContentIntelligence\Enums\SourceReliabilityTier;
use App\Features\ContentIntelligence\Livewire\ContentIntelligencePage;
use App\Features\ContentIntelligence\Models\ContentBlueprint;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\SectionDraft;
use App\Features\ContentIntelligence\Models\WorkflowNodeRecord;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Pipeline\ContentWorkflowEngine;
use App\Features\ContentIntelligence\Services\AdaptiveOutlineService;
use App\Features\ContentIntelligence\Services\ContentBlueprintService;
use App\Features\ContentIntelligence\Services\ContradictionResolverService;
use App\Features\ContentIntelligence\Services\CriticAgentService;
use App\Features\ContentIntelligence\Services\FactCheckGateService;
use App\Features\ContentIntelligence\Services\KnowledgeFabricService;
use App\Features\ContentIntelligence\Services\MediaEnhancerService;
use App\Features\ContentIntelligence\Services\ResearchDirectorService;
use App\Features\ContentIntelligence\Services\SearchIntelligenceService;
use App\Features\ContentIntelligence\Services\SectionDraftsmanService;
use App\Features\ContentIntelligence\Services\SeoOptimizationService;
use App\Features\ContentIntelligence\Services\TipTapDocumentAssembler;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class ContentIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_'.uniqid().'@helpofai.com',
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'name' => 'Author User',
            'email' => 'author_'.uniqid().'@helpofai.com',
            'role' => 'user',
        ]);
    }

    public function test_enums_provide_correct_metrics_and_hierarchy(): void
    {
        $this->assertEquals(15, ResearchBudgetTier::STANDARD->maxQueries());
        $this->assertEquals(0.85, ResearchBudgetTier::STANDARD->minConfidenceThreshold());

        $this->assertEquals(98, SourceReliabilityTier::OFFICIAL_DOCUMENTATION->defaultReliabilityScore());
        $this->assertTrue(SourceReliabilityTier::OFFICIAL_DOCUMENTATION->isPrimary());
        $this->assertFalse(SourceReliabilityTier::FORUM_SOCIAL->isPrimary());

        $this->assertTrue(EpistemicState::VERIFIED->isAssertable());
        $this->assertTrue(EpistemicState::UNVERIFIED->isBlocked());
        $this->assertTrue(EpistemicState::PARTIALLY_VERIFIED->requiresCaution());

        $this->assertTrue(RiskLevel::HIGH->requiresPrimarySources());
        $this->assertFalse(RiskLevel::LOW->requiresPrimarySources());
    }

    public function test_content_mission_dto_validates_and_serializes(): void
    {
        $dto = new ContentMissionDTO(
            topic: 'Enterprise Laravel 13 Queues',
            primaryObjective: 'In-depth architectural deployment guide',
            secondaryObjectives: ['Worker restarts', 'Memory leak prevention'],
            targetAudience: [
                'persona' => 'DevOps Engineer',
                'expertise_level' => 'Advanced',
                'pain_points' => ['Worker memory leaks'],
            ],
            targetWordCountRange: ['min' => 2000, 'max' => 4000],
            riskLevel: RiskLevel::HIGH,
            researchBudgetTier: ResearchBudgetTier::DEEP
        );

        $this->assertEquals('Enterprise Laravel 13 Queues', $dto->topic);
        $this->assertEquals(RiskLevel::HIGH, $dto->riskLevel);
        $this->assertEquals(ResearchBudgetTier::DEEP, $dto->researchBudgetTier);

        $machinePrompt = $dto->toMachinePrompt();
        $this->assertStringContainsString('=== CONTENT MISSION SPECIFICATION ===', $machinePrompt);
        $this->assertStringContainsString('Enterprise Laravel 13 Queues', $machinePrompt);

        // Validation failure on empty topic
        $this->expectException(InvalidArgumentException::class);
        new ContentMissionDTO(
            topic: '',
            primaryObjective: 'Objective'
        );
    }

    public function test_create_content_mission_action_initializes_run_and_state(): void
    {
        $action = new CreateContentMission;

        $result = $action->execute($this->regularUser, [
            'topic' => 'Production Queue Workers',
            'primary_objective' => 'Explain supervisor and pcntl signal handling',
            'audience' => 'Senior Developers',
            'min_words' => 1800,
            'max_words' => 3500,
            'risk_level' => 'medium',
            'research_budget_tier' => 'standard',
        ]);

        $this->assertInstanceOf(ContentMission::class, $result['mission']);
        $this->assertInstanceOf(WorkflowRun::class, $result['run']);

        $this->assertDatabaseHas('content_missions', [
            'id' => $result['mission']->id,
            'user_id' => $this->regularUser->id,
            'topic' => 'Production Queue Workers',
        ]);

        $this->assertDatabaseHas('workflow_runs', [
            'id' => $result['run']->id,
            'mission_id' => $result['mission']->id,
            'user_id' => $this->regularUser->id,
            'current_node' => 'mission_intake',
            'status' => ContentWorkflowStatus::QUEUED->value,
        ]);

        $dto = $result['mission']->toDTO();
        $this->assertEquals('Production Queue Workers', $dto->topic);
    }

    public function test_workflow_engine_executes_mission_node_and_updates_structured_state(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Advanced Microservices in PHP',
            'primary_objective' => 'Compare gRPC with HTTP/2 for service communication',
            'min_words' => 2000,
            'max_words' => 4000,
        ]);

        $run = $init['run'];
        $engine = new ContentWorkflowEngine;

        $this->assertTrue($engine->hasNode('mission_intake'));

        $stepResult = $engine->step($run);
        $updatedRun = $stepResult['run'];
        $nodeResult = $stepResult['result'];

        $this->assertTrue($nodeResult->isSuccess());
        $this->assertEquals('search_intelligence', $updatedRun->current_node);
        $this->assertEquals(ContentWorkflowStatus::RUNNING, $updatedRun->status);

        // Verify structured data handoff in graph_state
        $this->assertIsArray($updatedRun->graph_state);
        $this->assertArrayHasKey('mission_intake', $updatedRun->graph_state);
        $this->assertEquals($init['mission']->id, $updatedRun->graph_state['mission_intake']['mission_id']);
        $this->assertEquals('deep_authority', $updatedRun->graph_state['mission_intake']['target_depth']);

        // Verify WorkflowNodeRecord was logged
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'mission_intake',
            'status' => 'success',
        ]);
    }

    public function test_role_authorization_and_data_isolation(): void
    {
        $action = new CreateContentMission;
        $userMission = $action->execute($this->regularUser, [
            'topic' => 'User Private Mission',
            'primary_objective' => 'Confidential business strategy',
        ]);

        $adminMission = $action->execute($this->admin, [
            'topic' => 'Admin Global Mission',
            'primary_objective' => 'Platform-wide infrastructure documentation',
        ]);

        // Verify user can find their own mission
        $userVisibleMissions = ContentMission::where('user_id', $this->regularUser->id)->get();
        $this->assertTrue($userVisibleMissions->contains($userMission['mission']));
        $this->assertFalse($userVisibleMissions->contains($adminMission['mission']));

        // Verify admin has access to all records
        $allMissions = ContentMission::all();
        $this->assertTrue($allMissions->contains($userMission['mission']));
        $this->assertTrue($allMissions->contains($adminMission['mission']));
    }

    public function test_search_intelligence_service_deconstructs_intent_and_detects_gaps(): void
    {
        $dto = new ContentMissionDTO(
            topic: 'Laravel 13 Zero Downtime Queues',
            primaryObjective: 'Practical setup and SIGTERM supervision guide',
            secondaryObjectives: ['Prevent memory leaks', 'Configure Supervisor'],
            targetAudience: ['persona' => 'DevOps Engineer', 'expertise_level' => 'Advanced']
        );

        $service = new SearchIntelligenceService;
        $intel = $service->analyze($dto);

        $this->assertInstanceOf(SearchIntelligenceDTO::class, $intel);
        $this->assertStringContainsString('Implementation', $intel->primaryIntent);
        $this->assertEquals('Implementation', $intel->userJourneyStage);

        // Verify Query Clusters
        $this->assertNotEmpty($intel->queryClusters['primary']);
        $this->assertNotEmpty($intel->queryClusters['paa_questions']);

        // Verify Topic Universe
        $this->assertNotEmpty($intel->topicUniverse['core_topics']);
        $this->assertNotEmpty($intel->topicUniverse['supporting_topics']);

        // Verify Content Gaps
        $this->assertNotEmpty($intel->contentGaps['missing_topics']);
        $this->assertNotEmpty($intel->contentGaps['weak_angles']);

        // Verify Target Entities
        $this->assertContains('Laravel 13 Zero Downtime Queues', $intel->targetEntities);
    }

    public function test_research_director_service_formulates_plan_with_budget_tiers(): void
    {
        $mission = new ContentMissionDTO(
            topic: 'Laravel 13 Queue Concurrency',
            primaryObjective: 'Deep architectural benchmarking guide',
            researchBudgetTier: ResearchBudgetTier::DEEP
        );

        $searchService = new SearchIntelligenceService;
        $searchIntel = $searchService->analyze($mission);

        $director = new ResearchDirectorService;
        $plan = $director->formulatePlan($mission, $searchIntel);

        $this->assertInstanceOf(ResearchPlanDTO::class, $plan);
        $this->assertEquals(ResearchBudgetTier::DEEP, $plan->budgetTier);
        $this->assertEquals(30, $plan->allocatedBudget);
        $this->assertNotEmpty($plan->tasks);

        // Verify official documentation priority task exists
        $hasOfficialDocTask = false;
        foreach ($plan->tasks as $task) {
            if ($task->sourceTypePriority === SourceReliabilityTier::OFFICIAL_DOCUMENTATION) {
                $hasOfficialDocTask = true;
                break;
            }
        }
        $this->assertTrue($hasOfficialDocTask);
        $this->assertGreaterThanOrEqual(0.70, $plan->researchConfidence);
    }

    public function test_workflow_graph_multi_stage_execution_from_mission_to_research_director(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Supervisor Scaling',
            'primary_objective' => 'Explain supervisor deployment with zero worker drops',
            'research_budget_tier' => 'standard',
        ]);

        $run = $init['run'];
        $engine = new ContentWorkflowEngine;

        // Stage 0: Mission Intake
        $this->assertEquals('mission_intake', $run->current_node);
        $step1 = $engine->step($run);
        $this->assertTrue($step1['result']->isSuccess());
        $this->assertEquals('search_intelligence', $step1['run']->current_node);
        $this->assertArrayHasKey('mission_intake', $step1['run']->graph_state);

        // Stage 1: Search Intelligence
        $step2 = $engine->step($step1['run']);
        $this->assertTrue($step2['result']->isSuccess());
        $this->assertEquals('research_director', $step2['run']->current_node);
        $this->assertArrayHasKey('search_intelligence', $step2['run']->graph_state);
        $this->assertNotEmpty($step2['run']->graph_state['search_intelligence']['target_entities']);

        // Stage 2: Research Director
        $step3 = $engine->step($step2['run']);
        $this->assertTrue($step3['result']->isSuccess());
        $this->assertEquals('knowledge_fabric', $step3['run']->current_node);
        $this->assertArrayHasKey('research_director', $step3['run']->graph_state);
        $this->assertArrayHasKey('research_plan', $step3['run']->graph_state['research_director']);

        // Verify all 3 WorkflowNodeRecord execution logs exist in database
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'mission_intake',
            'status' => 'success',
        ]);
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'search_intelligence',
            'status' => 'success',
        ]);
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'research_director',
            'status' => 'success',
        ]);

        $this->assertEquals(3, WorkflowNodeRecord::where('workflow_run_id', $run->id)->count());
    }

    public function test_contradiction_resolver_identifies_and_resolves_conflicting_claims(): void
    {
        $sources = [
            'https://docs.laravel.com' => new SourceIntelligenceDTO(
                url: 'https://docs.laravel.com',
                title: 'Official Documentation',
                sourceType: SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
                reliabilityScore: 98,
                isPrimary: true
            ),
            'https://old-forum.org' => new SourceIntelligenceDTO(
                url: 'https://old-forum.org',
                title: 'User Forum',
                sourceType: SourceReliabilityTier::FORUM_SOCIAL,
                reliabilityScore: 40,
                isPrimary: false
            ),
        ];

        $claims = [
            new ClaimNodeDTO(
                claimId: 'clm_1',
                statement: 'Laravel 13 requires PHP 8.3 or greater',
                sourceUrl: 'https://docs.laravel.com',
                sectionTarget: 'requirements'
            ),
            new ClaimNodeDTO(
                claimId: 'clm_2',
                statement: 'Laravel 13 does not require PHP 8.3, works on PHP 8.1',
                sourceUrl: 'https://old-forum.org',
                sectionTarget: 'requirements'
            ),
        ];

        $resolver = new ContradictionResolverService;
        $result = $resolver->resolve($claims, $sources);

        $this->assertEquals(1, $result['contradictions_detected']);
        $this->assertEquals(1, $result['contradictions_resolved']);

        $resolvedClaims = $result['claims'];
        $this->assertCount(2, $resolvedClaims);

        // High authority official source should be marked VERIFIED
        $this->assertEquals(EpistemicState::VERIFIED, $resolvedClaims[0]->epistemicState);
        $this->assertEquals('authority', $resolvedClaims[0]->resolutionStrategy);

        // Low authority conflicting forum claim should be marked CONTRADICTED
        $this->assertEquals(EpistemicState::CONTRADICTED, $resolvedClaims[1]->epistemicState);
    }

    public function test_knowledge_fabric_service_synthesizes_sources_triples_and_lineage_claims(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Redis Cluster Queue Scaling',
            'primary_objective' => 'Implement high-throughput Redis queues with cluster sharding',
            'research_budget_tier' => 'standard',
        ]);

        $mission = $init['mission'];
        $missionDTO = $mission->toDTO();

        $director = new ResearchDirectorService;
        $searchService = new SearchIntelligenceService;
        $searchIntel = $searchService->analyze($missionDTO);
        $plan = $director->formulatePlan($missionDTO, $searchIntel);

        $fabricService = new KnowledgeFabricService;
        $fabric = $fabricService->synthesize($mission, $missionDTO, $plan);

        $this->assertInstanceOf(KnowledgeFabricDTO::class, $fabric);
        $this->assertNotEmpty($fabric->sources);
        $this->assertNotEmpty($fabric->triples);
        $this->assertNotEmpty($fabric->claims);
        $this->assertGreaterThanOrEqual(0.90, $fabric->knowledgeConfidence);

        // Verify Database Persistence
        $this->assertDatabaseHas('source_intelligences', [
            'reliability_score' => 98,
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('knowledge_triples', [
            'mission_id' => $mission->id,
            'subject' => 'Redis Cluster Queue Scaling',
            'predicate' => 'requires',
        ]);

        $this->assertDatabaseHas('claim_nodes', [
            'mission_id' => $mission->id,
            'epistemic_state' => EpistemicState::VERIFIED->value,
        ]);
    }

    public function test_workflow_graph_multi_stage_execution_from_mission_to_knowledge_fabric(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Horizon Metrics',
            'primary_objective' => 'Telemetry and workload monitoring guide',
            'research_budget_tier' => 'standard',
        ]);

        $run = $init['run'];
        $engine = new ContentWorkflowEngine;

        // Stage 0: Mission Intake
        $step0 = $engine->step($run);
        $this->assertEquals('search_intelligence', $step0['run']->current_node);

        // Stage 1: Search Intelligence
        $step1 = $engine->step($step0['run']);
        $this->assertEquals('research_director', $step1['run']->current_node);

        // Stage 2: Research Director
        $step2 = $engine->step($step1['run']);
        $this->assertEquals('knowledge_fabric', $step2['run']->current_node);

        // Stage 3: Knowledge Fabric
        $step3 = $engine->step($step2['run']);
        $this->assertTrue($step3['result']->isSuccess());
        $this->assertEquals('content_blueprint', $step3['run']->current_node);

        // Verify graph_state has accumulated structured payloads across all 4 stages
        $state = $step3['run']->graph_state;
        $this->assertArrayHasKey('mission_intake', $state);
        $this->assertArrayHasKey('search_intelligence', $state);
        $this->assertArrayHasKey('research_director', $state);
        $this->assertArrayHasKey('knowledge_fabric', $state);

        $this->assertGreaterThanOrEqual(2, $state['knowledge_fabric']['source_count']);
        $this->assertGreaterThanOrEqual(3, $state['knowledge_fabric']['triple_count']);
        $this->assertGreaterThanOrEqual(3, $state['knowledge_fabric']['claim_count']);

        // Verify all 4 WorkflowNodeRecord logs exist
        $this->assertEquals(4, WorkflowNodeRecord::where('workflow_run_id', $run->id)->count());
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'knowledge_fabric',
            'status' => 'success',
        ]);
    }

    public function test_content_blueprint_service_generates_angle_uvp_and_requirements(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Database Indexing',
            'primary_objective' => 'Mastering composite indexes for high-throughput Postgres queries',
        ]);

        $mission = $init['mission'];
        $missionDTO = $mission->toDTO();

        $searchIntel = (new SearchIntelligenceService)->analyze($missionDTO);
        $plan = (new ResearchDirectorService)->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = (new KnowledgeFabricService)->synthesize($mission, $missionDTO, $plan);

        $blueprintService = new ContentBlueprintService;
        $blueprint = $blueprintService->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);

        $this->assertInstanceOf(ContentBlueprintDTO::class, $blueprint);
        $this->assertStringContainsString('Production Database Indexing', $blueprint->articleAngle);
        $this->assertNotEmpty($blueprint->uniqueValueProposition);
        $this->assertNotEmpty($blueprint->requiredSections);
        $this->assertNotEmpty($blueprint->requiredEntities);

        // Verify Database Persistence
        $this->assertDatabaseHas('content_blueprints', [
            'mission_id' => $mission->id,
            'status' => 'approved',
        ]);
    }

    public function test_adaptive_outline_service_builds_section_hierarchy_and_maps_claims(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Zero Downtime Deployment Blueprints',
            'primary_objective' => 'Implement blue-green deployments with zero dropped connections',
            'min_words' => 2000,
            'max_words' => 3000,
        ]);

        $mission = $init['mission'];
        $missionDTO = $mission->toDTO();

        $searchIntel = (new SearchIntelligenceService)->analyze($missionDTO);
        $plan = (new ResearchDirectorService)->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = (new KnowledgeFabricService)->synthesize($mission, $missionDTO, $plan);

        $blueprintService = new ContentBlueprintService;
        $blueprintDTO = $blueprintService->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);
        $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->first();

        $outlineService = new AdaptiveOutlineService;
        $outline = $outlineService->build($mission, $blueprintModel, $blueprintDTO, $knowledgeFabric, $missionDTO);

        $this->assertInstanceOf(AdaptiveOutlineDTO::class, $outline);
        $this->assertGreaterThanOrEqual(5, $outline->totalSections);
        $this->assertEquals(2500, $outline->targetWordCount);
        $this->assertNotEmpty($outline->sections);

        // Verify that sections have assigned claims and dependency links
        $firstSection = $outline->sections[0];
        $this->assertEquals('sec_01', $firstSection->sectionId);
        $this->assertNotEmpty($firstSection->assignedClaimIds);
        $this->assertEmpty($firstSection->dependencySections);

        $secondSection = $outline->sections[1];
        $this->assertEquals('sec_02', $secondSection->sectionId);
        $this->assertEquals(['sec_01'], $secondSection->dependencySections);

        // Verify Database Persistence
        $this->assertDatabaseHas('content_outlines', [
            'blueprint_id' => $blueprintModel->id,
            'mission_id' => $mission->id,
            'status' => 'ready',
        ]);
    }

    public function test_workflow_graph_multi_stage_execution_from_mission_to_adaptive_outline(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Kubernetes Worker Autoscaling',
            'primary_objective' => 'HPA scaling and pod graceful termination hooks',
            'research_budget_tier' => 'standard',
        ]);

        $run = $init['run'];
        $engine = new ContentWorkflowEngine;

        // Stage 0: Mission Intake
        $step0 = $engine->step($run);
        $this->assertEquals('search_intelligence', $step0['run']->current_node);

        // Stage 1: Search Intelligence
        $step1 = $engine->step($step0['run']);
        $this->assertEquals('research_director', $step1['run']->current_node);

        // Stage 2: Research Director
        $step2 = $engine->step($step1['run']);
        $this->assertEquals('knowledge_fabric', $step2['run']->current_node);

        // Stage 3: Knowledge Fabric
        $step3 = $engine->step($step2['run']);
        $this->assertEquals('content_blueprint', $step3['run']->current_node);

        // Stage 4: Content Blueprint
        $step4 = $engine->step($step3['run']);
        $this->assertTrue($step4['result']->isSuccess());
        $this->assertEquals('adaptive_outline', $step4['run']->current_node);
        $this->assertArrayHasKey('content_blueprint', $step4['run']->graph_state);

        // Stage 5: Adaptive Outline
        $step5 = $engine->step($step4['run']);
        $this->assertTrue($step5['result']->isSuccess());
        $this->assertEquals('section_draftsman', $step5['run']->current_node);
        $this->assertArrayHasKey('adaptive_outline', $step5['run']->graph_state);
        $this->assertGreaterThanOrEqual(5, $step5['run']->graph_state['adaptive_outline']['total_sections']);

        // Verify all 6 WorkflowNodeRecord logs exist with status 'success'
        $this->assertEquals(6, WorkflowNodeRecord::where('workflow_run_id', $run->id)->count());
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'content_blueprint',
            'status' => 'success',
        ]);
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'adaptive_outline',
            'status' => 'success',
        ]);
    }

    public function test_section_draftsman_and_critic_agent_and_fact_check_gate(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Supervisor Scaling Guide',
            'primary_objective' => 'Explain supervisor deployment with zero worker drops',
        ]);

        $mission = $init['mission'];
        $run = $init['run'];
        $missionDTO = $mission->toDTO();

        $searchIntel = (new SearchIntelligenceService)->analyze($missionDTO);
        $plan = (new ResearchDirectorService)->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = (new KnowledgeFabricService)->synthesize($mission, $missionDTO, $plan);

        $blueprintService = new ContentBlueprintService;
        $blueprintDTO = $blueprintService->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);
        $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->first();

        $outlineService = new AdaptiveOutlineService;
        $outlineDTO = $outlineService->build($mission, $blueprintModel, $blueprintDTO, $knowledgeFabric, $missionDTO);

        $section = $outlineDTO->sections[0];

        // 1. Test Section Drafting
        $draftsman = new SectionDraftsmanService;
        $draft = $draftsman->draft($run, $section, $missionDTO, $knowledgeFabric);

        $this->assertInstanceOf(SectionDraftDTO::class, $draft);
        $this->assertEquals($section->sectionId, $draft->sectionId);
        $this->assertNotEmpty($draft->contentHtml);
        $this->assertGreaterThan(0, $draft->wordCount);

        // 2. Test Critic Evaluation
        $critic = new CriticAgentService;
        $criticScore = $critic->evaluate($draft, $section, $missionDTO, $knowledgeFabric);

        $this->assertInstanceOf(CriticScoreDTO::class, $criticScore);
        $this->assertGreaterThanOrEqual(75.0, $criticScore->overallScore);
        $this->assertGreaterThanOrEqual(70.0, $criticScore->factGrounding);

        // 3. Test Fact-Checking Gate
        $factGate = new FactCheckGateService;
        $gateResult = $factGate->audit($draft, $knowledgeFabric);

        $this->assertInstanceOf(FactCheckGateDTO::class, $gateResult);
        $this->assertTrue($gateResult->passed);
        $this->assertEquals(0, $gateResult->hallucinationsDetected);
        $this->assertGreaterThanOrEqual(1, $gateResult->verifiedClaimsCount);

        // Verify Database Persistence
        $this->assertDatabaseHas('section_drafts', [
            'workflow_run_id' => $run->id,
            'section_id' => $section->sectionId,
        ]);
    }

    public function test_workflow_graph_multi_stage_execution_from_mission_to_section_drafting_with_critic_loop(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Kubernetes Worker Autoscaling Guide',
            'primary_objective' => 'HPA scaling and pod graceful termination hooks',
            'research_budget_tier' => 'standard',
        ]);

        $run = $init['run'];
        $engine = new ContentWorkflowEngine;

        // Step through: mission_intake -> search_intelligence -> research_director -> knowledge_fabric -> content_blueprint -> adaptive_outline
        $engine->step($run); // 0 -> search_intelligence
        $engine->step($run); // 1 -> research_director
        $engine->step($run); // 2 -> knowledge_fabric
        $engine->step($run); // 3 -> content_blueprint
        $engine->step($run); // 4 -> adaptive_outline
        $engine->step($run); // 5 -> section_draftsman

        $this->assertEquals('section_draftsman', $run->current_node);

        // Step 6: Execute SectionWriterNode (Drafts all sections, runs critic loop & fact gate)
        $writerStep = $engine->step($run);
        $this->assertTrue($writerStep['result']->isSuccess());
        $this->assertEquals('seo_optimization', $writerStep['run']->current_node);

        // Verify graph_state has section drafting outputs
        $state = $writerStep['run']->graph_state;
        $this->assertArrayHasKey('section_draftsman', $state);
        $this->assertGreaterThanOrEqual(5, $state['section_draftsman']['total_sections_drafted']);
        $this->assertGreaterThan(0, $state['section_draftsman']['total_word_count']);
        $this->assertGreaterThanOrEqual(75.0, $state['section_draftsman']['average_critic_score']);
        $this->assertTrue($state['section_draftsman']['fact_gates_all_passed']);

        // Verify WorkflowNodeRecord was logged
        $this->assertDatabaseHas('workflow_nodes', [
            'workflow_run_id' => $run->id,
            'node_name' => 'section_draftsman',
            'status' => 'success',
        ]);

        // Verify SectionDraft database records exist
        $this->assertGreaterThanOrEqual(5, SectionDraft::where('workflow_run_id', $run->id)->count());
    }

    public function test_seo_optimization_service_generates_meta_and_schemas(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Supervisor Scaling Guide',
            'primary_objective' => 'Explain supervisor deployment with zero worker drops',
        ]);

        $mission = $init['mission'];
        $run = $init['run'];
        $missionDTO = $mission->toDTO();

        $searchIntel = (new SearchIntelligenceService)->analyze($missionDTO);
        $plan = (new ResearchDirectorService)->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = (new KnowledgeFabricService)->synthesize($mission, $missionDTO, $plan);
        $blueprintDTO = (new ContentBlueprintService)->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);
        $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->first();
        $outlineDTO = (new AdaptiveOutlineService)->build($mission, $blueprintModel, $blueprintDTO, $knowledgeFabric, $missionDTO);

        $draftsman = new SectionDraftsmanService;
        $drafts = [];
        foreach ($outlineDTO->sections as $sec) {
            $drafts[] = $draftsman->draft($run, $sec, $missionDTO, $knowledgeFabric);
        }

        $seoService = new SeoOptimizationService;
        $seoDTO = $seoService->optimize($run, $missionDTO, $blueprintDTO, $drafts);

        $this->assertInstanceOf(SeoMetadataDTO::class, $seoDTO);
        $this->assertNotEmpty($seoDTO->metaTitle);
        $this->assertLessThanOrEqual(60, strlen($seoDTO->metaTitle));
        $this->assertNotEmpty($seoDTO->metaDescription);
        $this->assertNotEmpty($seoDTO->canonicalUrl);
        $this->assertGreaterThanOrEqual(80, $seoDTO->seoScore);
        $this->assertArrayHasKey('@graph', $seoDTO->schemaJsonLd);
        $this->assertGreaterThanOrEqual(2, count($seoDTO->schemaJsonLd['@graph']));

        // Verify Database Persistence
        $this->assertDatabaseHas('content_seo_metadatas', [
            'workflow_run_id' => $run->id,
            'mission_id' => $mission->id,
            'primary_keyword' => $seoDTO->primaryKeyword,
        ]);
    }

    public function test_media_enhancer_service_synthesizes_diagrams_and_tables(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Supervisor Scaling Guide',
            'primary_objective' => 'Explain supervisor deployment with zero worker drops',
        ]);

        $mission = $init['mission'];
        $run = $init['run'];
        $missionDTO = $mission->toDTO();

        $searchIntel = (new SearchIntelligenceService)->analyze($missionDTO);
        $plan = (new ResearchDirectorService)->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = (new KnowledgeFabricService)->synthesize($mission, $missionDTO, $plan);
        $blueprintDTO = (new ContentBlueprintService)->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);
        $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->first();
        $outlineDTO = (new AdaptiveOutlineService)->build($mission, $blueprintModel, $blueprintDTO, $knowledgeFabric, $missionDTO);

        $draftsman = new SectionDraftsmanService;
        $drafts = [];
        foreach ($outlineDTO->sections as $sec) {
            $drafts[] = $draftsman->draft($run, $sec, $missionDTO, $knowledgeFabric);
        }

        $enhancer = new MediaEnhancerService;
        $assets = $enhancer->enhance($run, $missionDTO, $knowledgeFabric, $drafts);

        $this->assertNotEmpty($assets);
        $this->assertGreaterThanOrEqual(2, count($assets));

        // Check for diagram asset
        $hasDiagram = false;
        $hasTable = false;
        foreach ($assets as $asset) {
            $this->assertInstanceOf(MediaAssetDTO::class, $asset);
            if ($asset->assetType === 'diagram') {
                $hasDiagram = true;
                $this->assertStringContainsString('mermaid', $asset->content);
            }
            if ($asset->assetType === 'table') {
                $hasTable = true;
                $this->assertStringContainsString('<table', $asset->content);
            }
        }
        $this->assertTrue($hasDiagram);
        $this->assertTrue($hasTable);

        // Verify Database Persistence
        $this->assertDatabaseHas('content_media_assets', [
            'workflow_run_id' => $run->id,
            'asset_type' => 'diagram',
        ]);
    }

    public function test_tiptap_document_assembler_creates_canonical_ast_and_persists_document(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Supervisor Scaling Guide',
            'primary_objective' => 'Explain supervisor deployment with zero worker drops',
        ]);

        $mission = $init['mission'];
        $run = $init['run'];
        $missionDTO = $mission->toDTO();

        $searchIntel = (new SearchIntelligenceService)->analyze($missionDTO);
        $plan = (new ResearchDirectorService)->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = (new KnowledgeFabricService)->synthesize($mission, $missionDTO, $plan);
        $blueprintDTO = (new ContentBlueprintService)->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);
        $blueprintModel = ContentBlueprint::where('mission_id', $mission->id)->first();
        $outlineDTO = (new AdaptiveOutlineService)->build($mission, $blueprintModel, $blueprintDTO, $knowledgeFabric, $missionDTO);

        $draftsman = new SectionDraftsmanService;
        $drafts = [];
        foreach ($outlineDTO->sections as $sec) {
            $drafts[] = $draftsman->draft($run, $sec, $missionDTO, $knowledgeFabric);
        }

        $seoDTO = (new SeoOptimizationService)->optimize($run, $missionDTO, $blueprintDTO, $drafts);
        $mediaAssets = (new MediaEnhancerService)->enhance($run, $missionDTO, $knowledgeFabric, $drafts);

        $assembler = new TipTapDocumentAssembler;
        $masterDoc = $assembler->assemble($run, $missionDTO, $blueprintDTO, $drafts, $seoDTO, $mediaAssets);

        $this->assertInstanceOf(MasterDocumentDTO::class, $masterDoc);
        $this->assertNotNull($masterDoc->documentId);
        $this->assertNotEmpty($masterDoc->title);
        $this->assertNotEmpty($masterDoc->slug);
        $this->assertNotEmpty($masterDoc->html);
        $this->assertNotEmpty($masterDoc->markdown);
        $this->assertGreaterThan(0, $masterDoc->wordCount);
        $this->assertGreaterThan(0, $masterDoc->readingTimeMinutes);

        // Verify TipTap JSON AST Structure
        $this->assertArrayHasKey('type', $masterDoc->tiptapJson);
        $this->assertEquals('doc', $masterDoc->tiptapJson['type']);
        $this->assertArrayHasKey('content', $masterDoc->tiptapJson);
        $this->assertNotEmpty($masterDoc->tiptapJson['content']);

        // Verify HOA Document and DocumentContent models exist in DB
        $this->assertDatabaseHas('documents', [
            'id' => $masterDoc->documentId,
            'user_id' => $this->regularUser->id,
            'title' => $masterDoc->title,
        ]);

        $this->assertDatabaseHas('document_contents', [
            'document_id' => $masterDoc->documentId,
        ]);

        // Verify WorkflowRun is linked to Document
        $this->assertEquals($masterDoc->documentId, $run->fresh()->document_id);
    }

    public function test_full_ten_stage_end_to_end_workflow_execution(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Production Horizon Queue Architecture',
            'primary_objective' => 'Complete end-to-end mission guide with high availability and fault tolerance',
            'research_budget_tier' => 'standard',
        ]);

        $run = $init['run'];
        $engine = new ContentWorkflowEngine;

        // Stage 0: Mission Intake
        $s0 = $engine->step($run);
        $this->assertEquals('search_intelligence', $s0['run']->current_node);

        // Stage 1: Search Intelligence
        $s1 = $engine->step($s0['run']);
        $this->assertEquals('research_director', $s1['run']->current_node);

        // Stage 2: Research Director
        $s2 = $engine->step($s1['run']);
        $this->assertEquals('knowledge_fabric', $s2['run']->current_node);

        // Stage 3: Knowledge Fabric
        $s3 = $engine->step($s2['run']);
        $this->assertEquals('content_blueprint', $s3['run']->current_node);

        // Stage 4: Content Blueprint
        $s4 = $engine->step($s3['run']);
        $this->assertEquals('adaptive_outline', $s4['run']->current_node);

        // Stage 5: Adaptive Outline
        $s5 = $engine->step($s4['run']);
        $this->assertEquals('section_draftsman', $s5['run']->current_node);

        // Stage 6: Section Draftsman + Critic Loop + Fact Gate
        $s6 = $engine->step($s5['run']);
        $this->assertEquals('seo_optimization', $s6['run']->current_node);

        // Stage 7: SEO Optimization & Google Schema JSON-LD
        $s7 = $engine->step($s6['run']);
        $this->assertTrue($s7['result']->isSuccess());
        $this->assertEquals('media_enhancement', $s7['run']->current_node);

        // Stage 8: Rich Media Enhancement (Diagrams, Tables, Evidence)
        $s8 = $engine->step($s7['run']);
        $this->assertTrue($s8['result']->isSuccess());
        $this->assertEquals('master_assembly', $s8['run']->current_node);

        // Stage 9: Master Assembly & TipTap AST Conversion
        $s9 = $engine->step($s8['run']);
        $this->assertTrue($s9['result']->isSuccess());

        // Verify Workflow Run is fully COMPLETED!
        $finalRun = $s9['run']->fresh();
        $this->assertEquals(ContentWorkflowStatus::COMPLETED, $finalRun->status);
        $this->assertNotNull($finalRun->completed_at);
        $this->assertNotNull($finalRun->document_id);
        $this->assertGreaterThanOrEqual(0.70, $finalRun->overall_confidence);

        // Verify all 10 WorkflowNodeRecord logs exist with status 'success'
        $this->assertEquals(10, WorkflowNodeRecord::where('workflow_run_id', $run->id)->count());
        $this->assertEquals(10, WorkflowNodeRecord::where('workflow_run_id', $run->id)->where('status', 'success')->count());

        // Verify persisted Document in database
        $this->assertDatabaseHas('documents', [
            'id' => $finalRun->document_id,
            'user_id' => $this->regularUser->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('document_contents', [
            'document_id' => $finalRun->document_id,
        ]);

        // Verify full graph state contains all stage payloads
        $state = $finalRun->graph_state;
        $this->assertArrayHasKey('mission_intake', $state);
        $this->assertArrayHasKey('search_intelligence', $state);
        $this->assertArrayHasKey('research_director', $state);
        $this->assertArrayHasKey('knowledge_fabric', $state);
        $this->assertArrayHasKey('content_blueprint', $state);
        $this->assertArrayHasKey('adaptive_outline', $state);
        $this->assertArrayHasKey('section_draftsman', $state);
        $this->assertArrayHasKey('seo_optimization', $state);
        $this->assertArrayHasKey('media_enhancement', $state);
        $this->assertArrayHasKey('master_assembly', $state);

        $this->assertTrue($state['master_assembly']['is_publish_ready']);
        $this->assertGreaterThan(0, $state['master_assembly']['word_count']);
    }

    public function test_content_intelligence_page_renders_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('content-intelligence.index'));
        $response->assertStatus(200);
        $response->assertSee('Content Intelligence');
        $response->assertSee('Dynamic Workflow Graph');
        $response->assertSee('New Mission');
    }

    public function test_content_intelligence_page_blocks_unauthenticated_guests(): void
    {
        $response = $this->get(route('content-intelligence.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_create_mission_from_livewire_page(): void
    {
        Livewire::actingAs($this->regularUser)
            ->test(ContentIntelligencePage::class)
            ->set('topic', 'Scalable Queue Architecture in Laravel 13')
            ->set('primaryObjective', 'Deep dive into horizon metrics and graceful SIGTERM shutdown')
            ->set('audiencePersona', 'Staff Engineer')
            ->set('expertiseLevel', 'Advanced')
            ->set('riskLevel', 'high')
            ->set('researchBudgetTier', 'deep')
            ->set('minWords', 2500)
            ->set('maxWords', 5000)
            ->call('createMission')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('content_missions', [
            'user_id' => $this->regularUser->id,
            'topic' => 'Scalable Queue Architecture in Laravel 13',
            'risk_level' => 'high',
            'research_budget_tier' => 'deep',
        ]);

        $this->assertDatabaseHas('workflow_runs', [
            'user_id' => $this->regularUser->id,
            'current_node' => 'mission_intake',
        ]);
    }

    public function test_user_can_step_workflow_from_livewire_page(): void
    {
        $action = new CreateContentMission;
        $init = $action->execute($this->regularUser, [
            'topic' => 'Interactive Workflow Step Test',
            'primary_objective' => 'Step through nodes via Livewire UI',
        ]);

        $run = $init['run'];

        Livewire::actingAs($this->regularUser)
            ->test(ContentIntelligencePage::class)
            ->call('stepWorkflow', $run->id)
            ->assertHasNoErrors();

        $this->assertEquals('search_intelligence', $run->fresh()->current_node);
    }

    public function test_workspace_sidebar_contains_content_intelligence_menu_item(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee(route('content-intelligence.index'));
        $response->assertSee('Content Intelligence');
    }

    public function test_content_intelligence_page_renders_clean_pagination_without_raw_php_snippets(): void
    {
        $action = new CreateContentMission;
        for ($i = 1; $i <= 12; $i++) {
            $action->execute($this->regularUser, [
                'topic' => "Mission Number {$i}",
                'primary_objective' => 'Objective for pagination testing',
            ]);
        }

        $response = $this->actingAs($this->regularUser)->get(route('content-intelligence.index'));
        $response->assertStatus(200);
        $response->assertDontSee('scrollIntoViewJsSnippet');
        $response->assertDontSee('php if (! isset($scrollTo))');
        $response->assertDontSee('<?php');
        $response->assertSee('Next');
    }

    public function test_gaming_domain_and_item_list_extraction_generates_clean_sections_and_prose(): void
    {
        $topic = 'free fire max similer game';
        $thesis = 'If you enjoy Free Fire MAX, you can try PUBG Mobile, Call of Duty: Mobile, Omega Legends. Top Alternatives PUBG Mobile: A 100-player battle royale game APKPure.com. Call of Duty: Mobile: Combines first-person shooter mechanics APKPure.com. Omega Legends: Features multiple battle royale modes moregameslike.com.';

        $domain = \App\Features\ContentIntelligence\Services\ContentDomainClassifier::classify($topic, $thesis);
        $this->assertEquals(\App\Features\ContentIntelligence\Services\ContentDomainClassifier::DOMAIN_GAMING, $domain);

        $action = new CreateContentMission;
        $missionData = $action->execute($this->regularUser, [
            'topic' => $topic,
            'primary_objective' => $thesis,
        ]);

        $blueprintService = new \App\Features\ContentIntelligence\Services\ContentBlueprintService;
        $searchService = new \App\Features\ContentIntelligence\Services\SearchIntelligenceService;
        $directorService = new \App\Features\ContentIntelligence\Services\ResearchDirectorService;
        $knowledgeService = new \App\Features\ContentIntelligence\Services\KnowledgeFabricService;

        $missionDTO = $missionData['mission']->toDTO();
        $searchIntel = $searchService->analyze($missionDTO);
        $plan = $directorService->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = $knowledgeService->synthesize($missionData['mission'], $missionDTO, $plan);

        $blueprint = $blueprintService->generate($missionData['mission'], $missionDTO, $searchIntel, $knowledgeFabric);

        // Assert section headings do not contain raw domain names or messy citation fragments
        foreach ($blueprint->requiredSections as $sec) {
            $this->assertStringNotContainsString('APKPure.com', $sec);
            $this->assertStringNotContainsString('moregameslike.com', $sec);
            $this->assertLessThan(90, strlen($sec));
        }

        $this->assertContains('PUBG Mobile: Gameplay, Features & Player Experience', $blueprint->requiredSections);
        $this->assertContains('Call of Duty: Mobile: Gameplay, Features & Player Experience', $blueprint->requiredSections);
        $this->assertContains('Omega Legends: Gameplay, Features & Player Experience', $blueprint->requiredSections);
    }

    public function test_article_archetype_enum_attributes_and_defaults(): void
    {
        $archetypes = ArticleArchetype::cases();
        $this->assertCount(6, $archetypes);

        foreach ($archetypes as $arch) {
            $this->assertNotEmpty($arch->label());
            $this->assertNotEmpty($arch->shortLabel());
            $this->assertNotEmpty($arch->description());
            $this->assertNotEmpty($arch->icon());
            $range = $arch->defaultWordRange();
            $this->assertArrayHasKey('min', $range);
            $this->assertArrayHasKey('max', $range);
            $this->assertGreaterThan($range['min'], $range['max']);

            $templates = $arch->defaultSectionTemplates('Test Topic');
            $this->assertIsArray($templates);
            $this->assertGreaterThanOrEqual(4, count($templates));
        }
    }

    public function test_content_mission_with_different_article_archetypes(): void
    {
        $action = new CreateContentMission;
        $result = $action->execute($this->regularUser, [
            'topic' => 'Microservices with gRPC',
            'primary_objective' => 'Deep technical breakdown of gRPC streaming and proto architecture',
            'article_archetype' => ArticleArchetype::TECHNICAL_TEARDOWN,
        ]);

        $mission = $result['mission'];
        $this->assertEquals(ArticleArchetype::TECHNICAL_TEARDOWN, $mission->article_archetype);
        $this->assertEquals(ArticleArchetype::TECHNICAL_TEARDOWN, $mission->toDTO()->archetype);

        $blueprintService = new ContentBlueprintService;
        $searchService = new SearchIntelligenceService;
        $directorService = new ResearchDirectorService;
        $knowledgeService = new KnowledgeFabricService;

        $missionDTO = $mission->toDTO();
        $searchIntel = $searchService->analyze($missionDTO);
        $plan = $directorService->formulatePlan($missionDTO, $searchIntel);
        $knowledgeFabric = $knowledgeService->synthesize($mission, $missionDTO, $plan);

        $blueprint = $blueprintService->generate($mission, $missionDTO, $searchIntel, $knowledgeFabric);
        $this->assertNotEmpty($blueprint->requiredSections);
        $this->assertContains('System Architecture & Core Engine Mechanisms of Microservices With GRPC', $blueprint->requiredSections);
    }

    public function test_livewire_content_intelligence_page_presets_and_archetype_selection(): void
    {
        Livewire::actingAs($this->regularUser)
            ->test(ContentIntelligencePage::class)
            ->assertStatus(200)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->call('applyPreset', 'comparative_roundup')
            ->assertSet('articleArchetype', 'comparative_roundup')
            ->assertSet('minWords', 2200)
            ->assertSet('maxWords', 4000)
            ->call('applyPreset', 'technical_teardown')
            ->assertSet('articleArchetype', 'technical_teardown')
            ->assertSet('expertiseLevel', 'Advanced')
            ->set('topic', 'Next.js vs Remix vs Astro')
            ->set('primaryObjective', 'Full comparative benchmark and architectural review of modern frameworks')
            ->call('createMission')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);
    }

    public function test_authenticated_user_can_stream_workflow_step_with_sse(): void
    {
        $action = new CreateContentMission;
        $created = $action->execute($this->regularUser, [
            'topic' => 'SSE Streaming Engine in Distributed Systems',
            'primary_objective' => 'Explore reactive web streams and SSE connections',
            'article_archetype' => 'technical_teardown',
        ]);

        $run = $created['run'];

        $response = $this->actingAs($this->regularUser)
            ->get(route('content-intelligence.stream', $run->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));
    }

    public function test_unauthenticated_guest_cannot_stream_workflow_step(): void
    {
        $response = $this->get('/dashboard/content-intelligence/stream/1');
        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_stream_other_users_workflow_step(): void
    {
        $action = new CreateContentMission;
        $created = $action->execute($this->admin, [
            'topic' => 'Admin Secret AI Research',
            'primary_objective' => 'Confidential analysis',
        ]);

        $run = $created['run'];

        $response = $this->actingAs($this->regularUser)
            ->get(route('content-intelligence.stream', $run->id));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('Workflow run not found', $content);
    }
}
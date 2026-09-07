<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 4 Agent Orchestrator & Router Test Suite
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
use App\Features\ContentIntelligence\Blackboard\MissionBlackboard;
use App\Features\ContentIntelligence\DTOs\AgentTaskDTO;
use App\Features\ContentIntelligence\Enums\AgentRole;
use App\Features\ContentIntelligence\Enums\TaskType;
use App\Features\ContentIntelligence\Livewire\ContentIntelligencePage;
use App\Features\ContentIntelligence\Services\AgentOrchestratorService;
use App\Features\ContentIntelligence\Services\BrainDecisionEngine;
use App\Features\ContentIntelligence\Services\ModelRouterService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBrainAgentOrchestratorAndRouterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'orchestrator_lead@helpofai.com',
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'ai_director@helpofai.com',
            'role' => 'admin',
        ]);
    }

    public function test_orchestrator_initializes_with_seven_worker_agents(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $agents = $orchestrator->getAllAgents();

        $this->assertCount(7, $agents);
        $this->assertArrayHasKey('researcher', $agents);
        $this->assertArrayHasKey('analyst', $agents);
        $this->assertArrayHasKey('writer', $agents);
        $this->assertArrayHasKey('fact_checker', $agents);
        $this->assertArrayHasKey('critic', $agents);
        $this->assertArrayHasKey('seo', $agents);
        $this->assertArrayHasKey('editor', $agents);

        $this->assertSame(AgentRole::RESEARCHER, $orchestrator->getAgent('researcher')->getRole());
        $this->assertSame(AgentRole::WRITER, $orchestrator->getAgent(AgentRole::WRITER)->getRole());
    }

    public function test_researcher_agent_execution_updates_blackboard(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $blackboard = new MissionBlackboard(['mission_topic' => 'PostgreSQL 16 Replication']);

        $task = new AgentTaskDTO(
            taskId: 'task_research_01',
            taskType: TaskType::RESEARCH_SYNTHESIS,
            instruction: 'Investigate official documentation for PostgreSQL 16 standby decoding.'
        );

        $result = $orchestrator->dispatch(
            role: 'researcher',
            task: $task,
            blackboard: $blackboard
        );

        $this->assertTrue($result->isSuccessful);
        $this->assertSame('researcher', $result->agentName);
        $this->assertNotEmpty($result->outputData['primary_sources']);

        // Verify Blackboard update
        $this->assertContains('PostgreSQL 16 Replication', $blackboard->get('important_entities'));
        $this->assertNotEmpty($blackboard->get('known_facts'));
    }

    public function test_analyst_agent_identifies_gaps_and_sets_hypotheses(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $blackboard = new MissionBlackboard(['mission_topic' => 'Redis Enterprise Clusters']);

        $task = new AgentTaskDTO(
            taskId: 'task_analyst_01',
            taskType: TaskType::REASONING_ANALYSIS,
            instruction: 'Analyze SERP gaps and formulate Information Gain thesis.'
        );

        $result = $orchestrator->dispatch(
            role: 'analyst',
            task: $task,
            blackboard: $blackboard
        );

        $this->assertTrue($result->isSuccessful);
        $this->assertNotEmpty($result->outputData['information_gain_angle']);
        $this->assertNotEmpty($blackboard->get('hypotheses'));
        $this->assertNotNull($blackboard->get('next_best_action'));
    }

    public function test_writer_agent_composes_grounded_prose(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $blackboard = new MissionBlackboard([
            'mission_topic' => 'Laravel 13 Architecture',
            'known_facts' => ['PHP 8.3 required', 'Asynchronous queue workers'],
        ]);

        $task = new AgentTaskDTO(
            taskId: 'task_write_01',
            taskType: TaskType::CREATIVE_WRITING,
            instruction: 'Write production deployment section.',
            contextPayload: ['section_title' => 'Core Architecture of Laravel 13']
        );

        $result = $orchestrator->dispatch('writer', $task, $blackboard);

        $this->assertTrue($result->isSuccessful);
        $this->assertGreaterThan(20, $result->outputData['word_count']);
        $this->assertStringContainsString('Laravel 13', $result->outputData['draft_html']);
    }

    public function test_fact_checker_agent_audits_claims(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $blackboard = new MissionBlackboard;

        $task = new AgentTaskDTO(
            taskId: 'task_fact_01',
            taskType: TaskType::FACT_CHECKING,
            instruction: 'Cross-audit drafted claim statements.',
            contextPayload: [
                'claims' => [
                    'Laravel 13 queue workers trap SIGTERM signals for graceful termination.',
                    'Redis clusters replicate data with sub-millisecond network roundtrips.',
                ],
            ]
        );

        $result = $orchestrator->dispatch('fact_checker', $task, $blackboard);

        $this->assertTrue($result->isSuccessful);
        $this->assertSame(2, $result->outputData['total_claims_audited']);
        $this->assertSame(2, $result->outputData['verified_claims_count']);
    }

    public function test_critic_agent_evaluates_six_quality_rubrics(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $blackboard = new MissionBlackboard;

        $task = new AgentTaskDTO(
            taskId: 'task_critic_01',
            taskType: TaskType::CRITIQUE_EVALUATION,
            instruction: 'Evaluate drafted section across 6 dimensions.'
        );

        $result = $orchestrator->dispatch('critic', $task, $blackboard);

        $this->assertTrue($result->isSuccessful);
        $this->assertArrayHasKey('overall_score', $result->outputData);
        $this->assertArrayHasKey('dimension_scores', $result->outputData);
        $this->assertArrayHasKey('fact_grounding', $result->outputData['dimension_scores']);
        $this->assertTrue($result->outputData['passes_gating']);
    }

    public function test_seo_and_editor_agents_refine_content(): void
    {
        $orchestrator = app(AgentOrchestratorService::class);
        $blackboard = new MissionBlackboard(['mission_topic' => 'GraphQL vs REST']);

        // 1. SEO Agent
        $seoTask = new AgentTaskDTO(
            taskId: 'task_seo_01',
            taskType: TaskType::SEO_OPTIMIZATION,
            instruction: 'Generate SERP meta and schema.'
        );
        $seoResult = $orchestrator->dispatch('seo', $seoTask, $blackboard);
        $this->assertTrue($seoResult->isSuccessful);
        $this->assertArrayHasKey('meta_title', $seoResult->outputData);
        $this->assertSame('TechArticle', $seoResult->outputData['schema_type']);

        // 2. Editor Agent
        $editTask = new AgentTaskDTO(
            taskId: 'task_edit_01',
            taskType: TaskType::PROOFREADING_EDITING,
            instruction: 'Polish cadence and check readability.'
        );
        $editResult = $orchestrator->dispatch('editor', $editTask, $blackboard);
        $this->assertTrue($editResult->isSuccessful);
        $this->assertArrayHasKey('flesch_reading_ease_score', $editResult->outputData);
        $this->assertGreaterThan(0, $editResult->outputData['passive_voice_eliminations']);
    }

    public function test_model_router_assigns_specialized_models_by_task_type(): void
    {
        $router = app(ModelRouterService::class);

        // Creative Writing -> Flagship model with high accuracy
        $writeRoute = $router->routeTask(TaskType::CREATIVE_WRITING);
        $this->assertSame('high_accuracy', $writeRoute->qualityTier);
        $this->assertNotEmpty($writeRoute->selectedModelId);

        // Reasoning Analysis -> Reasoning model
        $reasoningRoute = $router->routeTask(TaskType::REASONING_ANALYSIS);
        $this->assertSame('reasoning', $reasoningRoute->qualityTier);
        $this->assertTrue($reasoningRoute->isReasoningModel);

        // Classification -> Fast tier with sub-second latency
        $classRoute = $router->routeTask(TaskType::CLASSIFICATION);
        $this->assertSame('fast', $classRoute->qualityTier);
        $this->assertLessThan(600, $classRoute->estimatedLatencyMs);

        // Direct Override
        $overrideRoute = $router->routeTask(TaskType::RESEARCH_SYNTHESIS, ['model_override' => 'custom-expert-v1']);
        $this->assertSame('custom-expert-v1', $overrideRoute->selectedModelId);

        // Routing matrix completeness
        $matrix = $router->getRoutingMatrix();
        $this->assertCount(count(TaskType::cases()), $matrix);
    }

    public function test_brain_decision_engine_logs_explainable_decisions(): void
    {
        $decisionEngine = app(BrainDecisionEngine::class);
        $blackboard = new MissionBlackboard;

        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Cloudflare Workers Architecture',
            'primary_objective' => 'Deep dive',
        ]);
        $mission = $missionData['mission'];
        $run = $missionData['run'];

        // Decision 1: Secondary research deepening
        $decision = $decisionEngine->decide(
            question: 'Should we deepen secondary research?',
            inputs: ['current_confidence' => 0.65, 'risk_level' => 'high'],
            candidateOptions: ['EXECUTE_SECONDARY_RESEARCH', 'PROCEED_TO_BLUEPRINT'],
            blackboard: $blackboard,
            missionId: $mission->id,
            workflowRunId: $run->id
        );

        $this->assertSame('EXECUTE_SECONDARY_RESEARCH', $decision->decision);
        $this->assertNotEmpty($decision->reasoning);
        $this->assertDatabaseHas('brain_decisions', [
            'id' => $decision->id,
            'mission_id' => $mission->id,
            'decision' => 'EXECUTE_SECONDARY_RESEARCH',
        ]);

        $this->assertNotEmpty($blackboard->get('decisions'));
    }

    public function test_livewire_content_intelligence_page_renders_agents_router_tab_and_triggers_dispatch(): void
    {
        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Production Kubernetes Ingress Architecture',
            'primary_objective' => 'Production blueprint guide',
        ]);
        $mission = $missionData['mission'];
        $run = $missionData['run'];

        Livewire::actingAs($this->user)
            ->test(ContentIntelligencePage::class)
            ->set('selectedRunId', $run->id)
            ->call('setInspectorTab', 'agents_router')
            ->assertSee('Agent Orchestrator Worker Pool')
            ->assertSee('Brain Blackboard (Cognitive Workspace)')
            ->assertSee('Multi-Model Router Matrix')
            ->assertSee('Explainable Brain Decisions Log')
            ->call('dispatchWorkerAgent', 'researcher')
            ->assertSee('Researcher Agent');

        $this->assertDatabaseHas('agent_activities', [
            'workflow_run_id' => $run->id,
            'agent_name' => 'researcher',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_content_intelligence_page(): void
    {
        $response = $this->get(route('content-intelligence.index'));
        $response->assertRedirect(route('login'));
    }
}

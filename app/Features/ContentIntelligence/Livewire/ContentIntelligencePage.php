<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Intelligence Page
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

namespace App\Features\ContentIntelligence\Livewire;

use App\Features\ContentIntelligence\Actions\CreateContentMission;
use App\Features\ContentIntelligence\Blackboard\MissionBlackboard;
use App\Features\ContentIntelligence\DTOs\AgentTaskDTO;
use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Enums\ProblemCategory;
use App\Features\ContentIntelligence\Enums\RepairUnitType;
use App\Features\ContentIntelligence\Enums\TaskType;
use App\Features\ContentIntelligence\Memory\MemoryManager;
use App\Features\ContentIntelligence\Models\AgentActivity;
use App\Features\ContentIntelligence\Models\ArticleElementNode;
use App\Features\ContentIntelligence\Models\BrainDecision;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\ContentGenome;
use App\Features\ContentIntelligence\Models\ContentLineageNode;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\EpisodicEvent;
use App\Features\ContentIntelligence\Models\EvidenceSnippet;
use App\Features\ContentIntelligence\Models\MemoryCandidate;
use App\Features\ContentIntelligence\Models\MicroRepair;
use App\Features\ContentIntelligence\Models\QualityHealthAudit;
use App\Features\ContentIntelligence\Models\RiskAssessment;
use App\Features\ContentIntelligence\Models\SiteTopicCluster;
use App\Features\ContentIntelligence\Models\StrategyMemory;
use App\Features\ContentIntelligence\Models\UserStylePreference;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Models\WorldEntity;
use App\Features\ContentIntelligence\Pipeline\ContentWorkflowEngine;
use App\Features\ContentIntelligence\Services\AgentOrchestratorService;
use App\Features\ContentIntelligence\Services\ArticleBrainService;
use App\Features\ContentIntelligence\Services\AutonomousLearningEngineService;
use App\Features\ContentIntelligence\Services\ContentGenomeService;
use App\Features\ContentIntelligence\Services\ContentLineageService;
use App\Features\ContentIntelligence\Services\ContentRiskEngineService;
use App\Features\ContentIntelligence\Services\DeepEvidenceGraphService;
use App\Features\ContentIntelligence\Services\MicroRepairService;
use App\Features\ContentIntelligence\Services\ModelRouterService;
use App\Features\ContentIntelligence\Services\QualityEngineService;
use App\Features\ContentIntelligence\Services\SiteTopicStrategyService;
use App\Features\ContentIntelligence\Services\TruthLayerService;
use App\Features\ContentIntelligence\Services\UserFeedbackIntelligenceService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.workspace')]
#[Title('Content Intelligence Hub & Pipeline — HelpOfAi Studio')]
class ContentIntelligencePage extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public ?int $selectedRunId = null;

    public string $filterStatus = 'all';

    // Form fields for new mission
    public string $topic = '';

    public string $primaryObjective = '';

    public string $targetAudience = 'Technical Leads & Developers';

    public string $marketGeo = 'Global';

    public string $language = 'en';

    public string $contentType = 'comprehensive_guide';

    public string $businessGoal = 'authority_and_engagement';

    public string $searchGoal = 'organic_search_rank_1';

    public string $audiencePersona = '';

    public string $expertiseLevel = 'Intermediate';

    public string $riskLevel = 'medium';

    public string $researchBudgetTier = 'standard';

    public string $articleArchetype = 'auto_detect';

    public int $minWords = 1800;

    public int $maxWords = 3500;

    public bool $isStepping = false;

    public bool $isAutoRunning = false;

    public string $statusMessage = '';

    public string $errorMessage = '';

    public string $search = '';

    public string $inspectorTab = 'progression';

    public int $modalStep = 1;

    public string $selectedPreset = '';

    // AI Provider & Model Gateway Selection
    public ?int $selectedAiProviderId = null;

    public string $selectedAiModel = 'auto';

    // Cognitive Memory OS Filters & Actions
    public string $memoryFilterLayer = 'all';

    public string $memoryFilterScope = 'all';

    public string $invalidationSubject = '';

    public string $invalidationReason = '';

    public ?int $selectedClaimId = null;

    // Phase 6: Lineage, Learning & Topic Strategy Properties
    public ?int $selectedLineageNodeId = null;

    public string $newStrategyKey = '';

    public string $newStrategyCategory = 'structure';

    public string $newStrategyRule = '';

    public string $testOriginalEdit = '';

    public string $testManualEdit = '';

    public function mount(): void
    {
        try {
            $defaultModel = \Illuminate\Support\Facades\DB::table('ai_models')
                ->where('is_active', 1)
                ->where('is_default', 1)
                ->first();

            if ($defaultModel) {
                $this->selectedAiProviderId = $defaultModel->ai_provider_id;
                $this->selectedAiModel = $defaultModel->model_id;
            } else {
                $firstModel = \Illuminate\Support\Facades\DB::table('ai_models')
                    ->where('is_active', 1)
                    ->first();
                if ($firstModel) {
                    $this->selectedAiProviderId = $firstModel->ai_provider_id;
                    $this->selectedAiModel = $firstModel->model_id;
                }
            }
        } catch (\Throwable $e) {
            // DB fallback
        }
    }

    public function updatedSelectedAiProviderId($providerId): void
    {
        if ($providerId) {
            try {
                $firstModel = \Illuminate\Support\Facades\DB::table('ai_models')
                    ->where('ai_provider_id', $providerId)
                    ->where('is_active', 1)
                    ->first();

                if ($firstModel) {
                    $this->selectedAiModel = $firstModel->model_id;
                }
            } catch (\Throwable $e) {
            }
        }
    }

    public function setInspectorTab(string $tab): void
    {
        $this->inspectorTab = $tab;
    }

    public function selectClaim(?int $claimId): void
    {
        $this->selectedClaimId = $claimId;
    }

    public function setModalStep(int $step): void
    {
        $this->modalStep = max(1, min(4, $step));
    }

    public function applyPreset(string $preset): void
    {
        $this->selectedPreset = $preset;
        match ($preset) {
            'technical_teardown', 'technical_guide' => [
                $this->articleArchetype = 'technical_teardown',
                $this->expertiseLevel = 'Advanced',
                $this->riskLevel = 'high',
                $this->researchBudgetTier = 'deep',
                $this->minWords = 2500,
                $this->maxWords = 4500,
            ],
            'comparative_roundup', 'seo_pillar' => [
                $this->articleArchetype = 'comparative_roundup',
                $this->expertiseLevel = 'Intermediate',
                $this->riskLevel = 'medium',
                $this->researchBudgetTier = 'standard',
                $this->minWords = 2200,
                $this->maxWords = 4000,
            ],
            'step_by_step_tutorial' => [
                $this->articleArchetype = 'step_by_step_tutorial',
                $this->expertiseLevel = 'Intermediate',
                $this->riskLevel = 'medium',
                $this->researchBudgetTier = 'standard',
                $this->minWords = 1800,
                $this->maxWords = 3500,
            ],
            'thought_leadership' => [
                $this->articleArchetype = 'thought_leadership',
                $this->expertiseLevel = 'Expert',
                $this->riskLevel = 'medium',
                $this->researchBudgetTier = 'standard',
                $this->minWords = 1400,
                $this->maxWords = 2600,
            ],
            'executive_strategy', 'executive_brief' => [
                $this->articleArchetype = 'executive_strategy',
                $this->expertiseLevel = 'Expert',
                $this->riskLevel = 'high',
                $this->researchBudgetTier = 'expert',
                $this->minWords = 1600,
                $this->maxWords = 3000,
            ],
            default => [
                $this->articleArchetype = 'auto_detect',
            ],
        };
    }

    public function deleteRun(int $runId): void
    {
        $run = WorkflowRun::where('id', $runId)
            ->where('user_id', Auth::id())
            ->first();

        if ($run) {
            if ($this->selectedRunId === $runId) {
                $this->selectedRunId = null;
            }
            $run->delete();
            $this->statusMessage = "Workflow run #{$runId} removed.";
        }
    }

    protected function rules(): array
    {
        return [
            'topic' => 'required|string|min:3|max:250',
            'primaryObjective' => 'required|string|min:10|max:1000',
            'audiencePersona' => 'nullable|string|max:200',
            'expertiseLevel' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
            'riskLevel' => 'required|string|in:low,medium,high',
            'researchBudgetTier' => 'required|string|in:quick,standard,deep,expert',
            'articleArchetype' => 'required|string|in:auto_detect,comparative_roundup,technical_teardown,step_by_step_tutorial,executive_strategy,thought_leadership',
            'minWords' => 'required|integer|min:300|max:15000',
            'maxWords' => 'required|integer|min:500|max:25000|gte:minWords',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['topic', 'primaryObjective', 'audiencePersona', 'errorMessage', 'statusMessage', 'selectedPreset']);
        $this->modalStep = 1;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createMission(CreateContentMission $action): void
    {
        $this->validate();

        try {
            $user = Auth::user();
            $result = $action->execute($user, [
                'topic' => trim($this->topic),
                'primary_objective' => trim($this->primaryObjective),
                'target_audience' => [
                    'persona' => trim($this->audiencePersona) ?: 'Enterprise Practitioner',
                    'expertise_level' => $this->expertiseLevel,
                ],
                'article_archetype' => $this->articleArchetype,
                'archetype' => $this->articleArchetype,
                'risk_level' => $this->riskLevel,
                'research_budget_tier' => $this->researchBudgetTier,
                'target_word_count_range' => [
                    'min' => $this->minWords,
                    'max' => $this->maxWords,
                ],
                'custom_constraints' => [
                    'ai_provider_id' => $this->selectedAiProviderId,
                    'ai_model' => $this->selectedAiModel,
                ],
            ]);

            $this->showCreateModal = false;
            $this->selectedRunId = $result['run']->id;
            $this->statusMessage = "Content Mission '{$result['mission']->topic}' initialized successfully.";
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to create Content Mission: '.$e->getMessage();
        }
    }

    public function startAutoRun(int $runId): void
    {
        $this->isAutoRunning = true;
        $this->selectedRunId = $runId;
        $this->stepWorkflow($runId);
    }

    public function stopAutoRun(): void
    {
        $this->isAutoRunning = false;
        $this->statusMessage = 'Autonomous pipeline execution paused.';
    }

    public function stepWorkflow(int $runId): void
    {
        $this->isStepping = true;
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            $run = WorkflowRun::where('id', $runId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if ($run->status === ContentWorkflowStatus::COMPLETED) {
                $this->isAutoRunning = false;
                $this->statusMessage = 'Workflow run is already completed. Document is ready in TipTap editor.';
                $this->isStepping = false;

                return;
            }

            $engine = new ContentWorkflowEngine;
            $result = $engine->step($run);
            $freshRun = $run->fresh();

            if ($result['result']->isSuccess()) {
                $nodeName = $result['result']->outputPayload['last_completed_node'] ?? $freshRun->current_node;
                $this->statusMessage = "Stage '{$nodeName}' completed successfully with confidence {$result['result']->confidence}.";

                if ($this->isAutoRunning) {
                    if ($freshRun->status === ContentWorkflowStatus::COMPLETED) {
                        $this->isAutoRunning = false;
                        $this->statusMessage = 'Workflow completed all stages! Publish-ready document assembled in TipTap editor.';
                    } else {
                        // Dispatch client loop so browser receives each step's update and triggers next step cleanly
                        $this->dispatch('trigger-next-ci-step', runId: $runId);
                    }
                }
            } else {
                $this->isAutoRunning = false;
                $this->errorMessage = $freshRun->error_message ?: 'Stage execution encountered an issue.';
            }
        } catch (Exception $e) {
            $this->isAutoRunning = false;
            $this->errorMessage = 'Step execution error: '.$e->getMessage();
        } finally {
            $this->isStepping = false;
        }
    }

    public function runFullWorkflow(int $runId): void
    {
        // Route to the non-blocking step-by-step autonomous runner
        $this->startAutoRun($runId);
    }

    public function selectRun(?int $runId): void
    {
        $this->selectedRunId = $runId;
    }

    public function triggerDecayCheck(): void
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        try {
            $memoryManager = app(MemoryManager::class);
            $result = $memoryManager->evaluateDecay($userId, $this->selectedRunId ? WorkflowRun::find($this->selectedRunId)?->mission_id : null);
            $this->statusMessage = "Cognitive Memory OS Evaluation Complete: {$result->totalChecked} checked, {$result->activeCount} active, {$result->uncertainCount} uncertain, {$result->decayedCount} decayed.";
        } catch (Exception $e) {
            $this->errorMessage = 'Decay check error: '.$e->getMessage();
        }
    }

    public function triggerInvalidation(): void
    {
        if (trim($this->invalidationSubject) === '') {
            $this->errorMessage = 'Please specify a fact subject to invalidate.';

            return;
        }

        try {
            $articleBrain = app(ArticleBrainService::class);
            $result = $articleBrain->invalidateFact($this->invalidationSubject, $this->invalidationReason ?: 'Superseded by updated knowledge');
            $this->statusMessage = "Fact Invalidation Executed: {$result['affectedSentencesCount']} downstream sentences marked stale across {$result['affectedSentencesCount']} elements.";
            $this->invalidationSubject = '';
            $this->invalidationReason = '';
        } catch (Exception $e) {
            $this->errorMessage = 'Invalidation error: '.$e->getMessage();
        }
    }

    public function dispatchWorkerAgent(string $agentRole): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            $orchestrator = app(AgentOrchestratorService::class);
            $router = app(ModelRouterService::class);

            $selectedRun = $this->selectedRunId
                ? WorkflowRun::with('mission')->where('id', $this->selectedRunId)->where('user_id', Auth::id())->first()
                : null;

            $blackboard = $selectedRun
                ? MissionBlackboard::fromRun($selectedRun)
                : new MissionBlackboard(['mission_topic' => 'Production Content Architecture']);

            $taskType = match ($agentRole) {
                'researcher' => TaskType::RESEARCH_SYNTHESIS,
                'analyst' => TaskType::REASONING_ANALYSIS,
                'writer' => TaskType::CREATIVE_WRITING,
                'fact_checker' => TaskType::FACT_CHECKING,
                'critic' => TaskType::CRITIQUE_EVALUATION,
                'seo' => TaskType::SEO_OPTIMIZATION,
                'editor' => TaskType::PROOFREADING_EDITING,
                default => TaskType::RESEARCH_SYNTHESIS,
            };

            $routing = $router->routeTask($taskType);

            $task = new AgentTaskDTO(
                taskId: uniqid('manual_task_'),
                taskType: $taskType,
                instruction: "Execute specialized {$agentRole} evaluation for content mission.",
                modelOverride: $routing->selectedModelId
            );

            $result = $orchestrator->dispatch(
                role: $agentRole,
                task: $task,
                blackboard: $blackboard,
                missionId: $selectedRun?->mission_id,
                workflowRunId: $selectedRun?->id
            );

            if ($selectedRun) {
                $blackboard->saveToRun($selectedRun);
            }

            $this->statusMessage = $result->summary ?: "Agent '{$agentRole}' executed successfully.";
        } catch (Exception $e) {
            $this->errorMessage = 'Agent execution error: '.$e->getMessage();
        }
    }

    public function runQualityAudit(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        if (! $this->selectedRunId) {
            $this->errorMessage = 'Please select a workflow run to audit.';

            return;
        }

        try {
            $selectedRun = WorkflowRun::with('mission')->where('id', $this->selectedRunId)->where('user_id', Auth::id())->firstOrFail();
            $audit = app(QualityEngineService::class)->auditContentHealth($selectedRun);

            $this->statusMessage = "Content Health audit completed: Grade {$audit->grade} ({$audit->overall_score}/100 across 15 dimensions).";
        } catch (Exception $e) {
            $this->errorMessage = 'Quality audit error: '.$e->getMessage();
        }
    }

    public function triggerMicroRepair(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        if (! $this->selectedRunId) {
            $this->errorMessage = 'Please select a workflow run to perform micro-repairs on.';

            return;
        }

        try {
            $selectedRun = WorkflowRun::with('mission')->where('id', $this->selectedRunId)->where('user_id', Auth::id())->firstOrFail();
            $repairService = app(MicroRepairService::class);
            $content = $selectedRun->getGraphStateValue('document_content', '') ?: ($selectedRun->mission->target_topic ?? 'Enterprise Application Architecture');

            $problems = $repairService->detectProblems($content);

            if (empty($problems)) {
                $targetSentence = 'In today\'s fast-paced world, it is crucial to remember that this architecture is guaranteed 100% and a testament to modern engineering.';
                $repair = $repairService->repairSmallestUnit(
                    run: $selectedRun,
                    fullContent: $targetSentence,
                    targetUnit: $targetSentence,
                    unitType: RepairUnitType::SENTENCE,
                    category: ProblemCategory::REPETITION,
                    rootCause: 'Stereotypical AI filler and unverified absolute claim detected.',
                    unitPointer: 'sec-demo-s-1'
                );
                $this->statusMessage = "Surgical Micro-Repair executed at Sentence level: {$repair->diff_summary}";
            } else {
                $prob = $problems[0];
                $repair = $repairService->repairSmallestUnit(
                    run: $selectedRun,
                    fullContent: $content,
                    targetUnit: $prob['text'],
                    unitType: $prob['unit_type'],
                    category: $prob['category'],
                    rootCause: $prob['root_cause'],
                    unitPointer: $prob['unit_pointer']
                );
                $this->statusMessage = "Surgical Micro-Repair completed for '{$prob['category']->label()}' at {$prob['unit_type']->label()}.";
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Micro-Repair error: '.$e->getMessage();
        }
    }

    public function approveRiskGate(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        if (! $this->selectedRunId) {
            $this->errorMessage = 'Please select a workflow run.';

            return;
        }

        try {
            $selectedRun = WorkflowRun::where('id', $this->selectedRunId)->where('user_id', Auth::id())->firstOrFail();
            app(ContentRiskEngineService::class)->approveByHuman($selectedRun, Auth::id());

            $this->statusMessage = 'Human approval granted. Risk gating cleared successfully.';
        } catch (Exception $e) {
            $this->errorMessage = 'Risk approval error: '.$e->getMessage();
        }
    }

    public function synthesizeContentGenome(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        if (! $this->selectedRunId) {
            $this->errorMessage = 'Please select a workflow run.';

            return;
        }

        try {
            $selectedRun = WorkflowRun::with('mission')->where('id', $this->selectedRunId)->where('user_id', Auth::id())->firstOrFail();
            $genome = app(ContentGenomeService::class)->synthesizeGenome($selectedRun);

            $this->statusMessage = 'Content Genome synthesized successfully! Signature: '.substr($genome->genome_signature, 0, 12).'...';
        } catch (Exception $e) {
            $this->errorMessage = 'Content Genome error: '.$e->getMessage();
        }
    }

    // Phase 6 Actions: Lineage, Learning & Portfolio
    public function selectLineageNode(?int $nodeId): void
    {
        $this->selectedLineageNodeId = $nodeId;
    }

    public function invalidateLineageSource(int $sourceId, string $reason = 'Source facts updated'): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            $impact = app(ContentLineageService::class)->invalidateSourceFact($sourceId, $reason);
            $this->statusMessage = "Source #{$sourceId} invalidated. {$impact->affectedSentencesCount} sentence(s) flagged for repair.";
        } catch (Exception $e) {
            $this->errorMessage = 'Lineage invalidation failed: '.$e->getMessage();
        }
    }

    public function resolveStaleLineageNode(int $nodeId, string $newText): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            app(ContentLineageService::class)->resolveStaleNode($nodeId, $newText);
            $this->statusMessage = "Lineage node #{$nodeId} repaired and marked fresh.";
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to repair lineage node: '.$e->getMessage();
        }
    }

    public function recordStrategyObservation(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        if (empty(trim($this->newStrategyKey)) || empty(trim($this->newStrategyRule))) {
            $this->errorMessage = 'Strategy Key and Rule are required.';

            return;
        }

        try {
            $strategy = app(AutonomousLearningEngineService::class)->recordObservation(
                Auth::id(),
                trim($this->newStrategyKey),
                $this->newStrategyCategory,
                ['rule' => trim($this->newStrategyRule)],
                0.45
            );
            $this->reset(['newStrategyKey', 'newStrategyRule']);
            $this->statusMessage = "Strategic observation '{$strategy->strategy_key}' registered (Status: {$strategy->status->value}).";
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to record strategy: '.$e->getMessage();
        }
    }

    public function adoptStrategyCandidate(int $strategyId): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            $strategy = app(AutonomousLearningEngineService::class)->adoptStrategy($strategyId);
            $this->statusMessage = "Strategy '{$strategy->strategy_key}' successfully adopted!";
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to adopt strategy: '.$e->getMessage();
        }
    }

    public function rejectStrategyCandidate(int $strategyId): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            $strategy = app(AutonomousLearningEngineService::class)->rejectStrategy($strategyId, 'Disapproved via Content Intelligence Center');
            $this->statusMessage = "Strategy '{$strategy->strategy_key}' marked rejected.";
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to reject strategy: '.$e->getMessage();
        }
    }

    public function analyzeUserEditDiff(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        if (empty(trim($this->testOriginalEdit)) || empty(trim($this->testManualEdit))) {
            $this->errorMessage = 'Both original and edited texts are required.';

            return;
        }

        try {
            $pref = app(UserFeedbackIntelligenceService::class)->analyzeDiffAndRecordPreference(
                Auth::id(),
                $this->testOriginalEdit,
                $this->testManualEdit
            );
            if ($pref) {
                $this->statusMessage = "Learned preference '{$pref->preference_key}' updated (Confidence: {$pref->confidence}).";
                $this->reset(['testOriginalEdit', 'testManualEdit']);
            } else {
                $this->statusMessage = 'Edit analyzed; difference within normal stylistic variance.';
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Feedback analysis failed: '.$e->getMessage();
        }
    }

    public function toggleStylePreference(int $prefId, bool $isActive): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            app(UserFeedbackIntelligenceService::class)->togglePreference($prefId, $isActive);
            $this->statusMessage = 'Style preference status updated.';
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to toggle preference: '.$e->getMessage();
        }
    }

    public function refreshTopicPortfolio(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            app(SiteTopicStrategyService::class)->analyzeTopicClusters(Auth::id());
            $this->statusMessage = 'Site Topic Portfolio analyzed & refreshed.';
        } catch (Exception $e) {
            $this->errorMessage = 'Portfolio refresh failed: '.$e->getMessage();
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $runsQuery = WorkflowRun::with(['mission', 'document', 'nodes'])
            ->where('user_id', $userId)
            ->latest();

        if ($this->filterStatus !== 'all') {
            $runsQuery->where('status', $this->filterStatus);
        }

        if (trim($this->search) !== '') {
            $searchTerm = trim($this->search);
            $runsQuery->whereHas('mission', function ($q) use ($searchTerm) {
                $q->where('topic', 'like', "%{$searchTerm}%")
                    ->orWhere('primary_objective', 'like', "%{$searchTerm}%");
            });
        }

        $runs = $runsQuery->paginate(10);

        $selectedRun = $this->selectedRunId
            ? WorkflowRun::with([
                'mission.claims.source',
                'mission.knowledgeTriples',
                'mission.blueprint.outline',
                'document.content',
                'nodes',
                'drafts',
                'seoMetadata',
            ])
                ->where('id', $this->selectedRunId)
                ->where('user_id', $userId)
                ->first()
            : null;

        $stats = [
            'total_missions' => ContentMission::where('user_id', $userId)->count(),
            'active_runs' => WorkflowRun::where('user_id', $userId)->whereIn('status', [ContentWorkflowStatus::QUEUED, ContentWorkflowStatus::RUNNING])->count(),
            'completed_runs' => WorkflowRun::where('user_id', $userId)->where('status', ContentWorkflowStatus::COMPLETED)->count(),
            'average_confidence' => round((float) WorkflowRun::where('user_id', $userId)->avg('overall_confidence') * 100, 1),
            'total_memories' => BrainMemory::where('user_id', $userId)->count(),
            'active_memories' => BrainMemory::where('user_id', $userId)->where('status', 'active')->count(),
        ];

        // Memory OS Queries
        $memoriesQuery = BrainMemory::where('user_id', $userId)->latest();
        if ($this->memoryFilterLayer !== 'all') {
            $memoriesQuery->where('layer', $this->memoryFilterLayer);
        }
        if ($this->memoryFilterScope !== 'all') {
            $memoriesQuery->where('scope', $this->memoryFilterScope);
        }
        $memories = $memoriesQuery->limit(30)->get();

        $memoryCandidates = MemoryCandidate::where('user_id', $userId)->latest()->limit(15)->get();
        $episodicEvents = EpisodicEvent::where('user_id', $userId)->latest()->limit(15)->get();
        $staleElementsCount = ArticleElementNode::where('is_stale', true)->count();

        // Phase 3: World Model & Truth Layer Queries
        $truthAudit = null;
        $claimLineage = null;
        $claimConsensus = null;
        if ($selectedRun && $selectedRun->mission) {
            try {
                $truthAudit = app(TruthLayerService::class)->auditMission($selectedRun->mission);
                $claimId = $this->selectedClaimId ?: $selectedRun->mission->claims->first()?->id;
                if ($claimId) {
                    $claimLineage = app(DeepEvidenceGraphService::class)->getDeepLineage($claimId);
                    $claimConsensus = app(DeepEvidenceGraphService::class)->calculateConsensus($claimId);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("TruthLayer render error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            }
        }
        $worldEntities = WorldEntity::query()->limit(25)->get();
        $evidenceSnippets = ($selectedRun && $selectedRun->mission_id)
            ? EvidenceSnippet::with('source')->where('mission_id', $selectedRun->mission_id)->limit(15)->get()
            : collect();

        // Phase 4: Agent Orchestrator, Blackboard & Model Router Queries
        $orchestratorAgents = app(AgentOrchestratorService::class)->getAllAgents();
        $routingMatrix = app(ModelRouterService::class)->getRoutingMatrix();
        $blackboard = $selectedRun ? MissionBlackboard::fromRun($selectedRun)->toArray() : [];
        $agentActivities = $selectedRun
            ? AgentActivity::where('workflow_run_id', $selectedRun->id)->latest()->limit(15)->get()
            : collect();
        $brainDecisions = $selectedRun
            ? BrainDecision::where('workflow_run_id', $selectedRun->id)->latest()->limit(10)->get()
            : collect();

        // Phase 5: Surgical Micro-Repairs, Quality Audits, Risk Assessments & Content Genomes
        $microRepairs = $selectedRun
            ? MicroRepair::where('workflow_run_id', $selectedRun->id)->latest()->get()
            : collect();
        $qualityAudit = $selectedRun
            ? QualityHealthAudit::where('workflow_run_id', $selectedRun->id)->first()
            : null;
        $riskAssessment = $selectedRun
            ? RiskAssessment::where('workflow_run_id', $selectedRun->id)->first()
            : null;
        $contentGenomes = ContentGenome::where('user_id', $userId)->latest()->limit(5)->get();

        // Phase 6: Content Lineage, Autonomous Learning, Style Preferences & Topic Strategy
        $lineageNodes = ($selectedRun && $selectedRun->document_id)
            ? ContentLineageNode::with(['source', 'claim', 'evidenceSnippet'])
                ->where('document_id', $selectedRun->document_id)
                ->orderBy('section_index')
                ->orderBy('sentence_index')
                ->limit(20)
                ->get()
            : collect();

        $selectedLineageTrace = null;
        if ($this->selectedLineageNodeId) {
            $selectedLineageTrace = app(ContentLineageService::class)->traceSentenceLineage($this->selectedLineageNodeId);
        } elseif ($lineageNodes->isNotEmpty()) {
            $selectedLineageTrace = app(ContentLineageService::class)->traceSentenceLineage($lineageNodes->first()->id);
        }

        $strategyMemories = StrategyMemory::where('user_id', $userId)->latest()->limit(15)->get();
        $stylePreferences = UserStylePreference::where('user_id', $userId)->latest()->limit(10)->get();
        $topicClusters = SiteTopicCluster::where('user_id', $userId)->latest()->limit(10)->get();
        $portfolioReport = app(SiteTopicStrategyService::class)->generatePortfolioReport($userId);

        $aiProviders = \Illuminate\Support\Facades\DB::table('ai_providers')->where('is_active', 1)->get();
        $aiModels = \Illuminate\Support\Facades\DB::table('ai_models')->where('is_active', 1)->get();

        return view('content-intelligence.index', [
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'stats' => $stats,
            'aiProviders' => $aiProviders,
            'aiModels' => $aiModels,
            'memories' => $memories,
            'memoryCandidates' => $memoryCandidates,
            'episodicEvents' => $episodicEvents,
            'staleElementsCount' => $staleElementsCount,
            'truthAudit' => $truthAudit,
            'claimLineage' => $claimLineage,
            'claimConsensus' => $claimConsensus,
            'worldEntities' => $worldEntities,
            'evidenceSnippets' => $evidenceSnippets,
            'orchestratorAgents' => $orchestratorAgents,
            'routingMatrix' => $routingMatrix,
            'blackboard' => $blackboard,
            'agentActivities' => $agentActivities,
            'brainDecisions' => $brainDecisions,
            'microRepairs' => $microRepairs,
            'qualityAudit' => $qualityAudit,
            'riskAssessment' => $riskAssessment,
            'contentGenomes' => $contentGenomes,
            'lineageNodes' => $lineageNodes,
            'selectedLineageTrace' => $selectedLineageTrace,
            'strategyMemories' => $strategyMemories,
            'stylePreferences' => $stylePreferences,
            'topicClusters' => $topicClusters,
            'portfolioReport' => $portfolioReport,
        ]);
    }
}
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
use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Pipeline\ContentWorkflowEngine;
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

    public string $audiencePersona = '';

    public string $expertiseLevel = 'Intermediate';

    public string $riskLevel = 'medium';

    public string $researchBudgetTier = 'standard';

    public int $minWords = 1800;

    public int $maxWords = 3500;

    public bool $isStepping = false;

    public string $statusMessage = '';

    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'topic' => 'required|string|min:3|max:250',
            'primaryObjective' => 'required|string|min:10|max:1000',
            'audiencePersona' => 'nullable|string|max:200',
            'expertiseLevel' => 'required|string|in:Beginner,Intermediate,Advanced,Expert',
            'riskLevel' => 'required|string|in:low,medium,high',
            'researchBudgetTier' => 'required|string|in:quick,standard,deep,expert',
            'minWords' => 'required|integer|min:300|max:15000',
            'maxWords' => 'required|integer|min:500|max:25000|gte:minWords',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['topic', 'primaryObjective', 'audiencePersona', 'errorMessage', 'statusMessage']);
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
                'risk_level' => $this->riskLevel,
                'research_budget_tier' => $this->researchBudgetTier,
                'target_word_count_range' => [
                    'min' => $this->minWords,
                    'max' => $this->maxWords,
                ],
            ]);

            $this->showCreateModal = false;
            $this->selectedRunId = $result['run']->id;
            $this->statusMessage = "Content Mission '{$result['mission']->topic}' initialized successfully.";
        } catch (Exception $e) {
            $this->errorMessage = 'Failed to create Content Mission: '.$e->getMessage();
        }
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
                $this->statusMessage = 'Workflow run is already completed.';
                $this->isStepping = false;

                return;
            }

            $engine = new ContentWorkflowEngine;
            $result = $engine->step($run);

            if ($result['result']->isSuccess()) {
                $nodeName = $result['result']->outputPayload['last_completed_node'] ?? $run->fresh()->current_node;
                $this->statusMessage = "Stage '{$nodeName}' completed successfully with confidence {$result['result']->confidence}.";
            } else {
                $this->errorMessage = $run->fresh()->error_message ?: 'Stage execution encountered an issue.';
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Step execution error: '.$e->getMessage();
        } finally {
            $this->isStepping = false;
        }
    }

    public function runFullWorkflow(int $runId): void
    {
        $this->isStepping = true;
        $this->errorMessage = '';
        $this->statusMessage = '';

        try {
            $run = WorkflowRun::where('id', $runId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $engine = new ContentWorkflowEngine;
            $maxIterations = 15;
            $iterations = 0;

            while ($run->status !== ContentWorkflowStatus::COMPLETED && $run->status !== ContentWorkflowStatus::FAILED && $iterations < $maxIterations) {
                $iterations++;
                $stepResult = $engine->step($run);
                $run = $stepResult['run']->fresh();

                if (! $stepResult['result']->isSuccess() && ! $stepResult['result']->isLoop()) {
                    break;
                }
            }

            if ($run->status === ContentWorkflowStatus::COMPLETED) {
                $this->statusMessage = 'Workflow completed all stages! Publish-ready document assembled in TipTap editor.';
            } elseif ($run->status === ContentWorkflowStatus::FAILED) {
                $this->errorMessage = 'Workflow halted on error: '.($run->error_message ?: 'Unknown failure');
            } else {
                $this->statusMessage = "Executed {$iterations} workflow stages. Current node: {$run->current_node}.";
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Execution error: '.$e->getMessage();
        } finally {
            $this->isStepping = false;
        }
    }

    public function selectRun(?int $runId): void
    {
        $this->selectedRunId = $runId;
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

        $runs = $runsQuery->paginate(10);

        $selectedRun = $this->selectedRunId
            ? WorkflowRun::with(['mission', 'document', 'nodes'])
                ->where('id', $this->selectedRunId)
                ->where('user_id', $userId)
                ->first()
            : null;

        $stats = [
            'total_missions' => ContentMission::where('user_id', $userId)->count(),
            'active_runs' => WorkflowRun::where('user_id', $userId)->whereIn('status', [ContentWorkflowStatus::QUEUED, ContentWorkflowStatus::RUNNING])->count(),
            'completed_runs' => WorkflowRun::where('user_id', $userId)->where('status', ContentWorkflowStatus::COMPLETED)->count(),
            'average_confidence' => round((float) WorkflowRun::where('user_id', $userId)->avg('overall_confidence') * 100, 1),
        ];

        return view('content-intelligence.index', [
            'runs' => $runs,
            'selectedRun' => $selectedRun,
            'stats' => $stats,
        ]);
    }
}

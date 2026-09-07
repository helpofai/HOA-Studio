<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Agent Orchestrator Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\Agents\AnalystAgent;
use App\Features\ContentIntelligence\Agents\CriticAgent;
use App\Features\ContentIntelligence\Agents\EditorAgent;
use App\Features\ContentIntelligence\Agents\FactCheckerAgent;
use App\Features\ContentIntelligence\Agents\ResearcherAgent;
use App\Features\ContentIntelligence\Agents\SeoAgent;
use App\Features\ContentIntelligence\Agents\WriterAgent;
use App\Features\ContentIntelligence\Contracts\AgentInterface;
use App\Features\ContentIntelligence\Contracts\BlackboardInterface;
use App\Features\ContentIntelligence\DTOs\AgentResultDTO;
use App\Features\ContentIntelligence\DTOs\AgentTaskDTO;
use App\Features\ContentIntelligence\Enums\AgentRole;
use App\Features\ContentIntelligence\Models\AgentActivity;
use Illuminate\Support\Str;

class AgentOrchestratorService
{
    /**
     * @var array<string, AgentInterface>
     */
    protected array $agents = [];

    public function __construct()
    {
        $this->registerDefaultAgents();
    }

    protected function registerDefaultAgents(): void
    {
        $defaultAgents = [
            new ResearcherAgent,
            new AnalystAgent,
            new WriterAgent,
            new FactCheckerAgent,
            new CriticAgent,
            new SeoAgent,
            new EditorAgent,
        ];

        foreach ($defaultAgents as $agent) {
            $this->agents[$agent->getName()] = $agent;
        }
    }

    public function registerAgent(AgentInterface $agent): void
    {
        $this->agents[$agent->getName()] = $agent;
    }

    public function getAgent(string|AgentRole $role): ?AgentInterface
    {
        $name = $role instanceof AgentRole ? $role->value : strtolower($role);

        return $this->agents[$name] ?? null;
    }

    /**
     * @return array<string, AgentInterface>
     */
    public function getAllAgents(): array
    {
        return $this->agents;
    }

    /**
     * Dispatch a task to a specialized worker agent and record execution telemetry.
     */
    public function dispatch(
        string|AgentRole $role,
        AgentTaskDTO $task,
        BlackboardInterface $blackboard,
        ?int $missionId = null,
        ?int $workflowRunId = null
    ): AgentResultDTO {
        $agent = $this->getAgent($role);

        if (! $agent) {
            $agentName = $role instanceof AgentRole ? $role->value : (string) $role;

            return AgentResultDTO::failure(
                agentName: $agentName,
                taskType: $task->taskType,
                errorMessage: "No registered worker agent found for role '{$agentName}'."
            );
        }

        try {
            $result = $agent->execute($task, $blackboard);

            // Persist agent activity telemetry to database
            try {
                AgentActivity::create([
                    'id' => 'act_'.Str::lower(Str::random(12)),
                    'mission_id' => $missionId,
                    'workflow_run_id' => $workflowRunId,
                    'agent_name' => $agent->getName(),
                    'task_type' => $task->taskType->value,
                    'model_used' => $result->modelUsed,
                    'tokens_used' => $result->tokensUsed,
                    'latency_ms' => $result->latencyMs,
                    'status' => $result->isSuccessful ? 'completed' : 'failed',
                    'input_payload' => $task->contextPayload,
                    'output_summary' => $result->summary,
                ]);
            } catch (\Throwable) {
                // Defensive fallback for transient database or schema states
            }

            return $result;
        } catch (\Throwable $e) {
            return AgentResultDTO::failure(
                agentName: $agent->getName(),
                taskType: $task->taskType,
                errorMessage: $e->getMessage()
            );
        }
    }
}

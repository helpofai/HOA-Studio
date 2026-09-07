<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Workflow Engine
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

namespace App\Features\ContentIntelligence\Pipeline;

use App\Features\ContentIntelligence\Contracts\WorkflowNodeInterface;
use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Models\AgentActivity;
use App\Features\ContentIntelligence\Models\BrainDecision;
use App\Features\ContentIntelligence\Models\WorkflowNodeRecord;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Pipeline\Nodes\AssemblyNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\BlueprintNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\KnowledgeNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\MediaEnhancerNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\MissionNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\OutlineNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\ResearchDirectorNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\SearchIntelNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\SectionWriterNode;
use App\Features\ContentIntelligence\Pipeline\Nodes\SeoOptimizerNode;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ContentWorkflowEngine
{
    /**
     * Registered workflow nodes keyed by node name.
     *
     * @var array<string, WorkflowNodeInterface>
     */
    protected array $nodes = [];

    public function __construct()
    {
        // Register standard default graph nodes
        $this->registerNode(new MissionNode);
        $this->registerNode(new SearchIntelNode);
        $this->registerNode(new ResearchDirectorNode);
        $this->registerNode(new KnowledgeNode);
        $this->registerNode(new BlueprintNode);
        $this->registerNode(new OutlineNode);
        $this->registerNode(new SectionWriterNode);
        $this->registerNode(new SeoOptimizerNode);
        $this->registerNode(new MediaEnhancerNode);
        $this->registerNode(new AssemblyNode);
    }

    /**
     * Register a node into the workflow graph.
     */
    public function registerNode(WorkflowNodeInterface $node): self
    {
        $this->nodes[$node->getName()] = $node;

        return $this;
    }

    /**
     * Check if a node is registered.
     */
    public function hasNode(string $name): bool
    {
        return isset($this->nodes[$name]);
    }

    /**
     * Get a registered node instance.
     */
    public function getNode(string $name): WorkflowNodeInterface
    {
        if (! isset($this->nodes[$name])) {
            throw new InvalidArgumentException("Workflow node '{$name}' is not registered in the graph.");
        }

        return $this->nodes[$name];
    }

    /**
     * Execute the active/current node for the given workflow run.
     *
     * @return array{run: WorkflowRun, result: WorkflowNodeResultDTO}
     */
    public function step(WorkflowRun $run): array
    {
        $nodeName = $run->current_node;
        $node = $this->getNode($nodeName);

        $run->status = ContentWorkflowStatus::RUNNING;
        $run->save();

        $context = $run->graph_state ?? [];
        $startTime = microtime(true);
        $startedAt = now();

        $nodeRecord = WorkflowNodeRecord::create([
            'workflow_run_id' => $run->id,
            'node_name' => $nodeName,
            'input_payload' => $context,
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        try {
            $result = $node->execute($run, $context);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            // Record node execution history
            $nodeRecord->update([
                'output_payload' => $result->outputPayload,
                'status' => $result->status,
                'confidence' => $result->confidence,
                'latency_ms' => $latencyMs,
                'token_count' => (int) ($result->metrics['tokens'] ?? 0),
                'completed_at' => now(),
            ]);

            // Update cumulative workflow state
            $graphState = $run->graph_state ?? [];
            $graphState[$nodeName] = $result->outputPayload;
            $graphState['last_completed_node'] = $nodeName;
            $graphState['history'][] = [
                'node' => $nodeName,
                'status' => $result->status,
                'completed_at' => now()->toIso8601String(),
                'confidence' => $result->confidence,
            ];

            $run->graph_state = $graphState;
            $run->overall_confidence = min($run->overall_confidence, $result->confidence);

            // ══════════════════════════════════════════════════════════════
            // PHASE 4 TELEMETRY: Log Agent Activity & Brain Decision
            // ══════════════════════════════════════════════════════════════
            try {
                AgentActivity::create([
                    'id' => Str::orderedUuid()->toString(),
                    'mission_id' => $run->mission_id,
                    'workflow_run_id' => $run->id,
                    'agent_name' => $nodeName,
                    'task_type' => $node->getName(),
                    'model_used' => 'dynamic_ai',
                    'tokens_used' => (int) ($result->metrics['tokens'] ?? 0),
                    'latency_ms' => $latencyMs,
                    'status' => $result->status === 'success' ? 'completed' : 'failed',
                    'input_payload' => $context,
                    'output_summary' => json_encode($result->outputPayload),
                ]);
                BrainDecision::create([
                    'id' => Str::orderedUuid()->toString(),
                    'mission_id' => $run->mission_id,
                    'workflow_run_id' => $run->id,
                    'question' => "Execute node: {$node->getName()}",
                    'decision' => $result->status,
                    'reasoning' => $node->getDescription(),
                    'inputs' => $context,
                    'confidence' => $result->confidence,
                ]);
            } catch (Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("WorkflowEngine telemetry logging failed: " . $e->getMessage());
            }

            if ($result->isSuccess()) {
                if ($result->nextSuggestedNode) {
                    $run->current_node = $result->nextSuggestedNode;
                    $run->status = ContentWorkflowStatus::RUNNING;
                } else {
                    $run->status = ContentWorkflowStatus::COMPLETED;
                    $run->completed_at = now();
                }
            } elseif ($result->isLoop()) {
                $run->current_node = $result->nextSuggestedNode ?? $nodeName;
                $run->status = ContentWorkflowStatus::RUNNING;
            } else {
                $run->status = ContentWorkflowStatus::FAILED;
                $run->error_message = (string) ($result->outputPayload['error'] ?? 'Node execution failed.');
            }

            $run->save();

            return [
                'run' => $run,
                'result' => $result,
            ];
        } catch (Throwable $e) {
            Log::error("Workflow node {$nodeName} exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            $nodeRecord->update([
                'status' => 'failed',
                'latency_ms' => $latencyMs,
                'error_log' => $e->getMessage()."\n".$e->getTraceAsString(),
                'completed_at' => now(),
            ]);

            $run->status = ContentWorkflowStatus::FAILED;
            $run->error_message = $e->getMessage();
            $run->save();

            return [
                'run' => $run,
                'result' => WorkflowNodeResultDTO::failed($e->getMessage()),
            ];
        }
    }
}

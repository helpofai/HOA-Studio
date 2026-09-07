<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Model Router Service
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

use App\Features\AI\Models\AiModel;
use App\Features\ContentIntelligence\DTOs\ModelRoutingDecisionDTO;
use App\Features\ContentIntelligence\Enums\TaskType;

class ModelRouterService
{
    /**
     * Determine the optimal model for a given task type and execution criteria.
     *
     * @param  array<string, mixed>  $options
     */
    public function routeTask(TaskType $taskType, array $options = []): ModelRoutingDecisionDTO
    {
        // 1. Direct Model Override if provided by user or task configuration
        if (! empty($options['model_override'])) {
            $override = (string) $options['model_override'];

            return new ModelRoutingDecisionDTO(
                taskType: $taskType,
                selectedModelId: $override,
                providerFamily: $this->detectProviderFamily($override),
                rationale: "Explicit task model override specified ('{$override}').",
                qualityTier: $taskType->defaultQualityTier(),
                isReasoningModel: $this->isReasoningModel($override),
                estimatedLatencyMs: 800,
                candidateModelsConsidered: [$override]
            );
        }

        $preferredCandidates = $taskType->defaultPreferredModels();
        $qualityTier = $taskType->defaultQualityTier();

        // 2. Query configured active AI models in database if available
        $selectedModel = null;
        try {
            foreach ($preferredCandidates as $candidate) {
                $dbModel = AiModel::where('is_active', true)
                    ->where(function ($q) use ($candidate) {
                        $q->where('model_id', $candidate)->orWhere('name', 'like', "%{$candidate}%");
                    })
                    ->first();

                if ($dbModel) {
                    $selectedModel = $dbModel->model_id;
                    break;
                }
            }
        } catch (\Throwable) {
            // Fallback gracefully to default top candidate
        }

        $selectedModel = $selectedModel ?: $preferredCandidates[0];
        $isReasoning = $this->isReasoningModel($selectedModel);
        $providerFamily = $this->detectProviderFamily($selectedModel);

        $rationale = match ($taskType) {
            TaskType::CLASSIFICATION, TaskType::KEYWORD_ANALYSIS => "Selected lightweight, low-latency model '{$selectedModel}' for sub-second classification.",
            TaskType::RESEARCH_SYNTHESIS, TaskType::SEO_OPTIMIZATION => "Selected balanced model '{$selectedModel}' for high-throughput information extraction.",
            TaskType::REASONING_ANALYSIS, TaskType::FACT_CHECKING => "Routed to specialized reasoning model '{$selectedModel}' for rigorous logical verification and anti-hallucination checks.",
            TaskType::CREATIVE_WRITING => "Selected flagship narrative model '{$selectedModel}' for maximum prose nuance and cadence.",
            TaskType::CRITIQUE_EVALUATION => "Selected high-accuracy evaluator '{$selectedModel}' for unbiased 6-rubric scoring.",
            TaskType::PROOFREADING_EDITING => "Selected fast-response editor '{$selectedModel}' for cadence polishing and grammar refinement.",
        };

        $latency = match ($qualityTier) {
            'fast' => 350,
            'balanced' => 700,
            'reasoning' => 1500,
            'high_accuracy' => 1200,
            default => 800,
        };

        return new ModelRoutingDecisionDTO(
            taskType: $taskType,
            selectedModelId: $selectedModel,
            providerFamily: $providerFamily,
            rationale: $rationale,
            qualityTier: $qualityTier,
            isReasoningModel: $isReasoning,
            estimatedLatencyMs: $latency,
            candidateModelsConsidered: $preferredCandidates
        );
    }

    /**
     * Return the complete dynamic model routing matrix for inspector UI display.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getRoutingMatrix(): array
    {
        $matrix = [];
        foreach (TaskType::cases() as $type) {
            $decision = $this->routeTask($type);
            $matrix[$type->value] = [
                'task_name' => ucwords(str_replace('_', ' ', $type->value)),
                'quality_tier' => $decision->qualityTier,
                'model_id' => $decision->selectedModelId,
                'provider_family' => $decision->providerFamily,
                'is_reasoning' => $decision->isReasoningModel,
                'estimated_latency_ms' => $decision->estimatedLatencyMs,
                'rationale' => $decision->rationale,
            ];
        }

        return $matrix;
    }

    protected function detectProviderFamily(string $modelId): string
    {
        $lower = strtolower($modelId);
        if (str_contains($lower, 'gpt') || str_contains($lower, 'o1') || str_contains($lower, 'o3')) {
            return 'openai';
        }
        if (str_contains($lower, 'claude')) {
            return 'anthropic';
        }
        if (str_contains($lower, 'gemini')) {
            return 'google';
        }
        if (str_contains($lower, 'deepseek')) {
            return 'deepseek';
        }

        return 'omniroute';
    }

    protected function isReasoningModel(string $modelId): bool
    {
        $lower = strtolower($modelId);

        return str_contains($lower, 'o1')
            || str_contains($lower, 'o3')
            || str_contains($lower, 'reasoner')
            || str_contains($lower, 'claude-3-7-sonnet');
    }
}

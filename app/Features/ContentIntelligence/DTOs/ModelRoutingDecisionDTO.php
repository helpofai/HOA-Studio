<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Model Routing Decision DTO
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

namespace App\Features\ContentIntelligence\DTOs;

use App\Features\ContentIntelligence\Enums\TaskType;

final class ModelRoutingDecisionDTO
{
    public function __construct(
        public readonly TaskType $taskType,
        public readonly string $selectedModelId,
        public readonly string $providerFamily,
        public readonly string $rationale,
        public readonly string $qualityTier,
        public readonly bool $isReasoningModel = false,
        public readonly int $estimatedLatencyMs = 800,
        public readonly array $candidateModelsConsidered = []
    ) {}

    public static function fromArray(array $data): self
    {
        $taskType = isset($data['task_type']) && $data['task_type'] instanceof TaskType
            ? $data['task_type']
            : TaskType::tryFrom($data['task_type'] ?? '') ?? TaskType::RESEARCH_SYNTHESIS;

        return new self(
            taskType: $taskType,
            selectedModelId: (string) ($data['selected_model_id'] ?? 'gpt-4o-mini'),
            providerFamily: (string) ($data['provider_family'] ?? 'openai'),
            rationale: (string) ($data['rationale'] ?? 'Default multi-model routing selection'),
            qualityTier: (string) ($data['quality_tier'] ?? 'balanced'),
            isReasoningModel: (bool) ($data['is_reasoning_model'] ?? false),
            estimatedLatencyMs: (int) ($data['estimated_latency_ms'] ?? 800),
            candidateModelsConsidered: (array) ($data['candidate_models_considered'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'task_type' => $this->taskType->value,
            'selected_model_id' => $this->selectedModelId,
            'provider_family' => $this->providerFamily,
            'rationale' => $this->rationale,
            'quality_tier' => $this->qualityTier,
            'is_reasoning_model' => $this->isReasoningModel,
            'estimated_latency_ms' => $this->estimatedLatencyMs,
            'candidate_models_considered' => $this->candidateModelsConsidered,
        ];
    }
}

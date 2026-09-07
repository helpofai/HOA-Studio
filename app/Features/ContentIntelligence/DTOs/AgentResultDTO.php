<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Agent Result DTO
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

final class AgentResultDTO
{
    public function __construct(
        public readonly string $agentName,
        public readonly TaskType $taskType,
        public readonly bool $isSuccessful,
        public readonly array $outputData = [],
        public readonly ?string $summary = null,
        public readonly ?string $modelUsed = null,
        public readonly int $tokensUsed = 0,
        public readonly int $latencyMs = 0,
        public readonly float $confidence = 0.90
    ) {}

    public static function success(
        string $agentName,
        TaskType $taskType,
        array $outputData = [],
        ?string $summary = null,
        ?string $modelUsed = null,
        int $tokensUsed = 0,
        int $latencyMs = 0,
        float $confidence = 0.95
    ): self {
        return new self(
            agentName: $agentName,
            taskType: $taskType,
            isSuccessful: true,
            outputData: $outputData,
            summary: $summary,
            modelUsed: $modelUsed,
            tokensUsed: $tokensUsed,
            latencyMs: $latencyMs,
            confidence: $confidence
        );
    }

    public static function failure(
        string $agentName,
        TaskType $taskType,
        string $errorMessage,
        ?string $modelUsed = null
    ): self {
        return new self(
            agentName: $agentName,
            taskType: $taskType,
            isSuccessful: false,
            outputData: ['error' => $errorMessage],
            summary: "Execution failed: {$errorMessage}",
            modelUsed: $modelUsed,
            confidence: 0.0
        );
    }

    public function toArray(): array
    {
        return [
            'agent_name' => $this->agentName,
            'task_type' => $this->taskType->value,
            'is_successful' => $this->isSuccessful,
            'output_data' => $this->outputData,
            'summary' => $this->summary,
            'model_used' => $this->modelUsed,
            'tokens_used' => $this->tokensUsed,
            'latency_ms' => $this->latencyMs,
            'confidence' => $this->confidence,
        ];
    }
}

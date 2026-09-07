<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Agent Task DTO
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

final class AgentTaskDTO
{
    public function __construct(
        public readonly string $taskId,
        public readonly TaskType $taskType,
        public readonly string $instruction,
        public readonly array $contextPayload = [],
        public readonly ?string $modelOverride = null,
        public readonly int $timeoutSeconds = 30
    ) {}

    public static function fromArray(array $data): self
    {
        $taskType = isset($data['task_type']) && $data['task_type'] instanceof TaskType
            ? $data['task_type']
            : TaskType::tryFrom($data['task_type'] ?? '') ?? TaskType::RESEARCH_SYNTHESIS;

        return new self(
            taskId: (string) ($data['task_id'] ?? uniqid('task_')),
            taskType: $taskType,
            instruction: (string) ($data['instruction'] ?? ''),
            contextPayload: (array) ($data['context_payload'] ?? []),
            modelOverride: $data['model_override'] ?? null,
            timeoutSeconds: (int) ($data['timeout_seconds'] ?? 30)
        );
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'task_type' => $this->taskType->value,
            'instruction' => $this->instruction,
            'context_payload' => $this->contextPayload,
            'model_override' => $this->modelOverride,
            'timeout_seconds' => $this->timeoutSeconds,
        ];
    }
}

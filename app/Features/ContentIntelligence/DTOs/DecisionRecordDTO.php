<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Decision Record DTO
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

final class DecisionRecordDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $question,
        public readonly string $decision,
        public readonly string $reasoning,
        public readonly array $inputs = [],
        public readonly array $alternatives = [],
        public readonly float $confidence = 0.90,
        public readonly ?int $missionId = null,
        public readonly ?int $workflowRunId = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? uniqid('dec_')),
            question: (string) ($data['question'] ?? ''),
            decision: (string) ($data['decision'] ?? ''),
            reasoning: (string) ($data['reasoning'] ?? ''),
            inputs: (array) ($data['inputs'] ?? []),
            alternatives: (array) ($data['alternatives'] ?? []),
            confidence: (float) ($data['confidence'] ?? 0.90),
            missionId: isset($data['mission_id']) ? (int) $data['mission_id'] : null,
            workflowRunId: isset($data['workflow_run_id']) ? (int) $data['workflow_run_id'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'decision' => $this->decision,
            'reasoning' => $this->reasoning,
            'inputs' => $this->inputs,
            'alternatives' => $this->alternatives,
            'confidence' => $this->confidence,
            'mission_id' => $this->missionId,
            'workflow_run_id' => $this->workflowRunId,
        ];
    }
}

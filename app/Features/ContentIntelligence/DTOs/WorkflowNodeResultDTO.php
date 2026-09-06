<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Workflow Node Result DTO
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

final class WorkflowNodeResultDTO
{
    /**
     * @param  string  $status  'success' | 'failed' | 'paused' | 'loop' | 'branch'
     * @param  array<string, mixed>  $outputPayload  Structured data for the next stage
     * @param  float  $confidence  Confidence score 0.00 to 1.00
     * @param  string|null  $nextSuggestedNode  Optional explicit target node name
     * @param  array<string, mixed>  $metrics  Latency, token count, cost, etc.
     * @param  array<string, mixed>  $directives  Directives or issues for revision/looping
     */
    public function __construct(
        public readonly string $status,
        public readonly array $outputPayload,
        public readonly float $confidence = 1.0,
        public readonly ?string $nextSuggestedNode = null,
        public readonly array $metrics = [],
        public readonly array $directives = []
    ) {}

    public static function success(
        array $outputPayload,
        float $confidence = 1.0,
        ?string $nextSuggestedNode = null,
        array $metrics = []
    ): self {
        return new self(
            status: 'success',
            outputPayload: $outputPayload,
            confidence: $confidence,
            nextSuggestedNode: $nextSuggestedNode,
            metrics: $metrics
        );
    }

    public static function failed(
        string $errorMessage,
        array $directives = [],
        array $metrics = []
    ): self {
        return new self(
            status: 'failed',
            outputPayload: ['error' => $errorMessage],
            confidence: 0.0,
            metrics: $metrics,
            directives: $directives
        );
    }

    public static function loop(
        string $targetNode,
        array $directives,
        array $outputPayload = [],
        float $confidence = 0.5
    ): self {
        return new self(
            status: 'loop',
            outputPayload: $outputPayload,
            confidence: $confidence,
            nextSuggestedNode: $targetNode,
            directives: $directives
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isLoop(): bool
    {
        return $this->status === 'loop';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'output_payload' => $this->outputPayload,
            'confidence' => $this->confidence,
            'next_suggested_node' => $this->nextSuggestedNode,
            'metrics' => $this->metrics,
            'directives' => $this->directives,
        ];
    }
}

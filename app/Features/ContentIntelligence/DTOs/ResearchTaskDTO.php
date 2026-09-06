<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Research Task DTO
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

use App\Features\ContentIntelligence\Enums\SourceReliabilityTier;

final class ResearchTaskDTO
{
    /**
     * @param  string  $status  'pending' | 'completed' | 'failed'
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $taskId,
        public readonly string $query,
        public readonly string $purpose,
        public readonly SourceReliabilityTier $sourceTypePriority = SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
        public readonly string $status = 'pending',
        public readonly int $findingsCount = 0,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            taskId: (string) ($data['task_id'] ?? uniqid('task_')),
            query: (string) ($data['query'] ?? ''),
            purpose: (string) ($data['purpose'] ?? 'Domain evidence discovery'),
            sourceTypePriority: isset($data['source_type_priority']) && $data['source_type_priority'] instanceof SourceReliabilityTier
                ? $data['source_type_priority']
                : SourceReliabilityTier::tryFrom($data['source_type_priority'] ?? '') ?? SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
            status: (string) ($data['status'] ?? 'pending'),
            findingsCount: (int) ($data['findings_count'] ?? 0),
            metadata: (array) ($data['metadata'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'query' => $this->query,
            'purpose' => $this->purpose,
            'source_type_priority' => $this->sourceTypePriority->value,
            'status' => $this->status,
            'findings_count' => $this->findingsCount,
            'metadata' => $this->metadata,
        ];
    }
}

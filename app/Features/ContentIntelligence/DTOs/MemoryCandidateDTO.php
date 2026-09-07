<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Candidate DTO
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

use App\Features\ContentIntelligence\Enums\BrainScope;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Models\MemoryCandidate;

final class MemoryCandidateDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly string $content,
        public readonly ?int $missionId = null,
        public readonly BrainScope $scope = BrainScope::PROJECT,
        public readonly MemoryLayerType $layer = MemoryLayerType::SEMANTIC,
        public readonly string $type = 'technical_fact',
        public readonly ?string $subject = null,
        public readonly ?string $predicate = null,
        public readonly ?string $object = null,
        public readonly ?string $sourceUrl = null,
        public readonly ?int $sourceId = null,
        public readonly array $provenance = [],
        public readonly float $confidence = 0.85,
        public readonly float $importanceScore = 0.85,
        public readonly string $gateStatus = 'pending',
        public readonly ?string $rejectionReason = null,
        public readonly array $admissionNotes = [],
        public readonly ?string $admittedMemoryId = null
    ) {}

    public static function fromModel(MemoryCandidate $model): self
    {
        return new self(
            id: $model->id,
            userId: (int) $model->user_id,
            content: $model->content,
            missionId: $model->mission_id ? (int) $model->mission_id : null,
            scope: BrainScope::tryFrom($model->scope ?? '') ?? BrainScope::PROJECT,
            layer: MemoryLayerType::tryFrom($model->layer ?? '') ?? MemoryLayerType::SEMANTIC,
            type: (string) ($model->type ?? 'technical_fact'),
            subject: $model->subject,
            predicate: $model->predicate,
            object: $model->object,
            sourceUrl: $model->source_url,
            sourceId: $model->source_id ? (int) $model->source_id : null,
            provenance: (array) ($model->provenance ?? []),
            confidence: (float) ($model->confidence ?? 0.85),
            importanceScore: (float) ($model->importance_score ?? 0.85),
            gateStatus: (string) ($model->gate_status ?? 'pending'),
            rejectionReason: $model->rejection_reason,
            admissionNotes: (array) ($model->admission_notes ?? []),
            admittedMemoryId: $model->admitted_memory_id
        );
    }

    public static function fromArray(array $data): self
    {
        $scope = isset($data['scope']) && $data['scope'] instanceof BrainScope
            ? $data['scope']
            : BrainScope::tryFrom($data['scope'] ?? '') ?? BrainScope::PROJECT;

        $layer = isset($data['layer']) && $data['layer'] instanceof MemoryLayerType
            ? $data['layer']
            : MemoryLayerType::tryFrom($data['layer'] ?? '') ?? MemoryLayerType::SEMANTIC;

        return new self(
            id: (string) ($data['id'] ?? uniqid('cand_')),
            userId: (int) ($data['user_id'] ?? 1),
            content: (string) ($data['content'] ?? ''),
            missionId: isset($data['mission_id']) ? (int) $data['mission_id'] : null,
            scope: $scope,
            layer: $layer,
            type: (string) ($data['type'] ?? 'technical_fact'),
            subject: $data['subject'] ?? null,
            predicate: $data['predicate'] ?? null,
            object: $data['object'] ?? null,
            sourceUrl: $data['source_url'] ?? null,
            sourceId: isset($data['source_id']) ? (int) $data['source_id'] : null,
            provenance: (array) ($data['provenance'] ?? []),
            confidence: (float) ($data['confidence'] ?? 0.85),
            importanceScore: (float) ($data['importance_score'] ?? 0.85),
            gateStatus: (string) ($data['gate_status'] ?? 'pending'),
            rejectionReason: $data['rejection_reason'] ?? null,
            admissionNotes: (array) ($data['admission_notes'] ?? []),
            admittedMemoryId: $data['admitted_memory_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'mission_id' => $this->missionId,
            'scope' => $this->scope->value,
            'layer' => $this->layer->value,
            'type' => $this->type,
            'subject' => $this->subject,
            'predicate' => $this->predicate,
            'object' => $this->object,
            'content' => $this->content,
            'source_url' => $this->sourceUrl,
            'source_id' => $this->sourceId,
            'provenance' => $this->provenance,
            'confidence' => $this->confidence,
            'importance_score' => $this->importanceScore,
            'gate_status' => $this->gateStatus,
            'rejection_reason' => $this->rejectionReason,
            'admission_notes' => $this->admissionNotes,
            'admitted_memory_id' => $this->admittedMemoryId,
        ];
    }
}

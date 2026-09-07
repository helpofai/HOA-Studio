<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Object DTO
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
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Models\BrainMemory;

final class MemoryObjectDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly string $content,
        public readonly ?int $projectId = null,
        public readonly ?int $missionId = null,
        public readonly ?int $documentId = null,
        public readonly BrainScope $scope = BrainScope::PROJECT,
        public readonly MemoryLayerType $layer = MemoryLayerType::SEMANTIC,
        public readonly string $type = 'technical_fact',
        public readonly ?string $subject = null,
        public readonly ?string $predicate = null,
        public readonly ?string $object = null,
        public readonly ?int $sourceId = null,
        public readonly array $provenance = [],
        public readonly float $confidence = 0.95,
        public readonly int $authority = 80,
        public readonly float $freshnessScore = 1.0,
        public readonly MemoryStatus $status = MemoryStatus::ACTIVE,
        public readonly EpistemicState $epistemicState = EpistemicState::VERIFIED,
        public readonly array $relationships = [],
        public readonly int $version = 1,
        public readonly array $lineage = [],
        public readonly ?string $lineageParentId = null,
        public readonly ?string $verifiedAt = null,
        public readonly ?string $expiresAt = null
    ) {}

    public static function fromModel(BrainMemory $model): self
    {
        return new self(
            id: $model->id,
            userId: (int) $model->user_id,
            content: $model->content,
            projectId: $model->project_id ? (int) $model->project_id : null,
            missionId: $model->mission_id ? (int) $model->mission_id : null,
            documentId: $model->document_id ? (int) $model->document_id : null,
            scope: $model->scope instanceof BrainScope ? $model->scope : BrainScope::from($model->scope ?? 'project'),
            layer: $model->layer instanceof MemoryLayerType ? $model->layer : MemoryLayerType::from($model->layer ?? 'semantic'),
            type: (string) ($model->type ?? 'technical_fact'),
            subject: $model->subject,
            predicate: $model->predicate,
            object: $model->object,
            sourceId: $model->source_id ? (int) $model->source_id : null,
            provenance: (array) ($model->provenance ?? []),
            confidence: (float) ($model->confidence ?? 0.95),
            authority: (int) ($model->authority ?? 80),
            freshnessScore: (float) ($model->freshness_score ?? 1.0),
            status: $model->status instanceof MemoryStatus ? $model->status : MemoryStatus::from($model->status ?? 'active'),
            epistemicState: $model->epistemic_state instanceof EpistemicState ? $model->epistemic_state : EpistemicState::from($model->epistemic_state ?? 'verified'),
            relationships: (array) ($model->relationships ?? []),
            version: (int) ($model->version ?? 1),
            lineage: (array) ($model->lineage ?? []),
            lineageParentId: $model->lineage_parent_id,
            verifiedAt: $model->verified_at?->toIso8601String(),
            expiresAt: $model->expires_at?->toIso8601String()
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

        $status = isset($data['status']) && $data['status'] instanceof MemoryStatus
            ? $data['status']
            : MemoryStatus::tryFrom($data['status'] ?? '') ?? MemoryStatus::ACTIVE;

        $epistemicState = isset($data['epistemic_state']) && $data['epistemic_state'] instanceof EpistemicState
            ? $data['epistemic_state']
            : EpistemicState::tryFrom($data['epistemic_state'] ?? '') ?? EpistemicState::VERIFIED;

        return new self(
            id: (string) ($data['id'] ?? uniqid('mem_')),
            userId: (int) ($data['user_id'] ?? 1),
            content: (string) ($data['content'] ?? ''),
            projectId: isset($data['project_id']) ? (int) $data['project_id'] : null,
            missionId: isset($data['mission_id']) ? (int) $data['mission_id'] : null,
            documentId: isset($data['document_id']) ? (int) $data['document_id'] : null,
            scope: $scope,
            layer: $layer,
            type: (string) ($data['type'] ?? 'technical_fact'),
            subject: $data['subject'] ?? null,
            predicate: $data['predicate'] ?? null,
            object: $data['object'] ?? null,
            sourceId: isset($data['source_id']) ? (int) $data['source_id'] : null,
            provenance: (array) ($data['provenance'] ?? []),
            confidence: (float) ($data['confidence'] ?? 0.95),
            authority: (int) ($data['authority'] ?? 80),
            freshnessScore: (float) ($data['freshness_score'] ?? 1.0),
            status: $status,
            epistemicState: $epistemicState,
            relationships: (array) ($data['relationships'] ?? []),
            version: (int) ($data['version'] ?? 1),
            lineage: (array) ($data['lineage'] ?? []),
            lineageParentId: $data['lineage_parent_id'] ?? null,
            verifiedAt: $data['verified_at'] ?? null,
            expiresAt: $data['expires_at'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'project_id' => $this->projectId,
            'mission_id' => $this->missionId,
            'document_id' => $this->documentId,
            'scope' => $this->scope->value,
            'layer' => $this->layer->value,
            'type' => $this->type,
            'subject' => $this->subject,
            'predicate' => $this->predicate,
            'object' => $this->object,
            'content' => $this->content,
            'source_id' => $this->sourceId,
            'provenance' => $this->provenance,
            'confidence' => $this->confidence,
            'authority' => $this->authority,
            'freshness_score' => $this->freshnessScore,
            'status' => $this->status->value,
            'epistemic_state' => $this->epistemicState->value,
            'relationships' => $this->relationships,
            'version' => $this->version,
            'lineage' => $this->lineage,
            'lineage_parent_id' => $this->lineageParentId,
            'verified_at' => $this->verifiedAt,
            'expires_at' => $this->expiresAt,
        ];
    }
}

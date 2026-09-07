<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admission Result DTO
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

final class AdmissionResultDTO
{
    public function __construct(
        public readonly bool $isAdmitted,
        public readonly string $action, // 'admit', 'reject', 'merge_supersede', 'duplicate_ignored'
        public readonly string $candidateId,
        public readonly ?string $memoryId = null,
        public readonly ?string $rejectionReason = null,
        public readonly float $confidenceScore = 0.0,
        public readonly float $importanceScore = 0.0,
        public readonly array $conflictsDetected = [],
        public readonly array $admissionNotes = []
    ) {}

    public static function admitted(string $candidateId, string $memoryId, float $confidence, float $importance, array $notes = []): self
    {
        return new self(
            isAdmitted: true,
            action: 'admit',
            candidateId: $candidateId,
            memoryId: $memoryId,
            rejectionReason: null,
            confidenceScore: $confidence,
            importanceScore: $importance,
            conflictsDetected: [],
            admissionNotes: $notes
        );
    }

    public static function rejected(string $candidateId, string $reason, float $confidence, float $importance, array $conflicts = [], array $notes = []): self
    {
        return new self(
            isAdmitted: false,
            action: 'reject',
            candidateId: $candidateId,
            memoryId: null,
            rejectionReason: $reason,
            confidenceScore: $confidence,
            importanceScore: $importance,
            conflictsDetected: $conflicts,
            admissionNotes: $notes
        );
    }

    public static function duplicate(string $candidateId, string $existingMemoryId, array $notes = []): self
    {
        return new self(
            isAdmitted: false,
            action: 'duplicate_ignored',
            candidateId: $candidateId,
            memoryId: $existingMemoryId,
            rejectionReason: 'Duplicate memory already exists in cognitive store.',
            confidenceScore: 1.0,
            importanceScore: 1.0,
            conflictsDetected: [],
            admissionNotes: $notes
        );
    }

    public static function superseded(string $candidateId, string $newMemoryId, string $oldMemoryId, float $confidence, float $importance, array $notes = []): self
    {
        return new self(
            isAdmitted: true,
            action: 'merge_supersede',
            candidateId: $candidateId,
            memoryId: $newMemoryId,
            rejectionReason: null,
            confidenceScore: $confidence,
            importanceScore: $importance,
            conflictsDetected: ['superseded_memory_id' => $oldMemoryId],
            admissionNotes: array_merge($notes, ['superseded_memory_id' => $oldMemoryId])
        );
    }

    public function toArray(): array
    {
        return [
            'is_admitted' => $this->isAdmitted,
            'action' => $this->action,
            'candidate_id' => $this->candidateId,
            'memory_id' => $this->memoryId,
            'rejection_reason' => $this->rejectionReason,
            'confidence_score' => $this->confidenceScore,
            'importance_score' => $this->importanceScore,
            'conflicts_detected' => $this->conflictsDetected,
            'admission_notes' => $this->admissionNotes,
        ];
    }
}

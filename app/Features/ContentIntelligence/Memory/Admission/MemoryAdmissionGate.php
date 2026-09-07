<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Admission Gate
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

namespace App\Features\ContentIntelligence\Memory\Admission;

use App\Features\ContentIntelligence\Contracts\MemoryAdmissionInterface;
use App\Features\ContentIntelligence\DTOs\AdmissionResultDTO;
use App\Features\ContentIntelligence\DTOs\MemoryCandidateDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\MemoryCandidate;
use Illuminate\Support\Str;

class MemoryAdmissionGate implements MemoryAdmissionInterface
{
    public function __construct(
        protected EvidenceValidator $evidenceValidator,
        protected DuplicateDetector $duplicateDetector
    ) {}

    /**
     * Evaluate a candidate memory through the multi-stage cognitive admission gate.
     */
    public function evaluate(MemoryCandidateDTO $candidate): AdmissionResultDTO
    {
        // 1. Stage 1: Validation (Completeness & Syntax)
        if (trim($candidate->content) === '' || strlen(trim($candidate->content)) < 5) {
            return AdmissionResultDTO::rejected(
                candidateId: $candidate->id,
                reason: 'Memory content is empty or too short (< 5 chars).',
                confidence: 0.0,
                importance: 0.0
            );
        }

        // 2. Stage 2: Evidence Check
        $evidenceCheck = $this->evidenceValidator->validate($candidate);
        $finalConfidence = $evidenceCheck['confidence'];
        $epistemicState = $evidenceCheck['epistemicState'];

        // 3. Stage 3: Confidence Score Gate (≥ 0.80 for permanent admission)
        if ($finalConfidence < 0.80) {
            $this->persistCandidate($candidate, 'rejected', "Confidence score ({$finalConfidence}) below minimum threshold (0.80).");

            return AdmissionResultDTO::rejected(
                candidateId: $candidate->id,
                reason: "Insufficient confidence ({$finalConfidence} < 0.80).",
                confidence: $finalConfidence,
                importance: $candidate->importanceScore,
                notes: $evidenceCheck['notes']
            );
        }

        // 4. Stage 4: Duplicate & Contradiction Detection
        $duplicateCheck = $this->duplicateDetector->detect($candidate);

        if ($duplicateCheck['isDuplicate']) {
            $existing = $duplicateCheck['conflictingMemory'];
            $this->persistCandidate($candidate, 'merged', "Duplicate of existing memory {$existing->id}");

            return AdmissionResultDTO::duplicate(
                candidateId: $candidate->id,
                existingMemoryId: (string) $existing->id,
                notes: ['match_type' => $duplicateCheck['matchType']]
            );
        }

        // 5. Stage 5: Contradiction Reconciliation (Fact v1 -> Fact v2 Lineage)
        if ($duplicateCheck['isContradiction']) {
            $conflicting = $duplicateCheck['conflictingMemory'];

            // If candidate confidence meets or exceeds existing, supersede the old fact
            if ($finalConfidence >= (float) $conflicting->confidence) {
                return $this->supersedeAndAdmit($candidate, $conflicting, $finalConfidence, $epistemicState, $evidenceCheck['notes']);
            }

            // Conflicting fact has higher authority, candidate rejected
            $this->persistCandidate(
                $candidate,
                'rejected',
                "Contradicts established fact {$conflicting->id} with lower confidence ({$finalConfidence} vs {$conflicting->confidence})"
            );

            return AdmissionResultDTO::rejected(
                candidateId: $candidate->id,
                reason: "Contradicts active fact {$conflicting->id} with lower confidence.",
                confidence: $finalConfidence,
                importance: $candidate->importanceScore,
                conflicts: ['active_memory_id' => $conflicting->id, 'active_value' => $conflicting->object]
            );
        }

        // 6. Stage 6: Importance Gate (≥ 0.70)
        if ($candidate->importanceScore < 0.70) {
            $this->persistCandidate($candidate, 'rejected', "Importance score ({$candidate->importanceScore}) below threshold (0.70)");

            return AdmissionResultDTO::rejected(
                candidateId: $candidate->id,
                reason: "Low reusable importance score ({$candidate->importanceScore} < 0.70).",
                confidence: $finalConfidence,
                importance: $candidate->importanceScore
            );
        }

        // 7. Stage 7: Admission & Storage into Brain Memory
        $memoryId = 'mem_'.Str::lower(Str::random(12));

        $memory = BrainMemory::create([
            'id' => $memoryId,
            'user_id' => $candidate->userId,
            'mission_id' => $candidate->missionId,
            'scope' => $candidate->scope->value,
            'layer' => $candidate->layer->value,
            'type' => $candidate->type,
            'subject' => $candidate->subject,
            'predicate' => $candidate->predicate,
            'object' => $candidate->object,
            'content' => $candidate->content,
            'source_id' => $candidate->sourceId,
            'provenance' => array_merge($candidate->provenance, $evidenceCheck['notes'], [
                'source_url' => $candidate->sourceUrl,
                'admitted_at' => now()->toIso8601String(),
            ]),
            'confidence' => $finalConfidence,
            'authority' => 85,
            'freshness_score' => 1.0000,
            'status' => MemoryStatus::ACTIVE->value,
            'epistemic_state' => $epistemicState->value,
            'version' => 1,
            'lineage' => [$memoryId],
            'lineage_parent_id' => null,
            'verified_at' => now(),
            'expires_at' => now()->addMonths(6),
        ]);

        $this->persistCandidate($candidate, 'admitted', null, $memoryId);

        return AdmissionResultDTO::admitted(
            candidateId: $candidate->id,
            memoryId: $memory->id,
            confidence: $finalConfidence,
            importance: $candidate->importanceScore,
            notes: $evidenceCheck['notes']
        );
    }

    /**
     * Supersede an old memory and admit the new version with preserved lineage (Fact v1 -> Fact v2).
     */
    protected function supersedeAndAdmit(
        MemoryCandidateDTO $candidate,
        BrainMemory $oldMemory,
        float $confidence,
        EpistemicState $epistemicState,
        array $evidenceNotes
    ): AdmissionResultDTO {
        // Mark old memory as superseded
        $oldMemory->update([
            'status' => MemoryStatus::SUPERSEDED->value,
            'epistemic_state' => EpistemicState::OUTDATED->value,
        ]);

        $newMemoryId = 'mem_'.Str::lower(Str::random(12));
        $newVersion = ((int) $oldMemory->version) + 1;
        $newLineage = array_merge((array) ($oldMemory->lineage ?? [$oldMemory->id]), [$newMemoryId]);

        $newMemory = BrainMemory::create([
            'id' => $newMemoryId,
            'user_id' => $candidate->userId,
            'mission_id' => $candidate->missionId,
            'scope' => $candidate->scope->value,
            'layer' => $candidate->layer->value,
            'type' => $candidate->type,
            'subject' => $candidate->subject,
            'predicate' => $candidate->predicate,
            'object' => $candidate->object,
            'content' => $candidate->content,
            'source_id' => $candidate->sourceId,
            'provenance' => array_merge($candidate->provenance, $evidenceNotes, [
                'superseded_parent_id' => $oldMemory->id,
                'admitted_at' => now()->toIso8601String(),
            ]),
            'confidence' => $confidence,
            'authority' => 90,
            'freshness_score' => 1.0000,
            'status' => MemoryStatus::ACTIVE->value,
            'epistemic_state' => $epistemicState->value,
            'version' => $newVersion,
            'lineage' => $newLineage,
            'lineage_parent_id' => $oldMemory->id,
            'verified_at' => now(),
            'expires_at' => now()->addMonths(6),
        ]);

        $this->persistCandidate($candidate, 'admitted', null, $newMemoryId);

        return AdmissionResultDTO::superseded(
            candidateId: $candidate->id,
            newMemoryId: $newMemory->id,
            oldMemoryId: $oldMemory->id,
            confidence: $confidence,
            importance: $candidate->importanceScore,
            notes: array_merge($evidenceNotes, [
                'version' => $newVersion,
                'lineage' => $newLineage,
            ])
        );
    }

    /**
     * Persist candidate state in the staging table.
     */
    protected function persistCandidate(MemoryCandidateDTO $dto, string $status, ?string $reason = null, ?string $admittedMemoryId = null): void
    {
        MemoryCandidate::updateOrCreate(
            ['id' => $dto->id],
            [
                'user_id' => $dto->userId,
                'mission_id' => $dto->missionId,
                'scope' => $dto->scope->value,
                'layer' => $dto->layer->value,
                'type' => $dto->type,
                'subject' => $dto->subject,
                'predicate' => $dto->predicate,
                'object' => $dto->object,
                'content' => $dto->content,
                'source_url' => $dto->sourceUrl,
                'source_id' => $dto->sourceId,
                'provenance' => $dto->provenance,
                'confidence' => $dto->confidence,
                'importance_score' => $dto->importanceScore,
                'gate_status' => $status,
                'rejection_reason' => $reason,
                'admitted_memory_id' => $admittedMemoryId,
            ]
        );
    }
}

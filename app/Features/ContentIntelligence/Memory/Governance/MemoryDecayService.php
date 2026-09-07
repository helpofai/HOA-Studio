<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Decay Service
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

namespace App\Features\ContentIntelligence\Memory\Governance;

use App\Features\ContentIntelligence\DTOs\DecayReconciliationDTO;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Models\ArticleElementNode;
use App\Features\ContentIntelligence\Models\BrainMemory;
use Carbon\Carbon;

class MemoryDecayService
{
    /**
     * Run decay evaluations across active memories for a given user or mission.
     */
    public function evaluateDecay(?int $userId = null, ?int $missionId = null): DecayReconciliationDTO
    {
        $query = BrainMemory::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($missionId) {
            $query->where('mission_id', $missionId);
        }

        $memories = $query->get();

        $activeCount = 0;
        $uncertainCount = 0;
        $decayedCount = 0;
        $supersededCount = 0;
        $updatedMemoryIds = [];
        $impactedElements = [];

        $now = Carbon::now();

        foreach ($memories as $memory) {
            if ($memory->status === MemoryStatus::SUPERSEDED) {
                $supersededCount++;

                continue;
            }

            if ($memory->status === MemoryStatus::REJECTED) {
                continue;
            }

            // Calculate freshness based on expiration date
            $freshness = (float) $memory->freshness_score;

            if ($memory->expires_at && $memory->created_at) {
                $totalLifespanSeconds = max(1, $memory->expires_at->diffInSeconds($memory->created_at));
                $elapsedSeconds = max(0, $now->diffInSeconds($memory->created_at));

                if ($now->greaterThanOrEqualTo($memory->expires_at)) {
                    $freshness = 0.20;
                } else {
                    $ratio = 1.0 - ($elapsedSeconds / $totalLifespanSeconds);
                    $freshness = max(0.20, min(1.0, round($ratio, 4)));
                }
            }

            $newStatus = $memory->status;
            $newEpistemicState = $memory->epistemic_state;

            if ($freshness < 0.40) {
                $newStatus = MemoryStatus::DECAYED;
                $newEpistemicState = EpistemicState::OUTDATED;
                $decayedCount++;
            } elseif ($freshness < 0.60) {
                $newStatus = MemoryStatus::UNCERTAIN;
                $newEpistemicState = EpistemicState::PARTIALLY_VERIFIED;
                $uncertainCount++;
            } else {
                $newStatus = MemoryStatus::ACTIVE;
                $activeCount++;
            }

            if ($memory->freshness_score != $freshness || $memory->status !== $newStatus) {
                $memory->update([
                    'freshness_score' => $freshness,
                    'status' => $newStatus->value,
                    'epistemic_state' => $newEpistemicState->value,
                ]);

                $updatedMemoryIds[] = $memory->id;

                // Invalidate affected article elements if decayed or uncertain
                if ($newStatus !== MemoryStatus::ACTIVE) {
                    $elements = $this->flagImpactedElements($memory->id, "Memory {$memory->id} has decayed (freshness: {$freshness})");
                    $impactedElements = array_merge($impactedElements, $elements);
                }
            }
        }

        return new DecayReconciliationDTO(
            totalChecked: $memories->count(),
            activeCount: $activeCount,
            uncertainCount: $uncertainCount,
            decayedCount: $decayedCount,
            supersededCount: $supersededCount,
            updatedMemoryIds: $updatedMemoryIds,
            impactedElementCount: count($impactedElements),
            impactedElements: $impactedElements
        );
    }

    /**
     * Flag downstream article element nodes linked to this memory as stale.
     *
     * @return array<int, string>
     */
    public function flagImpactedElements(string $memoryId, string $reason): array
    {
        $elements = ArticleElementNode::where('memory_id', $memoryId)
            ->where('is_stale', false)
            ->get();

        $impactedIds = [];

        foreach ($elements as $element) {
            $element->update([
                'is_stale' => true,
                'invalidation_reason' => $reason,
                'epistemic_state' => EpistemicState::OUTDATED->value,
            ]);

            $impactedIds[] = (string) $element->id;
        }

        return $impactedIds;
    }
}

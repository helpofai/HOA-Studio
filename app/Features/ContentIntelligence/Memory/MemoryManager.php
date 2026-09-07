<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Manager
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

namespace App\Features\ContentIntelligence\Memory;

use App\Features\ContentIntelligence\DTOs\AdmissionResultDTO;
use App\Features\ContentIntelligence\DTOs\DecayReconciliationDTO;
use App\Features\ContentIntelligence\DTOs\MemoryCandidateDTO;
use App\Features\ContentIntelligence\DTOs\MemoryObjectDTO;
use App\Features\ContentIntelligence\Enums\BrainScope;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Memory\Admission\MemoryAdmissionGate;
use App\Features\ContentIntelligence\Memory\Governance\MemoryDecayService;
use App\Features\ContentIntelligence\Memory\Retrieval\AttentionEngine;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\EpisodicEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MemoryManager
{
    public function __construct(
        protected MemoryAdmissionGate $admissionGate,
        protected MemoryDecayService $decayService,
        protected AttentionEngine $attentionEngine
    ) {}

    /**
     * Submit a candidate fact or piece of knowledge through the cognitive admission gate.
     */
    public function admit(MemoryCandidateDTO $candidate): AdmissionResultDTO
    {
        return $this->admissionGate->evaluate($candidate);
    }

    /**
     * Store an explicit memory directly (e.g. user-defined brand guideline or working state).
     */
    public function remember(MemoryObjectDTO $memory): BrainMemory
    {
        return BrainMemory::updateOrCreate(
            ['id' => $memory->id],
            $memory->toArray()
        );
    }

    /**
     * Recall memories matching given filter criteria.
     *
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, BrainMemory>
     */
    public function recall(int $userId, array $criteria = []): Collection
    {
        $query = BrainMemory::query()
            ->where('user_id', $userId)
            ->where('status', MemoryStatus::ACTIVE->value);

        if (! empty($criteria['scope'])) {
            $val = $criteria['scope'] instanceof BrainScope ? $criteria['scope']->value : $criteria['scope'];
            $query->where('scope', $val);
        }

        if (! empty($criteria['layer'])) {
            $val = $criteria['layer'] instanceof MemoryLayerType ? $criteria['layer']->value : $criteria['layer'];
            $query->where('layer', $val);
        }

        if (! empty($criteria['mission_id'])) {
            $query->where('mission_id', $criteria['mission_id']);
        }

        if (! empty($criteria['subject'])) {
            $query->where('subject', 'LIKE', "%{$criteria['subject']}%");
        }

        $limit = (int) ($criteria['limit'] ?? 20);

        return $query->orderByDesc('confidence')->limit($limit)->get();
    }

    /**
     * Record an episodic event (e.g. strategy outcomes, user feedback, reflections).
     */
    public function recordEpisode(
        int $userId,
        string $eventType,
        ?int $missionId = null,
        array $context = [],
        ?string $actionTaken = null,
        float $outcomeScore = 0.0,
        ?string $lessonsLearned = null
    ): EpisodicEvent {
        return EpisodicEvent::create([
            'id' => 'ep_'.Str::lower(Str::random(12)),
            'user_id' => $userId,
            'mission_id' => $missionId,
            'event_type' => $eventType,
            'context' => $context,
            'action_taken' => $actionTaken,
            'outcome_score' => $outcomeScore,
            'lessons_learned' => $lessonsLearned,
        ]);
    }

    /**
     * Run freshness decay and reconciliation across memories.
     */
    public function evaluateDecay(?int $userId = null, ?int $missionId = null): DecayReconciliationDTO
    {
        return $this->decayService->evaluateDecay($userId, $missionId);
    }

    /**
     * Focus attention and compile prompt context.
     *
     * @param  array<int, string>  $keywords
     * @return array{contextText: string, memoryIds: array<int, string>, count: int}
     */
    public function focusAttention(
        int $userId,
        ?int $missionId,
        string $topic,
        ?string $currentSectionTitle = null,
        array $keywords = [],
        int $maxItems = 8
    ): array {
        return $this->attentionEngine->focus($userId, $missionId, $topic, $currentSectionTitle, $keywords, $maxItems);
    }
}

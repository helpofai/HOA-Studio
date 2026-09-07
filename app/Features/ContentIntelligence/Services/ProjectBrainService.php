<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Level 2 Project Brain Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\MemoryObjectDTO;
use App\Features\ContentIntelligence\Enums\BrainScope;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Memory\MemoryManager;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\ContentIntelligence\Models\KnowledgeTriple;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProjectBrainService
{
    public function __construct(
        protected MemoryManager $memoryManager
    ) {}

    /**
     * Get the full state snapshot of a Project Brain for a mission.
     *
     * @return array<string, mixed>
     */
    public function getState(ContentMission $mission): array
    {
        $workingMemories = BrainMemory::query()
            ->where('mission_id', $mission->id)
            ->where('status', MemoryStatus::ACTIVE->value)
            ->get();

        $knowledgeTriples = KnowledgeTriple::where('mission_id', $mission->id)->get();
        $claims = ClaimNode::where('mission_id', $mission->id)->get();

        return [
            'mission_id' => $mission->id,
            'topic' => $mission->topic,
            'status' => $mission->status,
            'risk_level' => $mission->risk_level,
            'active_memories_count' => $workingMemories->count(),
            'knowledge_triples_count' => $knowledgeTriples->count(),
            'claims_count' => $claims->count(),
            'verified_claims_count' => $claims->where('epistemic_state', 'verified')->count(),
            'controversial_claims_count' => $claims->where('is_controversial', true)->count(),
        ];
    }

    /**
     * Save an editorial decision or directive into the Project Brain's working memory.
     */
    public function recordDecision(ContentMission $mission, string $decision, array $context = []): BrainMemory
    {
        $dto = new MemoryObjectDTO(
            id: 'mem_dec_'.Str::lower(Str::random(10)),
            userId: (int) $mission->user_id,
            content: $decision,
            missionId: $mission->id,
            scope: BrainScope::PROJECT,
            layer: MemoryLayerType::WORKING,
            type: 'editorial_decision',
            provenance: $context,
            confidence: 1.0,
            authority: 100,
            freshnessScore: 1.0,
            status: MemoryStatus::ACTIVE
        );

        return $this->memoryManager->remember($dto);
    }

    /**
     * Retrieve active Project Brain memories for a mission.
     *
     * @return Collection<int, BrainMemory>
     */
    public function getMemories(ContentMission $mission): Collection
    {
        return BrainMemory::query()
            ->where('mission_id', $mission->id)
            ->where('status', MemoryStatus::ACTIVE->value)
            ->orderByDesc('confidence')
            ->get();
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Level 1 Site Brain Service
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

use App\Features\ContentIntelligence\Enums\BrainScope;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Memory\MemoryManager;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\ContentMission;
use App\Features\Documents\Models\Document;
use Illuminate\Support\Collection;

class SiteBrainService
{
    public function __construct(
        protected MemoryManager $memoryManager
    ) {}

    /**
     * Check for potential keyword or topic cannibalization across published or in-progress articles.
     *
     * @return array{hasCannibalizationRisk: bool, matchingCount: int, existingItems: array<int, array<string, mixed>>, recommendation: string}
     */
    public function checkCannibalization(int $userId, string $topic): array
    {
        $matchingDocs = Document::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($topic) {
                $q->where('title', 'LIKE', "%{$topic}%");
            })
            ->limit(5)
            ->get();

        $matchingMissions = ContentMission::query()
            ->where('user_id', $userId)
            ->where('topic', 'LIKE', "%{$topic}%")
            ->limit(5)
            ->get();

        $totalMatches = $matchingDocs->count() + $matchingMissions->count();
        $items = [];

        foreach ($matchingDocs as $doc) {
            $items[] = [
                'type' => 'document',
                'id' => $doc->id,
                'title' => $doc->title,
                'status' => 'draft',
            ];
        }

        foreach ($matchingMissions as $m) {
            $items[] = [
                'type' => 'mission',
                'id' => $m->id,
                'title' => $m->topic,
                'status' => $m->status,
            ];
        }

        $hasRisk = $totalMatches > 0;
        $recommendation = $hasRisk
            ? "Found {$totalMatches} existing assets covering '{$topic}'. Consider focusing on a distinct angle, sub-problem, or advanced implementation to prevent keyword cannibalization."
            : "No cannibalization detected for '{$topic}'. Fresh topic angle confirmed.";

        return [
            'hasCannibalizationRisk' => $hasRisk,
            'matchingCount' => $totalMatches,
            'existingItems' => $items,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Get site-wide brand knowledge and rules from memory.
     *
     * @return Collection<int, BrainMemory>
     */
    public function getBrandGuidelines(int $userId): Collection
    {
        return $this->memoryManager->recall($userId, [
            'scope' => BrainScope::SITE,
            'layer' => MemoryLayerType::BRAND,
        ]);
    }

    /**
     * Retrieve all active Site Brain memories.
     *
     * @return Collection<int, BrainMemory>
     */
    public function getSiteMemories(int $userId): Collection
    {
        return $this->memoryManager->recall($userId, [
            'scope' => BrainScope::SITE,
            'limit' => 50,
        ]);
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Attention Engine
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

namespace App\Features\ContentIntelligence\Memory\Retrieval;

use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Models\BrainMemory;

class AttentionEngine
{
    /**
     * Focus attention and compile minimal, high-salience cognitive context for the current writing task.
     *
     * @param  array<int, string>  $keywords
     * @return array{contextText: string, memoryIds: array<int, string>, count: int}
     */
    public function focus(
        int $userId,
        ?int $missionId,
        string $topic,
        ?string $currentSectionTitle = null,
        array $keywords = [],
        int $maxItems = 8
    ): array {
        $selectedMemories = collect();
        $memoryIds = [];

        // 1. Mandatory Core: Brand Guidelines and Tone Rules
        $brandMemories = BrainMemory::query()
            ->where('user_id', $userId)
            ->where('status', MemoryStatus::ACTIVE->value)
            ->where('layer', MemoryLayerType::BRAND->value)
            ->orderByDesc('authority')
            ->limit(2)
            ->get();

        foreach ($brandMemories as $mem) {
            $selectedMemories->push($mem);
            $memoryIds[] = $mem->id;
        }

        // 2. Working Constraints & Decisions for this Mission
        if ($missionId) {
            $workingMemories = BrainMemory::query()
                ->where('user_id', $userId)
                ->where('mission_id', $missionId)
                ->where('status', MemoryStatus::ACTIVE->value)
                ->where('layer', MemoryLayerType::WORKING->value)
                ->orderByDesc('confidence')
                ->limit(2)
                ->get();

            foreach ($workingMemories as $mem) {
                if (! in_array($mem->id, $memoryIds)) {
                    $selectedMemories->push($mem);
                    $memoryIds[] = $mem->id;
                }
            }
        }

        // 3. Highly Targeted Semantic & Knowledge Facts (matches topic, section title, or keywords)
        $semanticQuery = BrainMemory::query()
            ->where('user_id', $userId)
            ->where('status', MemoryStatus::ACTIVE->value)
            ->whereIn('layer', [MemoryLayerType::SEMANTIC->value, MemoryLayerType::KNOWLEDGE->value]);

        $searchTerms = array_filter(array_merge(
            [$topic],
            $currentSectionTitle ? [$currentSectionTitle] : [],
            $keywords
        ));

        if (! empty($searchTerms)) {
            $semanticQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('subject', 'LIKE', "%{$term}%")
                        ->orWhere('content', 'LIKE', "%{$term}%");
                }
            });
        }

        $semanticFacts = $semanticQuery
            ->orderByDesc('confidence')
            ->orderByDesc('authority')
            ->limit($maxItems - $selectedMemories->count())
            ->get();

        foreach ($semanticFacts as $mem) {
            if (! in_array($mem->id, $memoryIds)) {
                $selectedMemories->push($mem);
                $memoryIds[] = $mem->id;
            }
        }

        // 4. Strategic Memory Heuristics (What strategies succeed)
        if ($selectedMemories->count() < $maxItems) {
            $strategicMemories = BrainMemory::query()
                ->where('user_id', $userId)
                ->where('status', MemoryStatus::ACTIVE->value)
                ->where('layer', MemoryLayerType::STRATEGIC->value)
                ->orderByDesc('confidence')
                ->limit($maxItems - $selectedMemories->count())
                ->get();

            foreach ($strategicMemories as $mem) {
                if (! in_array($mem->id, $memoryIds)) {
                    $selectedMemories->push($mem);
                    $memoryIds[] = $mem->id;
                }
            }
        }

        // Format into a focused, token-efficient context prompt block
        $contextLines = [];
        foreach ($selectedMemories as $mem) {
            $prefix = strtoupper($mem->layer->value);
            $subj = $mem->subject ? "[{$mem->subject}] " : '';
            $contextLines[] = "• [{$prefix}] {$subj}{$mem->content} (Confidence: {$mem->confidence})";
        }

        $contextText = implode("\n", $contextLines);

        return [
            'contextText' => $contextText,
            'memoryIds' => $memoryIds,
            'count' => count($memoryIds),
        ];
    }
}

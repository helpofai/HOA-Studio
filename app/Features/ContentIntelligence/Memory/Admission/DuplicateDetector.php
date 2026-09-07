<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Duplicate Detector
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

use App\Features\ContentIntelligence\DTOs\MemoryCandidateDTO;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Models\BrainMemory;

class DuplicateDetector
{
    /**
     * Check if a candidate memory is an exact or semantic duplicate of an active memory.
     *
     * @return array{isDuplicate: bool, isContradiction: bool, conflictingMemory: ?BrainMemory, matchType: ?string}
     */
    public function detect(MemoryCandidateDTO $candidate): array
    {
        // 1. Triple-based check (Subject + Predicate)
        if (! empty($candidate->subject) && ! empty($candidate->predicate)) {
            $existing = BrainMemory::query()
                ->where('user_id', $candidate->userId)
                ->where('status', MemoryStatus::ACTIVE->value)
                ->where('subject', $candidate->subject)
                ->where('predicate', $candidate->predicate)
                ->first();

            if ($existing) {
                // Check if the object matches or contradicts
                if (trim((string) $existing->object) === trim((string) $candidate->object)) {
                    return [
                        'isDuplicate' => true,
                        'isContradiction' => false,
                        'conflictingMemory' => $existing,
                        'matchType' => 'exact_triple',
                    ];
                }

                // Different object for same subject + predicate: contradiction/update
                return [
                    'isDuplicate' => false,
                    'isContradiction' => true,
                    'conflictingMemory' => $existing,
                    'matchType' => 'triple_contradiction',
                ];
            }
        }

        // 2. Exact or high-similarity content check
        $query = BrainMemory::query()
            ->where('user_id', $candidate->userId)
            ->where('status', MemoryStatus::ACTIVE->value)
            ->where('layer', $candidate->layer->value);

        $existingMemories = $query->limit(50)->get();

        $candidateTokens = $this->tokenize($candidate->content);

        foreach ($existingMemories as $memory) {
            if ($memory->content === $candidate->content) {
                return [
                    'isDuplicate' => true,
                    'isContradiction' => false,
                    'conflictingMemory' => $memory,
                    'matchType' => 'exact_text',
                ];
            }

            $memoryTokens = $this->tokenize($memory->content);
            $similarity = $this->jaccardSimilarity($candidateTokens, $memoryTokens);

            if ($similarity >= 0.88) {
                return [
                    'isDuplicate' => true,
                    'isContradiction' => false,
                    'conflictingMemory' => $memory,
                    'matchType' => 'fuzzy_semantic',
                ];
            }
        }

        return [
            'isDuplicate' => false,
            'isContradiction' => false,
            'conflictingMemory' => null,
            'matchType' => null,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function tokenize(string $text): array
    {
        $clean = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_count_values($words);
    }

    /**
     * Compute token Jaccard similarity.
     *
     * @param  array<string, int>  $a
     * @param  array<string, int>  $b
     */
    protected function jaccardSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = 0;
        $union = count($a) + count($b);

        foreach ($a as $word => $count) {
            if (isset($b[$word])) {
                $intersection++;
            }
        }

        $union -= $intersection;

        return $union > 0 ? ($intersection / $union) : 0.0;
    }
}

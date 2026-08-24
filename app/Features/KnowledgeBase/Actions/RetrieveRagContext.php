<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\KnowledgeBase\Actions;

use App\Features\KnowledgeBase\Services\VectorSearchEngine;
use App\Models\User;

class RetrieveRagContext
{
    public function __construct(
        protected VectorSearchEngine $vectorEngine
    ) {}

    /**
     * Retrieve matching knowledge base chunks via Hybrid Search and format as a structured RAG context prompt block
     */
    public function execute(
        User $user,
        string $query,
        int $limit = 4,
        ?int $projectId = null,
        ?string $category = null,
        float $minSimilarity = 0.50
    ): array {
        $results = $this->vectorEngine->hybridSearch(
            user: $user,
            query: $query,
            topK: $limit,
            minSimilarity: $minSimilarity,
            projectId: $projectId,
            category: $category
        );

        if (empty($results)) {
            return [
                'has_context' => false,
                'prompt_snippet' => '',
                'chunks' => [],
                'total_tokens' => 0,
            ];
        }

        $totalTokens = 0;
        $snippet = "=== RETRIEVED USER BRAIN & KNOWLEDGE BASE CONTEXT ===\n";
        $snippet .= "Use the following verified user knowledge, brand facts, and reference data to ground your content accurately:\n\n";

        $formattedChunks = [];
        foreach ($results as $idx => $item) {
            $sourceNum = $idx + 1;
            $chunk = $item['chunk'];
            $title = $item['source_title'] ?? 'Knowledge Source';
            $categoryName = strtoupper($item['category'] ?? 'GENERAL');
            $relevancePercent = round(($item['score'] ?? 0.8) * 100);

            $snippet .= "[Source {$sourceNum}: {$title} | {$categoryName} | Match: {$relevancePercent}%]\n";
            $snippet .= trim($chunk->content) . "\n\n";
            
            $tokens = $chunk->token_count > 0 ? $chunk->token_count : (int) ceil(mb_strlen($chunk->content) / 4);
            $totalTokens += $tokens;

            $formattedChunks[] = [
                'id' => $chunk->id,
                'source_title' => $title,
                'category' => $item['category'] ?? 'general_docs',
                'content' => $chunk->content,
                'score' => $item['score'],
                'token_count' => $tokens,
            ];
        }

        $snippet .= "=== END USER BRAIN CONTEXT ===\n";
        $snippet .= "Strictly maintain accuracy based on the provided facts and brand tone. Do not fabricate unverifiable claims.\n";

        return [
            'has_context' => true,
            'prompt_snippet' => $snippet,
            'chunks' => $formattedChunks,
            'total_tokens' => $totalTokens,
        ];
    }
}
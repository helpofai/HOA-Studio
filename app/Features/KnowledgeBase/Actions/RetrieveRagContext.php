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
     * Retrieve matching knowledge base chunks and format as a structured RAG context prompt block
     */
    public function execute(User $user, string $query, int $limit = 4, ?int $projectId = null): array
    {
        $chunks = $this->vectorEngine->search($user, $query, $limit, $projectId);

        if (empty($chunks)) {
            return [
                'has_context' => false,
                'prompt_snippet' => '',
                'chunks' => [],
                'total_tokens' => 0,
            ];
        }

        $totalTokens = 0;
        $snippet = "=== RETRIEVED KNOWLEDGE BASE CONTEXT ===\n";
        $snippet .= "Use the following verified company knowledge and reference data to answer or generate content accurately:\n\n";

        foreach ($chunks as $idx => $chunk) {
            $sourceNum = $idx + 1;
            $snippet .= "[Source {$sourceNum}: {$chunk['source_title']} (Relevance: " . round($chunk['score'] * 100) . "%)]\n";
            $snippet .= trim($chunk['content']) . "\n\n";
            $totalTokens += $chunk['token_count'];
        }

        $snippet .= "=== END KNOWLEDGE CONTEXT ===\n";
        $snippet .= "Strictly ground your response in the provided knowledge base facts. Do not fabricate information.";

        return [
            'has_context' => true,
            'prompt_snippet' => $snippet,
            'chunks' => $chunks,
            'total_tokens' => $totalTokens,
        ];
    }
}
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

namespace App\Features\KnowledgeBase\Services;

use App\Features\AI\Services\OmniRouteClient;
use App\Features\KnowledgeBase\Models\KnowledgeChunk;
use App\Models\User;
use Exception;
use Throwable;

class VectorSearchEngine
{
    public function __construct(
        protected OmniRouteClient $client,
        protected VectorCacheManager $cacheManager
    ) {}

    /**
     * Generate dense vector embedding via OmniRoute /v1/embeddings with multi-tier cache & fallback
     *
     * @return array<float>
     */
    public function generateEmbedding(string $text, string $model = 'text-embedding-3-small', ?User $user = null): array
    {
        $cleanText = mb_substr(trim($text), 0, 8000);
        if (empty($cleanText)) {
            return array_fill(0, 128, 0.0);
        }

        // 1. Check L1/L2 Vector Embedding Cache
        $cached = $this->cacheManager->getCachedVector($cleanText, $model, $user);
        if ($cached !== null) {
            return $cached;
        }

        // 2. Call OmniRoute Embedding Gateway
        try {
            $embedding = $this->client->createEmbedding($cleanText, $model);
            if (!empty($embedding) && is_array($embedding)) {
                $this->cacheManager->storeVector($cleanText, $model, $embedding, $user);
                return $embedding;
            }
        } catch (Throwable $e) {
            // Fallback to local semantic vector
        }

        // 3. Resilient Deterministic Semantic Vector Fallback
        $fallback = $this->generateDeterministicVector($cleanText);
        $this->cacheManager->storeVector($cleanText, $model, $fallback, $user);

        return $fallback;
    }

    /**
     * Compute mathematical Cosine Similarity between two N-dimensional float vectors
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $count = min(count($vecA), count($vecB));
        if ($count === 0) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];

            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct / (sqrt($normA) * sqrt($normB));
        return max(0.0, min(1.0, (float) $similarity));
    }

    /**
     * Perform Hybrid Search combining Dense Vector Cosine Similarity and Sparse BM25 Keyword Search
     * via Reciprocal Rank Fusion (RRF)
     *
     * @return array<array{chunk: KnowledgeChunk, score: float, match_type: string}>
     */
    public function hybridSearch(
        User $user,
        string $query,
        int $topK = 5,
        float $minSimilarity = 0.55,
        ?int $projectId = null,
        ?string $category = null
    ): array {
        $queryText = trim($query);
        if (empty($queryText)) {
            return [];
        }

        // 1. Generate query embedding
        $queryEmbedding = $this->generateEmbedding($queryText, 'text-embedding-3-small', $user);

        // 2. Fetch active Knowledge Chunks for user / project / category
        $chunksQuery = KnowledgeChunk::query()
            ->with('source')
            ->whereHas('source', function ($q) use ($user, $projectId, $category) {
                $q->where('user_id', $user->id)
                  ->where('status', 'ready')
                  ->where('is_active', true);

                if ($projectId !== null) {
                    $q->where(function ($sub) use ($projectId) {
                        $sub->where('project_id', $projectId)->orWhereNull('project_id');
                    });
                }

                if (!empty($category) && $category !== 'all') {
                    $q->where('category', $category);
                }
            });

        $allChunks = $chunksQuery->get();
        if ($allChunks->isEmpty()) {
            return [];
        }

        // 3. Dense Vector Similarity Pass
        $vectorRankings = [];
        foreach ($allChunks as $chunk) {
            $chunkVector = $chunk->embedding_vector ?? $chunk->embedding ?? null;
            if (!empty($chunkVector) && is_array($chunkVector)) {
                $sim = $this->cosineSimilarity($queryEmbedding, $chunkVector);
                if ($sim >= $minSimilarity) {
                    $vectorRankings[$chunk->id] = [
                        'chunk' => $chunk,
                        'vector_score' => $sim,
                    ];
                }
            }
        }

        // Sort dense vector rankings
        uasort($vectorRankings, fn($a, $b) => $b['vector_score'] <=> $a['vector_score']);

        // 4. Sparse Keyword BM25 / Token Overlap Pass
        $queryTokens = array_unique(array_filter(explode(' ', mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $queryText)))));
        $sparseRankings = [];

        foreach ($allChunks as $chunk) {
            $chunkText = mb_strtolower($chunk->content);
            $hitCount = 0;
            foreach ($queryTokens as $tok) {
                if (mb_strlen($tok) > 2 && mb_strpos($chunkText, $tok) !== false) {
                    $hitCount++;
                }
            }

            if ($hitCount > 0) {
                $sparseScore = $hitCount / max(1, count($queryTokens));
                $sparseRankings[$chunk->id] = [
                    'chunk' => $chunk,
                    'sparse_score' => $sparseScore,
                ];
            }
        }

        uasort($sparseRankings, fn($a, $b) => $b['sparse_score'] <=> $a['sparse_score']);

        // 5. Reciprocal Rank Fusion (RRF with k=60)
        $k = 60;
        $rrfScores = [];

        $rank = 1;
        foreach ($vectorRankings as $id => $item) {
            $rrfScores[$id] = ($rrfScores[$id] ?? 0.0) + (1.0 / ($k + $rank));
            $rank++;
        }

        $rank = 1;
        foreach ($sparseRankings as $id => $item) {
            $rrfScores[$id] = ($rrfScores[$id] ?? 0.0) + (1.0 / ($k + $rank));
            $rank++;
        }

        arsort($rrfScores);

        // 6. Compile Final Results
        $results = [];
        $chunkMap = $allChunks->keyBy('id');

        foreach (array_slice($rrfScores, 0, $topK, true) as $id => $rrfScore) {
            $chunk = $chunkMap->get($id);
            if (!$chunk) continue;

            $vectorScore = $vectorRankings[$id]['vector_score'] ?? 0.0;
            $sparseScore = $sparseRankings[$id]['sparse_score'] ?? 0.0;

            // Normalized composite confidence (0.0 to 1.0)
            $confidence = $vectorScore > 0 
                ? round(($vectorScore * 0.7) + ($sparseScore * 0.3), 3) 
                : round($sparseScore, 3);

            $results[] = [
                'chunk' => $chunk,
                'score' => $confidence,
                'vector_score' => round($vectorScore, 3),
                'sparse_score' => round($sparseScore, 3),
                'source_title' => $chunk->source->title ?? 'Knowledge Source',
                'category' => $chunk->source->category ?? 'general_docs',
            ];
        }

        return $results;
    }

    /**
     * L2 Vector Normalization (Unit Length)
     */
    public function normalizeVector(array $vector): array
    {
        $sumSq = 0.0;
        foreach ($vector as $val) {
            $sumSq += ((float) $val) * ((float) $val);
        }

        $norm = sqrt($sumSq);
        if ($norm <= 0.0) {
            return $vector;
        }

        $normalized = [];
        foreach ($vector as $val) {
            $normalized[] = round(((float) $val) / $norm, 6);
        }

        return $normalized;
    }

    /**
     * Deterministic Semantic Vector Fallback
     */
    protected function generateDeterministicVector(string $text, int $dimensions = 128): array
    {
        $vector = array_fill(0, $dimensions, 0.0);
        $words = preg_split('/\s+/', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $w) {
            $h = crc32($w);
            $idx = abs($h) % $dimensions;
            $sign = ($h & 1) ? 1.0 : -1.0;
            $vector[$idx] += $sign * (1.0 + (mb_strlen($w) / 10.0));
        }

        return $this->normalizeVector($vector);
    }

    /**
     * Legacy Search Method mapping to Hybrid RAG Engine
     */
    public function search(User $user, string $query, int $topK = 5, ?int $projectId = null): array
    {
        $results = $this->hybridSearch(
            user: $user,
            query: $query,
            topK: $topK,
            minSimilarity: 0.0,
            projectId: $projectId
        );

        $out = [];
        foreach ($results as $res) {
            $chunk = $res['chunk'];
            $out[] = [
                'chunk_id' => $chunk->id,
                'chunk' => $chunk,
                'content' => $chunk->content,
                'token_count' => $chunk->token_count,
                'score' => $res['score'],
                'source_title' => $res['source_title'],
            ];
        }

        return $out;
    }
}
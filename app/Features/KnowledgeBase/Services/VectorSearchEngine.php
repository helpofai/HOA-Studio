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
use Illuminate\Support\Facades\Http;

class VectorSearchEngine
{
    public function __construct(
        protected OmniRouteClient $client,
        protected VectorCacheManager $cacheManager
    ) {}

    /**
     * Generate dense vector embedding via OmniRoute /v1/embeddings with smart cache and fallback
     *
     * @return array<float>
     */
    public function generateEmbedding(string $text, string $model = 'text-embedding-3-small', ?User $user = null): array
    {
        $cleanText = mb_substr(trim($text), 0, 8000);
        if (empty($cleanText)) {
            return array_fill(0, 128, 0.0);
        }

        // Check Vector Embedding Cache (1, 7, or 30 days)
        $cached = $this->cacheManager->getCachedVector($cleanText, $model);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $embedding = $this->client->createEmbedding($cleanText, $model);
            if (!empty($embedding)) {
                $this->cacheManager->storeVector($cleanText, $model, $embedding, $user);
                return $embedding;
            }
        } catch (Exception $e) {
            // Fallback to local semantic vector
        }

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

        return (float) max(0.0, min(1.0, $similarity));
    }

    /**
     * Perform hybrid semantic vector search and keyword matching across user chunks
     *
     * @param User $user
     * @param string $query
     * @param int $limit
     * @param int|null $projectId
     * @return array<int, array{chunk_id: int, source_id: int, source_title: string, content: string, score: float, token_count: int}>
     */
    public function search(User $user, string $query, int $limit = 5, ?int $projectId = null): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        $queryVector = $this->generateEmbedding($query);
        $queryTerms = preg_split('/\s+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY);

        // Fetch candidate chunks
        $chunksQuery = KnowledgeChunk::with('source')
            ->whereHas('source', function ($q) use ($user, $projectId) {
                $q->where('user_id', $user->id)
                  ->where('status', 'ready');

                if ($projectId) {
                    $q->where(function ($sub) use ($projectId) {
                        $sub->where('project_id', $projectId)->orWhereNull('project_id');
                    });
                }
            });

        $chunks = $chunksQuery->get();
        if ($chunks->isEmpty()) {
            return [];
        }

        $scoredResults = [];

        foreach ($chunks as $chunk) {
            $chunkVector = $chunk->embedding_vector ?? [];
            $cosine = !empty($chunkVector) ? $this->cosineSimilarity($queryVector, $chunkVector) : 0.0;

            // Keyword match ratio
            $chunkTextLower = mb_strtolower($chunk->content);
            $matches = 0;
            foreach ($queryTerms as $term) {
                if (mb_strpos($chunkTextLower, $term) !== false) {
                    $matches++;
                }
            }
            $keywordScore = !empty($queryTerms) ? ($matches / count($queryTerms)) : 0.0;

            // Hybrid Weighted Score: 75% Cosine Vector + 25% Exact Keyword Match
            $finalScore = (0.75 * $cosine) + (0.25 * $keywordScore);

            $scoredResults[] = [
                'chunk_id' => $chunk->id,
                'source_id' => $chunk->knowledge_source_id,
                'source_title' => $chunk->source->title ?? 'Knowledge Source',
                'chunk_index' => $chunk->chunk_index,
                'content' => $chunk->content,
                'score' => round($finalScore, 4),
                'cosine_similarity' => round($cosine, 4),
                'keyword_score' => round($keywordScore, 4),
                'token_count' => $chunk->token_count,
            ];
        }

        // Sort descending by final score
        usort($scoredResults, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scoredResults, 0, $limit);
    }

    /**
     * Deterministic local semantic embedding generation fallback
     */
    protected function generateDeterministicVector(string $text, int $dimensions = 128): array
    {
        $vector = array_fill(0, $dimensions, 0.0);
        $words = preg_split('/\s+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            $hash = crc32($word);
            $idx = abs($hash) % $dimensions;
            $vector[$idx] += 1.0;
        }

        // Normalize vector to unit length
        $norm = 0.0;
        foreach ($vector as $v) {
            $norm += $v * $v;
        }

        if ($norm > 0.0) {
            $sqrtNorm = sqrt($norm);
            foreach ($vector as $i => $v) {
                $vector[$i] = (float) round($v / $sqrtNorm, 6);
            }
        }

        return $vector;
    }
}
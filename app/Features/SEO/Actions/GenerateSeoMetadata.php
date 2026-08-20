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

namespace App\Features\SEO\Actions;

use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\AI\Services\OmniRouteClient;
use App\Models\User;
use Exception;

class GenerateSeoMetadata
{
    public function __construct(
        protected OmniRouteClient $client,
        protected RecordGenerationUsage $recordUsage
    ) {}

    /**
     * Generate 3 click-worthy Meta Descriptions (150-160 chars) matching focus keyword
     */
    public function generateMetaDescriptions(User $user, string $documentText, ?string $keyword = null): array
    {
        if (!$user->hasQuota(1)) {
            throw new Exception("Monthly word quota exceeded.");
        }

        $systemPrompt = "You are an elite SEO copywriter. Generate exactly 3 compelling, high-CTR meta descriptions (strictly between 145 and 160 characters each). Each meta description must include the target keyword naturally and end with a clear call to action. Return ONLY valid JSON formatted as: [\"desc1\", \"desc2\", \"desc3\"].";

        $prompt = "Target Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

        $response = $this->client->chatCompletion([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt],
        ], ['model' => 'auto', 'temperature' => 0.7]);

        $content = trim($response['content']);
        $wordsUsed = max(1, str_word_count(strip_tags($content)));

        $this->recordUsage->execute($user, [
            'words_used' => $wordsUsed,
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model_slug' => $response['model'] ?? 'omniroute',
        ]);

        $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);

        return is_array($decoded) ? array_slice($decoded, 0, 3) : [$content];
    }

    /**
     * Generate 3 high-converting SEO Title Tags
     */
    public function generateTitles(User $user, string $documentText, ?string $keyword = null): array
    {
        if (!$user->hasQuota(1)) {
            throw new Exception("Monthly word quota exceeded.");
        }

        $systemPrompt = "You are a master SEO title copywriter. Generate 3 click-magnet, search-optimized title tags (between 50 and 60 characters). Front-load the primary keyword. Return ONLY valid JSON formatted as: [\"title1\", \"title2\", \"title3\"].";

        $prompt = "Target Keyword: " . ($keyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

        $response = $this->client->chatCompletion([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt],
        ], ['model' => 'auto', 'temperature' => 0.7]);

        $content = trim($response['content']);
        $wordsUsed = max(1, str_word_count(strip_tags($content)));

        $this->recordUsage->execute($user, [
            'words_used' => $wordsUsed,
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model_slug' => $response['model'] ?? 'omniroute',
        ]);

        $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);

        return is_array($decoded) ? array_slice($decoded, 0, 3) : [$content];
    }

    /**
     * Suggest Semantic (LSI) Keywords
     */
    public function suggestKeywords(User $user, string $documentText, ?string $primaryKeyword = null): array
    {
        if (!$user->hasQuota(1)) {
            throw new Exception("Monthly word quota exceeded.");
        }

        $systemPrompt = "You are an SEO semantic search expert. Analyze the topic and primary keyword, and return a list of 8 high-relevance semantic entities, synonyms, and secondary keywords (LSI keywords) to improve topical authority. Return ONLY valid JSON formatted as: [\"keyword1\", \"keyword2\", ...].";

        $prompt = "Primary Keyword: " . ($primaryKeyword ?: 'General') . "\n\nContent Excerpt:\n" . mb_substr($documentText, 0, 1500);

        $response = $this->client->chatCompletion([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt],
        ], ['model' => 'auto', 'temperature' => 0.6]);

        $content = trim($response['content']);
        $wordsUsed = max(1, str_word_count(strip_tags($content)));

        $this->recordUsage->execute($user, [
            'words_used' => $wordsUsed,
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model_slug' => $response['model'] ?? 'omniroute',
        ]);

        $decoded = json_decode(preg_replace('/^```json|```$/m', '', $content), true);

        return is_array($decoded) ? array_slice($decoded, 0, 8) : [];
    }
}
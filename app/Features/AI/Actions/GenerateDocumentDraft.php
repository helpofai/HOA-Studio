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

namespace App\Features\AI\Actions;

use App\Features\AI\Services\OmniRouteClient;
use App\Models\User;
use Exception;

class GenerateDocumentDraft
{
    public function __construct(
        protected OmniRouteClient $client,
        protected RecordGenerationUsage $recordUsage
    ) {}

    public function execute(User $user, string $topic, array $options = []): string
    {
        if (!$user->hasQuota(100)) {
            throw new Exception("Insufficient word quota to generate a complete draft.");
        }

        $systemPrompt = 'You are a world-class long-form content creator and copywriter. Generate a comprehensive, high-quality, structured article with HTML formatting (<h2>, <h3>, <p>, <ul>, <li>, <strong>, <blockquote>). Ensure engaging hooks and actionable insights.';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Write an in-depth article about: {$topic}"],
        ];

        $response = $this->client->chatCompletion($messages, $options);
        $resultHtml = trim($response['content']);

        $wordCount = str_word_count(strip_tags($resultHtml));
        $this->recordUsage->execute($user, [
            'words_used' => $wordCount,
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model_slug' => $response['model'] ?? 'omniroute',
        ]);

        return $resultHtml;
    }
}
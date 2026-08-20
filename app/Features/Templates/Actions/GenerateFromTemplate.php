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

namespace App\Features\Templates\Actions;

use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\AI\Services\OmniRouteClient;
use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\Templates\Models\Template;
use App\Models\User;
use Exception;

class GenerateFromTemplate
{
    public function __construct(
        protected OmniRouteClient $client,
        protected RecordGenerationUsage $recordUsage
    ) {}

    public function execute(User $user, Template $template, array $inputs, ?BrandProfile $brandVoice = null, array $options = []): array
    {
        if (!$user->hasQuota(1)) {
            throw new Exception("Monthly word quota exceeded. Please upgrade your plan or wait for the next billing cycle.");
        }

        $prompt = $template->renderPrompt($inputs);
        $systemPrompt = $template->compileSystemPrompt($brandVoice);
        $model = $options['model'] ?? config('omniroute.default_model', 'auto');

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->client->chatCompletion($messages, [
            'model' => $model,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 4096,
        ]);

        $content = trim($response['content']);
        $wordsUsed = max(1, str_word_count(strip_tags($content)));

        $this->recordUsage->execute($user, [
            'words_used' => $wordsUsed,
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model_slug' => $response['model'] ?? $model,
        ]);

        return [
            'content' => $content,
            'words_used' => $wordsUsed,
            'tokens_used' => $response['total_tokens'] ?? 0,
            'model' => $response['model'] ?? $model,
            'latency_ms' => $response['latency_ms'] ?? 0,
            'cost_usd' => $response['cost_usd'] ?? 0,
        ];
    }
}
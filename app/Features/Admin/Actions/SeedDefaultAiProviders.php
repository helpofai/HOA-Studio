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

namespace App\Features\Admin\Actions;

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;

class SeedDefaultAiProviders
{
    public function execute(): void
    {
        $providers = [
            [
                'name' => 'OmniRoute Gateway',
                'slug' => 'omniroute',
                'icon' => '⚡',
                'description' => 'Unified AI Proxy Gateway v3.8.50 with multi-provider routing, load balancing, and free-tier cascades.',
                'base_url' => config('omniroute.base_url', 'http://127.0.0.1:20128'),
                'is_local' => true,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'DeepSeek V3 (OmniRoute)', 'model_id' => 'deepseek/deepseek-chat', 'context_window' => 128000, 'is_default' => true],
                    ['name' => 'Claude 3.7 Sonnet (OmniRoute)', 'model_id' => 'cc/claude-3-7-sonnet', 'context_window' => 200000],
                    ['name' => 'OpenAI GPT-4o (OmniRoute)', 'model_id' => 'openai/gpt-4o', 'context_window' => 128000],
                ],
            ],
            [
                'name' => 'DeepSeek',
                'slug' => 'deepseek',
                'icon' => '🐳',
                'description' => 'DeepSeek models via official API.',
                'base_url' => 'https://api.deepseek.com/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'DeepSeek V3', 'model_id' => 'deepseek-chat', 'context_window' => 128000],
                    ['name' => 'DeepSeek R1', 'model_id' => 'deepseek-reasoner', 'context_window' => 128000],
                ],
            ],
            [
                'name' => 'OpenAI',
                'slug' => 'openai',
                'icon' => '🤖',
                'description' => 'Official OpenAI API integration.',
                'base_url' => 'https://api.openai.com/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'GPT-4o', 'model_id' => 'gpt-4o', 'context_window' => 128000],
                    ['name' => 'GPT-4o Mini', 'model_id' => 'gpt-4o-mini', 'context_window' => 128000],
                ],
            ],
            [
                'name' => 'Anthropic',
                'slug' => 'anthropic',
                'icon' => '🧠',
                'description' => 'Claude models via Anthropic API.',
                'base_url' => 'https://api.anthropic.com',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'Claude 3.5 Sonnet', 'model_id' => 'claude-3-5-sonnet', 'context_window' => 200000],
                    ['name' => 'Claude 3 Opus', 'model_id' => 'claude-3-opus', 'context_window' => 200000],
                ],
            ],
            [
                'name' => 'Google AI',
                'slug' => 'google',
                'icon' => '🌈',
                'description' => 'Gemini models via Google AI Studio.',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'Gemini 1.5 Pro', 'model_id' => 'gemini-1.5-pro', 'context_window' => 2000000],
                    ['name' => 'Gemini 1.5 Flash', 'model_id' => 'gemini-1.5-flash', 'context_window' => 1000000],
                ],
            ],
            [
                'name' => 'Groq',
                'slug' => 'groq',
                'icon' => '⚡',
                'description' => 'Ultra-fast inference for open models.',
                'base_url' => 'https://api.groq.com/openai/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'Llama 3.1 70B', 'model_id' => 'llama-3.1-70b-versatile', 'context_window' => 131072],
                    ['name' => 'Llama 3.1 8B', 'model_id' => 'llama-3.1-8b-instant', 'context_window' => 131072],
                ],
            ],
        ];

        foreach ($providers as $providerData) {
            $models = $providerData['models'] ?? [];
            unset($providerData['models']);

            $provider = AiProvider::firstOrCreate(
                ['slug' => $providerData['slug']],
                $providerData
            );

            foreach ($models as $modelData) {
                $provider->models()->firstOrCreate(
                    ['model_id' => $modelData['model_id']],
                    array_merge($modelData, [
                        'capabilities' => ['chat', 'completion'],
                        'max_output_tokens' => 4096,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
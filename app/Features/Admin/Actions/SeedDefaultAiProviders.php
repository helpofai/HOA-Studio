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
                    ['name' => 'GLM 4 Flash (Free Tier)', 'model_id' => 'glm/glm-4-flash', 'context_window' => 128000],
                    ['name' => 'Groq Llama 3.3 70B (Fast Free)', 'model_id' => 'groq/llama-3.3-70b-versatile', 'context_window' => 128000],
                    ['name' => 'Creative Combo (Auto Fallback)', 'model_id' => 'combo:creative-pro', 'context_window' => 128000],
                ],
            ],
            [
                'name' => 'DeepSeek Direct',
                'slug' => 'deepseek',
                'icon' => '🐋',
                'description' => 'Direct API integration with DeepSeek-V3 and DeepSeek-R1 reasoning models.',
                'base_url' => 'https://api.deepseek.com/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'DeepSeek V3 Chat', 'model_id' => 'deepseek-chat', 'context_window' => 128000],
                    ['name' => 'DeepSeek R1 Reasoner', 'model_id' => 'deepseek-reasoner', 'context_window' => 128000],
                ],
            ],
            [
                'name' => 'OpenAI',
                'slug' => 'openai',
                'icon' => '🤖',
                'description' => 'Industry-standard OpenAI GPT-4o, GPT-4o mini, o1, and reasoning models.',
                'base_url' => 'https://api.openai.com/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'GPT-4o Omnimodal', 'model_id' => 'gpt-4o', 'context_window' => 128000],
                    ['name' => 'GPT-4o Mini Fast', 'model_id' => 'gpt-4o-mini', 'context_window' => 128000],
                    ['name' => 'o3-mini Reasoning', 'model_id' => 'o3-mini', 'context_window' => 200000],
                ],
            ],
            [
                'name' => 'Anthropic Claude',
                'slug' => 'anthropic',
                'icon' => '🎭',
                'description' => 'Claude 3.7 Sonnet hybrid reasoning and Claude 3.5 Haiku high-speed models.',
                'base_url' => 'https://api.anthropic.com/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'Claude 3.7 Sonnet', 'model_id' => 'claude-3-7-sonnet-20250219', 'context_window' => 200000],
                    ['name' => 'Claude 3.5 Haiku', 'model_id' => 'claude-3-5-haiku-20241022', 'context_window' => 200000],
                ],
            ],
            [
                'name' => 'Google Gemini',
                'slug' => 'gemini',
                'icon' => '✨',
                'description' => 'Google Gemini 2.5 Flash and Pro 2M token context window models.',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'Gemini 2.5 Flash', 'model_id' => 'gemini-2.5-flash', 'context_window' => 1000000],
                    ['name' => 'Gemini 2.5 Pro', 'model_id' => 'gemini-2.5-pro', 'context_window' => 2000000],
                ],
            ],
            [
                'name' => 'Groq Cloud',
                'slug' => 'groq',
                'icon' => '⚡',
                'description' => 'Ultra-low latency LPU hardware inference with high-speed open-weights models.',
                'base_url' => 'https://api.groq.com/openai/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'models' => [
                    ['name' => 'Llama 3.3 70B Versatile', 'model_id' => 'llama-3.3-70b-versatile', 'context_window' => 128000],
                    ['name' => 'Mixtral 8x7B 32k', 'model_id' => 'mixtral-8x7b-32768', 'context_window' => 32768],
                ],
            ],
            [
                'name' => 'Ollama Local Sidecar',
                'slug' => 'ollama',
                'icon' => '🦙',
                'description' => 'Local offline self-hosted AI inference server running directly on private infrastructure.',
                'base_url' => 'http://127.0.0.1:11434/v1',
                'is_local' => true,
                'is_active' => false,
                'allow_user_key' => false,
                'models' => [
                    ['name' => 'Llama 3.3 (Local)', 'model_id' => 'llama3:latest', 'context_window' => 8192],
                    ['name' => 'Mistral Nemo (Local)', 'model_id' => 'mistral:latest', 'context_window' => 8192],
                ],
            ],
        ];

        foreach ($providers as $provData) {
            $models = $provData['models'] ?? [];
            unset($provData['models']);

            $provider = AiProvider::firstOrCreate(
                ['slug' => $provData['slug']],
                $provData
            );

            foreach ($models as $m) {
                AiModel::firstOrCreate(
                    ['ai_provider_id' => $provider->id, 'model_id' => $m['model_id']],
                    array_merge($m, ['ai_provider_id' => $provider->id])
                );
            }
        }
    }
}
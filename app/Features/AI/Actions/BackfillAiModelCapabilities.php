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

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;

class BackfillAiModelCapabilities
{
    public static function run(): array
    {
        $models = AiModel::all();
        $freeCount = 0;
        $comboCount = 0;
        $reasoningCount = 0;

        foreach ($models as $m) {
            $id = strtolower($m->model_id);
            $isCombo = str_starts_with($id, 'combo:') || str_starts_with($id, 'cascade:') || str_starts_with($id, 'auto:');
            
            $isFree = str_contains($id, 'flash') || 
                      str_contains($id, 'free') || 
                      str_contains($id, 'lite') || 
                      str_starts_with($id, 'groq/') || 
                      str_starts_with($id, 'cerebras/') || 
                      str_starts_with($id, 'glm/') || 
                      str_starts_with($id, 'siliconflow/') || 
                      str_starts_with($id, 'mistral/') || 
                      str_starts_with($id, 'sambanova/') || 
                      str_starts_with($id, 'together/') || 
                      str_starts_with($id, 'cloudflare/') || 
                      str_starts_with($id, 'nebius/') || 
                      str_starts_with($id, 'kilo/');

            $supportsReasoning = str_contains($id, 'think') || 
                                 str_contains($id, 'reason') || 
                                 str_contains($id, 'r1') || 
                                 str_contains($id, 'o1') || 
                                 str_contains($id, 'o3') || 
                                 str_contains($id, 'high') || 
                                 str_contains($id, 'claude-3-7') || 
                                 str_contains($id, 'deepseek');

            $ownedBy = str_contains($m->model_id, '/') ? explode('/', $m->model_id)[0] : 'omniroute';

            $m->is_combo = $isCombo;
            $m->is_free_tier = $isFree;
            $m->supports_reasoning = $supportsReasoning;
            $m->owned_by = $ownedBy;
            $m->save();

            if ($isFree) $freeCount++;
            if ($isCombo) $comboCount++;
            if ($supportsReasoning) $reasoningCount++;
        }

        // Add 4 known OmniRoute Combo cascades if missing
        $provider = AiProvider::where('slug', 'omniroute')->first();
        if ($provider) {
            $knownCombos = [
                ['id' => 'combo:creative-pro', 'name' => 'Creative Combo (Claude 3.7 + GPT-4o + DeepSeek-V3)', 'context' => 200000, 'reasoning' => true, 'free' => false],
                ['id' => 'combo:free-tier-fast', 'name' => 'Free Tier Cascade (GLM 4 Flash + Groq Llama + Cerebras)', 'context' => 128000, 'reasoning' => false, 'free' => true],
                ['id' => 'combo:reasoning-r1', 'name' => 'Deep Reasoning Combo (DeepSeek-R1 + Claude Hybrid + o3-mini)', 'context' => 128000, 'reasoning' => true, 'free' => false],
                ['id' => 'combo:code-builder', 'name' => 'Code & Architecture Combo (Qwen 2.5 Coder + DeepSeek + Sonnet)', 'context' => 128000, 'reasoning' => true, 'free' => false],
            ];

            foreach ($knownCombos as $kc) {
                AiModel::updateOrCreate(
                    ['ai_provider_id' => $provider->id, 'model_id' => $kc['id']],
                    [
                        'name' => $kc['name'],
                        'context_window' => $kc['context'],
                        'is_free_tier' => $kc['free'],
                        'is_combo' => true,
                        'supports_reasoning' => $kc['reasoning'],
                        'owned_by' => 'omniroute',
                        'supports_streaming' => true,
                        'is_active' => true,
                    ]
                );
            }
        }

        return [
            'total' => $models->count(),
            'free' => $freeCount,
            'combo' => $comboCount,
            'reasoning' => $reasoningCount,
        ];
    }
}
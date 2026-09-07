<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Verify WordPress Handshake Action
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\WordPress\Actions;

use App\Features\AI\Models\AiModel;
use App\Features\BrandVoice\Models\BrandProfile;
use App\Models\User;

class VerifyWordPressHandshake
{
    /**
     * Executes handshake payload construction and node capability discovery.
     */
    public function execute(User $user): array
    {
        $remainingWords = max(0, (int) $user->monthly_word_quota - (int) $user->used_word_quota);
        $pct = $user->monthly_word_quota > 0
            ? min(100, round(($user->used_word_quota / $user->monthly_word_quota) * 100))
            : 0;

        $models = AiModel::where('is_active', true)
            ->with('provider:id,name,slug')
            ->select('id', 'ai_provider_id', 'name', 'model_id', 'context_window')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'model_id' => $m->model_id,
                'provider' => $m->provider?->name ?? 'Default',
                'context_window' => $m->context_window,
            ]);

        $brandVoices = BrandProfile::where('user_id', $user->id)
            ->select('id', 'name', 'tone_description', 'target_audience', 'guidelines')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'tone' => $b->tone_description,
                'audience' => $b->target_audience,
                'style_guide' => $b->guidelines,
            ]);

        return [
            'success' => true,
            'status' => 'connected',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan ?? 'Starter',
                'quota' => [
                    'monthly_limit' => (int) $user->monthly_word_quota,
                    'used_words' => (int) $user->used_word_quota,
                    'remaining_words' => $remainingWords,
                    'percentage_used' => $pct,
                ],
                'preferences' => $user->preferences ?? [],
            ],
            'available_models' => $models,
            'brand_voices' => $brandVoices,
            'server_time' => now()->toIso8601String(),
            'protocol_version' => '2.6.0',
        ];
    }
}

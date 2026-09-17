<?php

namespace Database\Seeders;

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use Illuminate\Database\Seeder;

class AntigravityProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provider = AiProvider::firstOrCreate(
            ['slug' => 'antigravity'],
            [
                'name' => 'Antigravity (Google Powered)',
                'icon' => '🌌',
                'description' => 'Google OAuth backed multi-account AI routing with automatic model quota rotation.',
                'base_url' => 'https://api.antigravity.ai/v1',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'settings' => [
                    'supports_oauth' => true,
                    'auth_type' => 'google_oauth_bearer',
                ],
            ]
        );

        $models = [
            [
                'name' => 'Antigravity Pro (Claude 3.5 Sonnet / Gemini 1.5 Pro Hybrid)',
                'model_id' => 'antigravity/pro-hybrid',
                'context_window' => 200000,
                'max_output_tokens' => 8192,
                'supports_streaming' => true,
                'supports_reasoning' => true,
                'supports_vision' => true,
                'supports_tools' => true,
                'supports_json' => true,
                'is_free_tier' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Antigravity Flash (Ultra Fast / Cost Optimized)',
                'model_id' => 'antigravity/flash-fast',
                'context_window' => 1000000,
                'max_output_tokens' => 8192,
                'supports_streaming' => true,
                'supports_reasoning' => false,
                'supports_vision' => true,
                'supports_tools' => true,
                'supports_json' => true,
                'is_free_tier' => true,
                'is_active' => true,
            ],
        ];

        foreach ($models as $modelData) {
            AiModel::firstOrCreate(
                ['model_id' => $modelData['model_id']],
                array_merge($modelData, ['ai_provider_id' => $provider->id])
            );
        }
    }
}

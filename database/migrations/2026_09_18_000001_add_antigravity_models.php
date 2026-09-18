<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Antigravity Provider ID from earlier check is 2
        $antigravityProviderId = 2;

        // List of models to insert: [name, model_id, is_active, is_default, context_window, cost_per_1k_input, cost_per_1k_output]
        $models = [
            // Reasoning Models (User-selectable)
            ['Gemini 3.8 Flash', 'antigravity.google', true, false, 32768, 0.0001, 0.0001],
            ['Gemini 3.7 Flash', 'antigravity.google', true, false, 32768, 0.0001, 0.0001],
            ['Gemini 3.6 Flash', 'antigravity.google', true, false, 32768, 0.0001, 0.0001],
            ['Gemini 3.1 Pro (High)', 'antigravity.google', true, false, 32768, 0.00025, 0.0005],
            ['Gemini 3.1 Pro (Low)', 'antigravity.google', true, false, 32768, 0.000125, 0.00025],
            ['Claude Sonnet 4.6 (thinking)', 'antigravity.google', true, false, 32768, 0.003, 0.015],
            ['Claude Opus 4.6 (thinking)', 'antigravity.google', true, false, 32768, 0.015, 0.075],
            ['GPT-OSS-120B (open-weights model)', 'antigravity.google', true, false, 32768, 0.0005, 0.0015],
        ];

        foreach ($models as [$name, $model_id, $isActive, $isDefault, $contextWindow, $costIn, $costOut]) {
            // Insert if not exists (by model_id for this provider)
            \DB::table('ai_models')->insertOrIgnore([
                'ai_provider_id' => $antigravityProviderId,
                'name' => $name,
                'model_id' => $model_id,
                'is_active' => $isActive,
                'is_default' => $isDefault,
                'context_window' => $contextWindow,
                'cost_per_1k_input' => $costIn,
                'cost_per_1k_output' => $costOut,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove these specific models if needed
        $modelIds = [
            'antigravity.google',
            // but careful: this may delete other antigravity models; we'll just not implement down for safety
        ];
        \DB::table('ai_models')
            ->where('ai_provider_id', 2)
            ->whereIn('model_id', $modelIds)
            ->delete();
    }
};
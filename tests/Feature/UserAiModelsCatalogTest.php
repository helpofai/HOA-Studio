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

namespace Tests\Feature;

use App\Features\Admin\Actions\SeedDefaultAiProviders;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Models\User;
use App\Features\Auth\Models\UserApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class UserAiModelsCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        app(SeedDefaultAiProviders::class)->execute();

        $this->user = User::factory()->create([
            'role' => 'user',
            'plan' => 'Pro',
            'monthly_word_quota' => 100000,
            'used_word_quota' => 25000,
        ]);
    }

    public function test_user_can_access_ai_models_catalog_page()
    {
        $response = $this->actingAs($this->user)->get(route('ai-models.index'));
        $response->assertStatus(200);
        $response->assertSee('AI Providers');
        $response->assertSee('OmniRoute AI Model Catalog');
    }

    public function test_user_can_search_and_filter_models_in_catalog()
    {
        $provider = AiProvider::where('slug', 'openai')->first();
        AiModel::create([
            'ai_provider_id' => $provider->id,
            'model_id' => 'gpt-4o-custom-search',
            'name' => 'GPT-4o Custom Search',
            'context_window' => 128000,
            'is_active' => true,
            'is_free_tier' => false,
        ]);

        Livewire::actingAs($this->user)
            ->test('App\Features\AI\Livewire\UserAiModelsPage')
            ->set('search', 'GPT-4o Custom Search')
            ->assertSee('GPT-4o Custom Search')
            ->set('search', 'non-existent-xyz-model')
            ->assertSee('No models found matching your search filters.');
    }

    public function test_user_can_ping_model_health_check()
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'PONG']],
                ],
            ], 200),
            '*/v1/models' => Http::response([
                'data' => [
                    ['id' => 'gpt-4o'],
                ],
            ], 200),
        ]);

        $model = AiModel::first();

        Livewire::actingAs($this->user)
            ->test('App\Features\AI\Livewire\UserAiModelsPage')
            ->call('pingModel', $model->id)
            ->assertSee('online & responsive');

        $this->assertEquals('healthy', $model->fresh()->last_test_status);
    }

    public function test_user_can_save_and_manage_byok_keys_from_ai_models_catalog()
    {
        $provider = AiProvider::where('slug', 'deepseek')->first();
        $provider->allow_user_key = true;
        $provider->save();

        Livewire::actingAs($this->user)
            ->test('App\Features\AI\Livewire\UserAiModelsPage')
            ->set('byok_provider', 'deepseek')
            ->set('byok_api_key', 'sk-ds-user-test-token-9988')
            ->call('saveApiKey');

        $this->assertDatabaseHas('user_api_keys', [
            'user_id' => $this->user->id,
            'provider_slug' => 'deepseek',
        ]);
    }
}

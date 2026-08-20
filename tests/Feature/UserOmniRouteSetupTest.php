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

class UserOmniRouteSetupTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        app(SeedDefaultAiProviders::class)->execute();

        $this->user = User::factory()->create([
            'role' => 'user',
            'plan' => 'Starter',
            'monthly_word_quota' => 15000,
            'used_word_quota' => 500,
        ]);
    }

    public function test_user_can_access_omniroute_gateway_setup_page()
    {
        Http::fake([
            '*' => Http::response(['data' => [['id' => 'gpt-4o']]], 200),
        ]);

        $response = $this->actingAs($this->user)->get(route('ai-models.omniroute'));
        $response->assertStatus(200);
        $response->assertSee('OmniRoute Gateway Hub');
    }

    public function test_user_can_save_personal_omniroute_api_key_and_custom_url()
    {
        Http::fake([
            '*' => Http::response(['data' => [['id' => 'gpt-4o']]], 200),
        ]);

        Livewire::actingAs($this->user)
            ->test('App\Features\AI\Livewire\UserOmniRouteSetupPage')
            ->set('user_api_key', 'sk-or-v1-my-personal-secret-key')
            ->set('user_custom_url', 'http://127.0.0.1:20128/v1')
            ->call('saveUserKey');

        $this->assertDatabaseHas('user_api_keys', [
            'user_id' => $this->user->id,
            'provider_slug' => 'omniroute',
            'custom_base_url' => 'http://127.0.0.1:20128/v1',
        ]);
    }

    public function test_user_can_remove_personal_omniroute_key()
    {
        Http::fake([
            '*' => Http::response(['data' => [['id' => 'gpt-4o']]], 200),
        ]);

        UserApiKey::create([
            'user_id' => $this->user->id,
            'provider_slug' => 'omniroute',
            'api_key' => 'sk-or-v1-old-key',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test('App\Features\AI\Livewire\UserOmniRouteSetupPage')
            ->call('removeUserKey');

        $this->assertDatabaseMissing('user_api_keys', [
            'user_id' => $this->user->id,
            'provider_slug' => 'omniroute',
        ]);
    }
}

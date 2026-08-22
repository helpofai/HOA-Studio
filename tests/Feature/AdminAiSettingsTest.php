<?php

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminAiSettingsPage;
use App\Features\Admin\Livewire\AdminOmniRouteSetupPage;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAiSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(\App\Features\Admin\Actions\SeedDefaultAiProviders::class)->execute();
    }

    public function test_admin_can_view_ai_settings_hub(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(AdminAiSettingsPage::class)
            ->assertStatus(200)
            ->assertSee('AI Providers')
            ->assertSee('OmniRoute Gateway')
            ->assertSee('DeepSeek')
            ->assertSee('OpenAI')
            ->assertSee('Anthropic');
    }

    public function test_standard_users_cannot_access_ai_settings(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $this->get('/admin/ai-settings')->assertStatus(403);
        $this->get('/admin/ai-settings/omniroute')->assertStatus(403);
    }

    public function test_admin_can_toggle_provider_active_and_byok_policy(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $provider = AiProvider::firstOrCreate(
            ['slug' => 'test-ai'],
            [
                'name' => 'Test AI Cloud',
                'slug' => 'test-ai',
                'is_active' => true,
                'allow_user_key' => true,
            ]
        );

        Livewire::test(AdminAiSettingsPage::class)
            ->call('toggleProviderActive', $provider->id);

        $this->assertFalse($provider->fresh()->is_active);

        Livewire::test(AdminAiSettingsPage::class)
            ->call('toggleAllowUserKey', $provider->id);

        $this->assertFalse($provider->fresh()->allow_user_key);
    }

    public function test_admin_can_save_omniroute_configuration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(AdminOmniRouteSetupPage::class)
            ->set('base_url', 'http://localhost:20128/v1')
            ->set('api_key', 'omni-secret-key-123')
            ->set('default_model', 'combo:creative-pro')
            ->set('compression_mode', 'engine:rtk')
            ->set('allow_user_key', true)
            ->call('saveConfiguration');

        $this->assertDatabaseHas('ai_providers', [
            'slug' => 'omniroute',
            'base_url' => 'http://localhost:20128/v1',
            'api_key_encrypted' => 'omni-secret-key-123',
            'allow_user_key' => true,
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'omniroute_base_url',
            'value' => 'http://localhost:20128/v1',
        ]);
    }

    public function test_omniroute_test_connection_syncs_live_models(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        AiProvider::firstOrCreate(['slug' => 'omniroute'], [
            'name' => 'OmniRoute Gateway',
            'base_url' => 'http://localhost:20128/v1',
            'is_local' => true,
        ]);

        Http::fake([
            'http://127.0.0.1:20128/v1/models' => Http::response([
                'object' => 'list',
                'data' => [
                    ['id' => 'deepseek/deepseek-chat', 'name' => 'DeepSeek V3', 'context_window' => 128000],
                    ['id' => 'cc/claude-3-7-sonnet', 'name' => 'Claude 3.7 Sonnet', 'context_window' => 200000],
                    ['id' => 'combo:free-tier-fast', 'name' => 'Free Tier Cascade', 'context_window' => 128000],
                ],
            ], 200),
        ]);

        Livewire::test(AdminOmniRouteSetupPage::class)
            ->set('base_url', 'http://localhost:20128/v1')
            ->set('api_key', 'test-key')
            ->call('testConnectionAndSyncModels')
            ->assertSet('connectionStatus', true);

        $this->assertDatabaseHas('ai_models', [
            'model_id' => 'deepseek/deepseek-chat',
            'name' => 'DeepSeek V3',
        ]);

        $this->assertDatabaseHas('ai_models', [
            'model_id' => 'combo:free-tier-fast',
            'is_combo' => true,
        ]);
    }

    public function test_omniroute_model_filtering_searching_and_default_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $provider = AiProvider::firstOrCreate(['slug' => 'omniroute'], [
            'name' => 'OmniRoute Gateway',
            'base_url' => 'http://localhost:20128/v1',
            'is_local' => true,
        ]);

        $m1 = AiModel::create([
            'ai_provider_id' => $provider->id,
            'name' => 'DeepSeek V3 Chat',
            'model_id' => 'deepseek/deepseek-chat',
            'is_active' => true,
        ]);

        $m2 = AiModel::create([
            'ai_provider_id' => $provider->id,
            'name' => 'Claude 3.7 Sonnet',
            'model_id' => 'cc/claude-3-7-sonnet',
            'is_active' => false,
        ]);

        $m3 = AiModel::create([
            'ai_provider_id' => $provider->id,
            'name' => 'Creative Combo Cascade',
            'model_id' => 'combo:creative-pro',
            'is_active' => true,
        ]);

        // Search test: Search for 'Creative' -> Should only show Creative Combo Cascade card
        Livewire::test(AdminOmniRouteSetupPage::class)
            ->set('modelSearch', 'Creative')
            ->assertSee('Creative Combo Cascade')
            ->assertDontSee('DeepSeek V3 Chat');

        // Status filter: Offline only -> Should show Claude 3.7 Sonnet card, not DeepSeek V3 Chat card
        Livewire::test(AdminOmniRouteSetupPage::class)
            ->set('modelStatusFilter', 'offline')
            ->assertSee('Claude 3.7 Sonnet')
            ->assertDontSee('DeepSeek V3 Chat');

        // Toggle model status
        Livewire::test(AdminOmniRouteSetupPage::class)
            ->call('toggleModelStatus', $m2->id);

        $this->assertTrue($m2->fresh()->is_active);

        // Set default routing model
        Livewire::test(AdminOmniRouteSetupPage::class)
            ->call('setDefaultRoutingModel', 'combo:creative-pro');

        $this->assertEquals('combo:creative-pro', $provider->fresh()->settings['default_model']);
    }

    public function test_url_resolver_supports_both_127_0_0_1_and_localhost_with_and_without_v1(): void
    {
        // 1. Without /v1 and with localhost
        $r1 = \App\Features\AI\Services\OmniRouteUrlResolver::resolve('http://localhost:20128');
        $this->assertEquals('http://localhost:20128/v1', $r1['openai_base']);
        $this->assertEquals('http://localhost:20128', $r1['root_url']);
        $this->assertEquals('http://127.0.0.1:20128/v1/chat/completions', $r1['chat_completions_endpoint']);
        $this->assertEquals('http://127.0.0.1:20128/v1/models', $r1['models_endpoint']);
        $this->assertEquals('http://127.0.0.1:20128/api/combos', $r1['combos_endpoint']);

        // 2. With 127.0.0.1 and /v1
        $r2 = \App\Features\AI\Services\OmniRouteUrlResolver::resolve('http://127.0.0.1:20128/v1');
        $this->assertEquals('http://127.0.0.1:20128/v1', $r2['openai_base']);
        $this->assertEquals('http://127.0.0.1:20128', $r2['root_url']);
        $this->assertEquals('http://127.0.0.1:20128/v1/models', $r2['models_endpoint']);

        // 3. With trailing slash http://localhost:20128/v1/
        $r3 = \App\Features\AI\Services\OmniRouteUrlResolver::resolve('http://localhost:20128/v1/');
        $this->assertEquals('http://localhost:20128/v1', $r3['openai_base']);
        $this->assertEquals('http://localhost:20128', $r3['root_url']);
    }
}
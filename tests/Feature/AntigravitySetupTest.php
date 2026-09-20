<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntigravitySetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_antigravity_settings_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.ai-settings.antigravity'));

        $response->assertStatus(200);
        $response->assertSee('Antigravity Admin');
    }

    public function test_non_admin_cannot_access_antigravity_settings_page()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.ai-settings.antigravity'));

        $response->assertStatus(403);
    }

    public function test_oauth_redirect_route_resolves_controller()
    {
        $user = User::factory()->create();

        // Simulate admin configuring Google Client ID in settings
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            'key' => 'google_client_id',
            'value' => 'custom-google-client-id-12345.apps.googleusercontent.com',
            'type' => 'social_auth',
            'group' => 'auth',
        ]);
        config(['services.google.client_id' => 'custom-google-client-id-12345.apps.googleusercontent.com']);

        $response = $this->actingAs($user)->get(route('oauth.antigravity.redirect'));

        // Should redirect to Google OAuth URL (302 redirect) containing the client_id
        $response->assertStatus(302);
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com/o/oauth2/v2/auth', $targetUrl);
        $this->assertStringContainsString('client_id=custom-google-client-id-12345.apps.googleusercontent.com', $targetUrl);
        $this->assertStringContainsString('redirect_uri=', $targetUrl);
    }

    public function test_user_can_access_antigravity_models_page_and_view_catalog()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ai-models.antigravity'));

        $response->assertStatus(200);
        $response->assertSee('Antigravity Gateway');
        $response->assertSee('Linked Google Accounts');
    }

    public function test_user_with_linked_account_sees_resync_and_test_buttons()
    {
        $user = User::factory()->create();
        \App\Features\Antigravity\Models\AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'test@google.com',
            'google_oauth_id' => '123456789',
            'google_oauth_token' => 'dummy_token',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('ai-models.antigravity'));

        $response->assertStatus(200);
        $response->assertSee('Resync Models');
        $response->assertSee('Test Visible Models');
    }

    public function test_resync_models_action_populates_real_google_models()
    {
        $user = User::factory()->create();
        $account = \App\Features\Antigravity\Models\AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'test@google.com',
            'google_oauth_id' => '123456789',
            'google_oauth_token' => 'dummy_token',
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models' => \Illuminate\Support\Facades\Http::response([
                'models' => [
                    [
                        'name' => 'models/gemini-1.5-pro',
                        'displayName' => 'Gemini 1.5 Pro',
                        'inputTokenLimit' => 2097152,
                        'outputTokenLimit' => 8192,
                        'supportedGenerationMethods' => ['generateContent', 'countTokens'],
                    ],
                    [
                        'name' => 'models/gemini-1.5-flash',
                        'displayName' => 'Gemini 1.5 Flash',
                        'inputTokenLimit' => 1048576,
                        'outputTokenLimit' => 8192,
                        'supportedGenerationMethods' => ['generateContent', 'countTokens'],
                    ],
                    [
                        'name' => 'models/text-embedding-004',
                        'displayName' => 'Text Embedding 004',
                        'supportedGenerationMethods' => ['embedContent'],
                    ]
                ]
            ], 200),
        ]);

        $manager = app(\App\Features\Antigravity\Services\AntigravityAccountManager::class);
        $synced = $manager->syncAntigravityModels($user);

        $this->assertEquals(2, $synced);
        $this->assertDatabaseHas('ai_models', [
            'model_id' => 'antigravity/gemini-1.5-pro',
            'name' => 'Gemini 1.5 Pro',
            'context_window' => 2097152,
        ]);
        $this->assertDatabaseHas('ai_models', [
            'model_id' => 'antigravity/gemini-1.5-flash',
            'name' => 'Gemini 1.5 Flash',
            'context_window' => 1048576,
        ]);
        // Embedding model should be excluded
        $this->assertDatabaseMissing('ai_models', [
            'model_id' => 'antigravity/text-embedding-004',
        ]);
    }

    public function test_user_can_link_antigravity_cli_token_manually()
    {
        $user = User::factory()->create();

        \Illuminate\Support\Facades\Http::fake([
            'https://www.googleapis.com/oauth2/v3/userinfo' => \Illuminate\Support\Facades\Http::response([
                'sub' => 'cli_google_sub_123',
                'email' => 'cli-user@antigravity.test',
            ], 200),
            'https://generativelanguage.googleapis.com/v1beta/models' => \Illuminate\Support\Facades\Http::response([
                'models' => [],
            ], 200),
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Features\Antigravity\Livewire\UserAntigravityModelsPage::class)
            ->set('manualEmail', 'cli-user@antigravity.test')
            ->set('manualToken', 'ya29.test_cli_access_token_1234567890')
            ->call('saveManualAccount')
            ->assertHasNoErrors()
            ->assertSet('showManualModal', false);

        $this->assertDatabaseHas('antigravity_accounts', [
            'user_id' => $user->id,
            'email' => 'cli-user@antigravity.test',
            'google_oauth_token' => 'ya29.test_cli_access_token_1234567890',
            'is_active' => true,
        ]);
    }
}

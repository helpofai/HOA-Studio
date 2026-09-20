<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Execution Test
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

namespace Tests\Feature;

use App\Features\AI\Actions\TransformText;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Services\AntigravityGatewayService;
use App\Features\Antigravity\Services\AntigravityHealthMonitor;
use App\Features\Antigravity\Services\AntigravityPayloadAdapter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AntigravityExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payload_adapter_normalizes_openai_messages_to_google_format()
    {
        $adapter = new AntigravityPayloadAdapter();

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert editor.'],
            ['role' => 'user', 'content' => 'Please polish this sentence.'],
            ['role' => 'assistant', 'content' => 'Here is the draft.'],
            ['role' => 'user', 'content' => 'Make it more concise.'],
        ];

        $payload = $adapter->formatToGooglePayload($messages, [
            'temperature' => 0.6,
            'max_tokens' => 2048,
        ]);

        $this->assertArrayHasKey('systemInstruction', $payload);
        $this->assertEquals('You are an expert editor.', $payload['systemInstruction']['parts'][0]['text']);
        $this->assertCount(3, $payload['contents']);
        $this->assertEquals('user', $payload['contents'][0]['role']);
        $this->assertEquals('model', $payload['contents'][1]['role']);
        $this->assertEquals(0.6, $payload['generationConfig']['temperature']);
        $this->assertEquals(2048, $payload['generationConfig']['maxOutputTokens']);
    }

    public function test_antigravity_gateway_service_chat_completion_success()
    {
        $user = User::factory()->create([
            'monthly_word_quota' => 100000,
            'used_word_quota' => 0,
        ]);

        $provider = AiProvider::create([
            'slug' => 'antigravity',
            'name' => 'Antigravity',
            'is_active' => true,
        ]);

        $model = AiModel::create([
            'model_id' => 'antigravity/gemini-1.5-pro',
            'ai_provider_id' => $provider->id,
            'name' => 'Gemini 1.5 Pro',
            'is_active' => true,
        ]);

        AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'author@google.com',
            'google_oauth_id' => 'oauth_998877',
            'google_oauth_token' => 'valid_bearer_token',
            'is_active' => true,
            'is_quota_exhausted' => false,
            'priority_order' => 1,
        ]);

        Http::fake([
            'https://cloudcode-pa.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'This is high performance generated text from Gemini API.'],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 15,
                    'candidatesTokenCount' => 10,
                    'totalTokenCount' => 25,
                ],
            ], 200),
            'https://generativelanguage.googleapis.com/*' => Http::response([], 200),
        ]);

        $service = app(AntigravityGatewayService::class);
        $result = $service->chatCompletion($user, [
            ['role' => 'user', 'content' => 'Hello Gemini!'],
        ], ['model' => 'antigravity/gemini-1.5-pro']);

        \Log::debug('Result content: '.$result['content']);
        $this->assertStringContainsString('This is high performance generated text', $result['content']);
        $this->assertEquals('antigravity/gemini-1.5-pro', $result['model']);
        $this->assertEquals(25, $result['total_tokens']);

        $this->assertDatabaseHas('antigravity_telemetry', [
            'user_id' => $user->id,
            'tokens_used' => 25,
            'status_code' => 200,
        ]);
    }

    public function test_antigravity_gateway_service_auto_rotates_on_429()
    {
        $user = User::factory()->create();

        // Ensure the Antigravity provider and model exist
        $provider = AiProvider::create([
            'slug' => 'antigravity',
            'name' => 'Antigravity',
            'is_active' => true,
        ]);

        AiModel::create([
            'model_id' => 'antigravity/gemini-1.5-flash',
            'ai_provider_id' => $provider->id,
            'name' => 'Gemini 1.5 Flash',
            'is_active' => true,
        ]);

        $account1 = AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'primary@google.com',
            'google_oauth_id' => 'oauth_1',
            'project_id' => 'projects/test-proj-1',
            'google_oauth_token' => 'token_primary',
            'is_active' => true,
            'is_quota_exhausted' => false,
            'priority_order' => 1,
        ]);

        $account2 = AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'backup@google.com',
            'google_oauth_id' => 'oauth_2',
            'project_id' => 'projects/test-proj-2',
            'google_oauth_token' => 'token_backup',
            'is_active' => true,
            'is_quota_exhausted' => false,
            'priority_order' => 2,
        ]);

        Http::fake([
            'https://cloudcode-pa.googleapis.com/v1internal:generateContent*' => Http::sequence()
                ->push(['error' => ['code' => 429, 'message' => 'Resource exhausted']], 429)
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Backup account succeeded!'],
                                ],
                            ],
                        ],
                    ],
                    'usageMetadata' => ['promptTokenCount' => 5, 'candidatesTokenCount' => 4, 'totalTokenCount' => 9],
                ], 200),
            'https://cloudcode-pa.googleapis.com/*' => Http::response(['cloudaicompanionProject' => ['id' => 'projects/test-proj']], 200),
            'https://generativelanguage.googleapis.com/*' => Http::response([], 200),
        ]);

        $service = app(AntigravityGatewayService::class);
        $result = $service->chatCompletion($user, [
            ['role' => 'user', 'content' => 'Test rotation'],
        ], ['model' => 'antigravity/gemini-1.5-flash']);

        $this->assertStringContainsString('Backup account succeeded', $result['content']);
        $account1->refresh();
        $this->assertTrue($account1->is_quota_exhausted);
    }

    public function test_transform_text_action_routes_antigravity_model()
    {
        $user = User::factory()->create([
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
        ]);

        $provider = AiProvider::create([
            'slug' => 'antigravity',
            'name' => 'Antigravity',
            'is_active' => true,
        ]);

        AiModel::create([
            'model_id' => 'antigravity/gemini-1.5-pro',
            'ai_provider_id' => $provider->id,
            'name' => 'Gemini 1.5 Pro',
            'is_active' => true,
        ]);

        AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'editor@google.com',
            'google_oauth_id' => 'oauth_777',
            'google_oauth_token' => 'token_editor',
            'is_active' => true,
            'is_quota_exhausted' => false,
            'priority_order' => 1,
        ]);

        Http::fake([
            'https://cloudcode-pa.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Polished professional sentence.'],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 20,
                    'candidatesTokenCount' => 5,
                    'totalTokenCount' => 25,
                ],
            ], 200),
            'https://generativelanguage.googleapis.com/*' => Http::response([], 200),
        ]);

        $action = app(TransformText::class);
        $result = $action->execute($user, 'Draft sentence.', 'polish', [
            'model' => 'antigravity/gemini-1.5-pro',
        ]);

        $this->assertEquals('Polished professional sentence.', $result);
    }

    public function test_admin_health_probe_returns_probe_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $provider = AiProvider::create([
            'slug' => 'antigravity',
            'name' => 'Antigravity',
            'is_active' => true,
        ]);

        AiModel::create([
            'model_id' => 'antigravity/gemini-1.5-flash',
            'ai_provider_id' => $provider->id,
            'name' => 'Gemini 1.5 Flash',
            'is_active' => true,
        ]);

        AntigravityAccount::create([
            'user_id' => $admin->id,
            'email' => 'admin@google.com',
            'google_oauth_id' => 'oauth_admin',
            'google_oauth_token' => 'token_admin',
            'is_active' => true,
        ]);

        Http::fake([
            'https://cloudcode-pa.googleapis.com/*' => Http::response([
                'models' => [
                    'gemini-1.5-flash' => [
                        'displayName' => 'Gemini 1.5 Flash',
                        'inputTokenLimit' => 1048576,
                        'outputTokenLimit' => 8192,
                    ],
                ],
            ], 200),
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'models' => [
                    ['name' => 'models/gemini-1.5-flash', 'supportedGenerationMethods' => ['generateContent']],
                ],
            ], 200),
        ]);

        $monitor = app(AntigravityHealthMonitor::class);
        $probe = $monitor->probe($admin);

        $this->assertEquals('healthy', $probe['status']);
        $this->assertEquals(1, $probe['models_count']);
    }

    public function test_bootstrap_account_project_discovers_and_onboards_cloud_code_project()
    {
        $user = User::factory()->create();
        $account = AntigravityAccount::create([
            'user_id' => $user->id,
            'email' => 'developer@gmail.com',
            'google_oauth_id' => '1029384756',
            'google_oauth_token' => 'oauth_access_token_123',
            'is_active' => true,
        ]);

        Http::fake([
            'https://cloudcode-pa.googleapis.com/v1internal:loadCodeAssist' => Http::sequence()
                ->push(['cloudaicompanionProject' => null], 200)
                ->push([
                    'cloudaicompanionProject' => ['id' => 'projects/cloudaicompanion-prod-123'],
                    'currentTier' => ['id' => 'standard-tier'],
                ], 200),
            'https://cloudcode-pa.googleapis.com/v1internal:onboardUser' => Http::response(['done' => true], 200),
        ]);

        $manager = app(\App\Features\Antigravity\Services\AntigravityAccountManager::class);
        $projectId = $manager->bootstrapAccountProject($account);

        $this->assertEquals('projects/cloudaicompanion-prod-123', $projectId);
        $account->refresh();
        $this->assertEquals('projects/cloudaicompanion-prod-123', $account->project_id);
    }
}

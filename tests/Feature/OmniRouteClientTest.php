<?php

namespace Tests\Feature;

use App\Features\AI\Actions\TransformText;
use App\Features\AI\Services\OmniRouteClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OmniRouteClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_omniroute_client_sends_headers_and_parses_telemetry(): void
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-test-123',
                'model' => 'deepseek/deepseek-chat',
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Refined text with elevated prose.',
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 20,
                    'completion_tokens' => 10,
                    'total_tokens' => 30,
                ],
            ], 200, [
                'X-OmniRoute-Response-Cost' => '0.0001500000',
                'X-OmniRoute-Decision' => 'strategy=auto; provider=deepseek; latency_ms=210',
                'X-OmniRoute-Latency-Ms' => '210',
                'X-OmniRoute-Cache' => 'MISS',
            ]),
        ]);

        $client = new OmniRouteClient();
        $response = $client->chatCompletion([
            ['role' => 'user', 'content' => 'Polish this content.'],
        ], ['model' => 'deepseek/deepseek-chat']);

        $this->assertEquals('Refined text with elevated prose.', $response['content']);
        $this->assertEquals('deepseek/deepseek-chat', $response['model']);
        $this->assertEquals(30, $response['total_tokens']);
        $this->assertEquals(0.00015, $response['cost_usd']);
        $this->assertEquals(210, $response['latency_ms']);
        $this->assertFalse($response['cache_hit']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-OmniRoute-Session-Id') &&
                   $request->hasHeader('X-Request-Id') &&
                   $request->hasHeader('x-omniroute-compression');
        });
    }

    public function test_transform_text_action_reduces_user_quota_and_logs_usage(): void
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'model' => 'deepseek/deepseek-chat',
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Short concise output.',
                        ],
                    ],
                ],
                'usage' => [
                    'total_tokens' => 15,
                ],
            ], 200),
        ]);

        $user = User::factory()->create([
            'monthly_word_quota' => 10000,
            'used_word_quota' => 0,
        ]);

        $action = app(TransformText::class);
        $result = $action->execute($user, 'Original verbose text', 'shorten');

        $this->assertEquals('Short concise output.', $result);

        $user->refresh();
        $this->assertEquals(3, $user->used_word_quota); // 'Short concise output.' = 3 words

        $this->assertDatabaseHas('generation_usage', [
            'user_id' => $user->id,
            'words_used' => 3,
            'model_slug' => 'deepseek/deepseek-chat',
        ]);
    }

    public function test_transform_api_endpoint_returns_json_response(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'model' => 'cc/claude-3-7-sonnet',
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Polished professional sentence.',
                        ],
                    ],
                ],
                'usage' => ['total_tokens' => 12],
            ], 200),
        ]);

        $user = User::factory()->create([
            'monthly_word_quota' => 5000,
            'used_word_quota' => 0,
        ]);

        $response = $this->actingAs($user)->postJson(route('ai.transform'), [
            'text' => 'Draft raw sentence',
            'type' => 'polish',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'result' => 'Polished professional sentence.',
                'word_count' => 3,
            ]);
    }

    public function test_transform_fails_when_user_has_no_quota(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $user = User::factory()->create([
            'monthly_word_quota' => 1000,
            'used_word_quota' => 1000, // No words left
        ]);

        $response = $this->actingAs($user)->postJson(route('ai.transform'), [
            'text' => 'Draft raw sentence',
            'type' => 'polish',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
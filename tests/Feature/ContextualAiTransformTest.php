<?php

namespace Tests\Feature;

use App\Features\AI\Models\AiProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContextualAiTransformTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
        ]);

        AiProvider::create([
            'name' => 'OmniRoute Gateway',
            'slug' => 'omniroute',
            'base_url' => 'http://localhost:20128/v1',
            'api_key_encrypted' => 'test-key',
            'is_local' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_execute_polish_transformation(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'id' => 'chatcmpl-test-123',
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Artificial intelligence is revolutionizing modern creative workflows with incredible speed.',
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 30,
                    'completion_tokens' => 15,
                    'total_tokens' => 45,
                ],
                'model' => 'cc/claude-3-7-sonnet',
            ], 200, [
                'X-OmniRoute-Model' => 'cc/claude-3-7-sonnet',
            ]),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'ai is change creative work real fast',
            'type' => 'polish',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'result' => 'Artificial intelligence is revolutionizing modern creative workflows with incredible speed.',
            'type' => 'polish',
        ]);

        $this->assertDatabaseHas('generation_usage', [
            'user_id' => $this->user->id,
            'model_slug' => 'cc/claude-3-7-sonnet',
        ]);
    }

    public function test_user_can_execute_tone_transformations(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Please find attached our strategic quarterly report for your review.',
                        ],
                    ],
                ],
                'usage' => ['total_tokens' => 20],
                'model' => 'openai/gpt-4o',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'hey look at this quarterly report',
            'type' => 'tone:professional',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'result' => 'Please find attached our strategic quarterly report for your review.',
        ]);
    }

    public function test_user_can_execute_custom_instruction_transform(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Step 1: Planning. Step 2: Implementation.',
                        ],
                    ],
                ],
                'usage' => ['total_tokens' => 25],
                'model' => 'groq/llama-3.3-70b-versatile',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'First do this then do that then finish',
            'type' => 'custom',
            'custom_instruction' => 'Format as numbered steps',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'result' => 'Step 1: Planning. Step 2: Implementation.',
        ]);
    }

    public function test_user_exceeding_quota_cannot_transform(): void
    {
        $this->user->update([
            'monthly_word_quota' => 100,
            'used_word_quota' => 100,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'Some text',
            'type' => 'shorten',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
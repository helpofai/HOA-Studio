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

        $this->withoutMiddleware();

        $this->user = User::factory()->create([
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
        ]);

        AiProvider::firstOrCreate(
            ['slug' => 'omniroute'],
            [
                'name' => 'OmniRoute Gateway',
                'slug' => 'omniroute',
                'base_url' => 'http://localhost:20128/v1',
                'api_key_encrypted' => 'test-key',
                'is_local' => true,
                'is_active' => true,
            ]
        );
    }

    public function test_user_can_execute_polish_transformation(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
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

    public function test_content_writer_brain_surgical_execution_with_full_document_memory(): void
    {
        Http::fake([
            '*/chat/completions' => function ($request) {
                $payload = json_decode($request->body(), true);
                $systemPrompt = $payload['messages'][0]['content'] ?? '';
                $userContent = $payload['messages'][1]['content'] ?? '';

                // Verify Brain & Memory contains Document Context, Preceding, and Following text
                $this->assertStringContainsString('CONTENT WRITER BRAIN & MEMORY', $systemPrompt);
                $this->assertStringContainsString('The Complete Guide to Organic Growth', $systemPrompt);
                $this->assertStringContainsString('CRITICAL SURGICAL', $systemPrompt);

                // Verify target marked content is shielded
                $this->assertStringContainsString('<target_marked_content>', $userContent);
                $this->assertStringContainsString('This is the marked paragraph to improve.', $userContent);

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'This is the significantly improved paragraph crafted with full document awareness.',
                            ],
                        ],
                    ],
                    'usage' => ['total_tokens' => 35],
                    'model' => 'anthropic/claude-3-7-sonnet',
                ], 200);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'This is the marked paragraph to improve.',
            'type' => 'recreate',
            'context' => [
                'has_selection' => true,
                'selected_text' => 'This is the marked paragraph to improve.',
                'document_title' => 'The Complete Guide to Organic Growth',
                'target_keyword' => 'SaaS SEO',
                'full_document_text' => 'Introduction to organic growth... This is the marked paragraph to improve... Conclusion.',
                'preceding_text' => 'Introduction to organic growth...',
                'following_text' => 'Conclusion and next steps.',
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'result' => 'This is the significantly improved paragraph crafted with full document awareness.',
            'type' => 'recreate',
        ]);
    }

    public function test_15_stage_production_pipeline_integrated_with_brain_memory(): void
    {
        Http::fake([
            '*/chat/completions' => function ($request) {
                $payload = json_decode($request->body(), true);
                $messages = $payload['messages'] ?? [];
                $systemPrompt = $messages[0]['content'] ?? '';
                $userContent = $messages[1]['content'] ?? '';

                // Verify Brain & Memory integration
                $this->assertStringContainsString('CONTENT WRITER BRAIN & GLOBAL MEMORY', $systemPrompt);
                $this->assertStringContainsString('Best Android Games 2026', $systemPrompt);
                $this->assertStringContainsString('Android Gaming', $systemPrompt);

                // Verify 15-Stage Production Pipeline Directives
                $this->assertStringContainsString('ACTIVE ENTERPRISE PRODUCTION PIPELINE', $systemPrompt);
                $this->assertStringContainsString('Search Intent Analysis', $systemPrompt);
                $this->assertStringContainsString('Keyword & Entity Integration', $systemPrompt);
                $this->assertStringContainsString('Outline & Structural Architecture', $systemPrompt);
                $this->assertStringContainsString('Schema FAQ Block', $systemPrompt);
                $this->assertStringContainsString('FORMATTING & TIPTAP PUBLICATION PERMISSIONS', $systemPrompt);

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => "# Best Android Games in 2026\n\n> **Quick Overview:** Modern Android gaming delivers console-quality experiences.\n\n## 1. Top Picks\n\n### Call of Duty: Mobile\n\nFast-paced 120Hz action.",
                            ],
                        ],
                    ],
                    'usage' => ['total_tokens' => 120],
                    'model' => 'anthropic/claude-3-7-sonnet',
                ], 200);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'create article about best android game in 1000 words',
            'type' => 'custom',
            'pipeline_stages' => [
                'search_intent',
                'keyword_research',
                'article_outline',
                'section_generation',
                'schema_generation',
                'publish_assembly',
            ],
            'context' => [
                'has_selection' => false,
                'document_title' => 'Best Android Games 2026',
                'target_keyword' => 'Android Gaming',
                'full_document_text' => 'Draft outline...',
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'type' => 'custom',
        ]);
    }
}
<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Writing Intelligence Test
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

use App\Features\AI\Models\AiProvider;
use App\Features\AI\Services\ContentWriterBrain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WritingIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected ContentWriterBrain $brain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->user = User::factory()->create([
            'role' => 'user',
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'monthly_word_quota' => 500000,
            'used_word_quota' => 0,
        ]);

        $this->brain = app(ContentWriterBrain::class);

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

    public function test_all_seven_writing_intelligence_actions_generate_anti_echo_prompts_with_full_context(): void
    {
        $actions = [
            'recreate' => 'RECREATE PARAGRAPH',
            'rewrite' => 'REWRITE & POLISH',
            'expand' => 'EXPAND WITH DEPTH',
            'shorten' => 'SHORTEN & CONDENSE',
            'simplify' => 'SIMPLIFY',
            'generate_faq' => 'GENERATE FAQ BLOCK',
            'seo_optimize' => 'SEO OPTIMIZE TEXT',
        ];

        $context = [
            'document_title' => 'The Complete Architecture of Cloud Native Applications',
            'target_keyword' => 'cloud native architecture',
            'full_document_text' => "Introduction to modern software paradigms.\n\nMicroservices provide high flexibility but introduce network complexity.\n\nConclusion and key takeaways.",
            'selected_text' => 'Microservices provide high flexibility but introduce network complexity.',
        ];

        foreach ($actions as $actionKey => $expectedLabel) {
            $prompt = $this->brain->buildSurgicalPrompt($actionKey, $context);

            $this->assertArrayHasKey('system', $prompt);
            $this->assertArrayHasKey('user', $prompt);

            $this->assertStringContainsString('The Complete Architecture of Cloud Native Applications', $prompt['system']);
            $this->assertStringContainsString('cloud native architecture', $prompt['system']);
            $this->assertStringContainsString('CONTENT WRITER BRAIN & MEMORY', $prompt['system']);
            $this->assertStringContainsString($expectedLabel, $prompt['system']);
            $this->assertStringContainsString('STRICT ANTI-ECHO GUARANTEE', $prompt['system']);

            $this->assertStringContainsString('Introduction to modern software paradigms', $prompt['system']);
            $this->assertStringContainsString('Conclusion and key takeaways', $prompt['system']);

            $this->assertStringContainsString('<target_marked_content>', $prompt['user']);
            $this->assertStringContainsString('Microservices provide high flexibility but introduce network complexity.', $prompt['user']);
            $this->assertStringContainsString('The Complete Architecture of Cloud Native Applications', $prompt['user']);
            $this->assertStringContainsString('STRICT ANTI-ECHO PROTOCOL', $prompt['user']);
            $this->assertStringContainsString(strtoupper(str_replace('_', ' ', $actionKey)), $prompt['user']);
        }
    }

    public function test_local_algorithmic_engine_executes_all_seven_actions_with_anti_echo_guarantee(): void
    {
        $rawText = "In order to utilize microservices effectively, it is important to note that teams should implement good monitoring because problems will happen.";

        $context = [
            'document_title' => 'Cloud Scalability Guide',
            'target_keyword' => 'distributed monitoring',
        ];

        $recreated = $this->brain->executeLocalActionTransform('recreate', $rawText, $context);
        $this->assertNotEmpty($recreated);
        $this->assertNotEquals($rawText, $recreated);
        $this->assertStringContainsString('Cloud Scalability Guide', $recreated);
        $this->assertStringContainsString('exceptional', $recreated);
        $this->assertStringContainsString('bottleneck', $recreated);

        $rewritten = $this->brain->executeLocalActionTransform('rewrite', $rawText, $context);
        $this->assertNotEmpty($rewritten);
        $this->assertNotEquals($rawText, $rewritten);
        $this->assertStringNotContainsString('in order to', strtolower($rewritten));
        $this->assertStringNotContainsString('it is important to note that', strtolower($rewritten));

        $expanded = $this->brain->executeLocalActionTransform('expand', $rawText, $context);
        $this->assertGreaterThan(strlen($rawText), strlen($expanded));
        $this->assertStringContainsString('distributed monitoring', $expanded);

        $shortened = $this->brain->executeLocalActionTransform('shorten', $rawText, $context);
        $this->assertLessThan(strlen($rawText), strlen($shortened));
        $this->assertNotEmpty($shortened);

        $simplified = $this->brain->executeLocalActionTransform('simplify', $rawText, $context);
        $this->assertStringContainsString('use', strtolower($simplified));
        $this->assertStringContainsString('set up', strtolower($simplified));

        $faq = $this->brain->executeLocalActionTransform('generate_faq', $rawText, $context);
        $this->assertStringContainsString('###', $faq);
        $this->assertStringContainsString('distributed monitoring', $faq);
        $this->assertStringContainsString('**', $faq);

        $seoOptimized = $this->brain->executeLocalActionTransform('seo_optimize', $rawText, $context);
        $this->assertStringContainsString('**Distributed monitoring**', $seoOptimized);
    }

    public function test_prepare_prompt_api_returns_full_messages_and_calibrated_temperature(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('ai.prepare-prompt'), [
            'text' => 'Microservices provide high flexibility but introduce network complexity.',
            'type' => 'recreate',
            'context' => [
                'has_selection' => true,
                'selected_text' => 'Microservices provide high flexibility but introduce network complexity.',
                'document_title' => 'Modern Distributed Systems',
                'target_keyword' => 'microservices pattern',
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'messages',
            'model',
            'temperature',
            'routing',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(0.82, $data['temperature']);
        $this->assertStringContainsString('Modern Distributed Systems', $data['messages'][0]['content']);
        $this->assertStringContainsString('STRICT ANTI-ECHO PROTOCOL', $data['messages'][1]['content']);
    }

    public function test_dual_role_authorization_and_unauthenticated_access(): void
    {
        // 1. Admin access
        $adminResponse = $this->actingAs($this->admin)->postJson(route('ai.prepare-prompt'), [
            'text' => 'Paragraph content.',
            'type' => 'rewrite',
        ]);
        $adminResponse->assertOk();

        // 2. Regular user access
        $userResponse = $this->actingAs($this->user)->postJson(route('ai.prepare-prompt'), [
            'text' => 'Paragraph content.',
            'type' => 'rewrite',
        ]);
        $userResponse->assertOk();
    }
}

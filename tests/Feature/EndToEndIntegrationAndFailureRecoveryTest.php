<?php

namespace Tests\Feature;

use App\Features\Admin\Actions\SeedDefaultAiProviders;
use App\Features\AI\Actions\TransformText;
use App\Features\AI\Services\AiCircuitBreaker;
use App\Features\AI\Services\OmniRouteClient;
use App\Features\Auth\Models\UserApiKey;
use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\Documents\Actions\CreateDocumentShare;
use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Models\Document;
use App\Features\KnowledgeBase\Models\KnowledgeSource;
use App\Features\KnowledgeBase\Services\VectorSearchEngine;
use App\Features\Projects\Models\Project;
use App\Features\Templates\Actions\GenerateFromTemplate;
use App\Features\Templates\Models\Template;
use App\Features\Templates\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class EndToEndIntegrationAndFailureRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'creator@helpofai.com',
            'role' => 'user',
            'plan' => 'pro',
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin@helpofai.com',
            'role' => 'admin',
            'plan' => 'enterprise',
            'is_active' => true,
        ]);

        app(SeedDefaultAiProviders::class)->execute();
    }

    public function test_end_to_end_document_lifecycle_create_edit_autosave_version_restore()
    {
        $project = Project::create([
            'user_id' => $this->user->id,
            'name' => 'SaaS Marketing Campaign',
            'slug' => 'saas-marketing-campaign',
            'description' => 'Q4 Marketing assets',
        ]);

        $document = Document::create([
            'user_id' => $this->user->id,
            'project_id' => $project->id,
            'title' => 'Product Launch Blog Post',
            'slug' => 'product-launch-blog-post',
            'content' => '<h1>Initial Draft</h1><p>Welcome to our new release.</p>',
            'word_count' => 8,
            'editor_type' => 'tiptap',
        ]);

        $this->assertDatabaseHas('documents', ['id' => $document->id, 'title' => 'Product Launch Blog Post']);

        // Create Version Snapshot
        $versionAction = app(SaveDocumentVersion::class);
        $version = $versionAction->execute($document, $this->user, [
            'summary' => 'Pre-Publish Milestone',
            'content_html' => $document->content,
        ]);

        $this->assertDatabaseHas('document_versions', [
            'document_id' => $document->id,
            'summary' => 'Pre-Publish Milestone',
        ]);

        $document->update([
            'title' => 'Product Launch Blog Post (Final Edition)',
            'content' => '<h1>Launch Day</h1><p>Experience the future of AI copywriting with HelpOfAi Studio today.</p>',
        ]);

        $this->assertEquals('Product Launch Blog Post (Final Edition)', $document->fresh()->title);
    }

    public function test_brand_voice_profile_is_injected_into_ai_transform_prompt()
    {
        $brand = BrandProfile::create([
            'user_id' => $this->user->id,
            'name' => 'Cyberpunk Tech Tone',
            'tone_description' => 'bold, visionary, high-energy, authoritative',
            'target_audience' => 'AI Engineers and Startup Founders',
            'rules' => ['Use concise punchy sentences. Avoid corporate fluff.'],
            'is_active' => true,
        ]);

        $action = app(TransformText::class);
        $systemPrompt = $action->getSystemPrompt('custom', 'Focus on developer productivity');

        $this->assertNotEmpty($systemPrompt);
        $this->assertStringContainsString('Focus on developer productivity', $systemPrompt);
    }

    public function test_knowledge_base_ingestion_chunks_and_cached_rag_retrieval()
    {
        Http::fake([
            '*/v1/embeddings' => Http::response([
                'data' => [
                    ['embedding' => array_fill(0, 64, 0.25)],
                ],
            ], 200),
        ]);

        $source = KnowledgeSource::create([
            'user_id' => $this->user->id,
            'title' => 'Refund Policy Guide',
            'source_type' => 'text',
            'content' => "HelpOfAi Studio provides a 30-day money-back guarantee for all annual subscriptions.\n\nMonthly subscriptions can be canceled anytime from your profile settings without penalty.",
            'status' => 'ready',
        ]);

        $chunk = $source->chunks()->create([
            'chunk_index' => 0,
            'content' => 'HelpOfAi Studio provides a 30-day money-back guarantee for all annual subscriptions.',
            'token_count' => 15,
            'embedding' => array_fill(0, 64, 0.25),
        ]);

        $vectorEngine = app(VectorSearchEngine::class);
        $searchResults = $vectorEngine->search($this->user, 'How do refunds work on annual plans?', 5);

        $this->assertNotEmpty($searchResults);
        $this->assertEquals($chunk->id, $searchResults[0]['chunk_id']);
    }

    public function test_template_variable_compilation_and_usage_quota_deduction()
    {
        $cat = TemplateCategory::create([
            'name' => 'Email Marketing',
            'slug' => 'email-marketing',
            'icon' => '✉️',
        ]);

        $template = Template::create([
            'template_category_id' => $cat->id,
            'name' => 'Cold Email Outreach',
            'slug' => 'cold-email-outreach',
            'description' => 'Generate high converting B2B cold emails',
            'prompt_template' => 'Write a cold email to {{prospect_name}} at {{company_name}} offering {{service}}.',
            'variables' => [
                ['name' => 'prospect_name', 'label' => 'Prospect Name', 'type' => 'text'],
                ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text'],
                ['name' => 'service', 'label' => 'Service', 'type' => 'text'],
            ],
            'is_active' => true,
        ]);

        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Subject: Quick question for Acme Corp\n\nHi John, I noticed Acme Corp is expanding...']],
                ],
                'usage' => [
                    'prompt_tokens' => 25,
                    'completion_tokens' => 45,
                    'total_tokens' => 70,
                ],
            ], 200),
        ]);

        $generateAction = app(GenerateFromTemplate::class);
        $result = $generateAction->execute($this->user, $template, [
            'prospect_name' => 'John Doe',
            'company_name' => 'Acme Corp',
            'service' => 'AI Workflow Automation',
        ]);

        $this->assertNotEmpty($result['content']);
        $this->assertDatabaseHas('generation_usage', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_document_sharing_password_gate_and_multi_format_export()
    {
        $document = Document::create([
            'user_id' => $this->user->id,
            'title' => 'Confidential Whitepaper',
            'slug' => 'confidential-whitepaper',
            'content' => '<h2>Executive Summary</h2><p>This report details quarterly AI advancements.</p>',
            'word_count' => 10,
        ]);

        $action = app(CreateDocumentShare::class);
        $share = $action->execute($document, [
            'password' => 'SecurePass2026!',
            'allow_download' => true,
            'allow_copy' => true,
        ]);

        $this->assertTrue(Hash::check('SecurePass2026!', $share->password_hash));

        // Public Share Page Password Gate
        Livewire::test('App\Features\Documents\Livewire\PublicDocumentPage', ['token' => $share->share_token])
            ->set('passwordInput', 'WrongPassword')
            ->call('unlock')
            ->assertSet('isUnlocked', false)
            ->set('passwordInput', 'SecurePass2026!')
            ->call('unlock')
            ->assertSet('isUnlocked', true);

        // Authenticated Export Routes
        $mdResponse = $this->actingAs($this->user)->get(route('documents.export', ['id' => $document->id, 'format' => 'markdown']));
        $mdResponse->assertStatus(200);
        $mdResponse->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');

        $htmlResponse = $this->actingAs($this->user)->get(route('documents.export', ['id' => $document->id, 'format' => 'html']));
        $htmlResponse->assertStatus(200);
        $htmlResponse->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_model_governance_circuit_breaker_resilience()
    {
        $breaker = app(AiCircuitBreaker::class);
        $breaker->trip('Scheduled server cluster migration', 'Lead Dev');

        $response = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'Make this sound formal',
            'type' => 'tone',
        ]);

        $response->assertStatus(503);
        $response->assertJsonFragment(['success' => false]);

        $breaker->reset();

        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Formalized content here']],
                ],
            ], 200),
        ]);

        $responseActive = $this->actingAs($this->user)->postJson(route('ai.transform'), [
            'text' => 'Make this sound formal',
            'type' => 'tone',
        ]);

        $responseActive->assertStatus(200);
        $responseActive->assertJsonFragment(['success' => true]);
    }

    public function test_omniroute_automatic_fallback_model_recovery_on_upstream_failure()
    {
        // First request to expensive model fails with 502, Fallback to deepseek succeeds
        Http::fake([
            '*/v1/chat/completions' => Http::sequence()
                ->push(['error' => ['message' => '502 Bad Gateway']], 502)
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Recovered output from fallback model']],
                    ],
                    'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 15, 'total_tokens' => 25],
                ], 200),
        ]);

        $client = app(OmniRouteClient::class);
        $res = $client->chatCompletion([
            ['role' => 'user', 'content' => 'Hello AI'],
        ], ['model' => 'openai/gpt-4o']);

        $this->assertEquals('Recovered output from fallback model', $res['content']);
    }

    public function test_user_byok_rate_limit_bypass_and_quota_independence()
    {
        $key = UserApiKey::create([
            'user_id' => $this->user->id,
            'provider_slug' => 'openai',
            'api_key' => 'sk-custom-user-secret-12345',
            'is_active' => true,
        ]);

        $this->assertEquals('sk-custom-user-secret-12345', $key->getRawKeyForOwner($this->user));
        $this->assertDatabaseHas('user_api_keys', ['id' => $key->id, 'provider_slug' => 'openai']);
    }
}
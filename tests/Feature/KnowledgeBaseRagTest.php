<?php

namespace Tests\Feature;

use App\Features\AI\Models\AiProvider;
use App\Features\KnowledgeBase\Actions\CreateKnowledgeSource;
use App\Features\KnowledgeBase\Actions\RetrieveRagContext;
use App\Features\KnowledgeBase\Models\KnowledgeSource;
use App\Features\KnowledgeBase\Services\SemanticChunker;
use App\Features\KnowledgeBase\Services\VectorSearchEngine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseRagTest extends TestCase
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

    public function test_user_can_view_knowledge_base_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('knowledge-base.index'));
        $response->assertOk();
        $response->assertSee('Knowledge Base & RAG Pipeline');
    }

    public function test_semantic_chunker_splits_markdown_into_token_aware_chunks(): void
    {
        $chunker = new SemanticChunker();
        $markdown = "# Section 1: Introduction\n\nThis is the introductory paragraph detailing our cloud infrastructure.\n\n# Section 2: Architecture\n\nOur system uses multi-model LLM routing across distributed edge gateways to minimize latency.";

        $chunks = $chunker->chunk($markdown, 20, 5);

        $this->assertGreaterThanOrEqual(2, count($chunks));
        $this->assertArrayHasKey('chunk_index', $chunks[0]);
        $this->assertArrayHasKey('content', $chunks[0]);
        $this->assertArrayHasKey('token_count', $chunks[0]);
        $this->assertGreaterThan(0, $chunks[0]['token_count']);
    }

    public function test_vector_search_engine_computes_accurate_cosine_similarity(): void
    {
        $engine = app(VectorSearchEngine::class);

        $vecA = [1.0, 0.0, 0.0];
        $vecB = [1.0, 0.0, 0.0];
        $vecC = [0.0, 1.0, 0.0];

        // Identical vectors should have similarity 1.0
        $this->assertEquals(1.0, $engine->cosineSimilarity($vecA, $vecB));

        // Orthogonal vectors should have similarity 0.0
        $this->assertEquals(0.0, $engine->cosineSimilarity($vecA, $vecC));
    }

    public function test_user_can_ingest_knowledge_source_and_retrieve_rag_context(): void
    {
        $createAction = app(CreateKnowledgeSource::class);
        $source = $createAction->execute($this->user, [
            'title' => 'Refund Policy & SLA Guarantees',
            'source_type' => 'text',
            'content' => "We offer a 100% money-back guarantee within 30 days of purchase for all SaaS subscriptions. Enterprise customers have a 99.99% uptime SLA backed by credits.",
        ]);

        $this->assertDatabaseHas('knowledge_sources', [
            'id' => $source->id,
            'user_id' => $this->user->id,
            'title' => 'Refund Policy & SLA Guarantees',
            'status' => 'ready',
        ]);

        $this->assertDatabaseHas('knowledge_chunks', [
            'knowledge_source_id' => $source->id,
        ]);

        $ragAction = app(RetrieveRagContext::class);
        $result = $ragAction->execute($this->user, 'What is the refund policy and money-back guarantee?');

        $this->assertTrue($result['has_context']);
        $this->assertStringContainsString('Refund Policy & SLA Guarantees', $result['prompt_snippet']);
        $this->assertNotEmpty($result['chunks']);
        $this->assertGreaterThan(0, $result['total_tokens']);
    }

    public function test_user_can_reindex_and_delete_knowledge_source(): void
    {
        $source = KnowledgeSource::create([
            'user_id' => $this->user->id,
            'title' => 'Temporary Guide',
            'source_type' => 'text',
            'content' => 'Sample content to be reindexed and deleted later.',
            'status' => 'ready',
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\KnowledgeBase\Livewire\KnowledgeBasePage::class)
            ->call('reindex', $source->id)
            ->assertHasNoErrors()
            ->call('deleteSource', $source->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('knowledge_sources', [
            'id' => $source->id,
        ]);
    }
}
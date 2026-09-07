<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Cognitive Memory OS Test Suite
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

use App\Features\ContentIntelligence\Actions\CreateContentMission;
use App\Features\ContentIntelligence\DTOs\MemoryCandidateDTO;
use App\Features\ContentIntelligence\Enums\BrainScope;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\ContentIntelligence\Livewire\ContentIntelligencePage;
use App\Features\ContentIntelligence\Memory\Admission\DuplicateDetector;
use App\Features\ContentIntelligence\Memory\Admission\EvidenceValidator;
use App\Features\ContentIntelligence\Memory\Admission\MemoryAdmissionGate;
use App\Features\ContentIntelligence\Memory\Governance\MemoryDecayService;
use App\Features\ContentIntelligence\Memory\MemoryManager;
use App\Features\ContentIntelligence\Memory\Retrieval\AttentionEngine;
use App\Features\ContentIntelligence\Models\ArticleElementNode;
use App\Features\ContentIntelligence\Models\BrainMemory;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\ArticleBrainService;
use App\Features\ContentIntelligence\Services\SiteBrainService;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBrainMemoryOSTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'author_'.uniqid().'@helpofai.com',
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin_'.uniqid().'@helpofai.com',
            'role' => 'admin',
        ]);
    }

    public function test_admission_gate_admits_valid_high_confidence_fact(): void
    {
        $gate = new MemoryAdmissionGate(new EvidenceValidator, new DuplicateDetector);

        $candidate = new MemoryCandidateDTO(
            id: 'cand_test_01',
            userId: $this->user->id,
            content: 'Laravel 13 queue workers trap SIGTERM signals for graceful job completion.',
            scope: BrainScope::PROJECT,
            layer: MemoryLayerType::SEMANTIC,
            type: 'technical_fact',
            subject: 'Laravel 13 Queue Worker',
            predicate: 'handles_signal',
            object: 'SIGTERM',
            sourceUrl: 'https://laravel.com/docs/13.x/queues',
            confidence: 0.95,
            importanceScore: 0.85
        );

        $result = $gate->evaluate($candidate);

        $this->assertTrue($result->isAdmitted);
        $this->assertSame('admit', $result->action);
        $this->assertNotNull($result->memoryId);

        $this->assertDatabaseHas('brain_memories', [
            'id' => $result->memoryId,
            'user_id' => $this->user->id,
            'subject' => 'Laravel 13 Queue Worker',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->assertDatabaseHas('memory_candidates', [
            'id' => 'cand_test_01',
            'gate_status' => 'admitted',
        ]);
    }

    public function test_admission_gate_rejects_insufficient_confidence_or_empty_content(): void
    {
        $gate = new MemoryAdmissionGate(new EvidenceValidator, new DuplicateDetector);

        // 1. Empty content
        $emptyCandidate = new MemoryCandidateDTO(
            id: 'cand_empty',
            userId: $this->user->id,
            content: '   ',
            confidence: 0.90,
            importanceScore: 0.90
        );
        $emptyResult = $gate->evaluate($emptyCandidate);
        $this->assertFalse($emptyResult->isAdmitted);
        $this->assertStringContainsString('empty or too short', $emptyResult->rejectionReason);

        // 2. Low confidence candidate without source
        $weakCandidate = new MemoryCandidateDTO(
            id: 'cand_weak',
            userId: $this->user->id,
            content: 'Some unverified rumor without any source cited anywhere.',
            scope: BrainScope::PROJECT,
            layer: MemoryLayerType::SEMANTIC,
            confidence: 0.60,
            importanceScore: 0.90
        );
        $weakResult = $gate->evaluate($weakCandidate);
        $this->assertFalse($weakResult->isAdmitted);
        $this->assertSame('reject', $weakResult->action);
    }

    public function test_admission_gate_deduplicates_exact_triples(): void
    {
        $gate = new MemoryAdmissionGate(new EvidenceValidator, new DuplicateDetector);

        $cand1 = new MemoryCandidateDTO(
            id: 'cand_dup_1',
            userId: $this->user->id,
            content: 'PHP 8.3 implements typed class constants.',
            subject: 'PHP 8.3',
            predicate: 'implements',
            object: 'typed class constants',
            sourceUrl: 'https://php.net/docs',
            confidence: 0.95,
            importanceScore: 0.85
        );
        $res1 = $gate->evaluate($cand1);
        $this->assertTrue($res1->isAdmitted);

        // Submit duplicate
        $cand2 = new MemoryCandidateDTO(
            id: 'cand_dup_2',
            userId: $this->user->id,
            content: 'PHP 8.3 implements typed class constants.',
            subject: 'PHP 8.3',
            predicate: 'implements',
            object: 'typed class constants',
            sourceUrl: 'https://php.net/docs',
            confidence: 0.95,
            importanceScore: 0.85
        );
        $res2 = $gate->evaluate($cand2);

        $this->assertFalse($res2->isAdmitted);
        $this->assertSame('duplicate_ignored', $res2->action);
        $this->assertSame($res1->memoryId, $res2->memoryId);
        $this->assertSame(1, BrainMemory::where('user_id', $this->user->id)->count());
    }

    public function test_admission_gate_reconciles_contradictions_and_increments_version_lineage(): void
    {
        $gate = new MemoryAdmissionGate(new EvidenceValidator, new DuplicateDetector);

        // Fact v1: Laravel 13 latest version is 13.0
        $cand1 = new MemoryCandidateDTO(
            id: 'cand_v1',
            userId: $this->user->id,
            content: 'Laravel latest version is 13.0',
            subject: 'Laravel Framework',
            predicate: 'latest_stable_version',
            object: '13.0',
            sourceUrl: 'https://laravel.com/docs',
            confidence: 0.85,
            importanceScore: 0.90
        );
        $res1 = $gate->evaluate($cand1);
        $this->assertTrue($res1->isAdmitted);

        $memoryV1 = BrainMemory::find($res1->memoryId);
        $this->assertSame(1, $memoryV1->version);
        $this->assertSame(MemoryStatus::ACTIVE, $memoryV1->status);

        // Fact v2: Laravel updated to 13.1 with higher authority/confidence
        $cand2 = new MemoryCandidateDTO(
            id: 'cand_v2',
            userId: $this->user->id,
            content: 'Laravel latest version is 13.1',
            subject: 'Laravel Framework',
            predicate: 'latest_stable_version',
            object: '13.1',
            sourceUrl: 'https://github.com/laravel/framework/releases/tag/v13.1.0',
            confidence: 0.98,
            importanceScore: 0.90
        );
        $res2 = $gate->evaluate($cand2);

        $this->assertTrue($res2->isAdmitted);
        $this->assertSame('merge_supersede', $res2->action);

        // Verify v1 is now superseded and outdated
        $memoryV1Fresh = $memoryV1->fresh();
        $this->assertSame(MemoryStatus::SUPERSEDED, $memoryV1Fresh->status);
        $this->assertSame(EpistemicState::OUTDATED, $memoryV1Fresh->epistemic_state);

        // Verify v2 is active with version 2 and parent pointer
        $memoryV2 = BrainMemory::find($res2->memoryId);
        $this->assertSame(2, $memoryV2->version);
        $this->assertSame(MemoryStatus::ACTIVE, $memoryV2->status);
        $this->assertSame($memoryV1->id, $memoryV2->lineage_parent_id);
        $this->assertContains($memoryV1->id, $memoryV2->lineage);
    }

    public function test_memory_decay_service_transitions_stale_memories_and_flags_article_elements(): void
    {
        $init = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Production Queues',
            'primary_objective' => 'Deep architectural guide for production queues',
        ]);
        $mission = $init['mission'];

        $memory = BrainMemory::create([
            'id' => 'mem_decay_test',
            'user_id' => $this->user->id,
            'mission_id' => $mission->id,
            'scope' => BrainScope::PROJECT->value,
            'layer' => MemoryLayerType::SEMANTIC->value,
            'content' => 'Old legacy driver setting',
            'confidence' => 0.95,
            'authority' => 80,
            'freshness_score' => 1.0,
            'status' => MemoryStatus::ACTIVE->value,
            'epistemic_state' => EpistemicState::VERIFIED->value,
            'created_at' => now()->subMonths(12),
            'expires_at' => now()->subDays(5), // Expired!
        ]);

        $articleElement = ArticleElementNode::create([
            'id' => 'aen_test_01',
            'mission_id' => $mission->id,
            'element_type' => 'sentence',
            'text_content' => 'Always configure the old legacy driver setting in queue config.',
            'memory_id' => $memory->id,
            'is_stale' => false,
        ]);

        $decayService = new MemoryDecayService;
        $result = $decayService->evaluateDecay($this->user->id);

        $this->assertGreaterThanOrEqual(1, $result->totalChecked);
        $this->assertGreaterThanOrEqual(1, $result->decayedCount);

        $memoryFresh = $memory->fresh();
        $this->assertSame(MemoryStatus::DECAYED, $memoryFresh->status);
        $this->assertLessThan(0.40, $memoryFresh->freshness_score);

        $elementFresh = $articleElement->fresh();
        $this->assertTrue($elementFresh->is_stale);
        $this->assertStringContainsString('decayed', (string) $elementFresh->invalidation_reason);
    }

    public function test_site_brain_detects_topic_cannibalization(): void
    {
        Document::create([
            'user_id' => $this->user->id,
            'title' => 'Mastering Laravel Queues and Workers',
            'slug' => 'mastering-laravel-queues-and-workers',
            'status' => 'draft',
        ]);

        $memoryManager = app(MemoryManager::class);
        $siteBrain = new SiteBrainService($memoryManager);

        $check = $siteBrain->checkCannibalization($this->user->id, 'Laravel Queues');

        $this->assertTrue($check['hasCannibalizationRisk']);
        $this->assertGreaterThanOrEqual(1, $check['matchingCount']);
        $this->assertStringContainsString('cannibalization', $check['recommendation']);
    }

    public function test_article_brain_indexes_elements_and_performs_surgical_invalidation(): void
    {
        $init = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Redis Caching Architecture',
            'primary_objective' => 'Enterprise Redis guide',
        ]);
        $mission = $init['mission'];

        $doc = Document::create([
            'user_id' => $this->user->id,
            'title' => 'Redis Caching Architecture',
            'slug' => 'redis-caching-architecture',
            'status' => 'draft',
        ]);

        $articleBrain = new ArticleBrainService;

        $sections = [
            [
                'title' => 'Introduction to Redis Cache',
                'content' => "Redis 7 introduces multi-threading for I/O multiplexing. This enhances throughput dramatically across distributed nodes.\n\nAlways monitor cluster health.",
            ],
        ];

        $nodes = $articleBrain->indexArticleElements($mission, $doc, $sections);

        $this->assertCount(4, $nodes); // 1 section node + 3 sentence nodes
        $this->assertDatabaseHas('article_element_nodes', [
            'mission_id' => $mission->id,
            'element_type' => 'section',
            'text_content' => 'Introduction to Redis Cache',
        ]);

        // Surgically invalidate "Redis 7"
        $invalidated = $articleBrain->invalidateFact('Redis 7', 'Redis 8 released with changed threading model');

        $this->assertGreaterThanOrEqual(1, $invalidated['affectedSentencesCount']);
        $this->assertContains($doc->id, $invalidated['impactedDocumentIds']);

        $staleNodes = ArticleElementNode::where('is_stale', true)->get();
        $this->assertNotEmpty($staleNodes);
        $this->assertSame(EpistemicState::OUTDATED, $staleNodes->first()->epistemic_state);
    }

    public function test_attention_engine_focuses_minimal_context(): void
    {
        $engine = new AttentionEngine;

        // Create Brand Memory
        BrainMemory::create([
            'id' => 'mem_brand_01',
            'user_id' => $this->user->id,
            'scope' => BrainScope::SITE->value,
            'layer' => MemoryLayerType::BRAND->value,
            'content' => 'Tone: Authoritative, pragmatic, and developer-centric.',
            'confidence' => 1.0,
            'authority' => 100,
            'status' => MemoryStatus::ACTIVE->value,
        ]);

        // Create Specific Semantic Fact
        BrainMemory::create([
            'id' => 'mem_sem_01',
            'user_id' => $this->user->id,
            'scope' => BrainScope::PROJECT->value,
            'layer' => MemoryLayerType::SEMANTIC->value,
            'subject' => 'Supervisor',
            'predicate' => 'requires_setting',
            'object' => 'stopwaitsecs=3600',
            'content' => 'Supervisor must set stopwaitsecs higher than worker timeout.',
            'confidence' => 0.98,
            'authority' => 90,
            'status' => MemoryStatus::ACTIVE->value,
        ]);

        $focused = $engine->focus(
            userId: $this->user->id,
            missionId: null,
            topic: 'Supervisor queue management',
            currentSectionTitle: 'Configuring Supervisor',
            keywords: ['Supervisor', 'timeout']
        );

        $this->assertGreaterThanOrEqual(2, $focused['count']);
        $this->assertStringContainsString('Tone: Authoritative', $focused['contextText']);
        $this->assertStringContainsString('stopwaitsecs', $focused['contextText']);
    }

    public function test_memory_manager_records_episodic_events(): void
    {
        $memoryManager = app(MemoryManager::class);

        $event = $memoryManager->recordEpisode(
            userId: $this->user->id,
            eventType: 'user_correction_observed',
            missionId: null,
            context: ['edited_field' => 'hook_sentence'],
            actionTaken: 'Author shortened introductory sentence',
            outcomeScore: 0.95,
            lessonsLearned: 'Author favors concise lead-ins under 15 words.'
        );

        $this->assertNotNull($event->id);
        $this->assertDatabaseHas('episodic_events', [
            'id' => $event->id,
            'user_id' => $this->user->id,
            'event_type' => 'user_correction_observed',
        ]);
    }

    public function test_livewire_content_intelligence_page_renders_memory_os_tab(): void
    {
        $init = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Production Microservices',
            'primary_objective' => 'Microservices deployment blueprint',
        ]);
        $mission = $init['mission'];

        $run = WorkflowRun::create([
            'mission_id' => $mission->id,
            'user_id' => $this->user->id,
            'current_node' => 'knowledge_fabric',
            'status' => 'running',
        ]);

        BrainMemory::create([
            'id' => 'mem_livewire_test',
            'user_id' => $this->user->id,
            'mission_id' => $mission->id,
            'scope' => BrainScope::PROJECT->value,
            'layer' => MemoryLayerType::SEMANTIC->value,
            'subject' => 'Docker',
            'predicate' => 'utilizes',
            'object' => 'cgroups v2',
            'content' => 'Docker utilizes cgroups v2 for memory isolation.',
            'confidence' => 0.96,
            'authority' => 90,
            'status' => MemoryStatus::ACTIVE->value,
        ]);

        Livewire::actingAs($this->user)
            ->test(ContentIntelligencePage::class)
            ->set('selectedRunId', $run->id)
            ->call('setInspectorTab', 'memory_os')
            ->assertSee('Level 1: Site Brain')
            ->assertSee('Level 2: Project Brain')
            ->assertSee('Level 3: Article Brain')
            ->assertSee('Docker')
            ->assertSee('cgroups v2')
            ->call('triggerDecayCheck')
            ->assertSee('Cognitive Memory OS Evaluation Complete');
    }

    public function test_unauthenticated_user_cannot_access_content_intelligence_page(): void
    {
        $response = $this->get(route('content-intelligence.index'));
        $response->assertRedirect(route('login'));
    }
}

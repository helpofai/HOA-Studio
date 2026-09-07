<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 3 World Model & Truth Test Suite
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
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\EvidenceSnippetDTO;
use App\Features\ContentIntelligence\DTOs\ResearchPlanDTO;
use App\Features\ContentIntelligence\DTOs\TruthAuditReportDTO;
use App\Features\ContentIntelligence\DTOs\WorldEntityDTO;
use App\Features\ContentIntelligence\DTOs\WorldRelationshipDTO;
use App\Features\ContentIntelligence\Enums\ContentWorkflowStatus;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\EvidenceRelationType;
use App\Features\ContentIntelligence\Enums\SourceReliabilityTier;
use App\Features\ContentIntelligence\Livewire\ContentIntelligencePage;
use App\Features\ContentIntelligence\Models\ArticleElementNode;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\SourceIntelligence;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Services\DeepEvidenceGraphService;
use App\Features\ContentIntelligence\Services\KnowledgeFabricService;
use App\Features\ContentIntelligence\Services\TruthLayerService;
use App\Features\ContentIntelligence\Services\WorldModelService;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ContentBrainWorldModelAndTruthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'researcher@helpofai.com',
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'email' => 'chief_architect@helpofai.com',
            'role' => 'admin',
        ]);
    }

    public function test_world_model_registers_and_resolves_entities(): void
    {
        $service = app(WorldModelService::class);

        $dto = WorldEntityDTO::fromArray([
            'name' => 'PHP 8.3',
            'category' => 'programming_language',
            'slug' => 'php-8-3',
            'description' => 'Latest major release of PHP with typed class constants and json_validate.',
            'aliases' => ['php8.3', 'php 8.3.0', 'hypertext preprocessor 8.3'],
            'attributes' => ['released' => 2023, 'status' => 'active'],
        ]);

        $entity = $service->registerEntity($dto);

        $this->assertNotNull($entity->id);
        $this->assertSame('PHP 8.3', $entity->name);
        $this->assertSame('php-8-3', $entity->slug);
        $this->assertContains('php8.3', $entity->aliases);

        // Resolve by name
        $resolvedByName = $service->findEntity('PHP 8.3');
        $this->assertNotNull($resolvedByName);
        $this->assertSame($entity->id, $resolvedByName->id);

        // Resolve by alias
        $resolvedByAlias = $service->findEntity('php8.3');
        $this->assertNotNull($resolvedByAlias);
        $this->assertSame($entity->id, $resolvedByAlias->id);
    }

    public function test_world_model_links_relationships_and_detects_incompatibilities(): void
    {
        $service = app(WorldModelService::class);

        $php83 = $service->registerEntity(WorldEntityDTO::fromArray([
            'name' => 'PHP 8.3',
            'category' => 'runtime',
            'slug' => 'php-8-3',
        ]));

        $legacyExt = $service->registerEntity(WorldEntityDTO::fromArray([
            'name' => 'Mcrypt Extension',
            'category' => 'php_extension',
            'slug' => 'ext-mcrypt',
        ]));

        $modernExt = $service->registerEntity(WorldEntityDTO::fromArray([
            'name' => 'Sodium Extension',
            'category' => 'php_extension',
            'slug' => 'ext-sodium',
        ]));

        // Link Mcrypt as incompatible_with PHP 8.3
        $service->linkEntities(WorldRelationshipDTO::fromArray([
            'subject_entity_id' => $legacyExt->id,
            'object_entity_id' => $php83->id,
            'predicate' => 'incompatible_with',
            'confidence' => 0.98,
            'explanation' => 'Mcrypt was completely removed in PHP 7.2+',
        ]));

        // Link Sodium as compatible_with PHP 8.3
        $service->linkEntities(WorldRelationshipDTO::fromArray([
            'subject_entity_id' => $modernExt->id,
            'object_entity_id' => $php83->id,
            'predicate' => 'compatible_with',
            'confidence' => 0.99,
        ]));

        // Detect incompatibility when both PHP 8.3 and Mcrypt are in entity list
        $conflicts = $service->detectIncompatibilities(['PHP 8.3', 'Mcrypt Extension']);
        $this->assertNotEmpty($conflicts);
        $this->assertCount(1, $conflicts);
        $this->assertSame('Mcrypt Extension', $conflicts[0]['entityA']);
        $this->assertSame('PHP 8.3', $conflicts[0]['entityB']);

        // No incompatibility when PHP 8.3 and Sodium are checked together
        $cleanList = $service->detectIncompatibilities(['PHP 8.3', 'Sodium Extension']);
        $this->assertEmpty($cleanList);
    }

    public function test_world_model_verifies_prerequisite_chains(): void
    {
        $service = app(WorldModelService::class);

        $composer = $service->registerEntity(WorldEntityDTO::fromArray([
            'name' => 'Composer 2',
            'category' => 'package_manager',
            'slug' => 'composer-2',
        ]));

        $php = $service->registerEntity(WorldEntityDTO::fromArray([
            'name' => 'PHP CLI',
            'category' => 'runtime',
            'slug' => 'php-cli',
        ]));

        // Composer 2 requires PHP CLI
        $service->linkEntities(WorldRelationshipDTO::fromArray([
            'subject_entity_id' => $composer->id,
            'object_entity_id' => $php->id,
            'predicate' => 'requires',
            'confidence' => 0.99,
        ]));

        $prereqs = $service->getPrerequisites('Composer 2');
        $this->assertCount(1, $prereqs);
        $this->assertSame('PHP CLI', $prereqs[0]);
    }

    public function test_deep_evidence_graph_records_snippets_and_grounds_claims(): void
    {
        $deepGraph = app(DeepEvidenceGraphService::class);

        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'PostgreSQL 16 Logical Replication',
            'primary_objective' => 'Comprehensive technical evaluation',
        ]);
        $mission = $missionData['mission'];

        $source = SourceIntelligence::create([
            'url_hash' => hash('sha256', 'https://postgresql.org/docs/16/release.html'),
            'url' => 'https://postgresql.org/docs/16/release.html',
            'title' => 'Official PostgreSQL 16 Release Notes',
            'source_type' => SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
            'reliability_score' => 95,
            'domain_authority' => 95,
            'is_primary' => true,
        ]);

        $claim = ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'statement' => 'PostgreSQL 16 supports logical replication from standby servers.',
            'epistemic_state' => EpistemicState::UNVERIFIED,
            'confidence_score' => 0.85,
        ]);

        $snippetDTO = EvidenceSnippetDTO::fromArray([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'extract_text' => 'PostgreSQL 16 introduces the ability to perform logical decoding on a standby server, reducing load on the primary.',
            'verbatim_quote' => 'PostgreSQL 16 introduces the ability to perform logical decoding on a standby server',
            'confidence_score' => 0.98,
        ]);

        $snippet = $deepGraph->recordSnippet($snippetDTO);
        $this->assertNotNull($snippet->id);
        $this->assertSame($mission->id, $snippet->mission_id);

        // Ground claim with supporting evidence
        $link = $deepGraph->linkClaimToEvidence(
            claimId: $claim->id,
            evidenceId: $snippet->id,
            type: EvidenceRelationType::SUPPORTS,
            weight: 0.95,
            notes: 'Verbatim quote confirms standby logical decoding in PG16'
        );

        $this->assertNotNull($link->id);
        $this->assertDatabaseHas('claim_evidence_links', [
            'claim_id' => $claim->id,
            'evidence_id' => $snippet->id,
            'relation_type' => 'supports',
        ]);
    }

    public function test_deep_evidence_graph_calculates_consensus_and_detects_contradictions(): void
    {
        $deepGraph = app(DeepEvidenceGraphService::class);

        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Redis Enterprise Sharding Architecture',
            'primary_objective' => 'Architecture evaluation',
        ]);
        $mission = $missionData['mission'];

        $sourceA = SourceIntelligence::create([
            'url_hash' => hash('sha256', 'https://benchmarks.redis.io'),
            'url' => 'https://benchmarks.redis.io',
            'title' => 'Redis Benchmarks 2024',
            'source_type' => SourceReliabilityTier::INDUSTRY_PUBLICATION,
            'reliability_score' => 90,
            'domain_authority' => 90,
            'is_primary' => true,
        ]);

        $sourceB = SourceIntelligence::create([
            'url_hash' => hash('sha256', 'https://independent-audit.dev'),
            'url' => 'https://independent-audit.dev',
            'title' => 'Third-Party Latency Audit',
            'source_type' => SourceReliabilityTier::ACADEMIC_PAPER,
            'reliability_score' => 85,
            'domain_authority' => 85,
            'is_primary' => false,
        ]);

        $claim = ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $sourceA->id,
            'statement' => 'Redis clustering guarantees sub-millisecond writes under 100% network partition.',
            'epistemic_state' => EpistemicState::UNVERIFIED,
            'confidence_score' => 0.70,
        ]);

        // Supporting evidence from source A
        $snippetA = $deepGraph->recordSnippet(EvidenceSnippetDTO::fromArray([
            'mission_id' => $mission->id,
            'source_id' => $sourceA->id,
            'extract_text' => 'In localized microbenchmarks, sub-millisecond responses were observed.',
            'verbatim_quote' => 'sub-millisecond responses were observed in benchmark runs',
            'confidence_score' => 0.80,
        ]));
        $deepGraph->linkClaimToEvidence($claim->id, $snippetA->id, EvidenceRelationType::SUPPORTS);

        // Strong refuting evidence from source B
        $snippetB = $deepGraph->recordSnippet(EvidenceSnippetDTO::fromArray([
            'mission_id' => $mission->id,
            'source_id' => $sourceB->id,
            'extract_text' => 'Under network partitions, CAP theorem prevents both write availability and consistency without latency spikes.',
            'verbatim_quote' => 'network partitions trigger write latency spikes exceeding 50ms',
            'confidence_score' => 0.95,
        ]));
        $deepGraph->linkClaimToEvidence($claim->id, $snippetB->id, EvidenceRelationType::REFUTES);

        $consensus = $deepGraph->calculateConsensus($claim->id);

        $this->assertArrayHasKey('consensusScore', $consensus);
        $this->assertArrayHasKey('is_contradicted', $consensus);
        $this->assertSame(1, $consensus['supportCount']);
        $this->assertSame(1, $consensus['refuteCount']);
        // Refuting evidence exists with high weight -> flagged contradicted
        $this->assertTrue($consensus['is_contradicted']);
        $this->assertSame(EpistemicState::CONTRADICTED, $consensus['epistemicState']);
    }

    public function test_deep_evidence_graph_traces_complete_deep_lineage(): void
    {
        $deepGraph = app(DeepEvidenceGraphService::class);

        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Vite 5 Build Optimizations',
            'primary_objective' => 'Deep dive into Vite Rollup engine',
        ]);
        $mission = $missionData['mission'];

        $doc = Document::create([
            'user_id' => $this->user->id,
            'title' => 'Mastering Vite 5 Architecture',
            'slug' => 'mastering-vite-5-architecture',
            'content' => '<p>Vite uses Rollup for production builds.</p>',
        ]);

        $source = SourceIntelligence::create([
            'url_hash' => hash('sha256', 'https://vitejs.dev/guide'),
            'url' => 'https://vitejs.dev/guide',
            'title' => 'Vite 5 Documentation',
            'source_type' => SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
            'reliability_score' => 95,
            'domain_authority' => 95,
            'is_primary' => true,
        ]);

        $claim = ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'statement' => 'Vite 5 leverages Rollup for production bundling.',
            'epistemic_state' => EpistemicState::VERIFIED,
            'confidence_score' => 0.98,
        ]);

        $snippet = $deepGraph->recordSnippet(EvidenceSnippetDTO::fromArray([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'extract_text' => 'Vite uses Rollup under the hood for production build generation.',
            'verbatim_quote' => 'Vite uses Rollup under the hood for production build generation',
            'confidence_score' => 0.99,
        ]));

        $deepGraph->linkClaimToEvidence($claim->id, $snippet->id, EvidenceRelationType::SUPPORTS);

        $sentence = ArticleElementNode::create([
            'id' => 'elem_test_'.Str::lower(Str::random(8)),
            'mission_id' => $mission->id,
            'document_id' => $doc->id,
            'claim_id' => $claim->id,
            'section_index' => 1,
            'sentence_index' => 1,
            'text_content' => 'Vite utilizes Rollup under the hood to ensure tree-shaken production bundles.',
            'is_stale' => false,
        ]);

        $lineage = $deepGraph->getDeepLineage($claim->id);

        $this->assertNotEmpty($lineage);
        $this->assertSame($claim->id, $lineage['claim_id']);
        $this->assertSame('Vite 5 Documentation', $lineage['primary_source']['title']);
        $this->assertCount(1, $lineage['evidence_nodes']);
        $this->assertSame('supports', $lineage['evidence_nodes'][0]['relation']);
        $this->assertCount(1, $lineage['article_sentences']);
        $this->assertSame($sentence->text_content, $lineage['article_sentences'][0]['sentence_text']);
        $this->assertStringContainsString('Source #', $lineage['lineage_chain']);
        $this->assertStringContainsString('Evidence Extracts', $lineage['lineage_chain']);
        $this->assertStringContainsString('Claim #', $lineage['lineage_chain']);
    }

    public function test_truth_layer_audits_mission_epistemic_state(): void
    {
        $truthLayer = app(TruthLayerService::class);

        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Database Indexing Strategies',
            'primary_objective' => 'Indexing best practices guide',
        ]);
        $mission = $missionData['mission'];

        $source = SourceIntelligence::create([
            'url_hash' => hash('sha256', 'https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html'),
            'url' => 'https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html',
            'title' => 'MySQL 8.0 Performance Guide',
            'source_type' => SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
            'reliability_score' => 92,
            'domain_authority' => 92,
            'is_primary' => true,
        ]);

        // Claim 1: Verified
        ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'statement' => 'B-Tree indexes optimize equality and range lookups.',
            'epistemic_state' => EpistemicState::VERIFIED,
            'confidence_score' => 0.95,
        ]);

        // Claim 2: Partially verified
        ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'statement' => 'Hash indexes always outperform B-Trees on point queries.',
            'epistemic_state' => EpistemicState::PARTIALLY_VERIFIED,
            'confidence_score' => 0.70,
        ]);

        // Claim 3: Contradicted
        ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'statement' => 'Indexing every single column guarantees instant query execution.',
            'epistemic_state' => EpistemicState::CONTRADICTED,
            'confidence_score' => 0.10,
        ]);

        $report = $truthLayer->auditMission($mission);

        $this->assertInstanceOf(TruthAuditReportDTO::class, $report);
        $this->assertSame(3, $report->totalClaims);
        $this->assertSame(1, $report->verifiedCount);
        $this->assertSame(1, $report->partiallyVerifiedCount);
        $this->assertSame(1, $report->contradictedCount);
        $this->assertLessThan(100.0, $report->overallTruthScore);
        $this->assertNotEmpty($report->recommendedActions);
        $this->assertNotEmpty($report->unresolvedIssues);
    }

    public function test_knowledge_fabric_integrates_world_model_and_evidence_snippets(): void
    {
        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Enterprise API Gateway Architecture',
            'primary_objective' => 'Gateway benchmark',
        ]);
        $mission = $missionData['mission'];
        $missionDTO = ContentMissionDTO::fromArray($mission->toArray());

        $planDTO = ResearchPlanDTO::fromArray([
            'rationale' => 'Deep dive into gateway proxying engines',
        ]);

        $fabric = app(KnowledgeFabricService::class);
        $result = $fabric->synthesize($mission, $missionDTO, $planDTO);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('world_entities', [
            'name' => 'Enterprise API Gateway Architecture',
        ]);
        $this->assertDatabaseHas('evidence_snippets', [
            'mission_id' => $mission->id,
        ]);
        $this->assertDatabaseHas('claim_evidence_links', []);
    }

    public function test_livewire_content_intelligence_page_renders_world_truth_tab_and_traces_lineage(): void
    {
        $missionData = (new CreateContentMission)->execute($this->user, [
            'topic' => 'Distributed Microservices Architecture',
            'primary_objective' => 'Microservices production guide',
        ]);
        $mission = $missionData['mission'];

        $run = WorkflowRun::create([
            'mission_id' => $mission->id,
            'user_id' => $this->user->id,
            'current_node' => 'knowledge_fabric',
            'status' => ContentWorkflowStatus::RUNNING,
        ]);

        $source = SourceIntelligence::create([
            'url_hash' => hash('sha256', 'https://kubernetes.io/docs'),
            'url' => 'https://kubernetes.io/docs',
            'title' => 'Kubernetes Core Documentation',
            'source_type' => SourceReliabilityTier::OFFICIAL_DOCUMENTATION,
            'reliability_score' => 95,
            'domain_authority' => 95,
            'is_primary' => true,
        ]);

        $claim = ClaimNode::create([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'statement' => 'Kubernetes Pods encapsulate one or more containers sharing network and storage.',
            'epistemic_state' => EpistemicState::VERIFIED,
            'confidence_score' => 0.98,
        ]);

        $deepGraph = app(DeepEvidenceGraphService::class);
        $snippet = $deepGraph->recordSnippet(EvidenceSnippetDTO::fromArray([
            'mission_id' => $mission->id,
            'source_id' => $source->id,
            'extract_text' => 'A Pod is the smallest execution unit in Kubernetes.',
            'verbatim_quote' => 'A Pod is the smallest execution unit in Kubernetes',
            'confidence_score' => 0.95,
        ]));

        $deepGraph->linkClaimToEvidence($claim->id, $snippet->id, EvidenceRelationType::SUPPORTS);

        $worldModel = app(WorldModelService::class);
        $worldModel->registerEntity(WorldEntityDTO::fromArray([
            'name' => 'Kubernetes Pod',
            'category' => 'infrastructure_primitive',
            'slug' => 'k8s-pod',
            'description' => 'Smallest deployable unit of computing in Kubernetes',
        ]));

        Livewire::actingAs($this->user)
            ->test(ContentIntelligencePage::class)
            ->set('selectedRunId', $run->id)
            ->call('setInspectorTab', 'world_truth')
            ->assertSee('Truth Layer Epistemic Audit')
            ->assertSee('Deep Lineage Trace')
            ->assertSee('World Model Domain Knowledge Graph')
            ->assertSee('Kubernetes Pod')
            ->call('selectClaim', $claim->id)
            ->assertSee('Lineage Focus Claim #'.$claim->id)
            ->assertSee('A Pod is the smallest execution unit in Kubernetes');
    }

    public function test_unauthenticated_user_cannot_access_content_intelligence_page(): void
    {
        $response = $this->get(route('content-intelligence.index'));
        $response->assertRedirect(route('login'));
    }
}

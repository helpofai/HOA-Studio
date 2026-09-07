<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Genome Service
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

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\ContentGenomeDTO;
use App\Features\ContentIntelligence\Models\ClaimNode;
use App\Features\ContentIntelligence\Models\ContentGenome;
use App\Features\ContentIntelligence\Models\EvidenceSnippet;
use App\Features\ContentIntelligence\Models\QualityHealthAudit;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use App\Features\ContentIntelligence\Models\WorldEntity;
use App\Features\Documents\Models\Document;

class ContentGenomeService
{
    /**
     * Synthesize a comprehensive, reusable Content Genome from a completed or active workflow run.
     */
    public function synthesizeGenome(WorkflowRun $run): ContentGenome
    {
        $mission = $run->mission;
        $title = $mission->topic ?? 'Content Mission #'.$run->id;
        $userId = $run->user_id;
        $projectId = $mission->project_id ?? null;

        // 1. Mission DNA
        $missionDna = [
            'goal' => $mission->primary_objective ?? 'Enterprise Content Production',
            'audience' => $mission->target_audience ?? 'Professional Practitioners',
            'target_topic' => $mission->topic ?? 'Technology & Architecture',
            'search_intent' => $mission->search_goal ?? 'Informational / Authoritative',
            'tone' => $mission->business_goal ?? 'Professional',
        ];

        // 2. Topics DNA
        $topicsDna = [
            'primary_topic' => $mission->topic ?? 'General Domain',
            'keywords' => $run->getGraphStateValue('focus_keywords', []),
            'secondary_topics' => $run->getGraphStateValue('secondary_topics', []),
        ];

        // 3. Entities DNA (Gather from WorldEntity if available or from text)
        $entities = WorldEntity::where('project_id', $projectId)->take(15)->get();
        $entitiesDna = $entities->map(fn (WorldEntity $e) => [
            'name' => $e->canonical_name,
            'type' => $e->entity_type,
            'description' => $e->description,
        ])->toArray();

        // 4. Claims DNA (Gather verified claims)
        $claims = ClaimNode::where('workflow_run_id', $run->id)->take(20)->get();
        $claimsDna = $claims->map(fn (ClaimNode $c) => [
            'claim_text' => $c->claim_text,
            'epistemic_state' => $c->epistemic_state,
            'confidence' => $c->confidence_score,
        ])->toArray();

        // 5. Facts & Benchmarks DNA
        $documentContent = $run->getGraphStateValue('document_content', '');
        preg_match_all('/\b\d+(\.\d+)?%|\b\$\d+(\.\d+)?|\b\d+\s*(ms|seconds|minutes|hours|GB|MB)\b/i', $documentContent, $statMatches);
        $factsDna = array_values(array_unique($statMatches[0] ?? []));

        // 6. Sources DNA
        $snippets = EvidenceSnippet::where('project_id', $projectId)->take(10)->get();
        $sourcesDna = $snippets->map(fn (EvidenceSnippet $s) => [
            'source_title' => $s->source->source_title ?? 'Domain Documentation',
            'verbatim_quote' => $s->verbatim_quote,
            'reliability' => $s->source->reliability_tier ?? 'authoritative',
        ])->toArray();

        // 7. Quality DNA
        $audit = QualityHealthAudit::where('workflow_run_id', $run->id)->first();
        $qualityDna = [
            'overall_score' => $audit?->overall_score ?? 85,
            'grade' => $audit?->grade ?? 'B',
            'strengths' => $audit?->key_strengths ?? [],
            'recommendations' => $audit?->recommendations ?? [],
        ];

        // 8. Reusable Knowledge Fragments (Modular takeaways for future missions)
        $reusableFragments = [
            'core_summary' => "Authoritative guide on {$missionDna['target_topic']} established with {$qualityDna['grade']} rating.",
            'key_entities' => array_column($entitiesDna, 'name'),
            'verified_claims_count' => count($claimsDna),
            'benchmark_facts' => array_slice($factsDna, 0, 8),
        ];

        $signature = hash('sha256', "genome:{$userId}:{$projectId}:{$missionDna['target_topic']}:".substr($title, 0, 50));

        $dto = new ContentGenomeDTO(
            genomeSignature: $signature,
            title: $title,
            missionDna: $missionDna,
            topicsDna: $topicsDna,
            entitiesDna: $entitiesDna,
            claimsDna: $claimsDna,
            factsDna: $factsDna,
            sourcesDna: $sourcesDna,
            qualityDna: $qualityDna,
            reusableFragments: $reusableFragments
        );

        return ContentGenome::updateOrCreate(
            ['genome_signature' => $dto->genomeSignature],
            [
                'user_id' => $userId,
                'project_id' => $projectId,
                'workflow_run_id' => $run->id,
                'document_id' => $run->document_id,
                'title' => $dto->title,
                'mission_dna' => $dto->missionDna,
                'topics_dna' => $dto->topicsDna,
                'entities_dna' => $dto->entitiesDna,
                'claims_dna' => $dto->claimsDna,
                'facts_dna' => $dto->factsDna,
                'sources_dna' => $dto->sourcesDna,
                'quality_dna' => $dto->qualityDna,
                'reusable_fragments' => $dto->reusableFragments,
            ]
        );
    }

    /**
     * Synthesize a comprehensive, reusable Content Genome directly from a Document instance.
     * Enables 1-click snapshotting inside TipTap and Document Editor.
     */
    public function synthesizeDocumentGenome(Document $document, array $customMeta = []): ContentGenome
    {
        $userId = $document->user_id;
        $projectId = $document->project_id;
        $title = $document->title ?: 'Document #'.$document->id;
        $documentContent = (string) ($document->content?->content_plain ?? strip_tags($document->content?->content_html ?? ''));

        // 1. Mission DNA
        $missionDna = [
            'goal' => $customMeta['goal'] ?? 'Authoritative Content Production',
            'audience' => $customMeta['audience'] ?? 'Enterprise Readers',
            'target_topic' => $customMeta['topic'] ?? $title,
            'search_intent' => $customMeta['intent'] ?? 'Informational / Authoritative',
            'tone' => $customMeta['tone'] ?? 'Professional',
        ];

        // 2. Topics DNA
        $topicsDna = [
            'primary_topic' => $title,
            'keywords' => $customMeta['keywords'] ?? array_slice(array_filter(explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i', '', $title))), fn ($w) => strlen($w) > 3), 0, 8),
            'secondary_topics' => $customMeta['secondary_topics'] ?? [],
        ];

        // 3. Entities DNA
        $entities = WorldEntity::where('project_id', $projectId)->take(15)->get();
        $entitiesDna = $entities->map(fn (WorldEntity $e) => [
            'name' => $e->canonical_name,
            'type' => $e->entity_type,
            'description' => $e->description,
        ])->toArray();

        // 4. Claims DNA
        $claims = ClaimNode::where('user_id', $userId)->take(20)->get();
        $claimsDna = $claims->map(fn (ClaimNode $c) => [
            'claim_text' => $c->claim_text,
            'epistemic_state' => $c->epistemic_state,
            'confidence' => $c->confidence_score,
        ])->toArray();

        // 5. Facts & Benchmarks DNA
        preg_match_all('/\b\d+(\.\d+)?%|\b\$\d+(\.\d+)?|\b\d+\s*(ms|seconds|minutes|hours|GB|MB)\b/i', $documentContent, $statMatches);
        $factsDna = array_values(array_unique($statMatches[0] ?? []));

        // 6. Sources DNA
        $snippets = EvidenceSnippet::where('project_id', $projectId)->take(10)->get();
        $sourcesDna = $snippets->map(fn (EvidenceSnippet $s) => [
            'source_title' => $s->source->source_title ?? 'Domain Documentation',
            'verbatim_quote' => $s->verbatim_quote,
            'reliability' => $s->source->reliability_tier ?? 'authoritative',
        ])->toArray();

        // 7. Quality DNA
        $qualityDna = [
            'overall_score' => $customMeta['quality_score'] ?? 88,
            'grade' => $customMeta['quality_grade'] ?? 'B+',
            'strengths' => $customMeta['strengths'] ?? ['Empirical clarity', 'Consistent vocabulary'],
            'recommendations' => $customMeta['recommendations'] ?? ['Maintain updated references'],
        ];

        // 8. Reusable Fragments
        $reusableFragments = [
            'core_summary' => "Authoritative snapshot for {$title} recorded with {$qualityDna['grade']} rating.",
            'key_entities' => array_column($entitiesDna, 'name'),
            'verified_claims_count' => count($claimsDna),
            'benchmark_facts' => array_slice($factsDna, 0, 8),
        ];

        $signature = hash('sha256', "genome:doc:{$userId}:{$projectId}:{$document->id}:".substr($title, 0, 40));

        $dto = new ContentGenomeDTO(
            genomeSignature: $signature,
            title: $title,
            missionDna: $missionDna,
            topicsDna: $topicsDna,
            entitiesDna: $entitiesDna,
            claimsDna: $claimsDna,
            factsDna: $factsDna,
            sourcesDna: $sourcesDna,
            qualityDna: $qualityDna,
            reusableFragments: $reusableFragments
        );

        return ContentGenome::updateOrCreate(
            ['genome_signature' => $dto->genomeSignature],
            [
                'user_id' => $userId,
                'project_id' => $projectId,
                'workflow_run_id' => null,
                'document_id' => $document->id,
                'title' => $dto->title,
                'mission_dna' => $dto->missionDna,
                'topics_dna' => $dto->topicsDna,
                'entities_dna' => $dto->entitiesDna,
                'claims_dna' => $dto->claimsDna,
                'facts_dna' => $dto->factsDna,
                'sources_dna' => $dto->sourcesDna,
                'quality_dna' => $dto->qualityDna,
                'reusable_fragments' => $dto->reusableFragments,
            ]
        );
    }

    /**
     * Inherit reusable knowledge from existing Content Genomes for a new mission.
     * Prevents starting from zero on related topics.
     *
     * @return array{inherited_claims: array, inherited_entities: array, inherited_sources: array}
     */
    public function inheritKnowledge(int $userId, string $topic): array
    {
        $genomes = ContentGenome::where('user_id', $userId)
            ->where(function ($query) use ($topic) {
                $query->where('title', 'like', "%{$topic}%")
                    ->orWhere('topics_dna', 'like', "%{$topic}%");
            })
            ->latest('id')
            ->take(3)
            ->get();

        $inheritedClaims = [];
        $inheritedEntities = [];
        $inheritedSources = [];

        foreach ($genomes as $genome) {
            foreach ($genome->claims_dna ?? [] as $c) {
                $inheritedClaims[] = $c;
            }
            foreach ($genome->entities_dna ?? [] as $e) {
                $inheritedEntities[] = $e;
            }
            foreach ($genome->sources_dna ?? [] as $s) {
                $inheritedSources[] = $s;
            }
        }

        return [
            'inherited_claims' => array_slice($inheritedClaims, 0, 10),
            'inherited_entities' => array_slice($inheritedEntities, 0, 10),
            'inherited_sources' => array_slice($inheritedSources, 0, 10),
            'genomes_consulted' => $genomes->count(),
        ];
    }
}

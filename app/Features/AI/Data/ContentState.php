<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - ContentState DTO
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

namespace App\Features\AI\Data;

/**
 * Master State DTO for the 24-Stage Knowledge-First Article Engine.
 * Threaded through stages with strict versioning and state transition tracking.
 */
class ContentState
{
    public int $stage = 1;

    public string $status = 'pending';

    public int $version = 1;

    public ArticleMission $mission;

    public AudienceProfile $audience;

    public IntentContract $intent;

    public KeywordUniverse $keywordUniverse;

    public KnowledgeGraph $knowledgeGraph;

    public ContentBlueprint $blueprint;

    public StructuredArticle $structuredArticle;

    public array $stageOutputs = [];

    public array $warnings = [];

    public array $unresolvedQuestions = [];

    public function __construct(string $topic = 'Untitled Topic')
    {
        $this->mission = new ArticleMission(topic: $topic);
        $this->audience = new AudienceProfile;
        $this->intent = new IntentContract;
        $this->keywordUniverse = new KeywordUniverse(primaryKeyword: $topic);
        $this->knowledgeGraph = new KnowledgeGraph;
        $this->blueprint = new ContentBlueprint;
        $this->structuredArticle = new StructuredArticle;
    }

    public static function fromArray(array $data): self
    {
        $state = new self($data['mission']['topic'] ?? 'Untitled Topic');
        $state->stage = $data['stage'] ?? 1;
        $state->status = $data['status'] ?? 'pending';
        $state->version = $data['version'] ?? 1;

        if (isset($data['mission'])) {
            $state->mission = ArticleMission::fromArray($data['mission']);
        }
        if (isset($data['audience'])) {
            $state->audience = AudienceProfile::fromArray($data['audience']);
        }
        if (isset($data['intent'])) {
            $state->intent = IntentContract::fromArray($data['intent']);
        }
        if (isset($data['keyword_universe'])) {
            $state->keywordUniverse = KeywordUniverse::fromArray($data['keyword_universe']);
        }
        if (isset($data['knowledge_graph'])) {
            $state->knowledgeGraph = KnowledgeGraph::fromArray($data['knowledge_graph']);
        }
        if (isset($data['blueprint'])) {
            $state->blueprint = ContentBlueprint::fromArray($data['blueprint']);
        }
        if (isset($data['structured_article'])) {
            $state->structuredArticle = StructuredArticle::fromArray($data['structured_article']);
        }

        $state->stageOutputs = $data['stage_outputs'] ?? [];
        $state->warnings = $data['warnings'] ?? [];
        $state->unresolvedQuestions = $data['unresolved_questions'] ?? [];

        return $state;
    }

    public function advanceStage(int $nextStage, array $outputData = []): void
    {
        $this->stageOutputs[$this->stage] = [
            'stage' => $this->stage,
            'status' => 'completed',
            'output' => $outputData,
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        $this->stage = $nextStage;
        $this->version++;
        $this->status = 'in_progress';
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = [
            'stage' => $this->stage,
            'message' => $warning,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function toArray(): array
    {
        return [
            'stage' => $this->stage,
            'status' => $this->status,
            'version' => $this->version,
            'mission' => $this->mission->toArray(),
            'audience' => $this->audience->toArray(),
            'intent' => $this->intent->toArray(),
            'keyword_universe' => $this->keywordUniverse->toArray(),
            'knowledge_graph' => $this->knowledgeGraph->toArray(),
            'blueprint' => $this->blueprint->toArray(),
            'structured_article' => $this->structuredArticle->toArray(),
            'stage_outputs' => $this->stageOutputs,
            'warnings' => $this->warnings,
            'unresolved_questions' => $this->unresolvedQuestions,
        ];
    }
}

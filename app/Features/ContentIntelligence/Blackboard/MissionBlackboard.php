<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Mission Blackboard
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

namespace App\Features\ContentIntelligence\Blackboard;

use App\Features\ContentIntelligence\Contracts\BlackboardInterface;
use App\Features\ContentIntelligence\Models\WorkflowRun;

class MissionBlackboard implements BlackboardInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $initialData
     */
    public function __construct(array $initialData = [])
    {
        $this->data = array_merge([
            'mission_topic' => null,
            'current_goal' => null,
            'current_subgoal' => null,
            'known_facts' => [],
            'important_entities' => [],
            'hypotheses' => [],
            'open_questions' => [],
            'contradictions' => [],
            'risks' => [],
            'active_tasks' => [],
            'decisions' => [],
            'next_best_action' => null,
            'confidence' => 0.95,
        ], $initialData);
    }

    public static function fromRun(WorkflowRun $run): self
    {
        $stateData = $run->state_data ?? [];
        $blackboardData = $stateData['blackboard'] ?? [];

        if (empty($blackboardData['mission_topic']) && $run->mission) {
            $blackboardData['mission_topic'] = $run->mission->topic;
            $blackboardData['current_goal'] = $run->mission->primary_objective;
        }

        return new self($blackboardData);
    }

    public function saveToRun(WorkflowRun $run): void
    {
        $stateData = $run->state_data ?? [];
        $stateData['blackboard'] = $this->data;
        $run->state_data = $stateData;
        $run->save();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        data_set($this->data, $key, $value);
    }

    public function has(string $key): bool
    {
        return ! is_null(data_get($this->data, $key));
    }

    public function push(string $key, mixed $value): void
    {
        $current = data_get($this->data, $key, []);
        if (! is_array($current)) {
            $current = [$current];
        }
        $current[] = $value;
        data_set($this->data, $key, $current);
    }

    /**
     * Export full blackboard snapshot.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}

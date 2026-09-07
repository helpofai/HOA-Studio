<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Agent Interface
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

namespace App\Features\ContentIntelligence\Contracts;

use App\Features\ContentIntelligence\DTOs\AgentResultDTO;
use App\Features\ContentIntelligence\DTOs\AgentTaskDTO;
use App\Features\ContentIntelligence\Enums\AgentRole;
use App\Features\ContentIntelligence\Enums\TaskType;

interface AgentInterface
{
    /**
     * Unique identifier name for the agent (e.g. 'researcher', 'analyst').
     */
    public function getName(): string;

    /**
     * Specialized role enum for the agent.
     */
    public function getRole(): AgentRole;

    /**
     * Human-readable description of what this agent does.
     */
    public function getDescription(): string;

    /**
     * Array of TaskType enums that this agent is equipped to execute.
     *
     * @return array<TaskType>
     */
    public function getSupportedTaskTypes(): array;

    /**
     * Execute a specific task with access to the shared Brain Blackboard.
     */
    public function execute(AgentTaskDTO $task, BlackboardInterface $blackboard): AgentResultDTO;
}

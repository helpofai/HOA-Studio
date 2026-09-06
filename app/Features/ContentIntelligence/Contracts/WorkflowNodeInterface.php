<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Workflow Node Interface
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

use App\Features\ContentIntelligence\DTOs\WorkflowNodeResultDTO;
use App\Features\ContentIntelligence\Models\WorkflowRun;

interface WorkflowNodeInterface
{
    /**
     * Unique identifier/name of this node in the graph.
     */
    public function getName(): string;

    /**
     * Human-readable description of what this node performs.
     */
    public function getDescription(): string;

    /**
     * Execute the node logic against the workflow run and structured context.
     *
     * @param  array<string, mixed>  $context
     */
    public function execute(WorkflowRun $run, array $context): WorkflowNodeResultDTO;
}

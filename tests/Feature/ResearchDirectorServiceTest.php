<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - ResearchDirectorService Test
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

use App\Features\AI\Data\ContentState;
use App\Features\AI\Services\ResearchDirectorService;
use App\Models\User;
use Tests\TestCase;

class ResearchDirectorServiceTest extends TestCase
{
    public function test_research_director_generates_tasks_and_populates_knowledge_graph(): void
    {
        $user = User::factory()->make();

        $service = app(ResearchDirectorService::class);
        $state = new ContentState('Livewire 3 Reactivity');

        $plan = $service->generateResearchPlan($state);
        $this->assertGreaterThanOrEqual(3, count($plan));
        $this->assertEquals('critical', $plan[0]['priority']);

        $updatedState = $service->executeResearch($state, $user);

        $this->assertEquals(14, $updatedState->stage);
        $this->assertGreaterThan(0, count($updatedState->knowledgeGraph->sources));
        $this->assertGreaterThan(0, count($updatedState->knowledgeGraph->evidence));
        $this->assertGreaterThan(0, count($updatedState->knowledgeGraph->claims));
    }
}

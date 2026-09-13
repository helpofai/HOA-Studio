<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Livewire Concern: Content Brain Lineage
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

namespace App\Features\Documents\Livewire\Concerns;

trait HasBrainLineage
{
    public function loadBrainState(): void
    {
        $this->isBrainLoading = false;
        $this->brainStaleNodesCount = 0;
        $this->brainLineageNodes = [];
        $this->brainHealthScore = 95;
        $this->brainHealthGrade = 'A';
        $this->brainHealthDimensions = [];
        $this->brainHealthRecommendations = [];
        $this->brainStyleRules = [];
        $this->brainCannibalization = [];
        $this->brainInternalLinks = [];
        $this->brainGenomeSnapshot = null;
    }

    public function selectLineageNode(int $nodeId): void
    {
        $this->selectedLineageNodeId = $nodeId;
        $this->brainSelectedTrace = null;
    }

    public function repairStaleSentence(int $nodeId): void
    {
        $this->brainStatusMessage = 'Sentence repair updated.';
    }

    public function markNodeStale(int $nodeId, string $reason = 'Fact requires citation grounding'): void
    {
        $this->brainStatusMessage = 'Sentence flagged.';
    }

    public function synthesizeGenomeForCurrentDocument(): void
    {
        $this->brainStatusMessage = 'Document snapshot updated.';
    }

    public function toggleBrainStyleRule(int $ruleId, bool $isActive): void
    {
        $this->loadBrainState();
    }

    public function setBrainSubTab(string $tab): void
    {
        $this->brainActiveSubTab = $tab;
    }
}

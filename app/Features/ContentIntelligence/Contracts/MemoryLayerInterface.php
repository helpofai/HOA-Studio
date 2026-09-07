<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Layer Interface
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

use App\Features\ContentIntelligence\DTOs\MemoryObjectDTO;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Models\BrainMemory;
use Illuminate\Support\Collection;

interface MemoryLayerInterface
{
    /**
     * Get the specific cognitive layer handled by this implementer.
     */
    public function getLayerName(): MemoryLayerType;

    /**
     * Store a memory object into this layer.
     */
    public function store(MemoryObjectDTO $memory): BrainMemory;

    /**
     * Retrieve memories matching specified criteria from this layer.
     *
     * @param  array<string, mixed>  $criteria
     * @return Collection<int, BrainMemory>
     */
    public function retrieve(array $criteria = []): Collection;
}

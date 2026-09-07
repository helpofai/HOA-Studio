<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - World Model Interface
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

use App\Features\ContentIntelligence\DTOs\WorldEntityDTO;
use App\Features\ContentIntelligence\DTOs\WorldRelationshipDTO;
use App\Features\ContentIntelligence\Models\WorldEntity;
use App\Features\ContentIntelligence\Models\WorldRelationship;

interface WorldModelInterface
{
    /**
     * Register or update a named domain entity in the world model.
     */
    public function registerEntity(WorldEntityDTO $dto): WorldEntity;

    /**
     * Link two entities with a directed semantic relationship.
     */
    public function linkEntities(WorldRelationshipDTO $dto): WorldRelationship;

    /**
     * Resolve an entity by name, alias, or slug.
     */
    public function findEntity(string $query, ?int $userId = null): ?WorldEntity;

    /**
     * Traverse the entity graph to uncover relationships, dependencies, and incompatibilities.
     *
     * @return array<string, mixed>
     */
    public function getEntityGraph(string $entityId, int $depth = 2): array;
}

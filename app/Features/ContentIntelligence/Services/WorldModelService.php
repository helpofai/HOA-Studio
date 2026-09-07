<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - World Model Service
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

use App\Features\ContentIntelligence\Contracts\WorldModelInterface;
use App\Features\ContentIntelligence\DTOs\WorldEntityDTO;
use App\Features\ContentIntelligence\DTOs\WorldRelationshipDTO;
use App\Features\ContentIntelligence\Models\WorldEntity;
use App\Features\ContentIntelligence\Models\WorldRelationship;
use Illuminate\Support\Str;

class WorldModelService implements WorldModelInterface
{
    /**
     * Register or update a domain entity in the world model.
     */
    public function registerEntity(WorldEntityDTO $dto): WorldEntity
    {
        return WorldEntity::updateOrCreate(
            ['slug' => $dto->slug],
            [
                'id' => $dto->id,
                'user_id' => $dto->userId,
                'name' => $dto->name,
                'category' => $dto->category,
                'description' => $dto->description,
                'aliases' => $dto->aliases,
                'attributes' => $dto->attributes,
                'temporal_valid_from' => $dto->temporalValidFrom,
                'temporal_valid_until' => $dto->temporalValidUntil,
            ]
        );
    }

    /**
     * Link two entities with a directed semantic relationship.
     */
    public function linkEntities(WorldRelationshipDTO $dto): WorldRelationship
    {
        return WorldRelationship::updateOrCreate(
            [
                'subject_entity_id' => $dto->subjectEntityId,
                'predicate' => $dto->predicate,
                'object_entity_id' => $dto->objectEntityId,
            ],
            [
                'id' => $dto->id ?: 'rel_'.Str::lower(Str::random(10)),
                'confidence' => $dto->confidence,
                'strength' => $dto->strength,
                'explanation' => $dto->explanation,
            ]
        );
    }

    /**
     * Find an entity by exact name, slug, or alias.
     */
    public function findEntity(string $query, ?int $userId = null): ?WorldEntity
    {
        $normalized = trim(strtolower($query));
        $slug = Str::slug($query);

        $dbQuery = WorldEntity::query();
        if ($userId) {
            $dbQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            });
        }

        return $dbQuery->where(function ($q) use ($normalized, $slug) {
            $q->whereRaw('LOWER(name) = ?', [$normalized])
                ->orWhere('slug', $slug)
                ->orWhereJsonContains('aliases', $normalized);
        })->first();
    }

    /**
     * Traverse the entity graph to uncover relationships, dependencies, and incompatibilities.
     *
     * @return array<string, mixed>
     */
    public function getEntityGraph(string $entityId, int $depth = 2): array
    {
        $entity = WorldEntity::with(['outgoingRelationships.objectEntity', 'incomingRelationships.subjectEntity'])->find($entityId);
        if (! $entity) {
            return [];
        }

        $nodes = [
            $entity->id => [
                'id' => $entity->id,
                'name' => $entity->name,
                'category' => $entity->category,
            ],
        ];

        $edges = [];

        foreach ($entity->outgoingRelationships as $rel) {
            if ($rel->objectEntity) {
                $nodes[$rel->objectEntity->id] = [
                    'id' => $rel->objectEntity->id,
                    'name' => $rel->objectEntity->name,
                    'category' => $rel->objectEntity->category,
                ];

                $edges[] = [
                    'source' => $entity->id,
                    'target' => $rel->objectEntity->id,
                    'predicate' => $rel->predicate,
                    'confidence' => $rel->confidence,
                ];
            }
        }

        foreach ($entity->incomingRelationships as $rel) {
            if ($rel->subjectEntity) {
                $nodes[$rel->subjectEntity->id] = [
                    'id' => $rel->subjectEntity->id,
                    'name' => $rel->subjectEntity->name,
                    'category' => $rel->subjectEntity->category,
                ];

                $edges[] = [
                    'source' => $rel->subjectEntity->id,
                    'target' => $entity->id,
                    'predicate' => $rel->predicate,
                    'confidence' => $rel->confidence,
                ];
            }
        }

        return [
            'root_entity' => $entity->name,
            'nodes' => array_values($nodes),
            'edges' => $edges,
            'node_count' => count($nodes),
            'edge_count' => count($edges),
        ];
    }

    /**
     * Check if any entities in a candidate set have recorded incompatibility conflicts.
     *
     * @param  array<int, string>  $entityNames
     * @return array<int, array{entityA: string, entityB: string, reason: string}>
     */
    public function detectIncompatibilities(array $entityNames): array
    {
        $resolvedIds = [];
        $nameMap = [];

        foreach ($entityNames as $name) {
            $entity = $this->findEntity($name);
            if ($entity) {
                $resolvedIds[] = $entity->id;
                $nameMap[$entity->id] = $entity->name;
            }
        }

        if (count($resolvedIds) < 2) {
            return [];
        }

        $conflicts = WorldRelationship::query()
            ->whereIn('subject_entity_id', $resolvedIds)
            ->whereIn('object_entity_id', $resolvedIds)
            ->whereIn('predicate', ['incompatible_with', 'conflicts_with', 'deprecated_by'])
            ->get();

        $results = [];
        foreach ($conflicts as $conflict) {
            $results[] = [
                'entityA' => $nameMap[$conflict->subject_entity_id] ?? $conflict->subject_entity_id,
                'entityB' => $nameMap[$conflict->object_entity_id] ?? $conflict->object_entity_id,
                'reason' => $conflict->explanation ?: "Relationship '{$conflict->predicate}' detected in World Model.",
            ];
        }

        return $results;
    }

    /**
     * Retrieve prerequisite chain for a specified entity.
     *
     * @return array<int, string>
     */
    public function getPrerequisites(string $entityName): array
    {
        $entity = $this->findEntity($entityName);
        if (! $entity) {
            return [];
        }

        return WorldRelationship::query()
            ->where('subject_entity_id', $entity->id)
            ->where('predicate', 'requires')
            ->with('objectEntity')
            ->get()
            ->pluck('objectEntity.name')
            ->filter()
            ->values()
            ->toArray();
    }
}

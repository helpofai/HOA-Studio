<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Brain Memory Model
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

namespace App\Features\ContentIntelligence\Models;

use App\Features\ContentIntelligence\Enums\BrainScope;
use App\Features\ContentIntelligence\Enums\EpistemicState;
use App\Features\ContentIntelligence\Enums\MemoryLayerType;
use App\Features\ContentIntelligence\Enums\MemoryStatus;
use App\Features\Documents\Models\Document;
use App\Features\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrainMemory extends Model
{
    use HasFactory;

    protected $table = 'brain_memories';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'scope' => BrainScope::class,
        'layer' => MemoryLayerType::class,
        'status' => MemoryStatus::class,
        'epistemic_state' => EpistemicState::class,
        'provenance' => 'array',
        'relationships' => 'array',
        'lineage' => 'array',
        'confidence' => 'float',
        'authority' => 'integer',
        'freshness_score' => 'float',
        'version' => 'integer',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceIntelligence::class, 'source_id');
    }

    public function parentMemory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'lineage_parent_id');
    }

    public function childMemories(): HasMany
    {
        return $this->hasMany(self::class, 'lineage_parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MemoryStatus::ACTIVE->value);
    }

    public function scopeByScope(Builder $query, BrainScope|string $scope): Builder
    {
        $val = $scope instanceof BrainScope ? $scope->value : $scope;

        return $query->where('scope', $val);
    }

    public function scopeByLayer(Builder $query, MemoryLayerType|string $layer): Builder
    {
        $val = $layer instanceof MemoryLayerType ? $layer->value : $layer;

        return $query->where('layer', $val);
    }
}

<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - World Entity Model
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

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorldEntity extends Model
{
    use HasFactory;

    protected $table = 'world_entities';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'aliases' => 'array',
        'attributes' => 'array',
        'temporal_valid_from' => 'date',
        'temporal_valid_until' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(WorldRelationship::class, 'subject_entity_id');
    }

    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(WorldRelationship::class, 'object_entity_id');
    }
}

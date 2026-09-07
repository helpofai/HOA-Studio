<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - World Relationship Model
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldRelationship extends Model
{
    use HasFactory;

    protected $table = 'world_relationships';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'confidence' => 'float',
        'strength' => 'float',
    ];

    public function subjectEntity(): BelongsTo
    {
        return $this->belongsTo(WorldEntity::class, 'subject_entity_id');
    }

    public function objectEntity(): BelongsTo
    {
        return $this->belongsTo(WorldEntity::class, 'object_entity_id');
    }
}

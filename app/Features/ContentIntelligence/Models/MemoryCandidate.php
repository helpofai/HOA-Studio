<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Memory Candidate Model
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

class MemoryCandidate extends Model
{
    use HasFactory;

    protected $table = 'memory_candidates';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'provenance' => 'array',
        'admission_notes' => 'array',
        'confidence' => 'float',
        'importance_score' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ContentMission::class, 'mission_id');
    }

    public function admittedMemory(): BelongsTo
    {
        return $this->belongsTo(BrainMemory::class, 'admitted_memory_id');
    }
}

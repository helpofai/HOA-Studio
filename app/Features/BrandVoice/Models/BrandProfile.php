<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\BrandVoice\Models;

use App\Features\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandProfile extends Model
{
    use HasFactory;

    protected $table = 'brand_profiles';

    protected $fillable = [
        'user_id',
        'project_id',
        'name',
        'tone_description',
        'target_audience',
        'guidelines',
        'forbidden_words',
        'sample_content',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'forbidden_words' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Compile brand voice guidelines into a structured AI system instruction snippet
     */
    public function toSystemPromptSnippet(): string
    {
        $snippet = "=== BRAND VOICE GUIDELINES: {$this->name} ===\n";
        $snippet .= "Tone & Style: {$this->tone_description}\n";

        if (!empty($this->target_audience)) {
            $snippet .= "Target Audience: {$this->target_audience}\n";
        }

        if (!empty($this->guidelines)) {
            $snippet .= "Rules & Guidelines: {$this->guidelines}\n";
        }

        if (!empty($this->forbidden_words) && is_array($this->forbidden_words)) {
            $words = implode(', ', $this->forbidden_words);
            $snippet .= "Forbidden Words (DO NOT USE): {$words}\n";
        }

        if (!empty($this->sample_content)) {
            $snippet .= "Reference Voice Sample: \"{$this->sample_content}\"\n";
        }

        $snippet .= "Strictly adhere to this brand voice across all generated copy.";

        return $snippet;
    }
}
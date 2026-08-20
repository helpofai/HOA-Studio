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

namespace App\Features\Templates\Models;

use App\Features\BrandVoice\Models\BrandProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    use HasFactory;

    protected $table = 'templates';

    protected $fillable = [
        'template_category_id',
        'user_id',
        'name',
        'slug',
        'description',
        'icon',
        'prompt_template',
        'system_instructions',
        'inputs_schema',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'inputs_schema' => 'array',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Compile template with variables and optional brand voice
     */
    public function renderPrompt(array $variables = []): string
    {
        $rendered = $this->prompt_template;

        foreach ($variables as $key => $val) {
            if (is_array($val)) {
                $val = implode(', ', $val);
            }
            $rendered = str_replace(["{{" . $key . "}}", "{{" . $key . " }}", "{{ " . $key . "}}", "{{ " . $key . " }}"], (string) $val, $rendered);
        }

        return $rendered;
    }

    /**
     * Compile system prompt including optional brand voice guidelines
     */
    public function compileSystemPrompt(?BrandProfile $brandVoice = null): string
    {
        $prompt = $this->system_instructions ?: "You are an expert AI copywriter and content strategist. Produce exceptionally engaging, structured, and high-impact content.";

        if ($brandVoice) {
            $prompt .= "\n\n" . $brandVoice->toSystemPromptSnippet();
        }

        return $prompt;
    }
}
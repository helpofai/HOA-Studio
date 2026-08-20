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

namespace App\Features\Usage\Services;

class TokenCostCalculator
{
    /**
     * Pricing table in USD per 1 Million tokens: [input_per_million, output_per_million]
     */
    protected array $pricing = [
        // OpenAI
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'o1' => ['input' => 15.00, 'output' => 60.00],
        'o3-mini' => ['input' => 1.10, 'output' => 4.40],

        // Anthropic
        'claude-3-7-sonnet' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-sonnet' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-haiku' => ['input' => 0.80, 'output' => 4.00],

        // DeepSeek
        'deepseek-chat' => ['input' => 0.14, 'output' => 0.28],
        'deepseek-reasoner' => ['input' => 0.55, 'output' => 2.19],

        // Google
        'gemini-2.0-flash' => ['input' => 0.10, 'output' => 0.40],
        'gemini-1.5-pro' => ['input' => 1.25, 'output' => 5.00],

        // Groq / Meta
        'llama-3.3-70b-versatile' => ['input' => 0.59, 'output' => 0.79],
        'llama-3.1-8b-instant' => ['input' => 0.05, 'output' => 0.08],

        // Embeddings
        'text-embedding-3-small' => ['input' => 0.02, 'output' => 0.00],
        'text-embedding-3-large' => ['input' => 0.13, 'output' => 0.00],

        // Free / Local / Default
        'auto:free' => ['input' => 0.00, 'output' => 0.00],
        'auto' => ['input' => 0.30, 'output' => 0.80],
        'default' => ['input' => 0.50, 'output' => 1.50],
    ];

    /**
     * Calculate cost in USD for a given completion
     *
     * @param string $modelSlug
     * @param int $inputTokens
     * @param int $outputTokens
     * @return float
     */
    public function calculateCost(string $modelSlug, int $inputTokens, int $outputTokens = 0): float
    {
        $rates = $this->resolveRates($modelSlug);

        $inputCost = ($inputTokens / 1_000_000) * $rates['input'];
        $outputCost = ($outputTokens / 1_000_000) * $rates['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Calculate baseline market cost (GPT-4o equivalent) vs actual cost to compute savings
     */
    public function calculateSavings(string $modelSlug, int $inputTokens, int $outputTokens = 0): float
    {
        $actualCost = $this->calculateCost($modelSlug, $inputTokens, $outputTokens);
        $baselineCost = $this->calculateCost('gpt-4o', $inputTokens, $outputTokens);

        return max(0.0, round($baselineCost - $actualCost, 6));
    }

    /**
     * Resolve pricing rates for model slug
     */
    public function resolveRates(string $modelSlug): array
    {
        $clean = mb_strtolower(trim($modelSlug));

        // Exact match
        if (isset($this->pricing[$clean])) {
            return $this->pricing[$clean];
        }

        // Partial match
        foreach ($this->pricing as $key => $rates) {
            if ($key !== 'default' && str_contains($clean, $key)) {
                return $rates;
            }
        }

        return $this->pricing['default'];
    }

    /**
     * Register or override pricing for a custom model / provider
     */
    public function registerModelRate(string $modelSlug, float $inputPerMillion, float $outputPerMillion): void
    {
        $this->pricing[mb_strtolower(trim($modelSlug))] = [
            'input' => $inputPerMillion,
            'output' => $outputPerMillion,
        ];
    }
}
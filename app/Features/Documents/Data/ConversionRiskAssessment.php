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

namespace App\Features\Documents\Data;

/**
 * Conversion Risk Assessment
 * 
 * Provides detailed analysis of potential data loss when converting
 * a Canonical Document AST to a specific editor format.
 * 
 * Used by the UI to warn users before switching editors.
 */
class ConversionRiskAssessment
{
    public const RISK_NONE = 'none';       // No data loss expected
    public const RISK_LOW = 'low';         // Minor formatting loss (e.g., custom colors)
    public const RISK_MEDIUM = 'medium';   // Structural loss (e.g., tables to paragraphs)
    public const RISK_HIGH = 'high';       // Major content loss (e.g., images, embeds)
    public const RISK_CRITICAL = 'critical'; // Content cannot be represented at all

    protected string $riskLevel = self::RISK_NONE;
    protected array $warnings = [];
    protected array $unsupportedNodes = [];
    protected array $unsupportedMarks = [];
    protected array $transformations = [];
    protected int $affectedNodeCount = 0;
    protected bool $requiresConfirmation = false;

    public function __construct(
        string $riskLevel = self::RISK_NONE,
        array $warnings = [],
        array $unsupportedNodes = [],
        array $unsupportedMarks = [],
        array $transformations = [],
        int $affectedNodeCount = 0
    ) {
        $this->riskLevel = $riskLevel;
        $this->warnings = $warnings;
        $this->unsupportedNodes = $unsupportedNodes;
        $this->unsupportedMarks = $unsupportedMarks;
        $this->transformations = $transformations;
        $this->affectedNodeCount = $affectedNodeCount;
        $this->requiresConfirmation = in_array($riskLevel, [
            self::RISK_HIGH, 
            self::RISK_CRITICAL
        ]);
    }

    public static function none(): self
    {
        return new self(self::RISK_NONE);
    }

    public static function low(array $warnings = [], array $transformations = []): self
    {
        return new self(self::RISK_LOW, $warnings, [], [], $transformations);
    }

    public static function medium(array $warnings = [], array $unsupportedNodes = [], array $transformations = []): self
    {
        return new self(self::RISK_MEDIUM, $warnings, $unsupportedNodes, [], $transformations, count($unsupportedNodes));
    }

    public static function high(array $warnings = [], array $unsupportedNodes = [], array $unsupportedMarks = [], array $transformations = []): self
    {
        $count = count($unsupportedNodes) + count($unsupportedMarks);
        return new self(self::RISK_HIGH, $warnings, $unsupportedNodes, $unsupportedMarks, $transformations, $count);
    }

    public static function critical(array $warnings = [], array $unsupportedNodes = []): self
    {
        return new self(self::RISK_CRITICAL, $warnings, $unsupportedNodes, [], [], count($unsupportedNodes));
    }

    // Getters
    public function getRiskLevel(): string { return $this->riskLevel; }
    public function getWarnings(): array { return $this->warnings; }
    public function getUnsupportedNodes(): array { return $this->unsupportedNodes; }
    public function getUnsupportedMarks(): array { return $this->unsupportedMarks; }
    public function getTransformations(): array { return $this->transformations; }
    public function getAffectedNodeCount(): int { return $this->affectedNodeCount; }
    public function requiresConfirmation(): bool { return $this->requiresConfirmation; }

    /**
     * Get user-friendly risk label.
     */
    public function getRiskLabel(): string
    {
        return match ($this->riskLevel) {
            self::RISK_NONE => 'No Risk',
            self::RISK_LOW => 'Low Risk',
            self::RISK_MEDIUM => 'Medium Risk',
            self::RISK_HIGH => 'High Risk',
            self::RISK_CRITICAL => 'Critical Risk',
            default => 'Unknown',
        };
    }

    /**
     * Get user-friendly description.
     */
    public function getDescription(): string
    {
        return match ($this->riskLevel) {
            self::RISK_NONE => 'All content will be preserved perfectly.',
            self::RISK_LOW => 'Minor formatting differences may occur (e.g., custom colors, font sizes).',
            self::RISK_MEDIUM => 'Some structural elements will be simplified (e.g., tables converted to text).',
            self::RISK_HIGH => 'Significant content will be lost or transformed (e.g., images, embeds, complex layouts).',
            self::RISK_CRITICAL => 'This editor cannot represent the current content. Major data loss will occur.',
            default => 'Risk level unknown.',
        };
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return [
            'risk_level' => $this->riskLevel,
            'risk_label' => $this->getRiskLabel(),
            'description' => $this->getDescription(),
            'warnings' => $this->warnings,
            'unsupported_nodes' => $this->unsupportedNodes,
            'unsupported_marks' => $this->unsupportedMarks,
            'transformations' => $this->transformations,
            'affected_node_count' => $this->affectedNodeCount,
            'requires_confirmation' => $this->requiresConfirmation,
        ];
    }
}
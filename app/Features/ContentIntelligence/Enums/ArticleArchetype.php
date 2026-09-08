<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Article Archetype Enum
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

namespace App\Features\ContentIntelligence\Enums;

enum ArticleArchetype: string
{
    case AUTO_DETECT = 'auto_detect';
    case COMPARATIVE_ROUNDUP = 'comparative_roundup';
    case TECHNICAL_TEARDOWN = 'technical_teardown';
    case STEP_BY_STEP_TUTORIAL = 'step_by_step_tutorial';
    case EXECUTIVE_STRATEGY = 'executive_strategy';
    case THOUGHT_LEADERSHIP = 'thought_leadership';

    public function label(): string
    {
        return match ($this) {
            self::AUTO_DETECT => 'Smart Auto-Detect (AI Inferred)',
            self::COMPARATIVE_ROUNDUP => 'Comparative Roundup & Top Alternatives',
            self::TECHNICAL_TEARDOWN => 'Technical Architecture Teardown',
            self::STEP_BY_STEP_TUTORIAL => 'Step-by-Step Implementation Guide',
            self::EXECUTIVE_STRATEGY => 'Executive Strategy & Playbook',
            self::THOUGHT_LEADERSHIP => 'Thought Leadership & Trend Analysis',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::AUTO_DETECT => 'Auto-Detect',
            self::COMPARATIVE_ROUNDUP => 'Roundup',
            self::TECHNICAL_TEARDOWN => 'Teardown',
            self::STEP_BY_STEP_TUTORIAL => 'Tutorial',
            self::EXECUTIVE_STRATEGY => 'Strategy',
            self::THOUGHT_LEADERSHIP => 'Opinion',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AUTO_DETECT => 'AI analyzes your topic and thesis to automatically assemble the optimal structural outline and sections.',
            self::COMPARATIVE_ROUNDUP => 'Structured multi-product or tool review with feature matrices, spec comparisons, and buyer decision trees.',
            self::TECHNICAL_TEARDOWN => 'Deep engineering breakdown covering architecture, core mechanics, configuration, benchmarks, and production edge-cases.',
            self::STEP_BY_STEP_TUTORIAL => 'Actionable developer guide with sequential steps, code snippets, environmental prerequisites, and verification steps.',
            self::EXECUTIVE_STRATEGY => 'High-level business framework detailing ROI impact, organizational KPIs, implementation roadmap, and risk management.',
            self::THOUGHT_LEADERSHIP => 'Persuasive narrative deconstructing industry assumptions, presenting original arguments, and predicting future trends.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AUTO_DETECT => 'sparkles',
            self::COMPARATIVE_ROUNDUP => 'scale',
            self::TECHNICAL_TEARDOWN => 'cpu-chip',
            self::STEP_BY_STEP_TUTORIAL => 'academic-cap',
            self::EXECUTIVE_STRATEGY => 'chart-bar-square',
            self::THOUGHT_LEADERSHIP => 'light-bulb',
        };
    }

    /**
     * Recommended word count target range for this archetype.
     *
     * @return array{min: int, max: int}
     */
    public function defaultWordRange(): array
    {
        return match ($this) {
            self::AUTO_DETECT => ['min' => 1800, 'max' => 3200],
            self::COMPARATIVE_ROUNDUP => ['min' => 2200, 'max' => 4000],
            self::TECHNICAL_TEARDOWN => ['min' => 2500, 'max' => 4500],
            self::STEP_BY_STEP_TUTORIAL => ['min' => 1800, 'max' => 3500],
            self::EXECUTIVE_STRATEGY => ['min' => 1600, 'max' => 3000],
            self::THOUGHT_LEADERSHIP => ['min' => 1400, 'max' => 2600],
        };
    }

    /**
     * Canonical section layout template for this archetype.
     *
     * @return array<string>
     */
    public function defaultSectionTemplates(string $topic): array
    {
        $clean = ucwords(trim($topic));

        return match ($this) {
            self::COMPARATIVE_ROUNDUP => [
                "Executive Verdict: Top Alternatives & Standout Options in {$clean}",
                "Detailed Breakdown: Core Mechanics, Features & Experience per Title",
                "Side-by-Side Comparison: Feature Matrix, Performance & System Requirements",
                "Buyer's Guide: How to Choose the Right Solution for Your Needs",
                "Final Verdict & Strategic Recommendations",
            ],
            self::TECHNICAL_TEARDOWN => [
                "System Architecture & Core Engine Mechanisms of {$clean}",
                "Deep Component Breakdown & Internal Data Flow",
                "Production Configuration, Code Examples & High-Throughput Setup",
                "Performance Benchmarks, Scaling Limits & Latency Profiles",
                "Common Pitfalls, Edge Cases & Failure Mode Mitigation",
                "Production Readiness Checklist & Observability Blueprint",
            ],
            self::STEP_BY_STEP_TUTORIAL => [
                "Prerequisites, Environment Setup & Dependency Matrix for {$clean}",
                "Step 1: Core Foundation & Initial Architecture Setup",
                "Step 2: Implementing Primary Logic, Workflows & Handlers",
                "Step 3: Integrating Security, Error Handling & Persistence",
                "Step 4: Testing, Verification & End-to-End Validation",
                "Troubleshooting Common Issues & Production Deployment Guide",
            ],
            self::EXECUTIVE_STRATEGY => [
                "Executive Summary: Market Dynamics & Strategic Impact of {$clean}",
                "Core Framework: Strategic Pillars & ROI Quantification Model",
                "Implementation Playbook: Phased Rollout & Resource Allocation",
                "Risk Management, Compliance & Governance Controls",
                "Measuring Success: KPI Dashboard & Long-Term Growth Metrics",
            ],
            self::THOUGHT_LEADERSHIP => [
                "The Paradigm Shift: Why Traditional Approaches to {$clean} Are Obsolete",
                "Deconstructing Common Misconceptions & Industry Consensus",
                "The First-Principles Counter-Thesis & Architectural Evidence",
                "Strategic Implications for Leaders & Next-Gen Practitioners",
                "The Horizon Ahead: Predictions & Actionable Playbook for 2026+",
            ],
            self::AUTO_DETECT => [
                "Comprehensive Overview & Core Landscape of {$clean}",
                "Technical Mechanics, Capabilities & Key Features",
                "Practical Implementation & Real-World Use Cases",
                "Performance Optimization, Scaling & Best Practices",
                "Strategic Roadmap & Final Verdict",
            ],
        };
    }
}

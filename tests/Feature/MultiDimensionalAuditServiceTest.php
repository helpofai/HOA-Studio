<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - MultiDimensionalAuditService Test
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

namespace Tests\Feature;

use App\Features\AI\Data\ContentState;
use App\Features\AI\Services\MultiDimensionalAuditService;
use Tests\TestCase;

class MultiDimensionalAuditServiceTest extends TestCase
{
    public function test_audit_returns_quality_gate_score_and_dimensions(): void
    {
        $service = app(MultiDimensionalAuditService::class);

        $html = '<h1>Guide to Laravel 13</h1>
        <div class="quick-answer">💡 Quick summary: Laravel 13 is a powerful, high-performance PHP framework for building modern web applications.</div>
        <p>Laravel 13 introduces enhanced Livewire 3 reactivity, zero-latency AST processing, and advanced architectural patterns. Developers can rapidly craft scalable web applications, REST APIs, and background job queues with minimal friction. This comprehensive guide explores all core principles and technical details required to master the platform.</p>
        <h2>Key Architectural Features</h2>
        <p>Developers can build scalable AI content engines with clean state management. The framework provides robust database migration guards, elegant dependency injection, and native WebSockets support out of the box. By leveraging modern PHP 8.3 features, memory efficiency is optimized across both web requests and background CLI commands.</p>
        <h2>Frequently Asked Questions</h2>
        <h3>Is PHP 8.3 required?</h3>
        <p>Yes, PHP 8.3 or higher is strictly required for running Laravel 13 applications in production environments.</p>';

        $state = new ContentState('Laravel 13');
        $state->knowledgeGraph->addSource('src_1', 'Laravel Docs', 0.98, 'Tier 1');
        $state->knowledgeGraph->addEvidence('ev_1', 'src_1', 'Laravel 13 requires PHP 8.3', 0.95);
        $state->knowledgeGraph->addApprovedClaim('claim_1', 'ev_1', 'PHP 8.3 is required');

        $result = $service->audit(
            htmlContent: $html,
            title: 'Guide to Laravel 13',
            targetKeyword: 'Laravel 13',
            state: $state
        );

        $this->assertArrayHasKey('overall_score', $result);
        $this->assertArrayHasKey('grade', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('dimensions', $result);
        $this->assertEquals('PASS', $result['status']);
        $this->assertGreaterThanOrEqual(70, $result['overall_score']);
        $this->assertCount(5, $result['dimensions']);
    }
}

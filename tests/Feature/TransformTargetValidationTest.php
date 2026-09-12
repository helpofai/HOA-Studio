<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - TransformTargetValidationTest
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

use App\Features\AI\Services\ContentWriterBrain;
use Tests\TestCase;

class TransformTargetValidationTest extends TestCase
{
    protected ContentWriterBrain $brain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->brain = app(ContentWriterBrain::class);
    }

    public function test_expand_action_increases_word_count_substantially(): void
    {
        $input = "OpenAI designs multimodal AI foundation models that process text, image, audio, code, and video inputs.";
        $context = ['document_title' => 'OpenAI Research', 'target_keyword' => 'Multimodal AI'];

        $result = $this->brain->executeLocalActionTransform('expand', $input, $context);

        $this->assertGreaterThan(mb_strlen($input) * 1.3, mb_strlen($result));
        $this->assertStringContainsString($input, $result);
    }

    public function test_shorten_action_condenses_text(): void
    {
        $input = "In order to ensure that the system operates efficiently, it is important to note that developers must eliminate filler words due to the fact that they slow down reading speed.";
        $context = [];

        $result = $this->brain->executeLocalActionTransform('shorten', $input, $context);

        $this->assertLessThan(mb_strlen($input), mb_strlen($result));
        $this->assertStringNotContainsString('in order to', mb_strtolower($result));
    }

    public function test_simplify_action_replaces_polysyllabic_jargon(): void
    {
        $input = "The organization utilizes advanced frameworks to facilitate optimal outcomes and substantiate findings.";
        $context = [];

        $result = $this->brain->executeLocalActionTransform('simplify', $input, $context);

        $this->assertStringContainsString('use', mb_strtolower($result));
        $this->assertStringContainsString('help', mb_strtolower($result));
        $this->assertStringContainsString('best', mb_strtolower($result));
        $this->assertStringNotContainsString('utilizes', mb_strtolower($result));
    }

    public function test_generate_faq_action_produces_question_headings(): void
    {
        $input = "OmniRoute provides unified multi-model routing with strict circuit breaker fallback protection.";
        $context = ['document_title' => 'OmniRoute Gateway', 'target_keyword' => 'OmniRoute'];

        $result = $this->brain->executeLocalActionTransform('generate_faq', $input, $context);

        $this->assertStringContainsString('###', $result);
        $this->assertStringContainsString('?', $result);
    }

    public function test_key_takeaways_action_produces_bulleted_list(): void
    {
        $input = "First sentence explains foundational setup. Second sentence details execution strategy. Third sentence covers metrics.";
        $context = [];

        $result = $this->brain->executeLocalActionTransform('key_takeaways', $input, $context);

        $this->assertStringContainsString('-', $result);
        $this->assertStringContainsString('**', $result);
    }

    public function test_seo_optimize_action_weaves_focus_keyword(): void
    {
        $input = "This architecture ensures sub-millisecond AST processing.";
        $context = ['target_keyword' => 'Laravel 13 Architecture'];

        $result = $this->brain->executeLocalActionTransform('seo_optimize', $input, $context);

        $this->assertStringContainsString('Laravel 13 Architecture', $result);
    }
}

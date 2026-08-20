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

namespace Tests\Feature;

use App\Features\Documents\Services\EditorManager;
use Tests\TestCase;

class EditorManagerTest extends TestCase
{
    public function test_editor_manager_can_resolve_tiptap_adapter(): void
    {
        $manager = app(EditorManager::class);
        $adapter = $manager->adapter('tiptap');

        $content = '<h1>Hello World</h1><p>Test paragraph</p>';
        $canonical = $adapter->toCanonical($content);

        $this->assertArrayHasKey('content_html', $canonical);
        $this->assertArrayHasKey('content_plain', $canonical);
        $this->assertStringContainsString('Hello World', $canonical['content_plain']);
        $this->assertEquals($content, $adapter->fromCanonical($canonical));
    }

    public function test_editor_manager_can_resolve_markdown_adapter(): void
    {
        $manager = app(EditorManager::class);
        $adapter = $manager->adapter('markdown');

        $markdown = "# Welcome\n\nThis is a *markdown* page.";
        $canonical = $adapter->toCanonical($markdown);

        $this->assertArrayHasKey('content_html', $canonical);
        $this->assertStringContainsString('Welcome', $canonical['content_html']);
        $this->assertEquals($markdown, $adapter->fromCanonical($canonical));
    }

    public function test_editor_manager_can_resolve_plaintext_adapter(): void
    {
        $manager = app(EditorManager::class);
        $adapter = $manager->adapter('plaintext');

        $text = "Hello Plain World";
        $canonical = $adapter->toCanonical($text);

        $this->assertArrayHasKey('content_plain', $canonical);
        $this->assertEquals($text, $canonical['content_plain']);
        $this->assertEquals($text, $adapter->fromCanonical($canonical));
    }

    public function test_editor_manager_can_resolve_gutenberg_and_notion_adapters(): void
    {
        $manager = app(EditorManager::class);
        
        $gutenberg = $manager->adapter('gutenberg');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\GutenbergAdapter::class, $gutenberg);

        $notion = $manager->adapter('block_editor');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\NotionAdapter::class, $notion);

        $html = $manager->adapter('html');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\HtmlAdapter::class, $html);
    }
}

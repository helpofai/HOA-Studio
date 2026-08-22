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
use App\Features\Documents\Data\CanonicalDocumentSchema;
use Tests\TestCase;

class EditorManagerTest extends TestCase
{
    public function test_editor_manager_can_resolve_tiptap_adapter(): void
    {
        $manager = app(EditorManager::class);
        $adapter = $manager->adapter('tiptap');

        // Pass valid Tiptap JSON (ProseMirror format)
        $tiptapJson = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => ['level' => 1],
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello World']
                    ]
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Test paragraph']
                    ]
                ]
            ]
        ];
        $canonical = $adapter->toCanonical($tiptapJson);

        // Assert against Canonical AST structure
        $this->assertEquals('doc', $canonical['type']);
        $this->assertArrayHasKey('content', $canonical);
        $this->assertStringContainsString('Hello World', $canonical['content'][0]['content'][0]['text']);
    }

    public function test_editor_manager_can_resolve_markdown_adapter(): void
    {
        $manager = app(EditorManager::class);
        $adapter = $manager->adapter('markdown');

        $markdown = "# Welcome\n\nThis is a *markdown* page.";
        $canonical = $adapter->toCanonical($markdown);

        // Assert against Canonical AST structure
        $this->assertEquals('doc', $canonical['type']);
        $this->assertArrayHasKey('content', $canonical);
        
        // Find text node recursively since structure may vary
        $textNode = $this->findTextNode($canonical['content']);
        $this->assertNotNull($textNode, 'Could not find text node in AST: ' . json_encode($canonical));
        $this->assertStringContainsString('Welcome', $textNode);
    }

    public function test_editor_manager_can_resolve_plaintext_adapter(): void
    {
        $manager = app(EditorManager::class);
        $adapter = $manager->adapter('plain_text');

        $text = "Hello Plain Text";
        $canonical = $adapter->toCanonical($text);

        // Assert against Canonical AST structure
        $this->assertEquals('doc', $canonical['type']);
        $this->assertEquals('paragraph', $canonical['content'][0]['type']);
        $this->assertEquals($text, $canonical['content'][0]['content'][0]['text']);
    }

    public function test_editor_manager_can_resolve_all_registered_adapters(): void
    {
        $manager = app(EditorManager::class);

        $this->assertNotNull($manager->adapter('tiptap'));
        $this->assertNotNull($manager->adapter('gutenberg'));
        $this->assertNotNull($manager->adapter('markdown'));
        $this->assertNotNull($manager->adapter('markdown_split'));
        $this->assertNotNull($manager->adapter('block_editor'));
        $this->assertNotNull($manager->adapter('html'));
        $this->assertNotNull($manager->adapter('plain_text'));

        $tiptap = $manager->adapter('tiptap');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\TiptapAdapter::class, $tiptap);

        $gutenberg = $manager->adapter('gutenberg');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\GutenbergAdapter::class, $gutenberg);

        $markdown = $manager->adapter('markdown');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\MarkdownAdapter::class, $markdown);

        $notion = $manager->adapter('block_editor');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\NotionAdapter::class, $notion);

        $html = $manager->adapter('html');
        $this->assertInstanceOf(\App\Features\Documents\Adapters\HtmlAdapter::class, $html);
    }

    public function test_adapter_interface_methods_exist(): void
    {
        $manager = app(EditorManager::class);

        $tiptap = $manager->adapter('tiptap');
        $this->assertTrue(method_exists($tiptap, 'extractPlainText'));
        $this->assertTrue(method_exists($tiptap, 'getEditorKey'));
        $this->assertTrue(method_exists($tiptap, 'getDisplayName'));
        $this->assertTrue(method_exists($tiptap, 'getSupportedNodeTypes'));
        $this->assertTrue(method_exists($tiptap, 'getSupportedMarkTypes'));
        $this->assertTrue(method_exists($tiptap, 'assessConversionRisk'));
        $this->assertTrue(method_exists($tiptap, 'sanitize'));
        $this->assertTrue(method_exists($tiptap, 'getSupportedSchemaVersion'));

        $markdown = $manager->adapter('markdown');
        $this->assertTrue(method_exists($markdown, 'extractPlainText'));
        $this->assertEquals('markdown', $markdown->getEditorKey());

        $plainText = $manager->adapter('plain_text');
        $this->assertEquals('plain_text', $plainText->getEditorKey());
    }

    public function test_canonical_schema_validation(): void
    {
        $validDoc = CanonicalDocumentSchema::createDocument([
            CanonicalDocumentSchema::createNode('paragraph', [], [
                CanonicalDocumentSchema::createTextNode('Test content')
            ])
        ]);

        $this->assertTrue(CanonicalDocumentSchema::validate($validDoc));

        // Invalid document (missing required fields)
        $invalidDoc = ['type' => 'doc'];
        $this->assertFalse(CanonicalDocumentSchema::validate($invalidDoc));
    }

    /**
     * Helper to recursively find text content in the AST
     */
    protected function findTextNode(array $nodes): ?string
    {
        foreach ($nodes as $node) {
            if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
                return $node['text'];
            }
            if (isset($node['content']) && is_array($node['content'])) {
                $found = $this->findTextNode($node['content']);
                if ($found) return $found;
            }
        }
        return null;
    }
}
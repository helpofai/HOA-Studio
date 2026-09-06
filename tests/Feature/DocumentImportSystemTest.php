<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Document Import System Test
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

use App\Features\Documents\Livewire\DocumentEditor;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use App\Features\Documents\Services\DocumentImporter;
use App\Features\Documents\Services\DocumentTextAnalyzer;
use App\Features\Documents\Services\UniversalDocumentExtractor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentImportSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'editor_user_' . uniqid() . '@example.com',
            'role' => 'user',
        ]);

        $this->document = Document::create([
            'user_id' => $this->user->id,
            'title' => 'Untitled Document',
            'slug' => 'untitled-document-' . uniqid(),
            'status' => 'draft',
            'word_count' => 0,
            'character_count' => 0,
            'reading_time_minutes' => 1,
        ]);

        DocumentContent::create([
            'document_id' => $this->document->id,
            'content_html' => '<p></p>',
            'content_plain' => '',
        ]);
    }

    public function test_extractor_parses_markdown_correctly()
    {
        $extractor = new UniversalDocumentExtractor();
        $md = "# Next-Gen AI Workflows\n\nArtificial intelligence revolutionizes content creation.\n\n- Streamlined publishing\n- Real-time optimization\n\n| Feature | Status |\n| --- | --- |\n| Neural | Active |";
        $file = UploadedFile::fake()->createWithContent('article.md', $md);

        $result = $extractor->extract($file, [
            'clean_whitespace' => true,
            'preserve_headings' => true,
            'smart_typography' => true,
        ]);

        $this->assertEquals('Next-Gen AI Workflows', $result['title']);
        $this->assertEquals('md', $result['format']);
        $this->assertStringContainsString('<h1>Next-Gen AI Workflows</h1>', $result['html']);
        $this->assertStringContainsString('<li>Streamlined publishing</li>', $result['html']);
        $this->assertStringContainsString('<table', $result['html']);
        $this->assertStringContainsString('>Neural</td>', $result['html']);
        $this->assertStringContainsString('Neural', $result['plain_text']);
    }

    public function test_extractor_parses_csv_into_html_table()
    {
        $extractor = new UniversalDocumentExtractor();
        $csv = "Name,Role,Efficiency\nClaude 3.7,Reasoning,99%\nDeepSeek V3,Coding,98%\nGPT-4o,Multimodal,97%";
        $file = UploadedFile::fake()->createWithContent('ai-matrix.csv', $csv);

        $result = $extractor->extract($file);

        $this->assertEquals('csv', $result['format']);
        $this->assertStringContainsString('<table', $result['html']);
        $this->assertStringContainsString('>Name</th>', $result['html']);
        $this->assertStringContainsString('>Role</th>', $result['html']);
        $this->assertStringContainsString('>Claude 3.7</td>', $result['html']);
        $this->assertStringContainsString('>Coding</td>', $result['html']);
    }

    public function test_extractor_parses_html_cleanly_and_sanitizes_xss()
    {
        $extractor = new UniversalDocumentExtractor();
        $html = "<!DOCTYPE html><html><head><title>Clean Architecture Guide</title></head><body><h1>Clean Architecture</h1><p>Maintain loose coupling.<script>alert('hack');</script></p></body></html>";
        $file = UploadedFile::fake()->createWithContent('guide.html', $html);

        $result = $extractor->extract($file);

        $this->assertEquals('Clean Architecture Guide', $result['title']);
        $this->assertEquals('html', $result['format']);
        $this->assertStringNotContainsString('<script>', $result['html']);
        $this->assertStringContainsString('<h1>Clean Architecture</h1>', $result['html']);
        $this->assertStringContainsString('Maintain loose coupling.', $result['plain_text']);
    }

    public function test_extractor_parses_plain_text_with_paragraph_detection()
    {
        $extractor = new UniversalDocumentExtractor();
        $text = "First paragraph discussing modern web engineering and reactive frameworks.\n\nSecond paragraph covering database optimization and indexing strategies.";
        $file = UploadedFile::fake()->createWithContent('notes.txt', $text);

        $result = $extractor->extract($file);

        $this->assertEquals('txt', $result['format']);
        $this->assertStringContainsString('<p>First paragraph', $result['html']);
        $this->assertStringContainsString('<p>Second paragraph', $result['html']);
    }

    public function test_extractor_parses_json_structure()
    {
        $extractor = new UniversalDocumentExtractor();
        $json = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'TipTap AST document content loaded cleanly.']
                    ]
                ]
            ]
        ]);
        $file = UploadedFile::fake()->createWithContent('state.json', $json);

        $result = $extractor->extract($file);

        $this->assertEquals('json', $result['format']);
        $this->assertStringContainsString('TipTap AST document content loaded cleanly.', $result['html']);
    }

    public function test_text_analyzer_computes_deep_metrics_readability_and_tone()
    {
        $analyzer = new DocumentTextAnalyzer();
        $sampleText = "Artificial intelligence and machine learning architectures require robust software engineering practices. Distributed systems handle massive data throughput with low latency and high availability. Neural networks optimize predictive accuracy through algorithmic gradient descent.";
        $sampleHtml = "<p>" . $sampleText . "</p>";

        $analysis = $analyzer->analyze($sampleText, $sampleHtml);

        $this->assertArrayHasKey('metrics', $analysis);
        $this->assertArrayHasKey('readability', $analysis);
        $this->assertArrayHasKey('tone', $analysis);
        $this->assertArrayHasKey('keywords', $analysis);
        $this->assertArrayHasKey('summary', $analysis);

        $metrics = $analysis['metrics'];
        $this->assertGreaterThan(20, $metrics['word_count']);
        $this->assertEquals(3, $metrics['sentence_count']);
        $this->assertEquals(1, $metrics['paragraph_count']);

        $readability = $analysis['readability'];
        $this->assertIsNumeric($readability['flesch_reading_ease']);
        $this->assertNotEmpty($readability['level']);

        $tone = $analysis['tone'];
        $this->assertNotEmpty($tone['primary_tone']);
        $this->assertGreaterThan(0, $tone['primary_confidence']);

        $this->assertNotEmpty($analysis['keywords']);
        $this->assertNotEmpty($analysis['summary']);
    }

    public function test_document_editor_modal_lifecycle_and_extraction()
    {
        $md = "# Autonomous Agents in 2026\n\nMulti-agent orchestration coordinates specialized tasks concurrently.\n\nKey benefits include resilience and autonomous error recovery.";
        $file = UploadedFile::fake()->createWithContent('agents.md', $md);

        $test = Livewire::actingAs($this->user)
            ->test(DocumentEditor::class, ['id' => $this->document->id])
            ->assertSet('showImportModal', false)
            ->call('openImportModal')
            ->assertSet('showImportModal', true)
            ->assertSet('extractedDocument', null)
            ->set('importFile', $file)
            ->assertSet('extractedDocument.format', 'md')
            ->assertSet('extractedDocument.title', 'Autonomous Agents in 2026')
            ->call('closeImportModal')
            ->assertSet('showImportModal', false)
            ->assertSet('extractedDocument', null);

        $this->assertNull($test->get('extractedDocument'));
    }

    public function test_document_editor_confirm_import_replace_mode()
    {
        $md = "# Autonomous Agents in 2026\n\nMulti-agent orchestration coordinates specialized tasks concurrently.";
        $file = UploadedFile::fake()->createWithContent('agents.md', $md);

        Livewire::actingAs($this->user)
            ->test(DocumentEditor::class, ['id' => $this->document->id])
            ->call('openImportModal')
            ->set('importFile', $file)
            ->set('importInsertMode', 'replace')
            ->call('confirmImport')
            ->assertDispatched('editor:insertImportedContent')
            ->assertSet('showImportModal', false)
            ->assertSet('title', 'Autonomous Agents in 2026');

        $this->assertDatabaseHas('documents', [
            'id' => $this->document->id,
            'title' => 'Autonomous Agents in 2026',
        ]);
    }

    public function test_document_editor_confirm_import_append_mode()
    {
        $txt = "Appended notes for subsequent chapters.\n\nContinuing analysis.";
        $file = UploadedFile::fake()->createWithContent('notes.txt', $txt);

        Livewire::actingAs($this->user)
            ->test(DocumentEditor::class, ['id' => $this->document->id])
            ->call('openImportModal')
            ->set('importFile', $file)
            ->set('importInsertMode', 'append')
            ->call('confirmImport')
            ->assertDispatched('editor:insertImportedContent')
            ->assertSet('showImportModal', false);
    }

    public function test_document_editor_confirm_import_new_doc_mode()
    {
        $csv = "Module,Version\nEditor,2.5\nAI Router,3.0";
        $file = UploadedFile::fake()->createWithContent('modules.csv', $csv);

        $test = Livewire::actingAs($this->user)
            ->test(DocumentEditor::class, ['id' => $this->document->id])
            ->call('openImportModal')
            ->set('importFile', $file)
            ->set('importInsertMode', 'new_doc')
            ->call('confirmImport');

        $newDoc = Document::where('user_id', $this->user->id)
            ->where('id', '!=', $this->document->id)
            ->first();

        $this->assertNotNull($newDoc);
        $test->assertRedirect(route('documents.editor', ['id' => $newDoc->id]));
    }

    public function test_document_editor_import_modal_renders_analysis_intelligence_tab_without_errors()
    {
        $md = "# Autonomous Agents in 2026\n\nMulti-agent orchestration coordinates specialized tasks concurrently. Distributed consensus protocols ensure state synchronization across autonomous workers.";
        $file = UploadedFile::fake()->createWithContent('intelligence.md', $md);

        Livewire::actingAs($this->user)
            ->test(DocumentEditor::class, ['id' => $this->document->id])
            ->call('openImportModal')
            ->set('importFile', $file)
            ->assertSet('extractedDocument.format', 'md')
            ->set('importActiveTab', 'analysis')
            ->assertSee('Executive Extractive Summary')
            ->assertSee('Top Key Entities')
            ->assertSee('Total Words');
    }
}
<?php

namespace Tests\Feature;

use App\Features\Documents\Actions\CreateDocumentShare;
use App\Features\Documents\Actions\RevokeDocumentShare;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use App\Features\Documents\Models\DocumentShare;
use App\Features\Documents\Services\DocumentExporter;
use App\Features\Documents\Services\DocumentImporter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentImportExportSharingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'author_' . uniqid() . '@example.com',
            'role' => 'user',
        ]);

        $this->document = Document::create([
            'user_id' => $this->user->id,
            'title' => 'Mastering AI SEO 2026',
            'slug' => 'mastering-ai-seo-2026',
            'status' => 'draft',
            'word_count' => 150,
            'character_count' => 900,
            'reading_time_minutes' => 1,
        ]);

        DocumentContent::create([
            'document_id' => $this->document->id,
            'content_html' => '<h1>Mastering AI SEO 2026</h1><p>Search engines now prioritize <strong>semantic authority</strong> and high relevance.</p><blockquote>Quality over quantity always wins.</blockquote><ul><li>Optimize for intent</li><li>Include structured citations</li></ul>',
            'content_plain' => 'Mastering AI SEO 2026. Search engines now prioritize semantic authority and high relevance.',
        ]);
    }

    public function test_document_can_be_exported_to_markdown()
    {
        $exporter = new DocumentExporter();
        $md = $exporter->exportMarkdown($this->document);

        $this->assertStringContainsString('# Mastering AI SEO 2026', $md);
        $this->assertStringContainsString('**semantic authority**', $md);
        $this->assertStringContainsString('> Quality over quantity always wins.', $md);
        $this->assertStringContainsString('- Optimize for intent', $md);
    }

    public function test_document_can_be_exported_to_html()
    {
        $exporter = new DocumentExporter();
        $html = $exporter->exportHtml($this->document);

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Mastering AI SEO 2026', $html);
        $this->assertStringContainsString('semantic authority', $html);
        $this->assertStringContainsString('HelpOfAi Studio', $html);
    }

    public function test_document_can_be_exported_to_plain_text()
    {
        $exporter = new DocumentExporter();
        $txt = $exporter->exportPlainText($this->document);

        $this->assertStringContainsString('Mastering AI SEO 2026', $txt);
        $this->assertStringContainsString('Search engines now prioritize semantic authority', $txt);
        $this->assertStringNotContainsString('<h1>', $txt);
        $this->assertStringNotContainsString('<strong>', $txt);
    }

    public function test_document_can_be_exported_to_docx_mime()
    {
        $exporter = new DocumentExporter();
        $docx = $exporter->exportDocx($this->document);

        $this->assertStringContainsString('urn:schemas-microsoft-com:office:word', $docx);
        $this->assertStringContainsString('Mastering AI SEO 2026', $docx);
    }

    public function test_document_can_be_exported_to_json_ast()
    {
        $exporter = new DocumentExporter();
        $json = $exporter->exportJson($this->document);

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertEquals('HelpOfAi Studio Document Engine', $data['generator']);
        $this->assertEquals('Mastering AI SEO 2026', $data['document']['title']);
        $this->assertArrayHasKey('html', $data);
        $this->assertArrayHasKey('plain_text', $data);
    }

    public function test_export_endpoint_returns_file_download_for_authorized_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.export', ['id' => $this->document->id, 'format' => 'md']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="mastering-ai-seo-2026.md"');
    }

    public function test_export_endpoint_returns_json_ast_download_for_authorized_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.export', ['id' => $this->document->id, 'format' => 'json']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="mastering-ai-seo-2026.json"');
        $this->assertJson($response->getContent());
    }

    public function test_export_endpoint_returns_docx_download_for_authorized_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.export', ['id' => $this->document->id, 'format' => 'docx']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-word; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="mastering-ai-seo-2026.doc"');
    }

    public function test_print_pdf_endpoint_returns_printable_html_for_authorized_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('documents.print-pdf', ['id' => $this->document->id]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('window.print()', false);
        $response->assertSee('Mastering AI SEO 2026');
    }

    public function test_document_can_be_imported_from_markdown_file()
    {
        $importer = new DocumentImporter();

        $markdownContent = "# Complete Guide to Content Architecture\n\n**Structured writing** is the foundation of high-converting copies.\n\n- Point 1\n- Point 2";
        $uploadedFile = UploadedFile::fake()->createWithContent('content-guide.md', $markdownContent);

        $importedDoc = $importer->importFile($this->user, $uploadedFile);

        $this->assertDatabaseHas('documents', [
            'id' => $importedDoc->id,
            'user_id' => $this->user->id,
            'title' => 'Content Guide',
        ]);

        $this->assertNotNull($importedDoc->content);
        $this->assertStringContainsString('<h1>Complete Guide to Content Architecture</h1>', $importedDoc->content->content_html);
        $this->assertStringContainsString('<strong>Structured writing</strong>', $importedDoc->content->content_html);
    }

    public function test_public_document_share_can_be_created_and_accessed()
    {
        $action = new CreateDocumentShare();
        $share = $action->execute($this->document, [
            'allow_copy' => true,
            'allow_download' => true,
        ]);

        $this->assertDatabaseHas('document_shares', [
            'document_id' => $this->document->id,
            'share_token' => $share->share_token,
            'is_active' => true,
        ]);

        $response = $this->get(route('public.share', ['token' => $share->share_token]));
        $response->assertStatus(200);
        $response->assertSee('Mastering AI SEO 2026');
        $response->assertSee('semantic authority');
    }

    public function test_password_protected_document_share_requires_valid_password()
    {
        $action = new CreateDocumentShare();
        $share = $action->execute($this->document, [
            'password' => 'secret123',
            'allow_copy' => true,
            'allow_download' => true,
        ]);

        // Access share via Livewire
        Livewire::test('App\Features\Documents\Livewire\PublicDocumentPage', ['token' => $share->share_token])
            ->assertSet('isUnlocked', false)
            ->set('passwordInput', 'wrong-pass')
            ->call('unlock')
            ->assertSet('isUnlocked', false)
            ->assertSee('Incorrect password')
            ->set('passwordInput', 'secret123')
            ->call('unlock')
            ->assertSet('isUnlocked', true);
    }

    public function test_revoking_document_share_deactivates_public_access()
    {
        $createAction = new CreateDocumentShare();
        $revokeAction = new RevokeDocumentShare();

        $share = $createAction->execute($this->document);
        $this->assertTrue($share->is_active);

        $revokeAction->execute($share);
        $this->assertFalse($share->fresh()->is_active);

        $response = $this->get(route('public.share', ['token' => $share->share_token]));
        $response->assertStatus(404);
    }

    public function test_public_share_allows_format_downloads_when_enabled()
    {
        $action = new CreateDocumentShare();
        $share = $action->execute($this->document, [
            'allow_download' => true,
        ]);

        $response = $this->get(route('public.export', ['token' => $share->share_token, 'format' => 'html']));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
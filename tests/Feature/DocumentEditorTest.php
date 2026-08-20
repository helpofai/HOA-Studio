<?php

namespace Tests\Feature;

use App\Features\Documents\Actions\CreateDocument;
use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Livewire\DocumentEditor;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_editor_renders_with_document_content(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument())->execute($user, [
            'title' => 'Deep Dive into AI Agents',
            'content_html' => '<p>Autonomous coding agents are revolutionizing software development.</p>',
        ]);

        $this->actingAs($user);

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->assertStatus(200)
            ->assertSet('title', 'Deep Dive into AI Agents')
            ->assertSet('editorType', 'tiptap')
            ->assertSee('Tiptap ProseMirror');
    }

    public function test_autosave_updates_content_and_metrics(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument())->execute($user, [
            'title' => 'Draft Article',
            'content_html' => '<p>Initial</p>',
        ]);

        $this->actingAs($user);

        $newHtml = '<h1>Updated Heading</h1><p>This is a much longer paragraph with ten words for accurate metric calculations in test.</p>';

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->call('autosave', $newHtml)
            ->assertSet('wordCount', 16);

        $doc->refresh();
        $this->assertEquals($newHtml, $doc->content->content_html);
        $this->assertEquals(16, $doc->word_count);
    }

    public function test_explicit_snapshot_creation(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument())->execute($user, [
            'title' => 'Snapshot Test',
            'content_html' => '<p>Content for snapshot</p>',
        ]);

        $this->actingAs($user);

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->call('saveExplicitSnapshot')
            ->assertSet('saveStatusText', 'New snapshot created');

        $this->assertEquals(2, $doc->versions()->count());
    }

    public function test_restore_version_dispatches_event_and_updates_active_content(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument())->execute($user, [
            'title' => 'Original Title v1',
            'content_html' => '<p>Original v1 content</p>',
        ]);

        (new SaveDocumentVersion())->execute($doc, $user, [
            'title' => 'Modified Title v2',
            'content_html' => '<p>Modified v2 content</p>',
        ]);

        $this->actingAs($user);

        $v1 = $doc->versions()->where('version_number', 1)->first();

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->call('restoreVersion', $v1->id)
            ->assertDispatched('editor:setContent')
            ->assertSet('title', 'Original Title v1');

        $this->assertEquals(3, $doc->versions()->count());
    }

    public function test_multi_editor_type_switching(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument())->execute($user, [
            'title' => 'Editor Switch Test',
        ]);

        $this->actingAs($user);

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->call('switchEditorType', 'gutenberg')
            ->assertSet('editorType', 'gutenberg')
            ->assertDispatched('editor:reload');

        $this->assertEquals('gutenberg', $doc->fresh()->editor_type);
    }
}
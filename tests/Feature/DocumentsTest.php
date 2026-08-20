<?php

namespace Tests\Feature;

use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Actions\RestoreDocumentVersion;
use App\Features\Documents\Livewire\DocumentsPage;
use App\Features\Documents\Models\Document;
use App\Features\Projects\Actions\CreateProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_filter_and_delete_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $projAction = new CreateProject();
        $project = $projAction->execute($user, ['name' => 'AI Guides']);

        // Create Document via Livewire modal
        Livewire::test(DocumentsPage::class)
            ->set('newTitle', 'Mastering Prompt Engineering')
            ->set('newProjectId', $project->id)
            ->call('createDocument')
            ->assertSee('Mastering Prompt Engineering');

        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'title' => 'Mastering Prompt Engineering',
            'project_id' => $project->id,
        ]);

        $doc = Document::where('user_id', $user->id)->first();
        $this->assertNotNull($doc->content);
        $this->assertEquals(1, $doc->versions()->count());

        // Delete Document
        Livewire::test(DocumentsPage::class)
            ->call('delete', $doc->id);

        $this->assertSoftDeleted('documents', [
            'id' => $doc->id,
        ]);
    }

    public function test_document_versioning_and_restore(): void
    {
        $user = User::factory()->create();
        $doc = (new \App\Features\Documents\Actions\CreateDocument())->execute($user, [
            'title' => 'Initial Title',
            'content_html' => '<p>Version 1 content</p>',
        ]);

        $saveVersion = new SaveDocumentVersion();
        $v2 = $saveVersion->execute($doc, $user, [
            'title' => 'Title v2',
            'content_html' => '<p>Version 2 expanded content with extra details.</p>',
            'operation_type' => 'ai_expand',
            'summary' => 'Expanded via AI',
        ]);

        $this->assertEquals(2, $doc->versions()->count());
        $this->assertEquals('Title v2', $doc->fresh()->title);

        // Restore v1
        $restoreAction = new RestoreDocumentVersion();
        $restoredDoc = $restoreAction->execute($doc, $doc->versions()->where('version_number', 1)->first(), $user);

        $this->assertEquals(3, $restoredDoc->versions()->count());
        $this->assertEquals('Initial Title', $restoredDoc->title);
    }
}
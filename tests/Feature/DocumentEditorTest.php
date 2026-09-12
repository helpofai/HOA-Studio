<?php

namespace Tests\Feature;

use App\Features\Blog\Models\BlogPost;
use App\Features\Documents\Actions\CreateDocument;
use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Livewire\DocumentEditor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_editor_renders_with_document_content(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
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
        $doc = (new CreateDocument)->execute($user, [
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
        $doc = (new CreateDocument)->execute($user, [
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
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Original Title v1',
            'content_html' => '<p>Original v1 content</p>',
        ]);

        (new SaveDocumentVersion)->execute($doc, $user, [
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
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Editor Switch Test',
        ]);

        $this->actingAs($user);

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->call('switchEditorType', 'gutenberg')
            ->assertSet('editorType', 'gutenberg')
            ->assertDispatched('editor:reload');

        $this->assertEquals('gutenberg', $doc->fresh()->editor_type);
    }

    public function test_long_title_is_safely_truncated_without_sql_exception(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Initial Title',
            'content_html' => '<p>Initial content</p>',
        ]);

        $this->actingAs($user);

        // Generate a 500-character title (longer than MySQL VARCHAR(255))
        $veryLongTitle = str_repeat('A very long comprehensive guide headline for testing purposes. ', 10);
        $this->assertGreaterThan(255, mb_strlen($veryLongTitle));

        // Test applyTitle
        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->call('applyTitle', $veryLongTitle);

        $doc->refresh();
        $this->assertLessThanOrEqual(255, mb_strlen($doc->title));
        $this->assertNotEmpty($doc->title);

        // Test autosave with long title
        $component = Livewire::test(DocumentEditor::class, ['id' => $doc->id]);
        $component->set('title', $veryLongTitle)
            ->call('autosave', '<p>Updated content</p>');

        $doc->refresh();
        $this->assertLessThanOrEqual(255, mb_strlen($doc->title));
    }

    public function test_featured_image_upload_with_multi_formats(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Featured Image Test',
            'content_html' => '<p>Article with image</p>',
        ]);

        $this->actingAs($user);

        // Test uploading PNG image
        $pngFile = UploadedFile::fake()->image('cover.png', 800, 600);
        $component = Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->set('featuredImageUpload', $pngFile);

        $component->assertHasNoErrors('featuredImageUpload');
        $this->assertNotEmpty($component->get('blogFeaturedImage'));
        $this->assertStringContainsString('featured-images', $component->get('blogFeaturedImage'));
        $this->assertTrue($component->get('hasUnsavedChanges'));

        // Test uploading WebP image
        $webpFile = UploadedFile::fake()->create('cover.webp', 300, 'image/webp');
        $component->set('featuredImageUpload', $webpFile);
        $component->assertHasNoErrors('featuredImageUpload');
        $this->assertStringContainsString('featured-images', $component->get('blogFeaturedImage'));
    }

    public function test_featured_image_upload_updates_existing_blog_post(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Blog Post Image Test',
            'content_html' => '<p>Content</p>',
        ]);

        $post = BlogPost::create([
            'user_id' => $user->id,
            'document_id' => $doc->id,
            'title' => 'Blog Post Image Test',
            'slug' => 'blog-post-image-test',
            'category' => 'Technology',
            'status' => 'draft',
            'content_html' => '<p>Content</p>',
            'featured_image' => null,
        ]);

        $this->actingAs($user);

        $image = UploadedFile::fake()->image('hero.jpg', 1200, 630);
        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->set('featuredImageUpload', $image)
            ->assertHasNoErrors('featuredImageUpload');

        $post->refresh();
        $this->assertNotNull($post->featured_image);
        $this->assertStringContainsString('featured-images', $post->featured_image);
    }

    public function test_featured_image_remove_clears_image_and_updates_blog_post(): void
    {
        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Blog Post Remove Image Test',
            'content_html' => '<p>Content</p>',
        ]);

        $post = BlogPost::create([
            'user_id' => $user->id,
            'document_id' => $doc->id,
            'title' => 'Blog Post Remove Image Test',
            'slug' => 'blog-post-remove-image-test',
            'category' => 'Technology',
            'status' => 'draft',
            'content_html' => '<p>Content</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1234',
        ]);

        $this->actingAs($user);

        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->assertSet('blogFeaturedImage', 'https://images.unsplash.com/photo-1234')
            ->call('removeFeaturedImage')
            ->assertSet('blogFeaturedImage', '');

        $post->refresh();
        $this->assertNull($post->featured_image);
    }

    public function test_featured_image_upload_validation_rejects_invalid_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'Invalid Image Test',
            'content_html' => '<p>Content</p>',
        ]);

        $this->actingAs($user);

        $invalidFile = UploadedFile::fake()->create('malicious.pdf', 500, 'application/pdf');
        Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->set('featuredImageUpload', $invalidFile)
            ->assertHasErrors(['featuredImageUpload']);
    }

    public function test_featured_image_upload_generates_seo_friendly_filename(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $doc = (new CreateDocument)->execute($user, [
            'title' => 'High Performance SEO Strategy',
            'content_html' => '<p>SEO content</p>',
        ]);

        $this->actingAs($user);

        $image = UploadedFile::fake()->image('IMG_9876.jpg', 800, 600);
        $component = Livewire::test(DocumentEditor::class, ['id' => $doc->id])
            ->set('featuredImageUpload', $image);

        $component->assertHasNoErrors('featuredImageUpload');
        $imageUrl = $component->get('blogFeaturedImage');
        $this->assertNotEmpty($imageUrl);
        // Verify SEO slug in filename
        $this->assertStringContainsString('high-performance-seo-strategy-featured-image-', $imageUrl);
        $this->assertStringEndsWith('.jpg', $imageUrl);
    }

    public function test_storage_fallback_route_serves_files(): void
    {
        Storage::disk('public')->put('featured-images/test-seo-sample.png', 'fake-image-bytes');

        $response = $this->get('/storage/featured-images/test-seo-sample.png');
        $response->assertStatus(200);

        // Cleanup
        Storage::disk('public')->delete('featured-images/test-seo-sample.png');
    }
}

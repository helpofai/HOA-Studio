<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blog Post System Test Suite
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

use App\Features\Blog\Livewire\BlogIndexPage;
use App\Features\Blog\Livewire\BlogManagerPage;
use App\Features\Blog\Livewire\BlogPostPage;
use App\Features\Blog\Models\BlogPost;
use App\Features\Documents\Livewire\DocumentEditor;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogPostSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create([
            'name' => 'Jane Author',
            'email' => 'author_' . uniqid() . '@helpofai.com',
            'role' => 'user',
            'plan' => 'pro',
            'monthly_word_quota' => 50000,
        ]);

        $this->document = Document::create([
            'user_id' => $this->author->id,
            'title' => 'Mastering AI Content Workflows in 2026',
            'slug' => 'mastering-ai-content-workflows-2026',
            'status' => 'draft',
            'editor_type' => 'tiptap',
            'word_count' => 600,
            'character_count' => 3600,
            'reading_time_minutes' => 3,
        ]);

        $this->document->content()->create([
            'content_html' => '<h2>Introduction</h2><p>Here is an in-depth guide on using AI agents to streamline writing workflows and enhance retention.</p>',
            'content_plain' => 'Introduction Here is an in-depth guide on using AI agents to streamline writing workflows and enhance retention.',
        ]);
    }

    public function test_guest_can_view_public_blog_index()
    {
        BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Published Guide to AI',
            'slug' => 'published-guide-to-ai',
            'excerpt' => 'A comprehensive guide to artificial intelligence.',
            'content_html' => '<p>Article body content here.</p>',
            'category' => 'Artificial Intelligence',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.index'));
        $response->assertStatus(200);
        $response->assertSee('The HelpOfAi Studio Journal');
        $response->assertSee('Published Guide to AI');
    }

    public function test_guest_can_view_published_blog_article()
    {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Building High Retention Articles',
            'slug' => 'building-high-retention-articles',
            'excerpt' => 'Learn how to optimize readability and engagement.',
            'content_html' => '<p>Detailed article content with rich takeaways.</p>',
            'category' => 'Content Strategy',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));
        $response->assertStatus(200);
        $response->assertSee('Building High Retention Articles');
        $response->assertSee('Detailed article content with rich takeaways');
        $response->assertSee('Jane Author');
    }

    public function test_guest_cannot_view_draft_blog_article()
    {
        $draftPost = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Unpublished Secret Article',
            'slug' => 'unpublished-secret-article',
            'content_html' => '<p>Private draft.</p>',
            'category' => 'General',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->get(route('blog.show', $draftPost->slug));
        $response->assertStatus(404);
    }

    public function test_author_can_preview_draft_blog_article()
    {
        $draftPost = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Author Private Draft Article',
            'slug' => 'author-private-draft-article',
            'content_html' => '<p>Private draft content.</p>',
            'category' => 'General',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->author)->get(route('blog.show', $draftPost->slug));
        $response->assertStatus(200);
        $response->assertSee('Author Private Draft Article');
        $response->assertSee('Private Draft Preview');
    }

    public function test_author_can_publish_article_from_editor()
    {
        $this->actingAs($this->author);

        Livewire::test(DocumentEditor::class, ['id' => $this->document->id])
            ->call('openBlogModal')
            ->assertSet('showBlogModal', true)
            ->set('blogTitle', 'Live Post From Editor')
            ->set('blogCategory', 'Writing & Creativity')
            ->set('blogTags', 'AI, Production, TipTap')
            ->call('publishToBlog')
            ->assertHasNoErrors()
            ->assertSet('isPublishedToBlog', true);

        $this->assertDatabaseHas('blog_posts', [
            'document_id' => $this->document->id,
            'user_id' => $this->author->id,
            'title' => 'Live Post From Editor',
            'category' => 'Writing & Creativity',
            'status' => 'published',
        ]);

        $this->document->refresh();
        $this->assertEquals('published', $this->document->status);
    }

    public function test_author_can_unpublish_article_from_editor()
    {
        $this->actingAs($this->author);

        // Publish first
        Livewire::test(DocumentEditor::class, ['id' => $this->document->id])
            ->set('blogTitle', 'Article to Unpublish')
            ->call('publishToBlog');

        $this->assertDatabaseHas('blog_posts', [
            'document_id' => $this->document->id,
            'status' => 'published',
        ]);

        // Unpublish
        Livewire::test(DocumentEditor::class, ['id' => $this->document->id])
            ->call('unpublishFromBlog')
            ->assertSet('isPublishedToBlog', false);

        $this->assertDatabaseHas('blog_posts', [
            'document_id' => $this->document->id,
            'status' => 'draft',
        ]);

        $this->document->refresh();
        $this->assertEquals('draft', $this->document->status);
    }

    public function test_author_can_manage_blog_posts_in_dashboard()
    {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Dashboard Manageable Article',
            'slug' => 'dashboard-manageable-article',
            'content_html' => '<p>Article content.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($this->author);

        $response = $this->get(route('dashboard.blog'));
        $response->assertStatus(200);
        $response->assertSee('Blog Articles Manager');
        $response->assertSee('Dashboard Manageable Article');

        Livewire::test(BlogManagerPage::class)
            ->call('togglePostStatus', $post->id);

        $post->refresh();
        $this->assertEquals('draft', $post->status);
    }

    public function test_author_can_manage_wordpress_style_post_options()
    {
        $this->actingAs($this->author);

        Livewire::test(DocumentEditor::class, ['id' => $this->document->id])
            // Test Category selection
            ->call('setBlogCategory', 'SEO & Optimization')
            ->assertSet('blogCategory', 'SEO & Optimization')
            // Test Tag adding and deduplication
            ->call('addBlogTag', 'AI Writing')
            ->call('addBlogTag', 'WordPress')
            ->call('addBlogTag', 'AI Writing') // duplicate should be ignored
            ->assertSet('blogTags', 'AI Writing, WordPress')
            // Test Tag removing
            ->call('removeBlogTag', 'AI Writing')
            ->assertSet('blogTags', 'WordPress')
            // Test Featured Image setting and removal
            ->set('blogFeaturedImage', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe')
            ->assertSet('blogFeaturedImage', 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe')
            ->call('removeFeaturedImage')
            ->assertSet('blogFeaturedImage', '')
            // Test Excerpt AI generation
            ->call('generateBlogExcerpt')
            ->assertSet('blogExcerpt', fn($excerpt) => !empty($excerpt));
    }
}


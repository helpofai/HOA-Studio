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
use App\Features\Blog\Models\BlogPost;
use App\Features\Documents\Livewire\DocumentEditor;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            'email' => 'author_'.uniqid().'@helpofai.com',
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

    public function test_guest_sees_previous_and_next_navigation_in_publisher_layout()
    {
        $firstPost = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'First Pioneer Post',
            'slug' => 'first-pioneer-post',
            'content_html' => '<h2>Part 1</h2><p>First article body.</p>',
            'category' => 'Technology',
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        $secondPost = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Second Progressive Post',
            'slug' => 'second-progressive-post',
            'content_html' => '<h2>Part 2</h2><p>Second article body.</p>',
            'category' => 'Technology',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $thirdPost = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Third Future Post',
            'slug' => 'third-future-post',
            'content_html' => '<h2>Part 3</h2><p>Third article body.</p>',
            'category' => 'Technology',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $secondPost->slug));
        $response->assertStatus(200);
        $response->assertSee('Second Progressive Post');
        $response->assertSee('First Pioneer Post');
        $response->assertSee('Third Future Post');
        $response->assertSee('Previous Article');
        $response->assertSee('Next Article');
        $response->assertSee('Similar');
        $response->assertSee('By Author');
        $response->assertSee('Trending');
    }

    public function test_guest_can_view_article_with_mermaid_ascii_and_matrix_tables()
    {
        $complexPost = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Technical Architecture Blueprint',
            'slug' => 'technical-architecture-blueprint',
            'content_html' => '
                <h2>System Schemas</h2>
                <pre class="language-mermaid"><code>erDiagram USERS ||--o{ DOCUMENTS : creates</code></pre>
                <h2>Hybrid Routing Architecture</h2>
                <pre><code>┌────────┐\n│ CLIENT │\n└────────┘</code></pre>
                <h2>Permissions Matrix</h2>
                <table>
                    <thead><tr><th>Feature</th><th>Pro</th></tr></thead>
                    <tbody><tr><td>RAG Sources</td><td>✓</td></tr></tbody>
                </table>
            ',
            'category' => 'Architecture',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $complexPost->slug));
        $response->assertStatus(200);
        $response->assertSee('Technical Architecture Blueprint');
        $response->assertSee('language-mermaid');
        $response->assertSee('erDiagram');
        $response->assertSee('CLIENT');
        $response->assertSee('Permissions Matrix');
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
            ->assertSet('blogExcerpt', fn ($excerpt) => ! empty($excerpt));
    }

    public function test_blog_post_shows_published_and_updated_dates_when_updated_after_publication()
    {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'How to Master DeepSeek AI in 2026: Step-by-Step Technical Guide',
            'slug' => 'how-to-master-deepseek-ai-in-2026-step-by-step-technical-guide',
            'excerpt' => 'A complete technical guide to DeepSeek AI.',
            'content_html' => '<p>DeepSeek AI architecture and mastery guide.</p>',
            'category' => 'Artificial Intelligence',
            'status' => 'published',
            'published_at' => '2026-09-05 16:17:08',
        ]);

        DB::table('blog_posts')
            ->where('id', $post->id)
            ->update([
                'published_at' => '2026-09-05 16:17:08',
                'updated_at' => '2026-09-09 10:00:00',
            ]);

        $response = $this->get(route('blog.show', $post->slug));
        $response->assertStatus(200);
        $response->assertSee('Published on September 05, 2026');
        $response->assertSee('Updated on September 09, 2026');

        // Verify that view increment does not touch updated_at
        $post->refresh();
        $this->assertEquals('2026-09-09 10:00:00', $post->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_dynamic_blog_archive_route_and_view_switching()
    {
        BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Article One for Archive',
            'slug' => 'article-one-for-archive',
            'excerpt' => 'First test post for dynamic archive view.',
            'content_html' => '<p>Content for post one.</p>',
            'category' => 'Engineering',
            'tags' => ['Laravel', 'AI'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.archive'));
        $response->assertStatus(200);
        $response->assertSee('The HelpOfAi Studio Journal');
        $response->assertSee('Tag Cloud');
        $response->assertSee('Categories Directory');
        $response->assertSee('Archive Timeline');

        // Test Livewire view toggle
        Livewire::test(BlogIndexPage::class)
            ->assertSet('view', 'grid')
            ->call('setView', 'list')
            ->assertSet('view', 'list')
            ->call('setView', 'grid')
            ->assertSet('view', 'grid');
    }

    public function test_dynamic_blog_archive_tag_and_category_filtering()
    {
        $postAi = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Deep Dive into LLM Architecture',
            'slug' => 'deep-dive-into-llm-architecture',
            'excerpt' => 'Exploring neural attention mechanisms.',
            'content_html' => '<p>Deep attention layers.</p>',
            'category' => 'Artificial Intelligence',
            'tags' => ['DeepSeek', 'Neural Networks'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $postSeo = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Advanced SERP Intelligence Tactics',
            'slug' => 'advanced-serp-intelligence-tactics',
            'excerpt' => 'Optimizing search visibility.',
            'content_html' => '<p>SERP snippets.</p>',
            'category' => 'SEO Strategy',
            'tags' => ['Rankings', 'Keywords'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Filter by Tag
        Livewire::test(BlogIndexPage::class)
            ->call('filterTag', 'DeepSeek')
            ->assertSet('tag', 'DeepSeek')
            ->assertSee('Deep Dive into LLM Architecture')
            ->assertDontSee('Advanced SERP Intelligence Tactics')
            // Toggle tag off
            ->call('filterTag', 'DeepSeek')
            ->assertSet('tag', 'all')
            // Filter by Category
            ->call('filterCategory', 'SEO Strategy')
            ->assertSet('category', 'SEO Strategy')
            ->assertSee('Advanced SERP Intelligence Tactics')
            ->assertDontSee('Deep Dive into LLM Architecture');
    }

    public function test_dynamic_blog_archive_search_and_clear_filters()
    {
        BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Mastering TipTap 3.30 Realtime Extensions',
            'slug' => 'mastering-tiptap-3-30-realtime-extensions',
            'excerpt' => 'Rich text editing capabilities.',
            'content_html' => '<p>TipTap editor setup.</p>',
            'category' => 'Editor Workflows',
            'tags' => ['TipTap', 'Vue'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        Livewire::test(BlogIndexPage::class)
            ->set('search', 'TipTap')
            ->assertSee('Mastering TipTap 3.30 Realtime Extensions')
            ->set('sort', 'popular')
            ->set('readTime', 'quick')
            ->assertSet('sort', 'popular')
            ->assertSet('readTime', 'quick')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('category', 'all')
            ->assertSet('tag', 'all')
            ->assertSet('sort', 'latest')
            ->assertSet('readTime', 'all')
            ->assertSet('archive', 'all');
    }

    public function test_blog_cards_render_reading_progress_tracker_and_upgraded_action_buttons()
    {
        BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'The Next Era of Semantic Search in 2026',
            'slug' => 'the-next-era-of-semantic-search-2026',
            'excerpt' => 'Exploring neural vector databases.',
            'content_html' => '<p>Semantic search vectors.</p>',
            'category' => 'Artificial Intelligence',
            'tags' => ['Search', 'Vector'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.index'));
        $response->assertStatus(200);
        $response->assertSee('hoaCardReadingProgress');
        $response->assertSee('the-next-era-of-semantic-search-2026');
        $response->assertSee('Read Again');
        $response->assertSee('Resume');
        $response->assertSee('100% Read');
    }

    public function test_blog_post_page_renders_resume_reading_toast_and_scroll_tracker()
    {
        $post = BlogPost::create([
            'user_id' => $this->author->id,
            'document_id' => $this->document->id,
            'title' => 'Deep Dive into LLM Architecture 2026',
            'slug' => 'deep-dive-into-llm-architecture-2026',
            'excerpt' => 'Architectural breakdown of transformers.',
            'content_html' => '<p>Transformer layers and self-attention.</p>',
            'category' => 'Deep Learning',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post->slug));
        $response->assertStatus(200);
        $response->assertSee("hoaBlogPostReader('{$post->slug}')", false);
        $response->assertSee('Pick up where you left off');
        $response->assertSee('Jump &rarr;', false);
    }
}

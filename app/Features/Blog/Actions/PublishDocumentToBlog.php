<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Publish Document To Blog Action
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

namespace App\Features\Blog\Actions;

use App\Features\Blog\Models\BlogPost;
use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Support\Str;

class PublishDocumentToBlog
{
    /**
     * Publish or update a document as a public blog post.
     */
    public function execute(Document $document, User $user, array $attributes = []): BlogPost
    {
        $existingPost = BlogPost::where('document_id', $document->id)->first();

        $title = ! empty($attributes['title']) ? trim($attributes['title']) : ($document->title ?: 'Untitled Article');

        $slug = ! empty($attributes['slug'])
            ? Str::slug($attributes['slug'])
            : ($existingPost ? $existingPost->slug : BlogPost::generateUniqueSlug($title, $existingPost?->id));

        // Ensure unique slug
        if ($existingPost && $existingPost->slug !== $slug) {
            $slug = BlogPost::generateUniqueSlug($slug, $existingPost->id);
        } elseif (! $existingPost) {
            $slug = BlogPost::generateUniqueSlug($slug);
        }

        $contentHtml = $attributes['content_html'] ?? $document->content?->content_html ?? '';
        $contentMarkdown = $attributes['content_markdown'] ?? $document->content?->content_markdown ?? null;

        // Auto-generate excerpt if not supplied
        $excerpt = ! empty($attributes['excerpt']) ? trim($attributes['excerpt']) : null;
        if (empty($excerpt) && ! empty($contentHtml)) {
            $plain = trim(strip_tags($contentHtml));
            $excerpt = Str::limit(preg_replace('/\s+/', ' ', $plain), 220);
        }

        $wordCount = $document->word_count ?: str_word_count(strip_tags($contentHtml));
        $readingTime = max(1, (int) ceil($wordCount / 200));

        $status = $attributes['status'] ?? 'published';
        $isPublished = ($status === 'published');

        $tags = isset($attributes['tags'])
            ? (is_array($attributes['tags']) ? $attributes['tags'] : array_filter(array_map('trim', explode(',', $attributes['tags']))))
            : ($existingPost?->tags ?? []);

        $postData = [
            'user_id' => $user->id,
            'document_id' => $document->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content_html' => $contentHtml,
            'content_markdown' => $contentMarkdown,
            'featured_image' => $attributes['featured_image'] ?? $existingPost?->featured_image,
            'category' => $attributes['category'] ?? $existingPost?->category ?? 'General',
            'tags' => array_values($tags),
            'status' => $status,
            'is_featured' => (bool) ($attributes['is_featured'] ?? $existingPost?->is_featured ?? false),
            'reading_time_minutes' => $readingTime,
            'seo_title' => $attributes['seo_title'] ?? $existingPost?->seo_title ?? $title,
            'seo_description' => $attributes['seo_description'] ?? $existingPost?->seo_description ?? $excerpt,
            'published_at' => $isPublished ? ($existingPost?->published_at ?? now()) : null,
        ];

        if ($existingPost) {
            $existingPost->update($postData);
            $post = $existingPost->fresh();
        } else {
            $post = BlogPost::create($postData);
        }

        if ($isPublished) {
            $document->update(['status' => 'published']);
        }

        return $post;
    }
}

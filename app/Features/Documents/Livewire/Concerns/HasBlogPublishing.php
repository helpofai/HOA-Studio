<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Livewire Concern: Blog Publishing
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

namespace App\Features\Documents\Livewire\Concerns;

use App\Features\Blog\Actions\PublishDocumentToBlog;
use App\Features\Blog\Actions\UnpublishDocumentFromBlog;
use App\Features\Blog\Models\BlogPost;
use App\Features\Documents\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait HasBlogPublishing
{
    public function openBlogModal(): void
    {
        $this->loadBlogState();
        $this->showBlogModal = true;
    }

    public function loadBlogState(): void
    {
        $this->blogCategories = BlogPost::defaultCategories();

        $post = BlogPost::where('document_id', $this->documentId)->first();

        if ($post) {
            $this->isPublishedToBlog = ($post->status === 'published');
            $this->blogPostId = $post->id;
            $this->blogTitle = $post->title;
            $this->blogSlug = $post->slug;
            $this->blogCategory = $post->category ?: 'Artificial Intelligence';
            if (! in_array($this->blogCategory, $this->blogCategories)) {
                $this->blogCategories[] = $this->blogCategory;
            }
            $this->blogTags = implode(', ', $post->tags ?? []);
            $this->blogFeaturedImage = $post->featured_image ?? '';
            $this->blogExcerpt = $post->excerpt ?? '';
            $this->blogStatus = $post->status;
            $this->blogIsFeatured = (bool) $post->is_featured;
            $this->blogViewsCount = (int) $post->views_count;
            $this->blogPublishedUrl = route('blog.show', $post->slug);
        } else {
            $this->isPublishedToBlog = false;
            $this->blogPostId = null;
            $this->blogTitle = $this->title ?: 'Untitled Article';
            $this->blogSlug = BlogPost::generateUniqueSlug($this->title ?: 'article');
            $this->blogCategory = 'Artificial Intelligence';
            $this->blogTags = '';
            $this->blogFeaturedImage = '';
            $plain = trim(strip_tags($this->contentHtml));
            $this->blogExcerpt = Str::limit(preg_replace('/\s+/', ' ', $plain), 220);
            $this->blogStatus = 'published';
            $this->blogIsFeatured = false;
            $this->blogViewsCount = 0;
            $this->blogPublishedUrl = null;
        }
    }

    public function generateBlogExcerpt(): void
    {
        $plain = trim(strip_tags($this->contentHtml));
        $this->blogExcerpt = Str::limit(preg_replace('/\s+/', ' ', $plain), 240);
        session()->flash('blog_status', 'Excerpt auto-generated from current document content.');
    }

    public function publishToBlog(PublishDocumentToBlog $action): void
    {
        $user = Auth::user();
        $document = Document::with('content')->where('user_id', $user->id)->findOrFail($this->documentId);

        $this->validate([
            'blogTitle' => 'required|string|min:3|max:255',
            'blogCategory' => 'required|string|max:100',
            'blogSlug' => 'nullable|string|max:255',
        ]);

        $tags = array_filter(array_map('trim', explode(',', $this->blogTags)));

        $post = $action->execute($document, $user, [
            'title' => $this->blogTitle,
            'slug' => $this->blogSlug,
            'excerpt' => $this->blogExcerpt,
            'content_html' => $this->contentHtml,
            'featured_image' => $this->blogFeaturedImage ?: null,
            'category' => $this->blogCategory,
            'tags' => $tags,
            'status' => $this->blogStatus,
            'is_featured' => $this->blogIsFeatured,
        ]);

        $this->loadBlogState();
        session()->flash('blog_status', "Article successfully published! Live at: {$post->public_url}");
    }

    public function unpublishFromBlog(UnpublishDocumentFromBlog $action): void
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($this->documentId);

        $action->execute($document);
        $this->loadBlogState();
        session()->flash('blog_status', 'Article has been unpublished and moved to draft.');
    }

    public function updatedFeaturedImageUpload(): void
    {
        $this->validate([
            'featuredImageUpload' => [
                'required',
                'file',
                'mimes:png,jpg,jpeg,webp,gif,svg,avif,bmp,ico,tif,tiff',
                'max:15360',
            ],
        ], [
            'featuredImageUpload.required' => 'Please select an image file to upload.',
            'featuredImageUpload.file' => 'The uploaded file must be a valid image file.',
            'featuredImageUpload.mimes' => 'Supported image formats: PNG, JPG, JPEG, WebP, GIF, SVG, AVIF, BMP, ICO, TIFF.',
            'featuredImageUpload.max' => 'The image size cannot exceed 15MB.',
        ]);

        try {
            $seoFileName = $this->generateSeoFriendlyImageName();
            $path = $this->featuredImageUpload->storeAs('featured-images', $seoFileName, 'public');
            $this->blogFeaturedImage = asset('storage/'.$path);
            if ($this->blogPostId) {
                BlogPost::where('id', $this->blogPostId)->update(['featured_image' => $this->blogFeaturedImage]);
            }
            $this->hasUnsavedChanges = true;
            session()->flash('blog_status', 'Featured image uploaded successfully!');
        } catch (\Throwable $e) {
            $this->addError('featuredImageUpload', 'Failed to upload image: '.$e->getMessage());
        } finally {
            $this->featuredImageUpload = null;
        }
    }

    protected function generateSeoFriendlyImageName(): string
    {
        $slugBase = '';
        if (! empty($this->blogSlug)) {
            $slugBase = $this->blogSlug;
        } elseif (! empty($this->targetKeyword)) {
            $slugBase = $this->targetKeyword;
        } elseif (! empty($this->blogTitle)) {
            $slugBase = $this->blogTitle;
        } elseif (! empty($this->title)) {
            $slugBase = $this->title;
        }

        $slugBase = Str::slug($slugBase);

        $originalRaw = pathinfo((string) $this->featuredImageUpload->getClientOriginalName(), PATHINFO_FILENAME);
        $originalSlug = Str::slug($originalRaw);

        $isGeneric = (bool) preg_match('/^(img|image|screenshot|photo|dsc|untitled|banner|cover)[-_0-9]*$/i', $originalSlug);

        if (! empty($slugBase)) {
            $baseName = Str::limit($slugBase, 60, '').'-featured-image';
        } elseif (! empty($originalSlug) && ! $isGeneric) {
            $baseName = Str::limit($originalSlug, 60, '').'-featured-image';
        } else {
            $baseName = 'featured-image';
        }

        $extension = strtolower(
            $this->featuredImageUpload->getClientOriginalExtension()
            ?: $this->featuredImageUpload->guessExtension()
            ?: 'png'
        );

        $uniqueSuffix = substr(md5(uniqid((string) mt_rand(), true)), 0, 6);

        return "{$baseName}-{$uniqueSuffix}.{$extension}";
    }

    public function removeFeaturedImage(): void
    {
        $this->blogFeaturedImage = '';
        if ($this->blogPostId) {
            BlogPost::where('id', $this->blogPostId)->update(['featured_image' => null]);
        }
        $this->hasUnsavedChanges = true;
        session()->flash('blog_status', 'Featured image removed.');
    }

    public function setBlogCategory(string $category): void
    {
        $category = trim($category);
        if (empty($category)) {
            return;
        }

        $this->blogCategory = $category;
        if (! in_array($category, $this->blogCategories)) {
            $this->blogCategories[] = $category;
        }
    }

    public function updatedBlogStatus(string $value): void
    {
        $this->isPublishedToBlog = ($value === 'published');
    }

    public function addBlogTag(string $tag): void
    {
        $tag = trim($tag);
        if (empty($tag)) {
            return;
        }

        $existingTags = array_filter(array_map('trim', explode(',', $this->blogTags)));
        if (! in_array($tag, $existingTags)) {
            $existingTags[] = $tag;
            $this->blogTags = implode(', ', $existingTags);
        }
    }

    public function removeBlogTag(string $tagToRemove): void
    {
        $existingTags = array_filter(array_map('trim', explode(',', $this->blogTags)));
        $filtered = array_values(array_filter($existingTags, fn ($t) => strcasecmp($t, trim($tagToRemove)) !== 0));
        $this->blogTags = implode(', ', $filtered);
    }
}

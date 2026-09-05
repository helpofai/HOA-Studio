<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blog Post Model
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

namespace App\Features\Blog\Models;

use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'document_id',
        'title',
        'slug',
        'excerpt',
        'content_html',
        'content_markdown',
        'featured_image',
        'category',
        'tags',
        'status',
        'is_featured',
        'views_count',
        'reading_time_minutes',
        'seo_title',
        'seo_description',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'reading_time_minutes' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")
                ->orWhere('content_html', 'like', "%{$term}%");
        });
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (empty($category) || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function getPublicUrlAttribute(): string
    {
        return route('blog.show', $this->slug);
    }

    public function getEditUrlAttribute(): ?string
    {
        return $this->document_id ? route('documents.editor', $this->document_id) : null;
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if (empty($base)) {
            $base = 'article-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public static function defaultCategories(): array
    {
        return [
            'Artificial Intelligence',
            'Content Strategy',
            'Writing & Creativity',
            'Tutorials & Guides',
            'Business & Growth',
            'Technology & Engineering',
        ];
    }
}

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
use Illuminate\Support\Facades\DB;
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
                ->orWhere('tags', 'like', "%{$term}%")
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

    public function scopeTag(Builder $query, ?string $tag): Builder
    {
        if (empty($tag) || $tag === 'all') {
            return $query;
        }

        return $query->where(function ($q) use ($tag) {
            $q->whereJsonContains('tags', $tag)
                ->orWhere('tags', 'like', '%"'.$tag.'"%');
        });
    }

    public function scopeArchive(Builder $query, ?string $archive): Builder
    {
        if (empty($archive) || $archive === 'all') {
            return $query;
        }

        if (preg_match('/^(\d{4})-(\d{2})$/', $archive, $matches)) {
            return $query->whereYear('published_at', (int) $matches[1])
                ->whereMonth('published_at', (int) $matches[2]);
        }

        return $query;
    }

    public function scopeReadTime(Builder $query, ?string $readTime): Builder
    {
        if ($readTime === 'quick') {
            return $query->where('reading_time_minutes', '<', 5);
        }

        if ($readTime === 'deep') {
            return $query->where('reading_time_minutes', '>=', 5);
        }

        return $query;
    }

    public function scopeSortByCriteria(Builder $query, string $sort = 'latest'): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest('published_at'),
            'popular' => $query->orderByDesc('views_count')->latest('published_at'),
            'read_time_asc' => $query->orderBy('reading_time_minutes')->latest('published_at'),
            'read_time_desc' => $query->orderByDesc('reading_time_minutes')->latest('published_at'),
            'alpha' => $query->orderBy('title'),
            default => $query->latest('published_at'),
        };
    }

    public static function getPublishedTagsWithCounts(): array
    {
        $allTags = static::published()
            ->whereNotNull('tags')
            ->pluck('tags');

        $tagCounts = [];
        foreach ($allTags as $tagsList) {
            if (! is_array($tagsList)) {
                $tagsList = json_decode($tagsList, true) ?: [];
            }
            foreach ((array) $tagsList as $tag) {
                $cleanTag = trim($tag);
                if (! empty($cleanTag)) {
                    $tagCounts[$cleanTag] = ($tagCounts[$cleanTag] ?? 0) + 1;
                }
            }
        }

        arsort($tagCounts);

        return $tagCounts;
    }

    public static function getPublishedArchiveTimeline(): array
    {
        $posts = static::published()
            ->select(['id', 'published_at'])
            ->latest('published_at')
            ->get();

        $timeline = [];
        foreach ($posts as $post) {
            if ($post->published_at) {
                $key = $post->published_at->format('Y-m');
                $year = $post->published_at->format('Y');
                $month = $post->published_at->format('F');
                $label = $post->published_at->format('F Y');

                if (! isset($timeline[$key])) {
                    $timeline[$key] = [
                        'key' => $key,
                        'year' => $year,
                        'month' => $month,
                        'label' => $label,
                        'count' => 0,
                    ];
                }
                $timeline[$key]['count']++;
            }
        }

        return array_values($timeline);
    }

    public function incrementViews(): void
    {
        DB::table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->increment('views_count');

        $this->views_count = ($this->views_count ?? 0) + 1;
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

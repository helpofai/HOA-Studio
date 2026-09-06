<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blog Index Page Livewire Component
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

namespace App\Features\Blog\Livewire;

use App\Features\Blog\Models\BlogPost;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Blog & Articles — HelpOfAi Studio')]
class BlogIndexPage extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $category = 'all';

    #[Url(history: true)]
    public string $tag = 'all';

    #[Url(history: true)]
    public string $sort = 'latest';

    #[Url(history: true)]
    public string $archive = 'all';

    #[Url(history: true)]
    public string $readTime = 'all';

    #[Url(history: true)]
    public string $view = 'grid';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingTag(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function updatingArchive(): void
    {
        $this->resetPage();
    }

    public function updatingReadTime(): void
    {
        $this->resetPage();
    }

    public function filterCategory(string $cat): void
    {
        $this->category = ($this->category === $cat) ? 'all' : $cat;
        $this->resetPage();
    }

    public function filterTag(string $tag): void
    {
        $this->tag = ($this->tag === $tag) ? 'all' : $tag;
        $this->resetPage();
    }

    public function filterArchive(string $ym): void
    {
        $this->archive = ($this->archive === $ym) ? 'all' : $ym;
        $this->resetPage();
    }

    public function filterReadTime(string $time): void
    {
        $this->readTime = ($this->readTime === $time) ? 'all' : $time;
        $this->resetPage();
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['grid', 'list']) ? $view : 'grid';
    }

    public function removeSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function removeCategory(): void
    {
        $this->category = 'all';
        $this->resetPage();
    }

    public function removeTag(): void
    {
        $this->tag = 'all';
        $this->resetPage();
    }

    public function removeArchive(): void
    {
        $this->archive = 'all';
        $this->resetPage();
    }

    public function removeReadTime(): void
    {
        $this->readTime = 'all';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = 'all';
        $this->tag = 'all';
        $this->archive = 'all';
        $this->readTime = 'all';
        $this->sort = 'latest';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return ! empty($this->search)
            || $this->category !== 'all'
            || $this->tag !== 'all'
            || $this->archive !== 'all'
            || $this->readTime !== 'all'
            || $this->sort !== 'latest';
    }

    public function activeFilterCount(): int
    {
        $count = 0;
        if (! empty($this->search)) {
            $count++;
        }
        if ($this->category !== 'all') {
            $count++;
        }
        if ($this->tag !== 'all') {
            $count++;
        }
        if ($this->archive !== 'all') {
            $count++;
        }
        if ($this->readTime !== 'all') {
            $count++;
        }
        if ($this->sort !== 'latest') {
            $count++;
        }

        return $count;
    }

    public function render()
    {
        $hasFilters = $this->hasActiveFilters();

        // Featured Post (only on page 1 and when no active search/filters)
        $featuredPost = null;
        if (! $hasFilters && $this->getPage() === 1) {
            $featuredPost = BlogPost::with('user')
                ->published()
                ->featured()
                ->latest('published_at')
                ->first();

            if (! $featuredPost) {
                $featuredPost = BlogPost::with('user')
                    ->published()
                    ->latest('published_at')
                    ->first();
            }
        }

        // Exclude featured post ID from regular grid only if it's currently spotlighted
        $excludeId = $featuredPost?->id;

        $posts = BlogPost::with('user')
            ->published()
            ->search($this->search)
            ->category($this->category)
            ->tag($this->tag)
            ->archive($this->archive)
            ->readTime($this->readTime)
            ->sortByCriteria($this->sort)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->paginate(9);

        // Dynamic categories summary with counts
        $categories = BlogPost::published()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Dynamic tag cloud with frequencies
        $tagCloud = BlogPost::getPublishedTagsWithCounts();

        // Dynamic archive timeline (Year/Month)
        $archiveTimeline = BlogPost::getPublishedArchiveTimeline();

        $totalPublished = BlogPost::published()->count();

        // Determine dynamic page title
        $pageTitle = 'Knowledge Archive & Articles — HelpOfAi Studio';
        if (! empty($this->search)) {
            $pageTitle = 'Search: "'.$this->search.'" — HelpOfAi Archive';
        } elseif ($this->tag !== 'all') {
            $pageTitle = '#'.$this->tag.' Articles — HelpOfAi Archive';
        } elseif ($this->category !== 'all') {
            $pageTitle = $this->category.' Articles — HelpOfAi Archive';
        }

        return view('blog.index', [
            'featuredPost' => $featuredPost,
            'posts' => $posts,
            'categories' => $categories,
            'tagCloud' => $tagCloud,
            'archiveTimeline' => $archiveTimeline,
            'totalPublished' => $totalPublished,
            'hasFilters' => $hasFilters,
            'filterCount' => $this->activeFilterCount(),
        ])->title($pageTitle);
    }
}

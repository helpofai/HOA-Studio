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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function filterCategory(string $cat): void
    {
        $this->category = $cat;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = 'all';
        $this->resetPage();
    }

    public function render()
    {
        // Featured Post (only if not searching or filtering)
        $featuredPost = null;
        if (empty($this->search) && $this->category === 'all') {
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

        // Exclude featured post ID if displayed as hero
        $excludeId = $featuredPost?->id;

        $posts = BlogPost::with('user')
            ->published()
            ->search($this->search)
            ->category($this->category)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->latest('published_at')
            ->paginate(9);

        // Categories summary
        $categories = BlogPost::published()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalPublished = BlogPost::published()->count();

        return view('blog.index', [
            'featuredPost' => $featuredPost,
            'posts' => $posts,
            'categories' => $categories,
            'totalPublished' => $totalPublished,
        ]);
    }
}

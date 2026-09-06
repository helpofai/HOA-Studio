<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blog Post View Livewire Component
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
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BlogPostPage extends Component
{
    public string $slug;

    public function mount(string $slug)
    {
        $this->slug = $slug;

        $post = BlogPost::where('slug', $slug)->firstOrFail();

        // Check visibility: if draft, only author or admin can preview
        if ($post->status !== 'published') {
            $user = Auth::user();
            if (! $user || ($user->id !== $post->user_id && ! $user->isAdmin())) {
                abort(404, 'Blog post not found or not yet published.');
            }
        }

        // Increment view count once per user session
        $sessionKey = 'viewed_post_'.$post->id;
        if (! session()->has($sessionKey)) {
            $post->incrementViews();
            session()->put($sessionKey, true);
        }
    }

    public function render()
    {
        $post = BlogPost::with(['user', 'document'])
            ->where('slug', $this->slug)
            ->firstOrFail();

        $similarPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->latest('published_at')
            ->take(4)
            ->get();

        $authorPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('user_id', $post->user_id)
            ->latest('published_at')
            ->take(4)
            ->get();

        $trendingPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('views_count')
            ->latest('published_at')
            ->take(4)
            ->get();

        $canEdit = false;
        if (Auth::check()) {
            $currentUser = Auth::user();
            $canEdit = ($currentUser->id === $post->user_id || $currentUser->isAdmin());
        }

        $publishedAt = $post->published_at ?? now();

        $previousPost = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('published_at', '<=', $publishedAt)
            ->latest('published_at')
            ->first();

        $nextPost = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('published_at', '>=', $publishedAt)
            ->oldest('published_at')
            ->first();

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $similarPosts,
            'similarPosts' => $similarPosts,
            'authorPosts' => $authorPosts,
            'trendingPosts' => $trendingPosts,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'canEdit' => $canEdit,
        ])->title($post->seo_title ?: $post->title.' — HelpOfAi Blog');
    }
}

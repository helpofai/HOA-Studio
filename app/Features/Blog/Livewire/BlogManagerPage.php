<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blog Manager Dashboard Component
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
use App\Features\Documents\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.workspace')]
#[Title('Blog Articles Manager — HelpOfAi Studio')]
class BlogManagerPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all'; // all, published, draft

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function createNewBlogArticle()
    {
        $user = Auth::user();

        $document = Document::create([
            'user_id' => $user->id,
            'title' => 'New Blog Article',
            'slug' => 'new-blog-article-'.Str::lower(Str::random(6)),
            'status' => 'draft',
            'editor_type' => 'tiptap',
            'word_count' => 0,
            'character_count' => 0,
            'reading_time_minutes' => 1,
        ]);

        $document->content()->create([
            'content_html' => '<h1>New Blog Article</h1><p>Start writing your awesome article here with AI assistance...</p>',
            'content_plain' => 'New Blog Article Start writing your awesome article here with AI assistance...',
        ]);

        return redirect()->route('documents.editor', $document->id);
    }

    public function togglePostStatus(int $postId)
    {
        $user = Auth::user();
        $post = BlogPost::where('id', $postId)
            ->where(function ($q) use ($user) {
                if (! $user->isAdmin()) {
                    $q->where('user_id', $user->id);
                }
            })
            ->firstOrFail();

        if ($post->status === 'published') {
            $post->update(['status' => 'draft']);
            if ($post->document) {
                $post->document->update(['status' => 'draft']);
            }
            session()->flash('status', "Post '{$post->title}' changed to draft.");
        } else {
            $post->update(['status' => 'published', 'published_at' => $post->published_at ?? now()]);
            if ($post->document) {
                $post->document->update(['status' => 'published']);
            }
            session()->flash('status', "Post '{$post->title}' published live.");
        }
    }

    public function deletePost(int $postId)
    {
        $user = Auth::user();
        $post = BlogPost::where('id', $postId)
            ->where(function ($q) use ($user) {
                if (! $user->isAdmin()) {
                    $q->where('user_id', $user->id);
                }
            })
            ->firstOrFail();

        $title = $post->title;
        $post->delete();

        session()->flash('status', "Blog post '{$title}' deleted.");
    }

    public function render()
    {
        $user = Auth::user();

        $query = BlogPost::with(['user', 'document'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when(! empty($this->search), fn ($q) => $q->search($this->search))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest();

        $posts = $query->paginate(10);

        $stats = [
            'total' => BlogPost::when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))->count(),
            'published' => BlogPost::when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))->where('status', 'published')->count(),
            'drafts' => BlogPost::when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))->where('status', 'draft')->count(),
            'views' => (int) BlogPost::when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))->sum('views_count'),
        ];

        return view('blog.manager', [
            'posts' => $posts,
            'stats' => $stats,
        ]);
    }
}

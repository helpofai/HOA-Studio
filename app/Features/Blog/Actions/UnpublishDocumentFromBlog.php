<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Unpublish Document From Blog Action
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

class UnpublishDocumentFromBlog
{
    /**
     * Unpublish an active blog post linked to a document.
     */
    public function execute(Document $document): bool
    {
        $post = BlogPost::where('document_id', $document->id)->first();

        if ($post) {
            $post->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
        }

        $document->update(['status' => 'draft']);

        return true;
    }
}

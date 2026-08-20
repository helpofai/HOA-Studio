<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Documents\Http\Controllers;

use App\Features\Documents\Actions\CreateDocument;
use App\Features\Documents\Models\Document;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpenEditorController extends Controller
{
    public function __invoke(Request $request, CreateDocument $creator): RedirectResponse
    {
        $user = Auth::user();

        // If a new document is explicitly requested via query parameter
        if ($request->has('new')) {
            $doc = $creator->execute($user, [
                'title' => 'Untitled Document',
                'content_html' => '<p>Start writing your AI-powered content...</p>',
            ]);
            return redirect()->route('documents.editor', $doc->id);
        }

        // Open user's most recent document, or create a new one if none exists
        $latest = Document::where('user_id', $user->id)->latest('updated_at')->first();

        if ($latest) {
            return redirect()->route('documents.editor', $latest->id);
        }

        $doc = $creator->execute($user, [
            'title' => 'Untitled Document',
            'content_html' => '<p>Start writing your AI-powered content...</p>',
        ]);

        return redirect()->route('documents.editor', $doc->id);
    }
}

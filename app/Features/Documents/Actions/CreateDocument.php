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

namespace App\Features\Documents\Actions;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentContent;
use App\Features\Documents\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDocument
{
    public function execute(User $user, array $data): Document
    {
        return DB::transaction(function () use ($user, $data) {
            $title = trim($data['title'] ?? 'Untitled Document');
            $slug = Str::slug($title) . '-' . Str::lower(Str::random(6));

            $initialHtml = $data['content_html'] ?? '<p>Start writing your AI-powered content...</p>';
            $plainText = strip_tags($initialHtml);
            $wordCount = str_word_count($plainText);
            $charCount = mb_strlen($plainText);
            $readingTime = max(1, (int) ceil($wordCount / 200));

            $document = Document::create([
                'user_id' => $user->id,
                'project_id' => $data['project_id'] ?? null,
                'brand_profile_id' => $data['brand_profile_id'] ?? null,
                'title' => $title,
                'slug' => $slug,
                'status' => 'draft',
                'word_count' => $wordCount,
                'character_count' => $charCount,
                'reading_time_minutes' => $readingTime,
                'seo_score' => 0,
            ]);

            DocumentContent::create([
                'document_id' => $document->id,
                'content_html' => $initialHtml,
                'content_json' => $data['content_json'] ?? null,
                'content_markdown' => $data['content_markdown'] ?? null,
                'content_plain' => $plainText,
            ]);

            $version = DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => 1,
                'title' => $title,
                'content_html' => $initialHtml,
                'content_json' => $data['content_json'] ?? null,
                'operation_type' => 'manual_save',
                'summary' => 'Initial document creation',
                'word_count' => $wordCount,
                'created_by' => $user->id,
            ]);

            $document->update(['current_version_id' => $version->id]);

            return $document->load(['content', 'versions']);
        });
    }
}
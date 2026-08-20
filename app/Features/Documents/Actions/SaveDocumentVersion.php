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
use App\Features\Documents\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveDocumentVersion
{
    public function execute(Document $document, User $user, array $data): DocumentVersion
    {
        return DB::transaction(function () use ($document, $user, $data) {
            $latestVersion = $document->versions()->max('version_number') ?? 0;
            $nextVersion = $latestVersion + 1;

            $html = $data['content_html'] ?? ($document->content->content_html ?? '');
            $plain = strip_tags($html);
            $wordCount = str_word_count($plain);

            $version = DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $nextVersion,
                'title' => $data['title'] ?? $document->title,
                'content_html' => $html,
                'content_json' => $data['content_json'] ?? ($document->content->content_json ?? null),
                'operation_type' => $data['operation_type'] ?? 'manual_save',
                'summary' => $data['summary'] ?? ('Version ' . $nextVersion . ' saved'),
                'word_count' => $wordCount,
                'created_by' => $user->id,
            ]);

            $document->update([
                'title' => $data['title'] ?? $document->title,
                'word_count' => $wordCount,
                'character_count' => mb_strlen($plain),
                'reading_time_minutes' => max(1, (int) ceil($wordCount / 200)),
                'current_version_id' => $version->id,
            ]);

            if ($document->content) {
                $document->content->update([
                    'content_html' => $html,
                    'content_json' => $data['content_json'] ?? $document->content->content_json,
                    'content_plain' => $plain,
                ]);
            }

            return $version;
        });
    }
}
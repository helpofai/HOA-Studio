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

class RestoreDocumentVersion
{
    public function execute(Document $document, DocumentVersion $versionToRestore, User $user): Document
    {
        return DB::transaction(function () use ($document, $versionToRestore, $user) {
            $latestVersion = $document->versions()->max('version_number') ?? 0;
            $nextVersionNumber = $latestVersion + 1;

            $plain = strip_tags($versionToRestore->content_html);
            $wordCount = str_word_count($plain);

            $newVersion = DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $nextVersionNumber,
                'title' => $versionToRestore->title,
                'content_html' => $versionToRestore->content_html,
                'content_json' => $versionToRestore->content_json,
                'operation_type' => 'restore',
                'summary' => 'Restored from Version #' . $versionToRestore->version_number,
                'word_count' => $wordCount,
                'created_by' => $user->id,
            ]);

            $document->update([
                'title' => $versionToRestore->title,
                'word_count' => $wordCount,
                'character_count' => mb_strlen($plain),
                'reading_time_minutes' => max(1, (int) ceil($wordCount / 200)),
                'current_version_id' => $newVersion->id,
            ]);

            if ($document->content) {
                $document->content->update([
                    'content_html' => $versionToRestore->content_html,
                    'content_json' => $versionToRestore->content_json,
                    'content_plain' => $plain,
                ]);
            }

            return $document->fresh(['content', 'versions']);
        });
    }
}
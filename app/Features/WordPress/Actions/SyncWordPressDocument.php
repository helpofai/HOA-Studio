<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Sync WordPress Document Action
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

namespace App\Features\WordPress\Actions;

use App\Features\Documents\Models\Document;
use App\Models\User;
use Illuminate\Support\Str;

class SyncWordPressDocument
{
    /**
     * Executes bidirectional document sync between WordPress and HOA Studio.
     */
    public function execute(User $user, array $data): Document
    {
        if (! empty($data['document_id'])) {
            $doc = Document::where('id', $data['document_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $doc = new Document;
            $doc->user_id = $user->id;
            $doc->status = 'draft';
            $doc->slug = Str::slug($data['title']).'-'.Str::random(6);
        }

        $plain = strip_tags($data['content_html']);
        $doc->title = $data['title'];
        $doc->word_count = str_word_count($plain);
        $doc->character_count = strlen($plain);
        $doc->reading_time_minutes = max(1, (int) ceil($doc->word_count / 200));
        $doc->save();

        $content = $doc->content()->firstOrCreate(['document_id' => $doc->id]);
        $content->content_html = $data['content_html'];
        $content->content_plain = $plain;
        $content->save();

        return $doc;
    }
}

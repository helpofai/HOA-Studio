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

namespace App\Features\Documents\Adapters;

use App\Features\Documents\Contracts\EditorAdapterInterface;

class NotionAdapter implements EditorAdapterInterface
{
    public function toCanonical(string $content): array
    {
        $plainText = strip_tags($content);

        return [
            'content_html' => $content,
            'content_plain' => $plainText,
            'content_markdown' => $this->blocksToMarkdown($content),
        ];
    }

    public function fromCanonical(array $canonical): string
    {
        return $canonical['content_html'] ?? '';
    }

    protected function blocksToMarkdown(string $html): string
    {
        $markdown = preg_replace('/<h[1-4]>(.*?)<\/h[1-4]>/i', "\n# $1\n", $html);
        $markdown = preg_replace('/<p>(.*?)<\/p>/i', "$1\n\n", $markdown);
        $markdown = preg_replace('/<strong>(.*?)<\/strong>/i', "**$1**", $markdown);
        $markdown = preg_replace('/<em>(.*?)<\/em>/i', "*$1*", $markdown);
        $markdown = preg_replace('/<li>(.*?)<\/li>/i', "* $1\n", $markdown);
        return trim(strip_tags($markdown));
    }
}

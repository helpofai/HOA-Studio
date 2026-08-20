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
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownAdapter implements EditorAdapterInterface
{
    protected GithubFlavoredMarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function toCanonical(string $content): array
    {
        $html = $this->converter->convert($content)->getContent();

        return [
            'content_html' => $html,
            'content_markdown' => $content,
            'content_plain' => strip_tags($html),
        ];
    }

    public function fromCanonical(array $canonical): string
    {
        return $canonical['content_markdown'] ?? '';
    }
}

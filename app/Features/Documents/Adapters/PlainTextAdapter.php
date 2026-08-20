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

class PlainTextAdapter implements EditorAdapterInterface
{
    public function toCanonical(string $content): array
    {
        $sanitized = strip_tags($content);
        return [
            'content_html' => '<p>' . e($sanitized) . '</p>',
            'content_plain' => $sanitized,
            'content_markdown' => $sanitized,
        ];
    }

    public function fromCanonical(array $canonical): string
    {
        return $canonical['content_plain'] ?? '';
    }
}

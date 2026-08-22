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

namespace App\Features\Documents\Services;

use App\Features\Documents\Adapters\TiptapAdapter;
use App\Features\Documents\Adapters\GutenbergAdapter;
use App\Features\Documents\Adapters\NotionAdapter;
use App\Features\Documents\Adapters\MarkdownAdapter;
use App\Features\Documents\Adapters\HtmlAdapter;
use App\Features\Documents\Adapters\PlainTextAdapter;
use App\Features\Documents\Contracts\EditorAdapterInterface;
use InvalidArgumentException;

class EditorManager
{
    protected array $adapters = [];

    public function __construct()
    {
        // Register all standard and specialized adapters
        $this->registerAdapter('tiptap', new TiptapAdapter());
        $this->registerAdapter('gutenberg', new GutenbergAdapter());
        $this->registerAdapter('block_editor', new NotionAdapter());
        $this->registerAdapter('notion', new NotionAdapter());
        $this->registerAdapter('markdown', new MarkdownAdapter());
        $this->registerAdapter('markdown_split', new MarkdownAdapter());
        $this->registerAdapter('html', new HtmlAdapter());
        $this->registerAdapter('plain_text', new PlainTextAdapter());
    }

    public function registerAdapter(string $type, EditorAdapterInterface $adapter): void
    {
        $this->adapters[$type] = $adapter;
    }

    public function adapter(string $type): EditorAdapterInterface
    {
        if (!isset($this->adapters[$type])) {
            throw new InvalidArgumentException("Editor adapter for type [{$type}] is not registered.");
        }

        return $this->adapters[$type];
    }
}

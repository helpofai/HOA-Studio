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

namespace App\Features\Documents\Contracts;

class EditorRegistry
{
    /**
     * Supported editor drivers metadata.
     * Ready for Tiptap, Gutenberg, Block Editor, and Markdown canvas.
     */
    public static function getAvailableEditors(): array
    {
        return [
            'tiptap' => [
                'name' => 'Tiptap ProseMirror',
                'description' => 'Fast, modern rich-text editor with floating AI bubble bar and slash commands.',
                'icon' => 'sparkles',
                'is_active' => true,
            ],
            'gutenberg' => [
                'name' => 'Gutenberg Block Editor',
                'description' => 'WordPress-compatible block canvas with modular paragraph, heading, and media units.',
                'icon' => 'layout-grid',
                'is_active' => true,
            ],
            'block_editor' => [
                'name' => 'Notion-Style Block Canvas',
                'description' => 'Draggable modular block workspace for structured content authoring.',
                'icon' => 'blocks',
                'is_active' => true,
            ],
            'markdown' => [
                'name' => 'Raw Markdown / Split Preview',
                'description' => 'Distraction-free monospace Markdown editor with live HTML rendering.',
                'icon' => 'code',
                'is_active' => true,
            ],
        ];
    }

    public static function isValidEditor(string $type): bool
    {
        return array_key_exists($type, self::getAvailableEditors());
    }
}
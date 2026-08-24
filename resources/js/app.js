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

import './bootstrap';
import './features/editor/editor-manager.js';
import './features/editor/drivers/tiptap-driver.js';
import './features/editor/drivers/gutenberg-driver.js';
import './features/editor/drivers/notion-driver.js';
import './features/editor/drivers/markdown-driver.js';
import './features/editor/drivers/markdown-split-driver.js';
import './features/editor/drivers/html-driver.js';
import './features/editor/drivers/plaintext-driver.js';
import { initCursorSpotlight } from './features/ui/cursor-spotlight.js';
import { createIcons, icons } from 'lucide';

window.initLucideIcons = () => {
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initLucideIcons();
    initCursorSpotlight();
});

document.addEventListener('livewire:navigated', () => {
    window.initLucideIcons();
    initCursorSpotlight();
});


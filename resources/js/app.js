import './bootstrap';
import './features/editor/editor-manager.js';
import { initBlogVisualEnhancer } from './features/blog/blog-visual-enhancer.js';
import { initCursorSpotlight } from './features/ui/cursor-spotlight.js';
import { createIcons, icons } from 'lucide';

window.initLucideIcons = () => {
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initLucideIcons();
    initCursorSpotlight();
    initBlogVisualEnhancer();
});

document.addEventListener('livewire:navigated', () => {
    window.initLucideIcons();
    initCursorSpotlight();
    initBlogVisualEnhancer();
});

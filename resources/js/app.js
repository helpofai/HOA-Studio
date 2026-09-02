import './bootstrap';
import './features/editor/editor-manager.js';
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

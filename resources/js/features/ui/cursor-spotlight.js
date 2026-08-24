/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Mouse Cursor Glow Spotlight
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

export function initCursorSpotlight() {
    let spotlight = document.getElementById('hoa-cursor-spotlight');
    if (!spotlight) {
        spotlight = document.createElement('div');
        spotlight.id = 'hoa-cursor-spotlight';
        spotlight.className = 'fixed top-0 left-0 -mt-64 -ml-64 w-[36rem] h-[36rem] rounded-full pointer-events-none transition-opacity duration-300 ease-out opacity-0 z-0 will-change-transform';
        spotlight.style.background = 'radial-gradient(circle at center, rgba(129, 140, 248, 0.18) 0%, rgba(168, 85, 247, 0.09) 35%, rgba(6, 182, 212, 0.04) 60%, transparent 75%)';
        spotlight.style.filter = 'blur(40px)';
        document.body.prepend(spotlight);
    }

    if (window._hoaSpotlightInitialized) {
        return;
    }
    window._hoaSpotlightInitialized = true;

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let currentX = mouseX;
    let currentY = mouseY;
    let rafId = null;

    function updatePosition() {
        currentX += (mouseX - currentX) * 0.18;
        currentY += (mouseY - currentY) * 0.18;

        const el = document.getElementById('hoa-cursor-spotlight');
        if (el) {
            el.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;
        }

        rafId = requestAnimationFrame(updatePosition);
    }

    window.addEventListener('pointermove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;

        const el = document.getElementById('hoa-cursor-spotlight');
        if (el && el.style.opacity !== '1') {
            el.style.opacity = '1';
        }

        if (!rafId) {
            rafId = requestAnimationFrame(updatePosition);
        }
    }, { passive: true });

    document.addEventListener('mouseleave', () => {
        const el = document.getElementById('hoa-cursor-spotlight');
        if (el) {
            el.style.opacity = '0';
        }
    });

    document.addEventListener('mouseenter', () => {
        const el = document.getElementById('hoa-cursor-spotlight');
        if (el) {
            el.style.opacity = '1';
        }
    });

    rafId = requestAnimationFrame(updatePosition);
}

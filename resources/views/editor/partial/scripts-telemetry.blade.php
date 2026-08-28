{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Editor Scripts: Telemetry & Logging
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
--}}

// Draggable Floating Selection Actions Bar (.editor-floating-actions)
isDraggingBubble: false,
bubblePos: { x: null, y: null },
bubbleDragOffset: { x: 0, y: 0 },

startDragBubble(e) {
    const bubbleEl = document.getElementById('tiptap-bubble-menu');
    if (!bubbleEl) return;

    this.isDraggingBubble = true;
    const rect = bubbleEl.getBoundingClientRect();
    if (this.bubblePos.x === null) {
        this.bubblePos.x = rect.left;
        this.bubblePos.y = rect.top;
    }

    const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
    this.bubbleDragOffset.x = clientX - this.bubblePos.x;
    this.bubbleDragOffset.y = clientY - this.bubblePos.y;

    const onMouseMove = (moveEvent) => {
        if (!this.isDraggingBubble) return;
        const curX = moveEvent.clientX || (moveEvent.touches && moveEvent.touches[0] ? moveEvent.touches[0].clientX : 0);
        const curY = moveEvent.clientY || (moveEvent.touches && moveEvent.touches[0] ? moveEvent.touches[0].clientY : 0);
        const newX = Math.max(10, Math.min(window.innerWidth - (rect.width || 300), curX - this.bubbleDragOffset.x));
        const newY = Math.max(10, Math.min(window.innerHeight - 60, curY - this.bubbleDragOffset.y));
        this.bubblePos.x = newX;
        this.bubblePos.y = newY;
    };

    const onMouseUp = () => {
        this.isDraggingBubble = false;
        window.removeEventListener('mousemove', onMouseMove);
        window.removeEventListener('mouseup', onMouseUp);
        window.removeEventListener('touchmove', onMouseMove);
        window.removeEventListener('touchend', onMouseUp);
    };

    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
    window.addEventListener('touchmove', onMouseMove);
    window.addEventListener('touchend', onMouseUp);
},

aiLogs: [],
logFilter: 'ALL',

get filteredLogs() {
    if (this.logFilter === 'ALL') return this.aiLogs;
    if (this.logFilter === 'AI') return this.aiLogs.filter(l => l.level === 'AI' || l.level === 'STREAM' || l.level === 'GENERATE');
    if (this.logFilter === 'SEO') return this.aiLogs.filter(l => l.level === 'SEO');
    if (this.logFilter === 'ERR') return this.aiLogs.filter(l => l.level === 'ERROR' || l.level === 'ERR' || l.level === 'WARN');
    return this.aiLogs;
},

addLog(level, msg) {
    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    this.aiLogs.unshift({ time: timeStr, level: level.toUpperCase(), msg: msg });
    if (this.aiLogs.length > 50) this.aiLogs.pop();
},

clearLogs() {
    this.aiLogs = [];
}

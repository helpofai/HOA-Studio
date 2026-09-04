{{--
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Telemetry & Logs
|--------------------------------------------------------------------------
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
},

// Floating Telemetry Window State
showFloatingTelemetry: false,
pipelineStageLog: [],
draggingTelemetry: false,
resizingTelemetry: false,
startTelemetryX: 0,
startTelemetryY: 0,
startTelemetryW: 0,
startTelemetryH: 0,
telemetryX: window.innerWidth > 1024 ? window.innerWidth - 420 : 20,
telemetryY: window.innerHeight > 1024 ? window.innerHeight - 500 : 80,
telemetryW: 380,
telemetryH: 400,

startDragTelemetry(e) {
    this.draggingTelemetry = true;
    const cx = e.clientX || (e.touches && e.touches[0].clientX);
    const cy = e.clientY || (e.touches && e.touches[0].clientY);
    this.startTelemetryX = cx - this.telemetryX;
    this.startTelemetryY = cy - this.telemetryY;
},

startResizeTelemetry(e) {
    e.preventDefault();
    e.stopPropagation();
    this.resizingTelemetry = true;
    this.startTelemetryX = e.clientX || (e.touches && e.touches[0].clientX);
    this.startTelemetryY = e.clientY || (e.touches && e.touches[0].clientY);
    this.startTelemetryW = this.telemetryW;
    this.startTelemetryH = this.telemetryH;
},

doMoveTelemetry(e) {
    const cx = e.clientX || (e.touches && e.touches[0].clientX);
    const cy = e.clientY || (e.touches && e.touches[0].clientY);
    if (this.draggingTelemetry) {
        this.telemetryX = Math.max(0, Math.min(window.innerWidth - this.telemetryW, cx - this.startTelemetryX));
        this.telemetryY = Math.max(0, Math.min(window.innerHeight - 50, cy - this.startTelemetryY));
    } else if (this.resizingTelemetry) {
        this.telemetryW = Math.max(300, Math.min(window.innerWidth - this.telemetryX - 10, this.startTelemetryW + (cx - this.startTelemetryX)));
        this.telemetryH = Math.max(250, Math.min(window.innerHeight - this.telemetryY - 10, this.startTelemetryH + (cy - this.startTelemetryY)));
    }
},

endMoveTelemetry() {
    this.draggingTelemetry = false;
    this.resizingTelemetry = false;
}

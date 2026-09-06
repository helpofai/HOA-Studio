/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blog Visual Enhancer
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

let mermaidInstance = null;

async function getMermaid() {
    if (!mermaidInstance) {
        const { default: mermaid } = await import('mermaid');
        mermaid.initialize({
            startOnLoad: false,
            theme: 'dark',
            securityLevel: 'loose',
            fontFamily: 'ui-sans-serif, system-ui, sans-serif',
            er: {
                diagramPadding: 20,
                layoutDirection: 'TB',
                minEntityWidth: 100,
                minEntityHeight: 75,
                entityPadding: 15,
                stroke: '#818cf8',
                fill: '#0f172a',
                fontSize: 12,
                useMaxWidth: true
            },
            flowchart: {
                useMaxWidth: true,
                htmlLabels: true
            },
            themeVariables: {
                darkMode: true,
                background: '#020617',
                mainBkg: '#0b0f19',
                primaryColor: '#6366f1',
                primaryTextColor: '#f8fafc',
                primaryBorderColor: '#818cf8',
                lineColor: '#818cf8',
                secondaryColor: '#7c3aed',
                tertiaryColor: '#0f172a',
                nodeBorder: '#818cf8',
                clusterBkg: '#0b0f19',
                titleColor: '#e0e7ff',
                edgeLabelBackground: '#0b0f19',
                actorBkg: '#1e1b4b',
                actorBorder: '#818cf8',
                actorTextColor: '#f8fafc',
                signalColor: '#a5b4fc',
                signalTextColor: '#f8fafc',
                labelBoxBkgColor: '#0b0f19',
                labelBoxBorderColor: '#818cf8',
                labelTextColor: '#f8fafc',
                loopTextColor: '#f8fafc'
            }
        });
        mermaidInstance = mermaid;
    }
    return mermaidInstance;
}

export function cleanMermaidCode(code) {
    if (!code) return '';
    let clean = code.trim();
    clean = clean
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'");

    clean = clean.replace(/^```(?:mermaid)?\s*/i, '');
    clean = clean.replace(/\s*```\s*$/i, '');
    clean = clean.replace(/^mermaid\s+/i, '');

    return clean.trim();
}

export function detectDiagramType(code) {
    const trimmed = cleanMermaidCode(code).toLowerCase();
    if (trimmed.startsWith('erdiagram')) return '📊 ER Schema Diagram';
    if (trimmed.startsWith('flowchart') || trimmed.startsWith('graph')) return '⚡ Architecture Flow';
    if (trimmed.startsWith('sequencediagram')) return '🔄 Sequence Flow';
    if (trimmed.startsWith('classdiagram')) return '🏛️ Class Diagram';
    if (trimmed.startsWith('statediagram')) return '🔁 State Diagram';
    if (trimmed.startsWith('gitgraph')) return '🌿 Git Graph';
    if (trimmed.startsWith('pie')) return '🥧 Pie Distribution';
    if (trimmed.startsWith('mindmap')) return '🧠 Mind Map';
    return '📈 Vector Diagram';
}

function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Unpacks any giant <pre> code blocks that contain embedded Markdown headings,
 * dividers, ASCII art, or Mermaid blocks into separate native DOM elements.
 */
export function unpackEmbeddedMarkdownBlocks(container) {
    const preBlocks = Array.from(container.querySelectorAll('pre'));

    preBlocks.forEach(pre => {
        if (pre.closest('.hoa-mermaid-container') || pre.closest('.hoa-ascii-terminal-wrapper')) return;

        const rawText = pre.textContent;

        const hasHeadings = /(?:^|\n)#{1,4}\s+/.test(rawText);
        const hasDivider = /(?:^|\n)(\-{3,}|\*{3,}|_{3,})(?:\n|$)/.test(rawText);
        const hasFences = /```/.test(rawText);
        const hasMixedDiagrams = /[┌─┐│└┘├┤┬┴┼▼▲]/.test(rawText) && /(?:erDiagram|flowchart|graph|mermaid)/i.test(rawText);

        if (!hasHeadings && !hasDivider && !hasFences && !hasMixedDiagrams) {
            return;
        }

        const lines = rawText.split(/\r?\n/);
        const tokens = [];
        let currentMode = 'none'; // 'code', 'mermaid', 'text'
        let currentBuffer = [];
        let currentLang = '';

        function flushBuffer() {
            if (currentBuffer.length === 0) return;
            const chunk = currentBuffer.join('\n').trim();
            if (!chunk) {
                currentBuffer = [];
                return;
            }

            if (currentMode === 'code') {
                tokens.push({ type: 'code', lang: currentLang, content: currentBuffer.join('\n') });
            } else if (currentMode === 'mermaid') {
                tokens.push({ type: 'mermaid', content: currentBuffer.join('\n') });
            } else {
                tokens.push({ type: 'text', content: chunk });
            }
            currentBuffer = [];
        }

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const trimmed = line.trim();

            if (trimmed.startsWith('```')) {
                if (currentMode === 'code' || currentMode === 'mermaid') {
                    flushBuffer();
                    currentMode = 'none';
                    currentLang = '';
                    continue;
                } else {
                    flushBuffer();
                    const lang = trimmed.slice(3).trim().toLowerCase();
                    if (lang === 'mermaid') {
                        currentMode = 'mermaid';
                    } else {
                        currentMode = 'code';
                        currentLang = lang;
                    }
                    continue;
                }
            }

            if (currentMode === 'code' || currentMode === 'mermaid') {
                currentBuffer.push(line);
                continue;
            }

            if (trimmed === 'mermaid' || /^(erDiagram|flowchart|graph|sequenceDiagram|classDiagram|stateDiagram-v2|stateDiagram|gitGraph|pie|mindmap)/i.test(trimmed)) {
                flushBuffer();
                currentMode = 'mermaid';
                if (trimmed !== 'mermaid') {
                    currentBuffer.push(line);
                }
                continue;
            }

            const headingMatch = line.match(/^(#{1,4})\s+(.+)$/);
            if (headingMatch) {
                flushBuffer();
                tokens.push({
                    type: 'heading',
                    level: headingMatch[1].length,
                    text: headingMatch[2].trim()
                });
                continue;
            }

            if (/^(\-{3,}|\*{3,}|_{3,})$/.test(trimmed)) {
                flushBuffer();
                tokens.push({ type: 'divider' });
                continue;
            }

            if (trimmed.length > 0) {
                currentBuffer.push(line);
            } else if (currentBuffer.length > 0) {
                flushBuffer();
            }
        }

        flushBuffer();

        if (tokens.length <= 1 && tokens[0]?.type === 'code') {
            return;
        }

        const fragment = document.createDocumentFragment();
        tokens.forEach(token => {
            if (token.type === 'heading') {
                const tag = 'h' + Math.min(6, Math.max(2, token.level));
                const h = document.createElement(tag);
                const slugId = 'section-' + token.text.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                h.id = slugId;
                h.className = token.level === 2
                    ? 'text-2xl sm:text-3xl font-bold tracking-tight text-white mt-12 mb-6 flex items-center gap-3'
                    : 'text-xl sm:text-2xl font-semibold tracking-tight text-white mt-8 mb-4 flex items-center gap-2';
                h.textContent = token.text;
                fragment.appendChild(h);
            } else if (token.type === 'divider') {
                const hr = document.createElement('hr');
                hr.className = 'my-10 border-white/10';
                fragment.appendChild(hr);
            } else if (token.type === 'mermaid') {
                const preM = document.createElement('pre');
                preM.className = 'language-mermaid';
                const codeM = document.createElement('code');
                codeM.className = 'language-mermaid';
                codeM.textContent = cleanMermaidCode(token.content);
                preM.appendChild(codeM);
                fragment.appendChild(preM);
            } else if (token.type === 'code') {
                const preC = document.createElement('pre');
                if (token.lang) {
                    preC.className = 'language-' + token.lang;
                }
                const codeC = document.createElement('code');
                if (token.lang) {
                    codeC.className = 'language-' + token.lang;
                }
                codeC.textContent = token.content;
                preC.appendChild(codeC);
                fragment.appendChild(preC);
            } else if (token.type === 'text') {
                const p = document.createElement('p');
                p.className = 'text-slate-300 leading-relaxed my-4 text-base';
                p.textContent = token.content;
                fragment.appendChild(p);
            }
        });

        pre.replaceWith(fragment);
    });
}

export async function enhanceMermaidDiagrams(container) {
    const codeBlocks = container.querySelectorAll('pre code, pre');
    const mermaidBlocks = [];

    codeBlocks.forEach(block => {
        if (block.closest('.hoa-mermaid-container')) return;
        
        const rawText = block.textContent.trim();
        const clean = cleanMermaidCode(rawText);
        const isMermaidClass = block.classList.contains('language-mermaid') || block.parentElement?.classList.contains('language-mermaid');
        const isMermaidSyntax = /^(erDiagram|flowchart|graph|sequenceDiagram|classDiagram|stateDiagram-v2|stateDiagram|gitGraph|pie|mindmap)/i.test(clean);

        if (isMermaidClass || isMermaidSyntax) {
            const preElement = block.tagName === 'PRE' ? block : block.closest('pre');
            if (preElement && !mermaidBlocks.includes(preElement)) {
                mermaidBlocks.push(preElement);
            }
        }
    });

    if (mermaidBlocks.length === 0) return;

    try {
        const mermaid = await getMermaid();

        for (let i = 0; i < mermaidBlocks.length; i++) {
            const pre = mermaidBlocks[i];
            const rawCode = cleanMermaidCode(pre.textContent.trim());
            const badge = detectDiagramType(rawCode);
            const uniqueId = 'hoa-mermaid-' + i + '-' + Math.random().toString(36).substring(2, 8);

            try {
                const { svg } = await mermaid.render(uniqueId, rawCode);

                const card = document.createElement('div');
                card.className = 'hoa-mermaid-container my-8 rounded-3xl border border-indigo-500/25 bg-slate-950/85 p-5 shadow-2xl backdrop-blur-xl space-y-4';
                card.innerHTML = `
                    <div class="flex items-center justify-between pb-3 border-b border-white/10 flex-wrap gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[11px] font-mono uppercase font-bold tracking-wider">
                                ${badge}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-[10px] text-slate-400 font-mono">
                                <span>✋</span> <span>Drag to Move</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-[10px] text-slate-400 font-mono">
                                <span>🖱️</span> <span>Scroll to Zoom</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <!-- Zoom Controls Matrix -->
                            <div class="flex items-center bg-slate-900/90 rounded-xl border border-white/15 p-0.5 shadow-inner">
                                <button type="button" class="hoa-mermaid-zoom-out-btn px-2.5 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 text-xs font-mono transition-all cursor-pointer select-none" title="Zoom Out (-25%)">
                                    <span>➖</span>
                                </button>
                                <button type="button" class="hoa-mermaid-zoom-reset-btn px-2.5 py-1 text-slate-300 hover:text-indigo-300 text-xs font-mono font-semibold transition-all cursor-pointer select-none" title="Click to Reset to 100%">
                                    <span class="hoa-mermaid-zoom-label">100%</span>
                                </button>
                                <button type="button" class="hoa-mermaid-zoom-in-btn px-2.5 py-1 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 text-xs font-mono transition-all cursor-pointer select-none" title="Zoom In (+25%)">
                                    <span>➕</span>
                                </button>
                            </div>

                            <!-- Fit to View Button -->
                            <button type="button" class="hoa-mermaid-fit-btn px-2.5 py-1.5 rounded-xl glass-subtle hover:border-white/30 text-slate-300 hover:text-white text-[11px] font-mono transition-all flex items-center gap-1 cursor-pointer select-none" title="Fit to Container">
                                <span>⟲</span> <span>Fit</span>
                            </button>

                            <!-- Source Drawer Toggle -->
                            <button type="button" class="hoa-mermaid-toggle-btn px-2.5 py-1.5 rounded-xl glass-subtle hover:border-white/30 text-slate-300 hover:text-white text-[11px] font-mono transition-all flex items-center gap-1 cursor-pointer select-none">
                                <span>💻</span> <span>Source</span>
                            </button>

                            <!-- Copy Code Button -->
                            <button type="button" class="hoa-mermaid-copy-btn px-2.5 py-1.5 rounded-xl glass-subtle hover:border-white/30 text-slate-300 hover:text-white text-[11px] font-mono transition-all flex items-center gap-1 cursor-pointer select-none">
                                <span>📋</span> <span>Copy</span>
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Pan & Zoom Canvas Viewport -->
                    <div class="hoa-mermaid-svg-viewport relative overflow-hidden rounded-2xl border border-white/10 bg-slate-950/70 select-none cursor-grab flex items-center justify-center p-4 min-h-[460px]">
                        <div class="hoa-mermaid-canvas-inner will-change-transform flex items-center justify-center" style="transform-origin: center center; display: inline-flex;">
                            ${svg}
                        </div>

                        <!-- Floating Quick Action Dock in Canvas -->
                        <div class="absolute bottom-3 right-3 flex items-center gap-1 p-1 rounded-xl bg-slate-900/85 backdrop-blur-md border border-white/15 shadow-xl z-10 pointer-events-auto">
                            <button type="button" class="hoa-quick-zoom-out p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors text-xs" title="Zoom Out">➖</button>
                            <button type="button" class="hoa-quick-reset px-2 py-1 rounded-lg text-slate-300 hover:text-indigo-300 hover:bg-white/10 transition-colors text-[11px] font-mono font-semibold" title="Reset View">Fit</button>
                            <button type="button" class="hoa-quick-zoom-in p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors text-xs" title="Zoom In">➕</button>
                        </div>
                    </div>

                    <div class="hoa-mermaid-raw-code hidden pt-3 border-t border-white/10">
                        <pre class="!bg-slate-900/90 !border-white/10 text-xs font-mono text-slate-300 p-4 rounded-2xl overflow-x-auto"><code>${escapeHtml(rawCode)}</code></pre>
                    </div>
                `;

                const viewport = card.querySelector('.hoa-mermaid-svg-viewport');
                const inner = card.querySelector('.hoa-mermaid-canvas-inner');
                const zoomLabel = card.querySelector('.hoa-mermaid-zoom-label');
                const zoomInBtn = card.querySelector('.hoa-mermaid-zoom-in-btn');
                const zoomOutBtn = card.querySelector('.hoa-mermaid-zoom-out-btn');
                const zoomResetBtn = card.querySelector('.hoa-mermaid-zoom-reset-btn');
                const fitBtn = card.querySelector('.hoa-mermaid-fit-btn');
                const quickZoomIn = card.querySelector('.hoa-quick-zoom-in');
                const quickZoomOut = card.querySelector('.hoa-quick-zoom-out');
                const quickReset = card.querySelector('.hoa-quick-reset');

                let scale = 1.0;
                let panX = 0;
                let panY = 0;
                let isPanning = false;
                let startX = 0;
                let startY = 0;
                const minScale = 0.35;
                const maxScale = 4.0;

                function updateTransform(animate = false) {
                    inner.style.transition = animate ? 'transform 0.2s cubic-bezier(0.16, 1, 0.3, 1)' : 'none';
                    inner.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
                    const pct = Math.round(scale * 100) + '%';
                    if (zoomLabel) zoomLabel.textContent = pct;
                }

                function zoom(delta, animate = true) {
                    scale = Math.min(maxScale, Math.max(minScale, Number((scale + delta).toFixed(2))));
                    updateTransform(animate);
                }

                function reset(animate = true) {
                    scale = 1.0;
                    panX = 0;
                    panY = 0;
                    updateTransform(animate);
                }

                // Button Clicks
                zoomInBtn.onclick = (e) => { e.stopPropagation(); zoom(0.25, true); };
                zoomOutBtn.onclick = (e) => { e.stopPropagation(); zoom(-0.25, true); };
                zoomResetBtn.onclick = (e) => { e.stopPropagation(); reset(true); };
                fitBtn.onclick = (e) => { e.stopPropagation(); reset(true); };
                quickZoomIn.onclick = (e) => { e.stopPropagation(); zoom(0.25, true); };
                quickZoomOut.onclick = (e) => { e.stopPropagation(); zoom(-0.25, true); };
                quickReset.onclick = (e) => { e.stopPropagation(); reset(true); };

                // Mouse Drag to Pan
                viewport.addEventListener('mousedown', (e) => {
                    if (e.target.closest('button')) return;
                    isPanning = true;
                    startX = e.clientX - panX;
                    startY = e.clientY - panY;
                    viewport.style.cursor = 'grabbing';
                });

                window.addEventListener('mousemove', (e) => {
                    if (!isPanning) return;
                    panX = e.clientX - startX;
                    panY = e.clientY - startY;
                    updateTransform(false);
                });

                window.addEventListener('mouseup', () => {
                    if (isPanning) {
                        isPanning = false;
                        viewport.style.cursor = 'grab';
                    }
                });

                // Mouse Wheel Zoom
                viewport.addEventListener('wheel', (e) => {
                    e.preventDefault();
                    const delta = e.deltaY > 0 ? -0.15 : 0.15;
                    const oldScale = scale;
                    scale = Math.min(maxScale, Math.max(minScale, Number((scale + delta).toFixed(2))));
                    
                    const rect = viewport.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left - rect.width / 2;
                    const mouseY = e.clientY - rect.top - rect.height / 2;
                    panX -= mouseX * (scale / oldScale - 1);
                    panY -= mouseY * (scale / oldScale - 1);

                    updateTransform(false);
                }, { passive: false });

                // Double Click to Toggle Detail Zoom
                viewport.addEventListener('dblclick', (e) => {
                    if (e.target.closest('button')) return;
                    if (Math.abs(scale - 1.0) < 0.1) {
                        scale = 1.6;
                    } else {
                        scale = 1.0;
                        panX = 0;
                        panY = 0;
                    }
                    updateTransform(true);
                });

                // Touch Gestures for Mobile Pan & Pinch
                let touchStartX = 0;
                let touchStartY = 0;
                let initialPinchDist = 0;
                let initialScale = 1.0;

                viewport.addEventListener('touchstart', (e) => {
                    if (e.touches.length === 1) {
                        isPanning = true;
                        touchStartX = e.touches[0].clientX - panX;
                        touchStartY = e.touches[0].clientY - panY;
                    } else if (e.touches.length === 2) {
                        isPanning = false;
                        initialPinchDist = Math.hypot(
                            e.touches[0].clientX - e.touches[1].clientX,
                            e.touches[0].clientY - e.touches[1].clientY
                        );
                        initialScale = scale;
                    }
                }, { passive: true });

                viewport.addEventListener('touchmove', (e) => {
                    if (e.touches.length === 1 && isPanning) {
                        panX = e.touches[0].clientX - touchStartX;
                        panY = e.touches[0].clientY - touchStartY;
                        updateTransform(false);
                    } else if (e.touches.length === 2 && initialPinchDist > 0) {
                        const dist = Math.hypot(
                            e.touches[0].clientX - e.touches[1].clientX,
                            e.touches[0].clientY - e.touches[1].clientY
                        );
                        scale = Math.min(maxScale, Math.max(minScale, Number((initialScale * (dist / initialPinchDist)).toFixed(2))));
                        updateTransform(false);
                    }
                }, { passive: true });

                viewport.addEventListener('touchend', () => {
                    isPanning = false;
                    initialPinchDist = 0;
                }, { passive: true });

                // Source Drawer Toggle
                const toggleBtn = card.querySelector('.hoa-mermaid-toggle-btn');
                const rawDrawer = card.querySelector('.hoa-mermaid-raw-code');
                toggleBtn.onclick = () => {
                    const isHidden = rawDrawer.classList.contains('hidden');
                    rawDrawer.classList.toggle('hidden');
                    toggleBtn.innerHTML = isHidden ? '<span>📊</span> <span>Diagram</span>' : '<span>💻</span> <span>Source</span>';
                };

                // Source Copy Button
                const copyBtn = card.querySelector('.hoa-mermaid-copy-btn');
                copyBtn.onclick = () => {
                    navigator.clipboard.writeText(rawCode);
                    copyBtn.innerHTML = '<span class="text-emerald-400">✓</span> <span class="text-emerald-300 font-bold">Copied!</span>';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<span>📋</span> <span>Copy</span>';
                    }, 2000);
                };

                pre.replaceWith(card);
            } catch (renderErr) {
                console.warn('HOA Studio: Mermaid render error on diagram, displaying fallback:', renderErr);
            }
        }
    } catch (e) {
        console.warn('HOA Studio: Could not load local mermaid library:', e);
    }
}

export function enhanceAsciiArchitecture(container) {
    const preBlocks = container.querySelectorAll('pre');

    preBlocks.forEach(pre => {
        if (pre.closest('.hoa-ascii-terminal-wrapper') || pre.closest('.hoa-mermaid-container')) return;

        const text = pre.textContent;
        const isAsciiBox = /[┌─┐│└┘├┤┬┴┼▼▲]/.test(text);

        if (isAsciiBox) {
            const rawCode = text;
            const terminal = document.createElement('div');
            terminal.className = 'hoa-ascii-terminal-wrapper my-8 rounded-3xl border border-cyan-500/25 bg-slate-950/95 shadow-2xl overflow-hidden backdrop-blur-xl';
            terminal.innerHTML = `
                <div class="flex items-center justify-between px-4 sm:px-5 py-2.5 bg-slate-900/90 border-b border-white/10">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80 inline-block"></span>
                        </div>
                        <span class="text-[11px] font-mono text-cyan-300 font-semibold pl-2 tracking-wider uppercase">System Architecture Flow</span>
                    </div>
                    <button type="button" class="hoa-ascii-copy-btn px-2.5 py-1 rounded-xl glass-subtle hover:border-cyan-500/40 text-slate-300 hover:text-cyan-300 text-[11px] font-mono transition-all flex items-center gap-1 cursor-pointer select-none">
                        <span>📋</span> <span>Copy Flow</span>
                    </button>
                </div>
                <div class="hoa-ascii-viewport p-4 sm:p-6 overflow-x-auto hoa-custom-scrollbar">
                    <pre class="!bg-transparent !p-0 !m-0 !border-none !shadow-none text-xs sm:text-sm font-mono text-cyan-100/90" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important; font-variant-ligatures: none !important; letter-spacing: 0 !important; line-height: 1.25 !important; white-space: pre !important;"><code>${escapeHtml(rawCode)}</code></pre>
                </div>
            `;

            const copyBtn = terminal.querySelector('.hoa-ascii-copy-btn');
            copyBtn.onclick = () => {
                navigator.clipboard.writeText(rawCode);
                copyBtn.innerHTML = '<span class="text-emerald-400">✓</span> <span class="text-emerald-300 font-bold">Copied!</span>';
                setTimeout(() => {
                    copyBtn.innerHTML = '<span>📋</span> <span>Copy Flow</span>';
                }, 2000);
            };

            pre.replaceWith(terminal);
        }
    });
}

export function enhancePermissionMatrices(container) {
    const tables = container.querySelectorAll('table');

    tables.forEach(table => {
        if (table.parentElement?.classList.contains('hoa-matrix-table-viewport')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'hoa-matrix-table-wrapper my-8 rounded-3xl border border-white/10 bg-slate-950/70 shadow-2xl overflow-hidden backdrop-blur-xl';
        
        const viewport = document.createElement('div');
        viewport.className = 'hoa-matrix-table-viewport overflow-x-auto hoa-custom-scrollbar';

        table.parentNode.insertBefore(wrapper, table);
        viewport.appendChild(table);
        wrapper.appendChild(viewport);

        const cells = table.querySelectorAll('td');
        cells.forEach(cell => {
            const raw = cell.textContent.trim();
            if (raw === '✓' || raw === '✔') {
                cell.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30 text-xs shadow-[0_0_12px_rgba(16,185,129,0.15)]">✓</span>';
            } else if (raw === '✕' || raw === '✖' || raw === '✗' || raw === 'X') {
                cell.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-800/80 text-slate-500 border border-white/5 text-xs font-semibold">✕</span>';
            } else if (raw.startsWith('⚡')) {
                cell.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-amber-500/15 text-amber-300 font-bold border border-amber-500/30 text-xs shadow-[0_0_12px_rgba(245,158,11,0.15)]">${escapeHtml(raw)}</span>`;
            }
        });
    });
}

export async function initBlogVisualEnhancer() {
    const article = document.querySelector('.hoa-article-content');
    if (!article) return;

    // 1. Unpack any embedded markdown sections
    unpackEmbeddedMarkdownBlocks(article);

    // 2. Format tables with status badges
    enhancePermissionMatrices(article);

    // 3. Transform ASCII art into Cyberpunk terminals
    enhanceAsciiArchitecture(article);

    // 4. Transform Mermaid code into Interactive SVGs
    await enhanceMermaidDiagrams(article);

    // 5. Notify listeners that blog visuals and headings were fully unpacked
    window.dispatchEvent(new CustomEvent('hoa:blog-content-enhanced'));
}

if (typeof window !== 'undefined') {
    window.initBlogVisualEnhancer = initBlogVisualEnhancer;
}
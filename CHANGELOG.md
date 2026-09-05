# 📜 Changelog

All notable changes to **HelpOfAi Studio (HOA-Studio)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.7.5] - 2026-09-05

### Fixed
- **OmniRoute Telemetry & Settings Alpine Scope Stability:**
  - Resolved `ReferenceError: activePoint is not defined` during periodic Livewire DOM morphing cycles by migrating telemetry cursor tracking and floating HUD expressions to `$data.activePoint`.
  - Resolved `ReferenceError: showKey is not defined` on `/dashboard/ai-models/omniroute` by declaring reactive key state on root components and evaluating `$data.showKey ?? false`.
  - Added deterministic `wire:key` DOM anchoring across the SVG canvas container, user API key forms, and password input containers.
- **Framework View Cache Integrity:**
  - Reinstalled and sanitized vendor framework blade views, ensuring 100% clean compilation for all exception handling, pagination, and mailer templates.

### Added
- **Multi-Format Document Export Engine:**
  - Added native TipTap JSON AST document export (`application/json`) with indented formatting.
  - Added direct MS Word `.docx` file export with HTML document envelopes.
  - Integrated zero-dialog browser print/PDF preview formatting (`window.print()`).
  - Added 1-click Rich Text & HTML clipboard copying with visual confirmation toasts.

## [2.7.4] - 2026-09-05

### Added
- **Floating Table Operations Toolbar (`hoa-table-floating-bar`):**
  - Added an intelligent, glassmorphic floating toolbar that docks directly above active tables whenever the cursor is inside any table cell.
  - Quick 1-click actions: Insert Row Above (↑), Insert Row Below (↓), Delete Row (✕), Insert Column Left (←), Insert Column Right (→), Delete Column (✕), Toggle Header Row (🔲), Merge/Split Selected Cells (🔗), and Delete Entire Table (🗑️).
  - Built-in canvas boundary protection and sticky positioning when scrolling long tables.
- **Context Menu Table Controls Section:**
  - Dynamic table grid controls in the custom right-click context menu, detecting when the user right-clicks inside any `table`, `th`, or `td` element.
- **Complete TipTap 3 Table Methods Suite:**
  - Expanded `TiptapDriver` with `toggleHeaderRow()`, `toggleHeaderColumn()`, `toggleHeaderCell()`, `mergeCells()`, `splitCell()`, `mergeOrSplit()`, `fixTables()`, `goToNextCell()`, and `goToPreviousCell()`.
  - Added custom CSS styles for multi-cell selections (`.selectedCell`) with glowing indigo border and high-contrast column resize handles (`.column-resize-handle`).
- **Real-Time Code Syntax Highlighting Suite (`CodeBlockLowlight`):**
  - Integrated `@tiptap/extension-code-block-lowlight` powered by `lowlight` (highlight.js v11) covering 35+ common languages (JavaScript, TypeScript, Python, PHP, HTML, CSS, SQL, JSON, Bash, Markdown, Go, Rust, Java, C++, C#, etc.).
  - Added custom `CustomCodeBlockLowlight` NodeView featuring:
    - Sleek macOS terminal header with colored dots (`● ● ●`).
    - Interactive language selector dropdown with live syntax switching.
    - 1-Click "Copy Code" button with instant visual `"Copied! ✓"` feedback.
    - In-block <kbd>Tab</kbd> indentation (`enableTabIndentation: true`) and effortless exit navigation.
  - High-fidelity dark IDE theme CSS tokens (`hljs-keyword`, `hljs-string`, `hljs-title`, `hljs-number`, `hljs-comment`, `hljs-built_in`, `hljs-function`).

## [2.7.3] - 2026-09-04

### Added
- **Dynamic AI Reasoning & Writing Intelligence Suite:**
  - Upgraded surgical paragraph transformation engine across all 7 right-click writing intelligence actions:
    - *Recreate Paragraph* (`recreate`): Completely re-architects sentence structures and syntax from scratch using domain authority and high-engagement rhetoric.
    - *Rewrite & Polish* (`rewrite`): Substantive qualitative enhancement eliminating passive voice, weak verbs, and wordy filler phrases.
    - *Expand with Depth* (`expand`): Adds deep analytical rigor, tactical implications, and concrete real-world rationale (1.5x–2.2x depth).
    - *Shorten & Condense* (`shorten`): Distills selected text into its punchy essence in 40%–60% of original word count.
    - *Simplify (8th-Grade)* (`simplify`): Replaces polysyllabic academic abstractions with crisp, effortless plain English (Hemingway style).
    - *Generate FAQ Block* (`generate_faq`): Formulates 2–3 high-intent search questions with authoritative answers and bold key entities.
    - *SEO Optimize Text* (`seo_optimize`): Naturally front-loads primary entities and weaves focus keywords for Google AI Overviews and GEO readiness.
- **Deep Full-Document Narrative Comprehension:**
  - `ContentWriterBrain::buildSurgicalPrompt` now synthesizes the entire document thesis, working title, primary focus keyword, narrative placement role (Opening hook, Core body analysis, Concluding synthesis), and surrounding inflow/outflow context.
  - Automatically derives preceding and following text from the document even if the client cursor collapsed.
- **Zero-Token Local Algorithmic Writing Engine (Offline / Quota Fallback):**
  - Added pure-PHP and client-side JavaScript deterministic transformation algorithms (`executeLocalActionTransform` and `applyLocalParagraphAction`) for all 7 actions.
  - Activates seamlessly if external AI models are unavailable, network is disconnected, or monthly word quotas are exhausted.
- **Strict Anti-Echo Guarantee & Calibrated Temperatures:**
  - Added explicit anti-echo mandates in both system and user prompts to forbid returning unchanged text.
  - Dynamic temperature tuning per action (`recreate`: 0.82, `rewrite`: 0.78, `expand`: 0.75, `shorten`: 0.55, `simplify`: 0.60, `generate_faq`: 0.70, `seo_optimize`: 0.65).
  - Controller safety net automatically intercepts and transforms any identical text echoes before completing stream.
- **Interactive In-Canvas Sub-Agent Proposal Card:**
  - Locked ProseMirror selection range on right-click to eliminate selection loss.
  - Dynamic sub-agent proposal inspector showing exact active mode badge, token velocity, and model badge.

## [2.7.2] - 2026-09-04

### Added
- **Google AI Overviews & Generative Engine Optimization (GEO) Pillar:**
  - Added dedicated 7th SEO pillar evaluating direct definition snippets (40-60 words under H2), verifiable data point density, comparison table presence, and People Also Ask (PAA) query coverage.
  - Integrated in-canvas visual callouts (`#seo-loc-geo_direct_answer`, `#seo-loc-geo_structured_synthesis`) and canvas locator.
- **1-Click Magic SEO & GEO Auto-Healer:**
  - Master action in SEO tab dynamically aggregating all detected failing checks into a unified editorial directive, performing a single-pass holistic optimization without disjointed piecemeal rewriting.
- **Dynamic Schema.org JSON-LD Studio & Validator:**
  - Added `SchemaGenerator` service generating verified `BlogPosting`, `FAQPage`, and `HowTo` structured data.
  - Interactive Schema Studio in SEO tab with live syntax-highlighted JSON-LD, 1-click clipboard copy, and direct document injection.
- **Semantic NLP Entity Density Matrix:**
  - Real-time SurferSEO/Clearscope-style topical entity extraction with bi-gram and tri-gram analysis.
  - Interactive density chips with color-coded usage status (Underused, Optimal, Overused).
- **Multi-Platform SERP & Social Simulator:**
  - Interactive live preview in Titles & Meta tab supporting Google Desktop, Google Mobile, Google AI Overviews (Gemini), X/Twitter Summary Cards, and LinkedIn/Facebook OpenGraph cards.

- **Comprehensive Content Intelligence Sidebar Overhaul:**
  - **10-Point E-E-A-T Quality Audit:** Empirical evaluation across all 10 true E-E-A-T & GEO dimensions (search intent, topic coverage, original value, readability, SEO structure, internal linking, outbound citations, E-E-A-T signals, GEO readiness, technical SEO) with diagnostic progress bars, granular feedback, and 1-Click Master E-E-A-T Auto-Healer.
  - **Titles & Meta Descriptions:** Added real-time character counters (50-65 chars title, 120-160 chars meta), full meta description persistence in `SeoAnalysis::metrics['meta_description']` and seamless integration with `SeoAnalyzer`.
  - **AI Content Ideas & Gaps:** Added 1-click `⚡ AI Draft Section` to automatically draft missing gap sections into the editor canvas, plus structured comparison table generator.
  - **Keywords Density Matrix:** Real-time primary keyword density percentage and occurrence tracker with dynamic color tiers (0.8%–2.5% optimal), plus real-time secondary keyword usage tracking.
  - **Outline & Versions:** Added instant `↻ Sync` on tab activation and hardened diff inspection across all editor drivers.

- **Dual-Engine Architecture (With AI & Local Algorithmic Fallback):**
  - All Content Intelligence features (Viral Titles, Meta Descriptions, LSI Semantic Keywords, FAQ Generation, Content Gaps, Quick Answers, and Auto-Healing) now operate on a dual-engine architecture:
    - **With AI**: Leverages OmniRoute LLM streaming, deep semantic reasoning, and dynamic context synthesis.
    - **Without AI / Local Algorithmic Engine**: Executes deterministic statistical NLP, n-gram extraction, linguistic formulaic headline models, and DOM AST structural healers.
    - If the AI model is unavailable, offline, or monthly word quota is reached, features automatically and seamlessly execute native local algorithms with zero tokens and zero downtime.

### Fixed
- **SEO Heatmap Toggle & Editor Canvas Read-Only State:**
  - Resolved `Uncaught TypeError: ed.setEditable is not a function` by adding `setEditable` and `isEditable` methods to `TiptapDriver`.
  - Added unified `toggleSeoHeatmap(forceState)` method in Alpine editor state to safely stash live drafts, toggle read-only inspection mode, and restore editable content cleanly.

---

## [2.7.1] - 2026-09-04

### Added
- **In-Canvas SEO Recommendation & Color-Coded Heatmap System:**
  - Implemented real-time in-canvas visual callouts and annotations directly inside the editor canvas for missing SEO requirements.
  - Added 4-tier visual color system: 🔴 Critical Issues (Missing intro focus keyword, keyword stuffing, run-on sentences), 🟡 Warnings & Structure (Missing subheading keyword, bulky paragraphs, missing outbound citations), 🔵 Authority & E-E-A-T (Citations, clinical/research references, trust terms), 🟢 Focus Keyword Optimization.
  - Added floating in-canvas inspection legend bar and interactive `🎯 Locate in Content` buttons in the SEO tab to smoothly scroll and highlight targeted lines.
  - Enriched 6-pillar SEO checks with actionable recommendation boxes, current vs. goal metrics, and severity indicators.

---

## [2.7.0] - 2026-09-04

### Added
- **Multi-Agent Pipeline Coordinator:** Backend orchestrator mapping 15 frontend pipeline checkboxes to true multi-LLM generation cycles.
- **Dynamic SSE Status Frames:** SSE stream multiplexes AI tokens with status_message streams for real-time UI updates.
- **RAG Vector Injection:** Agentic loop invokes RetrieveRagContext to synthesize brand entities during generation.

### Changed
- Refactored Amber Swarm Button to bypass legacy JS fetch loops in favor of backend SSE pipeline.
- Updated ContentWriterBrain to interpret multi-phase pipeline streams as native article generators.

### Fixed
- SSE parsing logic in TipTap no longer drops ghost tokens.
- Resolved Livewire 500 errors from stale view caches and unclosed Blade directives.
- LLM JSON markdown format extraction no longer causes Architect failures.

## [2.6.7] - 2026-09-03

### 🐛 Fixed
- **Localhost Hybrid Routing & Telemetry Stability Fix**:
  - Fixed regression in `AiStreamController::streamTransform` and `preparePrompt` where `$hasSelection` was undefined, causing HTTP 500 (`Server error while generating transformation.`).
  - Fixed telemetry polling loop in `AdminOmniRouteSetupPage::pingGatewayHealth` where server-side sockets attempted to connect to the cloud server's local loopback (`127.0.0.1:20128`) instead of recognizing client-side daemons on remote installations (`studio.helpofai.com`).
  - Enhanced client browser bridge in `omniroute.blade.php` with periodic 8-second client health pulsing across `http://127.0.0.1:20128/v1/models` and `http://localhost:20128/v1/models`, preventing the gateway connection status from flipping between `LIVE` and `STANDBY / OFFLINE`.

---

## [2.6.6] - 2026-09-03

### 🐛 Fixed
- **Multi-Agent Swarm Double Article Bug in Editor**:
  - Resolved duplicate article synthesis triggered by the Multi-Agent Swarm button (`full-content-main-agent`).
  - Fixed regression where requests without active selection defaulted to full 15-stage pipeline article prompts regardless of action type.
  - Implemented `ContentWriterBrain::isFullArticleType()` across `TransformText`, `AiStreamController::streamTransform`, and `AiStreamController::preparePrompt` to correctly separate full-document generation from surgical components (`comparison_table`, `seo_fix_title`, `seo_fix_meta`, `quick_answer`, etc.).
  - Updated Swarm Step 4 (`rich_media`) to inspect existing document tables prior to requesting secondary tables, preventing redundant table insertion.
  - Added robust `isContentEmpty()` helper recognizing whitespace and all default placeholder variations (`Start writing your AI-powered content...`, `Start building your block content...`), ensuring `multi_agent_swarm` cleanly overwrites placeholder text without concatenation.
  - Injected missing `transformRoute` parameter into `MainEditor.blade.php` component initialization.

---

## [2.6.5] - 2026-09-02

### 🚀 Added & Improved
- **WordPress Plugin Integration Rewrite (HOA Studio AI Bridge)**:
  - Advanced Enterprise Glassmorphic Dashboard matching the Laravel backend UI using `bg-slate-950` design tokens.
  - Multi-page system with nested sidebar menus (`Dashboard`, `Connection`, `AI Settings`, `Editor Control`).
  - Added new `hoa_studio_brand_voice` global prompt injection settings natively configurable in WordPress.
  - Refactored `hoa-studio-wordpress.php` from a 340-line monolithic script into a clean, 4-module object-oriented architecture (`/includes/admin`, `/includes/api`, `/includes/gutenberg`).

### 🛡️ Cleaned & Hardened
- **AI Gateway & SSE Streaming Latency Fix**:
  - Eliminated delayed token chunking on `AiStreamController` and WP Plugin `ajax_stream_proxy` with strict `@ob_implicit_flush(true)` execution flow, restoring sub-50ms Time-To-First-Token capability over Nginx/Apache.
- **Automated Memory Protection**:
  - Implemented `php artisan hoa:prune-telemetry` to batch-delete decaying telemetry and security graph data older than 30 days without locking database tables.

---

## [2.6.4] - 2026-08-31

### 🚀 Added & Improved
- **High-Converting Welcome Page & Real Editor Simulation Suite**:
  - Rebuilt landing page with crystal-clear copy, SEO headlines, dynamic system version badge, and structured JSON-LD `FAQPage` schema.
  - Upgraded 3-column interactive studio simulation matching full Master Editor capabilities (8-engine switcher, OmniRoute AI live streaming, floating AI prompt bar, and 7-tab Content Intelligence suite).
  - Created standalone [`resources/css/welcome.css`](file:///C:/Users/rajib/Desktop/HOA-Studio/resources/css/welcome.css) with rich 3D ambient glassmorphic shadows (`.hoa-editor-shadow`), glowing hover physics (`.hoa-card-glow-shadow`), and mobile drawer scrollbars.
  - Implemented responsive right-edge slide-over mobile drawer with body teleportation and touch backdrop overlay.

---

## [2.6.3] - 2026-08-30

### 🚀 Added & Improved
- **Fast SPA Page Transitions & Request Storm Elimination**:
  - Replaced speculative `wire:navigate.hover` prefetching across user and admin sidebars with clean `wire:navigate`, preventing request queue starvation and server bottlenecks during navigation.
- **Glassmorphic Skeleton & Deferred Loading Architecture**:
  - Implemented Livewire 3 deferred loading (`wire:init="loadDashboard"`) with zero-shift glowing glass skeletons across User Dashboard and Admin Overview.
  - Hardened `<x-omniroute.telemetry-graph>` with defensive array initialization and seamless fallback telemetry streams.

---

## [2.6.2] - 2026-08-30

### 🚀 Added & Improved
- **Direct GitHub Repository & Version Sync Engine**:
  - Direct live inspection of `version.json`, build number, latest commit SHA, commit author, and release date from GitHub `main` branch with automated cache-busting (`?t=` timestamp) and zero rate-limit raw streaming.
  - Dedicated **GitHub Live Sync** matrix in Admin Updates control panel showing real-time repository connection status, target branch, and latest commit ID.
- **Explicit Connection & Diagnostics Handling**:
  - Replaced silent fallbacks with transparent connection diagnostic messages when remote hosts or DNS/cURL queries are blocked on shared hosting.

### 🛡️ Cleaned & Hardened
- **Clean Feature Decoupling & Modular Hygiene**:
  - Fully removed experimental image editing modules, obsolete stylesheets, unused schema migrations, and unneeded dependencies (`fabric`) to keep application bundle lean and production-focused.
  - Rebuilt production assets with Vite with zero warnings or dangling references.

---

## [2.6.1] - 2026-08-29

### 🚀 Added & Improved
- **Localized AI Sub-Agent Paragraph Recreation Engine (`sub-content-sub-agent`)**:
  - Contextual right-click & floating menu actions for rewriting, polishing, and recreating individual paragraphs.
  - Automatic paragraph detection at cursor position when no manual range is selected.
  - Capped token usage (150–450 tokens) and isolated synthesis pipeline preventing full document regeneration on localized edits.
- **Enhanced Glassmorphic High-Contrast Visual Feedback**:
  - High-contrast translucent amber highlight (`rgba(234, 179, 8, 0.22)`) with drop shadow for selected paragraphs, ensuring crisp text legibility in dark themes.
  - Glowing emerald green confirmation badge (`.ai-replaced-green-highlight`) with dynamic 5-second auto-fade transition and automated markup sanitization upon acceptance.
- **ProseMirror Atomic Transaction Replacements**:
  - Upgraded TipTap driver to execute `.setTextSelection({ from, to }).deleteSelection().insertContent()` with multi-tiered HTML normalization fallbacks.
  - Preserved canvas `scrollTop` across content updates via `requestAnimationFrame`.

### 🛡️ Fixed & Hardened
- **Livewire 3 Single-Root & DOM Isolation**:
  - Enforced single root element across Blade views and wrapped editor canvas and scripts under `wire:ignore` to prevent state resets.
  - Paused background autosaves during active AI proposal reviews to avoid premature re-renders or scroll jumps.
- **Floating Bubble & Context Menu Dismissal**:
  - Upgraded TipTap bubble selection toolbar to fixed viewport coordinates with boundary protection.
  - Isolated right-click context menu by explicit ID (`hoa-editor-context-menu`) and added global click/escape listeners for instant auto-dismissal.
  - Fixed variable scope `ReferenceError` in AI transform stream handler and restored `Placeholder` extension in TipTap driver.

---

## [2.6.0] - 2026-08-26

### 🚀 Added
- **HOA Studio Enterprise WordPress Plugin & Bridge API Suite (`/api/v1/wordpress/*`)**:
  - **Standalone WordPress Plugin (`public/plugins/hoa-studio-wordpress/` & `public/downloads/hoa-studio-wordpress.zip`)**:
    - **TipTap Fullscreen AI Editor Canvas**: Brings HOA Studio's complete TipTap ProseMirror engine, floating selection formatting toolbar, and inline AI prompt bar (`Ctrl+K` / `/`) directly into WordPress Post & Page edit screens.
    - **Gutenberg Custom AI Block Extension (`hoa-studio/ai-content-generator`)**: Injects live-streaming AI generation blocks natively into Gutenberg.
    - **Bidirectional Document & Post Sync (`/api/v1/wordpress/sync-document`)**: Sync articles between HOA Studio and WordPress drafts.
  - **Scoped User Studio Connect Keys (`hoa_live_...`)**:
    - Each user generates their own unique `SHA-256` connect tokens in User Settings &rarr; WordPress Connect Keys.
    - Authenticated via `AuthenticateStudioToken` middleware with automatic monthly word quota deduction and rate limiting (never exposes backend OmniRoute credentials).
- **Multi-Server & Sub-Directory Domain Support (`helpofai.com/studio`)**:
  - Full compatibility across **Linux**, **Apache**, **Nginx**, **LiteSpeed / OpenLiteSpeed**, and **cPanel Shared Hosting**.
  - Dynamic relative `.htaccess` routing for both root domains and subfolder paths (e.g. `public_html/studio/`).
  - Automatic `forceRootUrl` and `forceScheme` HTTPS enforcement in `AppServiceProvider`.
  - Configurable `ASSET_URL` & `update_route` support in `config/livewire.php` preventing Livewire SPA asset breakage in subdirectories.
  - LiteSpeed and Apache SSE AI streaming buffer bypass (`CacheLookup off` & `proxy_buffering off`).
- **Unified User Settings & Account Controls Suite (`/dashboard/settings`)**:
  - Built a 5-tab responsive glassmorphic settings dashboard:
    1. **Profile & Security**: Live display name, official workspace email editing, and password update.
    2. **AI Tokens & Word Quota**: Real-time word balance meter, total tokens processed, direct provider cost calculations, and per-model consumption breakdown.
    3. **My Content & Documents**: Paginated document portfolio, word count metrics, project associations, search & status filters, and instant trash/edit actions.
    4. **BYOK Custom API Keys**: Register personal OpenAI, DeepSeek, Anthropic, or local Ollama endpoints encrypted at rest with **AES-256-GCM**, unlocking unlimited rate limits.
    5. **Studio Preferences**: Configurable default AI generation models, vector embedding RAG cache duration, default editor canvas engine (TipTap, Gutenberg, Notion, Markdown), and notification options.
- **Sidebar & Top Header Navigation Polish**:
  - Added dedicated **Settings & Controls** sidebar navigation link and integrated the avatar pill across all workspace layouts with dynamic tab routing (`wire:navigate`).

---

## [2.5.2] - 2026-08-26

### 🚀 Added & Architectural Improvements
- **Thin Master Scripts Engine Refactor (`resources/views/editor/partial/`)**:
  - Refactored monolithic `scripts.blade.php` into a thin master orchestrator delegating to 5 feature-oriented sub-script blades:
    - `scripts-core.blade.php`: Lifecycle, model fetching, autosave engine, and draft disaster recovery.
    - `scripts-canvas.blade.php`: ProseMirror / TipTap editor instance, formatting status, context menus, and outline navigation.
    - `scripts-ai.blade.php`: Server-Sent Events (SSE) AI streaming, multi-agent swarm, and targeted SEO fixes.
    - `scripts-diff.blade.php`: Visual LCS diff review, candidate variations, ghost completion, and metrics calculation.
    - `scripts-telemetry.blade.php`: Floating bubble dragging, system logging, and memory buffer.

### 🛠️ Fixed & Polished
- **AI Content Direct Persistence & Anti-Cut Protection**:
  - Eliminated unwanted modal/diff review popups during full-document generation by adding strict active DOM selection validation.
  - Streamed AI content now always commits directly to the editor canvas with immediate autosave and disaster draft persistence.
- **Panel Visibility Persistence**:
  - User manual toggle choices for AI Command Center (`showLeftPanel`) and Content Intelligence (`showRightPanel`) are now persisted in browser `localStorage` and will not reset during AI transforms or Livewire renders.
- **Removal of Legacy Telemetry Modal**:
  - Cleaned up all remaining references to the floating telemetry terminal modal (`terminal-ui.blade.php` and `showTerminalModal`).

---

## [2.5.1] - 2026-08-25

### 🚀 Added
- **Turbo Hover Prefetching Engine (`wire:navigate.hover`)**:
  - Upgraded all navigation links across Workspace and Admin sidebars to instantly pre-fetch destination pages on 60ms cursor hover, eliminating perceived navigation delay.
- **Glassmorphic Multi-Variant Skeleton Loader Suite (`<x-glass.skeleton />`)**:
  - Built high-performance pulsating skeleton components for document cards, user lists, metrics, and data tables to eliminate Cumulative Layout Shift (CLS).
- **Animated Conic Gradient Brand Logo (`<x-glass.logo />`)**:
  - Created standalone CSS conic rotating border with dark glassmorphic inner box and shimmering typography (`HOA Studio` / `HOA Admin`).
- **Hardware-Accelerated Cursor Glow Spotlight**:
  - Implemented 60 FPS LERP trailing physics spotlight cursor across all dashboard viewports.

### 🛠️ Changed & Polished
- **Fixed-Coordinate Sidebar Icon Tracks**:
  - Re-architected sidebar navigation geometry with fixed `w-8 h-8` icon containers and unified `px-3 py-2.5` padding, completely eliminating icon wobble during rapid expand/collapse actions.
- **Top Navbar Header Collapse Trigger**:
  - Moved desktop sidebar toggle button outside sidebar container into the top navigation header (Linear / VS Code pattern) for unobstructed brand logo visibility.
- **Automated `.env` 3-Way Auto-Merge Engine**:
  - Upgraded `CoreUpdateService` with safe `.env.example` key synchronization that preserves user database secrets and API tokens.

---

## [2.5.0] - 2026-08-24

### 🚀 Added
- **Advanced Core Update & Self-Healing Rollback Engine**:
  - Dual-Engine Update Architecture supporting **Native Git** (VPS/CLI) and **Pure-PHP Zip Archive** (Shared Hosting / cPanel).
  - Pre-flight automated snapshots: creates immutable point-in-time codebase `.zip` backups and database `.sql` dumps in `storage/app/updates/backups/`.
  - Automated post-update synthetic diagnostics via `HealthProberService` (testing database tables, user model connectivity, write permissions, and Vite manifest).
  - Instant self-healing auto-rollback triggered automatically if any post-update health check fails or throws an exception.
- **Admin Time-Machine Rollback Control Center (`/admin/updates`)**:
  - Live version checker with GitHub release notes, 1-Click Update, manual snapshot creation, and 1-Click Rollback to any historical restore point.
- **Offline Disaster Recovery Script (`public/hoa-rescue.php`)**:
  - Standalone, zero-dependency recovery tool protected by `RESCUE_SECRET` enabling admins to force sites online, flush corrupted caches, or unpack restore points directly via native PHP.

---

## [2.4.0] - 2026-08-24

### 🚀 Added
- **Multi-Candidate AI Generation (Choice Variations)**:
  - Added support for generating, previewing, and toggling across variation candidates (`[ #1 ● ] [ #2 ] [ #3 ]`).
  - Added dynamic variation regeneration with persona style presets (*Professional, Casual, Persuasive, Academic*).
- **Granular Git-Style Word-by-Word Diffing**:
  - Built a 2D dynamic programming Longest Common Subsequence (LCS) diffing engine (`computeWordDiff`).
  - Highlights exact word-level deletions (`<del>` in rose) and additions (`<ins>` in emerald).
  - Added a **Split View (`◫ Split`)** vs **Unified View (`≡ Unified`)** switcher into the visual diff inspector.
- **Interactive Transform Modifiers & Sliders**:
  - Collapsible drawer controls for **Creativity Intensity** (`0.3` to `1.0`), **Tone Personas**, and **Length Targets** (*Shorter, Same, Longer*).
- **Live Before-vs-After SEO & Readability Delta Telemetry**:
  - Real-time 4-column metric preview bar measuring Word Count Delta, Flesch-Kincaid Readability Delta, Focus Keyword Frequency Delta, and Copywriting Power Verbs.
- **In-Canvas Inline Ghost Auto-Completion Mode**:
  - Copilot/Cursor-style typing pause debounce (`1.2s`) predicting the next 25–35 words from preceding document context.
  - Floating inline ghost container with `Tab` to accept and `Esc` to dismiss.
- **Snapshot Version Diff & Instant Time-Machine**:
  - Upgraded Version History (Tab 7) with side-by-side snapshot comparison (`🔍 Diff`) against the live editor canvas.
  - 1-click snapshot restoration rollback via Livewire.
- **Draggable Floating Selection Toolbar (`.editor-floating-actions`)**:
  - Added a dedicated drag handle indicator (`⋮⋮`) with mouse and touch coordinate tracking to position the floating toolbar anywhere on screen.

### 🛡️ Fixed & Hardened
- **Safe JSON Response Extraction**:
  - Hardened all AI transform and SEO audit fix requests with text-first decoding (`await resp.text()`) and safe `JSON.parse()` wrappers to prevent `Unexpected token '!'` HTML error crashes.
- **Route Fallbacks**:
  - Injected `transformRoute: '{{ route('ai.transform') }}'` into main editor initialization with automatic `/dashboard/api/ai/transform` fallback.
- **DOM Hierarchy Integrity**:
  - Fixed root canvas container markup and Blade comment tags in `canvas.blade.php`.

---

## [2.3.0] - 2026-08-22

### 🚀 Added
- **Multi-Driver Document Canvas**:
  - Support for switching active drivers between **TipTap ProseMirror**, **Notion Block Canvas**, **Gutenberg Block Canvas**, **Markdown Split Screen**, and **Raw HTML**.
- **OmniRoute Gateway v3.8.50 Live Telemetry**:
  - Added `ai-telemetry.log` floating terminal modal with level filtering (`AI`, `SEO`, `ERROR`, `SYSTEM`).
  - Live token streaming speedometer displaying real-time `tok/s` and received token counts.
- **Multi-Agent Copywriting Swarm**:
  - 5-step automated publishing pipeline: Researcher, Outliner Architect, Section Draftsman, Rich Media Engineer, and Rank Math Optimizer.

### 🛡️ Fixed
- Restructured `scripts.blade.php` to debounce autosaves (2000ms) and prevent duplicate write locks during active SSE streaming.

---

## [2.2.0] - 2026-08-20

### 🚀 Added
- **Rank Math 4-Pillar SEO Analyzer**:
  - Real-time scoring (0–100) across **Basic SEO**, **Additional SEO**, **Title Readability**, and **Content Readability**.
  - Integrated 1-click **⚡ AI Section Fix** buttons for each SEO audit check.
- **Google SERP Snippet Preview**:
  - Live desktop vs. mobile Google search result snippet simulator.
- **Local Draft Auto-Recovery**:
  - Ambient banner recovering unsaved browser edits from `localStorage` in case of accidental tab closures.

---

## [2.1.0] - 2026-08-18

### 🚀 Added
- **Vector Knowledge Base & RAG Pipeline**:
  - Multi-source ingestion for text, files, and web URLs.
  - Recursive 500-token chunker with 50-token semantic overlap.
  - SHA-256 cached vector embeddings with configurable TTL (**1 Day**, **7 Days**, **30 Days**).
  - Cosine similarity ranking engine injecting grounded passages into AI prompts.
- **Brand Voice Profiler**:
  - Custom brand voice personas with tone descriptors, audience targeting, and automated prompt constraints injection.

---

## [2.0.0] - 2026-08-15

### 🚀 Added
- **Full Architecture Rewrite**:
  - Upgraded to **Laravel 12.x**, **PHP 8.5.0**, **Livewire 3.x**, and **Tailwind CSS 4.0**.
  - Vite 8.x client bundler with sub-2s production asset compiles.
- **Cryptographic BYOK Security**:
  - AES-256-GCM encrypted user API keys for OpenAI, DeepSeek, Anthropic, and Groq.
  - Unlimited rate limits for BYOK endpoints vs. tiered plan throttling for shared admin gateway.
- **Multi-Format Binary Exporter**:
  - 1-click export to **Markdown (`.md`)**, **HTML (`.html`)**, **Plain Text (`.txt`)**, and **Word (`.docx`)** via WordprocessingML.
  - Print-ready PDF styling with `@media print`.
- **Password-Gated Public Sharing (`/share/{token}`)**:
  - AES-256 password gate, permission flags (`allow_download`, `allow_copy`), and expiration timers.

---

## [1.0.0] - 2026-08-01

### 🚀 Initial Release
- Initial core release of HelpOfAi Studio.
- Basic document management, project folders, user authentication, and initial TipTap WYSIWYG editor implementation.
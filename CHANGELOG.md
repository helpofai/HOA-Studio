# 📜 Changelog

All notable changes to **HelpOfAi Studio (HOA-Studio)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
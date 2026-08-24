# 📜 Changelog

All notable changes to **HelpOfAi Studio (HOA-Studio)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

# AGENTS.md

## Overview
**HOA-Studio** is an enterprise-grade AI content production workspace. It is a full-stack Laravel 13 application utilizing Livewire 3, Alpine.js, Tailwind CSS 4, and Vite, with a sophisticated AI infrastructure built around the OmniRoute gateway and TipTap editor.

## Build / Test / Lint
- **Install Dependencies**: `composer install && npm install`
- **Development**: `npm run dev`
- **Build**: `npm run build`
- **Tests**: `@php artisan test` (Uses PHPUnit 12)
- **Formatting**: `vendor/bin/pint`

---

## 🔍 MANDATORY PRE-IMPLEMENTATION & VERIFICATION PROTOCOL

Before creating, editing, refactoring, or deleting any files or features, AI agents **MUST ALWAYS** follow this 3-phase engineering cycle:

### Phase 1: 🔎 Global Codebase Discovery & Dependency Mapping (Before Coding)
1. **Understand Existing Implementations**: Thoroughly search and read all existing files related to the requested domain across the entire repository (`app/Features/`, `resources/views/`, `routes/`, `config/`, `resources/js/`).
2. **Trace Usages & Call-Sites**: Use grep/file search to find every place where the target function, class, model, route, Livewire property, or UI component is used to prevent breaking regressions.
3. **Validate Architectural Alignment**: Ensure new additions cleanly integrate with existing patterns (Livewire 3 reactivity, Alpine.js scoping, Tailwind CSS 4 design tokens, and modular feature separation).

### Phase 2: 🛠️ Careful Implementation & Non-Breaking Execution
1. Preserve existing functionalities, public API contracts, and unrelated docstrings/comments.
2. Follow strict modular directory structures (`app/Features/{FeatureName}/`) and semantic section class naming.

### Phase 3: 🧪 Integrity Verification & Transparent Change Reporting (After Coding)
1. **Run Verifications**:
   - Run tests: `@php artisan test` (Ensure 100% pass rate).
   - Verify build: `npm run build` (Ensure 0 compilation errors).
2. **Transparent Summary Output**:
   - Provide a clear, structured list of all files that were **Created**, **Modified**, or **Deleted** along with the rationale and purpose of each change.

---

## 🏛️ Modular Architecture & Feature-Driven Structure

All newly created files and directories **MUST ALWAYS** follow a modular, feature-oriented structure for clean discovery and long-term maintenance:

### 1. 📂 Feature-Wise Directory & File Naming:
- **Backend Modules**: Organize under `app/Features/{FeatureName}/` (e.g. `Admin`, `AI`, `Documents`, `Projects`, `Auth`, `SEO`, `Templates`).
  - `app/Features/{FeatureName}/Livewire/` (Interactive UI components)
  - `app/Features/{FeatureName}/Services/` (Business logic, third-party integrations)
  - `app/Features/{FeatureName}/Actions/` (Single-responsibility task executors)
  - `app/Features/{FeatureName}/Models/` (Eloquent entities)
- **Frontend Modules**: Organize under `resources/js/features/{feature-name}/` and `resources/views/{feature-name}/`.
- **Dedicated Styles**: If a feature requires specific styling (like Markdown rendering), extract it into a standalone stylesheet (e.g. `resources/css/markdown.css`) instead of bloating `app.css`.

### 2. 🏷️ Feature-Wise Container & Section Class Naming:
- Root and section wrappers must use semantic, feature-prefixed identifiers for easy DOM navigation, Alpine scope isolation, and CSS targeting:
  - Examples: `class="hoa-updater-terminal"`, `class="sidebar-nav-section"`, `class="editor-canvas-container"`, `class="system-info-matrix"`.
  - Avoid generic class names (e.g. `wrapper`, `box`, `content`).

### 3. ♻️ Lightweight & Reusable Design Philosophy:
- **Shared UI Elements**: Use reusable Blade components (`<x-glass.card>`, `<x-glass.button>`, `<x-glass.badge>`, `<x-glass.input>`) located in `resources/views/components/glass/`.
- **Zero Heavy Bloat**: Keep sub-components lightweight, reactive with Alpine.js (`@click`, `x-show`), and decoupled from heavy third-party vendor dependencies where native browser APIs or Tailwind utilities suffice.

---

## 🎨 Enterprise Design & Coding Conventions

### 1. 💎 Dark Glassmorphic Design Tokens & Corner Radii:
- **Theme Accents**: Use deep dark canvas backgrounds (`bg-slate-950`, `bg-slate-900/80`), subtle glowing borders (`border-white/10` or `border-violet-500/30`), and soft indigo/violet gradient buttons.
- **Corner Radii Consistency**: Adhere to the design system normalization:
  - Small badges / buttons: `rounded-lg` or `rounded-xl` (4px to 5px).
  - Cards & containers: `rounded-2xl` (6px to 9px).
  - Modals & dialogs: `rounded-3xl` with `backdrop-blur-xl`.

### 2. ⚡ Livewire 3 Single-Root Principle:
- **Single Root Element**: Every Livewire Blade component view (`resources/views/**/*.blade.php`) **MUST** be enclosed within a single top-level `<div>` container. Never return multiple root elements in Livewire views, as it causes DOM morphing bugs.

### 3. 📜 Proprietary Copyright File Header:
- Every newly created PHP and Blade file **MUST** contain the standard project copyright header:
```php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - {Feature/File Name}
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
```

---

## 🛡️ Safe Database Migrations & Schema Evolution Protocol

To prevent live website crashes and SQL errors during updates (`Table already exists` or `Column not found`):
1. **Defensive Table Creation**: Always check table existence before creating:
   ```php
   if (!Schema::hasTable('table_name')) {
       Schema::create('table_name', function (Blueprint $table) { ... });
   }
   ```
2. **Defensive Column Addition**: Always check column existence before altering:
   ```php
   if (!Schema::hasColumn('users', 'column_name')) {
       Schema::table('users', function (Blueprint $table) {
           $table->string('column_name')->nullable()->after('email');
       });
   }
   ```
3. **Non-Destructive Migrations**: Never drop active production columns or tables in a single migration without an explicit deprecation cycle.

---

## ⚡ AI Token Quotas & Streaming Protocol

1. **Mandatory Quota Enforcement**: Any action or controller generating AI text MUST invoke `$user->consumeQuota($words)` and verify `$user->hasQuota($words)`.
2. **Standardized SSE Streaming**: Server-Sent Events for AI generation must format events as `data: {"chunk": "...", "done": false}\n\n` for uniform TipTap and canvas consumption.
3. **Strict Circuit Breaker Timeouts**: External AI HTTP client calls must set a maximum timeout (e.g. `timeout(15)`) to prevent server process starvation on shared hosting.

---

## 🔒 Shared Hosting & cPanel Environment Guardrails

1. **Zero CLI Dependency Assumptions**: Never assume shell access or Git CLI is available. Always wrap `exec` in `function_exists('exec')` and provide Pure-PHP fallbacks (e.g., `ZipArchive`, native PDO).
2. **Execution & Memory Protection**: Chunk or stream heavy I/O operations to respect standard shared hosting constraints (`max_execution_time: 30s-60s`, `memory_limit: 128M-512M`).
3. **Robust Path Resolution**: Always use Laravel helper functions (`storage_path()`, `base_path()`, `public_path()`) instead of relative filesystem paths (`../`) to ensure cross-platform Windows and Linux/cPanel compatibility.

---

## 🧪 Regression Test & Role Authorization Matrix Policy

1. **New Feature Test Requirement**: Every new backend module or Livewire component created under `app/Features/{FeatureName}/` **MUST** have an accompanying feature test in `tests/Feature/{FeatureName}Test.php`.
2. **Dual-Role Authorization Verification**: Tests must explicitly verify:
   - **Admin Access**: Authenticated admin receives `200 OK`.
   - **Unauthorized / Regular User Access**: Non-admin user receives `403 Forbidden` or redirect.
3. **100% Pass Rate Standard**: The test suite (`php artisan test`) must pass with 0 failures before any feature is considered complete.

---

## 🚨 MANDATORY AI PRE-PUSH PROTOCOL (Release Checklist)

Before executing any `git commit` or `git push origin main`, AI agents **MUST ALWAYS** follow and execute this exact 7-step release protocol:

### 1. 🔢 Version Update (Sync All Version-Related Files)
Whenever features or fixes are completed, bump the version code and synchronize all version reference files across the codebase:
- **`version.json`**: Primary source of truth for the core updater (`version`, `version_code`, `build_number`, `release_date`, `schema_version`).
- **`composer.json`**: Ensure package metadata aligns.
- **`package.json`**: Match frontend release version.
- **`config/app.php` / `config/omniroute.php`**: Check version constants if applicable.

### 2. 📜 `CHANGELOG.md` Update
- Document every new feature, improvement, bug fix, or security patch under the new version header (e.g. `## [2.5.1] - YYYY-MM-DD`).
- Include sub-sections: `### Added`, `### Changed`, `### Fixed`, `### Security`.

### 3. 🔐 `.env.example` Update
- Whenever new environment variables or service keys are introduced (e.g. new AI provider keys, cache settings, rescue credentials), **immediately add them with clear comments into `.env.example`**.
- The `CoreUpdateService::syncEnvVariables()` engine relies on `.env.example` to automatically merge new keys into production `.env` files without overwriting user credentials.

### 4. 📖 `README.md` Update (If New Features Added)
- Update the system capabilities summary, architecture diagrams, screenshots/modules list, or feature matrix whenever domain capabilities change.

### 5. 📑 `DOCUMENTS.md` Update (If New Architecture/APIs)
- Maintain developer documentation, technical workflow notes, API endpoint specifications, and Livewire/Alpine component integration notes.

### 6. 🚀 `PRODUCTION-GUIDE.md` Update (If Deployment Changes)
- Document any new web server requirements, PHP extensions, cron jobs, background queue workers, or shared hosting guidelines.

### 7. 🧪 Test Suite & Production Build Verification
- **Run Full PHPUnit Suite**: `php artisan test` (Must achieve 100% pass rate).
- **Run Vite Production Build**: `npm run build` (Must exit cleanly with code 0).

---

## Key Files & Directories
- `version.json`: System version metadata spec.
- `app/Features/AI/`: Logic for AI routing, providers, and model management.
- `app/Features/Admin/`: Admin control center, updates engine, user management, and diagnostics.
- `app/Features/Documents/`: Editor implementation and document persistence.
- `resources/views/editor/`: UI components for the editor, including `MainEditor.blade.php`.
- `resources/views/admin/`: Admin control panels (`updates.blade.php`, `system-info.blade.php`, `users.blade.php`).
- `resources/css/markdown.css`: Standalone stylesheet for Enterprise Markdown rendering.
- `public/hoa-rescue.php`: Standalone zero-dependency offline emergency recovery tool.
- `config/omniroute.php`: Configuration for the AI multi-model gateway.
- `routes/web.php`: Web and API route definitions.

## Coding Conventions
- **Laravel 13 & Livewire 3**: Use Livewire components for dynamic UI updates.
- **Alpine.js**: Handle component-level frontend logic.
- **PSR-4 Autoloading**: Strictly adhered to.
- **Type Safety**: PHP 8.3+ features (strict typing where applicable).
- **Security**: Proprietary and confidential code; ensure all new files contain the project-standard file header.

## Git Workflow
- The project uses `main` as the primary branch.
- Follow conventional commit messages (`feat(...)`, `fix(...)`, `docs(...)`, etc.).

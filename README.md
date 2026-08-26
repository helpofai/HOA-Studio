<div align="center">

# ⚡ HelpOfAi Studio (HOA-Studio)
### Enterprise-Grade AI Copywriting, Knowledge Base RAG & Content Engineering Workspace

[![PHP](https://img.shields.io/badge/PHP-8.5.0-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![OmniRoute](https://img.shields.io/badge/OmniRoute-v3.8.50_Ready-8B5CF6?style=for-the-badge&logo=openai&logoColor=white)](http://127.0.0.1:20128)
[![Tests](https://img.shields.io/badge/Tests-115%20Passed%20(100%25)-10B981?style=for-the-badge&logo=githubactions&logoColor=white)](tests/)
[![License](https://img.shields.io/badge/License-Proprietary-blue?style=for-the-badge)](LICENSE)

<p align="center">
  <strong>HelpOfAi Studio</strong> is an all-in-one, enterprise-ready content production platform. Built with cutting-edge Laravel 12, Livewire 3, Alpine.js, Tailwind CSS 4, Tiptap Editor, and the OmniRoute AI Multi-Model Gateway, it delivers sub-millisecond AI transformations, vector-powered RAG knowledge retrieval, real-time SEO scoring, multi-format exports, and cryptographic BYOK key governance.
</p>

[Key Features](#-key-features) • [Architecture](#-system-architecture) • [Database Schema](#-entity-relationship-schema) • [Quickstart](#-quickstart--installation) • [AI Routing](#-omniroute-model-directory) • [API Guide](#-rest-api--sse-streaming) • [Roadmap](#-16-phase-roadmap-completion)

---

</div>

## 🌟 Key Features

### 📝 1. Tiptap Pro Rich-Text Editor & Multi-Driver Canvas
- **Multi-Engine Drivers**: Seamlessly switch between **TipTap ProseMirror**, **Notion Block Canvas**, **Gutenberg Block Canvas**, **Markdown Split Screen**, and **Raw HTML** modes.
- **Granular Git-Style Word Diffing**: Dynamic Programming Longest Common Subsequence (LCS) engine highlights exact word-level deletions (`<del>`) and insertions (`<ins>`) in both **Split** and **Unified** views.
- **Real-Time Autosave & Local Draft Recovery**: Non-blocking background debounced persistence (1500ms) with automatic browser localStorage backup recovery.
- **Snapshot Time-Machine Diff**: Milestone version history with inline word-by-word diff comparisons and 1-click snapshot restoration.
- **Inline Ghost Auto-Completion**: Real-time Copilot/Cursor-style sentence auto-completion with `Tab` to accept and `Esc` to dismiss.

### 🪄 2. Contextual AI Action Suite (SSE Streaming & Multi-Candidate)
- **Multi-Candidate Generation (Choice Variations)**: Generate and switch between variation candidates (`#1`, `#2`, `#3`) with live style preset regeneration.
- **Interactive Transform Modifiers**: Live slider controls for Creativity Intensity (`0.3` to `1.0`), Tone Personas (*Professional, Casual, Persuasive, Academic*), and Target Length (*Shorter, Same, Longer*).
- **15+ Inline AI Operations**:
  - *Rewrite & Polish*: Polish, structural rewrite, and proofreading.
  - *Length & Flow*: Expand, shorten, continuous co-authoring, and TL;DR synthesis.
  - *Tone Shifter*: Professional, Casual, Persuasive, Friendly, Academic, and Direct.
  - *Extraction & Strategy*: Action items checklist extraction, 8th-grade reading simplification, and SEO keyword optimization.
- **Live Before-vs-After Telemetry Delta**: Real-time inspection measuring word deltas, Flesch-Kincaid scannability gains, focus keyword frequencies, and power copy verbs before applying.
- **Live SSE Token Streaming**: Server-Sent Events (SSE) deliver sub-50ms Time-To-First-Token (TTFT) rendering directly inside the document with movable floating actions.

### 🧠 3. Knowledge Base & RAG Vector Pipeline
- **Multi-Source Ingestion**: Ingest raw text files, notes, policy manuals, and live web URLs.
- **Recursive Chunking Engine**: Chunks documents into 500-token units with 50-token semantic overlap.
- **Vector Embedding Cache**: Caches query vector embeddings using SHA-256 hashes with user-configurable TTL (**1 Day**, **7 Days**, or **30 Days**) for `< 1ms` repeat lookups.
- **Dense Cosine Similarity Search**: Mathematical ranking engine retrieving exact context-relevant passages.

### 🎭 4. Brand Voice Profiler
- **Tone-of-Voice Synthesis**: Create custom brand personas with target audience definitions, tonal characteristics, and style guide constraints.
- **Prompt Injection**: System automatically injects brand voice constraints into all template executions and contextual transformations.

### 📊 5. Real-Time SEO Analyzer
- **Real-Time Scoring (0 - 100)**: Evaluates content against target focus keywords.
- **Keyword Density Meter**: Visual health indicators measuring optimal 1.0% - 2.5% frequency.
- **Flesch-Kincaid Reading Ease**: Computes readability scores based on average sentence lengths and syllable counts.
- **Structural Heading Analysis**: Inspects H1, H2, and H3 distribution and heading keyword placement.

### 📦 6. Multi-Format Exporter & Public Sharing
- **1-Click Binary Exports**: Download documents instantly as **Markdown (`.md`)**, **Standalone HTML (`.html`)**, **Plain Text (`.txt`)**, and **Microsoft Word (`.docx`)** via WordprocessingML.
- **Print-Ready PDF**: Print view optimized for `@media print` with custom margins, typography, and page headers.
- **Secure Public Sharing (`/share/{token}`)**:
  - Password gate with **AES-256 hash protection**.
  - Reader mode with Light / Dark theme toggle and 1-click clipboard copy.
  - Granular permission flags (`allow_download`, `allow_copy`, `expires_at`).

### 🛡️ 7. Admin Control Panel & Model Governance
- **Multi-Model Routing Engine**: Toggle models, configure pricing tiers, and switch the global primary default model.
- **Live Latency & Health Prober**: Active ping probe benchmarks latency (ms) and status (`healthy`, `degraded`, `offline`).
- **Emergency AI Circuit Breaker**: Instant platform-wide kill switch to pause outgoing AI traffic during maintenance or unexpected provider billing spikes.
- **Quota & User Governance**: Grant bonus word credits, modify user subscription plans, and toggle account bans.

### 🔑 8. Cryptographic BYOK Keys & Dynamic Rate Limiting
- **AES-256-GCM Encryption**: Client API keys (OpenAI, DeepSeek, Anthropic, Ollama) are encrypted at rest.
- **Owner Visibility Control**: API key owners can reveal and copy their raw unencrypted keys on demand.
- **Tiered vs. Unlimited Rate Limiting**:
  - *Shared Admin Gateway*: Plan-based throttling (`Starter`: 15 req/min, `Pro`: 60 req/min, `Enterprise`: 180 req/min).
  - *BYOK / Local Endpoint*: **⚡ 100% UNLIMITED** requests (zero rate limit applied).

---

## 🏗 System Architecture

```
                               ┌────────────────────────────────────────────────────────┐
                               │                    HelpOfAi Studio                     │
                               └───────────────────────────┬────────────────────────────┘
                                                           │
               ┌───────────────────────────────────────────┼───────────────────────────────────────────┐
               ▼                                           ▼                                           ▼
┌───────────────────────────────┐           ┌───────────────────────────────┐           ┌───────────────────────────────┐
│     Content & Editor Core     │           │   OmniRoute AI Gateway SDK    │           │    RAG Vector Knowledge Hub   │
├───────────────────────────────┤           ├───────────────────────────────┤           ├───────────────────────────────┤
│ • Tiptap WYSIWYG Engine       │           │ • OpenAI / Claude / DeepSeek  │           │ • Document Chunking Engine    │
│ • Autosave & Version History  │           │ • SSE Live Streaming Engine   │           │ • Dense Vector Embeddings     │
│ • Multi-Format Export (.docx) │           │ • Outage Fallback Cascades    │           │ • Cosine Similarity Scoring   │
│ • Password-Gated Public Share │           │ • Emergency Circuit Breaker   │           │ • Configurable Vector Cache   │
└───────────────────────────────┘           └───────────────────────────────┘           └───────────────────────────────┘
```

---

## 💾 Entity Relationship Schema

```mermaid
erDiagram
    USERS ||--o{ PROJECTS : "owns"
    USERS ||--o{ DOCUMENTS : "creates"
    USERS ||--o{ BRAND_PROFILES : "defines"
    USERS ||--o{ KNOWLEDGE_SOURCES : "uploads"
    USERS ||--o{ GENERATIONS : "executes"
    USERS ||--o{ USER_API_KEYS : "stores BYOK"
    USERS ||--o{ USAGE_LOGS : "records"
    USERS ||--o{ AUDIT_LOGS : "triggers"

    PROJECTS ||--o{ DOCUMENTS : "categorizes"
    
    DOCUMENTS ||--|| DOCUMENT_CONTENTS : "persists html/json"
    DOCUMENTS ||--o{ DOCUMENT_VERSIONS : "milestone snapshots"
    DOCUMENTS ||--o{ DOCUMENT_SHARES : "secure public links"
    DOCUMENTS ||--o{ SEO_ANALYSES : "Rank Math audits"
    DOCUMENTS ||--o{ DOCUMENT_TAGS : "tagged with"

    TAGS ||--o{ DOCUMENT_TAGS : "classifies"

    KNOWLEDGE_SOURCES ||--o{ KNOWLEDGE_CHUNKS : "semantic chunks (500 tok)"
    KNOWLEDGE_CHUNKS ||--o{ VECTOR_EMBEDDINGS_CACHE : "vector hash cache"

    TEMPLATE_CATEGORIES ||--o{ TEMPLATES : "groups"
    TEMPLATES ||--o{ GENERATIONS : "executes"

    AI_PROVIDERS ||--o{ AI_MODELS : "offers"
    AI_PROVIDERS ||--o{ USER_API_KEYS : "authenticated by"
    AI_MODELS ||--o{ GENERATIONS : "routed via"
    AI_MODELS ||--o{ GATEWAY_TELEMETRY_LOGS : "benchmarks latency/health"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role "admin, user"
        string plan "starter, pro, enterprise"
        integer word_quota
        integer words_used
        integer bonus_words
        timestamp created_at
    }

    PROJECTS {
        bigint id PK
        bigint user_id FK
        string name
        string slug UK
        string description
        string color
        timestamp created_at
    }

    DOCUMENTS {
        bigint id PK
        bigint user_id FK
        bigint project_id FK
        bigint current_version_id FK
        string title
        string slug UK
        string editor_type "tiptap, notion, gutenberg, markdown"
        string status "draft, published, archived"
        integer word_count
        integer character_count
        integer reading_time_minutes
        timestamp updated_at
    }

    DOCUMENT_CONTENTS {
        bigint id PK
        bigint document_id FK
        longtext content_html
        json content_json
    }

    DOCUMENT_VERSIONS {
        bigint id PK
        bigint document_id FK
        integer version_number
        string summary
        longtext content_html
        json content_json
        integer word_count
        timestamp created_at
    }

    DOCUMENT_SHARES {
        bigint id PK
        bigint document_id FK
        string share_token UK
        string password_hash
        boolean allow_download
        boolean allow_copy
        timestamp expires_at
        integer view_count
    }

    SEO_ANALYSES {
        bigint id PK
        bigint document_id FK
        string target_keyword
        json secondary_keywords
        integer score "0-100"
        integer readability_score "0-100"
        json metrics "headings, images, links, density"
        json recommendations "Rank Math 4-pillar checks"
    }

    BRAND_PROFILES {
        bigint id PK
        bigint user_id FK
        string name
        text description
        string tone "professional, casual, persuasive"
        text target_audience
        text style_guidelines
        boolean is_default
    }

    KNOWLEDGE_SOURCES {
        bigint id PK
        bigint user_id FK
        string name
        string source_type "text, file, url"
        string cache_ttl "1d, 7d, 30d"
        integer total_tokens
    }

    KNOWLEDGE_CHUNKS {
        bigint id PK
        bigint knowledge_source_id FK
        integer chunk_index
        text chunk_text
        string embedding_hash UK
    }

    VECTOR_EMBEDDINGS_CACHE {
        bigint id PK
        string hash_key UK
        json embedding_vector
        timestamp expires_at
    }

    TEMPLATE_CATEGORIES {
        bigint id PK
        string name
        string slug UK
        string icon
    }

    TEMPLATES {
        bigint id PK
        bigint category_id FK
        string title
        string slug UK
        text prompt_template
        json schema_fields
        boolean is_active
    }

    GENERATIONS {
        bigint id PK
        bigint user_id FK
        bigint template_id FK
        bigint ai_model_id FK
        text input_prompt
        longtext output_result
        integer prompt_tokens
        integer completion_tokens
        integer total_words
        float execution_time_sec
    }

    USAGE_LOGS {
        bigint id PK
        bigint user_id FK
        bigint generation_id FK
        string action_type "transform, stream, template"
        integer words_billed
        string provider_slug
        string model_id
        timestamp created_at
    }

    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string event "auth, quota, config, admin"
        string ip_address
        string user_agent
        json metadata
        timestamp created_at
    }

    TAGS {
        bigint id PK
        string name
        string slug UK
        string color
    }

    DOCUMENT_TAGS {
        bigint id PK
        bigint document_id FK
        bigint tag_id FK
    }

    AI_PROVIDERS {
        bigint id PK
        string name
        string slug UK
        boolean is_active
        boolean allow_user_key
    }

    AI_MODELS {
        bigint id PK
        bigint provider_id FK
        string name
        string model_id UK
        integer context_window
        json capabilities "vision, reasoning, streaming"
        string health_status "healthy, degraded, offline"
        integer latency_ms
        boolean is_active
    }

    USER_API_KEYS {
        bigint id PK
        bigint user_id FK
        bigint provider_id FK
        string key_hash
        text encrypted_api_key "AES-256-GCM"
        timestamp last_used_at
    }

    GATEWAY_TELEMETRY_LOGS {
        bigint id PK
        bigint model_id FK
        integer response_time_ms
        integer status_code
        string error_message
        timestamp probed_at
    }
```

---

## 🛠 Tech Stack Matrix

| Category | Technology | Version | Purpose |
| :--- | :--- | :---: | :--- |
| **Backend Core** | PHP | `8.5.0` | Server runtime with JIT & modern typing |
| **Framework** | Laravel | `12.x` | Enterprise backend framework |
| **Full-Stack UI** | Livewire | `3.x` | Reactive server-driven UI components |
| **Client Reactivity**| Alpine.js | `3.x` | Micro-interactions, dropdowns & modals |
| **CSS Architecture** | Tailwind CSS | `v4.0` | High-performance CSS design system |
| **Asset Bundler** | Vite | `8.x` | Lightning-fast asset compiler (1.6s builds) |
| **Rich-Text Editor** | Tiptap | `v2.x` | Extensible ProseMirror-based WYSIWYG |
| **AI Gateway** | OmniRoute | `v3.8.50` | Decoupled multi-provider AI proxy |
| **Database** | MySQL / SQLite | `8.4+` | Relational storage with composite indexing |
| **Testing** | PHPUnit / Pest | `11.x` | Automated feature & unit test suite |

---

## 🚀 Quickstart & Installation

### 1. Prerequisites
- **PHP**: `>= 8.2` (PHP 8.5.0 recommended)
- **Composer**: `>= 2.7`
- **Node.js**: `>= 18.x` & NPM
- **Database**: MySQL 8.0+, MariaDB 10.5+, or SQLite

### 2. Setup Repository
```bash
git clone https://github.com/your-org/hoa-studio.git
cd hoa-studio

# Install PHP dependencies
composer install --optimize-autoloader

# Install NPM packages
npm install
```

### 3. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` to match your local or production database and AI endpoints:
```ini
APP_NAME="HelpOfAi Studio"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hoa-studio-db
DB_USERNAME=root
DB_PASSWORD=

# OmniRoute AI Multi-Model Gateway
OMNIROUTE_BASE_URL=http://127.0.0.1:20128
OMNIROUTE_API_KEY=sk-or-v1-dev-master-key
OMNIROUTE_DEFAULT_MODEL=deepseek/deepseek-chat
OMNIROUTE_FALLBACK_MODEL=glm/glm-4-flash
```

### 4. Database Setup & Seeding
```bash
php artisan migrate --seed
```

### 5. Build Assets & Start Server
```bash
# Build frontend bundle
npm run build

# Start local server
php artisan serve
```

Visit: **`http://127.0.0.1:8000`**

---

## 🤖 OmniRoute Model Directory

HelpOfAi Studio connects to OmniRoute for load balancing, multi-model cascades, and outage recovery:

| Provider | Model Identifier | Access Tier | Context Window | Capabilities |
| :--- | :--- | :---: | :---: | :---: |
| **DeepSeek** | `deepseek/deepseek-chat` | Starter / Free | 128,000 tokens | Copywriting, Polish, Summaries |
| **DeepSeek** | `deepseek-reasoner` | Pro / Enterprise | 128,000 tokens | R1 Chain-of-Thought Reasoning |
| **Anthropic** | `cc/claude-3-7-sonnet` | Pro Only | 200,000 tokens | Deep Expansion, Code, Long-Form |
| **OpenAI** | `openai/gpt-4o` | Pro / Enterprise | 128,000 tokens | Multimodal, Creative Copy, SEO |
| **Zhipu AI** | `glm/glm-4-flash` | Free Fallback | 128,000 tokens | High-Speed Fallback Cascade |
| **Groq** | `groq/llama-3.3-70b-versatile` | Free / Fast | 128,000 tokens | Sub-100ms Inference Speed |

---

## 📡 REST API & SSE Streaming

### 1. Synchronous Transformation
`POST /dashboard/api/ai/transform`

```bash
curl -X POST http://127.0.0.1:8000/dashboard/api/ai/transform \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "text": "HelpOfAi Studio makes AI copywriting simple and fast.",
    "type": "rewrite",
    "model": "deepseek/deepseek-chat",
    "temperature": 0.7
  }'
```

**Response (`200 OK`):**
```json
{
  "success": true,
  "result": "HelpOfAi Studio streamlines and accelerates AI-powered content creation.",
  "type": "rewrite",
  "word_count": 10,
  "quota_remaining": 49820
}
```

### 2. Live SSE Streaming
`POST /dashboard/api/ai/stream-transform`

```text
event: message
data: {"token": "HelpOfAi"}

event: message
data: {"token": " Studio"}

event: done
data: {"word_count": 10, "quota_remaining": 49820}
```

---

## 👥 Role & Plan Permissions Matrix

| Feature | Starter Plan | Pro Plan | Enterprise Plan | Admin |
| :--- | :---: | :---: | :---: | :---: |
| **Monthly Word Quota** | 15,000 words | 50,000 words | 250,000+ words | Unlimited |
| **Shared Gateway Rate Limit** | 15 req/min | 60 req/min | 180 req/min | 300 req/min |
| **BYOK Key Rate Limit** | ⚡ Unlimited | ⚡ Unlimited | ⚡ Unlimited | ⚡ Unlimited |
| **Advanced Models (Claude/GPT-4o)** | ✕ | ✓ | ✓ | ✓ |
| **Knowledge Base RAG Storage** | 3 Sources | 25 Sources | Unlimited | Unlimited |
| **Vector Cache Duration** | 1 or 7 Days | 1, 7, 30 Days | 1, 7, 30 Days | 1, 7, 30 Days |
| **Export Formats** | MD, HTML, TXT | MD, HTML, TXT, DOCX, PDF | All Formats | All Formats |
| **Admin Governance Panel** | ✕ | ✕ | ✕ | ✓ |

---

## ⚙️ Artisan Command Suite

| Command | Description |
| :--- | :--- |
| `php artisan hoa:verify-production` | Runs full system diagnostics for production readiness |
| `php artisan test` | Runs the 115-test automated test suite (100% passing) |
| `php artisan optimize:clear` | Flushes all compiled routes, configs, and blade views |
| `php artisan config:cache` | Caches all configuration files for production speed |
| `php artisan route:cache` | Compiles all application routes for fast dispatching |
| `php artisan view:cache` | Precompiles all Blade templates into raw PHP |

---

## 🧪 Quality Assurance & Test Verification

HelpOfAi Studio is backed by a rigorous automated test suite:

```bash
php artisan test
```

```text
 PASS  Tests\Feature\AdminControlPanelAndModelGovernanceTest
 PASS  Tests\Feature\AuthTest
 PASS  Tests\Feature\BrandVoiceTest
 PASS  Tests\Feature\DocumentImportExportSharingTest
 PASS  Tests\Feature\DocumentManagementTest
 PASS  Tests\Feature\EndToEndIntegrationAndFailureRecoveryTest
 PASS  Tests\Feature\KnowledgeBaseRagTest
 PASS  Tests\Feature\OmniRouteAiTest
 PASS  Tests\Feature\SecurityHardeningAndPerformanceTest
 PASS  Tests\Feature\SeoAnalysisTest
 PASS  Tests\Feature\SharedHostingDeploymentVerificationTest
 PASS  Tests\Feature\TemplateEngineTest
 PASS  Tests\Feature\UsageTrackingAndQuotasTest

Tests:    115 passed (481 assertions)
Duration: 24.42s
Result:   100% Green
```

---

## 📋 16-Phase Roadmap Completion

- [x] **Phase 01**: Foundation & Project Scaffolding *(PHP 8.5, Laravel 12, Vite 8, Tailwind CSS 4)*
- [x] **Phase 02**: Database & Authentication Core *(Session Auth, Roles, Plans & Quotas)*
- [x] **Phase 03**: Design System Tokens & Glass UI Components *(Badges, Cards, 60fps Sidebar)*
- [x] **Phase 04**: Projects & Document Management Core *(Livewire 3 Workspace)*
- [x] **Phase 05**: Tiptap Editor, Autosave & Versioning Engine *(ProseMirror, Diffs)*
- [x] **Phase 06**: Laravel AI SDK + OmniRoute Integration *(Multi-Provider Proxy Gateway)*
- [x] **Phase 07**: Contextual AI Transformations *(15+ Actions, Live SSE Stream)*
- [x] **Phase 08**: Template Engine & Brand Voice Profiles *(Variable Compilation, Style Injection)*
- [x] **Phase 09**: Real-time SEO Analysis Engine *(Flesch Reading Ease, Keyword Density)*
- [x] **Phase 10**: Knowledge Base / RAG Pipeline & Vector Cache *(Chunking, Cosine Similarity)*
- [x] **Phase 11**: Usage Tracking, Quotas & Token Accounting *(Token Telemetry, Plan Meters)*
- [x] **Phase 12**: Import / Export & Document Sharing *(PDF, Docx, MD, HTML, Password Gate)*
- [x] **Phase 13**: Admin Control Panel & Model Governance *(Health Prober, AI Circuit Breaker)*
- [x] **Phase 14**: Security Hardening & BYOK Cryptography *(AES-256-GCM Keys, Dynamic Limits)*
- [x] **Phase 15**: Automated Testing & Failure Recovery *(Outage Cascades, 3-Attempt Retries)*
- [x] **Phase 16**: Shared-Hosting Production Deployment Verification *(.htaccess, Caching)*
- [ ] **Phase 17 (Planned)**: Session Hijacking Defense & Concurrent Device Management *(Browser/Device fingerprinting, Token Revocation, "Log Out All Other Devices" hub)*
- [ ] **Phase 18 (Planned)**: Honeytoken Decoy Infrastructure & Account Lockout Security Alerts *(Fake credential trap auto-blacklisting, real-time brute-force lockout email alerts)*

---

## 📄 License & Attribution
HelpOfAi Studio is proprietary software. All rights reserved.
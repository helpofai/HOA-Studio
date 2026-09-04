# 📚 HelpOfAi Studio — Developer & Technical Architecture Guide

This document provides a comprehensive technical breakdown of the subsystems, domain models, algorithms, and API protocols powering **HelpOfAi Studio**.

---

## 📑 Table of Contents
1. [Architecture & Feature-Driven Structure](#1-architecture--feature-driven-structure)
2. [Tiptap Editor & Versioning Engine](#2-tiptap-editor--versioning-engine)
3. [OmniRoute AI Gateway SDK](#3-omniroute-ai-gateway-sdk)
4. [Contextual AI Transformation Engine](#4-contextual-ai-transformation-engine)
5. [Brand Voice Synthesis & Prompt Injection](#5-brand-voice-synthesis--prompt-injection)
6. [Knowledge Base & RAG Vector Pipeline](#6-knowledge-base--rag-vector-pipeline)
7. [Template Engine & Dynamic Compilation](#7-template-engine--dynamic-compilation)
8. [Real-time SEO Analysis Engine](#8-real-time-seo-analysis-engine)
9. [Import, Export & Public Sharing Engine](#9-import-export--public-sharing-engine)
10. [Model Governance & AI Circuit Breaker](#10-model-governance--ai-circuit-breaker)
11. [BYOK Cryptography & Dynamic Rate Limiter](#11-byok-cryptography--dynamic-rate-limiter)
12. [API Reference & Protocols](#12-api-reference--protocols)

---

## 1. Architecture & Feature-Driven Structure

HelpOfAi Studio follows a modular, **Domain Feature-Driven Architecture** located in `app/Features/`:

```
app/Features/
├── Admin/                 # Control center, analytics & telemetry
├── AI/                    # OmniRoute client, circuit breaker, rate limiters
├── Auth/                  # Auth actions, roles, plans, BYOK models
├── BrandVoice/            # Tone of voice profiles & synthesizer
├── Documents/             # Document CRUD, Tiptap, exporter, versions
├── KnowledgeBase/         # Chunking, vector embeddings, vector cache
├── Projects/              # Workspaces & folders
├── SEO/                   # Content readability & keyword analyzer
└── Templates/             # Reusable prompt templates & variable parser
```

---

## 2. Tiptap Editor & Versioning Engine

### 2.1 Editor Lifecycle & Extensions Suite
The editor is built with `@tiptap/core`, `@tiptap/starter-kit`, `@tiptap/extension-table` (with resizable columns), `@tiptap/extension-code-block-lowlight` (with `lowlight` syntax tokenizer), `@tiptap/extension-bubble-menu`, `@tiptap/extension-character-count`, `@tiptap/extension-task-list`, and `@tiptap/extension-typography`.

- **Floating Table Operations Bar**: When cursor focus enters any table cell, a reactive floating toolbar docks above the table offering 1-click actions: Add Row Above/Below, Delete Row, Add Column Left/Right, Delete Column, Toggle Header Row, Merge/Split Cells, and Delete Table. Supported by right-click table context controls.
- **Real-Time Code Highlighting (`CodeBlockLowlight`)**: 35+ programming languages parsed into AST tokens on the fly. Rendered with custom macOS terminal chrome, dynamic language selector dropdown, 1-click "Copy Code" button, and in-block <kbd>Tab</kbd> indentation (`enableTabIndentation: true`).
- **Debounced Autosave**: Changes dispatch a debounced Livewire event every `1500ms` saving HTML and ProseMirror JSON.
- **Reading Time Calculation**: Computed in real-time based on 200 words-per-minute average reading speed.

### 2.2 Versioning & Restoral
Managed by `SaveDocumentVersion.php` and `RestoreDocumentVersion.php`:
- Every manual save or AI batch generation creates an immutable snapshot in `document_versions`.
- Restoring a past version automatically creates a rollback snapshot preserving full operational history.

---

## 3. OmniRoute AI Gateway SDK

The SDK communicates with OmniRoute Gateway (`http://127.0.0.1:20128`):

### 3.1 Resilience & Outage Recovery
- **3-Attempt Exponential Backoff**: Automatic retry for transient 500, 502, 503, and 504 errors.
- **Fallback Cascades**: If primary upstream models (e.g. `openai/gpt-4o`) fail after 3 retries, the request cascades to the fallback model (`deepseek/deepseek-chat` or `glm/glm-4-flash`).
- **Guzzle IPv4 Forcing**: Configured with `'force_ip_resolve' => 'v4'` to ensure zero-latency local loopback resolution on Windows/Linux.

---

## 4. Contextual AI Transformation Engine

Supports 15+ contextual operations via `TransformText.php` and `AiStreamController.php`:

| Action Code | Description | Default Model |
| :--- | :--- | :--- |
| `polish` | Master copywriter polish preserving meaning | Auto / DeepSeek |
| `rewrite` | Structural rephrasing with fresh phrasing | Auto / DeepSeek |
| `fix_grammar` | Proofreader fixing spelling and punctuation | Auto / DeepSeek |
| `expand` | Deep content expansion with logical context | Auto / Claude |
| `shorten` | High-impact compacting and fluff removal | Auto / DeepSeek |
| `tone:*` | Switches tone (Professional, Casual, Persuasive, Academic) | Auto / DeepSeek |
| `seo_optimize`| Search engine optimization with target keywords | Auto / GPT-4o |
| `action_items`| Extracts markdown checklist `- [ ] Action` | Auto / DeepSeek |

### SSE Streaming Protocol
Clients establish an SSE stream to `/dashboard/api/ai/stream-transform`:
```text
event: message
data: {"token": "In"}

event: message
data: {"token": " today's"}

event: done
data: {"word_count": 120, "quota_remaining": 48500}
```

---

## 5. Brand Voice Synthesis & Prompt Injection

Brand Profiles compile target audiences, tonal attributes, style guides, and negative constraints into system instructions:

```php
// app/Features/BrandVoice/Models/BrandProfile.php
public function toPromptInstruction(): string
{
    return "BRAND VOICE CONSTRAINTS:
    - Tone: {$this->tone_description}
    - Target Audience: {$this->target_audience}
    - Style Rules: " . implode('; ', $this->rules);
}
```

---

## 6. Knowledge Base & RAG Vector Pipeline

```
Raw Text / URL / PDF ──► Recursive Chunker ──► Dense Vector Embedding ──► SHA-256 Cache ──► Cosine Similarity Search
```

### 6.1 Chunking Strategy
- Recursive text splitting with **500-token chunks** and **50-token overlapping windows**.

### 6.2 Vector Embedding Cache (`VectorCacheManager.php`)
- Queries are hashed using SHA-256 (`text + model`).
- Configurable TTL: **1 Day**, **7 Days (Default)**, or **30 Days**.
- Cached vector lookups resolve in `< 1ms` without making external API calls.

### 6.3 Cosine Similarity
$$\text{Cosine Similarity}(A, B) = \frac{A \cdot B}{\|A\| \|B\|} = \frac{\sum_{i=1}^{n} A_i B_i}{\sqrt{\sum_{i=1}^{n} A_i^2} \sqrt{\sum_{i=1}^{n} B_i^2}}$$

---

## 7. Template Engine & Dynamic Compilation

Templates support Mustache-style variable syntax: `{{variable_name}}`.

```php
// Template compilation example:
$renderedPrompt = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($matches) use ($inputs) {
    return $inputs[$matches[1]] ?? '';
}, $template->prompt_template);
```

---

## 8. Real-time SEO Analysis Engine

The SEO engine computes real-time content health:
- **Keyword Density**: Optimal frequency calculation ($1.0\% - 2.5\%$).
- **Flesch Reading Ease**:
  $$\text{Reading Ease} = 206.835 - (1.015 \times \text{ASL}) - (84.6 \times \text{ASW})$$
  *(where ASL = Average Sentence Length, ASW = Average Syllables per Word)*.
- **Structure Analysis**: Checks for `H1`, `H2`, `H3` hierarchy and introductory keyword placement.

---

## 9. Import, Export & Public Sharing Engine

### 9.1 Supported Formats
- **Markdown (`.md`)**: Full GFM export with preserved headings and lists.
- **Standalone HTML (`.html`)**: Self-contained styled document.
- **Plain Text (`.txt`)**: Clean ASCII text.
- **Word Document (`.docx`)**: Generated using Microsoft WordprocessingML XML standard.
- **Print PDF**: Auto-trigger print view optimized for `@media print` with headers and footers.

### 9.2 Public Sharing (`/share/{token}`)
- **AES-256 Password Hash**: Protected with `Hash::make()`.
- **View Tracker**: Atomic view counter incrementing.
- **Permission Toggles**: `allow_download` and `allow_copy`.

---

## 10. Model Governance & AI Circuit Breaker

### 10.1 Multi-Model Routing
- Designate primary global fallback models with atomic exclusivity.
- Allocate model access per subscription tier (`Free Tier` vs `Pro Only`).

### 10.2 Emergency Circuit Breaker (`AiCircuitBreaker.php`)
- Allows administrators to trip a platform-wide emergency hold on all outgoing AI traffic.
- Requests immediately return `503 Service Unavailable` with maintenance reasoning without consuming upstream billing.

---

## 11. BYOK Cryptography & Dynamic Rate Limiter

### 11.1 Key Encryption at Rest
- User API keys (OpenAI, DeepSeek, Anthropic) are encrypted using **AES-256-GCM** via Laravel's `encrypted` model casting.
- Keys are never stored in plain text.
- The key owner can safely inspect or copy their unencrypted key in their user profile with single-click visibility toggling.

### 11.2 Rate Limiter Rules (`AiRateLimiterService.php`)
- **Shared Gateway Users**:
  - `Starter`: 15 requests / min
  - `Pro`: 60 requests / min
  - `Enterprise`: 180 requests / min
- **BYOK / Local Endpoint Users**:
  - ⚡ **100% UNLIMITED** (zero rate limit applied).

---

## 12. API Reference & Protocols

### Contextual Transformation API
`POST /dashboard/api/ai/transform`

**Headers:**
```http
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "text": "HelpOfAi Studio empowers content creators to write faster.",
  "type": "rewrite",
  "model": "deepseek/deepseek-chat",
  "temperature": 0.7
}
```

**Response (`200 OK`):**
```json
{
  "success": true,
  "result": "HelpOfAi Studio enables writers and teams to accelerate content production.",
  "type": "rewrite",
  "word_count": 11,
  "quota_remaining": 49850
}
```

---

### Shared Document Export API
`GET /share/{token}/export/{format}`
- **Formats:** `markdown`, `html`, `txt`, `docx`

---

## 13. Security Roadmap & Future Specifications

### 13.1 Session Hijacking Defense & Concurrent Device Management (Phase 17)
- **Device & Browser Fingerprinting**: Cryptographic hash pairing `SHA-256(User-Agent + Client IP + Subnet)` evaluated on every incoming authenticated session request.
- **Mid-Session Hijack Invalidation**: If a high-entropy signature shift occurs mid-session, invalidate session tokens and force password re-authentication.
- **Concurrent Device Dashboard**: Display active devices (OS, Browser, IP, Country, Last Active) in the user profile with 1-click **"Log Out All Other Devices"** (revoking foreign session IDs from `sessions` table).

### 13.2 Honeytoken Accounts & Account Lockout Security Alerts (Phase 18)
- **Honeytoken Decoy Traps**: Seed system with inert decoy accounts (e.g. `admin@helpofai.com`, `root@helpofai.com`, `test@helpofai.com`). Any login attempt targeting a honeytoken immediately blacklists the originating IP in `blocked_ips` with zero tolerance.
- **Brute-Force Account Lockout Email Notifications**: When an authentic user account hits the 5-failure lockout limit, dispatch an automated high-priority alert email informing the owner with incident timestamp, IP address, and 1-click password reset link.
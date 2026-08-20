# AI Integration & OmniRoute Gateway Module — HelpOfAi Studio (HOA-Studio)

## Purpose
The `AI` feature module serves as the decoupled intelligent routing, generation, text transformation, and token metering layer for HelpOfAi Studio, communicating exclusively with the **OmniRoute v3.8.50+ Gateway**.

## Architecture & Integration
```text
app/Features/AI/
├── Services/
│   └── OmniRouteClient.php      # Unified HTTP & SSE streaming client for OmniRoute v3.8.50+
├── Actions/
│   ├── TransformText.php         # Contextual transformations (Polish, Shorten, Expand, Simplify, SEO)
│   ├── GenerateDocumentDraft.php # Full long-form structured article generator
│   └── RecordGenerationUsage.php # Quota consumption and telemetry audit logger
├── Http/Controllers/
│   └── AiStreamController.php   # SSE token stream & JSON transformation API
└── README.md

config/
└── omniroute.php                 # Gateway endpoints, combo mapping, compression & cache settings
```

## OmniRoute Protocol & Telemetry Compliance
HOA-Studio interacts with OmniRoute through the OpenAI-compatible `/v1/chat/completions` surface while leveraging OmniRoute-native features:
- **Custom Request Headers:**
  - `X-OmniRoute-Session-Id`: Conversation affinity & persistent memory tagging.
  - `X-Request-Id`: Idempotent deduplication & distributed tracing.
  - `X-OmniRoute-No-Cache`: Selective cache bypassing.
  - `x-omniroute-compression`: Context compression plan (`default`, `engine:rtk`, `combo`).
- **Telemetry Response Capture:**
  - `X-OmniRoute-Response-Cost`: Real-time cost in USD per generation.
  - `X-OmniRoute-Tokens-In` / `X-OmniRoute-Tokens-Out`: Token telemetry.
  - `X-OmniRoute-Decision`: Multi-provider combo routing trace (`strategy`, `provider`, `latency_ms`).
  - `X-OmniRoute-Cache`: Semantic cache hit status (`HIT` / `MISS`).

## Multi-Provider Combos & Free Tier Aggregation
- DeepSeek-V3, Claude 3.7 Sonnet, OpenAI GPT-4o, GLM-4-Flash, Groq Llama 3.3 70B, and Ollama local sidecars.
- Auto-fallback cascades across 42 free-tier pools aggregating over ~1.51B documented monthly tokens.

## Testing & Quality Gates
- `tests/Feature/OmniRouteClientTest.php` verifying HTTP custom headers, telemetry parsing, quota deduction, and error states (100% green).
# Feature specification: Larai Tracker v1.2.0

Status: implemented release candidate, 2026-10-01.

## Goal and scope

Harden authentication and untrusted input, make usage attribution and pricing honest, keep large installations responsive, and establish repeatable release checks. Scope includes the Laravel package runtime, migration stubs, dashboard, tests, CI, and operator documentation. Public installation paths and Laravel 10–12 support remain compatible.

## Technical design

- Authentication: production bootstrap requires `LARAI_TRACKER_SETUP_TOKEN`; login uses a named five-attempt/minute limiter; sessions regenerate on login and invalidate on POST logout.
- Capture: a fail-open listener maps exact hosts/endpoints for OpenAI Chat Completions/Responses, Anthropic Messages, Gemini Generate Content, Azure OpenAI deployments, and OpenRouter Chat Completions.
- Costs: `cost_usd` becomes nullable. Database overrides win, then the versioned bundled catalog; no match means price unavailable.
- Currency: budgets and estimated costs use USD. Existing currency settings migrate back to truthful USD labels.
- Scale: date filters use indexed `created_at` ranges, exports use database cursors, and retention uses a batched Artisan command rather than package boot.
- Alerts: period and threshold metadata guard against duplicate alerts; non-sync queue configurations queue mail.

## Security and privacy

- Sort fields, directions, dates, settings, prices, emails, retention, and export formats are bounded or allowlisted.
- Chart model names are inserted with `textContent`; CSV formula prefixes are neutralized.
- Raw AI content and credentials are never stored.
- Catalog sync requires HTTPS, a timeout, schema validation, and preserves manual prices.

## Test plan

- Authentication bootstrap, throttling, method safety, and password flows.
- Provider fixtures plus failed, absent, partial, unrelated, negative, and non-numeric usage cases.
- Unknown/known pricing, malformed sync, manual overrides, currency and settings validation.
- Stream export safety, batched retention, and period-idempotent budget alerts.
- Pest, PHP lint, Pint, Larastan, Composer validation, and Composer audit as release gates.

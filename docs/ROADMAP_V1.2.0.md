# Larai Tracker v1.2.0 release plan

Status: implemented release candidate, 2026-10-01. The original audit and acceptance criteria are retained below for release review.

## 1. Goal and scope

Make Larai Tracker a trustworthy, secure, and easy-to-adopt open-source Laravel package for AI usage and cost tracking. The release succeeds when a fresh install is safe by default, supported providers report defensible usage and costs, larger datasets remain usable, and contributors can reproduce the checks locally.

Scope: package listeners, pricing, dashboard, migrations, tests, documentation, and GitHub workflows. Preserve existing public package installation and Laravel 10–12 support unless the compatibility matrix proves a change necessary. Follow Semantic Versioning: breaking public API or configuration changes belong in a future major release.

The current `docs/PRODUCT_RULES.md` describes loyalty points and vouchers rather than this package. Replace it with the actual tracking, pricing, budget, and privacy rules before implementation.

## 2. Evidence from the current repository

| Area | Current state | Consequence |
| --- | --- | --- |
| Setup and login | Production with no password allows the first visitor to create one; login has no rate limit; login does not regenerate the session; logout uses GET. | Installation can be claimed by an unintended visitor; auth flow needs hardening. |
| Request handling | Log sort column and direction are used directly; settings and chart dates have little validation; chart HTML inserts model names using `innerHTML`. | Invalid input and untrusted model names need bounded, escaped handling. |
| Usage capture | `ResponseReceived` listener classifies an unknown host with a `usage` key as OpenAI; provider tests are absent. | False attribution and inaccurate logs are possible. |
| Pricing | Unknown models fall back to $10/$30 per million tokens; bundled and synced prices lack source/effective dates; sync overwrites `is_custom` rows. | Costs can look precise while being guesses; user overrides can be lost. |
| Currency and budget | Costs are stored in USD, while UI changes only the currency symbol; budget amount has no currency conversion. | Display and alerts can compare unlike units. |
| Scale | Exports load all logs in memory; cleanup runs during application boot; date queries wrap `created_at` in SQL `DATE()`. | Large installs can slow requests and miss useful indexes. |
| Project quality | 17 tests pass, focused on auth/settings/calculation; CI covers Laravel 10–12 but no static analysis or style gate; `composer.lock` is out of sync with `composer.json`; tags include v1.0.5 and v1.1.0 while `CHANGELOG.md` stops at v1.0.1. | Important paths lack regression coverage and dependency installs are less reproducible; upgrade history is incomplete. |

These are code review findings, not claims about a deployed installation. Verify each with a failing test before changing behavior.

## 3. Work packages in dependency order

### P0 — release blockers

| ID | Work | Acceptance criteria |
| --- | --- | --- |
| SEC-1 | Secure bootstrap and authentication. Require an explicit owner-controlled setup secret or console setup command in production; add login throttling, session regeneration/invalidation, POST logout, and documentation for existing installs. | An unauthenticated visitor cannot claim an unconfigured production dashboard; brute-force attempts are limited; session ID rotates at login; logout invalidates access; existing configured installations still sign in. |
| SEC-2 | Validate and safely render untrusted data. Allowlist log sort fields/directions; validate dates, amounts, prices, email and retention bounds; escape model names in chart updates; protect CSV consumers from formula injection; bound export formats. | Invalid requests return validation errors; model names cannot execute script; exported fields remain data in spreadsheet software; tests cover malicious and boundary inputs. |
| DATA-1 | Correct usage capture through small provider adapters for OpenAI-compatible chat/responses, Anthropic, Gemini, Azure, and OpenRouter. Match known hosts/endpoints, only successful responses, parse documented usage fields, and make recording failures non-disruptive to the host app. | Fixture tests cover each advertised provider, absent/partial usage, failed responses, and unrelated HTTP traffic; no prompts, completions, API keys, or raw response bodies are persisted. |
| DATA-2 | Make cost estimates honest. Remove the arbitrary unknown-model fallback; mark cost as unknown when no verified price exists. Keep a versioned price catalog with source and effective date, and never overwrite custom prices during sync. Validate the remote catalog and set request timeouts. | Unknown models display “price unavailable” and are excluded or separately identified in budget totals; overrides survive sync; stale/invalid catalogs cannot silently change prices; existing stored costs are not retroactively rewritten. |
| DATA-3 | Define one currency contract. Store and compare budgets in USD, or convert with an explicit dated FX rate before showing another currency. Provide a migration path for existing settings. | Every cost and budget label has a truthful unit; threshold tests compare the same unit; no symbol-only conversion remains; migration/upgrade notes explain old settings. |

### P1 — ship in v1.2.0 after P0

| ID | Work | Acceptance criteria |
| --- | --- | --- |
| REL-1 | Move retention cleanup to a documented scheduled command with batched deletes; stream/chunk exports; add indexes that match date/filter queries. | Normal requests do not perform cleanup; export works with a large fixture without loading every model; explain the host scheduler requirement and migration path. |
| REL-2 | Make budget alerts idempotent by billing period and threshold, and send mail through the host queue when configured. | Concurrent calls do not duplicate the same alert; crossing a threshold sends one alert for that period; mail failures do not lose usage logs. |
| DX-1 | Add focused tests for listeners, pricing sync, budgets, exports, validation, migrations, and upgrade scenarios. Add formatter/static analysis and dependency audit gates to CI; fix lock-file drift. | Local documented commands and the supported PHP/Laravel matrix pass; CI runs on PRs; package installation from a clean checkout is reproducible. |
| OSS-1 | Rewrite README and package docs around a five-minute quickstart, supported provider/endpoint matrix, exact tracking limitations, privacy/data retention, security setup, upgrade guide, and troubleshooting. Update the unrelated product rules. | A contributor can install the package and reproduce a tracked call from docs; claims about providers, currency, and “zero code changes” match tests and limitations. |
| OSS-2 | Add `SECURITY.md`, issue templates, Code of Conduct, maintainer/release policy, and missing changelog history for v1.0.5/v1.1.0 before the new entry. Enable dependency updates and protected-branch review/status checks in GitHub settings. | Community files are visible in the GitHub community profile; security reports have a private path; tagged release includes upgrade notes and all gates are green. |
| UX-1 | Improve dashboard semantics, empty/unknown-price states, keyboard access, contrast, mobile layouts, and safe loading/error states. | Manual keyboard and screen-reader smoke checks pass on dashboard, logs, login, and settings; UI clearly distinguishes measured tokens from estimated cost. |

### Later release candidates

Multi-tenant/team isolation, request tracing, additional SDK integrations, advanced forecasts, and automatic currency conversion from a live FX service should follow once the core data contract and tests are stable. They are not release blockers for v1.2.0.

## 4. Implementation sequence

1. Write the real product rules and feature specs for authentication, usage/cost, and currency; capture current upgrade behavior in tests.
2. Land SEC-1 and SEC-2 first, with security regression tests. Prepare a patch release sooner if an active deployment is exposed.
3. Land DATA-1 through DATA-3 with provider fixtures and documented migration behavior.
4. Land REL-1/REL-2, then DX-1 and UX-1; measure representative export/query behavior.
5. Complete OSS-1/OSS-2, run the full matrix, perform a clean-host install and upgrade smoke test, then publish v1.2.0 release notes and tag.

Each work package should be a reviewable PR with a focused spec, tests, and documentation. Record code changes in `docs/CHANGELOG_AI.md` as required by the repository workflow.

## 5. Release gate

- All P0 and P1 acceptance criteria are met; no known high-severity auth, XSS, input-validation, or data-integrity issue remains open.
- `composer validate`, PHP syntax checks, formatter/static analysis, and all automated tests pass on the supported matrix.
- Clean install and upgrade from the prior release are tested with SQLite plus at least one production-like database engine.
- Provider fixtures demonstrate the documented support matrix; unknown prices and mixed-currency budgets cannot produce misleading totals.
- README, security reporting, changelog, migration notes, and tagged package contents agree with the shipped behavior.

## 6. Reference standards

- [Semantic Versioning 2.0.0](https://semver.org/) for minor-release compatibility.
- [OpenSSF Scorecard checks](https://github.com/ossf/scorecard/blob/main/docs/checks.md) for branch protection, review, workflow permissions, and dependency hygiene.
- [GitHub community profile guidance](https://docs.github.com/en/communities/setting-up-your-project-for-healthy-contributions/about-community-profiles-for-public-repositories) for contribution files.
- [GitHub repository security advisories](https://docs.github.com/en/code-security/concepts/vulnerability-reporting-and-management/repository-security-advisories) for private vulnerability reporting and coordinated disclosure.

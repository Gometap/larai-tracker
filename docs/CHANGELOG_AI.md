# 📝 AI Technical Changelog (CHANGELOG_AI)

This document records the history of code changes made by AI Agents. Each entry helps subsequent AI sessions quickly understand technical context.

---

## [2026-10-01] Restore Laravel 10/11 CI compatibility coverage (Codex)
- **Summary:** Fixed five GitHub Actions jobs that stopped before executing tests after Composer began blocking installation of Laravel 10/11 releases affected by upstream security advisories, then fixed the Laravel 10 migration incompatibility exposed by the restored matrix.
- **Files Changed:**
  - `[MODIFY] .github/workflows/tests.yml`
  - `[MODIFY] composer.json, composer.lock`
  - `[MODIFY] docs/CHANGELOG_AI.md`
- **Technical Details:** Added Composer's scoped `--no-security-blocking` flag only to the Laravel 10/11 compatibility matrix entries. Dependency auditing remains blocking for supported Laravel 12 jobs, while legacy jobs still run `composer audit` and emit a warning without producing misleading failed-step annotations. Added Doctrine DBAL 3/4 as a runtime dependency because Laravel 10 requires DBAL 3 for the v1.2 migration's nullable `cost_usd` column change, while Laravel 12 resolves DBAL 4; this keeps legacy data intact across supported database engines without unsafe vendor-specific table rewrites. Added precise token array shapes so Larastan 2/PHPStan 1 used by the Laravel 10 matrix can prove arithmetic safety. Updated checkout/cache actions to their Node 24 releases. No advisory is ignored in package metadata.
- **Verification Results:** `composer check` passed on Laravel 10, 11, and 12 dependency sets (52 tests, 115 assertions each; Pint and Larastan/PHPStan clean). Strict Composer validation, locked Laravel 12 dependency audit, workflow YAML parsing, and `git diff --check` passed. GitHub Actions run #17 passed all seven matrix jobs; the follow-up push removes misleading legacy-audit error annotations and deprecated Node 20 action warnings.

## [2026-10-01] v1.2.0 security, accuracy, and scalability release (Codex)
- **Summary:** Implemented the v1.2.0 roadmap as a backward-compatible release candidate. Fixed dashboard bootstrap/authentication weaknesses, untrusted input paths, false provider attribution, misleading unknown-model costs/currency, custom-price overwrite, unbounded exports, request-time cleanup, duplicate budget alerts, and the broken public facade.
- **Files Changed:**
  - `[MODIFY] src/, routes/web.php, config/larai-tracker.php, resources/views/`
  - `[MODIFY] database/migrations/, resources/data/prices.json`
  - `[MODIFY] composer.json, composer.lock, .github/workflows/tests.yml`
  - `[NEW] src/Console/Commands/CleanupLogsCommand.php, database/migrations/upgrade_larai_tracker_to_v1_2_0.php.stub`
  - `[NEW] tests/Feature/{InterceptAiResponse,DashboardSecurity,CleanupCommand,LogAiCall,MigrationUpgrade}Test.php`
  - `[MODIFY] README.md, CHANGELOG.md, CONTRIBUTING.md, docs/PRODUCT_RULES.md, docs/TESTING_GUIDE.md`
  - `[NEW] SECURITY.md, CODE_OF_CONDUCT.md, docs/FEATURE_SPEC_V1.2.0.md, docs/PRICING_SOURCES.md, docs/RELEASE_POLICY.md, issue templates, Dependabot, PHPStan config`
- **Technical Details:** Added owner-token-gated production setup, throttled login, session rotation/invalidation and POST logout; exact fail-open provider adapters; nullable unknown costs and a versioned catalog that preserves manual overrides; a strict USD cost/budget contract; validated/allowlisted dashboard inputs; DOM-safe chart updates and CSV formula protection; streamed exports; indexed range queries; scheduled batched cleanup; period-idempotent queue-aware budget alerts; idempotent migration publishing; and working `Larai` facade resolution.
- **Verification Results:** `composer check` passed after merging with the existing `main` login-lockout work (52 tests, 115 assertions; Pint clean; Larastan level 5 clean). Strict Composer validation, locked dependency audit, and PHP lint over source/tests/config/routes/migration stubs passed. The migration suite includes an in-place legacy v1.1-style SQLite upgrade preserving logs and custom prices.

## [2026-09-21] v1.2.0 release planning (Codex)
- **Summary:** Audited the current package and created a prioritized v1.2.0 plan with release criteria. No runtime code changed.
- **Files Changed:**
  - `[NEW] docs/ROADMAP_V1.2.0.md`
  - `[MODIFY] docs/CHANGELOG_AI.md`
- **Technical Details:** Prioritized authentication, input safety, usage attribution, pricing accuracy, currency semantics, scaling, test coverage, and open-source maintenance. Documented a backward-compatible release sequence and upgrade gates.
- **Verification Results:** Baseline `composer test` passed (17 tests, 41 assertions); PHP lint passed on source, tests, config, routes, and migration stubs. `composer validate --no-check-publish` reported that `composer.lock` is out of date with `composer.json`; this is tracked under DX-1.

## [YYYY-MM-DD] Feature Name / Short Description (Agent Name)
- **Summary:** Concise description of the change (bug fixed or feature implemented).
- **Files Changed:**
  - `[MODIFY] path/to/file.ext`
  - `[NEW] path/to/new_file.ext`
- **Technical Details:** Architectural choices, new data structures, or business rules solved.
- **Verification Results:** Details of tests executed and their output.

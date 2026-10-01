# Changelog

All notable changes to `larai-tracker` will be documented in this file.

## [1.2.0] - 2026-10-01

### Added

- Provider fixtures and strict endpoint adapters for OpenAI Chat/Responses, Anthropic, Gemini, Azure OpenAI, and OpenRouter.
- Owner-controlled production setup token, login throttling, session rotation, and POST-only logout.
- Versioned price catalog provenance, nullable unknown costs, USD budget contract, and period-idempotent queued budget alerts.
- `larai:cleanup` batched retention command, cursor-streamed exports, matching date indexes, Pint, Larastan, dependency audit, and expanded CI gates.
- Security policy, issue templates, release policy, real product rules, v1.2 feature specification, and pricing-source documentation.

### Fixed

- Prevented unrelated HTTP responses with a `usage` key from being attributed to OpenAI.
- Prevented arbitrary sort fields/directions, invalid date/settings values, chart model-name XSS, and CSV formula injection.
- Prevented remote catalog synchronization from overwriting manual prices and rejected malformed or non-HTTPS catalogs.
- Removed invented `$10/$30` unknown-model pricing and display-only currency symbol conversion.
- Removed request-time retention cleanup and unbounded in-memory exports.

### Upgrade notes

- Publish and run the new v1.2 migration after updating. Existing custom prices and stored cost snapshots are preserved.
- Existing non-USD display settings are normalized to USD because historical costs are stored in USD.
- Published custom views must submit logout with POST. Schedule `php artisan larai:cleanup` if retention is enabled.

## [1.1.0] - 2026-04-20

### Added

- Anthropic usage and pricing support.
- Mobile improvements, toast notifications, configurable log retention, and settings/dashboard UI refinements.

## [1.0.5] - 2026-03-17

### Added

- Dashboard date-range filtering and asynchronous chart-data updates.

## [1.0.1] - 2026-02-27

### Added

- **Singleton Authentication**: A secure, password-based auth system to protect the dashboard.
- **Premium Login Interface**: Modern glassmorphism login UI with support for initial setup (first-time use).
- **Flexible Password Storage**: Hierarchical password resolution (Database > ENV > Config).
- **Security Controls**: Dedicated section in Settings to change the dashboard password from the UI.
- **Session Termination**: Integrated logout functionality across all dashboard views.
- **Developer-Friendly Bypass**: Automatic bypass of auth in the `local` environment when no password is set.

### Changed

- Replaced Gate-based authorization with a more robust Middleware-based authentication system.
- Standardized navigation bar across Dashboard, Logs, and Settings with integrated auth actions.

## [1.0.0] - 2026-02-18

### Added

- **Global Settings & Configuration**: A dedicated settings panel to manage package behavior.
- **Budget Management**: Set monthly AI spending limits and thresholds.
- **Email Alerts**: Native Laravel mail notifications when budget thresholds (e.g., 80%) are reached.
- **Multi-currency Support**: Configure preferred currency code and symbol (e.g., VND, ₫, EUR, €) globally.
- **Dynamic Cost Registry**: UI to manually override model prices (Input/Output per 1M tokens).
- **Price Synchronization**: One-click "Global Sync" to fetch latest pricing from the Gometap central registry.
- **Documentation Site**: Professional, dark-first landing page created for GitHub Pages.
- **Brand Identity**: Integrated official logo across Dashboard, Logs, and Documentation.

### Fixed

- Improved stacking context and z-index for dropdowns and headers in dark mode.
- Optimized responsive layouts for the configuration panel.

## [0.9.2] - 2026-02-18

### Added

- Fix logs page UI.
- Add export functionality for logs.
- Screenshot for logs page.

## [0.9.1] - 2026-02-18

### Added

- Logs page with search, filter, sort, and pagination.
- Log export functionality for JSON, CSV, and TXT formats.

## [0.9.0] - 2026-02-18

### Added

- First beta release.
- Invisible tracking via `ResponseReceived` event listener.
- Standalone Dashboard at `/larai-tracker`.
- Support for OpenAI, Azure, Gemini, and OpenRouter.
- Dark and Light mode support with theme toggle.
- Cost calculation for AI models.
- Migration and ServiceProvider integration.
- Authorization gate `viewLaraiTracker`.
- Unit tests for cost calculation and package initialization.
- Documentation: README, CONTRIBUTING, and PR Template.

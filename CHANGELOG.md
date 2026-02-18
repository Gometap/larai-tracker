# Changelog

All notable changes to `larai-tracker` will be documented in this file.

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

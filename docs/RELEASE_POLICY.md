# Release policy

Larai Tracker follows Semantic Versioning. Patch releases contain compatible fixes, minor releases add compatible behavior, and breaking public API/configuration changes require a major release.

A release requires:

1. `composer check`, strict Composer validation, PHP lint, and dependency audit passing.
2. The supported PHP/Laravel CI matrix passing.
3. Clean-install and previous-minor upgrade smoke tests.
4. `CHANGELOG.md`, README, migration notes, and the price-catalog effective date reviewed.
5. A signed or annotated Git tag matching the changelog version.

Security fixes may use an accelerated patch release, with details withheld until users can upgrade.

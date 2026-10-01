# Security policy

## Supported versions

Security fixes are provided for the latest minor release. Users on earlier versions should upgrade before reporting behavior that has already been corrected.

## Reporting a vulnerability

Please use GitHub's private security advisory flow for this repository. Do not open a public issue containing exploit details, credentials, prompts, completions, or production data. Include the affected version, Laravel/PHP versions, impact, reproduction steps, and any proposed mitigation.

Maintainers should acknowledge a report within seven days, coordinate a fix and disclosure timeline with the reporter, and publish a security release when impact is confirmed.

## Deployment notes

- Set a long random `LARAI_TRACKER_PASSWORD`, or use a temporary `LARAI_TRACKER_SETUP_TOKEN` for first setup and remove it afterward.
- Serve the dashboard over HTTPS and retain Laravel's CSRF/session middleware.
- Do not expose the dashboard as a substitute for per-user authorization; v1.2.0 is a package-wide owner dashboard.
- Review retention and export access because usage metadata can still be operationally sensitive.

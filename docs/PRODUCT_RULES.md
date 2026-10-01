# Larai Tracker product rules

## Usage capture

- Track only successful responses from explicitly supported provider hosts and endpoints.
- Persist provider, model, input/output/total token counts, authenticated Laravel user ID when available, timestamp, and estimated USD cost.
- Never persist prompts, completions, request/response bodies, authorization headers, API keys, or provider tokens.
- Tracking failures must not interrupt the host application's AI request.
- Negative or non-numeric usage values are invalid. A missing input or output count may be recorded as zero only when the other documented count is present.

## Pricing

- Prices are USD per one million tokens.
- Database prices take precedence over the bundled catalog. A manually edited price is an owner override and remote sync must never overwrite it.
- Bundled and synchronized entries carry a catalog version, source, and effective date.
- An unknown provider/model combination has an unknown cost (`null`), never an invented fallback. Unknown costs are excluded from totals and budgets and must be visible in the UI.
- Recorded historical costs are snapshots and are not retroactively recalculated when a catalog changes.

## Currency and budgets

- v1.2.0 stores, displays, and compares estimated costs and budgets in USD.
- Changing a symbol without converting the amount is forbidden.
- One active package-wide monthly budget is supported. Unknown-cost calls cannot contribute to its total.
- A budget threshold may emit at most one alert for the same percentage threshold and calendar month.

## Access and retention

- Outside `local`, the first password can be created only with an owner-controlled setup token.
- Login is rate-limited, successful authentication rotates the session ID, and logout is POST-only and invalidates the session.
- Retention cleanup never runs in a web request. Hosts schedule `php artisan larai:cleanup` and choose a retention period between 1 and 3650 days (zero disables configured cleanup).
- Exports stream rows and neutralize spreadsheet formulas in CSV text fields.

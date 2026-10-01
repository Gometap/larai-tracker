# Testing and build guide

Run the complete local release gate:

```bash
composer install
composer check
composer validate --strict --no-check-publish
composer audit --locked
find src tests config routes database/migrations -type f \
  \( -name '*.php' -o -name '*.stub' \) -print0 | xargs -0 -n1 php -l
```

`composer check` runs Pest, Pint in check mode, and Larastan. CI additionally resolves the supported Laravel 10–12 / PHP 8.2–8.4 matrix before running the same gates.

For upgrade testing, start with the v1.1.0 migration schema, publish the v1.2.0 migrations, run `php artisan migrate`, and verify that existing logs, custom prices, budget, and dashboard password remain intact.

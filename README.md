<p align="center">
  <img src="https://doq9otz3zrcmp.cloudfront.net/blogs/1_1771417079_rJ7ATPHw.png" width="128" alt="Larai Tracker Logo">
</p>

# Larai Tracker 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gometap/larai-tracker.svg?style=flat-square)](https://packagist.org/packages/gometap/larai-tracker)
[![Total Downloads](https://img.shields.io/packagist/dt/gometap/larai-tracker.svg?style=flat-square)](https://packagist.org/packages/gometap/larai-tracker)
[![Tests](https://github.com/gometap/larai-tracker/workflows/Tests/badge.svg)](https://github.com/gometap/larai-tracker/actions)

**Larai Tracker** is a standalone dashboard for tracking AI token usage and estimated USD API costs in Laravel applications. It observes supported requests made through Laravel's `Http` client for **OpenAI, Anthropic, Gemini, Azure OpenAI, and OpenRouter** without changing the calling code.

Supports Laravel **10, 11, and 12**.

## Screenshots

### Dashboard

![Dark Preview](https://github.com/gometap/larai-tracker/raw/main/art/dark.png)
![Light Preview](https://github.com/gometap/larai-tracker/raw/main/art/light.png)

### Logs

![Logs Preview](https://github.com/gometap/larai-tracker/raw/main/art/logs.png)

## Features

- 🕵️ **Automatic Tracking**: Logs documented token usage from supported Laravel HTTP client responses.
- 📊 **Premium Dashboard**: Access a high-end AI analytics center at `/larai-tracker`.
- 🔐 **Singleton Authentication**: Rate-limited, password-protected owner dashboard with secure first setup.
- 💰 **Honest Cost Estimates**: Versioned USD catalog, manual overrides, and explicit unavailable-price states.
- 🌐 **Multi-Provider Support**: OpenAI, Anthropic, Azure OpenAI, Gemini, and OpenRouter endpoint adapters.
- ⚙️ **Dynamic Pricing**: Sync latest prices or manually override model costs from the UI.
- 📦 **Large-data Safety**: Streamed exports, indexed date filters, and scheduled batched retention.

## Installation

Install the package via composer:

```bash
composer require gometap/larai-tracker
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="larai-tracker-migrations"
php artisan migrate
```

In production, configure either a permanent dashboard password or a temporary first-setup token:

```dotenv
LARAI_TRACKER_PASSWORD=a-long-random-password
# Or, only until the first password is saved:
LARAI_TRACKER_SETUP_TOKEN=a-long-random-owner-controlled-token
```

(Optional) Publish the configuration:

```bash
php artisan vendor:publish --tag="larai-tracker-config"
```

## Usage

### 🕵️ Automatic Tracking

Once installed, the package observes successful calls made through Laravel's `Http` facade. It does not observe provider SDKs that bypass Laravel's HTTP client, streamed responses that do not emit a complete supported usage payload, or arbitrary OpenAI-compatible hosts.

| Provider | Supported endpoint shape | Usage fields |
| --- | --- | --- |
| OpenAI | `/v1/chat/completions`, `/v1/responses` | prompt/completion or input/output tokens |
| Anthropic | `/v1/messages` | input/output tokens |
| Gemini | model `generateContent` URLs | `usageMetadata` |
| Azure OpenAI | deployment chat/responses URLs | OpenAI-compatible usage |
| OpenRouter | `/api/v1/chat/completions` | OpenAI-compatible usage |

Larai Tracker stores usage metadata only. It does not persist prompts, completions, raw bodies, authorization headers, or API keys.

### 📊 Accessing the Dashboard

Navigate to your application's URL at:
`https://your-domain.com/larai-tracker`

The dashboard features a premium dark-mode interface with:

- **Total Investment**: Your overall API spent.
- **Burn Rate**: Today's AI cost.
- **Token Metrics**: Total computation used.
- **Live Stream**: A real-time log of the latest AI calls.

## Configuration

### Authentication (Singleton Auth)

Larai Tracker uses a simple yet secure singleton authentication system. You can set the password in three ways (ordered by priority):

1. **Database**: Change it directly from the **Security** section in the dashboard settings.
2. **Environment**: Set `LARAI_TRACKER_PASSWORD` in your `.env` file.
3. **Config**: Set it in `config/larai-tracker.php`.

If no password is set outside `local`, first setup is disabled unless `LARAI_TRACKER_SETUP_TOKEN` contains at least 16 characters. Enter that token once on the setup screen, save the password, then remove the setup token from the environment. Login is limited to five attempts per minute per IP.

### Pricing, currency, and budgets

All estimated costs and budgets use USD. Unknown models are logged with a null cost, shown as **Price unavailable**, and excluded from totals and budget alerts. Prices are estimates and never replace provider invoices. Manual model prices take precedence and are not overwritten by catalog sync. See [catalog provenance](docs/PRICING_SOURCES.md).

### Retention

Web requests no longer run cleanup. Set the retention period in Settings and schedule the command in the host application:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('larai:cleanup')->daily();
```

You can also run `php artisan larai:cleanup --days=90 --batch=1000` manually.

## Upgrading from v1.1.x

```bash
composer require gometap/larai-tracker:^1.2
php artisan vendor:publish --tag="larai-tracker-migrations"
php artisan migrate
```

The upgrade makes `cost_usd` nullable, adds price provenance and budget-alert idempotency fields, and normalizes the old display-only currency setting to USD. Existing logs and manual prices remain unchanged. Replace any GET logout links with the package's POST form if you published custom views, and schedule `larai:cleanup` if retention is enabled.

## 🧪 Testing

The package includes a comprehensive test suite powered by [Pest](https://pestphp.com/).

```bash
composer test
composer format:check
composer analyse
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [danni](https://github.com/Danni2901)
- [Gometap Group](https://github.com/gometap)

## License

The Apache License 2.0. Please see [License File](LICENSE) for more information.

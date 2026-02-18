# Larai Tracker 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gometap/larai-tracker.svg?style=flat-square)](https://packagist.org/packages/gometap/larai-tracker)
[![Total Downloads](https://img.shields.io/packagist/dt/gometap/larai-tracker.svg?style=flat-square)](https://packagist.org/packages/gometap/larai-tracker)

**Larai Tracker** is a powerful, standalone dashboard for tracking AI token usage and API costs in Laravel applications. It "invisibly" intercepts AI responses via Laravel's native HTTP Client events, meaning it works with **OpenAI, Gemini, Azure, and OpenRouter** out of the box with **zero code changes** to your application logic.

## Screenshots
### Dashboard
![Dark Preview](https://github.com/gometap/larai-tracker/raw/main/art/dark.png)
![Light Preview](https://github.com/gometap/larai-tracker/raw/main/art/light.png)
### Logs
![Logs Preview](https://github.com/gometap/larai-tracker/raw/main/art/logs.png)
## Features

- 🕵️ **Invisible Tracking**: Automatically logs AI responses via Laravel's `ResponseReceived` event.
- 📊 **Premium Dashboard**: Access a high-end AI analytics center at `/larai-tracker`.
- 💰 **Cost Calculation**: Real-time USD cost estimation for GPT-4o, Gemini Flash, and more.
- 🌐 **Multi-Provider Support**: Seamlessly tracks OpenAI, Azure, Gemini, and OpenRouter.
- 🔒 **Secure by Default**: Built-in authorization gates to protect your cost data.

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

(Optional) Publish the views if you want to customize the dashboard:

```bash
php artisan vendor:publish --tag="larai-tracker-views"
```

## Usage

### 🕵️ Automatic Tracking

Once installed, the package starts working immediately. Every time your application uses the Laravel `Http` facade to call an AI provider (OpenAI, Gemini, etc.), Larai Tracker intercepts the response, parses the token usage, and logs it to the database.

### 📊 Accessing the Dashboard

Navigate to your application's URL at:
`https://your-domain.com/larai-tracker`

The dashboard features a premium dark-mode interface with:

- **Total Investment**: Your overall API spent.
- **Burn Rate**: Today's AI cost.
- **Token Metrics**: Total computation used.
- **Live Stream**: A real-time log of the latest AI calls.

## Configuration

### Authorization

By default, the dashboard is only accessible in `local` environment. To customize this, define the `viewLaraiTracker` gate in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewLaraiTracker', function ($user) {
    return $user->is_admin; // Example: only admins can view
});
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [danni](https://github.com/Danni2901)
- [Gometap Group](https://github.com/gometap)

## License

The Apache License 2.0. Please see [License File](LICENSE) for more information.

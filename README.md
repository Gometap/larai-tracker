<p align="center">
  <img src="https://doq9otz3zrcmp.cloudfront.net/blogs/1_1771417079_rJ7ATPHw.png" width="128" alt="Larai Tracker Logo">
</p>

# Larai Tracker 🚀

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gometap/larai-tracker.svg?style=flat-square)](https://packagist.org/packages/gometap/larai-tracker)
[![Total Downloads](https://img.shields.io/packagist/dt/gometap/larai-tracker.svg?style=flat-square)](https://packagist.org/packages/gometap/larai-tracker)
[![Tests](https://github.com/gometap/larai-tracker/workflows/Tests/badge.svg)](https://github.com/gometap/larai-tracker/actions)

**Larai Tracker** is a powerful, standalone dashboard for tracking AI token usage and API costs in Laravel applications. It "invisibly" intercepts AI responses via Laravel's native HTTP Client events, meaning it works with **OpenAI, Gemini, Azure, and OpenRouter** out of the box with **zero code changes** to your application logic.

Supports Laravel **10, 11, and 12**.

## Screenshots

### Dashboard

![Dark Preview](https://github.com/gometap/larai-tracker/raw/main/art/dark.png)
![Light Preview](https://github.com/gometap/larai-tracker/raw/main/art/light.png)

### Logs

![Logs Preview](https://github.com/gometap/larai-tracker/raw/main/art/logs.png)

## Features

- 🕵️ **Invisible Tracking**: Automatically logs AI responses via Laravel's `ResponseReceived` event.
- 📊 **Premium Dashboard**: Access a high-end AI analytics center at `/larai-tracker`.
- � **Singleton Authentication**: Secure password-protected dashboard (Config > ENV > DB).
- �💰 **Cost Calculation**: Real-time USD cost estimation for GPT-4o, Gemini Flash, and more.
- 🌐 **Multi-Provider Support**: Seamlessly tracks OpenAI, Azure, Gemini, and OpenRouter.
- ⚙️ **Dynamic Pricing**: Sync latest prices or manually override model costs from the UI.

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

(Optional) Publish the configuration:

```bash
php artisan vendor:publish --tag="larai-tracker-config"
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

### Authentication (Singleton Auth)

Larai Tracker uses a simple yet secure singleton authentication system. You can set the password in three ways (ordered by priority):

1. **Database**: Change it directly from the **Security** section in the dashboard settings.
2. **Environment**: Set `LARAI_TRACKER_PASSWORD` in your `.env` file.
3. **Config**: Set it in `config/larai-tracker.php`.

If no password is set and you are in a non-local environment, you will be prompted to set up a password upon your first visit.

## 🧪 Testing

The package includes a comprehensive test suite powered by [Pest](https://pestphp.com/).

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [danni](https://github.com/Danni2901)
- [Gometap Group](https://github.com/gometap)

## License

The Apache License 2.0. Please see [License File](LICENSE) for more information.

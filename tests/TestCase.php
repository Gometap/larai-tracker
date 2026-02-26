<?php

namespace Gometap\LaraiTracker\Tests;

use Gometap\LaraiTracker\LaraiTrackerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaraiTrackerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:y60M757i2Y0uX0H0zT3M7uW8mS7H9z1H9z1H9z1H9z0=');
        config()->set('app.cipher', 'AES-256-GCM');

        // Disable CSRF for testing (L10 uses VerifyCsrfToken, L11+ uses ValidateCsrfToken)
        $csrfClasses = [
            'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
            'Illuminate\Foundation\Http\Middleware\ValidateCsrfToken',
        ];

        foreach ($csrfClasses as $class) {
            if (class_exists($class)) {
                $app->singleton($class, function () {
                    return new class {
                        public function handle($request, $next) { return $next($request); }
                    };
                });
            }
        }

        // Necessary for testing session-based auth
        config()->set('session.driver', 'array');
        
        // Define 'web' middleware group as it's used in package routes but might not exist in Testbench
        $app['router']->middlewareGroup('web', []);

        // Load all migrations
        $migrations = [
            'create_larai_logs_table.php.stub',
            'create_larai_budgets_table.php.stub',
            'create_larai_model_prices_table.php.stub',
            'create_larai_settings_table.php.stub',
        ];

        foreach ($migrations as $m) {
            $migration = include __DIR__ . '/../database/migrations/' . $m;
            $migration->up();
        }
    }
}

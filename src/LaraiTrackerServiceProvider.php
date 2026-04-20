<?php

namespace Gometap\LaraiTracker;

use Illuminate\Support\ServiceProvider;

class LaraiTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/larai-tracker.php', 'larai-tracker');
        $this->app->singleton(Services\LaraiCostCalculator::class, function ($app) {
            return new Services\LaraiCostCalculator();
        });

        $this->app->booted(function () {
            $events = $this->app['events'];
            $events->listen(Events\AiCallRecorded::class, Listeners\LogAiCall::class);
            $events->listen(\Illuminate\Http\Client\Events\ResponseReceived::class, Listeners\InterceptAiResponse::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'larai');

        // Register middleware alias
        $router = $this->app['router'];
        $router->aliasMiddleware('larai.auth', Http\Middleware\LaraiAuthMiddleware::class);

        // Run log cleanup at most once per day
        $this->app->booted(function () {
            $this->runLogCleanup();
        });

        if ($this->app->runningInConsole()) {
            $this->publishMigrations();

            $this->publishes([
                __DIR__ . '/../config/larai-tracker.php' => config_path('larai-tracker.php'),
            ], 'larai-tracker-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/larai'),
            ], 'larai-tracker-views');
        }
    }

    /**
     * Delete logs older than the configured retention period (runs at most once per day).
     */
    protected function runLogCleanup(): void
    {
        try {
            $days = (int) Models\LaraiSetting::get('log_retention_days', 0);
            if ($days <= 0) {
                return;
            }

            $lastCleanup = Models\LaraiSetting::get('last_cleanup_at');
            if ($lastCleanup && \Carbon\Carbon::parse($lastCleanup)->isToday()) {
                return;
            }

            Models\LaraiLog::where('created_at', '<', now()->subDays($days))->delete();
            Models\LaraiSetting::set('last_cleanup_at', now()->toDateTimeString());
        } catch (\Exception $e) {
            // Table may not exist yet — silently skip
        }
    }

    /**
     * Publish package migrations.
     */
    protected function publishMigrations(): void
    {
        $migrations = [
            'create_larai_logs_table.php' => 'create_larai_logs_table.php',
            'create_larai_budgets_table.php' => 'create_larai_budgets_table.php',
            'create_larai_model_prices_table.php' => 'create_larai_model_prices_table.php',
            'create_larai_settings_table.php' => 'create_larai_settings_table.php',
        ];

        $publishPath = [];
        $i = 0;
        foreach ($migrations as $stub => $file) {
            $timestamp = date('Y_m_d_His', time() + $i);
            $publishPath[__DIR__ . "/../database/migrations/{$stub}.stub"] = database_path("migrations/{$timestamp}_{$file}");
            $i++;
        }

        $this->publishes($publishPath, 'larai-tracker-migrations');
    }
}

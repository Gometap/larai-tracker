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

        if ($this->app->runningInConsole()) {
            $migrationFileName = 'create_larai_logs_table.php';
            $timestamp = date('Y_m_d_His');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_larai_logs_table.php.stub' => database_path("migrations/{$timestamp}_{$migrationFileName}"),
            ], 'larai-tracker-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/larai'),
            ], 'larai-tracker-views');
        }

        $this->defineGates();
    }

    /**
     * Define the authorization gates.
     */
    protected function defineGates(): void
    {
        \Illuminate\Support\Facades\Gate::define('viewLaraiTracker', function ($user = null) {
            return app()->environment('local');
        });
    }
}

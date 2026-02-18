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

        $migration = include __DIR__ . '/../database/migrations/create_larai_logs_table.php.stub';
        $migration->up();
    }
}

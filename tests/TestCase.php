<?php

namespace Worksome\LaravelTelemetry\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Worksome\LaravelTelemetry\LaravelTelemetryServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Worksome\\LaravelTelemetry\\Database\\Factories\\' . class_basename(
                $modelName
            ) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelTelemetryServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-telemetry_table.php.stub';
        $migration->up();
        */
    }
}

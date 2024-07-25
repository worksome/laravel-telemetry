<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Worksome\LaravelTelemetry\LaravelTelemetryServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelTelemetryServiceProvider::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as MeterProviderSdkInterface;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SDK\Trace\TracerProviderInterface as TracerProviderSdkInterface;
use Psr\Log\LoggerInterface;

class LaravelTelemetryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            MeterProviderSdkInterface::class,
            fn() => (new MeterProviderFactory())->create()
        );
        $this->app->bind(MeterProviderInterface::class, MeterProviderSdkInterface::class);

        $this->app->singleton(
            TracerProviderSdkInterface::class,
            fn() => (new TracerProviderFactory())->create()
        );
        $this->app->bind(TracerProviderInterface::class, TracerProviderSdkInterface::class);
    }

    public function boot(
        LoggerInterface $logger,
        ConfigConfigurationResolver $configResolver,
    ): void
    {
        $this->publishes([
            __DIR__ . '/../config/telemetry.php' => $this->app->configPath('telemetry'),
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/../config/telemetry.php',
            'telemetry',
        );

        LoggerHolder::set($logger);
        CompositeResolver::instance()->addResolver($configResolver);

        $this->app->terminating(function (MeterProviderSdkInterface $meter, TracerProviderSdkInterface $tracer) {
            $meter->shutdown();
            $tracer->shutdown();
        });
    }
}

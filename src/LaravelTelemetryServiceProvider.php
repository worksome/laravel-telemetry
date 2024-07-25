<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface as LoggerProviderSdkInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as MeterProviderSdkInterface;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SDK\Trace\TracerProviderInterface as TracerProviderSdkInterface;
use Psr\Log\LoggerInterface;

class LaravelTelemetryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoggerProviderSdkInterface::class, function () {
            return (new LoggerProviderFactory())->create();
        });

        $this->app->bind(LoggerProviderInterface::class, LoggerProviderSdkInterface::class);

        $this->app->singleton(MeterProviderSdkInterface::class, function () {
            return (new MeterProviderFactory())->create();
        });

        $this->app->bind(MeterProviderInterface::class, MeterProviderSdkInterface::class);

        $this->app->singleton(TracerProviderSdkInterface::class, function () {
            return (new TracerProviderFactory())->create();
        });

        $this->app->bind(TracerProviderInterface::class, TracerProviderSdkInterface::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/telemetry.php' => $this->app->configPath('telemetry.php'),
        ], 'laravel-telemetry-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/telemetry.php',
            'telemetry',
        );

        $this->prepareConfigResolver();

        $this->callAfterResolving(Dispatcher::class, function (Dispatcher $event) {
            if ($this->app->make(Repository::class)->get('telemetry.sdk.disabled')) {
                return;
            }

            $event->listen(WorkerStopping::class, WorkerStoppingFlush::class);
        });

        $this->app->terminating(function () {
            if ($this->app->resolved(LoggerProviderSdkInterface::class)) {
                /** @var LoggerProviderSdkInterface $logger */
                $logger = $this->app->get(LoggerProviderSdkInterface::class);
                $logger->shutdown();
            }

            if ($this->app->resolved(MeterProviderSdkInterface::class)) {
                /** @var MeterProviderSdkInterface $meter */
                $meter = $this->app->get(MeterProviderSdkInterface::class);
                $meter->shutdown();
            }

            if ($this->app->resolved(TracerProviderSdkInterface::class)) {
                /** @var TracerProviderSdkInterface $tracer */
                $tracer = $this->app->get(TracerProviderSdkInterface::class);
                $tracer->shutdown();
            }
        });
    }

    private function prepareConfigResolver(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->app->get(LoggerInterface::class);

        LoggerHolder::set($logger);
        CompositeResolver::instance()->addResolver(
            new ConfigConfigurationResolver()
        );
    }
}

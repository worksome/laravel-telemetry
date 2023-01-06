<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Resolver\CompositeResolver;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\FactoryRegistry;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as MeterProviderSdkInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\StalenessHandler\NoopStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SDK\Trace\TracerProviderInterface as TracerProviderSdkInterface;
use Psr\Log\LoggerInterface;

class LaravelTelemetryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            MeterProviderSdkInterface::class,
            fn() => new MeterProvider(
                null,
                ResourceInfoFactory::defaultResource(),
                ClockFactory::getDefault(),
                Attributes::factory(),
                new InstrumentationScopeFactory(Attributes::factory()),
                [
                    new ExportingReader(
                        FactoryRegistry::metricExporterFactory('otlp')->create(),
                        ClockFactory::getDefault()
                    ),
                ],
                new CriteriaViewRegistry(),
                new WithSampledTraceExemplarFilter(),
                new NoopStalenessHandlerFactory(),
            )
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
            __DIR__ . '/../config/telemetry.php' => $this->app->configPath('telemetry.php'),
        ], 'laravel-telemetry-config');
        $this->mergeConfigFrom(
            __DIR__ . '/../config/telemetry.php',
            'telemetry',
        );

        LoggerHolder::set($logger);
        CompositeResolver::instance()->addResolver($configResolver);

        $this->app->terminating(function () {
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
}

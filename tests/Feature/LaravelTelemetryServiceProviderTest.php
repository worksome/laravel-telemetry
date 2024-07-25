<?php

declare(strict_types=1);

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface as LoggerProviderSdkInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as MeterProviderSdkInterface;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\TracerProviderInterface as TracerProviderSdkInterface;

it('registers the default config', function () {
    /** @var ConfigRepository $config */
    $config = $this->app->get(ConfigRepository::class);

    expect($config->get('telemetry'))
        ->sdk->disabled->toBeFalse()
        ->exporter->otlp->endpoint->toBe('http://localhost:4318');
});

it('can update the config at runtime', function () {
    /** @var ConfigRepository $config */
    $config = $this->app->get(ConfigRepository::class);

    expect(Sdk::isDisabled())->toBeFalse();

    $config->set('telemetry.sdk.disabled', true);

    expect(Sdk::isDisabled())->toBeTrue();
});

it('registers the logger provider', function () {
    expect(
        $this->app->get(LoggerProviderInterface::class)
    )
        ->toBeInstanceOf(LoggerProviderInterface::class)
        ->toBeInstanceOf(LoggerProviderSdkInterface::class);
});

it('registers the metric provider', function () {
    expect(
        $this->app->get(MeterProviderInterface::class)
    )
        ->toBeInstanceOf(MeterProviderInterface::class)
        ->toBeInstanceOf(MeterProviderSdkInterface::class);
});

it('registers the tracer provider', function () {
    expect(
        $this->app->get(TracerProviderInterface::class)
    )
        ->toBeInstanceOf(TracerProviderInterface::class)
        ->toBeInstanceOf(TracerProviderSdkInterface::class);
});

it('can get a logger from the logger provider', function () {
    expect(
        $this->app->get(LoggerProviderInterface::class)->getLogger('test')
    )
        ->toBeInstanceOf(LoggerInterface::class);
});

it('can get a meter from the meter provider', function () {
    expect(
        $this->app->get(MeterProviderInterface::class)->getMeter('test')
    )
        ->toBeInstanceOf(MeterInterface::class);
});

it('can get a tracer from the tracer provider', function () {
    expect(
        $this->app->get(TracerProviderInterface::class)->getTracer('test')
    )
        ->toBeInstanceOf(TracerInterface::class);
});

it('returns no-op instances when the SDK is disabled', function () {
    /** @var ConfigRepository $config */
    $config = $this->app->get(ConfigRepository::class);

    $config->set('telemetry.sdk.disabled', true);

    expect(
        $this->app->get(LoggerProviderInterface::class)->getLogger('test')
    )
        ->toBeInstanceOf(NoopLogger::class);

    expect(
        $this->app->get(MeterProviderInterface::class)->getMeter('test')
    )
        ->toBeInstanceOf(NoopMeter::class);

    expect(
        $this->app->get(TracerProviderInterface::class)->getTracer('test')
    )
        ->toBeInstanceOf(NoopTracer::class);
});

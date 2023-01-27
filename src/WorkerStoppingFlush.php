<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry;

use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\WorkerStopping;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as MeterProviderSdkInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface as TracerProviderSdkInterface;

readonly class WorkerStoppingFlush
{
    public function __construct(private Container $app)
    {
    }

    public function __invoke(WorkerStopping $event): void
    {
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
    }
}

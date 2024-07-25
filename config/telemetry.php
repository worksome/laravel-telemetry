<?php

declare(strict_types=1);

// The Configuration is based on OpenTelemetry's naming convention.
return [

    'sdk' => [
        'disabled' => ! env('LARAVEL_TELEMETRY_ENABLED', true)
    ],

    'exporter' => [
        'otlp' => [
            'endpoint' => env('OTEL_EXPORTER_OTLP_ENDPOINT', 'http://localhost:4318'),
            'metrics' => [
                'endpoint' => env('OTEL_EXPORTER_OTLP_METRICS_ENDPOINT', 'http://localhost:4318/v1/metrics'),
            ],
            'traces' => [
                'endpoint' => env('OTEL_EXPORTER_OTLP_TRACES_ENDPOINT', 'http://localhost:4318/v1/traces'),
            ],
        ],
    ],

];

<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry;

use OpenTelemetry\SDK\Common\Configuration\Resolver\ResolverInterface;

/**
 * Resolves Open Telemetry configuration via Laravel config.
 *
 * The `config()` function is intentionally used to ensure that the latest config is always used.
 */
class ConfigConfigurationResolver implements ResolverInterface
{
    private const string PREFIX = 'telemetry';

    public function retrieveValue(string $variableName): mixed
    {
        return config()->get($this->variableNameToConfigKey($variableName));
    }

    public function hasVariable(string $variableName): bool
    {
        return config()->has($this->variableNameToConfigKey($variableName));
    }

    private function variableNameToConfigKey(string $variableName): string
    {
        $names = collect(explode('_', $variableName))
            ->map(fn (string $key) => strtolower($key))
            ->skip(1)
            ->all();

        return implode('.', [self::PREFIX, ...$names]);
    }
}

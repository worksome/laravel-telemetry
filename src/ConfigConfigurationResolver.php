<?php

declare(strict_types=1);

namespace Worksome\LaravelTelemetry;

use Illuminate\Contracts\Config\Repository;
use OpenTelemetry\SDK\Common\Configuration\Resolver\ResolverInterface;

/**
 * Resolves Open Telemetry configuration via Laravel config.
 */
class ConfigConfigurationResolver implements ResolverInterface
{
    private const PREFIX = 'telemetry';

    public function __construct(
        private readonly Repository $config,
    ) {
    }

    private function getKey(string $variableName): string
    {
        $names = collect(explode('_', $variableName))
            ->map(fn(string $key) => strtolower($key))
            ->skip(1)
            ->all();

        return implode('.', [self::PREFIX, ...$names]);
    }

    public function retrieveValue(string $variableName)
    {
        return $this->config->get($this->getKey($variableName));
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->config->has($this->getKey($variableName));
    }
}

# Upgrade Guide

## Upgrading To 0.5 From 0.4.x

### Configuration Changes

The `telemetry.enabled` configuration key has been moved and negated to `sdk.disabled` to match the underlying
OpenTelemetry SDK.

Please update this value in your configuration if it has been published.

```diff
-    'enabled' => env('LARAVEL_TELEMETRY_ENABLED', true),
+    'sdk' => [
+        'disabled' => ! env('LARAVEL_TELEMETRY_ENABLED', true)
+    ],
```

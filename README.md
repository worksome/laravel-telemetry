# Telemetry support for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/worksome/laravel-telemetry.svg?style=flat-square)](https://packagist.org/packages/worksome/laravel-telemetry)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/worksome/laravel-telemetry/tests.yml?branch=main&style=flat-square&label=Tests)](https://github.com/worksome/laravel-telemetry/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub Static Analysis Action Status](https://img.shields.io/github/actions/workflow/status/worksome/laravel-telemetry/static.yml?branch=main&style=flat-square&label=Static%20Analysis)](https://github.com/worksome/laravel-telemetry/actions?query=workflow%3A"Static%20Analysis"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/worksome/laravel-telemetry.svg?style=flat-square)](https://packagist.org/packages/worksome/laravel-telemetry)

Add [Open Telemetry](https://opentelemetry.io) support to Laravel.

## Install

You can install the package via composer:

```shell
composer require worksome/laravel-telemetry
```

You can publish the config file with:

```shell
php artisan vendor:publish --tag="laravel-telemetry-config"
```

## Usage

This package will work out of the box with a default OTLP exporter configuration.

The default port is `4318`, and requests will be sent to `http://localhost:4318`.

## Testing

```bash
composer test
```

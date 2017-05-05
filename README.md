# Symfony extension for PHPStan

## What does it do?

* Provides correct return type for `ContainerInterface::get()` method,
* screams at you when you try to get unknown or private service from the container.

## Installation

```sh
composer require --dev lookyman/phpstan-symfony
```

## Configuration

Put this into your `phpstan.neon` config:

```neon
includes:
	- vendor/lookyman/phpstan-symfony/extension.neon
services:
	- Lookyman\PHPStan\Symfony\ServiceMap(/path/to/appDevDebugProjectContainer.xml)
```

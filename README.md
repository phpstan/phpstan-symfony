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
parameters:
	symfony:
		container_xml_path: %rootDir%/../../../var/cache/dev/srcDevDebugProjectContainer.xml
```

## Limitations

It can only recognize pure strings or `::class` constants passed into `ContainerInterface::get()` method. This follows from the nature of static code analysis.

You have to provide a path to `srcDevDebugProjectContainer.xml` or similar xml file describing your container.

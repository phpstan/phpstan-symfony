# Symfony extension for PHPStan

## What does it do?

* Provides correct return type for `ContainerInterface::get()` method,
* provides correct return type for `Controller::get()` method,
* notifies you when you try to get an unregistered service from the container,
* notifies you when you try to get a private service from the container.

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
		container_xml_path: %rootDir%/../../../var/cache/dev/srcDevDebugProjectContainer.xml # or appDevDebugProjectContainer.xml for Symfony < 4
```

## Limitations

It can only recognize pure strings or `::class` constants passed into `get()` method. This follows from the nature of static code analysis.

You have to provide a path to `srcDevDebugProjectContainer.xml` or similar xml file describing your container.

## Need something?

I don't use Symfony that often. So it might be entirely possible that something doesn't work here or that it lacks some functionality. If that's the case, **PLEASE DO NOT HESITATE** to open an issue or send a pull request. I will have a look at it and together we'll get you what you need. Thanks.

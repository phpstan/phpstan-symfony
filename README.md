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

## Limitations

It can only recognize pure strings passed into `ContainerInterface::get()` method. This follows from the nature of static code analysis.

You have to provide a path to `appDevDebugProjectContainer.xml` or similar xml file describing your container. To generate it, you have to run the `Sensio\Bundle\DistributionBundle\Composer\ScriptHandler::installAssets` composer script, which is part of Symfony's postinstall scripts. And to be able to run it, you have to have a database ready. Which kinda defeats the purpose of static code analysis, but whatever..

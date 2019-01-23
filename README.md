# PHPStan Symfony Framework extensions and rules

[![Build Status](https://travis-ci.org/phpstan/phpstan-symfony.svg)](https://travis-ci.org/phpstan/phpstan-symfony)
[![Latest Stable Version](https://poser.pugx.org/phpstan/phpstan-symfony/v/stable)](https://packagist.org/packages/phpstan/phpstan-symfony)
[![License](https://poser.pugx.org/phpstan/phpstan-symfony/license)](https://packagist.org/packages/phpstan/phpstan-symfony)

* [PHPStan](https://github.com/phpstan/phpstan)

This extension provides following features:

* Provides correct return type for `ContainerInterface::get()` and `::has()` methods.
* Provides correct return type for `Controller::get()` and `::has()` methods.
* Provides correct return type for `Request::getContent()` method based on the `$asResource` parameter.
* Notifies you when you try to get an unregistered service from the container.
* Notifies you when you try to get a private service from the container.

## Usage

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev phpstan/phpstan-symfony
```

And include extension.neon in your project's PHPStan config:

```
includes:
	- vendor/phpstan/phpstan-symfony/extension.neon
parameters:
	symfony:
		container_xml_path: %rootDir%/../../../var/cache/dev/srcDevDebugProjectContainer.xml
		# or with Symfony 4.2+
		container_xml_path: '%rootDir%/../../../var/cache/dev/srcApp_KernelDevDebugContainer.xml' 
```

You have to provide a path to `srcDevDebugProjectContainer.xml` or similar xml file describing your container.

## Constant hassers

Sometimes, when you are dealing with optional dependencies, the `::has()` methods can cause problems. For example, the following construct would complain that the condition is always either on or off, depending on whether you have the dependency for `service` installed:

```php
if ($this->has('service')) {
    // ...
}
```

In that case, you can disable the `::has()` method return type resolving like this: 

```
parameters:
	symfony:
		constant_hassers: false
```

Be aware that it may hide genuine errors in your application.

# PHPStan Symfony Framework extensions and rules

[![Build Status](https://travis-ci.org/phpstan/phpstan-symfony.svg)](https://travis-ci.org/phpstan/phpstan-symfony)
[![Latest Stable Version](https://poser.pugx.org/phpstan/phpstan-symfony/v/stable)](https://packagist.org/packages/phpstan/phpstan-symfony)
[![License](https://poser.pugx.org/phpstan/phpstan-symfony/license)](https://packagist.org/packages/phpstan/phpstan-symfony)

* [PHPStan](https://github.com/phpstan/phpstan)

This extension provides following features:

* Provides correct return type for `ContainerInterface::get()` and `::has()` methods.
* Provides correct return type for `Controller::get()` and `::has()` methods.
* Provides correct return type for `Request::getContent()` method based on the `$asResource` parameter.
* Provides correct return type for `HeaderBag::get()` method based on the `$first` parameter.
* Provides correct return type for `Envelope::all()` method based on the `$stampFqcn` parameter.
* Notifies you when you try to get an unregistered service from the container.
* Notifies you when you try to get a private service from the container.
* Optionally correct return types for `InputInterface::getArgument()`, `::getOption`, `::hasArgument`, and `::hasOption`.


## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev phpstan/phpstan-symfony
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```
includes:
    - vendor/phpstan/phpstan-symfony/extension.neon
```
</details>

# Configuration

You have to provide a path to `srcDevDebugProjectContainer.xml` or similar XML file describing your container.

```
parameters:
    symfony:
        container_xml_path: %rootDir%/../../../var/cache/dev/srcDevDebugProjectContainer.xml
        # or with Symfony 4.2+
        container_xml_path: '%rootDir%/../../../var/cache/dev/srcApp_KernelDevDebugContainer.xml'
```


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

## Console command analysis

You can opt in for more advanced analysis by providing the console application from your own application. This will allow the correct argument and option types to be inferred when accessing $input->getArgument() or $input->getOption().

```
parameters:
	symfony:
		console_application_loader: tests/console-application.php
```

For example, in a Symfony project, `console-application.php` would look something like this:

```php
require dirname(__DIR__).'/../config/bootstrap.php';
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
return new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
```

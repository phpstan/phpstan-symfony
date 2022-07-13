# PHPStan Symfony Framework extensions and rules

[![Build](https://github.com/phpstan/phpstan-symfony/workflows/Build/badge.svg)](https://github.com/phpstan/phpstan-symfony/actions)
[![Latest Stable Version](https://poser.pugx.org/phpstan/phpstan-symfony/v/stable)](https://packagist.org/packages/phpstan/phpstan-symfony)
[![License](https://poser.pugx.org/phpstan/phpstan-symfony/license)](https://packagist.org/packages/phpstan/phpstan-symfony)

* [PHPStan](https://phpstan.org/)

This extension provides following features:

* Provides correct return type for `ContainerInterface::get()` and `::has()` methods.
* Provides correct return type for `Controller::get()` and `::has()` methods.
* Provides correct return type for `AbstractController::get()` and `::has()` methods.
* Provides correct return type for `ContainerInterface::getParameter()` and `::hasParameter()` methods.
* Provides correct return type for `ParameterBagInterface::get()` and `::has()` methods.
* Provides correct return type for `Controller::getParameter()` method.
* Provides correct return type for `AbstractController::getParameter()` method.
* Provides correct return type for `Request::getContent()` method based on the `$asResource` parameter.
* Provides correct return type for `HeaderBag::get()` method based on the `$first` parameter.
* Provides correct return type for `Envelope::all()` method based on the `$stampFqcn` parameter.
* Provides correct return type for `InputBag::get()` method based on the `$default` parameter.
* Provides correct return type for `InputBag::all()` method based on the `$key` parameter.
* Provides correct return types for `TreeBuilder` and `NodeDefinition` objects.
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

To perform framework-specific checks, include also this file:

```
includes:
    - vendor/phpstan/phpstan-symfony/rules.neon
```
</details>

# Configuration

You have to provide a path to `srcDevDebugProjectContainer.xml` or similar XML file describing your container.

```yaml
parameters:
    symfony:
        containerXmlPath: var/cache/dev/srcDevDebugProjectContainer.xml
        # or with Symfony 4.2+
        containerXmlPath: var/cache/dev/srcApp_KernelDevDebugContainer.xml
        # or with Symfony 5+
        containerXmlPath: var/cache/dev/App_KernelDevDebugContainer.xml
    # If you're using PHP config files for Symfony 5.3+, you also need this for auto-loading of `Symfony\Config`:
    scanDirectories:
        - var/cache/dev/Symfony/Config
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
		constantHassers: false
```

Be aware that it may hide genuine errors in your application.

## Analysis of Symfony Console Commands

You can opt in for more advanced analysis of [Symfony Console Commands](https://symfony.com/doc/current/console.html)
by providing the console application from your own application. This will allow the correct argument and option types to be inferred when accessing `$input->getArgument()` or `$input->getOption()`.

```neon
parameters:
	symfony:
		consoleApplicationLoader: tests/console-application.php
```

Symfony 4:

```php
// tests/console-application.php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require __DIR__ . '/../config/bootstrap.php';
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
return new Application($kernel);
```

Symfony 5:

```php
// tests/console-application.php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
return new Application($kernel);
```

[Single Command Application](https://symfony.com/doc/current/components/console/single_command_tool.html):

```php
// tests/console-application.php

use App\Application; // where Application extends Symfony\Component\Console\SingleCommandApplication
use Symfony\Component\Console;

require __DIR__ . '/../vendor/autoload.php';

$application = new Console\Application();
$application->add(new Application());

return $application;
```

You may then encounter an error with PhpParser:

> Compile Error: Cannot Declare interface PhpParser\NodeVisitor, because the name is already in use

If this is the case, you should create a new environment for your application that will disable inlining. In `config/packages/phpstan_env/parameters.yaml`:

```yaml
parameters:
    container.dumper.inline_class_loader: false
```

Call the new env in your `console-application.php`:

```php
$kernel = new \App\Kernel('phpstan_env', (bool) $_SERVER['APP_DEBUG']);
```

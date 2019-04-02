<?php declare(strict_types = 1);

use PHPStan\Type\Symfony\ExampleACommand;
use PHPStan\Type\Symfony\ExampleBCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../../../vendor/autoload.php';

$application = new Application();
$application->add(new ExampleACommand());
$application->add(new ExampleBCommand());
return $application;

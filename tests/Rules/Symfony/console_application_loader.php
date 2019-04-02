<?php declare(strict_types = 1);

use PHPStan\Rules\Symfony\ExampleCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../../../vendor/autoload.php';

$application = new Application();
$application->add(new ExampleCommand());
return $application;

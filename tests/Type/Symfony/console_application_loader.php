<?php declare(strict_types = 1);

use PHPStan\Type\Symfony\ExampleACommand;
use PHPStan\Type\Symfony\ExampleBCommand;
use PHPStan\Type\Symfony\ExampleOptionCommand;
use PHPStan\Type\Symfony\ExampleOptionLazyCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;

require_once __DIR__ . '/../../../vendor/autoload.php';

$application = new Application();
$application->add(new ExampleACommand());
$application->add(new ExampleBCommand());
$application->add(new ExampleOptionCommand());

if (class_exists(LazyCommand::class)) {
	$application->add(new LazyCommand('lazy-example-option', [], '', false, function () {
		return new ExampleOptionLazyCommand();
	}));
} else {
	$application->add(new ExampleOptionLazyCommand());
}

return $application;

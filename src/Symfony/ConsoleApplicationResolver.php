<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use function file_exists;
use function get_class;
use function is_readable;
use function method_exists;
use function sprintf;

final class ConsoleApplicationResolver
{

	/** @var string|null */
	private $consoleApplicationLoader;

	/** @var Application|null */
	private $consoleApplication;

	public function __construct(Configuration $configuration)
	{
		$this->consoleApplicationLoader = $configuration->getConsoleApplicationLoader();
	}

	public function hasConsoleApplicationLoader(): bool
	{
		return $this->consoleApplicationLoader !== null;
	}

	private function getConsoleApplication(): ?Application
	{
		if ($this->consoleApplicationLoader === null) {
			return null;
		}

		if ($this->consoleApplication !== null) {
			return $this->consoleApplication;
		}

		if (!file_exists($this->consoleApplicationLoader)
			|| !is_readable($this->consoleApplicationLoader)
		) {
			throw new ShouldNotHappenException(sprintf('Cannot load console application. Check the parameters.symfony.consoleApplicationLoader setting in PHPStan\'s config. The offending value is "%s".', $this->consoleApplicationLoader));
		}

		return $this->consoleApplication = require $this->consoleApplicationLoader;
	}

	/**
	 * @return Command[]
	 */
	public function findCommands(ClassReflection $classReflection): array
	{
		$consoleApplication = $this->getConsoleApplication();
		if ($consoleApplication === null) {
			return [];
		}

		$classType = new ObjectType($classReflection->getName());
		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf($classType)->yes()) {
			return [];
		}

		$commands = [];
		foreach ($consoleApplication->all() as $name => $command) {
			$commandClass = new ObjectType(get_class($command));
			$isLazyCommand = (new ObjectType('Symfony\Component\Console\Command\LazyCommand'))->isSuperTypeOf($commandClass)->yes();

			if ($isLazyCommand && method_exists($command, 'getCommand')) {
				/** @var Command $wrappedCommand */
				$wrappedCommand = $command->getCommand();
				if (!$classType->isSuperTypeOf(new ObjectType(get_class($wrappedCommand)))->yes()) {
					continue;
				}
			}

			if (!$isLazyCommand && !$classType->isSuperTypeOf($commandClass)->yes()) {
				continue;
			}

			$commands[$name] = $command;
		}

		return $commands;
	}

}

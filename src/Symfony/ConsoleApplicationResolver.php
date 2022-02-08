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

	/** @var Application|null */
	private $consoleApplication;

	public function __construct(Configuration $configuration)
	{
		$consoleApplicationLoader = $configuration->getConsoleApplicationLoader();
		if ($consoleApplicationLoader === null) {
			return;
		}
		$this->consoleApplication = $this->loadConsoleApplication($consoleApplicationLoader);
	}

	/**
	 * @return Application|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	private function loadConsoleApplication(string $consoleApplicationLoader)
	{
		if (!file_exists($consoleApplicationLoader)
			|| !is_readable($consoleApplicationLoader)
		) {
			throw new ShouldNotHappenException(sprintf('Cannot load console application. Check the parameters.symfony.consoleApplicationLoader setting in PHPStan\'s config. The offending value is "%s".', $consoleApplicationLoader));
		}

		return require $consoleApplicationLoader;
	}

	/**
	 * @return Command[]
	 */
	public function findCommands(ClassReflection $classReflection): array
	{
		if ($this->consoleApplication === null) {
			return [];
		}

		$classType = new ObjectType($classReflection->getName());
		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf($classType)->yes()) {
			return [];
		}

		$commands = [];
		foreach ($this->consoleApplication->all() as $name => $command) {
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

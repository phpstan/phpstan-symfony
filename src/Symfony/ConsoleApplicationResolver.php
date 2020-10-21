<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use function file_exists;
use function get_class;
use function is_readable;

final class ConsoleApplicationResolver
{

	/** @var \Symfony\Component\Console\Application|null */
	private $consoleApplication;

	public function __construct(?string $consoleApplicationLoader)
	{
		if ($consoleApplicationLoader === null) {
			return;
		}
		$this->consoleApplication = $this->loadConsoleApplication($consoleApplicationLoader);
	}

	/**
	 * @return \Symfony\Component\Console\Application|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	private function loadConsoleApplication(string $consoleApplicationLoader)
	{
		if (!file_exists($consoleApplicationLoader)
			|| !is_readable($consoleApplicationLoader)
		) {
			throw new ShouldNotHappenException();
		}

		return require $consoleApplicationLoader;
	}

	/**
	 * @return \Symfony\Component\Console\Command\Command[]
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
			if (!$classType->isSuperTypeOf(new ObjectType(get_class($command)))->yes()) {
				continue;
			}
			$commands[$name] = $command;
		}

		return $commands;
	}

}

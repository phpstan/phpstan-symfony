<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use Symfony\Component\Console\Application;
use function file_exists;
use function get_class;
use function is_readable;

final class ConsoleApplicationResolver
{

	/** @var string|null */
	private $consoleApplicationLoader;

	/** @var \Symfony\Component\Console\Application|false|null */
	private $consoleApplication;

	public function __construct(?string $consoleApplicationLoader)
	{
		$this->consoleApplicationLoader = $consoleApplicationLoader;
	}

	/**
	 * @return \Symfony\Component\Console\Application|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
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

	public function getConsoleApplication(): ?Application
	{
		if ($this->consoleApplication === false) {
			return null;
		}

		if ($this->consoleApplication !== null) {
			return $this->consoleApplication;
		}

		if ($this->consoleApplicationLoader === null) {
			$this->consoleApplication = false;

			return null;
		}

		$this->consoleApplication = $this->loadConsoleApplication($this->consoleApplicationLoader);

		return $this->consoleApplication;
	}

	/**
	 * @return \Symfony\Component\Console\Command\Command[]
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
			if (!$classType->isSuperTypeOf(new ObjectType(get_class($command)))->yes()) {
				continue;
			}
			$commands[$name] = $command;
		}

		return $commands;
	}

}

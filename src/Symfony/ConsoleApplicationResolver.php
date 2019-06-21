<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use function file_exists;
use function get_class;
use function is_readable;

final class ConsoleApplicationResolver
{

	/** @var \Symfony\Component\Console\Application|null */
	private $consoleApplication;

	/**
	 * @phpcsSuppress SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
	 */
	public function __construct(?string $consoleApplicationLoader, ?string $consoleApplicationKernelClass = null)
	{
		if ($consoleApplicationLoader !== null) {
			$this->consoleApplication = $this->loadConsoleApplication($consoleApplicationLoader);
		} elseif ($consoleApplicationKernelClass !== null) {
			$this->consoleApplication = $this->createConsoleApplication($consoleApplicationKernelClass);
		}
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

	/**
	 * @return \Symfony\Component\Console\Application|null
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
	 */
	private function createConsoleApplication(string $consoleApplicationKernelClass)
	{
		if (!class_exists($consoleApplicationKernelClass)
			|| !is_a($consoleApplicationKernelClass, 'Symfony\Component\HttpKernel\KernelInterface')
		) {
			throw new ShouldNotHappenException();
		}
		$kernel = new $consoleApplicationKernelClass($_SERVER['APP_ENV'] ?? 'dev', (bool) $_SERVER['APP_DEBUG'] ?? true);

		return new Application($kernel);
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

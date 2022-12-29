<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use Throwable;
use function count;
use function get_class;

final class CommandGetHelperDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var ConsoleApplicationResolver */
	private $consoleApplicationResolver;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
	}

	public function getClass(): string
	{
		return 'Symfony\Component\Console\Command\Command';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getHelper';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		if (!isset($methodCall->getArgs()[0])) {
			return null;
		}

		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return null;
		}

		$argStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->getArgs()[0]->value));
		if (count($argStrings) !== 1) {
			return null;
		}
		$argName = $argStrings[0]->getValue();

		$returnTypes = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $command) {
			try {
				$command->mergeApplicationDefinition();
				$returnTypes[] = new ObjectType(get_class($command->getHelper($argName)));
			} catch (Throwable $e) {
				// no-op
			}
		}

		return count($returnTypes) > 0 ? TypeCombinator::union(...$returnTypes) : null;
	}

}

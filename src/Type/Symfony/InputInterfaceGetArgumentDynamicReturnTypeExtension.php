<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use InvalidArgumentException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use function count;
use function in_array;

final class InputInterfaceGetArgumentDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var ConsoleApplicationResolver */
	private $consoleApplicationResolver;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
	}

	public function getClass(): string
	{
		return 'Symfony\Component\Console\Input\InputInterface';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getArgument';
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

		$argTypes = [];
		$canBeNullInInteract = false;
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $command) {
			try {
				$command->mergeApplicationDefinition();
				$argument = $command->getDefinition()->getArgument($argName);
				if ($argument->isArray()) {
					$argType = new ArrayType(new IntegerType(), new StringType());
					if (!$argument->isRequired() && $argument->getDefault() !== []) {
						$argType = TypeCombinator::union($argType, $scope->getTypeFromValue($argument->getDefault()));
					}
				} else {
					$argType = new StringType();
					if (!$argument->isRequired()) {
						$argType = TypeCombinator::union($argType, $scope->getTypeFromValue($argument->getDefault()));
					} else {
						$canBeNullInInteract = true;
					}
				}
				$argTypes[] = $argType;
			} catch (InvalidArgumentException $e) {
				// noop
			}
		}

		if (count($argTypes) === 0) {
			return null;
		}

		$method = $scope->getFunction();
		if (
			$canBeNullInInteract
			&& $method instanceof MethodReflection
			&& ($method->getName() === 'interact' || $method->getName() === 'initialize')
			&& in_array('Symfony\Component\Console\Command\Command', $method->getDeclaringClass()->getParentClassesNames(), true)
		) {
			$argTypes[] = new NullType();
		}

		return TypeCombinator::union(...$argTypes);
	}

}

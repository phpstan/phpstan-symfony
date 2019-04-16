<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use InvalidArgumentException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use function count;

final class InputInterfaceGetOptionDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var \PHPStan\Symfony\ConsoleApplicationResolver */
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
		return $methodReflection->getName() === 'getOption';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		$defaultReturnType = ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->args, $methodReflection->getVariants())->getReturnType();

		if (!isset($methodCall->args[0])) {
			return $defaultReturnType;
		}

		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return $defaultReturnType;
		}

		$optStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->args[0]->value));
		if (count($optStrings) !== 1) {
			return $defaultReturnType;
		}
		$optName = $optStrings[0]->getValue();

		$optTypes = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $command) {
			try {
				$option = $command->getDefinition()->getOption($optName);
				if (!$option->acceptValue()) {
					$optType = new BooleanType();
				} else {
					$optType = TypeCombinator::union(new StringType(), new IntegerType(), new NullType());
					if ($option->isValueRequired() && ($option->isArray() || $option->getDefault() !== null)) {
						$optType = TypeCombinator::removeNull($optType);
					}
					if ($option->isArray()) {
						$optType = new ArrayType(new IntegerType(), TypeCombinator::remove($optType, new IntegerType()));
					}
					$optType = TypeCombinator::union($optType, $scope->getTypeFromValue($option->getDefault()));
				}
				$optTypes[] = $optType;
			} catch (InvalidArgumentException $e) {
				// noop
			}
		}

		return count($optTypes) > 0 ? TypeCombinator::union(...$optTypes) : $defaultReturnType;
	}

}

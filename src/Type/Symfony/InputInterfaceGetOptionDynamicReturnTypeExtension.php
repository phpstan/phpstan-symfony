<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use InvalidArgumentException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use function count;

final class InputInterfaceGetOptionDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var ConsoleApplicationResolver */
	private $consoleApplicationResolver;

	/** @var GetOptionTypeHelper */
	private $getOptionTypeHelper;

	public function __construct(ConsoleApplicationResolver $consoleApplicationResolver, GetOptionTypeHelper $getOptionTypeHelper)
	{
		$this->consoleApplicationResolver = $consoleApplicationResolver;
		$this->getOptionTypeHelper = $getOptionTypeHelper;
	}

	public function getClass(): string
	{
		return 'Symfony\Component\Console\Input\InputInterface';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getOption';
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

		$optStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->getArgs()[0]->value));
		if (count($optStrings) !== 1) {
			return null;
		}
		$optName = $optStrings[0]->getValue();

		$optTypes = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $command) {
			try {
				$command->mergeApplicationDefinition();
				$option = $command->getDefinition()->getOption($optName);
				$optTypes[] = $this->getOptionTypeHelper->getOptionType($scope, $option);
			} catch (InvalidArgumentException $e) {
				// noop
			}
		}

		return count($optTypes) > 0 ? TypeCombinator::union(...$optTypes) : null;
	}

}

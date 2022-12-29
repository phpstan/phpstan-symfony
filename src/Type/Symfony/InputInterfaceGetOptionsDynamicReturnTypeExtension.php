<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use InvalidArgumentException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;

final class InputInterfaceGetOptionsDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
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
		return $methodReflection->getName() === 'getOptions';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			return null;
		}

		$optTypes = [];
		foreach ($this->consoleApplicationResolver->findCommands($classReflection) as $command) {
			try {
				$command->mergeApplicationDefinition();
				$options = $command->getDefinition()->getOptions();
				$builder = ConstantArrayTypeBuilder::createEmpty();
				foreach ($options as $name => $option) {
					$optionType = $this->getOptionTypeHelper->getOptionType($scope, $option);
					$builder->setOffsetValueType(new ConstantStringType($name), $optionType);
				}

				$optTypes[] = $builder->getArray();
			} catch (InvalidArgumentException $e) {
				// noop
			}
		}

		return count($optTypes) > 0 ? TypeCombinator::union(...$optTypes) : null;
	}

}

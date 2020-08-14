<?php declare(strict_types=1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

final class InputBagDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
	public function getClass(): string
	{
		return 'Symfony\Component\HttpFoundation\InputBag';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'get';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (isset($methodCall->args[1]) && $scope->getType($methodCall->args[1]->value) instanceof ConstantStringType) {
			return new StringType();
		}

		return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
	}
}

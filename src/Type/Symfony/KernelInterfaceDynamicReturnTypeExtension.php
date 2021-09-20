<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

final class KernelInterfaceDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Component\HttpKernel\KernelInterface';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'locateResource';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$firstArgType = isset($methodCall->getArgs()[2]) ? $scope->getType($methodCall->getArgs()[2]->value) : new ConstantBooleanType(true);
		$isTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($firstArgType);
		$isFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($firstArgType);
		$compareTypes = $isTrueType->compareTo($isFalseType);

		if ($compareTypes === $isTrueType) {
			return new StringType();
		}
		if ($compareTypes === $isFalseType) {
			return new ArrayType(new IntegerType(), new StringType());
		}

		return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
	}

}

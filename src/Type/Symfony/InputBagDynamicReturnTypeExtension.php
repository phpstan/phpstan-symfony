<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class InputBagDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Component\HttpFoundation\InputBag';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), ['get', 'all'], true);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if ($methodReflection->getName() === 'get') {
			return $this->getGetTypeFromMethodCall($methodReflection, $methodCall, $scope);
		}

		if ($methodReflection->getName() === 'all') {
			return $this->getAllTypeFromMethodCall($methodCall);
		}

		throw new ShouldNotHappenException();
	}

	private function getGetTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (isset($methodCall->args[1])) {
			$argType = $scope->getType($methodCall->args[1]->value);
			$isNull = (new NullType())->isSuperTypeOf($argType);
			$isString = (new StringType())->isSuperTypeOf($argType);
			$compare = $isNull->compareTo($isString);
			if ($compare === $isString) {
				return new StringType();
			}
		}

		return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
	}

	private function getAllTypeFromMethodCall(
		MethodCall $methodCall
	): Type
	{
		if (isset($methodCall->args[0])) {
			return new ArrayType(new MixedType(), new StringType());
		}

		return new ArrayType(new StringType(), new UnionType([new StringType(), new ArrayType(new MixedType(), new StringType())]));
	}

}

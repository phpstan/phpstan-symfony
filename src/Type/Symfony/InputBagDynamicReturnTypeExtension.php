<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use function in_array;

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
		if (isset($methodCall->getArgs()[1])) {
			$argType = $scope->getType($methodCall->getArgs()[1]->value);
			$isNull = (new NullType())->isSuperTypeOf($argType);
			if ($isNull->no()) {
				return TypeCombinator::removeNull(ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType());
			}
		}

		return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
	}

	private function getAllTypeFromMethodCall(
		MethodCall $methodCall
	): Type
	{
		if (isset($methodCall->getArgs()[0])) {
			return new ArrayType(new MixedType(), new MixedType(true));
		}

		return new ArrayType(new StringType(), new UnionType([new ArrayType(new MixedType(), new MixedType(true)), new BooleanType(), new FloatType(), new IntegerType(), new StringType()]));
	}

}

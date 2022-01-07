<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Form;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

final class FormInterfaceDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return FormInterface::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getErrors';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (!isset($methodCall->getArgs()[1])) {
			return new GenericObjectType(FormErrorIterator::class, [new ObjectType(FormError::class)]);
		}

		$firstArgType = $scope->getType($methodCall->getArgs()[0]->value);
		$secondArgType = $scope->getType($methodCall->getArgs()[1]->value);

		$firstIsTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($firstArgType);
		$firstIsFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($firstArgType);
		$secondIsTrueType = (new ConstantBooleanType(true))->isSuperTypeOf($secondArgType);
		$secondIsFalseType = (new ConstantBooleanType(false))->isSuperTypeOf($secondArgType);

		$firstCompareType = $firstIsTrueType->compareTo($firstIsFalseType);
		$secondCompareType = $secondIsTrueType->compareTo($secondIsFalseType);

		if ($firstCompareType === $firstIsTrueType && $secondCompareType === $secondIsFalseType) {
			return new GenericObjectType(FormErrorIterator::class, [
				new UnionType([
					new ObjectType(FormError::class),
					new ObjectType(FormErrorIterator::class),
				]),
			]);
		}

		return new GenericObjectType(FormErrorIterator::class, [new ObjectType(FormError::class)]);
	}

}

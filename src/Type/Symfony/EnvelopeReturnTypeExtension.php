<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function count;

final class EnvelopeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Component\Messenger\Envelope';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'all';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (count($methodCall->getArgs()) === 0) {
			return new ArrayType(
				new GenericClassStringType(new ObjectType('Symfony\Component\Messenger\Stamp\StampInterface')),
				AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), new ObjectType('Symfony\Component\Messenger\Stamp\StampInterface')))
			);
		}

		$argType = $scope->getType($methodCall->getArgs()[0]->value);
		if (!$argType instanceof ConstantStringType) {
			return AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), new ObjectType('Symfony\Component\Messenger\Stamp\StampInterface')));
		}

		return AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), new ObjectType($argType->getValue())));
	}

}

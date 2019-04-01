<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

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
		if (count($methodCall->args) === 0) {
			return new ArrayType(new MixedType(), new ArrayType(new MixedType(), new ObjectType('Symfony\Component\Messenger\Stamp\StampInterface')));
		}

		$argType = $scope->getType($methodCall->args[0]->value);
		if (!$argType instanceof ConstantStringType) {
			return new ArrayType(new MixedType(), new ObjectType('Symfony\Component\Messenger\Stamp\StampInterface'));
		}

		return new ArrayType(new MixedType(), new ObjectType($argType->getValue()));
	}

}

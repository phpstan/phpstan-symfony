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

class SerializerInterfaceDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Component\Serializer\SerializerInterface';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'deserialize';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		$argType = $scope->getType($methodCall->args[1]->value);
		if (!$argType instanceof ConstantStringType) {
			return new MixedType();
		}

		$objectName = $argType->getValue();

		if (substr($objectName, -2) === '[]') {
			// The key type is determined by the data
			return new ArrayType(new MixedType(false), new ObjectType(substr($objectName, 0, -2)));
		}

		return new ObjectType($objectName);
	}

}

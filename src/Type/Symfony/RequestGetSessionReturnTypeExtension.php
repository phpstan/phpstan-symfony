<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class RequestGetSessionReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Component\HttpFoundation\Request';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getSession';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		return new ObjectType('Symfony\Component\HttpFoundation\Session\Session');
	}

}

<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class ResponseHeaderBagDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return ResponseHeaderBag::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getCookies';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		if (isset($methodCall->getArgs()[0])) {
			$argStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->getArgs()[0]->value));

			if (count($argStrings) === 1 && $argStrings[0]->getValue() === ResponseHeaderBag::COOKIES_ARRAY) {
				return new ArrayType(
					new StringType(),
					new ArrayType(
						new StringType(),
						new ArrayType(
							new StringType(),
							new ObjectType(Cookie::class)
						)
					)
				);
			}
		}

		return new ArrayType(new IntegerType(), new ObjectType(Cookie::class));
	}

}

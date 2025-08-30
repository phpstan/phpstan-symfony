<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function count;

final class BrowserKitAssertionTraitReturnTypeExtension implements ExpressionTypeResolverExtension
{

	private const TRAIT_NAME = 'Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait';
	private const TRAIT_METHOD_NAME = 'getClient';

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if ($this->isSupported($expr, $scope)) {
			$args = $expr->getArgs();
			if (count($args) > 0) {
				return $scope->getType($args[0]->value);
			}

			return new ObjectType('Symfony\Component\BrowserKit\AbstractBrowser');
		}

		return null;
	}

	/**
	 * @phpstan-assert-if-true =MethodCall $expr
	 */
	private function isSupported(Expr $expr, Scope $scope): bool
	{
		if (!($expr instanceof MethodCall) || !($expr->name instanceof Identifier) || $expr->name->name !== self::TRAIT_METHOD_NAME) {
			return false;
		}

		if (!$scope->isInClass()) {
			return false;
		}

		$reflectionClass = $scope->getClassReflection()->getNativeReflection();

		if (!$reflectionClass->hasMethod(self::TRAIT_METHOD_NAME)) {
			return false;
		}

		$methodReflection = $reflectionClass->getMethod(self::TRAIT_METHOD_NAME);
		$declaringClassReflection = $methodReflection->getBetterReflection()->getDeclaringClass();

		return $declaringClassReflection->getName() === self::TRAIT_NAME;
	}

}

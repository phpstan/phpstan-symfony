<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use function count;

final class BrowserKitAssertionTraitReturnTypeExtension implements ExpressionTypeResolverExtension
{

	private const TRAIT_NAME = 'Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait';
	private const TRAIT_METHOD_NAME = 'getclient';

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if ($this->isSupported($expr, $scope)) {
			$args = $expr->getArgs();
			if (count($args) > 0) {
				return TypeCombinator::intersect(
					$scope->getType($args[0]->value),
					new UnionType([
						new ObjectType('Symfony\Component\BrowserKit\AbstractBrowser'),
						new NullType(),
					]),
				);
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
		if (!($expr instanceof MethodCall) || !($expr->name instanceof Identifier) || $expr->name->toLowerString() !== self::TRAIT_METHOD_NAME) {
			return false;
		}

		if (!$scope->isInClass()) {
			return false;
		}

		$methodReflection = $scope->getMethodReflection($scope->getType($expr->var), $expr->name->toString());
		if ($methodReflection === null) {
			return false;
		}

		$reflectionClass = $methodReflection->getDeclaringClass()->getNativeReflection();
		if (!$reflectionClass->hasMethod(self::TRAIT_METHOD_NAME)) {
			return false;
		}

		$traitMethodReflection = $reflectionClass->getMethod(self::TRAIT_METHOD_NAME);
		$declaringClassReflection = $traitMethodReflection->getBetterReflection()->getDeclaringClass();

		return $declaringClassReflection->getName() === self::TRAIT_NAME;
	}

}

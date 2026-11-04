<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;
use function is_string;

final class AutowireLocatorTypeExtension implements ExpressionTypeResolverExtension
{

	private ServiceLocatorMapResolver $resolver;

	private bool $resolving = false;

	public function __construct(ServiceLocatorMapResolver $resolver)
	{
		$this->resolver = $resolver;
	}

	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if ($this->resolving) {
			return null;
		}
		$map = null;
		$declared = null;
		if ($expr instanceof Variable && is_string($expr->name) && !$scope->isInAnonymousFunction()) {
			$method = $scope->getFunction();
			if (!$method instanceof ExtendedMethodReflection) {
				return null;
			}
			foreach ($method->getVariants()[0]->getParameters() as $parameter) {
				if ($parameter->getName() === $expr->name) {
					$declared = $parameter->getType();
					$map = $this->resolver->parameter($method->getDeclaringClass(), $method->getName(), $expr->name);
					break;
				}
			}
		} elseif ($expr instanceof PropertyFetch && $expr->name instanceof Identifier) {
			$classes = $scope->getType($expr->var)->getObjectClassReflections();
			if (count($classes) !== 1 || !$classes[0]->hasProperty($expr->name->name)) {
				return null;
			}
			$property = $classes[0]->getProperty($expr->name->name, $scope);
			$declared = $property->getReadableType();
			$class = $expr->name->name === 'container' ? $classes[0] : $property->getDeclaringClass();
			$map = $this->resolver->property($class, $expr->name->name);
		}
		if ($map === null || $declared === null) {
			return null;
		}
		$this->resolving = true;
		try {
			$original = $scope->getType($expr);
		} finally {
			$this->resolving = false;
		}
		if (ServiceLocatorTypeHelper::getServices($original) !== null || $original->getObjectClassNames() !== $declared->getObjectClassNames()) {
			return null;
		}
		$type = new GenericObjectType('Psr\Container\ContainerInterface', [$map]);
		if ($original->getObjectClassNames() === ['Psr\Container\ContainerInterface']) {
			return TypeCombinator::containsNull($original) ? TypeCombinator::union($type, new NullType()) : $type;
		}
		return TypeCombinator::intersect($original, TypeCombinator::union($type, new NullType()));
	}

	/** @param Scope&DependencyTracker $scope */
	public function getServices(Expr $expr, Scope $scope): ?Type
	{
		$services = ServiceLocatorTypeHelper::getServices($scope->getType($expr));
		if ($services !== null) {
			return $services;
		}
		$type = $this->getType($expr, $scope);
		return $type === null ? null : ServiceLocatorTypeHelper::getServices($type);
	}

}

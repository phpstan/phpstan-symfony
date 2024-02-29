<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function str_contains;
use function strrpos;
use function substr_replace;

class ExtensionGetConfigurationReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var ReflectionProvider */
	private $reflectionProvider;

	public function __construct(ReflectionProvider $reflectionProvider)
	{
		$this->reflectionProvider = $reflectionProvider;
	}

	public function getClass(): string
	{
		return 'Symfony\Component\DependencyInjection\Extension\Extension';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getConfiguration'
			&& $methodReflection->getDeclaringClass()->getName() === 'Symfony\Component\DependencyInjection\Extension\Extension';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): ?Type
	{
		$types = [];
		$extensionType = $scope->getType($methodCall->var);
		$classes = $extensionType->getObjectClassNames();

		foreach ($classes as $extensionName) {
			if (str_contains($extensionName, "\0")) {
				$types[] = new NullType();
				continue;
			}

			$lastBackslash = strrpos($extensionName, '\\');
			if ($lastBackslash === false) {
				$types[] = new NullType();
				continue;
			}

			$configurationName = substr_replace($extensionName, '\Configuration', $lastBackslash);
			if (!$this->reflectionProvider->hasClass($configurationName)) {
				$types[] = new NullType();
				continue;
			}

			$reflection = $this->reflectionProvider->getClass($configurationName);
			if ($this->hasRequiredConstructor($reflection)) {
				$types[] = new NullType();
				continue;
			}

			$types[] = new ObjectType($configurationName);
		}

		return TypeCombinator::union(...$types);
	}

	private function hasRequiredConstructor(ClassReflection $class): bool
	{
		if (!$class->hasConstructor()) {
			return false;
		}

		$constructor = $class->getConstructor();
		foreach ($constructor->getVariants() as $variant) {
			$anyRequired = false;
			foreach ($variant->getParameters() as $parameter) {
				if (!$parameter->isOptional()) {
					$anyRequired = true;
					break;
				}
			}

			if (!$anyRequired) {
				return false;
			}
		}

		return true;
	}

}

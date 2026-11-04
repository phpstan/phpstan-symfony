<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Reflection\AttributeReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;
use function explode;
use function sprintf;
use function strpos;

final class AutowireCallableTypeResolver
{

	private ReflectionProvider $reflectionProvider;

	private ServiceMap $serviceMap;

	public function __construct(ReflectionProvider $reflectionProvider, ServiceMap $serviceMap)
	{
		$this->reflectionProvider = $reflectionProvider;
		$this->serviceMap = $serviceMap;
	}

	public function isUnspecifiedCallable(Type $type): bool
	{
		$type = TypeCombinator::removeNull($type);
		return $type->equals(new ClosureType()) || $type->equals(new CallableType()) || $type->equals(new ObjectType('Closure'));
	}

	/**
	 * @param list<AttributeReflection> $attributes
	 * @return array{type: ?Type, error: ?string}|null
	 */
	public function resolve(array $attributes, string $parameterName, Scope $scope): ?array
	{
		foreach ($attributes as $attribute) {
			$arguments = $attribute->getArgumentTypes();
			$name = $attribute->getName();
			if ($name === 'Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure') {
				$serviceType = $this->getServiceType($this->getString($arguments['service'] ?? null));
				return $serviceType === null ? null : ['type' => new ClosureType([], $serviceType), 'error' => null];
			}
			if ($name === 'Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf') {
				return $this->getMethodType($this->getServiceType($this->getString($arguments['service'] ?? null)), $parameterName, $scope);
			}
			if ($name !== 'Symfony\Component\DependencyInjection\Attribute\AutowireCallable') {
				continue;
			}

			$service = $this->getString($arguments['service'] ?? null);
			if ($service !== null) {
				return $this->getMethodType($this->getServiceType($service), $this->getString($arguments['method'] ?? null) ?? '__invoke', $scope);
			}

			$callable = $arguments['callable'] ?? null;
			if ($callable === null) {
				return null;
			}
			$string = $this->getString($callable);
			if ($string !== null) {
				if (strpos($string, '::') !== false) {
					[$class, $method] = explode('::', $string, 2);
					return $this->getStaticMethodType($class, $method, $scope);
				}
				$function = new FullyQualified($string);
				return $this->reflectionProvider->hasFunction($function, null)
					? ['type' => $scope->getType(new FuncCall($function, [new VariadicPlaceholder()])), 'error' => null]
					: ['type' => null, 'error' => sprintf('Function %s() does not exist.', $string)];
			}
			$arrays = $callable->getConstantArrays();
			if (count($arrays) !== 1) {
				return null;
			}
			$values = $arrays[0]->getValueTypes();
			if (count($values) !== 2) {
				return null;
			}
			$class = $this->getString($values[0]);
			$method = $this->getString($values[1]);
			return $class !== null && $method !== null ? $this->getStaticMethodType($class, $method, $scope) : null;
		}

		return null;
	}

	public function checkType(Type $callable, Type $expectedType, Scope $scope): ?string
	{
		$classes = $expectedType->getObjectClassReflections();
		if (count($classes) === 1 && $classes[0]->getName() !== 'Closure') {
			$class = $classes[0];
			if (!$class->isInterface()) {
				return sprintf('Adapter type %s is not an interface.', $class->getName());
			}
			$methods = $class->getNativeReflection()->getMethods();
			if (count($methods) !== 1) {
				return sprintf('Adapter interface %s must have exactly one method.', $class->getName());
			}
			$variant = $expectedType->getMethod($methods[0]->getName(), $scope)->getVariants()[0];
			$contract = new CallableType($variant->getParameters(), $variant->getReturnType(), $variant->isVariadic());
			return $contract->isSuperTypeOf($callable)->no() ? sprintf('Callable signature is incompatible with %s::%s().', $class->getName(), $methods[0]->getName()) : null;
		}

		return $expectedType->isSuperTypeOf($callable)->no() ? 'Callable signature is incompatible with the parameter type.' : null;
	}

	private function getString(?Type $type): ?string
	{
		if ($type === null) {
			return null;
		}
		$strings = $type->getConstantStrings();
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

	private function getServiceType(?string $id): ?ObjectType
	{
		if ($id === null) {
			return null;
		}
		$service = $this->serviceMap->getService($id);
		$class = $service !== null ? $service->getClass() : $id;
		return $class !== null && $this->reflectionProvider->hasClass($class) ? new ObjectType($class) : null;
	}

	/** @return array{type: ?Type, error: ?string}|null */
	private function getMethodType(?ObjectType $type, string $method, Scope $scope, bool $static = false): ?array
	{
		if ($type === null) {
			return null;
		}
		if (!$type->hasMethod($method)->yes()) {
			$magic = $static ? '__callStatic' : '__call';
			if ($type->hasMethod($magic)->yes() && $type->getMethod($magic, $scope)->isPublic()) {
				return null;
			}
			return ['type' => null, 'error' => sprintf('Method %s::%s() does not exist.', $type->getClassName(), $method)];
		}
		if (!$type->getMethod($method, $scope)->isPublic()) {
			return ['type' => null, 'error' => sprintf('Method %s::%s() is not public.', $type->getClassName(), $method)];
		}

		return ['type' => $scope->getType(new MethodCall(new TypeExpr($type), $method, [new VariadicPlaceholder()])), 'error' => null];
	}

	/** @return array{type: ?Type, error: ?string}|null */
	private function getStaticMethodType(string $class, string $method, Scope $scope): ?array
	{
		if (!$this->reflectionProvider->hasClass($class)) {
			return null;
		}
		$type = new ObjectType($class);
		$result = $this->getMethodType($type, $method, $scope, true);
		if ($result === null || $result['error'] !== null) {
			return $result;
		}
		if (!$type->getMethod($method, $scope)->isStatic()) {
			return ['type' => null, 'error' => sprintf('Method %s::%s() is not static.', $class, $method)];
		}

		return ['type' => $scope->getType(new StaticCall(new FullyQualified($class), $method, [new VariadicPlaceholder()])), 'error' => null];
	}

}

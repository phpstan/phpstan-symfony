<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\ParameterMap;
use PHPStan\Symfony\ServiceDefinition;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Symfony\ServiceResultCacheValueExtension;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use function class_exists;
use function in_array;
use function is_string;

final class ServiceDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var class-string */
	private string $className;

	private bool $constantHassers;

	private ServiceMap $serviceMap;

	private ParameterMap $parameterMap;

	private ?AutowireLocatorTypeExtension $locatorTypeExtension;

	private ?ParameterBag $parameterBag = null;

	/**
	 * @param class-string $className
	 */
	public function __construct(
		string $className,
		bool $constantHassers,
		ServiceMap $symfonyServiceMap,
		ParameterMap $symfonyParameterMap,
		?AutowireLocatorTypeExtension $locatorTypeExtension = null
	)
	{
		$this->className = $className;
		$this->constantHassers = $constantHassers;
		$this->serviceMap = $symfonyServiceMap;
		$this->parameterMap = $symfonyParameterMap;
		$this->locatorTypeExtension = $locatorTypeExtension;
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), ['get', 'has'], true);
	}

	/**
	 * @param Scope&DependencyTracker $scope
	 */
	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		switch ($methodReflection->getName()) {
			case 'get':
				return $this->getGetTypeFromMethodCall($methodCall, $scope);
			case 'has':
				return $this->getHasTypeFromMethodCall($methodCall, $scope);
		}
		throw new ShouldNotHappenException();
	}

	/**
	 * @param Scope&DependencyTracker $scope
	 */
	private function getGetTypeFromMethodCall(
		MethodCall $methodCall,
		Scope $scope
	): ?Type
	{
		if (!isset($methodCall->getArgs()[0])) {
			return null;
		}

		$services = $this->locatorTypeExtension !== null ? $this->locatorTypeExtension->getServices($methodCall->var, $scope) : null;
		if ($services !== null) {
			$key = $scope->getType($methodCall->getArgs()[0]->value);
			return $services->hasOffsetValueType($key)->no() ? new MixedType() : $services->getOffsetValueType($key);
		}

		$parameterBag = $this->tryGetParameterBag();
		if ($parameterBag === null) {
			return null;
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($methodCall->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$scope->trackValueDependency(ServiceResultCacheValueExtension::class, $serviceId);
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && (!$service->isSynthetic() || $service->getClass() !== null)) {
				return new ObjectType($this->determineServiceClass($parameterBag, $service) ?? $serviceId);
			}
		}

		return null;
	}

	private function tryGetParameterBag(): ?ParameterBag
	{
		if ($this->parameterBag !== null) {
			return $this->parameterBag;
		}

		return $this->parameterBag = $this->tryCreateParameterBag();
	}

	private function tryCreateParameterBag(): ?ParameterBag
	{
		if (!class_exists(ParameterBag::class)) {
			return null;
		}

		$parameters = [];

		foreach ($this->parameterMap->getParameters() as $parameterDefinition) {
			$parameters[$parameterDefinition->getKey()] = $parameterDefinition->getValue();
		}

		return new ParameterBag($parameters);
	}

	/**
	 * @param Scope&DependencyTracker $scope
	 */
	private function getHasTypeFromMethodCall(
		MethodCall $methodCall,
		Scope $scope
	): ?Type
	{
		if (!isset($methodCall->getArgs()[0]) || !$this->constantHassers) {
			return null;
		}

		$services = $this->locatorTypeExtension !== null ? $this->locatorTypeExtension->getServices($methodCall->var, $scope) : null;
		if ($services !== null) {
			$has = $services->hasOffsetValueType($scope->getType($methodCall->getArgs()[0]->value));
			return $has->maybe() ? new BooleanType() : new ConstantBooleanType($has->yes());
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($methodCall->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$scope->trackValueDependency(ServiceResultCacheValueExtension::class, $serviceId);
			$service = $this->serviceMap->getService($serviceId);
			return new ConstantBooleanType($service !== null && $service->isPublic());
		}

		return null;
	}

	private function determineServiceClass(ParameterBag $parameterBag, ServiceDefinition $service): ?string
	{
		$class = $service->getClass();
		if ($class === null) {
			return null;
		}

		$value = $parameterBag->resolveValue($class);
		if (!is_string($value)) {
			return null;
		}

		return $value;
	}

}

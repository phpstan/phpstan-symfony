<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\ParameterMap;
use PHPStan\Symfony\ServiceDefinition;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use function class_exists;
use function in_array;
use function is_string;

final class ServiceDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var string */
	private $className;

	/** @var bool */
	private $constantHassers;

	/** @var ServiceMap */
	private $serviceMap;

	/** @var ParameterMap */
	private $parameterMap;

	/** @var ParameterBag|null */
	private $parameterBag;

	public function __construct(
		string $className,
		Configuration $configuration,
		ServiceMap $symfonyServiceMap,
		ParameterMap $symfonyParameterMap
	)
	{
		$this->className = $className;
		$this->constantHassers = $configuration->hasConstantHassers();
		$this->serviceMap = $symfonyServiceMap;
		$this->parameterMap = $symfonyParameterMap;
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), ['get', 'has'], true);
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		switch ($methodReflection->getName()) {
			case 'get':
				return $this->getGetTypeFromMethodCall($methodReflection, $methodCall, $scope);
			case 'has':
				return $this->getHasTypeFromMethodCall($methodReflection, $methodCall, $scope);
		}
		throw new ShouldNotHappenException();
	}

	private function getGetTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->getArgs()[0])) {
			return $returnType;
		}

		$parameterBag = $this->tryGetParameterBag();
		if ($parameterBag === null) {
			return $returnType;
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($methodCall->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && (!$service->isSynthetic() || $service->getClass() !== null)) {
				return new ObjectType($this->determineServiceClass($parameterBag, $service) ?? $serviceId);
			}
		}

		return $returnType;
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

	private function getHasTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->getArgs()[0]) || !$this->constantHassers) {
			return $returnType;
		}

		$serviceId = $this->serviceMap::getServiceIdFromNode($methodCall->getArgs()[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			return new ConstantBooleanType($service !== null && $service->isPublic());
		}

		return $returnType;
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

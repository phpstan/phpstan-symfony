<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class ControllerDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var ServiceMap */
	private $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap)
	{
		$this->serviceMap = $symfonyServiceMap;
	}

	public function getClass(): string
	{
		return 'Symfony\Bundle\FrameworkBundle\Controller\Controller';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'get';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->args[0])) {
			return $returnType;
		}

		$serviceId = ServiceMap::getServiceIdFromNode($methodCall->args[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $this->serviceMap->getService($serviceId);
			if ($service !== null && $service['synthetic'] === false) {
				return new ObjectType($service['class'] ?? $service['id']);
			}
		}

		return $returnType;
	}

}

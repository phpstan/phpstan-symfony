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
use PHPStan\Type\TypeUtils;

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
		return in_array($methodReflection->getName(), ['get', 'has', 'createForm'], true);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		switch ($methodReflection->getName()) {
			case 'get':
				return Helper::getGetTypeFromMethodCall($methodReflection, $methodCall, $scope, $this->serviceMap);
			case 'has':
				return Helper::getHasTypeFromMethodCall($methodReflection, $methodCall, $scope, $this->serviceMap);
			case 'createForm':
				return $this->getCreateFormTypeFromMethodCall($methodReflection, $methodCall, $scope);
		}
		throw new \PHPStan\ShouldNotHappenException();
	}

	private function getCreateFormTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->args[0])) {
			return $returnType;
		}

		$types = TypeUtils::getConstantStrings($scope->getType($methodCall->args[0]->value));
		if (count($types) !== 1) {
			return $returnType;
		}

		$formType = new ObjectType($types[0]->getValue());
		if (!(new ObjectType('Symfony\Component\Form\FormTypeInterface'))->isSuperTypeOf($formType)->yes()) {
			return $returnType;
		}

		return $formType;
	}

}

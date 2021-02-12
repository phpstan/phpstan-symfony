<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class NodeBuilderCreateNodeDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	private const CREATE_NODE_METHODS = [
		'arrayNode',
		'scalarNode',
		'booleanNode',
		'integerNode',
		'floatNode',
		'enumNode',
		'variableNode',
	];

	public function getClass(): string
	{
		return 'Symfony\Component\Config\Definition\Builder\NodeBuilder';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), self::CREATE_NODE_METHODS, true);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$calledOnType = $scope->getType($methodCall->var);

		$defaultType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

		if ($calledOnType instanceof ParentObjectType) {
			return new ParentObjectType(
				$defaultType->describe(VerbosityLevel::typeOnly()),
				$calledOnType
			);
		}

		return $defaultType;
	}

}

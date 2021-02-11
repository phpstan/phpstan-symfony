<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class TreeBuilderGetRootNodeDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Component\Config\Definition\Builder\TreeBuilder';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getRootNode';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$calledOnType = $scope->getType($methodCall->var);
		if ($calledOnType instanceof TreeBuilderType) {
			$nodeDefinition = $calledOnType->getNodeDefinition();

			return new \PHPStan\Type\ObjectType(get_class($nodeDefinition));
		}

		return new \PHPStan\Type\ObjectType('Symfony\Component\Config\Definition\Builder\NodeDefinition');
	}

}

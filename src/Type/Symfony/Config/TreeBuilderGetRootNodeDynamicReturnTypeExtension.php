<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Symfony\Config\ValueObject\ParentObjectType;
use PHPStan\Type\Symfony\Config\ValueObject\TreeBuilderType;
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

		$defaultType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

		if ($calledOnType instanceof TreeBuilderType) {
			return new ParentObjectType(
				$calledOnType->getRootNodeClassName(),
				$calledOnType
			);
		}

		return $defaultType;
	}

}

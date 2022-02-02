<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Symfony\Config\ValueObject\ParentObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use function count;
use function in_array;

final class ArrayNodeDefinitionPrototypeDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	private const PROTOTYPE_METHODS = [
		'arrayPrototype',
		'scalarPrototype',
		'booleanPrototype',
		'integerPrototype',
		'floatPrototype',
		'enumPrototype',
		'variablePrototype',
	];

	private const MAPPING = [
		'variable' => 'Symfony\Component\Config\Definition\Builder\VariableNodeDefinition',
		'scalar' => 'Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition',
		'boolean' => 'Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition',
		'integer' => 'Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition',
		'float' => 'Symfony\Component\Config\Definition\Builder\FloatNodeDefinition',
		'array' => 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition',
		'enum' => 'Symfony\Component\Config\Definition\Builder\EnumNodeDefinition',
	];

	public function getClass(): string
	{
		return 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'prototype' || in_array($methodReflection->getName(), self::PROTOTYPE_METHODS, true);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$calledOnType = $scope->getType($methodCall->var);

		$defaultType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

		if ($methodReflection->getName() === 'prototype') {
			if (!isset($methodCall->getArgs()[0])) {
				return $defaultType;
			}

			$argStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->getArgs()[0]->value));
			if (count($argStrings) === 1 && isset(self::MAPPING[$argStrings[0]->getValue()])) {
				$type = $argStrings[0]->getValue();

				return new ParentObjectType(self::MAPPING[$type], $calledOnType);
			}
		}

		return new ParentObjectType(
			$defaultType->describe(VerbosityLevel::typeOnly()),
			$calledOnType
		);
	}

}

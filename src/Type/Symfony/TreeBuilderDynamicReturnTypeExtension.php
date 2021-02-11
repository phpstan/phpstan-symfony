<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\FloatNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

final class TreeBuilderDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{

	private const MAPPING = [
		'variable' => VariableNodeDefinition::class,
		'scalar' => ScalarNodeDefinition::class,
		'boolean' => BooleanNodeDefinition::class,
		'integer' => IntegerNodeDefinition::class,
		'float' => FloatNodeDefinition::class,
		'array' => ArrayNodeDefinition::class,
		'enum' => EnumNodeDefinition::class,
	];

	public function getClass(): string
	{
		return 'Symfony\Component\Config\Definition\Builder\TreeBuilder';
	}

	public function isStaticMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === '__construct';
	}

	public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
	{
		if (!$methodCall->class instanceof Name) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$className = $scope->resolveName($methodCall->class);

		$type = 'array';

		if (isset($methodCall->args[1])) {
			$argStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->args[1]->value));
			if (count($argStrings) === 1 && isset(self::MAPPING[$argStrings[0]->getValue()])) {
				$type = $argStrings[0]->getValue();
			}
		}

		return new TreeBuilderType($className, self::MAPPING[$type]);
	}

}

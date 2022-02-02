<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Symfony\Config\ValueObject\TreeBuilderType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use function count;

final class TreeBuilderDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{

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
		return 'Symfony\Component\Config\Definition\Builder\TreeBuilder';
	}

	public function isStaticMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === '__construct';
	}

	public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
	{
		if (!$methodCall->class instanceof Name) {
			throw new ShouldNotHappenException();
		}

		$className = $scope->resolveName($methodCall->class);

		$type = 'array';

		if (isset($methodCall->getArgs()[1])) {
			$argStrings = TypeUtils::getConstantStrings($scope->getType($methodCall->getArgs()[1]->value));
			if (count($argStrings) === 1 && isset(self::MAPPING[$argStrings[0]->getValue()])) {
				$type = $argStrings[0]->getValue();
			}
		}

		return new TreeBuilderType($className, self::MAPPING[$type]);
	}

}

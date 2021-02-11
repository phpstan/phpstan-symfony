<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class TreeBuilderDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension, BrokerAwareExtension
{

	/** @var Broker */
	private $broker;

	public function setBroker(Broker $broker): void
	{
		$this->broker = $broker;
	}

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
		if (!$this->broker->hasClass($className)) {
			return ParametersAcceptorSelector::selectSingle(
				$methodReflection->getVariants()
			)->getReturnType();
		}

		$args = [];
		foreach ($methodCall->args as $arg) {
			$value = $scope->getType($arg->value);

			if (!$value instanceof ConstantScalarType) {
				throw new \LogicException();
			}

			$args[] = $value->getValue();
		}

		$treeBuilder = new $className(
			...$args
		);

		return new TreeBuilderType($className, $treeBuilder->getRootNode());
	}

}

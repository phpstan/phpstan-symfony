<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony\Type;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerInterfaceDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/**
	 * @var ServiceMap
	 */
	private $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap)
	{
		$this->serviceMap = $symfonyServiceMap;
	}

	public function getClass(): string
	{
		return ContainerInterface::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'get';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type {
		$services = $this->serviceMap->getServices();
		return isset($methodCall->args[0])
			&& $methodCall->args[0] instanceof Arg
			&& $methodCall->args[0]->value instanceof String_
			&& \array_key_exists($methodCall->args[0]->value->value, $services)
			&& !$services[$methodCall->args[0]->value->value]['synthetic']
			? new ObjectType($services[$methodCall->args[0]->value->value]['class'])
			: $methodReflection->getReturnType();
	}

}

<?php
declare(strict_types=1);

namespace Lookyman\PHPStan\Symfony\Type;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;

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
		return 'Symfony\Component\DependencyInjection\ContainerInterface';
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
		if (isset($methodCall->args[0])
			&& $methodCall->args[0] instanceof Arg
		) {
			$service = $this->serviceMap->getServiceFromNode($methodCall->args[0]->value, $scope);
			if ($service !== \null && !$service['synthetic']) {
				return new ObjectType($service['class'] ?? $service['id']);
			}
		}
		return $methodReflection->getReturnType();
	}

}

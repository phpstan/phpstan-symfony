<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\Type;

final class CacheInterfaceGetDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return 'Symfony\Contracts\Cache\CacheInterface';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'get';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
	{
		if (!isset($methodCall->getArgs()[1])) {
			return null;
		}

		$callbackReturnType = $scope->getType($methodCall->getArgs()[1]->value);
		if ($callbackReturnType->isCallable()->yes()) {
			$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
				$scope,
				$methodCall->getArgs(),
				$callbackReturnType->getCallableParametersAcceptors($scope)
			);
			$returnType = $parametersAcceptor->getReturnType();

			// generalize template parameters
			return $returnType->generalize(GeneralizePrecision::templateArgument());
		}

		return null;
	}

}

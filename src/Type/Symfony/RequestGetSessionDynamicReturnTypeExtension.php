<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class RequestGetSessionDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	public function __construct(Standard $printer)
	{
		$this->printer = $printer;
	}

	public function getClass(): string
	{
		return 'Symfony\Component\HttpFoundation\Request';
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'getSession';
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$defaultReturnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		$defaultReturnTypeWithoutNull = TypeCombinator::removeNull($defaultReturnType);

		if ($scope->getType(Helper::createMarkerNode($methodCall, $defaultReturnTypeWithoutNull, $this->printer))->equals(new BooleanType())) {
			return $defaultReturnTypeWithoutNull;
		}

		return $defaultReturnType;
	}

}

<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class Helper
{

	public static function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope,
		ServiceMap $serviceMap
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->args[0])) {
			return $returnType;
		}

		$serviceId = ServiceMap::getServiceIdFromNode($methodCall->args[0]->value, $scope);
		if ($serviceId !== null) {
			$service = $serviceMap->getService($serviceId);
			if ($service !== null && !$service->isSynthetic()) {
				return new ObjectType($service->getClass() ?? $serviceId);
			}
		}

		return $returnType;
	}

	public static function specifyTypes(
		MethodReflection $methodReflection,
		MethodCall $node,
		Scope $scope,
		TypeSpecifierContext $context,
		TypeSpecifier $typeSpecifier,
		Standard $printer
	): SpecifiedTypes
	{
		if (!isset($node->args[0])) {
			return new SpecifiedTypes();
		}
		$argType = $scope->getType($node->args[0]->value);
		return $typeSpecifier->create(
			self::createMarkerNode($node->var, $argType, $printer),
			$argType,
			$context
		);
	}

	public static function createMarkerNode(Expr $expr, Type $type, Standard $printer): Expr
	{
		return new Expr\Variable(md5(sprintf(
			'%s::%s',
			$printer->prettyPrintExpr($expr),
			$type->describe(VerbosityLevel::value())
		)));
	}

}

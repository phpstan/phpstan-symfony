<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\ParameterMap;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use function in_array;

final class ParameterDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var string */
	private $className;

	/** @var string|null */
	private $methodGet;

	/** @var string|null */
	private $methodHas;

	/** @var bool */
	private $constantHassers;

	/** @var \PHPStan\Symfony\ParameterMap */
	private $parameterMap;

	public function __construct(string $className, ?string $methodGet, ?string $methodHas, bool $constantHassers, ParameterMap $symfonyParameterMap)
	{
		$this->className = $className;
		$this->methodGet = $methodGet;
		$this->methodHas = $methodHas;
		$this->constantHassers = $constantHassers;
		$this->parameterMap = $symfonyParameterMap;
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		$methods = array_filter([$this->methodGet, $this->methodHas], function (?string $method): bool {
			return $method !== null;
		});

		return in_array($methodReflection->getName(), $methods, true);
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		switch ($methodReflection->getName()) {
			case $this->methodGet:
				return $this->getGetTypeFromMethodCall($methodReflection, $methodCall, $scope);
			case $this->methodHas:
				return $this->getHasTypeFromMethodCall($methodReflection, $methodCall, $scope);
		}
		throw new ShouldNotHappenException();
	}

	private function getGetTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		// We don't use the method's return type because this won't work properly with lowest and
		// highest versions of Symfony ("mixed" for lowest, "array|bool|float|integer|string|null" for highest).
		$returnType = new UnionType([
			new ArrayType(new MixedType(), new MixedType()),
			new BooleanType(),
			new FloatType(),
			new IntegerType(),
			new StringType(),
			new NullType(),
		]);
		if (!isset($methodCall->args[0])) {
			return $returnType;
		}

		$parameterKey = $this->parameterMap::getParameterKeyFromNode($methodCall->args[0]->value, $scope);
		if ($parameterKey !== null) {
			$parameter = $this->parameterMap->getParameter($parameterKey);
			if ($parameter !== null) {
				return $scope->getTypeFromValue($parameter->getValue());
			}
		}

		return $returnType;
	}

	private function getHasTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->args[0]) || !$this->constantHassers) {
			return $returnType;
		}

		$parameterKey = $this->parameterMap::getParameterKeyFromNode($methodCall->args[0]->value, $scope);
		if ($parameterKey !== null) {
			$parameter = $this->parameterMap->getParameter($parameterKey);
			return new ConstantBooleanType($parameter !== null);
		}

		return $returnType;
	}

}

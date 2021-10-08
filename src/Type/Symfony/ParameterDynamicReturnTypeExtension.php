<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\ParameterMap;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
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

	public function __construct(string $className, ?string $methodGet, ?string $methodHas, Configuration $configuration, ParameterMap $symfonyParameterMap)
	{
		$this->className = $className;
		$this->methodGet = $methodGet;
		$this->methodHas = $methodHas;
		$this->constantHassers = $configuration->hasConstantHassers();
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
		if (!isset($methodCall->getArgs()[0])) {
			return $returnType;
		}

		$parameterKey = $this->parameterMap::getParameterKeyFromNode($methodCall->getArgs()[0]->value, $scope);
		if ($parameterKey !== null) {
			$parameter = $this->parameterMap->getParameter($parameterKey);
			if ($parameter !== null) {
				return $this->generalizeTypeFromValue($scope, $parameter->getValue());
			}
		}

		return $returnType;
	}

	/**
	 * @param Scope								 $scope
	 * @param array<mixed>|bool|float|int|string $value
	 */
	private function generalizeTypeFromValue(Scope $scope, $value): Type
	{
		if (is_array($value) && $value !== []) {
			return $this->generalizeType(
				new ArrayType(
					TypeCombinator::union(...array_map(function ($item) use ($scope): Type {
						return $this->generalizeTypeFromValue($scope, $item);
					}, array_keys($value))),
					TypeCombinator::union(...array_map(function ($item) use ($scope): Type {
						return $this->generalizeTypeFromValue($scope, $item);
					}, array_values($value)))
				)
			);
		}

		if (
			is_string($value)
			&& preg_match('/%env\((.*)\:.*\)%/U', $value, $matches) === 1
			&& strlen($matches[0]) === strlen($value)
		) {
			switch ($matches[1]) {
				case 'base64':
				case 'file':
				case 'resolve':
				case 'string':
				case 'trim':
					return new StringType();
				case 'bool':
					return new BooleanType();
				case 'int':
					return new IntegerType();
				case 'float':
					return new FloatType();
				case 'csv':
				case 'json':
				case 'url':
				case 'query_string':
					return new ArrayType(new MixedType(), new MixedType());
				default:
					return new UnionType([
						new ArrayType(new MixedType(), new MixedType()),
						new BooleanType(),
						new FloatType(),
						new IntegerType(),
						new StringType(),
					]);
			}
		}

		return $this->generalizeType($scope->getTypeFromValue($value));
	}

	private function generalizeType(Type $type): Type
	{
		return TypeTraverser::map($type, function (Type $type, callable $traverse): Type {
			if ($type instanceof ConstantArrayType) {
				if (count($type->getValueTypes()) === 0) {
					return new ArrayType(new MixedType(), new MixedType());
				}
				return new ArrayType($this->generalizeType($type->getKeyType()), $this->generalizeType($type->getItemType()));
			}
			if ($type instanceof ConstantType) {
				return $type->generalize(GeneralizePrecision::lessSpecific());
			}
			return $traverse($type);
		});
	}

	private function getHasTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!isset($methodCall->getArgs()[0]) || !$this->constantHassers) {
			return $returnType;
		}

		$parameterKey = $this->parameterMap::getParameterKeyFromNode($methodCall->getArgs()[0]->value, $scope);
		if ($parameterKey !== null) {
			$parameter = $this->parameterMap->getParameter($parameterKey);
			return new ConstantBooleanType($parameter !== null);
		}

		return $returnType;
	}

}

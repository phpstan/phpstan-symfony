<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function count;
use function substr;

class SerializerDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var string */
	private $class;

	/** @var string */
	private $method;

	public function __construct(string $class, string $method)
	{
		$this->class = $class;
		$this->method = $method;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === $this->method;
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		if (!isset($methodCall->getArgs()[1])) {
			return new MixedType();
		}

		$argType = $scope->getType($methodCall->getArgs()[1]->value);
		if (count($argType->getConstantStrings()) === 0) {
			return new MixedType();
		}

		$types = [];
		foreach ($argType->getConstantStrings() as $constantString) {
			$types[] = $this->getType($constantString->getValue());
		}

		return TypeCombinator::union(...$types);
	}

	private function getType(string $objectName): Type
	{
		if (substr($objectName, -2) === '[]') {
			// The key type is determined by the data
			return new ArrayType(new MixedType(false), $this->getType(substr($objectName, 0, -2)));
		}

		return new ObjectType($objectName);
	}

}

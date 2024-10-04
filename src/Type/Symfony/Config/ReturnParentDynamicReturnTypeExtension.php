<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Symfony\Config\ValueObject\ParentObjectType;
use PHPStan\Type\Type;
use function in_array;

final class ReturnParentDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var class-string */
	private string $className;

	/** @var string[] */
	private array $methods;

	/**
	 * @param class-string $className
	 * @param string[] $methods
	 */
	public function __construct(string $className, array $methods)
	{
		$this->className = $className;
		$this->methods = $methods;
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return in_array($methodReflection->getName(), $this->methods, true);
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): ?Type
	{
		$calledOnType = $scope->getType($methodCall->var);
		if ($calledOnType instanceof ParentObjectType) {
			return $calledOnType->getParent();
		}

		return null;
	}

}

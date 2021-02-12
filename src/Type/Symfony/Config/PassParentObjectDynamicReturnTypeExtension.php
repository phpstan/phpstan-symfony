<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Symfony\Config\ValueObject\ParentObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class PassParentObjectDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	/** @var string */
	private $className;

	/** @var string[] */
	private $methods;

	/**
	 * @param string $className
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
	): Type
	{
		$calledOnType = $scope->getType($methodCall->var);

		$defaultType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

		return new ParentObjectType($defaultType->describe(VerbosityLevel::typeOnly()), $calledOnType);
	}

}

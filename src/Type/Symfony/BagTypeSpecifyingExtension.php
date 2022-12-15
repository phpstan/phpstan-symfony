<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\NullType;

final class BagTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	private const HAS_METHOD_NAME = 'has';
	private const GET_METHOD_NAME = 'get';

	/** @var TypeSpecifier */
	private $typeSpecifier;

	/** @var class-string */
	private $className;

	/**
	 * @param class-string $className
	 */
	public function __construct(string $className)
	{
		$this->className = $className;
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
	{
		return $methodReflection->getName() === self::HAS_METHOD_NAME && !$context->null();
	}

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		return $this->typeSpecifier->create(
			new MethodCall($node->var, self::GET_METHOD_NAME, $node->getArgs()),
			new NullType(),
			$context->negate()
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}

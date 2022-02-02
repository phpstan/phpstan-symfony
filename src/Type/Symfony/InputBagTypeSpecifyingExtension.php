<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;
use Symfony\Component\HttpFoundation\InputBag;

final class InputBagTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	private const INPUT_BAG_CLASS = InputBag::class;
	private const HAS_METHOD_NAME = 'has';
	private const GET_METHOD_NAME = 'get';

	/** @var ReflectionProvider */
	private $reflectionProvider;

	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function __construct(ReflectionProvider $reflectionProvider)
	{
		$this->reflectionProvider = $reflectionProvider;
	}

	public function getClass(): string
	{
		return self::INPUT_BAG_CLASS;
	}

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
	{
		return $methodReflection->getName() === self::HAS_METHOD_NAME && !$context->null();
	}

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$classReflection = $this->reflectionProvider->getClass(self::INPUT_BAG_CLASS);
		$methodVariants = $classReflection->getNativeMethod(self::GET_METHOD_NAME)->getVariants();
		$returnType = ParametersAcceptorSelector::selectSingle($methodVariants)->getReturnType();

		if (!TypeCombinator::containsNull($returnType)) {
			return new SpecifiedTypes();
		}

		return $this->typeSpecifier->create(
			new MethodCall($node->var, self::GET_METHOD_NAME, $node->getArgs()),
			TypeCombinator::removeNull($returnType),
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}

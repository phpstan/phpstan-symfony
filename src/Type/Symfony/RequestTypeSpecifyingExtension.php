<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;

final class RequestTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	private const REQUEST_CLASS = 'Symfony\Component\HttpFoundation\Request';
	private const HAS_METHOD_NAME = 'hasSession';
	private const GET_METHOD_NAME = 'getSession';

	/** @var Broker */
	private $broker;

	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getClass(): string
	{
		return self::REQUEST_CLASS;
	}

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
	{
		return $methodReflection->getName() === self::HAS_METHOD_NAME && !$context->null();
	}

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$classReflection = $this->broker->getClass(self::REQUEST_CLASS);
		$methodVariants = $classReflection->getNativeMethod(self::GET_METHOD_NAME)->getVariants();

		return $this->typeSpecifier->create(
			new MethodCall($node->var, self::GET_METHOD_NAME),
			TypeCombinator::removeNull(ParametersAcceptorSelector::selectSingle($methodVariants)->getReturnType()),
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}

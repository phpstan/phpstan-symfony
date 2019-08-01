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
use Symfony\Component\HttpFoundation\Request;

final class RequestTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

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
		return Request::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
	{
		return $methodReflection->getName() === 'hasSession' && !$context->null();
	}

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$classReflection = $this->broker->getClass(Request::class);
		$methodVariants = $classReflection->getNativeMethod('getSession')->getVariants();

		return $this->typeSpecifier->create(
			new MethodCall($node->var, 'getSession'),
			TypeCombinator::removeNull(ParametersAcceptorSelector::selectSingle($methodVariants)->getReturnType()),
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}

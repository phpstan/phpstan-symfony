<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;

final class ServiceTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var string */
	private $className;

	/** @var Standard */
	private $printer;

	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function __construct(string $className, Standard $printer)
	{
		$this->className = $className;
		$this->printer = $printer;
	}

	public function getClass(): string
	{
		return $this->className;
	}

	public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
	{
		return $methodReflection->getName() === 'has' && !$context->null();
	}

	public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		if (!isset($node->getArgs()[0])) {
			return new SpecifiedTypes();
		}
		$argType = $scope->getType($node->getArgs()[0]->value);
		return $this->typeSpecifier->create(
			Helper::createMarkerNode($node->var, $argType, $this->printer),
			$argType,
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}

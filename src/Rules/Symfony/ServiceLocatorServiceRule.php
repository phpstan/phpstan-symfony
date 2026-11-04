<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Symfony\AutowireLocatorTypeExtension;
use function sprintf;

/** @implements Rule<MethodCall> */
final class ServiceLocatorServiceRule implements Rule
{

	private AutowireLocatorTypeExtension $typeExtension;

	public function __construct(AutowireLocatorTypeExtension $typeExtension)
	{
		$this->typeExtension = $typeExtension;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/** @param Scope&DependencyTracker $scope */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Identifier || $node->name->name !== 'get' || !isset($node->getArgs()[0])) {
			return [];
		}
		$services = $this->typeExtension->getServices($node->var, $scope);
		if ($services === null) {
			return [];
		}
		$errors = [];
		foreach ($scope->getType($node->getArgs()[0]->value)->getConstantStrings() as $key) {
			if (!$services->hasOffsetValueType($key)->no()) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf('Service "%s" is not declared in this service locator.', $key->getValue()))
				->identifier('symfonyLocator.serviceNotFound')
				->build();
		}
		return $errors;
	}

}

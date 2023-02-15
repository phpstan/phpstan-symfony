<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
final class InvalidLazyCommandRule implements Rule
{

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @return (string|RuleError)[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf(
			$scope->getType($node->var)
		)->yes()) {
			return [];
		}
		if (!$node->name instanceof Node\Identifier || $node->name->name !== 'setName') {
			return [];
		}

		return ['Symfony Commands should be lazy. See https://symfony.com/blog/new-in-symfony-3-4-lazy-commands'];
	}

}

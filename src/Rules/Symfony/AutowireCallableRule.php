<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Symfony\AutowireCallableTypeResolver;
use function sprintf;

/** @implements Rule<InClassMethodNode> */
final class AutowireCallableRule implements Rule
{

	private AutowireCallableTypeResolver $typeResolver;

	public function __construct(AutowireCallableTypeResolver $typeResolver)
	{
		$this->typeResolver = $typeResolver;
	}

	public function getNodeType(): string
	{
		return InClassMethodNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		$parameters = $node->getOriginalNode()->getParams();
		foreach ($node->getMethodReflection()->getVariants()[0]->getParameters() as $i => $parameter) {
			$result = $this->typeResolver->resolve($parameter->getAttributes(), $parameter->getName(), $scope);
			if ($result === null) {
				continue;
			}
			$error = $result['error'];
			if ($error === null && $result['type'] !== null) {
				$error = $this->typeResolver->checkType($result['type'], $parameter->getType(), $scope);
			}
			if ($error === null) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf('Cannot autowire parameter $%s: %s', $parameter->getName(), $error))
				->identifier('symfony.autowireCallable')
				->line($parameters[$i]->var->getStartLine())
				->build();
		}

		return $errors;
	}

}

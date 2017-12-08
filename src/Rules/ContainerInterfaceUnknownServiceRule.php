<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony\Rules;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerInterfaceUnknownServiceRule implements Rule
{

	/**
	 * @var ServiceMap
	 */
	private $serviceMap;

	public function __construct(ServiceMap $symfonyServiceMap)
	{
		$this->serviceMap = $symfonyServiceMap;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$services = $this->serviceMap->getServices();
		if ($node instanceof MethodCall && $node->name === 'get') {
			$type = $scope->getType($node->var);
			if (!$type instanceof ObjectType) {
				return [];
			}
			return $type->getClassName() === ContainerInterface::class
				&& isset($node->args[0])
				&& $node->args[0] instanceof Arg
				&& $node->args[0]->value instanceof String_
				&& !\array_key_exists($node->args[0]->value->value, $services)
				? [\sprintf('Service "%s" is not registered in the container.', $node->args[0]->value->value)]
				: [];
		}
		return [];
	}

}

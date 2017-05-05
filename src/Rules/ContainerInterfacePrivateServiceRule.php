<?php

declare(strict_types = 1);

namespace Lookyman\PHPStan\Symfony\Rules;

use Lookyman\PHPStan\Symfony\ServiceMap;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerInterfacePrivateServiceRule implements Rule
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

	/**
	 * @param MethodCall $node
	 * @param Scope $scope
	 * @return array
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$services = $this->serviceMap->getServices();
		return $node->name === 'get'
			&& $scope->getType($node->var)->getClass() === ContainerInterface::class
			&& isset($node->args[0])
			&& $node->args[0] instanceof Arg
			&& $node->args[0]->value instanceof String_
			&& \array_key_exists($node->args[0]->value->value, $services)
			&& !$services[$node->args[0]->value->value]['public']
			? [\sprintf('Service "%s" is private.', $node->args[0]->value->value)]
			: [];
	}
}

<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

class LazyServiceMap implements ServiceMap
{

	private ServiceMapFactory $factory;

	private ?ServiceMap $serviceMap = null;

	public function __construct(ServiceMapFactory $factory)
	{
		$this->factory = $factory;
	}

	public function getServices(): array
	{
		return ($this->serviceMap ??= $this->factory->create())->getServices();
	}

	public function getService(string $id): ?ServiceDefinition
	{
		return ($this->serviceMap ??= $this->factory->create())->getService($id);
	}

	public function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		return ($this->serviceMap ??= $this->factory->create())->getServiceIdFromNode($node, $scope);
	}

}

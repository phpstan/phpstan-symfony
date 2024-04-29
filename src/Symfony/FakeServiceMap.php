<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

final class FakeServiceMap implements ServiceMap
{

	/**
	 * @return ServiceDefinition[]
	 */
	public function getServices(): array
	{
		return [];
	}

	public function getService(string $id): ?ServiceDefinition
	{
		return null;
	}

	public function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		return null;
	}

}

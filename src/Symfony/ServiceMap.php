<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

interface ServiceMap
{

	/**
	 * @return ServiceDefinition[]
	 */
	public function getServices(): array;

	public function getService(string $id): ?ServiceDefinition;

	public function getServiceIdFromNode(Expr $node, Scope $scope): ?string;

}

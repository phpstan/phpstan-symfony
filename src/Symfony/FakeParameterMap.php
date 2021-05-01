<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

final class FakeParameterMap implements ParameterMap
{

	/**
	 * @return \PHPStan\Symfony\ParameterDefinition[]
	 */
	public function getParameters(): array
	{
		return [];
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return null;
	}

	public static function getParameterKeyFromNode(Expr $node, Scope $scope): ?string
	{
		return null;
	}
}

<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

final class FakeParameterMap implements ParameterMap
{

	/**
	 * @return ParameterDefinition[]
	 */
	public function getParameters(): array
	{
		return [];
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return null;
	}

	/**
	 * @deprecated Will be removed in 2.0
	 */
	public static function getParameterKeyFromNode(Expr $node, Scope $scope): ?string
	{
		return null;
	}

	/**
	 * @return array<string>
	 */
	public static function getParameterKeysFromNode(Expr $node, Scope $scope): array
	{
		return [];
	}

}

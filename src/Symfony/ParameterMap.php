<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

interface ParameterMap
{

	/**
	 * @return ParameterDefinition[]
	 */
	public function getParameters(): array;

	public function getParameter(string $key): ?ParameterDefinition;

	public static function getParameterKeyFromNode(Expr $node, Scope $scope): ?string;

}

<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

/**
 * @api
 */
interface ParameterMap
{

	/**
	 * @return ParameterDefinition[]
	 */
	public function getParameters(): array;

	public function getParameter(string $key): ?ParameterDefinition;

	/**
	 * @return array<string>
	 */
	public static function getParameterKeysFromNode(Expr $node, Scope $scope): array;

}

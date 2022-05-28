<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

/**
 * @method static array getParameterKeysFromNode(Expr $node, Scope $scope);
 */
interface ParameterMap
{

	/**
	 * @return ParameterDefinition[]
	 */
	public function getParameters(): array;

	public function getParameter(string $key): ?ParameterDefinition;

	/**
	 * @deprecated Will be removed in 2.0
	 */
	public static function getParameterKeyFromNode(Expr $node, Scope $scope): ?string;

	//  TODO: Uncomment this in 2.0
	//	/**
	//	 * @return array<string>
	//	 */
	//	public static function getParameterKeysFromNode(Expr $node, Scope $scope): array;

}

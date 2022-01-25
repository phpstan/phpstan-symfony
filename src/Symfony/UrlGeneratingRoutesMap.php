<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

interface UrlGeneratingRoutesMap
{

	public function hasRouteName(string $name): bool;

	public static function getRouteNameFromNode(Expr $node, Scope $scope): ?string;

}

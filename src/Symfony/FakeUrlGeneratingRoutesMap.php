<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

final class FakeUrlGeneratingRoutesMap implements UrlGeneratingRoutesMap
{

	public function hasRouteName(string $name): bool
	{
		return false;
	}

	public static function getRouteNameFromNode(Expr $node, Scope $scope): ?string
	{
		return null;
	}

}

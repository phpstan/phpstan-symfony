<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeUtils;
use function count;

final class DefaultUrlGeneratingRoutesMap implements UrlGeneratingRoutesMap
{

	/** @var \PHPStan\Symfony\UrlGeneratingRoutesDefinition[] */
	private $routes;

	/**
	 * @param \PHPStan\Symfony\UrlGeneratingRoutesDefinition[] $routes
	 */
	public function __construct(array $routes)
	{
		$this->routes = $routes;
	}

	public function hasRouteName(string $name): bool
	{
		foreach ($this->routes as $route) {
			if ($route->getName() === $name) {
				return true;
			}
		}

		return false;
	}

	public static function getRouteNameFromNode(Expr $node, Scope $scope): ?string
	{
		$strings = TypeUtils::getConstantStrings($scope->getType($node));
		return count($strings) === 1 ? $strings[0]->getValue() : null;
	}

}

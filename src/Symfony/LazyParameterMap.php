<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

abstract class LazyParameterMap implements ParameterMap
{

	private function __construct()
	{
	}

	final public static function create(ParameterMapFactory $parameterMapFactory): self
	{
		// Workaround to make the static method getParameterKeysFromNode() work without sharing state across all LazyParameterMap instances
		$lazyParameterMap = new class () extends LazyParameterMap
		{

			protected static ParameterMapFactory $parameterMapFactory;

			protected static ?ParameterMap $parameterMap;

			protected static function getParameterMap(): ParameterMap
			{
				self::$parameterMap ??= self::$parameterMapFactory->create();
				return self::$parameterMap;
			}

		};
		$lazyParameterMap::$parameterMapFactory = $parameterMapFactory;
		$lazyParameterMap::$parameterMap = null;

		return $lazyParameterMap;
	}

	abstract protected static function getParameterMap(): ParameterMap;

	/**
	 * @return ParameterDefinition[]
	 */
	public function getParameters(): array
	{
		return static::getParameterMap()->getParameters();
	}

	public function getParameter(string $key): ?ParameterDefinition
	{
		return static::getParameterMap()->getParameter($key);
	}

	public static function getParameterKeysFromNode(Expr $node, Scope $scope): array
	{
		return static::getParameterMap()::getParameterKeysFromNode($node, $scope);
	}

}

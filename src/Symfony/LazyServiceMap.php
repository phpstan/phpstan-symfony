<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

abstract class LazyServiceMap implements ServiceMap
{

	private function __construct()
	{
	}

	final public static function create(ServiceMapFactory $serviceMapFactory): self
	{
		// Workaround to make the static method getServiceIdFromNode() work without sharing state across all LazyServiceMap instances
		$lazyServiceMap = new class () extends LazyServiceMap
		{

			public static ServiceMapFactory $serviceMapFactory;

			public static ?ServiceMap $serviceMap;

			protected static function getServiceMap(): ServiceMap
			{
				self::$serviceMap ??= self::$serviceMapFactory->create();
				return self::$serviceMap;
			}

		};
		$lazyServiceMap::$serviceMapFactory = $serviceMapFactory;
		$lazyServiceMap::$serviceMap = null;

		return $lazyServiceMap;
	}

	abstract protected static function getServiceMap(): ServiceMap;

	/**
	 * @return ServiceDefinition[]
	 */
	public function getServices(): array
	{
		return static::getServiceMap()->getServices();
	}

	public function getService(string $id): ?ServiceDefinition
	{
		return static::getServiceMap()->getService($id);
	}

	public static function getServiceIdFromNode(Expr $node, Scope $scope): ?string
	{
		return static::getServiceMap()::getServiceIdFromNode($node, $scope);
	}

}

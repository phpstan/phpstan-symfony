<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Type\Type;

final class ServiceLocatorTypeHelper
{

	public static function getServices(Type $type): ?Type
	{
		$services = $type->getTemplateType('Psr\Container\ContainerInterface', 'TServices');
		return $services->isArray()->yes() ? $services : null;
	}

}

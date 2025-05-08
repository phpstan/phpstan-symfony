<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Container;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class TestContainerType extends ObjectType
{

	public function __construct(
		string $class = 'Symfony\Component\DependencyInjection\ContainerInterface',
		?Type $subtractedType = null,
		?ClassReflection $classReflection = null
	)
	{
		parent::__construct($class, $subtractedType, $classReflection);
	}

}

<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class ParentObjectType extends ObjectType
{

	/** @var Type */
	private $parent;

	public function __construct(string $className, Type $parent)
	{
		parent::__construct($className);

		$this->parent = $parent;
	}

	public function getParent(): Type
	{
		return $this->parent;
	}

}

<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use PHPStan\Type\ObjectType;

class TreeBuilderType extends ObjectType
{

	/** @var string */
	private $rootNodeClassName;

	public function __construct(string $className, string $rootNodeClassName)
	{
		parent::__construct($className);

		$this->rootNodeClassName = $rootNodeClassName;
	}

	public function getRootNodeClassName(): string
	{
		return $this->rootNodeClassName;
	}

}

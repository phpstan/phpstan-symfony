<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use PHPStan\Type\ObjectType;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class TreeBuilderType extends ObjectType
{

	/** @var NodeDefinition */
	private $nodeDefinition;

	public function __construct(string $className, NodeDefinition $nodeDefinition)
	{
		parent::__construct($className);

		$this->nodeDefinition = $nodeDefinition;
	}

	public function getNodeDefinition(): NodeDefinition
	{
		return $this->nodeDefinition;
	}

}

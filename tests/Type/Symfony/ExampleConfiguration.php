<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ExampleConfiguration implements ConfigurationInterface
{

	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('parameters');
		// @formatter:off
		$rootNode->ignoreExtraKeys(true);
		$children = $rootNode->children();
		$dataDirResult = $children->scalarNode('data_dir')
					->isRequired()
					->cannotBeEmpty()
				->end();
				$dataDirResult->scalarNode('extractor_class')
					->isRequired()
					->cannotBeEmpty()
				->end();
		$arrayDbResult = $dataDirResult->arrayNode('db')
					->children()
						->scalarNode('driver')->end()
						->scalarNode('host')->end()
						->scalarNode('port')->end()
						->scalarNode('database')
							->cannotBeEmpty()
						->end()
						->scalarNode('user')
							->isRequired()
						->end()
						->scalarNode('#password')
							->isRequired()
						->end()
					->end();

		$childrenResult = $arrayDbResult->end()->end();
		// @formatter:on
		return $treeBuilder;
	}

}

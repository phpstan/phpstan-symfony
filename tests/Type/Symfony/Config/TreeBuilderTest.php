<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use Iterator;
use PHPStan\Type\Symfony\ExtensionTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class TreeBuilderTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $expression, string $type): void
	{
		$arrayTreeBuilder = new TreeBuilder('my_tree', 'array');
		$arrayRootNode = $arrayTreeBuilder->getRootNode();
		$r = $arrayRootNode
			->children()
			->arrayNode('methods')
			->prototype('scalar')
			->validate()
			->ifNotInArray(['one', 'two'])
			->thenInvalid('%s is not a valid method.')
			->end()
			->end()
			->end()
			->end();

		$this->processFile(
			__DIR__ . '/tree_builder.php',
			$expression,
			$type,
			[
				new ArrayNodeDefinitionPrototypeDynamicReturnTypeExtension(),
				new ReturnParentDynamicReturnTypeExtension('Symfony\Component\Config\Definition\Builder\ExprBuilder', ['end']),
				new ReturnParentDynamicReturnTypeExtension('Symfony\Component\Config\Definition\Builder\NodeBuilder', ['end']),
				new ReturnParentDynamicReturnTypeExtension('Symfony\Component\Config\Definition\Builder\NodeDefinition', ['end']),
				new PassParentObjectDynamicReturnTypeExtension('Symfony\Component\Config\Definition\Builder\NodeBuilder', ['arrayNode', 'scalarNode', 'booleanNode', 'integerNode', 'floatNode', 'enumNode', 'variableNode']),
				new PassParentObjectDynamicReturnTypeExtension('Symfony\Component\Config\Definition\Builder\NodeDefinition', ['children', 'validate']),
				new TreeBuilderGetRootNodeDynamicReturnTypeExtension(),
			],
			[new TreeBuilderDynamicReturnTypeExtension()]
		);
	}

	/**
	 * @return \Iterator<array{string, string}>
	 */
	public function getProvider(): Iterator
	{
		yield ['$treeRootNode', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
			->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
				->scalarNode("protocol")
				->end()
			->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
		', 'Symfony\Component\Config\Definition\Builder\NodeBuilder'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
		', 'Symfony\Component\Config\Definition\Builder\NodeBuilder'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
						->scalarNode("protocol")
		', 'Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
						->scalarNode("protocol")
						->end()
		', 'Symfony\Component\Config\Definition\Builder\NodeBuilder'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
						->scalarNode("protocol")
						->end()
					->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
						->scalarNode("protocol")
						->end()
					->end()
				->end()
		', 'Symfony\Component\Config\Definition\Builder\NodeBuilder'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
						->scalarNode("protocol")
						->end()
					->end()
				->end()
			->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$treeRootNode
			->children()
				->arrayNode("protocols")
					->children()
						->booleanNode("auto_connect")
							->defaultTrue()
						->end()
						->scalarNode("default_connection")
							->defaultValue("default")
						->end()
						->integerNode("positive_value")
							->min(0)
						->end()
						->floatNode("big_value")
							->max(5E45)
						->end()
						->enumNode("delivery")
							->values(["standard", "expedited", "priority"])
						->end()
					->end()
				->end()
			->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];

		yield ['$arrayRootNode', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['$arrayRootNode->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];
		yield ['
		$arrayRootNode
			->children()
                ->arrayNode("methods")
                    ->prototype("scalar")
                        ->defaultNull()
                    ->end()
                ->end()
            ->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$arrayRootNode
			->children()
                ->arrayNode("methods")
                    ->scalarPrototype()
                        ->defaultNull()
                    ->end()
                ->end()
            ->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['
		$arrayRootNode
			->children()
                ->arrayNode("methods")
                    ->prototype("scalar")
                        ->validate()
                            ->ifNotInArray(["one", "two"])
                            ->thenInvalid("%s is not a valid method.")
                        ->end()
                    ->end()
                ->end()
            ->end()
		', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];

		yield ['$variableRootNode', 'Symfony\Component\Config\Definition\Builder\VariableNodeDefinition'];
		yield ['$variableRootNode->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];

		yield ['$scalarRootNode', 'Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition'];
		yield ['$scalarRootNode->defaultValue("default")', 'Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition'];
		yield ['$scalarRootNode->defaultValue("default")->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];

		yield ['$booleanRootNode', 'Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition'];
		yield ['$booleanRootNode->defaultTrue()', 'Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition'];
		yield ['$booleanRootNode->defaultTrue()->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];

		yield ['$integerRootNode', 'Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition'];
		yield ['$integerRootNode->min(0)', 'Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition'];
		yield ['$integerRootNode->min(0)->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];

		yield ['$floatRootNode', 'Symfony\Component\Config\Definition\Builder\FloatNodeDefinition'];
		yield ['$floatRootNode->max(5E45)', 'Symfony\Component\Config\Definition\Builder\FloatNodeDefinition'];
		yield ['$floatRootNode->max(5E45)->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];

		yield ['$enumRootNode', 'Symfony\Component\Config\Definition\Builder\EnumNodeDefinition'];
		yield ['$enumRootNode->values(["standard", "expedited", "priority"])', 'Symfony\Component\Config\Definition\Builder\EnumNodeDefinition'];
		yield ['$enumRootNode->values(["standard", "expedited", "priority"])->end()', 'Symfony\Component\Config\Definition\Builder\TreeBuilder'];
	}

}

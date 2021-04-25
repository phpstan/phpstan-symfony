<?php declare(strict_types = 1);

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use function PHPStan\Testing\assertType;

$treeBuilder = new TreeBuilder('my_tree');
$treeRootNode = $treeBuilder->getRootNode();
assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode);

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode
	->children()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode
	->children()
	->scalarNode("protocol")
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\NodeBuilder', $treeRootNode
	->children());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode
	->children()
	->arrayNode("protocols"));

assertType('Symfony\Component\Config\Definition\Builder\NodeBuilder', $treeRootNode
	->children()
	->arrayNode("protocols")
	->children());

assertType('Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition', $treeRootNode
	->children()
	->arrayNode("protocols")
	->children()
	->scalarNode("protocol"));

assertType('Symfony\Component\Config\Definition\Builder\NodeBuilder', $treeRootNode
	->children()
	->arrayNode("protocols")
	->children()
	->scalarNode("protocol")
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode
	->children()
	->arrayNode("protocols")
	->children()
	->scalarNode("protocol")
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\NodeBuilder', $treeRootNode
	->children()
	->arrayNode("protocols")
	->children()
	->scalarNode("protocol")
	->end()
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode
	->children()
	->arrayNode("protocols")
	->children()
	->scalarNode("protocol")
	->end()
	->end()
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $treeRootNode
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
	->end());

$arrayTreeBuilder = new TreeBuilder('my_tree', 'array');
$arrayRootNode = $arrayTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $arrayRootNode);
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $arrayRootNode->end());
assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $arrayRootNode
	->children()
	->arrayNode("methods")
	->prototype("scalar")
	->defaultNull()
	->end()
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $arrayRootNode
	->children()
	->arrayNode("methods")
	->scalarPrototype()
	->defaultNull()
	->end()
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $arrayRootNode
	->children()
	->arrayNode("methods")
	->prototype("scalar")
	->validate()
	->ifNotInArray(["one", "two"])
	->thenInvalid("%s is not a valid method.")
	->end()
	->end()
	->end()
	->end());

assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $arrayRootNode
	->children()
	->arrayNode("methods")
	->prototype("array")
	->beforeNormalization()
	->ifString()
	->then(static function ($v) {
		return [$v];
	})
	->end()
	->end()
	->end()
	->end());

$variableTreeBuilder = new TreeBuilder('my_tree', 'variable');
$variableRootNode = $variableTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\VariableNodeDefinition', $variableRootNode);
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $variableRootNode->end());

$scalarTreeBuilder = new TreeBuilder('my_tree', 'scalar');
$scalarRootNode = $scalarTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition', $scalarRootNode);
assertType('Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition', $scalarRootNode->defaultValue("default"));
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $scalarRootNode->defaultValue("default")->end());

$booleanTreeBuilder = new TreeBuilder('my_tree', 'boolean');
$booleanRootNode = $booleanTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition', $booleanRootNode);
assertType('Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition', $booleanRootNode->defaultTrue());
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $booleanRootNode->defaultTrue()->end());

$integerTreeBuilder = new TreeBuilder('my_tree', 'integer');
$integerRootNode = $integerTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition', $integerRootNode);
assertType('Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition', $integerRootNode->min(0));
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $integerRootNode->min(0)->end());

$floatTreeBuilder = new TreeBuilder('my_tree', 'float');
$floatRootNode = $floatTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\FloatNodeDefinition', $floatRootNode);
assertType('Symfony\Component\Config\Definition\Builder\FloatNodeDefinition', $floatRootNode->max(5E45));
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $floatRootNode->max(5E45)->end());

$enumTreeBuilder = new TreeBuilder('my_tree', 'enum');
$enumRootNode = $enumTreeBuilder->getRootNode();

assertType('Symfony\Component\Config\Definition\Builder\EnumNodeDefinition', $enumRootNode);
assertType('Symfony\Component\Config\Definition\Builder\EnumNodeDefinition', $enumRootNode->values(["standard", "expedited", "priority"]));
assertType('Symfony\Component\Config\Definition\Builder\TreeBuilder', $enumRootNode->values(["standard", "expedited", "priority"])->end());

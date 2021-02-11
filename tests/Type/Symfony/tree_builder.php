<?php declare(strict_types = 1);

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

$treeBuilder = new TreeBuilder('my_tree');
$treeRootNode = $treeBuilder->getRootNode();

$variableTreeBuilder = new TreeBuilder('my_tree', 'variable');
$variableRootNode = $variableTreeBuilder->getRootNode();

$scalarTreeBuilder = new TreeBuilder('my_tree', 'scalar');
$scalarRootNode = $scalarTreeBuilder->getRootNode();

$booleanTreeBuilder = new TreeBuilder('my_tree', 'boolean');
$booleanRootNode = $booleanTreeBuilder->getRootNode();

$integerTreeBuilder = new TreeBuilder('my_tree', 'integer');
$integerRootNode = $integerTreeBuilder->getRootNode();

$floatTreeBuilder = new TreeBuilder('my_tree', 'float');
$floatRootNode = $floatTreeBuilder->getRootNode();

$arrayTreeBuilder = new TreeBuilder('my_tree', 'array');
$arrayRootNode = $arrayTreeBuilder->getRootNode();

$enumTreeBuilder = new TreeBuilder('my_tree', 'enum');
$enumRootNode = $enumTreeBuilder->getRootNode();

die;

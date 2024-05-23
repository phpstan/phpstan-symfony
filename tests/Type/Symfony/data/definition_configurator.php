<?php declare(strict_types = 1);

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Loader\DefinitionFileLoader;
use function PHPStan\Testing\assertType;

$treeBuilder = new TreeBuilder('my_tree');
$loader = new DefinitionFileLoader($treeBuilder, new \Symfony\Component\Config\FileLocator());

$configurator = new DefinitionConfigurator(
	$treeBuilder,
	$loader,
	'',
	''
);

$rootNode = $configurator->rootNode();
assertType('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $rootNode);

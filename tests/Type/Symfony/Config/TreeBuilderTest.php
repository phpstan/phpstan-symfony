<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony\Config;

use Iterator;
use PHPStan\Type\Symfony\ExtensionTestCase;

final class TreeBuilderTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/tree_builder.php',
			$expression,
			$type,
			[
				new TreeBuilderGetRootNodeDynamicReturnTypeExtension(),
				new NodeDefinitionEndDynamicReturnTypeExtension()
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
		yield ['$arrayRootNode', 'Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition'];
		yield ['$variableRootNode', 'Symfony\Component\Config\Definition\Builder\VariableNodeDefinition'];
		yield ['$scalarRootNode', 'Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition'];
		yield ['$booleanRootNode', 'Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition'];
		yield ['$integerRootNode', 'Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition'];
		yield ['$floatRootNode', 'Symfony\Component\Config\Definition\Builder\FloatNodeDefinition'];
		yield ['$enumRootNode', 'Symfony\Component\Config\Definition\Builder\EnumNodeDefinition'];
	}

}

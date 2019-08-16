<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class ConfigurationDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider servicesProvider
	 */
	public function testServices(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/ExampleConfiguration.php',
			$expression,
			$type,
			new ConfigurationDynamicReturnTypeExtension(ExampleConfiguration::class)
		);
	}

	public function servicesProvider(): Iterator
	{
		yield ['$dataDirResult', NodeBuilder::class];
		yield ['$arrayDbResult', ArrayNodeDefinition::class];
		yield ['$childrenResult', ArrayNodeDefinition::class];
	}

}

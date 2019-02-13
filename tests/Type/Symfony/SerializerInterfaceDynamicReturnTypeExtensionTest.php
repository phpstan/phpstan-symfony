<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class SerializerInterfaceDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getContentProvider
	 */
	public function testGetContent(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/serializer.php',
			$expression,
			$type,
			new SerializerInterfaceDynamicReturnTypeExtension()
		);
	}

	public function getContentProvider(): Iterator
	{
		yield ['$first', 'Bar'];
		yield ['$second', 'array<Bar>'];
		yield ['$third', 'array<array<Bar>>'];
	}

}

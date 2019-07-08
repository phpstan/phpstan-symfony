<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class SerializerDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getContentProvider
	 */
	public function testSerializerInterface(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/serializer.php',
			$expression,
			$type,
			new SerializerDynamicReturnTypeExtension(
				'Symfony\Component\Serializer\SerializerInterface',
				'deserialize'
			)
		);
	}

	/**
	 * @dataProvider getContentProvider
	 */
	public function testDenormalizerInterface(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/denormalizer.php',
			$expression,
			$type,
			new SerializerDynamicReturnTypeExtension(
				'Symfony\Component\Serializer\Normalizer\DenormalizerInterface',
				'denormalize'
			)
		);
	}

	public function getContentProvider(): Iterator
	{
		yield ['$first', 'Bar'];
		yield ['$second', 'array<Bar>'];
		yield ['$third', 'array<array<Bar>>'];
	}

}

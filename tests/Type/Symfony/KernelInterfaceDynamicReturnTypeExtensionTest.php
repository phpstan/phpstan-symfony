<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class KernelInterfaceDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/kernel_interface.php',
			$expression,
			$type,
			[new KernelInterfaceDynamicReturnTypeExtension()]
		);
	}

	/**
	 * @return Iterator<int, array{string, string}>
	 */
	public function getProvider(): Iterator
	{
		yield ['$foo', 'string'];
		yield ['$bar', 'string'];
		yield ['$baz', 'array<int, string>'];
	}

}

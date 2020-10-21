<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class InputBagDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/input_bag_get.php',
			$expression,
			$type,
			new InputBagDynamicReturnTypeExtension()
		);
	}

	/**
	 * @return \Iterator<array{string, string}>
	 */
	public function getProvider(): Iterator
	{
		yield ['$test1', 'string|null'];
		yield ['$test2', 'string|null'];
		yield ['$test3', 'string'];
		yield ['$test4', 'string'];
	}

}

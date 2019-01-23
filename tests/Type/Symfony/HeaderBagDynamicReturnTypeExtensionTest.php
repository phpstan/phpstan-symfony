<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class HeaderBagDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/header_bag_get.php',
			$expression,
			$type,
			new HeaderBagDynamicReturnTypeExtension()
		);
	}

	public function getProvider(): Iterator
	{
		yield ['$test1', 'string|null'];
		yield ['$test2', 'string|null'];
		yield ['$test3', 'string'];
		yield ['$test5', 'string|null'];
		yield ['$test6', 'string'];
		yield ['$test8', 'array<int, string>'];
		yield ['$test9', 'array<int, string>'];
	}

}

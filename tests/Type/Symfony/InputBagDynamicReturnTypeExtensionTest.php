<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class InputBagDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider inputBagProvider
	 */
	public function testInputBag(string $expression, string $type): void
	{
		if (!class_exists('Symfony\\Component\\HttpFoundation\\InputBag')) {
			self::markTestSkipped('The test needs Symfony\Component\HttpFoundation\InputBag class.');
		}

		$this->processFile(
			__DIR__ . '/input_bag.php',
			$expression,
			$type,
			[new InputBagDynamicReturnTypeExtension()]
		);
	}

	/**
	 * @return \Iterator<array{string, string}>
	 */
	public function inputBagProvider(): Iterator
	{
		yield ['$test1', 'string|null'];
		yield ['$test2', 'string|null'];
		yield ['$test3', 'string'];
		yield ['$test4', 'string'];
		yield ['$test5', 'array<string, array<string>|string>'];
		yield ['$test6', 'array<string>'];
	}

}

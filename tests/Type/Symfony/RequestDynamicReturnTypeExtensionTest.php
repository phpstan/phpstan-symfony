<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;

final class RequestDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getContentProvider
	 */
	public function testGetContent(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/request_get_content.php',
			$expression,
			$type,
			new RequestDynamicReturnTypeExtension()
		);
	}

	/**
	 * @return Iterator<int, array{string, string}>
	 */
	public function getContentProvider(): Iterator
	{
		yield ['$content1', 'string'];
		yield ['$content2', 'string'];
		yield ['$content3', 'resource'];
		yield ['$content4', 'resource|string'];
	}

}

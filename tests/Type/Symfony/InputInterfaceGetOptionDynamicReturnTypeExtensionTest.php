<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;
use PHPStan\Symfony\ConsoleApplicationResolver;

final class InputInterfaceGetOptionDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider argumentTypesProvider
	 */
	public function testArgumentTypes(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/ExampleOptionCommand.php',
			$expression,
			$type,
			new InputInterfaceGetOptionDynamicReturnTypeExtension(new ConsoleApplicationResolver(__DiR__ . '/console_application_loader.php'))
		);
	}

	/**
	 * @return \Iterator<array{string, string}>
	 */
	public function argumentTypesProvider(): Iterator
	{
		yield ['$a', 'bool'];
		yield ['$b', 'int|string|null'];
		yield ['$c', 'int|string|null'];
		yield ['$d', 'array<int, string|null>'];
		yield ['$e', 'array<int, string>'];

		yield ['$bb', 'int|string|null'];
		yield ['$cc', 'int|string'];
		yield ['$dd', 'array<int, int|string|null>'];
		yield ['$ee', 'array<int, int|string>'];
	}

}

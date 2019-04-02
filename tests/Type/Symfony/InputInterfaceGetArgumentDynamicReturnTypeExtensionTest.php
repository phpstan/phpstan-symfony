<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;
use PHPStan\Symfony\ConsoleApplicationResolver;

final class InputInterfaceGetArgumentDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider argumentTypesProvider
	 */
	public function testArgumentTypes(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/ExampleBaseCommand.php',
			$expression,
			$type,
			new InputInterfaceGetArgumentDynamicReturnTypeExtension(new ConsoleApplicationResolver(__DiR__ . '/console_application_loader.php'))
		);
	}

	public function argumentTypesProvider(): Iterator
	{
		yield ['$base', 'string|null'];
		yield ['$aaa', 'string'];
		yield ['$bbb', 'string'];
		yield ['$diff', 'array<int, string>|string'];
		yield ['$arr', 'array<int, string>'];
		yield ['$both', 'string|null'];
	}

}

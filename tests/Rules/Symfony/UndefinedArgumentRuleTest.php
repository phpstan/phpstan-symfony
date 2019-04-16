<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\Symfony\ArgumentTypeSpecifyingExtension;

final class UndefinedArgumentRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UndefinedArgumentRule(new ConsoleApplicationResolver(__DIR__ . '/console_application_loader.php'), new Standard());
	}

	/**
	 * @return \PHPStan\Type\MethodTypeSpecifyingExtension[]
	 */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new ArgumentTypeSpecifyingExtension(new Standard()),
		];
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleCommand.php',
			],
			[
				[
					'Command "example-rule" does not define argument "undefined".',
					37,
				],
			]
		);
	}

}

<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PhpParser\PrettyPrinter\Standard;
use PHPStan\Rules\Rule;
use PHPStan\Symfony\ConsoleApplicationResolver;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UndefinedOptionRule>
 */
final class UndefinedOptionRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UndefinedOptionRule(new ConsoleApplicationResolver(__DIR__ . '/console_application_loader.php'), new Standard());
	}

	public function testGetArgument(): void
	{
		$this->analyse(
			[
				__DIR__ . '/ExampleCommand.php',
			],
			[
				[
					'Command "example-rule" does not define option "bbb".',
					49,
				],
			]
		);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/option.neon',
		];
	}

}
